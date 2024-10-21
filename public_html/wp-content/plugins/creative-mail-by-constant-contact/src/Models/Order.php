<?php

namespace CreativeMail\Models;

class Order {

	/**
	 * Order billing information
	 *
	 * @var OrderBilling
	 */
	public $billing;

	/**
	 * Currency string
	 *
	 * @var string
	 */
	public $currency;

	/**
	 * Currency symbol
	 *
	 * @var string
	 */
	public $currency_symbol;

	/**
	 * Order items
	 *
	 * @var array<OrderLineItem>
	 */
	public $line_items;

	/**
	 * Total of items in the order
	 *
	 * @var int|string
	 */
	public $total_line_items_quantity;
}
