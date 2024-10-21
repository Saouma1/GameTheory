<?php

namespace CreativeMail\Models;

use WC_Order;

final class OrderBillingShipping {

	/**
	 * Holds the first address.
	 *
	 * @var string
	 */
	protected $address_1;

	/**
	 * Holds the second address.
	 *
	 * @var string
	 */
	public $address_2;

	/**
	 * Holds the city.
	 *
	 * @var string
	 */
	public $city;

	/**
	 * Holds the company.
	 *
	 * @var string
	 */
	public $company;

	/**
	 * Holds the country.
	 *
	 * @var string
	 */
	public $country;

	/**
	 * Holds the first name.
	 *
	 * @var string
	 */
	public $first_name;

	/**
	 * Holds the last name.
	 *
	 * @var string
	 */
	protected $last_name;

	/**
	 * Holds the postcode.
	 *
	 * @var string
	 */
	public $postcode;

	/**
	 * Holds the shipping method used.
	 *
	 * @var string
	 */
	public $shipping_methods;

	/**
	 * Holds the state.
	 *
	 * @var string
	 */
	public $state;

	/**
	 * OrderBillingShipping constructor.
	 *
	 * @param WC_Order $woocommerce_order The WooCommerce order.
	 */
	public function __construct( WC_Order $woocommerce_order ) {
		$this->address_1        = $woocommerce_order->get_shipping_address_1();
		$this->address_2        = $woocommerce_order->get_shipping_address_2();
		$this->city             = $woocommerce_order->get_shipping_city();
		$this->company          = $woocommerce_order->get_shipping_company();
		$this->country          = $woocommerce_order->get_shipping_country();
		$this->first_name       = $woocommerce_order->get_shipping_first_name();
		$this->last_name        = $woocommerce_order->get_shipping_last_name();
		$this->shipping_methods = $woocommerce_order->get_shipping_method();
		$this->state            = $woocommerce_order->get_shipping_state();
		$this->postcode         = $woocommerce_order->get_shipping_postcode();
	}
}
