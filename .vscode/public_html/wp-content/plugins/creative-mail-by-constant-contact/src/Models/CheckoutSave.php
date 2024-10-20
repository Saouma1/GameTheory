<?php

namespace CreativeMail\Models;

class CheckoutSave {

	/**
	 * Billing email address
	 *
	 * @var string|bool
	 */
	public $billing_email;
	/**
	 * Checkout data
	 *
	 * @var string|bool
	 */
	public $data;
	/**
	 * Date timestamp
	 *
	 * @var string
	 */
	public $timestamp;
	/**
	 * User ID
	 *
	 * @var int
	 */
	public $user_id;
	/**
	 * Universal Identifier
	 *
	 * @var string
	 */
	public $uuid;
}
