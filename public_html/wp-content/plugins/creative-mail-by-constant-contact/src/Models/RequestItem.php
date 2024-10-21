<?php

namespace CreativeMail\Models;

use WC_DateTime;

class RequestItem {

	/**
	 * Cart tax
	 *
	 * @var float
	 */
	public $cart_tax;
	/**
	 * Customer ID
	 *
	 * @var int
	 */
	public $customer_id;
	/**
	 * Customer IP address
	 *
	 * @var string
	 */
	public $customer_ip;
	/**
	 * Customer user agent
	 *
	 * @var string
	 */
	public $customer_user_agent;
	/**
	 * Date completed
	 *
	 * @var WC_DateTime|NULL
	 */
	public $date_completed;
	/**
	 * Date created
	 *
	 * @var WC_DateTime|NULL
	 */
	public $date_created;
	/**
	 * Date modified
	 *
	 * @var WC_DateTime|NULL
	 */
	public $date_modified;
	/**
	 * Discount total
	 *
	 * @var string
	 */
	public $discount_total;
	/**
	 * Notes
	 *
	 * @var string
	 */
	public $note;
	/**
	 * Order object
	 *
	 * @var Order
	 */
	public $order;
	/**
	 * Order ID
	 *
	 * @var int
	 */
	public $order_id;
	/**
	 * Order number
	 *
	 * @var string
	 */
	public $order_number;
	/**
	 * Order URL string
	 *
	 * @var string
	 */
	public $order_url;
	/**
	 * Shipping tax value
	 *
	 * @var string
	 */
	public $shipping_tax;
	/**
	 * Shipping total
	 *
	 * @var string
	 */
	public $shipping_total;
	/**
	 * Request status
	 *
	 * @var string
	 */
	public $status;
	/**
	 * Subtotal
	 *
	 * @var string
	 */
	public $subtotal;
	/**
	 * Total
	 *
	 * @var string
	 */
	public $total;
	/**
	 * Total tax
	 *
	 * @var string
	 */
	public $total_tax;
}
