<?php


namespace CreativeMail\Helpers;

use Exception;

/**
 * Class SsoHelper
 *
 * @package CreativeMail\Helpers
 */
final class SsoHelper {
	/**
	 * Will request a one-time use link that can be used to initiate a single sign on into the Creative Mail product.
	 *
	 * @param int         $instanceId The instance id of the Creative Mail product.
	 * @param string      $apiKey    The api key of the Creative Mail product.
	 * @param int         $connectedAccountId The connected account id of the Creative Mail product.
	 * @param string|null $linkReference The link reference of the Creative Mail product.
	 * @param array|null  $linkParameters The link parameters of the Creative Mail product.
	 *
	 * @return string|null Returns the sso link or null if the link could not be generated.
	 *
	 * @throws Exception When one of the required arguments is not present.
	 */
	public static function generate_sso_link(
		int $instanceId,
		string $apiKey,
		int $connectedAccountId,
		?string $linkReference = null,
		?array $linkParameters = null
	): ?string {
		if ( ! isset( $instanceId ) ) {
			throw new Exception('Please provide a valid siteId');
		}

		if ( ! isset( $apiKey ) ) {
			throw new Exception('Please provide a valid apiKey');
		}

		if ( ! isset( $connectedAccountId ) ) {
			throw new Exception('Please provide a valid connectedAccountId');
		}

		// Build the request.
		$arguments = array(
			'method'  => 'POST',
			'headers' => array(
				'x-api-key'    => $apiKey,
				'x-account-id' => $connectedAccountId,
				'content-type' => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'instance_url'       => get_bloginfo('wpurl'),
					'plugin_version'     => CE4WP_PLUGIN_VERSION,
					'word_press_version' => get_bloginfo('version'),
					'link_reference'     => $linkReference,
					'link_parameters'    => $linkParameters,
				)
			),
		);

		$response = wp_remote_post(EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/account/sso'), $arguments);

		if ( is_wp_error($response) ) {
			return null;
		}

		$properties = json_decode($response['body'], true);

		if ( null === $properties ) {
			return null;
		}

		if ( array_key_exists('login_url', $properties) ) {
			return $properties['login_url'];
		}

		return null;
	}
}
