<?php declare(strict_types = 1);

namespace CreativeMail\Helpers;

use CreativeMail\Constants\EnvironmentNames;

/**
 * Class EnvironmentHelper
 *
 * @package CreativeMail\Helpers
 */
final class EnvironmentHelper {

	/**
	 * Determines if the plugin is currently pointing towards a test environment.
	 *
	 * @returns bool
	 */
	public static function is_test_environment(): bool {
		return self::get_environment() !== EnvironmentNames::PRODUCTION;
	}

	/**
	 * Gets the name of the environment this version of the plugin is build for.
	 *
	 * @return string
	 */
	public static function get_environment(): string {
		$environment = CE4WP_ENVIRONMENT;

		if ( '{ENV}' === $environment ) {
			$environment = EnvironmentNames::DEVELOPMENT;
		}

		return $environment;
	}

	/**
	 * Gets the url of the app-gateway.
	 *
	 * @param string $path Indicates the URL path that should be appended to the app-gateway URL.
	 *
	 * @return string
	 */
	public static function get_app_gateway_url( string $path = '' ): string {
		$url = CE4WP_APP_GATEWAY_URL;

		if ( '{GATEWAY_URL}' === $url ) {
			$url = 'https://app-gateway.creativemail.com/';
		}

		if ( ! empty($path) ) {
			$url .= $path;
		}

		return $url;
	}

	/**
	 * Gets the url of the app.
	 *
	 * @return string
	 */
	public static function get_app_url(): string {
		$url = CE4WP_APP_URL;

		if ( '{APP_URL}' === $url ) {
			$url = 'https://app.creativemail.com/';
		}

		return $url;
	}
}
