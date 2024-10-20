<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_FRM_EVENTTYPE', 'WordPress - Formidable');

use CreativeMail\Exceptions\CreativeMailException;
use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\FormidableContactForm;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use Exception;
use FrmField;   // Formidable Field Class.
use FrmForm;    // Formidable Forms Class.

/**
 * Class FormidablePluginHandler
 *
 * @package CreativeMail\Modules\Contacts\Handlers
 */
class FormidablePluginHandler extends BaseContactFormPluginHandler {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Find the entries from the submitted FE form.
	 *
	 * @param ?array                $entry The entry form data.
	 * @param FormidableContactForm $formidableContact The contact container to hold the data.
	 *
	 * @return void|null
	 */
	private function FindEntryValues( ?array $entry, FormidableContactForm $formidableContact ) {
		if ( $this->isNullOrEmpty($entry) ) {
			return null;
		}
		foreach ( $entry as $field ) {
			$fieldName = strtolower($field->fieldName);
			if ( 'phone' == $field->fieldType || in_array($fieldName, $this->phoneFields, true) ) {
				$formidableContact->phone = $field->entryValue;
			} elseif ( 'text' == $field->fieldType && in_array($fieldName, $this->firstnameFields, true) ) {
				$formidableContact->firstName = $field->entryValue;
			} elseif ( 'text' == $field->fieldType && in_array($fieldName, $this->lastnameFields, true) ) {
				$formidableContact->lastName = $field->entryValue;
			} elseif ( 'date' == $field->fieldType && in_array($fieldName, $this->birthdayFields, true) ) {
				$formidableContact->birthday = $field->entryValue;
			} elseif ( 'email' == $field->fieldType ) {
				$formidableContact->email = $field->entryValue;
			}
		}
	}

	/**
	 * Prepare the data and send it as a ContactModel.
	 *
	 * @param FormidableContactForm $contact The contact container to hold the data.
	 *
	 * @return ContactModel
	 *
	 * @throws Exception If the contact is not valid.
	 */
	public function convertToContactModel( $contact ) {
		$contactModel = new ContactModel();
		$contactModel->setEventType(CE4WP_FRM_EVENTTYPE);

		if ( isset($contact->isSync) && $contact->isSync ) {
			$contactModel->setOptIn(true);
			$contactModel->setOptOut(false);
			$contactModel->setOptActionBy(OptActionBy::OWNER);
		}

		// Formidable doesn't seem to have a consent checkbox.
		if ( ! empty($contact->email) ) {
			$contactModel->setEmail($contact->email);
		}
		if ( ! empty($contact->firstName) ) {
			$contactModel->setFirstName($contact->firstName);
		}
		if ( ! empty($contact->lastName) ) {
			$contactModel->setLastName($contact->lastName);
		}
		if ( ! empty($contact->phone) ) {
			$contactModel->setPhone($contact->phone);
		}
		if ( ! empty($contact->birthday) ) {
			$contactModel->setBirthday($contact->birthday);
		}
		return $contactModel;
	}

	/**
	 * Get the Formidable form data from the FE.
	 *
	 * @param int $entry_id The entry ID.
	 * @param int $form_id The form ID.
	 *
	 * @return void
	 */
	public function ceHandleFormidableFormSubmission( int $entry_id, int $form_id ) {
		try {
			$formidableContact = new FormidableContactForm();
			$nonce_key_prefix  = 'frm_submit_entry';
			$nonce_action      = 'frm_submit_entry_nonce';
			// Map entry values to the field meta.
			$entry = FrmField::get_all_for_form($form_id);

			// Looks for the nonce in the $_POST array.
            // @codingStandardsIgnoreLine
			foreach ( $_POST as $key => $value ) {
				if ( is_string(strstr($key, $nonce_key_prefix)) ) {
					$form_nonce = $value;
					$post_key   = $key;
				}
			}
			if ( empty ($form_nonce) || empty($post_key) ) {
				$exception = new CreativeMailException('Formidable Form Integration - Nonce not found');
				DatadogManager::get_instance()->exception_handler($exception);
				return;
			}

			if ( isset ( $_POST[ $post_key ] )
				&& wp_verify_nonce( sanitize_text_field( wp_unslash( $form_nonce ) ), $nonce_action )
				&& isset ($_POST['item_meta'])
			) {
				$entryFieldData = array_map( 'sanitize_text_field', wp_unslash( $_POST['item_meta'] ) );
			}

			foreach ( $entry as $field ) {
				if ( ! empty($entryFieldData[ $field->id ]) ) {
					$field->entryValue = $entryFieldData[ $field->id ];
					$field->fieldType  = $field->type;
					$field->fieldName  = $field->name;
				}
			}

			// Convert to contactModel.
			$this->FindEntryValues($entry, $formidableContact);

			if ( empty($formidableContact->email) ) {
				return;
			}
			$this->upsertContact($this->convertToContactModel($formidableContact));
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	/**
	 * Register the Hooks associated with Formidable Plugin
	 *
	 * @return void
	 */
	public function registerHooks() {
		add_action('frm_after_create_entry', array( $this, 'ceHandleFormidableFormSubmission' ), 30, 2);
	}

	/**
	 * Unregister the Hooks associated with Formidable Plugin
	 *
	 * @return void
	 */
	public function unregisterHooks() {
		remove_action('frm_after_create_entry', array( $this, 'ceHandleFormidableFormSubmission' ));
	}

	/**
	 * Get the contacts from the Form Plugin
	 *
	 * @param ?int $limit The limit of contacts to return.
	 *
	 * @return array|void|null
	 */
	public function get_contacts( $limit = null ) {
		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}

		if ( in_array('formidable/formidable.php', apply_filters('active_plugins', get_option('active_plugins')), true) ) {
			global $wpdb;
			$contactsArray = array();

			$forms = FrmForm::getAll();
			if ( is_array($forms) ) {
				foreach ( $forms as $form ) {
					$entryResults = $wpdb->get_results($wpdb->prepare('SELECT e.item_id AS entryId, e.meta_value AS entryValue, f.name AS fieldName, f.description AS fieldDescription, f.type AS fieldType
                                FROM wp_frm_item_metas e
                                INNER JOIN wp_frm_fields f ON f.id = e.field_id WHERE f.form_id = %s ORDER BY e.item_id', $form->id));
					if ( empty($entryResults) ) {
						continue;
					}

					$mappedEntries = $this->CombineEntryData($entryResults);

					// Get the contact data for each entry.
					foreach ( $mappedEntries as $entry ) {
						$formidableContact = new FormidableContactForm();

						// Convert to contactModel.
						$this->FindEntryValues($entry, $formidableContact);

						if ( empty($formidableContact->email) ) {
							continue;
						}
						$formidableContact->isSync = true;
						try {
							$contactModel = null;
							$contactModel = $this->convertToContactModel($formidableContact);
						} catch ( Exception $exception ) {
							DatadogManager::get_instance()->exception_handler($exception);
							continue;
						}
						if ( ! empty($contactModel->email) ) {
							array_push($contactsArray, $contactModel);
						}
						if ( isset($limit) && count($contactsArray) >= $limit ) {
							break;
						}
					}
				}
			}
			if ( ! empty($contactsArray) ) {
				return $contactsArray;
			}
			return null;
		}
	}

	/**
	 * Combine the entry data into a single array.
	 *
	 * @param array $entryResults The entry results.
	 *
	 * @return array
	 */
	private function CombineEntryData( array $entryResults ) {
		$entries = array();
		foreach ( $entryResults as $entryRow ) {
			if ( isset($entryRow->entryId) ) {
				$entries[ $entryRow->entryId ][] = $entryRow;
			} elseif ( isset($entryRow->id) ) {
				$entries[ $entryRow->id ][] = $entryRow;
			}
		}
		return $entries;
	}
}
