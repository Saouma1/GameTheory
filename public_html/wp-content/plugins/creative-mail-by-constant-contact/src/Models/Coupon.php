<?php

namespace CreativeMail\Models;

final class Coupon {

	/**
	 * Holds the coupon code.
	 *
	 * @var string
	 */
	public $code;

	/**
	 * Holds the coupon amount.
	 *
	 * @var float
	 */
	public $amount;

	/**
	 * Holds the discount type.
	 *
	 * @var string
	 */
	public $discount_type;

	/**
	 * Holds the coupon description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Checks for Free Shipping.
	 *
	 * @var bool
	 */
	public $free_shipping;

	public function __construct(
		?string $code,
		?float $amount,
		?string $discount_type,
		?string $description,
		?bool $free_shipping
	) {
		$this->code          = $code;
		$this->amount        = $amount;
		$this->discount_type = $discount_type;
		$this->description   = $description;
		$this->free_shipping = $free_shipping;
	}
}
