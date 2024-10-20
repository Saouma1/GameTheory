<?php

namespace CreativeMail\Models;

/**
 * Class CartData
 */
final class CartData {

	/**
	 * Coupons array
	 *
	 * @var array<Coupon>
	 */
	public $coupons;

	/**
	 * Coupons currency
	 *
	 * @var string
	 */
	public $currency;

	/**
	 * Coupons currency symbol
	 *
	 * @var string
	 */
	public $currency_symbol;

	/**
	 * Cart items
	 *
	 * @var array<string,mixed>
	 */
	public $products;

	/**
	 * User data
	 *
	 * @var User
	 */
	public $user;

	/**
	 * Cart Shipping Total
	 *
	 * @var float
	 */
	public $shipping_total;

	/**
	 * Cart Shipping Taxes
	 *
	 * @var mixed
	 */
	public $shipping_taxes;

	/**
	 * Cart Data constructor.
	 *
	 * @param float $shipping_total Cart Shipping Total.
	 * @param mixed $shipping_taxes Cart Shipping Taxes.
	 */
	public function __construct( float $shipping_total, $shipping_taxes ) {
		$this->coupons        = array();
		$this->products       = array();
		$this->user           = new User();
		$this->shipping_total = $shipping_total;
		$this->shipping_taxes = $shipping_taxes;
	}

	/**
	 * Sets the user ID
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 */
	public function set_user_id( int $user_id ) {
		$this->user->id = $user_id;
	}

	/**
	 * Set the user's email address
	 *
	 * @param string $user_email User email address.
	 *
	 * @return void
	 */
	public function set_user_email( string $user_email ) {
		$this->user->email = $user_email;
	}

	/**
	 * Sets the user's first name.
	 *
	 * @param string $user_first_name User first name.
	 *
	 * @return void
	 */
	public function set_user_first_name( string $user_first_name ) {
		$this->user->first_name = $user_first_name;
	}

	/**
	 * Sets the User's last name
	 *
	 * @param string $user_last_name The User's last name.
	 *
	 * @return void
	 */
	public function set_user_last_name( string $user_last_name ) {
		$this->user->last_name = $user_last_name;
	}

	/**
	 * Sets the username of the user.
	 *
	 * @param string $user_username The username of the user.
	 *
	 * @return void
	 */
	public function set_user_username( string $user_username ) {
		$this->user->username = $user_username;
	}

	/**
	 * Sets the user Display Name from WordPress.
	 *
	 * @param string $user_display_name The user's display name.
	 *
	 * @return void
	 */
	public function set_user_display_name( string $user_display_name ) {
		$this->user->display_name = $user_display_name;
	}

	/**
	 * Sets the Array of Coupons
	 *
	 * @param array $products_data Array products data.
	 *
	 * @return void
	 */
	public function set_products_data( array $products_data ) {
		$this->products[] = $products_data;
	}

	/**
	 * Sets the coupons' data.
	 *
	 * @param array $coupons_data Array of coupons.
	 *
	 * @return void
	 */
	public function set_coupons_data( array $coupons_data ) {
		$this->coupons[] = $coupons_data;
	}

	/**
	 * Sets the currency.
	 *
	 * @param string $currency The currency.
	 *
	 * @return void
	 */
	public function set_currency( string $currency ) {
		$this->currency = $currency;
	}

	/**
	 * Sets the currency symbol.
	 *
	 * @param string $currency_symbol The currency symbol.
	 *
	 * @return void
	 */
	public function set_currency_symbol( string $currency_symbol ) {
		$this->currency_symbol = $currency_symbol;
	}
}
