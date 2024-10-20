<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_NF_EVENTTYPE', 'WordPress - NinjaForms');

use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use Exception;
use stdClass;

final class NinjaFormsPluginHandler extends BaseContactFormPluginHandler {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the name from the form.
	 *
	 * @param array $fields The fields.
	 *
	 * @return string|null
	 */
	private function getNameFromForm( $fields ): ?string {
		$name = null;

		foreach ( $fields as $field ) {
			if ( 'name' == $field['key'] || 'name' == $field['type'] || strpos($field['key'], 'full_name') !== false ) {
				return $field['value'];
			}
			if ( 'firstname' == $field['type'] || strpos($field['key'], 'first_name') !== false ) {
				$name = $field['value'];
				continue;
			}
			if ( 'lastname' == $field['type'] || strpos($field['key'], 'last_name') !== false ) {
				return implode(' ', array( $name, $field['value'] ));
			}
		}

		return ! empty($name) ? $name : null;
	}

	/**
	 * Finds the Form Values
	 *
	 * @param object $contact The contact.
	 * @param array  $fields The fields.
	 *
	 * @return void
	 */
	private function FindFormValues( $contact, $fields ): void {
		foreach ( $fields as $field ) {
			if ( in_array($field['key'], $this->emailFields, true) || in_array($field['type'], $this->emailFields, true) ) {
				$contact->email = $field['value'];
			} elseif ( in_array($field['key'], $this->phoneFields, true) || in_array($field['type'], $this->phoneFields, true) ) {
				$contact->phone = $field['value'];
			} elseif ( in_array($field['key'], $this->birthdayFields, true) || in_array(strtolower($field['label']), $this->birthdayFields, true) ) {
				$contact->birthday = $field['value'];
			}
		}
	}

	/**
	 * Convert to Contact Model.
	 *
	 * @param object $contact The contact.
	 *
	 * @return ContactModel The contact model.
	 *
	 * @throws Exception The exception.
	 */
	public function convertToContactModel( $contact ) {
		$contactModel = new ContactModel();

		$contactModel->setEventType(CE4WP_NF_EVENTTYPE);
		// OptIn true on sync, false on form submission.
		$contactModel->setOptIn($contact->opt_in);
		$contactModel->setOptOut(false);
		$contactModel->setOptActionBy(OptActionBy::VISITOR);

		if ( isset($contact->optinByOwner) ) {
			$contactModel->setOptActionBy(OptActionBy::OWNER);
		}

		$email = $contact->email;
		if ( ! empty($email) ) {
			$contactModel->setEmail($email);
		}

		$name      = ! empty($contact->name) ? $contact->name : null;
		$firstName = null;
		$lastName  = null;
		if ( ! empty($name) ) {
			$values    = explode(' ', $contact->name);
			$firstName = array_shift($values);
			$lastName  = implode(' ', $values);
		} else {
			$firstName = isset($contact->firstName) ? $contact->firstName : null;
			$lastName  = isset($contact->lastName) ? $contact->lastName : null;
		}

		if ( ! empty($firstName) ) {
			$contactModel->setFirstName($firstName);
		}
		if ( ! empty($lastName) ) {
			$contactModel->setLastName($lastName);
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
	 * Attempt extraction of additional name.
	 *
	 * @param ?object $contact The contact.
	 * @param mixed   $field_key The field key.
	 * @param ?array  $field_values The field values.
	 *
	 * @return void|null
	 */
	public function attemptAdditionalNameExtraction( $contact, $field_key, $field_values ) {
		// Attempt additional checking for name in an attempt to get custom form fields for names.
		if ( ! isset($field_values[ $field_key ]) ) {
			return null;
		}
		if ( strpos($field_key, 'full_name') !== false || isset($field_values['name']) ) {
			$contact->name = $field_values[ $field_key ];
			return;
		}
		if ( strpos($field_key, 'first_name') !== false || isset($field_values['firstname']) ) {
			$contact->firstName = $field_values[ $field_key ];
			return;
		}
		if ( strpos($field_key, 'last_name') !== false || isset($field_values['lastname']) ) {
			$contact->lastName = $field_values[ $field_key ];
		}
	}

	/**
	 * Handle the NinjaForm submission.
	 *
	 * @param ?array $form_data The form data.
	 *
	 * @return void
	 */
	public function ceHandleNinjaFormSubmission( $form_data ) {
		try {
			$ninjaContact = new stdClass();
			$this->FindFormValues($ninjaContact, $form_data['fields_by_key']);

			if ( empty($ninjaContact->email) ) {
				return;
			};

			$ninjaContact->name   = $this->getNameFromForm($form_data['fields_by_key']);
			$ninjaContact->opt_in = false;

			$this->upsertContact($this->convertToContactModel($ninjaContact));
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	public function registerHooks() {
		add_action('ninja_forms_after_submission', array( $this, 'ceHandleNinjaFormSubmission' ), 10, 1);
	}

	public function unregisterHooks() {
		remove_action('ninja_forms_after_submission', array( $this, 'ceHandleNinjaFormSubmission' ));
	}

	public function get_contacts( $limit = null ) {
		$fields      = null;
		$submissions = null;

		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}
		try {
			// Relies on plugin => NinjaForms.
			if ( in_array('ninja-forms/ninja-forms.php', apply_filters('active_plugins', get_option('active_plugins')), true) ) {
				$contactsArray = array();
				// Get an array of Form Models for All Forms.
				$forms = Ninja_Forms()->form()->get_forms();
				if ( is_array($forms) ) {
					foreach ( $forms as $form ) {
						$formId = $form->get_id();
						// Get all form fields and submissions for the form.
						if ( ! empty($formId) ) {
							$fields      = Ninja_Forms()->form($formId)->get_fields();
							$submissions = Ninja_Forms()->form($formId)->get_subs();
						}
						if ( ! empty($submissions) ) {
							foreach ( $submissions as $submission ) {
								$contact = new stdClass();
								// Get all values for a submission.
								$field_values = $submission->get_field_values();

								if ( ! empty($fields) ) {
									foreach ( $fields as $field ) {
										// Get field settings, so we can map the values with its field type.
										$field_settings = $field->get_settings();
										$field_key      = $field_settings['key'];
										$field_type     = $field_settings['type'];
										$field_value    = $field_values[ $field_key ] ?? null; // This prevents undefined index on altered forms.
										switch ( $field_type ) {
											case 'email':
												$email = $field_value;
												if ( filter_var($email, FILTER_VALIDATE_EMAIL) ) {
													$contact->email = $email;
												}
												break;
											case 'phone':
												$contact->phone = $field_value;
												break;
											case 'date':
												if ( in_array(strtolower($field_settings['label']), $this->birthdayFields, true) ) {
													$contact->birthday = $field_value;
												}
												break;
											case 'name':
											case 'full_name':
												$contact->name = $field_value;
												break;
											case 'firstname':
											case 'first_name':
												$contact->firstName = $field_value;
												break;
											case 'lastname':
											case 'last_name':
												$contact->lastName = $field_value;
												break;
											case 'textbox':
											case 'text':
												if ( empty($contact->name) && ( empty($contact->firstName) || empty($contact->lastName) ) ) {
													$this->attemptAdditionalNameExtraction($contact, $field_key, $field_values);
												}
												break;
											default:
												break;
										}
									}
								}

								if ( ! empty($contact->email) && null != $contact->email ) {
									// Set optin by owner on db sync.
									$contact->optinByOwner = true;
									$contact->opt_in       = true;
									// Convert to contactModel and push to the array.
									$contactModel = null;
									try {
										$contactModel = $this->convertToContactModel($contact);
										if ( ! empty($contactModel->getEmail()) ) {
											array_push($contactsArray, $contactModel);
										}
									} catch ( Exception $exception ) {
										DatadogManager::get_instance()->exception_handler($exception);
										continue;
									}
									if ( isset($limit) && count($contactsArray) >= $limit ) {
										break;
									}
								}
							}
						}
						if ( isset($limit) && count($contactsArray) >= $limit ) {
							break;
						}
					}
				}
				// Upsert the contacts.
				if ( ! empty($contactsArray) ) {
					return $contactsArray;
				}
			}
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
		return null;
	}
}
