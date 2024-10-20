<?php

namespace NewFoldLabs\WP\Module\CTB;

use NewfoldLabs\WP\Module\Data\HiiveConnection;
use NewfoldLabs\WP\Module\Data\SiteCapabilities;
use NewfoldLabs\WP\Module\CustomerBluehost\CustomerBluehost;
use WP_Error;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Class CTBApi
 *
 * @package NewfoldLabs\CTB
 */
class CTBApi {

	/**
	 * Register notification routes.
	 */
	public static function registerRoutes() {

		// Add route for fetching a CTB
		register_rest_route(
			'newfold-ctb/v1',
			'/ctb/(?P<id>[a-zA-Z0-9-]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => function ( \WP_REST_Request $request ) {

					// Check for customer_id first. If it's not available, then CTB will not work.
					$customer_data = CustomerBluehost::collect();
					if ( empty( $customer_data ) || ! isset( $customer_data['customer_id'] ) ) {
						return new WP_Error( 500, 'Customer ID is required for CTB' );
					}

					// Capability check for Global CTB support
					$capability  = new SiteCapabilities();
					if ( $capability->get( 'canAccessGlobalCTB' ) ) {
						return new WP_Error( 500, 'Not able to access CTBs.' );
					}

					$response = wp_remote_get(
						NFD_HIIVE_URL . '/sites/v1/ctb/' . $request->get_param( 'id' ) . '',
						array(
							'headers' => array(
								'Content-Type'  => 'application/json',
								'Accept'        => 'application/json',
								'Authorization' => 'Bearer ' . HiiveConnection::get_auth_token(),
							),
							'timeout' => 20,
						)
					);

					if ( $response instanceof WP_Error ) {
						return $response;
					}

					return new \WP_REST_Response( json_decode( wp_remote_retrieve_body( $response ) ), wp_remote_retrieve_response_code( $response ) );
				},
			)
		);

		// Add route for purchasing a CTB
		register_rest_route(
			'newfold-ctb/v1',
			'/ctb/(?P<id>[a-zA-Z0-9-]+)',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => function ( \WP_REST_Request $request ) {

					$ctb_id = $request->get_param( 'id' );
					$customer_data = CustomerBluehost::collect();
					if ( empty( $customer_data ) || ! isset( $customer_data['customer_id'] ) ) {
						return new WP_Error( 500, 'Customer ID is required to purchase CTB' );
					}
					$site_id = container()->plugin()->site_id;
					if ( empty( $site_id ) ) {
						return new WP_Error( 500, 'Site ID is required to purchase CTB' );
					}
					$payload = array(
						'ctb_id'      => $ctb_id,
						'customer_id' => $customer_data['customer_id'],
						'site_id'     => $site_id,
					);

					$response = wp_remote_post(
						NFD_HIIVE_URL . '/sites/v1/ctb/' . $ctb_id . '/purchase',
						array(
							'headers' => array(
								'Content-Type'  => 'application/json',
								'Accept'        => 'application/json',
								'Authorization' => 'Bearer ' . HiiveConnection::get_auth_token(),
							),
							'body'    => wp_json_encode( $payload ),
							'timeout' => 20,
						)
					);

					if ( $response instanceof WP_Error ) {
						return $response;
					}

					return new \WP_REST_Response( json_decode( wp_remote_retrieve_body( $response ) ), wp_remote_retrieve_response_code( $response ) );
				},
			)
		);
	}
}
