<?php
namespace CreativeMail\Managers\Logs;

use CreativeMail\Helpers\EncryptionHelper;
use Exception;
use Monolog\Logger;

/**
 * Class DatadogManager
 */
final class DatadogManager {
	/**
	 * Holds the instance of the DatadogManager class.
	 *
	 * @var self
	 */
	private static $instance;
	/**
	 * Holds the Datadog Handler instance.
	 *
	 * @var DatadogHandler
	 */
	private DatadogHandler $datadog_handler;
	/**
	 * Holds the Datadog Logger instance.
	 *
	 * @var Logger
	 */
	private Logger $logger;
	/**
	 * Holds the attributes for the Datadog Logger.
	 *
	 * @var array
	 */
	private array $attributes = array();

	/**
	 * Obtain the Logger structure.
	 *
	 * @param int $level The minimum logging level at which this handler will be triggered.
	 *
	 * @return void
	 */
	private function get_logger( int $level = Logger::INFO ): void {
		$builder_tracking_id = EncryptionHelper::generate_x_builder_id();
		$log_level           = ucwords(strtolower(Logger::getLevelName($level)));
		$host                = gethostname();

		$this->attributes = array(
			'platform'      => 'appmachine',
			'channel'       => 'CE4WP',
			'hostname'      => $host,
			'source'        => 'php',
			'service'       => 'ce4wp',
			'role'          => 'plugin',
			'repo'          => 'creativ-email-wordpress-plugin',
			'level'         => $log_level,
			'business-unit' => 'addons',
			'properties'    => array(
				'php'               => phpversion(),
				'wordpress'         => get_bloginfo('version'),
				'plugin-version'    => CE4WP_PLUGIN_VERSION,
				'platform'          => 'wordpress',
				'env'               => strtolower(CE4WP_ENVIRONMENT),
				'host'              => $host,
				'Loglevel'          => $log_level,
				'BuilderTrackingId' => $builder_tracking_id,
				'service-group'     => 'wordpress',
				'service-app'       => 'ce4wp',
				'team'              => 'Avengers',
				'TrackingContext'   => array(
					'BuilderTrackingId' => $builder_tracking_id,
				),
			),
			'Timestamp'     => gmdate('Y-m-d H:i:s'),
		);

		$this->logger = new Logger('plugin-creative-mail');
		try {
			$this->datadog_handler = new DatadogHandler(CE4WP_DATADOG_API_KEY, $this->attributes, $level);
        // @codingStandardsIgnoreLine
		} catch ( Exception $e ) {
			// Empty catch to avoid breaking.
		}
		$this->logger->pushHandler($this->datadog_handler);
	}

	/**
	 * Sends an info message to Datadog.
	 *
	 * @param string $message The message to be sent.
	 */
	public function send_info( string $message ) {
		$this->get_logger();
		$this->logger->info($message, $this->attributes);
	}

	/**
	 * Sends a warning message to Datadog.
	 *
	 * @param string $message The message to be sent.
	 */
	public function send_warning( string $message ) {
		$this->get_logger(Logger::WARNING);
		$this->logger->warning($message, $this->attributes);
	}

	/**
	 * Sends an error message to Datadog.
	 *
	 * @param string      $message The message to be sent.
	 * @param string|null $stacktrace The stacktrace to be sent.
	 */
	public function send_error( string $message, string $stacktrace = null ) {
		$this->get_logger(Logger::ERROR);
		if ( ! empty($stacktrace) ) {
			$this->attributes['stacktrace'] = $stacktrace;
		}
		$this->logger->error($message, $this->attributes);
	}

	/**
	 * Sends a debug message to Datadog.
	 *
	 * @param string $message The message to be sent.
	 */
	public function send_debug( string $message ) {
		$this->get_logger(Logger::DEBUG);
		$this->logger->debug($message, $this->attributes);
	}

	/**
	 * Captures exceptions and send them to Datadog.
	 *
	 * @param Exception $exception The exception to be sent.
	 */
	public function exception_handler( Exception $exception ) {
		$message = $exception->getMessage();
		$this->send_error($message, $exception->getTraceAsString());
	}

	/**
	 * DatadogManager instance.
	 *
	 * @return DatadogManager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
