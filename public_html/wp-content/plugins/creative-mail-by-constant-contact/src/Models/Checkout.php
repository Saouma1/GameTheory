<?php

namespace CreativeMail\Models;

class Checkout {

	/**
	 * Order currency
	 *
	 * @var string
	 */
	public $order_currency;
	/**
	 * Order Identifier
	 *
	 * @var int
	 */
	public $order_id;
	/**
	 * Order total
	 *
	 * @var float
	 */
	public $order_total;
	/**
	 * Recovery time
	 *
	 * @var string
	 */
	public $recovery_date;
	/**
	 * Universal Identifier
	 *
	 * @var string
	 */
	public $uuid;
}
