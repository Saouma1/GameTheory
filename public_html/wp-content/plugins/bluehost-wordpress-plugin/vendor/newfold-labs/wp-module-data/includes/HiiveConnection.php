<?php

namespace NewfoldLabs\WP\Module\Data;

use NewfoldLabs\WP\Module\Data\Helpers\Encryption;
use NewfoldLabs\WP\Module\Data\Helpers\Plugin as PluginHelper;
use NewfoldLabs\WP\Module\Data\Helpers\Transient;
use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Manages a Hiive connection instance and interactions with it
 */
class HiiveConnection implements SubscriberInterface {

	/**
	 * Hiive API url
	 *
	 * @var string
	 */
	private $api;

	/**
	 * Authentication token for data api
	 *
	 * @var string
	 */
	private $token;


	/**
	 * Whether connection attempts are currently throttled
	 *
	 * @var boolean
	 */
	private $throttled;

	/**
	 * Construct
	 */
	public function __construct() {

		if ( ! defined( 'NFD_HIIVE_URL' ) ) {
			define( 'NFD_HIIVE_URL', 'https://hiive.cloud/api' );
		}

		$this->api = NFD_HIIVE_URL;

	}

	/**
	 * Register the hooks required for site verification
	 *
	 * @return void
	 */
	public function register_verification_hooks() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		add_action( 'wp_ajax_nopriv_nfd-hiive-verify', array( $this, 'ajax_verify' ) );

	}

	/**
	 * Set up REST API routes
	 *
	 * @return void
	 */
	public function rest_api_init() {
		$controller = new API\Verify( $this );
		$controller->register_routes();
	}

	/**
	 * Process the admin-ajax request
	 *
	 * @return void
	 */
	public function ajax_verify() {
		$valid  = $this->verify_token( $_REQUEST['token'] );
		$status = ( $valid ) ? 200 : 400;

		$data = array(
			'token' => $_REQUEST['token'],
			'valid' => $valid,
		);
		wp_send_json( $data, $status );
	}

	/**
	 * Confirm whether verification token is valid
	 *
	 * @param string $token Token to verify
	 *
	 * @return boolean
	 */
	public function verify_token( $token ) {
		$saved_token = Transient::get( 'nfd_data_verify_token' );

		if ( $saved_token && $saved_token === $token ) {
			Transient::delete( 'nfd_data_verify_token' );

			return true;
		}

		return false;
	}

	/**
	 * Check whether site has established connection to hiive
	 *
	 * @return boolean
	 */
	public static function is_connected() {
		return (bool) ( self::get_auth_token() );
	}

	/**
	 * Attempt to connect to hiive
	 *
	 * @return void
	 */
	public function connect() {

		if ( $this->is_throttled() ) {
			return;
		}

		$this->throttle();

		$token = md5( wp_generate_password() );
		Transient::set( 'nfd_data_verify_token', $token, 5 * MINUTE_IN_SECONDS );

		$data                 = $this->get_core_data();
		$data['verify_token'] = $token;
		$data['plugins']      = PluginHelper::collect_installed();

		$args = array(
			'body'     => wp_json_encode( $data ),
			'headers'  => array(
				'Content-Type' => 'applicaton/json',
				'Accept'       => 'applicaton/json',
			),
			'blocking' => true,
			'timeout'  => 30,
		);

		$attempts = intval( get_option( 'nfd_data_connection_attempts', 0 ) );
		update_option( 'nfd_data_connection_attempts', $attempts + 1 );

		$response = wp_remote_post( $this->api . '/sites/v2/connect', $args );
		$status   = wp_remote_retrieve_response_code( $response );

		// Created = 201; Updated = 200
		if ( 201 === $status || 200 === $status ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $body->token ) ) {

				// Token is auto-encrypted using the `pre_update_option_nfd_data_token` hook.
				update_option( 'nfd_data_token', $body->token );

			}
		}

	}

	/**
	 * Set the connection throttle
	 *
	 * @return void
	 */
	public function throttle() {
		$interval = $this->get_throttle_interval();

		$this->throttle = Transient::set( 'nfd_data_connection_throttle', true, $interval );
	}

	/**
	 * Determine the throttle interval based off number of connection attempts
	 *
	 * @return integer Time to wait until next connection attempt
	 */
	public function get_throttle_interval() {

		$attempts = intval( get_option( 'nfd_data_connection_attempts', 0 ) );

		// Throttle intervals step-up:
		// Hourly for 4 hours
		// Twice a day for 3 days
		// Once a day for 3 days
		// Every 3 days for 3 times
		// Once a week
		if ( $attempts <= 4 ) {
			return HOUR_IN_SECONDS;
		} elseif ( $attempts <= 10 ) {
			return 12 * HOUR_IN_SECONDS;
		} elseif ( $attempts <= 13 ) {
			return DAY_IN_SECONDS;
		} elseif ( $attempts <= 16 ) {
			return 3 * DAY_IN_SECONDS;
		} else {
			return WEEK_IN_SECONDS;
		}

	}

	/**
	 * Check whether connection is throttled
	 *
	 * @return boolean
	 */
	public function is_throttled() {
		$this->throttled = Transient::get( 'nfd_data_connection_throttle' );

		return $this->throttled;
	}

	/**
	 * Post event data payload to the hiive
	 *
	 * @param Event[] $events Array of Event objects representing the actions that occurred
	 * @param bool    $is_blocking Determines if the request is a blocking request
	 *
	 * @return array|\WP_Error
	 */
	public function notify( $events, $is_blocking = false ) {

		// If for some reason we are not connected, bail out now.
		if ( ! self::is_connected() ) {
			return new \WP_Error( 'hiive_connection', __( 'This site is not connected to the hiive.' ) );
		}

		$payload = array(
			'environment' => $this->get_core_data(),
			'events'      => $events,
		);

		$args = array(
			'body'     => wp_json_encode( $payload ),
			'headers'  => array(
				'Content-Type'  => 'applicaton/json',
				'Accept'        => 'applicaton/json',
				'Authorization' => 'Bearer ' . self::get_auth_token(),
			),
			'blocking' => $is_blocking,
			'timeout'  => $is_blocking ? 10 : .5,
		);

		return wp_remote_post( $this->api . '/sites/v1/events', $args );
	}

	/**
	 * Try to return the auth token
	 *
	 * @return string|false The decrypted token if it's set
	 */
	public static function get_auth_token() {
		return get_option( 'nfd_data_token' );
	}


	/**
	 * Get core site data for initial connection
	 *
	 * @return array
	 */
	public function get_core_data() {
		global $wpdb, $wp_version;

		$data = array(
			'brand'       => sanitize_title( get_option( 'mm_brand', 'false' ) ),
			'cache_level' => intval( get_option( 'newfold_cache_level', 2 ) ),
			'cloudflare'  => get_option( 'newfold_cloudflare_enabled', false ),
			'data'        => NFD_DATA_MODULE_VERSION,
			'email'       => get_option( 'admin_email' ),
			'hostname'    => gethostname(),
			'mysql'       => $wpdb->db_version(),
			'origin'      => container()->plugin()->get( 'id', 'error' ),
			'php'         => phpversion(),
			'plugin'      => container()->plugin()->get( 'version', '0' ),
			'url'         => get_site_url(),
			'username'    => get_current_user(),
			'wp'          => $wp_version,
		);

		return apply_filters( 'newfold_wp_data_module_core_data_filter', $data );

	}
}
