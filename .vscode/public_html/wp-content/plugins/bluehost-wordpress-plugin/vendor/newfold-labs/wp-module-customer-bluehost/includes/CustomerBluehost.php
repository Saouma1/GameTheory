<?php
namespace NewfoldLabs\WP\Module\CustomerBluehost;

use NewfoldLabs\WP\Module\CustomerBluehost\SiteMeta;
use NewfoldLabs\WP\Module\CustomerBluehost\AccessToken;
use NewfoldLabs\WP\Module\Data\Helpers\Transient;

/**
 * Helper class for gathering and formatting customer data
 */
class CustomerBluehost {

	/**
	 * Normalized, monthly soft deleted
	 *
	 * @var string
	 */
	private const CUST_DATA = 'bh_cdata';

	/**
	 * Soft delete flag
	 *
	 * @var string
	 */
	private const CUST_DATA_EXP = 'bh_cdata_expiration';

	/**
	 * Retry throttle.
	 *
	 * @var string
	 */
	private const THROTTLE = 'bh_cdata_pause';

	/**
	 * Provided option.
	 *
	 * @var string
	 */
	private const PROVIDED_GUAPI = 'bh_cdata_guapi';

	/**
	 * Provided option.
	 *
	 * @var string
	 */
	private const PROVIDED_MOLE = 'bh_cdata_mole';

	/**
	 * The number of failed connection attempts.
	 *
	 * @var integer
	 */
	private const RETRY_COUNT = 'bh_cdata_retry_count';

	private static $is_provided = false;

	/**
	 * Collect customer data
	 *
	 * @return array of customer data
	 */
	public static function collect() {

		// check if bh_cdata is stale
		if ( self::is_stale() ) {
			self::refresh_data();
		}

		// check if bh_cdata is in option (prefered) - always fresh
		$data = \get_option( self::CUST_DATA );

		// exists and no need for additional checks
		if ( ! empty( $data ) ) {
			return $data;
		}

		// If no option found, check for transient value
		// Get legacy data from Transient value 
		if ( empty( $data ) ) {
			$data = Transient::get( self::CUST_DATA );

			// check if transient data is malformed
			if ( $data &&
				is_array( $data ) &&
				( 
					! array_key_exists( 'signup_date', $data ) ||
					! array_key_exists( 'plan_subtype', $data ) 
				)
			) {
				$data = array();
				Transient::delete( self::CUST_DATA ); // delete malformed transient data
			}

			// valid data found as transient
			if ( ! empty( $data ) ) {
				// migrate transient data to option
				self::save_data( $data );
				Transient::delete( self::CUST_DATA ); // delete transient when data migrated to option
			}
		}

		// data is still empty (not found as option, or valid transient), Fetch it
		if ( empty( $data ) ) {
			self::refresh_data();
		}

		return $data;
	}

	/**
	 * Refresh customer data
	 *
	 */
	private static function refresh_data() {
		
		// get account info
		$guapi = self::get_account_info();

		// bail if no data
		if ( empty( $guapi ) ) {
			return;
		}

		// Validations
		// bail if any required values are missing
		// customer_id, signup_date & plan_subtype required by ecommerce module
		if ( ! array_key_exists( 'customer_id', $guapi ) ) {
			return;
		}
		if ( ! array_key_exists( 'signup_date', $guapi ) ) {
			return;
		}
		if ( ! array_key_exists( 'plan_subtype', $guapi ) ) {
			return;
		}

		// get onboarding info
		$mole  = self::get_onboarding_info();

		// No validations here since this data is not required,
		// TODO - remove mole from this module once we have plugin based onboarding
		// ideally we send it directly as sitemeta during new onboarding

		// combine into bh_cdata format
		$data = array_merge( $guapi, array( 'meta' => $mole ) );

		// save customer data
		self::save_data( $data );
	}

	/**
	 * Save data to option and set expiry
	 */
	private static function save_data( $data ) {

		// save data to option
		\update_option( self::CUST_DATA, $data );

		// set expiration
		if ( self::$is_provided ) {
			\update_option( self::CUST_DATA_EXP, time() + DAY_IN_SECONDS ); // set expiration to now + 1 day
		} else {
			\update_option( self::CUST_DATA_EXP, time() + MONTH_IN_SECONDS ); // set expiration to now + 1 month (30 days)
		}
	}

	/**
	 * Prepopulate with provided data.
	 *
	 * @param string $path of desired API endpoint
	 * @return object|false of response data in json format
	 */
	public static function provided( $path ) {
		$provided = false;
		switch( $path ) {
			case '/onboarding-info':
			case '/hosting-account-info':
				$key = self::get_cdata_key_by_path( $path );
				$provided = \get_option( $key );
				if (
					! empty( $provided )
					&& is_string( $provided )
					&& is_object( $decoded = json_decode( $provided ) )
				) {
					$provided = $decoded;
					self::$is_provided = true;
					\delete_option( $key );
				}
			break;
		}

		return $provided;
	}

	/**
	 * Map usersite path to cdata key.
	 *
	 * @param string $path
	 * @return string
	 */
	private static function get_cdata_key_by_path( $path ) {
		switch( $path ) {
			case '/hosting-account-info':
				return self::PROVIDED_GUAPI;
			case '/onboarding-info':
				return self::PROVIDED_MOLE;
		}
	}

	/**
	 * Connect to API with token via AccessToken Class in Bluehost Plugin
	 *
	 * @param string $path of desired API endpoint
	 * @return object of response data in json format
	 */
	public static function connect( $path ) {

		if ( ! $path ) {
			return;
		}

		$provided = self::provided( $path );

		if ( false !== $provided ) {
			return $provided;
		}

		// bail if throttled
		if ( self::is_throttled() ) {
			return;
		}

		// bail to avoid throttling customer endpoint when can not access token
		if ( ! AccessToken::should_refresh_token() ) {
			return;
		}

		// refresh token if needed
		AccessToken::maybe_refresh_token();

		// construct request
		$token   = AccessToken::get_token();
		$user_id = AccessToken::get_user();
		$domain  = SiteMeta::get_domain();

		if ( empty( $token ) || empty( $user_id ) || empty( $domain ) ) {
			self::throttle();
			return;
		}

		$api_endpoint  = 'https://my.bluehost.com/api/users/'.$user_id.'/usersite/'.$domain;
		$args          = array( 'headers' => array( 'X-SiteAPI-Token' => $token ) );
		$url           = $api_endpoint . $path;
		$response      = wp_remote_get( $url, $args );
		$response_code = wp_remote_retrieve_response_code( $response );

		// exit on errors
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			self::throttle();
			return;
		}

		self::clear_throttle();
		self::$is_provided = false;
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Checks if the expiration option has passed
	 * 
	 * @return bool
	 */
	private static function is_stale() {
		
		// check cdata expiry - return if not yet soft deleted/expired
		$expiry = \get_option( self::CUST_DATA_EXP, false );

		// if no expiry return false, as it hasn't been set up yet (not stale)
		if ( false === $expiry ) {
			return false;
		}

		// if current time is more than the expiration time, data is stale
		if ( $expiry < time() ) {
			return true;
		}

		// otherwise return false (not stale)
		return false;
	}

	/**
	 * Checks if a request should be throttled.
	 *
	 * @return bool
	 */
	public static function is_throttled() {
		$throttle = Transient::get( self::THROTTLE );
		$retry_count = (int) \get_option( self::RETRY_COUNT, 0 );

		if ( false !== $throttle || $retry_count >= 10 ) {
			return true;
		}

		return false;
	}

	/**
	 * Updates the throttle when there is a failure.
	 */
	public static function throttle() {
		$retry_count = (int) \get_option( self::RETRY_COUNT, 0 ) + 1;

		if ( $retry_count <= 5 ) {
			$timeout = MINUTE_IN_SECONDS * $retry_count;
		} elseif ( $retry_count < 10 ) {
			$timeout = HOUR_IN_SECONDS * $retry_count;
		} else {
			$timeout = WEEK_IN_SECONDS;
			$retry_count = 0;
		}

		Transient::set(  self::THROTTLE, 1, $timeout );
		\update_option( self::RETRY_COUNT, $retry_count );
	}


	/**
	 * Clears the retry count option.
	 */
	public static function clear_throttle() {
		\delete_option( self::RETRY_COUNT );
	}


	/**
	 * Connect to the hosting info (guapi) endpoint and format response into hiive friendly data
	 *
	 * @return array of relevant data
	 */
	public static function get_account_info(){

		$info     = array();
		$response = self::connect( '/hosting-account-info' );

		// exit if response is not object
		if ( ! is_object( $response ) ) {
			return $info;
		}

		// transfer relevant data to $info array
		$info['customer_id']  = AccessToken::get_user();

		if (
			isset( $response->affiliate ) &&
			is_object( $response->affiliate ) &&
			// using property_exists in case of null value
			property_exists( $response->affiliate, 'id' ) &&
			property_exists( $response->affiliate, 'tracking_code' )
		) {
			$info['affiliate'] = $response->affiliate->id .":". $response->affiliate->tracking_code;
		}

		if (
			isset( $response->customer ) &&
			is_object( $response->customer )
		) {

			if ( isset( $response->customer->provider ) ) {
				$info['provider'] = $response->customer->provider;
			}

			if ( isset( $response->customer->signup_date ) ) {
				$info['signup_date'] = $response->customer->signup_date;
			}
		}


		if ( isset( $response->plan ) && is_object( $response->plan ) ) {

			// using property_exists in case of null value
			if ( property_exists( $response->plan, 'term' ) ) {
				$info['plan_term'] = $response->plan->term;
			}

			if ( property_exists( $response->plan, 'type' ) ) {
				$info['plan_type'] = $response->plan->type;
			}

			if ( property_exists( $response->plan, 'subtype' ) ) {
				$info['plan_subtype'] = $response->plan->subtype;
			}

			// get username from server rather than $response->plan->username;
			$info['username'] = get_current_user();
		}

		return $info;
	}


	/**
	 * Connect to the onboarding info (mole) endpoint and format response into hiive friendly data
	 *
	 * @return array of relevant data
	 */
	public static function get_onboarding_info(){

		$info     = array();
		$response = self::connect( '/onboarding-info' );

		// exit if response is not object
		if ( ! is_object($response) ) {
			return $info;
		}

		// transfer existing relevant data to $info array
		if (
			isset( $response->description ) &&
			is_object( $response->description )
		) {
			if ( isset( $response->description->comfort_creating_sites ) ) {
				$comfort = self::normalize_comfort( $response->description->comfort_creating_sites ); // normalize to 0-100 value
				if ( $comfort > 0 ) {
					$info['comfort'] = $comfort;
				}
			}

			if ( isset( $response->description->help_needed ) ) {
				$help = self::normalize_help( $response->description->help_needed ); // normalize to 0-100
				if ( $help > 0 ) {
					$info['help'] = $help;
				}
			}
		}


		if (
			isset( $response->site_intentions ) &&
			is_object( $response->site_intentions )
		) {

			if ( isset( $response->site_intentions->want_blog ) ) {
				$blog = self::normalize_blog( $response->site_intentions->want_blog );
				if ( $blog > 0 ) {
					$info['blog'] = $blog;
				}
			}

			if ( isset( $response->site_intentions->want_store ) ) {
				$store = self::normalize_store( $response->site_intentions->want_store );
				if ( $store > 0 ) {
					$info['store'] = $store;
				}
			}

			if ( isset( $response->site_intentions->type ) ) {
				$info['type'] = $response->site_intentions->type;
			}

			if ( isset( $response->site_intentions->topic ) ) {
				$info['topic'] = $response->site_intentions->topic;
			}

			if ( isset( $response->site_intentions->owner ) ) {
				$info['owner'] = $response->site_intentions->owner;
			}

		}

		return $info;
	}

	/**
	 * Normalize blog
	 *
	 * For now this is just 0 or 20 values, but in the future we can update based on other factors and treat as a blog score
	 */
	public static function normalize_blog( $blog ){

		switch( $blog ){
			case '1':
				return 20;
				break;
			default: // 0 or blank
				return 0;
				break;
		}
	}

	/**
	 * Normalize store
	 *
	 * For now this is just 0 or 20 values, but in the future we can update based on other factors and treat as a store score
	 */
	public static function normalize_store( $store ){

		switch( $store ){
			case '1':
				return 20;
				break;
			default: // 0 or blank
				return 0;
				break;
		}
	}

	/**
	 * Normalize values returned for comfort_creating_sites:
	 * -1 When "Skip this step" is clicked
	 *  0 When selected comfort level is closest to "A little" and "Continue" is clicked
	 *  1 When selected comfort level is second closest to "A little" and "Continue" is clicked
	 *  2 When selected comfort level is second closest to "Very" and "Continue" is clicked
	 *  3 When selected comfort level is closest to "Very" and "Continue" is clicked
	 *
	 * @param string $comfort value returned from api for comfort_creating_sites
	 * @return integer representing normalized comfort level
	 */
	public static function normalize_comfort( $comfort ){

		switch( $comfort ){
			case "0":
				return 1;
				break;
			case "1":
				return 33;
				break;
			case "2":
				return 66;
				break;
			case "3":
				return 100;
				break;
			default: // -1 or blank
				return 0;
				break;
		}
	}

	/**
	 * Normalize values returned for help_needed:
	 * no_help When "No help needed" is clicked
	 * diy_with_help When "A little Help" is clicked
	 * do_it_for_me When "Built for you" is clicked
	 * skip When "Skip this step" is clicked
	 *
	 * @param string $help value returned from api for help_needed
	 * @return integer representing normalized help level
	 */
	public static function normalize_help( $help ){

		switch( $help ){
			case "no_help":
				return 1;
				break;
			case "diy_with_help":
				return 50;
				break;
			case "do_it_for_me":
				return 100;
				break;
			default: // skip
				return 0;
				break;
		}
	}

}
