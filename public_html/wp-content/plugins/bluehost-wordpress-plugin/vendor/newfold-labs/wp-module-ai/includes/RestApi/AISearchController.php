<?php

namespace NewfoldLabs\WP\Module\AI\RestApi;

use NewfoldLabs\WP\Module\AI\Utils\AISearchUtil;
use NewfoldLabs\WP\Module\Data\HiiveConnection;

/**
 * APIs for getting the result from the AI service
 */
class AISearchController extends \WP_REST_Controller {
	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'newfold-ai/v1';

	/**
	 * The base of this controller's route
	 *
	 * @var string
	 */
	protected $rest_base = 'search';

	/**
	 * Register the routes for this objects of the controller
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'get_search_result' ),
					'args'                => array(
						'user_prompt' => array(
							'required' => true,
							'type'     => 'string',
						),
						'identifier'  => array(
							'required' => true,
							'type'     => 'string',
						),
						'extra'       => array(
							'required' => false,
							'type'     => 'array',
						),
					),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/default',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array($this, 'get_default_search_results'),
					'permission_callback' => array($this, 'check_permission'),
				),
			)
		);
	}

	/**
	 * Proxy to the AI service to get the responses.
	 *
	 * @param \WP_REST_Request $request Request object
	 *
	 * @returns \WP_REST_Response|\WP_Error
	 */
	public function get_search_result( \WP_REST_Request $request ) {
		$user_prompt = $request['user_prompt'];
		$identifier  = $request['identifier'];

		$extra       = $request['extra'];
		$hiive_token = HiiveConnection::get_auth_token();

		if ( ! $hiive_token ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You are not authorized to make this call' ),
				array( 'status' => 403 )
			);
		}

		$response = AISearchUtil::get_search_results( $hiive_token, $user_prompt, $identifier, $extra );

		if ( array_key_exists( 'error', $response ) ) {
			return new \WP_Error( 'ServerError', $response['error'] );
		}

		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Proxy to AI service for getting default search results
	 *
	 * @param \WP_REST_Request $request Request object
	 *
	 * @returns \WP_REST_Response|\WP_Error
	 */
	public function get_default_search_results( \WP_REST_Request $request ) {
		$hiive_token = HiiveConnection::get_auth_token();

		if ( ! $hiive_token ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You are not authorized to make this call' ),
				array( 'status' => 403 )
			);
		}

		$response = AISearchUtil::get_default_search_results( $hiive_token );

		if ( array_key_exists( 'error', $response ) ) {
			return new \WP_Error( 'ServerError', $response['error'] );
		}

		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Check permissions for routes.
	 *
	 * @return \WP_Error
	 */
	public function check_permission() {
		if ( ! current_user_can('read') ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You must be authenticated to make this call' ),
				array( 'status' => 401 )
			);
		}
		return true;
	}
}
