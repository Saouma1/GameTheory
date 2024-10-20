<?php

namespace CreativeMail\Modules\Api\Processes;

use CreativeMail\Modules\Api\Models\ApiRequestItem;
use WP_Background_Process;

final class ApiBackgroundProcess extends WP_Background_Process {

	/**
	 * Sets the action name.
	 *
	 * @var string
	 */
	protected $action = 'ce_api_background_process';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param ApiRequestItem $item Queue item to iterate over.
	 *
	 * @return false
	 */
	protected function task( $item ): bool {
		if ( ! isset($item->httpMethod) || empty($item->httpMethod) ) {
			return false;
		}

		if ( ! isset($item->endpoint) || empty($item->endpoint) ) {
			return false;
		}

		$httpMethod = strtoupper($item->httpMethod);

		if ( 'POST' === $httpMethod ) {
			wp_remote_post(
				$item->endpoint, array(
					'method'  => $httpMethod,
					'headers' => array(
						'x-account-id' => $item->accountId,
						'x-api-key'    => $item->apiKey,
						'content-type' => $item->contentType,
					),
					'body'    => $item->payload,
				)
			);
			return false;
		}

		wp_remote_get(
			$item->endpoint, array(
				'method'  => $httpMethod,
				'headers' => array(
					'x-account-id' => $item->accountId,
					'x-api-key'    => $item->apiKey,
					'content-type' => $item->contentType,
				),
			)
		);
		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();
	}
}
