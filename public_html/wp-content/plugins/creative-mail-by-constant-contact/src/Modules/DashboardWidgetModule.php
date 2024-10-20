<?php

namespace CreativeMail\Modules;

use CreativeMail\Clients\CreativeMailClient;
use CreativeMail\CreativeMail;
use CreativeMail\Exceptions\CreativeMailException;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Managers\Logs\DatadogManager;
use Exception;

class DashboardWidgetModule {


	private $creative_mail_client;

	/**
	 * DashboardWidgetModule constructor.
	 */
	public function __construct() {
		$this->creative_mail_client = new CreativeMailClient();
	}

	/**
	 * Shows the Dashboard Widget.
	 */
	public function show() {
		wp_enqueue_script( 'ce4wp_dashboard_widget', CE4WP_PLUGIN_URL . 'assets/js/dashboard.js', array(), CE4WP_PLUGIN_VERSION, true );
		wp_localize_script( 'ce4wp_dashboard_widget', 'ce4wp_data', array(
			'url'   => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'ajax-nonce' ),
		) );

		$ce_has_account = OptionsHelper::get_instance_id() != null;
		if ( ! $ce_has_account ) {
			$this->show_no_account();
			return;
		}

		try {
			$ce_account_status = $this->creative_mail_client->get_account_status();

			if ( ! empty($ce_account_status) ) {
				$ce_has_finished_onboarding = $ce_account_status['has_finished_onboarding'];
				if ( ! $ce_has_finished_onboarding ) {
					$this->show_no_account();
					return;
				}
				$ce_has_campaign = $ce_account_status['has_campaigns'];
				if ( ! $ce_has_campaign ) {
					$this->show_no_campaigns();
				} else {
					$this->show_campaigns();
				}
			} else {
				$this->show_no_campaigns();
			}

			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
				include CE4WP_PLUGIN_DIR . 'src/views/admin-dashboard-widget/divider.php';
				$this->show_woo_commerce();
			}
		} catch ( CreativeMailException $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
			$this->show_exception();
		}
	}

	private function show_no_account() {
		include CE4WP_PLUGIN_DIR . 'src/views/admin-dashboard-widget/no-ce-account.php';
	}

	private function show_no_campaigns() {
		include CE4WP_PLUGIN_DIR . 'src/views/admin-dashboard-widget/no-campaign.php';
	}

	private function show_campaigns() {
		$ce_most_recent_campaigns = $this->creative_mail_client->get_most_recent_campaigns();
		include CE4WP_PLUGIN_DIR . 'src/views/admin-dashboard-widget/most-recent-campaigns.php';
	}

	private function show_woo_commerce() {
		$number_of_possible_notifications = 0;
		$number_of_active_notifications   = 0;

		try {
			$email_manager                 = CreativeMail::get_instance()->get_email_manager();
			$supported_email_notifications = $email_manager->get_managed_email_notifications();
			$active_email_notifications    = array_filter($supported_email_notifications, function ( $email_notification ) {
				return true === $email_notification->active;
			});

			$number_of_active_notifications = count($active_email_notifications);

			if ( $number_of_active_notifications > 0 ) {
				$number_of_possible_notifications = count($email_manager->get_valid_email_notification_names());
				include CE4WP_PLUGIN_DIR . 'src/views/admin-dashboard-widget/woocommerce.php';
			} else {
				include CE4WP_PLUGIN_DIR . 'src/views/admin-dashboard-widget/no-woocommerce.php';
			}
		} catch ( Exception $ex ) {
			DatadogManager::get_instance()->exception_handler($ex);
		}
	}

	private function show_exception() {
		include CE4WP_PLUGIN_DIR . 'src/views/admin-dashboard-widget/exception.php';
	}
}
