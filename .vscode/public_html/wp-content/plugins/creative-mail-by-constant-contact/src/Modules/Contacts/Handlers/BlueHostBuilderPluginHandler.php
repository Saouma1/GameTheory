<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_WB4WP_EVENTTYPE', 'WordPress - BlueHost Builder');

use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use Exception;

class BlueHostBuilderPluginHandler extends BaseContactFormPluginHandler {

	public function convertToContactModel( $contact ) {
		$contactModel = new ContactModel();

		$email = $contact->email_address;
		if ( $this->isNullOrEmpty($email) ) {
			return null;
		}
		$contactModel->setEmail($email);
		$contactModel->setEventType(CE4WP_WB4WP_EVENTTYPE);
		// @phpstan-ignore-next-line
		if ( ! empty($contact->opt_in) && ( ! $contact->opt_out || $contact->opt_out < $contact->opt_in ) ) {
			$contactModel->setOptIn(true);
			$contactModel->setOptActionBy(OptActionBy::OWNER);
		}
		// @phpstan-ignore-next-line
		if ( ! empty($contact->opt_out) && ( ! $contact->opt_in || $contact->opt_out > $contact->opt_in ) ) {
			$contactModel->setOptOut($contact->opt_out);
			$contactModel->setOptActionBy(OptActionBy::VISITOR);
		}

		if ( ! empty($contact->first_name) ) {
			$contactModel->setFirstName($contact->first_name);
		}
		if ( ! empty($contact->last_name) ) {
			$contactModel->setLastName($contact->last_name);
		}
		if ( ! empty($contact->phone) ) {
			$contactModel->setPhone($contact->phone);
		}
		if ( ! empty($contact->birthday) ) {
			$contactModel->setBirthday($contact->birthday);
		}

		return $contactModel;
	}

	public function ceHandleBHWBFormSubmission( $contact_id ) {
		try {
			global $wpdb;
			$table   = $wpdb->prefix . 'wb4wp_contacts';
			$contact = $wpdb->get_row( $wpdb->prepare ( 'SELECT * FROM %s WHERE contact_id = %s', $table, $contact_id ) );
			if ( empty($contact->email_address) ) {
				return;
			}
			$this->upsertContact($this->convertToContactModel($contact));
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	public function registerHooks() {
		add_action('wb4wp_contacts_updated', array( $this, 'ceHandleBHWBFormSubmission' ), 10, 1);
	}

	public function unregisterHooks() {
		remove_action('wb4wp_contacts_updated', array( $this, 'ceHandleBHWBFormSubmission' ));
	}

	public function get_contacts( $limit = null ) {
		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}

		if ( in_array('wb4wp-plugin.php', array_map('basename', apply_filters('active_plugins', get_option('active_plugins')))) ) {
			global $wpdb;

			$contactsArray = array();
			$table         = $wpdb->prefix . 'wb4wp_contacts';
			$contacts      = $wpdb->get_results($wpdb->prepare('SELECT * FROM %s', $table));

			// Loop through the entries and extract necessary data.
			foreach ( $contacts as $contact ) {
				// Convert to contactModel.
				$contactModel = null;
				try {
					$contactModel = $this->convertToContactModel($contact);
				} catch ( Exception $exception ) {
					DatadogManager::get_instance()->exception_handler($exception);
					continue;
				}
				if ( ! empty($contactModel) ) {
					array_push($contactsArray, $contactModel);
				}
				if ( isset($limit) && count($contactsArray) >= $limit ) {
					break;
				}
			}

			if ( ! empty($contactsArray) ) {
				return $contactsArray;
			}
		}
		return null;
	}

	public function __construct() {
		parent::__construct();
	}
}
