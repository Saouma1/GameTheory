<?php

namespace CreativeMail\Modules\Api\Models;

use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;

final class ApiRequestItem {

	/**
	 * Stores the HTTP method of the request.
	 *
	 * @var string
	 */
	public $httpMethod;

	/**
	 * Stores the content type of the request.
	 *
	 * @var string
	 */
	public $contentType;

	/**
	 * Stores the endpoint of the request.
	 *
	 * @var string
	 */
	public $endpoint;

	/**
	 * Stores the payload of the request.
	 *
	 * @var string
	 */
	public $payload;

	/**
	 * Stores the API Key of the request.
	 *
	 * @var string
	 */
	public $apiKey;

	/**
	 * Stores the Account ID of the request.
	 *
	 * @var int|null
	 */
	public $accountId;

	/**
	 * Main constructor of the API Method.
	 *
	 * @param string $httpMethod The HTTP method of the request.
	 * @param string $contentType The content type of the request.
	 * @param string $endpoint The endpoint of the request.
	 * @param string $payload The payload of the request.
	 */
	public function __construct( string $httpMethod, string $contentType, string $endpoint, string $payload ) {
		$apiKey    = OptionsHelper::get_instance_api_key();
		$accountId = OptionsHelper::get_connected_account_id();
		$baseUrl   = EnvironmentHelper::get_app_gateway_url('wordpress');

		$this->httpMethod  = $httpMethod;
		$this->contentType = $contentType;
		$this->endpoint    = $baseUrl . $endpoint;
		$this->payload     = $payload;
		$this->apiKey      = $apiKey;
		$this->accountId   = $accountId;
	}
}
