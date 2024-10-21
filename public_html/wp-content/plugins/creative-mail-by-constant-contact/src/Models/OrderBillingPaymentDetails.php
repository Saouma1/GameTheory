<?php

namespace CreativeMail\Models;

final class OrderBillingPaymentDetails {

	/**
	 * Holds the payment method.
	 *
	 * @var string
	 */
	public $method_id;

	/**
	 * Holds the payment method title.
	 *
	 * @var string
	 */
	public $method_title;

	/**
	 * Checks if order has been paid or not.
	 *
	 * @var bool
	 */
	public $paid;

	/**
	 * OrderBillingPaymentDetails constructor.
	 *
	 * @param string $method_id   The payment method.
	 * @param string $method_title  The payment method title.
	 * @param bool   $paid The payment status.
	 */
	public function __construct( string $method_id, string $method_title, bool $paid ) {
		$this->method_id    = $method_id;
		$this->method_title = $method_title;
		$this->paid         = $paid;
	}
}
