<?php

namespace CreativeMail;

use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Managers\AdminManager;
use CreativeMail\Managers\ApiManager;
use CreativeMail\Managers\CheckoutManager;
use CreativeMail\Managers\FormManager;
use CreativeMail\Managers\DatabaseManager;
use CreativeMail\Managers\EmailManager;
use CreativeMail\Managers\InstanceManager;
use CreativeMail\Managers\IntegrationManager;
use CreativeMail\Modules\Contacts\Managers\ContactsSyncManager;

final class CreativeMail {

	/**
	 * Sets the instance of the CreativeMail class.
	 *
	 * @var CreativeMail
	 */
	private static $instance;

	/**
	 * Sets the instance of the AdminManager class.
	 *
	 * @var AdminManager
	 */
	private $admin_manager;

	/**
	 * Sets the instance of the ApiManager class.
	 *
	 * @var ApiManager
	 */
	private $api_manager;

	/**
	 * Sets the instance of the InstanceManager class.
	 *
	 * @var InstanceManager
	 */
	private $instance_manager;

	/**
	 * Sets the instance of the IntegrationManager class.
	 *
	 * @var IntegrationManager
	 */
	private $integration_manager;

	/**
	 * Sets the instance of the EmailManager class.
	 *
	 * @var EmailManager
	 */
	private $email_manager;

	/**
	 * Sets the instance of the DatabaseManager class.
	 *
	 * @var DatabaseManager
	 */
	private $database_manager;

	/**
	 * Sets the instance of the CheckoutManager class.
	 *
	 * @var CheckoutManager
	 */
	private $checkout_manager;

	/**
	 * Sets the instance of the ContactsSyncManager class.
	 *
	 * @var ContactsSyncManager
	 */
	private $contacts_sync_manager;

	/**
	 * Sets the instance of the FormManager class.
	 *
	 * @var FormManager
	 */
	private $form_manager;

	public function __construct() {
		if ( current_user_can('administrator') ) {
			$this->admin_manager = new AdminManager();
		}

		$this->database_manager      = new DatabaseManager();
		$this->instance_manager      = new InstanceManager();
		$this->api_manager           = new ApiManager();
		$this->integration_manager   = new IntegrationManager();
		$this->email_manager         = new EmailManager();
		$this->checkout_manager      = new CheckoutManager();
		$this->contacts_sync_manager = new ContactsSyncManager();
		$this->form_manager          = new FormManager();
	}

	/**
	 * Add all the hooks required by the plugin.
	 *
	 * @return void
	 */
	public function add_hooks() {
		if ( ! $this->is_active() ) {
			return;
		}

		if ( null !== $this->admin_manager ) {
			$this->admin_manager->add_hooks();
		}
		if ( null !== $this->database_manager ) {
			$this->database_manager->add_hooks();
		}
		$this->api_manager->add_hooks();
		$this->integration_manager->add_hooks();
		$this->instance_manager->add_hooks();
		$this->email_manager->add_hooks();
		$this->form_manager->add_hooks();

		// Check if abandoned cart email is managed by creative mail.
		$enabled = $this->email_manager->is_email_managed('cart_abandoned_ce4wp');

		if ( $enabled ) {
			$this->checkout_manager->add_hooks();
		}
		if ( ! empty(OptionsHelper::get_instance_id()) ) {
			if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true) ) {
				$this->checkout_manager->add_order_completed_wc_hooks();
			}
		}
	}

	/**
	 * Returns the Database Manager instance.
	 *
	 * @return DatabaseManager
	 */
	public function get_database_manager(): DatabaseManager {
		return $this->database_manager;
	}

	/**
	 * Returns the Integration Manager instance.
	 *
	 * @return IntegrationManager
	 */
	public function get_integration_manager(): IntegrationManager {
		return $this->integration_manager;
	}

	/**
	 * Returns the Instance Manager instance.
	 *
	 * @return InstanceManager
	 */
	public function get_instance_manager(): InstanceManager {
		return $this->instance_manager;
	}

	/**
	 * Returns the Api Manager instance.
	 *
	 * @return ApiManager
	 */
	public function get_api_manager(): ApiManager {
		return $this->api_manager;
	}

	/**
	 * Returns the Email Manager instance.
	 *
	 * @return EmailManager
	 */
	public function get_email_manager(): EmailManager {
		return $this->email_manager;
	}

	/**
	 * Returns the Admin Manager instance.
	 *
	 * @return AdminManager
	 */
	public function get_admin_manager(): AdminManager {
		return $this->admin_manager;
	}

	/**
	 * Returns the Contacts Sync Manager instance.
	 *
	 * @return ContactsSyncManager
	 */
	public function get_contacts_sync_manager(): ContactsSyncManager {
		return $this->contacts_sync_manager;
	}

	/**
	 * Returns the Form Manager instance.
	 *
	 * @return FormManager
	 */
	public function get_form_manager(): FormManager {
		return $this->form_manager;
	}

	/**
	 * Checks if the plugin is active.
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return in_array(
			plugin_basename(CE4WP_PLUGIN_FILE),
			apply_filters('active_plugins', get_option('active_plugins')),
			true
		);
	}

	/**
	 * Returns the instance of the CreativeMail class.
	 *
	 * @return CreativeMail
	 */
	public static function get_instance(): CreativeMail {
		if ( null === self::$instance ) {
			self::$instance = new CreativeMail();
		}

		return self::$instance;
	}
}
