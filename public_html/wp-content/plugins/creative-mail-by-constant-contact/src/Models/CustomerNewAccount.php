<?php

namespace CreativeMail\Models;

use WC_Customer;

class CustomerNewAccount {

	/**
	 * Account URL string
	 *
	 * @var string
	 */
	public $account_url;
	/**
	 * Customer object
	 *
	 * @var WC_Customer
	 */
	public $customer;
	/**
	 * Customer ID
	 *
	 * @var int
	 */
	public $customer_id;
	/**
	 * Customer Password
	 *
	 * @var string
	 */
	public $generated_password;
	/**
	 * Salt created for the customer password
	 *
	 * @var string
	 */
	public $salt;
}
