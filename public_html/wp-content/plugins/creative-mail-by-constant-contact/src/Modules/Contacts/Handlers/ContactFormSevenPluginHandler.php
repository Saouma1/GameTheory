<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_CF7_EVENTTYPE', 'WordPress - Contact Form 7');

use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactFormSevenSubmission;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\ContactAddressModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use Exception;

class ContactFormSevenPluginHandler extends BaseContactFormPluginHandler {

	private function findValue( $data, $fieldOptions ) {
		foreach ( $fieldOptions as $fieldOption ) {
			$value = $data->get_posted_data($fieldOption);
			if ( isset($value) && ! empty($value) ) {
				return $value;
			}
		}

		return null;
	}

	private function findValueFromDb( $formData, $fieldOptions ) {
		foreach ( $fieldOptions as $fieldOption ) {
			if ( array_key_exists($fieldOption, $formData) ) {
				$value = $formData[ $fieldOption ];
				if ( ! empty($value) ) {
					return $value;
				}
			}
		}
		return null;
	}

	public function convertToContactModel( $contactForm ) {
		$contactForm = ContactFormSevenSubmission::get_instance(null, array( 'skip_mail' => true ));

		// Convert.
		$contactModel = new ContactModel();
		$email        = $this->findValue($contactForm, $this->emailFields);
		if ( ! empty($email) ) {
			$contactModel->setEmail($email);
		}

		$firstName = $this->findValue($contactForm, $this->firstnameFields);
		if ( ! empty($firstName) ) {
			$contactModel->setFirstName($firstName);
		}

		$lastName = $this->findValue($contactForm, $this->lastnameFields);
		if ( ! empty($lastName) ) {
			$contactModel->setLastName($lastName);
		}

		$phone = $this->findValue($contactForm, $this->phoneFields);
		if ( empty($phone) ) {
			$phone = $this->GetValueBySubstring($contactForm->get_posted_data(), $this->phoneFields);
		}
		if ( ! empty($phone) ) {
			$contactModel->setPhone($phone);
		}

		$birthday = $this->findValue($contactForm, $this->birthdayFields);
		if ( ! empty($birthday) ) {
			$contactModel->setBirthday($birthday);
		}

		$consent = $this->GetValueBySubstring($contactForm->get_posted_data(), $this->consentFields);
		if ( '1' === $consent ) {
			$contactModel->setOptIn(true);
			$contactModel->setOptOut(false);
			$contactModel->setOptActionBy(OptActionBy::VISITOR);
		}

		$contactAddress = $this->getContactAddressFromForm($contactForm);

		if ( ! empty($contactAddress) ) {
			$contactModel->setContactAddress($contactAddress);
		}

		$contactModel->setEventType(CE4WP_CF7_EVENTTYPE);

		return $contactModel;
	}

	public function getContactAddressFromForm( $contactForm ) {
		$contactAddress = new ContactAddressModel();

		if ( isset($contactForm) ) {
			$city = $this->findValue($contactForm, $this->cityFields);
			if ( ! empty($city) ) {
				$contactAddress->setCity($city);
			}

			$zip = $this->findValue($contactForm, $this->zipFields);
			if ( ! empty($zip) ) {
				$contactAddress->setPostalCode($zip);
			}

			$state = $this->findValue($contactForm, $this->stateFields);
			if ( ! empty($state) && ! empty($state[0]) ) {
				$contactAddress->setStateCode($state[0]);
			}

			$country = $this->findValue($contactForm, $this->countryFields);
			if ( ! empty($country) && ! empty($country[0]) ) {
				$contactAddress->setCountryCode($country[0]);
			}
		}
		return $contactAddress;
	}

	public function registerHooks() {
		add_action('wpcf7_mail_sent', array( $this, 'ceHandleContactFormSevenSubmit' ));
	}

	public function unregisterHooks() {
		remove_action('wpcf7_mail_sent', array( $this, 'ceHandleContactFormSevenSubmit' ));
	}

	public function get_contacts( $limit = null ) {
		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}

		// Relies on plugin => Contact Form CFDB7.
		if ( in_array('contact-form-cfdb7/contact-form-cfdb-7.php',
			apply_filters('active_plugins', get_option('active_plugins')),
			true)
		) {
			global $wpdb;

			$cfdb      = apply_filters('cfdb7_database', $wpdb);
			$cfdbtable = $cfdb->prefix . 'db7_forms';
			$cfdbQuery = "SELECT form_id, form_post_id, form_value FROM $cfdbtable";

			// Do we need to limit the number of results?
			if ( null != $limit ) {
				$cfdbQuery .= ' LIMIT %d';
				$cfdbQuery  = $cfdb->prepare($cfdbQuery, $limit);
			} else {
				$cfdbQuery = $cfdb->prepare($cfdbQuery);
			}

			$results       = $cfdb->get_results($cfdbQuery, OBJECT);
			$contactsArray = array();

			foreach ( $results as $formSubmission ) {
				$form_data    = unserialize($formSubmission->form_value);
				$contactModel = new ContactModel();
				$contactModel->setOptIn(true);
				$contactModel->setOptOut(false);
				$contactModel->setOptActionBy(OptActionBy::OWNER);

				try {
					$email = $this->findValueFromDb($form_data, $this->emailFields);
					if ( ! empty($email) ) {
						$contactModel->setEmail($email);
					}
					$firstname = $this->findValueFromDb($form_data, $this->firstnameFields);
					if ( ! empty($firstname) ) {
						$contactModel->setFirstName($firstname);
					}
					$lastname = $this->findValueFromDb($form_data, $this->lastnameFields);
					if ( ! empty($lastname) ) {
						$contactModel->setLastName($lastname);
					}
					$phone = $this->findValueFromDb($form_data, $this->phoneFields);
					if ( ! empty($phone) ) {
						$contactModel->setPhone($phone);
					}
					$birthday = $this->findValueFromDb($form_data, $this->birthdayFields);
					if ( ! empty($birthday) ) {
						$contactModel->setBirthday($birthday);
					}
				} catch ( Exception $exception ) {
					DatadogManager::get_instance()->exception_handler($exception);
					continue;
				}

				if ( ! empty($contactModel->getEmail()) ) {
					$contactModel->setEventType(CE4WP_CF7_EVENTTYPE);
					array_push($contactsArray, $contactModel);
				}
			}

			if ( ! empty($contactsArray) ) {
				return $contactsArray;
			}
		}

		return null;
	}

	public function ceHandleContactFormSevenSubmit( $contact_form ) {
		try {
			$this->upsertContact($this->convertToContactModel($contact_form));
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	public function __construct() {
		parent::__construct();
	}

	private function GetValueBySubstring( $form_values, $possible_values ) {
		foreach ( $form_values as $form_key => $form_value ) {
			foreach ( $possible_values as $possible_value ) {
				// If the name of the form_key contains the possible_value then we return its value.
				if ( mb_strpos(strtolower($form_key), $possible_value) !== false ) {
					return $form_value;
				}
			}
		}
	}
}
