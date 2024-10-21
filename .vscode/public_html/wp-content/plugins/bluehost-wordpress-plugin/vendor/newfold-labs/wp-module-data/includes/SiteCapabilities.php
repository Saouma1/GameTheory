<?php

namespace NewfoldLabs\WP\Module\Data;

/**
 * Class SiteCapabilities
 *
 * Class that handles fetching, caching, and checking of site capabilities.
 *
 * @package NewfoldLabs\WP\Module\Data
 */
class SiteCapabilities {

	/**
	 * Get all capabilities.
	 *
	 * @return array
	 */
	public function all() {
		$capabilities = get_transient( 'nfd_site_capabilities' );
		if ( false === $capabilities ) {
			$capabilities = $this->fetch();
			set_transient( 'nfd_site_capabilities', $capabilities, 4 * HOUR_IN_SECONDS );
		}

		return $capabilities;
	}

	/**
	 * Check if a capability exists.
	 *
	 * @param  string $capability Capability name.
	 *
	 * @return bool
	 */
	public function exists( $capability ) {
		return array_key_exists( $capability, $this->all() );
	}

	/**
	 * Get the value of a capability.
	 *
	 * @param  string $capability Capability name.
	 *
	 * @return bool
	 */
	public function get( $capability ) {
		return $this->exists( $capability ) && $this->all()[ $capability ];
	}

	/**
	 * Fetch all capabilities from Hiive.
	 *
	 * @return array
	 */
	public function fetch() {
		$capabilities = array();

		$response = wp_remote_get(
			NFD_HIIVE_URL . '/sites/v1/capabilities',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . HiiveConnection::get_auth_token(),
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			if ( $data && is_array( $data ) ) {
				$capabilities = $data;
			}
		}

		return $capabilities;
	}

}
