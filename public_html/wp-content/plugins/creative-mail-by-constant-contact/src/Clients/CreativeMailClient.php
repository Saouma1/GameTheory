<?php

namespace CreativeMail\Clients;

use CreativeMail\Exceptions\CreativeMailException;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Models\Campaign;

final class CreativeMailClient {

	/**
	 * Holds the Instance Api Key.
	 *
	 * @var string
	 */
	private $instance_api_key;

	/**
	 * Holds the Connected Account ID.
	 *
	 * @var int|null
	 */
	private $connected_account_id;

	public function __construct() {
		$this->instance_api_key     = OptionsHelper::get_instance_api_key();
		$this->connected_account_id = OptionsHelper::get_connected_account_id();
	}

	/**
	 * Get the instance of the CreativeMailClient class.
	 *
	 * @return mixed|null
	 *
	 * @throws CreativeMailException
	 */
	public function get_account_status() {
		$response = wp_remote_get(
			EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/account/status'),
			$this->get_default_headers()
		);

		if ( is_wp_error( $response ) ) {
			throw new CreativeMailException( 'Could not get account status' );
		}

		if ( 401 === $response['response']['code'] ) {
			return null;
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * Get the most recent campaigns.
	 *
	 * @return array<Campaign>
	 *
	 * @throws CreativeMailException
	 */
	public function get_most_recent_campaigns(): array {
		$response = wp_remote_get(
			EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/campaign-statistics/most-recent'),
			$this->get_default_headers()
		);

		if ( is_wp_error( $response ) ) {
			throw new CreativeMailException( 'Could not get most recent campaigns' );
		}

		$campaigns_data = json_decode( $response['body'], true );

		return $this->parse_most_recent_campaigns( $campaigns_data );
	}

	/**
	 * Get all the custom lists.
	 *
	 * @return mixed
	 *
	 * @throws CreativeMailException
	 */
	public function get_all_custom_lists() {
		$response = wp_remote_get(
			EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/lists'),
			$this->get_default_headers()
		);

		if ( is_wp_error( $response ) ) {
			throw new CreativeMailException( 'Could not get all custom lists' );
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * Parse the most recent campaigns.
	 *
	 * @param ?string $campaigns_data The campaigns data.
	 *
	 * @return array<Campaign>
	 */
	private function parse_most_recent_campaigns( $campaigns_data ): array {
		$most_recent_campaigns = array();

		foreach ( $campaigns_data as $campaign_data ) {
			$campaign       = new Campaign();
			$campaign->id   = $campaign_data['external_id'];
			$campaign->name = $campaign_data['name'];

			if ( empty( $campaign_data['scheduled_on'] ) ) {
				$campaign->status   = __( 'Draft', 'creative-mail-by-constant-contact' );
				$campaign->is_draft = true;
			} elseif ( empty( $campaign_data['activity_summaries'] ) ) {
				$scheduled_on = gmdate( 'm/d/Y', strtotime( $campaign_data['scheduled_on'] ) );
				// translators: %s is the date scheduled of the campaign.
				$campaign->status   = sprintf( __( 'Scheduled on %s', 'creative-mail-by-constant-contact' ), $scheduled_on );
				$campaign->is_draft = false;
			} else {
				$sent_on = gmdate( 'm/d/Y', strtotime( $campaign_data['scheduled_on'] ) );
				// translators: %s is the sent date of the campaign.
				$campaign->status   = sprintf( __( 'Sent on %s', 'creative-mail-by-constant-contact' ), $sent_on );
				$campaign->is_draft = false;

				$activity_summary    = $campaign_data['activity_summaries'][0];
				$number_of_opens     = $activity_summary['stats']['em_opens'];
				$number_of_sends     = $activity_summary['stats']['em_sends'];
				$campaign->open_rate = floor( ( $number_of_opens / $number_of_sends ) * 100 );
			}

			$most_recent_campaigns[] = $campaign;
		}

		return $most_recent_campaigns;
	}

	/**
	 * Get the default headers.
	 *
	 * @return array<array<string,string>>
	 */
	private function get_default_headers(): array {
		return array(
			'headers' => array(
				'x-api-key'    => $this->instance_api_key,
				'x-account-id' => $this->connected_account_id,
			),
		);
	}

}
