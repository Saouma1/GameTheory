<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_GF_EVENTTYPE', 'WordPress - GravityForms');

use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use Exception;
use stdClass;

final class GravityFormsPluginHandler extends BaseContactFormPluginHandler {

	/**
	 * Save the text fields from the form.
	 *
	 * @var array<string>
	 */
	private $textFormFields = array( 'text', 'textarea' );

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Converts to contact model.
	 *
	 * @param object $user The user.
	 *
	 * @return ContactModel
	 *
	 * @throws Exception
	 */
	public function convertToContactModel( $user ) {
		$contactModel = new ContactModel();
		$contactModel->setEventType(CE4WP_GF_EVENTTYPE);

		// If it's a DB sync we set optin on true, action by owner.
		if ( $user->isSync ) {
			$contactModel->setOptIn(true);
			$contactModel->setOptOut(false);
			$contactModel->setOptActionBy(OptActionBy::OWNER);
		} else {
			// Get contact data from submission.
			$contactModel->setOptIn(boolval($user->consent));
			$contactModel->setOptActionBy(OptActionBy::VISITOR);
		}

		$email = $user->email;
		if ( ! empty($email) ) {
			$contactModel->setEmail($email);
		}

		$firstName = isset($user->name['firstName']) ? $user->name['firstName'] : null;
		$insertion = isset($user->name['insertion']) ? $user->name['insertion'] : null;
		$lastName  = isset($user->name['lastName']) ? $user->name['lastName'] : null;

		if ( ! empty($firstName) ) {
			$contactModel->setFirstName($firstName);
		}

		if ( ! empty($lastName) ) {
			if ( ! empty($insertion) ) {
				$lastName = implode(' ', array( $insertion, $lastName ));
			}
			$contactModel->setLastName($lastName);
		}
		if ( ! empty($user->phone) ) {
			$contactModel->setPhone($user->phone);
		}
		if ( ! empty($user->birthday) ) {
			$contactModel->setBirthday($user->birthday);
		}

		return $contactModel;
	}

	/**
	 * Gets the first name, optional insertion and last name from the Contact Form.
	 *
	 * @param mixed                $entry (The form submission).
	 * @param array<string, mixed> $form (The form used).
	 *
	 * @return array (concatenated firstname, insertion and lastname) Returns the concatenated name.
	 */
	private function GetNameValuesFromForm( $entry, $form ): array {
		$name_values = array();

		foreach ( $form['fields'] as $field ) {
			if ( 'name' == $field['type'] ) {
				$values                   = $field['inputs'];
				$name_values['firstName'] = rgar($entry, $values[1]['id']);
				$name_values['insertion'] = rgar($entry, $values[2]['id']);
				$name_values['lastName']  = rgar($entry, $values[3]['id']);
			}
		}

		return $name_values;
	}

	/**
	 * Attempts to get the email from the email field if present,
	 * otherwise searches text fields for email labels and values
	 * Returns the value of the email field or the first valid email found in an "email" labelled text field, or NULL
	 *
	 * @param mixed               $entry The form submission.
	 * @param array<string|mixed> $form The form used.
	 *
	 * @return string (either a validated email or NULL)
	 */
	private function GetEmailFromForm( $entry, $form ): ?string {
		$email = null;
		// Check for email type in form.
		foreach ( $form['fields'] as $field ) {
			if ( 'email' == $field['type'] ) {
				$email = rgar($entry, $field['id']);
				// Check if the values is a valid email.
				if ( filter_var($email, FILTER_VALIDATE_EMAIL) ) {
					return $email;
				}
			}
		}
		// Else check if we can find an email value in text fields.
		foreach ( $form['fields'] as $field ) {
			if ( in_array(strtolower($field['type']), $this->textFormFields, true)
				&& in_array(strtolower($field['label']), $this->emailFields, true) ) {
				$possibleEmail = rgar($entry, $field['id']);
				if ( filter_var($possibleEmail, FILTER_VALIDATE_EMAIL) ) {
					return $possibleEmail;
				}
			}
		}
		return $email;
	}

	/**
	 * Finds all the data from the form.
	 *
	 * @param mixed               $contact The contact.
	 * @param mixed               $entry The form entry.
	 * @param array<string|mixed> $form The form.
	 *
	 * @return void
	 */
	private function FindFormData( $contact, $entry, $form ): void {
		foreach ( $form['fields'] as $field ) {
			if ( 'phone' === strtolower($field['type']) ) {
				$contact->phone = rgar($entry, $field['id']);
			} elseif ( strtolower($field['type']) === 'date' && in_array(strtolower($field['label']), $this->birthdayFields, true) ) {
				$contact->birthday = rgar($entry, $field['id']);
			} elseif ( strtolower($field['type']) === 'consent' && strtolower($field['label']) === 'consent' ) {
				// Consent values in the entry are not common.
				$contact->consent = rgar($entry, (string) ( $field['id'] + .1 ));
			}
		}
	}

	/**
	 * Handles the form submission.
	 *
	 * @param mixed                $entry The entries' submission.
	 * @param array<string, mixed> $form The form used.
	 *
	 * @return void
	 */
	public function ceHandleGravityFormSubmission( $entry, $form ) {
		try {
			$contact        = new stdClass();
			$contact->name  = $this->GetNameValuesFromForm($entry, $form);
			$contact->email = $this->GetEmailFromForm($entry, $form);
			$this->FindFormData($contact, $entry, $form);
			if ( empty($contact->email) ) {
				return;
			}

			$contact->isSync = false;
			$this->upsertContact($this->convertToContactModel($contact));
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	public function registerHooks() {
		add_action('gform_after_submission', array( $this, 'ceHandleGravityFormSubmission' ), 10, 2);
	}

	public function unregisterHooks() {
		remove_action('gform_after_submission', array( $this, 'ceHandleGravityFormSubmission' ));
	}

	/**
	 * Return all the contacts.
	 *
	 * @param int|null $limit The limit of contacts to return.
	 *
	 * @return array|null
	 */
	public function get_contacts( $limit = null ) {
		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}

		// Relies on plugin => GravityForms.
		if ( in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')), true) ) {
			global $wpdb;
			$contactsArray = array();

			// Get the forms and their fields.
			$formsResult = $wpdb->get_results('SELECT form_id, display_meta FROM wp_gf_form_meta');

			// Loop through the forms and get their respective entries.
			foreach ( $formsResult as $form ) {
				// Get the entries and their meta (I think only meta is needed).
				$entryResults = $wpdb->get_results( $wpdb->prepare( 'SELECT entry_id, meta_key, meta_value FROM wp_gf_entry_meta WHERE form_id = %s', $form->form_id ) );
				if ( empty($entryResults) ) {
					continue;
				}
				// Combine all entry meta into their respective entries.
				$entries = array();
				foreach ( $entryResults as $entry ) {
					$entries[ $entry->entry_id ][ $entry->meta_key ] = $entry->meta_value;
				}

				// Get the contact data for each entry.
				foreach ( $entries as $entry ) {
					$contact = new stdClass();
					// Get the formArray from the display_meta.
					$formArray = json_decode($form->display_meta, true);

					$contact->email = $this->GetEmailFromForm($entry, $formArray);
					if ( empty($contact->email) ) {
						continue;
					}
					$contact->name = $this->GetNameValuesFromForm($entry, $formArray);
					$this->FindFormData($contact, $entry, $formArray);
					$contact->isSync = true;

					// Convert to contactModel.
					$contactModel = null;
					try {
						$contactModel = $this->convertToContactModel($contact);
					} catch ( Exception $exception ) {
						DatadogManager::get_instance()->exception_handler($exception);
						continue;
					}

					array_push($contactsArray, $contactModel);

					if ( isset($limit) && count($contactsArray) >= $limit ) {
						break;
					}
				}

				if ( isset($limit) && count($contactsArray) >= $limit ) {
					break;
				}
			}
		}

		if ( ! empty($contactsArray) ) {
			return $contactsArray;
		}

		return null;
	}
}
