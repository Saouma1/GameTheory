<?php


namespace CreativeMail\Managers;

use CreativeMail\CreativeMail;
use CreativeMail\Clients\CreativeMailClient;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Managers\Logs\DatadogManager;
use Exception;

/**
 * Class CheckoutManager
 *
 * @package CreativeMail\Managers
 */
class FormManager {

	const NONCE     = 'nonce';
	const DOMAIN    = 'creative-mail-by-constant-contact';
	const TELEPHONE = 'telephone';
	const FIRSTNAME = 'first_name';
	const LASTNAME  = 'last_name';
	const EMAIL     = 'email';
	const CONSENT   = 'consent';
	const LISTID    = 'list_id';

	private $creative_mail_client;

	/**
	 * DashboardWidgetModule constructor.
	 */
	public function __construct() {
		$this->creative_mail_client = new CreativeMailClient();
	}

	/**
	 * Add hooks
	 *
	 * @since 1.4.0+
	 */
	public function add_hooks() {
		add_action('wp_ajax_ce4wp_form_submission', array( $this, 'submit_contact' ));
		add_action('wp_ajax_nopriv_ce4wp_form_submission', array( $this, 'submit_contact' ));
		add_action('wp_ajax_ce4wp_get_all_custom_lists', array( $this, 'get_all_custom_lists' ));
		add_action('wp_ajax_ce4wp_creative_email_activated', array( $this, 'get_creative_email_activated' ));
	}


	/**
	 * AJAX handler for subscribed contact.
	 *
	 * @since 1.4.0+
	 */
	public function submit_contact() {
		$data = filter_input_array(INPUT_POST, array(
			self::NONCE     => FILTER_SANITIZE_STRING,
			self::FIRSTNAME => FILTER_SANITIZE_STRING,
			self::TELEPHONE => FILTER_SANITIZE_STRING,
			self::LASTNAME  => FILTER_SANITIZE_STRING,
			self::EMAIL     => FILTER_SANITIZE_EMAIL,
			self::CONSENT   => FILTER_SANITIZE_STRING,
			self::LISTID    => FILTER_SANITIZE_STRING,
		));

		if ( is_array($data) && ( empty($data[ self::NONCE ]) || ! wp_verify_nonce($data[ self::NONCE ], 'ce4wp_form_submission') ) ) {
			wp_send_json_error(esc_html__('Invalid nonce.', self::DOMAIN));
		}

		do_action('ce4wp_contact_submission', $data);

		try {
			// Insert submission into database.
			CreativeMail::get_instance()->get_database_manager()->insert_contact($data);
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}

		wp_send_json_success();
	}

	public function get_all_custom_lists() {
		$data = filter_input_array(INPUT_POST, array(
			self::NONCE => FILTER_SANITIZE_STRING,
		));
		if ( is_array($data) && ( empty($data[ self::NONCE ]) || ! wp_verify_nonce($data[ self::NONCE ], 'ce4wp_get_lists') ) ) {
			wp_send_json_error(esc_html__('Invalid nonce.', self::DOMAIN));
		}
		$list = $this->creative_mail_client->get_all_custom_lists();
		if ( isset($list) ) {
			wp_send_json_success($list);
		} else {
			wp_send_json_error(esc_html__('Could not retrieve data.', self::DOMAIN));
		}
	}

	public function get_creative_email_activated() {
		$data = filter_input_array(INPUT_POST, array(
			self::NONCE => FILTER_SANITIZE_STRING,
		));

		if ( is_array($data) && ( empty($data[ self::NONCE ]) || ! wp_verify_nonce($data[ self::NONCE ], 'ce4wp_get_creative_email_activated') ) ) {
			wp_send_json_error(esc_html__('Invalid nonce.', self::DOMAIN));
		}
		wp_send_json_success(OptionsHelper::get_instance_id() != null);
	}
}
