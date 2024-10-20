<?php


namespace CreativeMail\Managers;

use Exception;
use Raygun4php\RaygunClient;

/**
 * The RaygunManager will manage the admin section of the plugin.
 *
 * @package CreativeMail\Managers
 */
final class RaygunManager {

	/**
	 * The RaygunManager instance.
	 *
	 * @var RaygunManager
	 */
	private static $instance;

	/**
	 * The RaygunClient instance.
	 *
	 * @var RaygunClient
	 */
	private $raygun_client;

	/**
	 * RaygunManager constructor.
	 */
	public function __construct() {
		$this->raygun_client = new RaygunClient(CE4WP_RAYGUN_PHP_KEY);
	}

	/**
	 * Transmits an exception to the Raygun.io API
	 *
	 * @param Exception $exception      An exception object to transmit.
	 *
	 * @return void
	 */
	public function exception_handler( Exception $exception ): void {
		$this->raygun_client->SendException($exception, self::build_tags(), self::build_custom_user_data());
	}

	/**
	 * Builds the tags to be sent to Raygun.io
	 *
	 * @return array<string, string>
	 */
	public function build_tags(): array {
		$tags                         = array();
		$tags['CE4WP_PLUGIN_VERSION'] = CE4WP_PLUGIN_VERSION;
		$tags['CE4WP_ENVIRONMENT']    = CE4WP_ENVIRONMENT;
		$tags['CE4WP_BUILD']          = CE4WP_BUILD_NUMBER;

		return $tags;
	}

	/**
	 * Builds the custom user data to be sent to Raygun.io
	 *
	 * @return array<string, mixed>
	 */
	private function build_custom_user_data(): array {
		$userData = array();

		try {
			// Get as much metadata as possible.
			$userData['CE4WP_APP_URL']         = CE4WP_APP_URL;
			$userData['CE4WP_APP_GATEWAY_URL'] = CE4WP_APP_GATEWAY_URL;

			// User data that helps us identify the error.
			$userData['CE4WP_CONNECTED_ACCOUNT_ID']        = get_option(CE4WP_CONNECTED_ACCOUNT_ID);
			$userData['CE4WP_INSTANCE_UUID_KEY']           = get_option(CE4WP_INSTANCE_UUID_KEY);
			$userData['CE4WP_MANAGED_EMAIL_NOTIFICATIONS'] = get_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS);
			$userData['CE4WP_ACTIVATED_PLUGINS']           = get_option(CE4WP_ACTIVATED_PLUGINS);

		} catch ( Exception $e ) {
			// We don't want to throw an exception here, as this is just a helper function.
			// We'll just log the error and move on.
			error_log($e->getMessage());
		}

		return $userData;
	}

	public static function get_instance(): RaygunManager {
		if ( null === self::$instance ) {
			self::$instance = new RaygunManager();
		}

		return self::$instance;
	}
}
