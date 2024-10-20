<?php

namespace CreativeMail\Models;

use WC_Customer;

class CustomerResetPassword {

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
	 * Reset password URL string
	 *
	 * @var string
	 */
	public $reset_url;
}
