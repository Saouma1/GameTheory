<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors Yith events
 */
class Commerce extends Listener {

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'woocommerce_order_status_processing', array( $this, 'on_payment' ), 10, 2 );
		add_filter( 'newfold_wp_data_module_cron_data_filter', array( $this, 'products_count' ) );
		add_filter( 'newfold_wp_data_module_cron_data_filter', array( $this, 'orders_count' ) );
		add_filter('woocommerce_before_cart', array( $this, 'site_cart_views'));
		add_filter('woocommerce_before_checkout_form', array( $this, 'checkout_views'));
		add_filter('woocommerce_thankyou', array( $this, 'thank_you_page'));
		add_filter( 'pre_update_option_nfd_ecommerce_captive_flow_razorpay', array( $this, 'razorpay_connection' ), 10, 2 );
		add_filter( 'pre_update_option_nfd_ecommerce_captive_flow_shippo', array( $this, 'shippo_connection' ), 10, 2 );
		add_filter( 'pre_update_option_nfd_ecommerce_captive_flow_stripe', array( $this, 'stripe_connection' ), 10, 2 );
		// Paypal Connection
		add_filter( 'pre_update_option_yith_ppwc_merchant_data_production', array( $this, 'paypal_connection' ), 10, 2 );
		add_filter('update_option_ewc4wp_sso_account_status', array($this, 'ecomdash_connected'));
	}

	/**
	 * On Payment, send data to Hiive
	 *
	 * @param  int  $order_id
	 * @param  \WC_Order  $order
	 *
	 * @return void
	 */
	public function on_payment( $order_id, \WC_Order $order ) {

		$data = array(
			'order_currency'       => $order->get_currency(),
			'order_total'          => $order->get_total(),
			'payment_method'       => $order->get_payment_method(),
			'payment_method_title' => $order->get_payment_method_title(),
		);

		$this->push( 'woocommerce_order_status_processing', $data );

	}

	/**
	 * Products Count
	 *
	 * @param  string  $data  Array of data to be sent to Hiive
	 *
	 * @return string Array of data
	 */
	public function products_count( $data ) {
		if ( ! isset( $data['meta'] ) ) {
			$data['meta'] = array();
		}
		$data['meta']['products_count'] = (int) wp_count_posts( 'product' )->publish;

		return $data;
	}

	/**
	 * Orders Count
	 *
	 * @param  string  $data  Array of data to be sent to Hiive
	 *
	 * @return string Array of data
	 */
	public function orders_count( $data ) {
		if ( ! isset( $data['meta'] ) ) {
			$data['meta'] = array();
		}
		$data['meta']['orders_count'] = (int) wp_count_posts( 'shop_order' )->publish;

		return $data;
	}

	/**
	 * Site Cart View, send data to Hiive
	 *
	 * @return void
	 */
	public function site_cart_views() { 
		if( WC()->cart->get_cart_contents_count() !== 0){
		$data = array( 
			"product_count" => WC()->cart->get_cart_contents_count(),
			"cart_total" 	=> floatval(WC()->cart->get_cart_contents_total()),
			"currency" 		=> get_woocommerce_currency(),
		); 
		
		$this->push(
			"site_cart_view",
			$data
		);
		}
	} 

	
	/**
	 * Checkout view, send data to Hiive
	 *
	 * @return void
	 */
	public function checkout_views() { 
		$data = array( 
			"product_count" 	=> WC()->cart->get_cart_contents_count(),
			"cart_total" 		=> floatval(WC()->cart->get_cart_contents_total()),
			"currency" 			=> get_woocommerce_currency(),
			"payment_method" 	=> WC()->payment_gateways()->get_available_payment_gateways()
		); 
		
		$this->push(
			"site_checkout_view",
			$data
		);
	}

	/**
	 * Thank you page, send data to Hiive
	 *
	 * @param  int  $order_id
	 * 
	 * @return void
	 */
	public function thank_you_page($order_id ) { 
		$order = wc_get_order( $order_id );
		$line_items = $order->get_items();

		// This loops over line items
		foreach ( $line_items as $item ) {
			$qty = $item['qty'];
		}
		$data = array( 
			"product_count" => $qty,
			"order_total" 	=> floatval($order->get_total()),
			"currency" 		=> get_woocommerce_currency(),
		);
		
		$this->push(
			"site_thank_you_view",
			$data
		);
	}

	/**
	 * Razorpay connected
	 *
	 * @param string $new_option New value of the razorpay_data_production option
	 * @param string $old_option Old value of the razorpay_data_production option
	 *
	 * @return string The new option value
	 */
	public function razorpay_connection( $new_option, $old_option ) {
		$url =  is_ssl() ? "https://" : "http://"; 
		$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$data = array( 
			"label_key" => "provider",
			"provider" 	=> "razorpay",
			"page" 		=> $url
		);
		if ( $new_option !== $old_option && ! empty( $new_option ) ) {	
			$this->push(
				"payment_connected",
				$data
			);
		}

		return $new_option;
	}

	/**
	 * Shippo connected
	 *
	 * @param string $new_option New value of the shippo_data option
	 * @param string $old_option Old value of the shippo_data option
	 *
	 * @return string The new option value
	 */
	public function shippo_connection( $new_option, $old_option ) {
		$url =  is_ssl() ? "https://" : "http://"; 
		$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$data = array( 
			"label_key" => "provider",
			"provider" 	=> "shippo",
			"page"		=> $url
		);
		if ( $new_option !== $old_option && ! empty( $new_option ) ) {	
			$this->push(
				"shpping_connected",
				$data
			);
		}

		return $new_option;
	}

	/**
	 * Stripe connected
	 *
	 * @param string $new_option New value of the stripe_data_production option
	 * @param string $old_option Old value of the stripe_data_production option
	 *
	 * @return string The new option value
	 */
	public function stripe_connection( $new_option, $old_option ) {
		$url =  is_ssl() ? "https://" : "http://"; 
		$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$data = array( 
			"label_key" => "provider",
			"provider" 	=> "stripe",
			"page" 		=> $url
		);
		if ( $new_option !== $old_option && ! empty( $new_option ) ) {	
			$this->push(
				"payment_connected",
				$data
			);
		}

		return $new_option;
	}

	/**
	 * PayPal connected
	 *
	 * @param string $new_option New value of the yith_ppwc_merchant_data_production option
	 * @param string $old_option Old value of the yith_ppwc_merchant_data_production option
	 *
	 * @return string The new option value
	 */
	public function paypal_connection( $new_option, $old_option ) {
		$url =  is_ssl() ? "https://" : "http://"; 
		$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$data = array( 
			"label_key" => "provider",
			"provider" 	=> "yith_paypal",
			"page" 		=> $url
		);
		if ( $new_option !== $old_option && ! empty( $new_option ) ) {	
			$this->push(
				"payment_connected",
				$data
			);
		}

		return $new_option;
	}
	
	/**
	 * Ecomdash connection, send data to Hiive
	 *
	 * @param string $new_option New value of the update_option_ewc4wp_sso_account_status option
	 * @param string $old_option Old value of the update_option_ewc4wp_sso_account_status option
	 *
	 * @return string The new option value
	 */
	public function ecomdash_connected($new_option, $old_option) {
		if ( $new_option !== $old_option && ! empty( $new_option ) && $new_option === 'connected' ) {
			$url =  is_ssl() ? "https://" : "http://";
			$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$data = array(
				"url"	=> $url
			);
			$this->push(
				"ecomdash_connected",
				$data
			);
    	}
		return $new_option;
	}
}
