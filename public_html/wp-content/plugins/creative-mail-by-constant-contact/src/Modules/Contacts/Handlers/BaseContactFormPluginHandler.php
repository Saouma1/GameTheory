<?php

namespace CreativeMail\Modules\Contacts\Handlers;

use CreativeMail\Modules\Contacts\Services\ContactsSyncService;
use Exception;

abstract class BaseContactFormPluginHandler {

	private $contactSyncService;

	abstract public function convertToContactModel( $contactForm);
	abstract public function registerHooks();
	abstract public function unregisterHooks();
	abstract public function get_contacts( $limit = null);

	protected $birthdayFields  = array( 'birthday', 'date-of-birth', 'date_of_birth', 'birth_date', 'birth-date', 'birth date', 'birth day', 'date of birth' );
	protected $phoneFields     = array( 'phone', 'phone_number', 'telephone', 'tel', 'tel-number', 'tel_number', 'mobile_number', 'mobile number', 'phone number' );
	protected $emailFields     = array( 'your-email', 'email', 'e-mail', 'emailaddress', 'email_address', 'email address', 'email-address', 'e-mail address', 'UserEmailAddress' );
	protected $firstnameFields = array( 'firstname', 'first_name', 'name', 'your-name', 'first name', 'first-name', 'first', 'UserFirstName' );
	protected $lastnameFields  = array( 'lastname', 'last_name', 'last name', 'last-name', 'last', 'UserLastName' );
	protected $consentFields   = array( 'accept-this', 'acceptance', 'consent' );
	protected $cityFields      = array( 'your-city', 'city', 'UserCity' );
	protected $stateFields     = array( 'your-state', 'state', 'StateDropDown' );
	protected $countryFields   = array( 'your-country', 'country', 'CountryDropDown' );
	protected $zipFields       = array( 'your-zip', 'zip', 'zipcode', 'UserZip' );

	public function upsertContact( $model ) {
		if ( ! isset($model) ) {
			throw new Exception('No model provided');
		}

		$contactModel = null;
		if ( ! is_a($model, 'CreativeMail\Modules\Contacts\Models\ContactModel') ) {
			$contactModel = $this->convertToContactModel($model);
		} else {
			$contactModel = $model;
		}
		$this->contactSyncService->upsertContact($contactModel);
	}

	public function batchUpsertContacts( $models ) {
		if ( ! isset($models) ) {
			throw new Exception('No models provided');
		}

		$this->contactSyncService->upsertContacts($models);
	}

	protected function isNullOrEmpty( $value ) {
		return ! isset($value) && empty($value);
	}

	public function __construct() {
		$this->contactSyncService = new ContactsSyncService();
		$this->registerHooks();
	}

	public function __destruct() {
		$this->unregisterHooks();
	}
}
