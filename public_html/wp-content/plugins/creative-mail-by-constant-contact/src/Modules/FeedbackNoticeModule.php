<?php

namespace CreativeMail\Modules;

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Managers\Logs\DatadogManager;
use Exception;

class FeedbackNoticeModule {


	private $integration_manager;

	public function __construct() {
		$this->integration_manager = CreativeMail::get_instance()->get_integration_manager();
	}

	public function display() {
		wp_register_style('ce4wp_feedback_notice_css', CE4WP_PLUGIN_URL . 'assets/css/feedback_notice.css', array(), CE4WP_PLUGIN_VERSION);
		wp_enqueue_style('ce4wp_feedback_notice_css');
		wp_enqueue_script('ce4wp_feedback_notice', CE4WP_PLUGIN_URL . 'assets/js/feedback_notice.js', array(), CE4WP_PLUGIN_VERSION, true);

		$admin_url = admin_url('admin-ajax.php');
		$nonce     = wp_create_nonce('ajax-nonce');

		wp_localize_script('ce4wp_feedback_notice', 'ce4wp_data', array(
			'url'             => $admin_url,
			'nonce'           => $nonce,
			'hide_banner_url' => get_rest_url( null, 'creativemail/v1/hide_banner?banner=' ),
		));
		wp_enqueue_script('ce4wp_dashboard', CE4WP_PLUGIN_URL . 'assets/js/dashboard.js', array(), CE4WP_PLUGIN_VERSION, true);
		wp_localize_script('ce4wp_dashboard', 'ce4wp_data', array(
			'url'             => $admin_url,
			'nonce'           => $nonce,
			'hide_banner_url' => get_rest_url( null, 'creativemail/v1/hide_banner?banner=' ),
		));

		$ce_sync_enabled = $this->integration_manager->is_plugin_active('jetpack') || $this->integration_manager->is_plugin_active('jetpack-beta');
		if ( ! $ce_sync_enabled ) {
			if ( OptionsHelper::get_hide_banner('feedback_notice_sync_disabled') ) {
				return;
			}

			include CE4WP_PLUGIN_DIR . 'src/views/admin-feedback-notice/sync-disabled.php';
			return;
		}

		try {
			$contact_metrics       = $this->get_contact_metrics();
			$ce_number_of_contacts = $contact_metrics['number_of_subscribed_contacts'];

			if ( $ce_number_of_contacts < 10 ) {
				if ( OptionsHelper::get_hide_banner('feedback_notice_few_contacts') ) {
					return;
				}

				include CE4WP_PLUGIN_DIR . 'src/views/admin-feedback-notice/few-contacts.php';
			} else {
				if ( OptionsHelper::get_hide_banner('feedback_notice_many_contacts') ) {
					return;
				}

				include CE4WP_PLUGIN_DIR . 'src/views/admin-feedback-notice/many-contacts.php';
			}
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	private function get_contact_metrics() {
		$response = wp_remote_get(
			EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/contacts/contact-metrics'),
			array(
				'headers' => array(
					'x-api-key'    => OptionsHelper::get_instance_api_key(),
					'x-account-id' => OptionsHelper::get_connected_account_id(),
				),
			) );

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'Could not get contact metrics' );
		}

		return json_decode( $response['body'], true );
	}

}
