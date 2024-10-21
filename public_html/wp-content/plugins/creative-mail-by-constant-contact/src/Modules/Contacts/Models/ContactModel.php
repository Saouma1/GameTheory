<?php

namespace CreativeMail\Modules\Contacts\Models;

use Exception;

class ContactModel {

	public $email;
	public $phone;
	public $companyName;
	public $name;
	public $firstName;
	public $lastName;
	public $optIn;
	public $optOut;
	public $optActionBy;
	public $contactAddresses;
	public $eventType;
	public $numberOfOrders;
	private $birthday;
	private $listId;

	public function __construct() {
	}

	public function setEmail( $email ) {
		if ( isset($email) && ! empty($email) ) {
			$this->email = $email;
		} else {
			throw new Exception('invalid value for email');
		}
	}

	public function getEmail() {
		return $this->email;
	}

	public function setPhone( $phone ) {
		$this->phone = $phone;
	}

	public function getPhone() {
		return $this->phone;
	}

	public function setCompanyName( $companyName ) {
		$this->companyName = $companyName;
	}

	public function getCompanyName() {
		return $this->companyName;
	}

	public function setName( $name ) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setFirstName( $firstName ) {
		$this->firstName = $firstName;
	}

	public function getFirstName() {
		return $this->firstName;
	}

	public function setLastName( $lastName ) {
		$this->lastName = $lastName;
	}

	public function getLastName() {
		return $this->lastName;
	}

	public function setOptIn( $optIn ) {
		$this->optIn = $optIn;
	}

	public function setOptOut( $optOut ) {
		$this->optOut = $optOut;
	}

	public function getOptIn() {
		return $this->optIn;
	}

	public function getOptOut() {
		return $this->optOut;
	}

	public function setOptActionBy( $optActionBy ) {
		$this->optActionBy = $optActionBy;
	}

	public function getOptActionBy() {
		return $this->optActionBy;
	}

	public function setNumberOfOrders( $numberOfOrders ) {
		$this->numberOfOrders = $numberOfOrders;
	}

	public function getNumberOfOrders() {
		return $this->numberOfOrders;
	}

	public function setContactAddress( ?ContactAddressModel $contactAddresses ) {
		$this->contactAddresses = $contactAddresses;
	}

	/**
	 * The address model for the contact.
	 *
	 * @return ContactAddressModel
	 */
	public function getContactAddress() {
		return $this->contactAddresses;
	}

	public function setEventType( $eventType ) {
		$this->eventType = $eventType;
	}

	public function getEventType() {
		return $this->eventType;
	}

	public function getBirthday() {
		return $this->birthday;
	}

	public function setBirthday( $birthday ) {
		$this->birthday = $birthday;
	}

	public function getListId() {
		return $this->listId;
	}

	public function setListId( $listId ) {
		$this->listId = $listId;
	}

	public function toArray() {
		$result = array(
			'email'            => $this->getEmail(),
			'phone'            => $this->getPhone(),
			'birthday'         => $this->getBirthday(),
			'company_name'     => $this->getCompanyName(),
			'name'             => $this->getName(),
			'first_name'       => $this->getFirstName(),
			'last_name'        => $this->getLastName(),
			'opt_in'           => $this->getOptIn(),
			'opt_out'          => $this->getOptOut(),
			'opt_action_by'    => $this->getOptActionBy(),
			'event_type'       => $this->getEventType(),
			'list_id'          => $this->getListId(),
			'number_of_orders' => $this->getNumberOfOrders(),
		);

		$address = $this->getContactAddress();
		if ( null != $address ) {
			$result['addresses'] = array( $address->toArray() );
		}

		return $result;
	}

	public function toJson() {
		return wp_json_encode($this->toArray());
	}
}
