<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_WPF_EVENTTYPE', 'WordPress - WPForms');

use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use Exception;
use function Sodium\add;

final class WpFormsPluginHandler extends BaseContactFormPluginHandler {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the form type field.
	 *
	 * @param mixed  $formData The form data.
	 * @param string $type The type.
	 *
	 * @return mixed|null
	 */
	private function get_form_type_field( $formData, $type ) {
		foreach ( $formData as $field ) {
			if ( array_key_exists('type', $field) && $field['type'] === $type ) {
				return $field;
			}
		}

		return null;
	}

	/**
	 * Converts the entry string into form data.
	 *
	 * @param mixed $entry The entry.
	 *
	 * @return mixed
	 */
	private function convertEntryStringToFormData( $entry ) {
		$formdata = array();

		$entry = json_decode($entry->fields, true);

		foreach ( $entry as $field ) {
			if ( array_key_exists('type', $field) ) {
				$formdata[ $field['type'] ] = $field['value'];
			}
		}

		return $entry;
	}

	/**
	 * Converts to Contact Model.
	 *
	 * @param mixed $formData The form data.
	 *
	 * @return ContactModel
	 *
	 * @throws Exception
	 */
	public function convertToContactModel( $formData ) {
		$contactModel = new ContactModel();

		$contactModel->setEventType(CE4WP_WPF_EVENTTYPE);
		$contactModel->setOptIn(false);
		$contactModel->setOptOut(false);
		$contactModel->setOptActionBy(OptActionBy::OWNER);

		$emailField = $this->get_form_type_field($formData, 'email');

		if ( array_key_exists('value', $emailField) ) {
			if ( ! empty($emailField['value']) ) {
				$contactModel->setEmail($emailField['value']);
			}
		}

		$nameField = $this->get_form_type_field($formData, 'name');

		if ( array_key_exists('first', $nameField) ) {
			if ( ! empty($nameField['first']) ) {
				$contactModel->setFirstName($nameField['first']);
			}
		}
		if ( array_key_exists('last', $nameField) ) {
			if ( ! empty($nameField['last']) ) {
				$contactModel->setLastName($nameField['last']);
			}
		}

		if ( empty($contactModel->firstName) && empty($contactModel->lastName) && ! empty($nameField) && ! empty($nameField['value']) ) {
			$nameValues = preg_split ('/ /', $nameField['value']);
			if ( ! empty($nameValues) ) {
				$arrLength = count($nameValues);
				$contactModel->setFirstName($nameValues[0]);

				if ( $arrLength > 1 ) {
					$contactModel->setLastName($nameValues[ $arrLength - 1 ]);
				}
			}
		}

		$phoneField = $this->get_form_type_field($formData, 'phone');

		if ( ! empty($phoneField) ) {
			if ( ! empty($phoneField['value']) ) {
				$contactModel->setPhone($phoneField['value']);
			}
		}

		$dateField = $this->get_form_type_field($formData, 'date-time');

		if ( ! empty($dateField) && array_key_exists('date', $dateField) ) {
			if ( ! empty($dateField['date']) && in_array(strtolower($dateField['name']), $this->birthdayFields, true) ) {
				$contactModel->setBirthday($dateField['date']);
			}
		}

		$consentField = $this->get_form_type_field($formData, 'gdpr-checkbox');

		if ( ! empty($consentField) && array_key_exists('value', $consentField) ) {
			// If a gdpr checkbox is present it is required before submitting.
			// The value is a string like "I consent to having this website store my information . . . " instead of a bool.
			// Will assume people won't alter or change this to be the other way around so having this value == consent.
			$contactModel->setOptIn(true);
			$contactModel->setOptActionBy(OptActionBy::VISITOR);
		}

		return $contactModel;
	}

	/**
	 * Handles the form submission.
	 *
	 * @param mixed $fields The fields.
	 *
	 * @return void
	 */
	public function ceHandleWpFormsProcessComplete( $fields ): void {
		try {
			$this->upsertContact($this->convertToContactModel($fields));
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	/**
	 * Register the Hooks.
	 *
	 * @return void
	 */
	public function registerHooks() {
		add_action('wpforms_process_complete', array( $this, 'ceHandleWpFormsProcessComplete' ), 10, 4);
	}

	/**
	 * Unregister the Hooks.
	 *
	 * @return void
	 */
	public function unregisterHooks() {
		remove_action('wpforms_process_complete', array( $this, 'ceHandleWpFormsProcessComplete' ));
	}

	/**
	 * Get the contacts.
	 *
	 * @param int|null $limit The limit.
	 *
	 * @return array|null
	 */
	public function get_contacts( $limit = null ) {
		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}

		// Relies on plugin => WpForms paid or pro.
		if ( in_array('wpforms/wpforms.php', apply_filters('active_plugins', get_option('active_plugins')), true)
			|| in_array('wpforms-lite/wpforms.php', apply_filters('active_plugins', get_option('active_plugins')), true)
		) {
			// Get form submissions from the WpForms DB.
			global $wpdb;
			$contactsArray = array();
			// Get the form entries.
			$entryResult = $wpdb->get_results('SELECT fields FROM wp_wpforms_entries');

			// Loop through entries and create the contacts.
			foreach ( $entryResult as $entry ) {
				$contactModel = null;
				try {
					$entryData = $this->convertEntryStringToFormData($entry);
					$contact   = $this->convertToContactModel($entryData);
					if ( ! empty($contact->getEmail()) ) {
						array_push($contactsArray, $contact);
					}
				} catch ( Exception $exception ) {
					DatadogManager::get_instance()->exception_handler($exception);
					continue;
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
}
