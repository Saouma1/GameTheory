<?php


namespace CreativeMail\Modules\WooCommerce\Models;

use CreativeMail\Helpers\OptionsHelper;
use WC_Countries;

/**
 * Class WCStoreInformation
 */
class WCStoreInformation {
	/**
	 * The first customer address field.
	 *
	 * @var string
	 */
	public $address1;
	/**
	 * The second customer address field.
	 *
	 * @var string
	 */
	public $address2;
	/**
	 * The city of the customer.
	 *
	 * @var string
	 */
	public $city;
	/**
	 * The customer postcode.
	 *
	 * @var string
	 */
	public $postcode;
	/**
	 * The customer State.
	 *
	 * @var string
	 */
	public $state;
	/**
	 * The customer country.
	 *
	 * @var mixed
	 */
	public $country;
	/**
	 * The customer country code.
	 *
	 * @var string
	 */
	public $country_code;
	/**
	 * The customer currency.
	 *
	 * @var string
	 */
	public $currency;
	/**
	 * The customer currency symbol.
	 *
	 * @var string
	 */
	public $currency_symbol;
	/**
	 * The customer email from field.
	 *
	 * @var mixed|null
	 */
	public $email_from;
	/**
	 * The customer Email name.
	 *
	 * @var mixed|null
	 */
	public $email_name;

	/**
	 * WCStoreInformation constructor.
	 */
	public function __construct() {
		if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true) ) {
			$this->address1        = WC()->countries->get_base_address();
			$this->address2        = WC()->countries->get_base_address_2();
			$this->city            = WC()->countries->get_base_city();
			$this->postcode        = WC()->countries->get_base_postcode();
			$this->state           = WC()->countries->get_base_state();
			$this->country         = WC()->countries->get_countries()[ WC()->countries->get_base_country() ];
			$this->country_code    = WC()->countries->get_base_country();
			$this->currency_symbol = get_woocommerce_currency_symbol();
			$this->currency        = get_woocommerce_currency();
			$this->email_from      = apply_filters('woocommerce_email_from_address', get_option('woocommerce_email_from_address'));
			$this->email_name      = apply_filters('woocommerce_email_from_name', get_option('woocommerce_email_from_name'));
		}
	}
}
