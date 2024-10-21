<?php declare(strict_types = 1);

namespace CreativeMail\Modules\Contacts\Models;

final class ContactAddressModel {

	/**
	 * Stores the country code of the contact address.
	 *
	 * @var ?string
	 */
	public $countryCode;

	/**
	 * Stores the postal code of the contact address.
	 *
	 * @var ?string
	 */
	public $postalCode;

	/**
	 * Stores the state of the contact address.
	 *
	 * @var ?string
	 */
	public $state;

	/**
	 * Stores the state code of the contact address.
	 *
	 * @var ?string
	 */
	public $stateCode;

	/**
	 * Stores the address of the contact.
	 *
	 * @var ?string
	 */
	public $address;

	/**
	 * Stores the secondary address of the contact.
	 *
	 * @var ?string
	 */
	public $address2;

	/**
	 * Stores the city of the contact address.
	 *
	 * @var ?string
	 */
	public $city;

	/**
	 * Sets the country code of the contact address.
	 *
	 * @param ?string $countryCode The country code of the contact address.
	 *
	 * @return void
	 */
	public function setCountryCode( ?string $countryCode ): void {
		$this->countryCode = $countryCode;
	}

	/**
	 * Returns the country code of the contact address.
	 *
	 * @return ?string
	 */
	public function getCountryCode(): ?string {
		return $this->countryCode;
	}

	/**
	 * Set the postal code.
	 *
	 * @param ?string $postalCode The postal code.
	 *
	 * @return void
	 */
	public function setPostalCode( ?string $postalCode ): void {
		$this->postalCode = $postalCode;
	}

	/**
	 * Returns the postal code of the contact address.
	 *
	 * @return ?string
	 */
	public function getPostalCode(): ?string {
		return $this->postalCode;
	}

	/**
	 * Set the state.
	 *
	 * @param ?string $state The state.
	 *
	 * @return void
	 */
	public function setState( ?string $state ): void {
		$this->state = $state;
	}

	/**
	 * Returns the state of the contact address.
	 *
	 * @return ?string
	 */
	public function getState(): ?string {
		return $this->state;
	}

	/**
	 * Set the state code.
	 *
	 * @param ?string $stateCode The state code.
	 *
	 * @return void
	 */
	public function setStateCode( ?string $stateCode ): void {
		$this->stateCode = $stateCode;
	}

	/**
	 * Returns the state code of the contact address.
	 *
	 * @return ?string
	 */
	public function getStateCode(): ?string {
		return $this->stateCode;
	}

	/**
	 * Set the address.
	 *
	 * @param ?string $address The address.
	 *
	 * @return void
	 */
	public function setAddress( ?string $address ): void {
		$this->address = $address;
	}

	/**
	 * Returns the address of the contact.
	 *
	 * @return ?string
	 */
	public function getAddress(): ?string {
		return $this->address;
	}

	/**
	 * Set the secondary address.
	 *
	 * @param ?string $address2 The secondary address.
	 *
	 * @return void
	 */
	public function setAddress2( ?string $address2 ): void {
		$this->address2 = $address2;
	}

	/**
	 * Returns the secondary address of the contact.
	 *
	 * @return ?string
	 */
	public function getAddress2(): ?string {
		return $this->address2;
	}

	/**
	 * Set the city.
	 *
	 * @param ?string $city The city.
	 *
	 * @return void
	 */
	public function setCity( ?string $city ): void {
		$this->city = $city;
	}

	/**
	 * Returns the city of the contact address.
	 *
	 * @return ?string
	 */
	public function getCity(): ?string {
		return $this->city;
	}

	/**
	 * Returns the contact address as an array.
	 *
	 * @return array<string,string>
	 */
	public function toArray(): array {
		return array(
			'country_code' => $this->getCountryCode(),
			'state_code'   => $this->getStateCode(),
			'state'        => $this->getState(),
			'postal_code'  => $this->getPostalCode(),
			'address'      => $this->getAddress(),
			'address2'     => $this->getAddress2(),
			'city'         => $this->getCity(),
		);
	}
}
