<?php

namespace CreativeMail\Managers\Logs;

use Exception;
use Monolog\Handler\MissingExtensionException;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Formatter\JsonFormatter;

/**
 * Updated from the https://github.com/guspio/monolog-datadog
 * Sends logs to Datadog Logs using Curl integrations
 *
 * You'll need a Datadog account to use this handler.
 *
 * @see    https://docs.datadoghq.com/logs/ Datadog Logs Documentation
 * @author Gusp <contact@gusp.io>
 */
class DatadogHandler extends AbstractProcessingHandler {

	/**
	 * Datadog Api Key access.
	 *
	 * @var string
	 */
	protected const DATADOG_LOG_HOST = 'https://http-intake.logs.datadoghq.com';

	/**
	 * Datadog Api Key access.
	 *
	 * @var string
	 */
	private string $apiKey;

	/**
	 * Datadog's optionals attributes.
	 *
	 * @var array
	 */
	private array $attributes;

	/**
	 * DatadogHandler constructor.
	 *
	 * @param string $apiKey Datadog Api Key access.
	 * @param array  $attributes Some options fore Datadog Logs.
	 * @param int    $level The minimum logging level at which this handler will be triggered.
	 * @param bool   $bubble Whether the messages that are handled can bubble up the stack or not.
	 *
	 * @throws Exception If the Datadog API Key is not set.
	 * @throws MissingExtensionException If the cURL extension is not installed.
	 */
	public function __construct(
		string $apiKey,
		array $attributes = array(),
		int $level = Logger::DEBUG,
		bool $bubble = true
	) {
		parent::__construct($level, $bubble);
		$this->apiKey     = $this->getApiKey($apiKey);
		$this->attributes = $attributes;
	}

	/**
	 * Handles a log record
	 *
	 * @param array $record The record to handle.
	 */
	protected function write( array $record ): void {
		$this->send($record['formatted']);
	}

	/**
	 * Send request to @link https://http-intake.logs.datadoghq.com on send action.
	 *
	 * @param string $record The record to handle.
	 */
	protected function send( string $record ) {
		$headers  = array( 'Content-Type' => 'application/json' );
		$source   = $this->getSource();
		$hostname = $this->getHostname();
		$service  = $this->getService($record);

		$url  = self::DATADOG_LOG_HOST . '/v1/input/';
		$url .= $this->apiKey;
		$url .= '?ddsource=' . $source . '&service=' . $service . '&hostname=' . $hostname;

		$args = array(
			'headers'   => $headers,
			'body'      => $record,
			'sslverify' => true,
		);

		wp_remote_post($url, $args);
	}

	/**
	 * Get Datadog Api Key from $attributes params.
	 *
	 * @param string $apiKey Datadog Api Key access.
	 *
	 * @return string
	 *
	 * @throws Exception If the apiKey is not set.
	 */
	protected function getApiKey( string $apiKey ): string {
		if ( $apiKey ) {
			return $apiKey;
		} else {
			throw new Exception('The Datadog Api Key is required');
		}
	}

	/**
	 * Get Datadog Source from $attributes params.
	 *
	 * @return string
	 */
	protected function getSource(): string {
		return ! empty($this->attributes['source']) ? $this->attributes['source'] : 'php';
	}

	/**
	 * Get Datadog Service from $attributes params.
	 *
	 * @param string $record The record to handle.
	 *
	 * @return string
	 */
	protected function getService( string $record ): string {
		$channel = json_decode($record, true);
		return ! empty($this->attributes['service']) ? $this->attributes['service'] : $channel['channel'];
	}

	/**
	 * Get Datadog Hostname from $attributes params.
	 *
	 * @return string
	 */
	protected function getHostname(): string {
		return ! empty($this->attributes['hostname']) ? $this->attributes['hostname'] : gethostname();
	}

	/**
	 * Returns the default formatter to use with this handler.
	 *
	 * @return JsonFormatter
	 */
	protected function getDefaultFormatter(): JsonFormatter {
		return new JsonFormatter();
	}
}
