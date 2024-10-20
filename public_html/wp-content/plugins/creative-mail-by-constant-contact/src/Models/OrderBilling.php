<?php

namespace CreativeMail\Models;

use CreativeMail\Modules\Contacts\Models\OptActionBy;

final class OrderBilling {

	/**
	 * Order Address Number 1
	 *
	 * @var string
	 */
	public $address_1;

	/**
	 * Order Address Number 2
	 *
	 * @var string
	 */
	public $address_2;

	/**
	 * Order city
	 *
	 * @var string
	 */
	public $city;

	/**
	 * Order company
	 *
	 * @var string
	 */
	public $company;

	/**
	 * Order country
	 *
	 * @var string
	 */
	public $country;

	/**
	 * Order email address
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Order customer first name
	 *
	 * @var string
	 */
	public $first_name;

	/**
	 * Variable to check is customer is first buyer
	 *
	 * @var bool
	 */
	public $is_first_time_buyer;

	/**
	 * Order customer last name
	 *
	 * @var string
	 */
	public $last_name;

	/**
	 * Order customer opt action by
	 *
	 * @var OptActionBy
	 */
	public $opt_action_by;

	/**
	 * Order customer opt action by
	 *
	 * @var bool|null
	 */
	public $opt_in;

	/**
	 * Order customer opt action by
	 *
	 * @var bool|null
	 */
	public $opt_out;

	/**
	 * Order payments details
	 *
	 * @var OrderBillingPaymentDetails
	 */
	public $payment_details;

	/**
	 * Order phone number
	 *
	 * @var string
	 */
	public $phone;

	/**
	 * Order shipping postcode
	 *
	 * @var string
	 */
	public $postcode;

	/**
	 * Order shipping details
	 *
	 * @var OrderBillingShipping
	 */
	public $shipping;

	/**
	 * Order Shipping state
	 *
	 * @var string
	 */
	public $state;
}
