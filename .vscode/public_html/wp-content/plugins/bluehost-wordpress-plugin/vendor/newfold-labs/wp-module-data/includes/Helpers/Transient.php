<?php
namespace NewfoldLabs\WP\Module\Data\Helpers;

/**
 * Custom Transient class to handle an Options API based fallback
 */
class Transient {

	/**
	 * Whether to use transients to store temporary data
	 *
	 * If the site has an object-cache.php drop-in, then we can't reliably
	 * use the transients API. We'll try to fall back to the options API.
	 *
	 * @return boolean
	 */
	public static function should_use_transients() {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		return ! array_key_exists( 'object-cache.php', get_dropins() );
	}

	/**
	 * Wrapper for get_transient() with Options API fallback
	 *
	 * @param string $key The key of the transient to retrieve
	 * @return mixed The value of the transient
	 */
	public static function get( $key ) {
		if ( self::should_use_transients() ) {
			return get_transient( $key );
		}

		$data = get_option( $key );
		if ( ! empty( $data ) && isset( $data['expires'] ) ) {
			if ( $data['expires'] > time() ) {
				return $data['value'];
			} else {
				delete_option( $key );
			}
		}

		return false;
	}

	/**
	 * Wrapper for set_transient() with Options API fallback
	 *
	 * @param string  $key     Key to use for storing the transient
	 * @param mixed   $value   Value to be saved
	 * @param integer $expires Optional expiration time in seconds from now. Default is 1 hour
	 * @return boolean Whether the value was saved
	 */
	public static function set( $key, $value, $expires = null ) {
		$expiration = ( $expires ) ? $expires : 60 * MINUTE_IN_SECONDS;
		if ( self::should_use_transients() ) {
			return set_transient( $key, $value, $expiration );
		}

		$data = array(
			'value'   => $value,
			'expires' => $expiration + time(),
		);
		return update_option( $key, $data, false );
	}

	/**
	 * Wrapper for delete_transient() with Options API fallback
	 *
	 * @param string $key The key of the transient/option to delete
	 * @return boolean Whether the value was deleted
	 */
	public static function delete( $key ) {
		if ( self::should_use_transients() ) {
			return delete_transient( $key );
		}

		return delete_option( $key );
	}
}
