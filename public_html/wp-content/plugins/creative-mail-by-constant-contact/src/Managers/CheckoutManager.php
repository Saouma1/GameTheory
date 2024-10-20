<?php

namespace CreativeMail\Managers;

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Models\CartData;
use CreativeMail\Models\Checkout;
use CreativeMail\Models\CheckoutSave;
use CreativeMail\Models\Coupon;
use CreativeMail\Models\OrderBillingPaymentDetails;
use CreativeMail\Models\OrderLineItem;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use CreativeMail\Models\Order;
use CreativeMail\Models\OrderBilling;
use CreativeMail\Models\RequestItem;
use Exception;
use WC_Coupon;
use WC_Order;
use WC_Order_Refund;

/**
 * Class CheckoutManager
 *
 * @package CreativeMail\Managers
 */
final class CheckoutManager {

	/**
	 * Current checkout UUID.
	 *
	 * @var   string
	 * @since 1.3.0
	 */
	protected $checkout_uuid = '';

	/**
	 * Check if the checkout needs to return to the shop or not.
	 *
	 * @var bool
	 */
	protected $return_to_shop = false;

	const UPDATE_CHECKOUT_DATA     = 'update_checkout_data';
	const META_CHECKOUT_UUID       = 'ce4wp_checkout_uuid';
	const META_CHECKOUT_RECOVERED  = 'ce4wp_checkout_recovered';
	const CHECKOUT_UUID            = 'checkout_uuid';
	const NONCE                    = 'nonce';
	const EMAIL                    = 'email';
	const CHECKED                  = 'checked';
	const DOMAIN                   = 'creative-mail-by-constant-contact';
	const BILLING_EMAIL            = 'billing_email';
	const BILLING_EMAIL_NOTICE     = 'billing_email_notice';
	const BILLING_EMAIL_NO_CONSENT = 'billing_email_no_consent';
	const CHECKOUT_UUID_PARAM      = 'checkout_uuid = %s';
	const COUPONS                  = 'coupons';
	const SHIPPING_TOTAL           = 'shipping_total';
	const SHIPPING_TAXES           = 'shipping_taxes';
	const PRODUCT_ID               = 'product_id';
	const VARIATION_ID             = 'variation_id';
	const QUANTITY                 = 'quantity';
	const VARIATION                = 'variation';
	const USER_EMAIL               = 'user_email';
	const PRODUCTS                 = 'products';
	const CUSTOMER                 = 'customer';
	const DATETIME_ZERO            = '0000-00-00 00:00:00';

	/**
	 * Add hooks
	 *
	 * @since 1.3.0
	 */
	public function add_hooks(): void {
		// Check if woocommerce is active.
		if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true) ) {
			add_action('woocommerce_before_checkout_form', array( $this, 'enqueue_scripts' ));
			// Add checkout notice field.
			add_filter('woocommerce_form_field_ce4wp_notice', array( $this, 'add_email_usage_notice_field' ), 10, 4);
			add_filter('woocommerce_checkout_fields', array( $this, 'ce4wp_filter_checkout_fields' ));

			add_action('woocommerce_after_template_part', array( $this, 'save_or_clear_checkout_data' ), 10, 1);
			add_action('woocommerce_add_to_cart', array( $this, self::UPDATE_CHECKOUT_DATA ));
			add_action('woocommerce_cart_item_removed', array( $this, self::UPDATE_CHECKOUT_DATA ), 30, 0);
			add_action('woocommerce_cart_item_restored', array( $this, self::UPDATE_CHECKOUT_DATA ), 30, 0);
			add_action('woocommerce_cart_item_set_quantity', array( $this, self::UPDATE_CHECKOUT_DATA ), 20, 0);

			add_action('wp_ajax_ce4wp_abandoned_checkouts_capture_guest_checkout', array( $this, 'maybe_capture_guest_checkout' ));
			add_action('wp_ajax_nopriv_ce4wp_abandoned_checkouts_capture_guest_checkout', array( $this, 'maybe_capture_guest_checkout' ));

			add_action('wp_ajax_ce4wp_abandoned_checkouts_no_consent_checkout', array( $this, 'no_consent_checkout' ));
			add_action('wp_ajax_nopriv_ce4wp_abandoned_checkouts_no_consent_checkout', array( $this, 'no_consent_checkout' ));

			add_action('woocommerce_checkout_create_order', array( $this, 'clear_purchased_data' ), 10, 1);
			add_action('woocommerce_checkout_order_processed', array( $this, 'order_processed' ), 10, 1);
			add_action('woocommerce_order_status_completed', array( $this, 'order_completed' ), 10, 1);

			// Sanitize checkout UUID.
			$this->checkout_uuid  = filter_input(INPUT_GET, 'ce4wp-recover', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$this->return_to_shop = filter_input (INPUT_GET, 'ce4wp-return-to-shop', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

			if ( empty($this->checkout_uuid) && empty($this->return_to_shop) ) {
				return;
			}

			if ( ! empty($this->checkout_uuid) ) {
				add_action('wp_loaded', array( $this, 'recover_checkout' ));
			}

			if ( ! empty($this->return_to_shop) ) {
				add_action('wp_loaded', array( $this, 'return_to_shop' ));
			}
		}
	}

	/**
	 * Add custom field under billing_email.
	 *
	 * @param array $fields Checkout fields.
	 *
	 * @return array
	 *
	 * @since 1.3.0
	 */
	public function ce4wp_filter_checkout_fields( array $fields ): array {
		$fields['billing'][ self::BILLING_EMAIL_NOTICE ] = array(
			'type'     => 'ce4wp_notice',
			'required' => false,
			'class'    => array( 'form-row-wide' ),
			'clear'    => true,
			'priority' => $fields['billing']['billing_email']['priority'] + 0.5,
		);

		return $fields;
	}

	/**
	 * Add logic for ce4wp_notice field type
	 *
	 * @param string $field Field name.
	 * @param string $key Field key.
	 * @param array  $args Field arguments.
	 * @param string $value Field value.
	 *
	 * @return string
	 *
	 * @since 1.3.0
	 */
	public function add_email_usage_notice_field( $field, $key, $args, $value ): string {
		$field_html = '<label style="font-weight:400;">' . __( 'Your email and cart are saved so we can send you email reminders about this order.', self::DOMAIN ) . ' <a href="#" id="ce4wp_no_consent">' . __( 'No thanks', self::DOMAIN ) . '</a></label>';

		$container_class = esc_attr( implode( ' ', $args['class'] ) );
		$container_id    = esc_attr( $args['id'] ) . '_field';

		$after = ! empty( $args['clear'] ) ? '<div class="clear"></div>' : '';

		$field_container = '<p class="form-row %1$s" id="%2$s">%3$s</p>';

		return sprintf( $field_container, $container_class, $container_id, $field_html ) . $after;
	}

	/**
	 * Order has been completed
	 *
	 * @param int $order_id    The order id.
	 *
	 * @return void
	 * @since  1.3.0
	 */
	public function order_completed( int $order_id ): void {
		$this->update_checkout( $order_id, '/v1.0/checkout/order_completed' );
		$this->cleanup_old_checkouts( $order_id );
	}

	/**
	 * Order has been created and is processed
	 *
	 * @param int $order_id    Newly created order id.
	 *
	 * @return void
	 * @since  1.3.0
	 */
	public function order_processed( int $order_id ): void {
		$this->update_checkout( $order_id, '/v1.0/checkout/order_created' );
	}

	/**
	 * Cleanup previous checkouts in case old is still marked as abandoned.
	 *
	 * @param int $order_id    Woocommerce order id.
	 *
	 * @return void
	 * @since  1.3.3
	 */
	private function cleanup_old_checkouts( int $order_id ): void {
		$order = wc_get_order($order_id);

		if ( empty( $order ) ) {
			return;
		}

		try {
			$data = array();
			if ( ! is_bool($order) && is_a( $order, 'WC_Order' ) ) {
				$data = $this->get_checkout_uuid_by_email($order->get_billing_email());
			}
			foreach ( $data as $checkout_data ) {
				$endpoint = EnvironmentHelper::get_app_gateway_url( 'wordpress/v1.0/checkout/' ) . $checkout_data->checkout_uuid;
				$this->ce4wp_remote_delete($endpoint);
				CreativeMail::get_instance()->get_database_manager()->remove_checkout_data($checkout_data->checkout_uuid);
			}
		} catch ( Exception $e ) {
			DatadogManager::get_instance()->exception_handler($e);
		}
	}

	/**
	 * Update of checkout data in the external service.
	 *
	 * @param int    $order_id The order id.
	 * @param string $endpoint Endpoint to call.
	 *
	 * @since 1.3.0
	 */
	private function update_checkout( int $order_id, string $endpoint ): void {
		$requestItem = new Checkout();
		$order       = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			return;
		}

		if ( ! is_bool($order) ) {
			// Check if order had checkout uuid.
			$uuid = $order->get_meta(self::META_CHECKOUT_UUID, true);
			// Check if order is created with checkout meta.
			if ( empty($uuid) ) {
				return;
			}

			// Try to find recovery date from order metadata.
			$recovery_date = $order->get_meta(self::META_CHECKOUT_RECOVERED, true);
			// Remote post to CE4WP marking checkout as completed/created.
			$requestItem->uuid           = $uuid;
			$requestItem->order_id       = $order->get_id();
			$requestItem->order_total    = $order->get_total();
			$requestItem->order_currency = $order->get_currency();
			$requestItem->recovery_date  = ( empty($recovery_date) || self::DATETIME_ZERO === $recovery_date ) ? null : $recovery_date;
		}

		$endpoint = EnvironmentHelper::get_app_gateway_url( 'wordpress' ) . $endpoint;
		// Call remote endpoint to update.
		$this->ce4wp_remote_post( $requestItem, $endpoint );
	}

	/**
	 * Enqueue abandoned cart javascript files.
	 *
	 * @since 1.3.0
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_script( 'ce4wp-consent-checkout', CE4WP_PLUGIN_URL . 'assets/js/consent_checkout.js', array( 'wp-util' ), CE4WP_PLUGIN_VERSION, false );

		if ( is_user_logged_in() ) {
			return;
		}

		wp_enqueue_script( 'ce4wp-guest-checkout', CE4WP_PLUGIN_URL . 'assets/js/guest_checkout.js', array( 'wp-util' ), CE4WP_PLUGIN_VERSION, false );
	}

	/**
	 * AJAX handler for attempting to capture guest checkouts.
	 *
	 * @since 1.3.0
	 */
	public function maybe_capture_guest_checkout(): void {
		$email = null;
		$data  = filter_input_array( INPUT_POST, array(
			self::NONCE => FILTER_SANITIZE_STRING,
			self::EMAIL => FILTER_SANITIZE_EMAIL,
		) );

		if ( ! is_bool($data) ) {
			if ( empty( $data[ self::NONCE ] ) || ! wp_verify_nonce( $data[ self::NONCE ], 'woocommerce-process_checkout' ) ) {
				wp_send_json_error( esc_html__( 'Invalid nonce.', self::DOMAIN ) );
			}
			$email = filter_var( $data[ self::EMAIL ], FILTER_VALIDATE_EMAIL );
		}

		if ( ! $email ) {
			wp_send_json_error( esc_html__( 'Invalid email.', self::DOMAIN ) );
		}

		WC()->session->set( self::BILLING_EMAIL, $email );
		$this->save_checkout_data( $email, true);

		wp_send_json_success();
	}

	/**
	 * AJAX handler for opt out on abandoned cart.
	 *
	 * @since 1.3.0
	 */
	public function no_consent_checkout(): void {
		$data = filter_input_array(INPUT_POST, array(
			self::NONCE => FILTER_SANITIZE_STRING,
		));

		if ( ! is_bool($data) ) {
			if ( empty($data[ self::NONCE ]) || ! wp_verify_nonce($data[ self::NONCE ], 'woocommerce-process_checkout') ) {
				wp_send_json_error(esc_html__('Invalid nonce.', self::DOMAIN));
			}
		}

		// Save no consent on session.
		WC()->session->set( self::BILLING_EMAIL_NO_CONSENT, true);

		$checkout_id = WC()->session->get( self::CHECKOUT_UUID );
		if ( empty($checkout_id) ) {
			wp_send_json_success();
		}

		$endpoint = EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/checkout/') . ( ! is_array($checkout_id) ? $checkout_id : '' );
		$this->ce4wp_remote_delete( $endpoint );
		CreativeMail::get_instance()->get_database_manager()->change_checkout_consent( $checkout_id, false );

		wp_send_json_success();
	}

	/**
	 * Either call an update of checkout data which will be saved or remove checkout data based on what template we arrive at.
	 *
	 * @param string $template_name Current template file name.
	 *
	 * @since 1.3.0
	 */
	public function save_or_clear_checkout_data( string $template_name ): void {
		// If checkout page displayed, save checkout data.
		if ( 'checkout/form-checkout.php' === $template_name ) {
			$this->save_checkout_data();
		}
	}

	/**
	 * Helper function to update current checkout session data in db.
	 *
	 * Used to strip unneeded params from callbacks.
	 *
	 * @since 1.3.0
	 */
	public function update_checkout_data(): void {
		$this->save_checkout_data();
	}

	/**
	 * Helper function to retrieve checkout contents based on checkout UUID.
	 *
	 * @param string $uuid Checkout UUID.
	 *
	 * @return mixed Checkout contents.
	 *
	 * @since 1.3.0
	 */
	private function get_checkout_contents( string $uuid ) {
		$checkout = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( 'checkout_contents', self::CHECKOUT_UUID_PARAM, array( $uuid ) );

		if ( empty( $checkout ) ) {
			return array();
		}

		return maybe_unserialize( array_shift( $checkout )->checkout_contents );
	}

	/**
	 * Helper function to retrieve checkout recovery date based on checkout UUID.
	 *
	 * @param string $uuid Checkout UUID.
	 *
	 * @return string|null Checkout recovery date if exists, else null.
	 *
	 * @since 1.3.0
	 */
	private function get_checkout_recovery_date( string $uuid ): ?string {
		$checkout = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( 'checkout_recovered', self::CHECKOUT_UUID_PARAM, array( $uuid ) );

		return ( empty( $checkout ) ? null : array_shift( $checkout )->checkout_recovered );
	}

	/**
	 * Helper function to retrieve checkout UUID for current user.
	 *
	 * @since 1.3.0
	 *
	 * @return string Checkout UUID if exists, else empty string.
	 */
	private function get_checkout_uuid_by_user(): string {
		$checkout = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( self::CHECKOUT_UUID, 'user_id = %d', array( get_current_user_id() ) );
		return ( empty( $checkout ) ? '' : array_shift( $checkout )->checkout_uuid );
	}

	/**
	 * Helper function to retrieve checkout UUID for email address.
	 *
	 * @param ?string $email_address Email address to retrieve checkout UUID for.
	 *
	 * @since 1.3.3
	 *
	 * @return array List of checkout UUIDs if exists, else empty string.
	 */
	private function get_checkout_uuid_by_email( $email_address ): array {
		return CreativeMail::get_instance()->get_database_manager()->get_checkout_data( self::CHECKOUT_UUID, 'user_email = %s', array( $email_address ) );
	}

	/**
	 * Save current checkout data to db.
	 *
	 * @param string  $billing_email Manually set customer billing email if provided.
	 * @param boolean $is_checkout Manually mark current page as checkout if necessary (e.g., coming from ajax callback).
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	protected function save_checkout_data( string $billing_email = '', bool $is_checkout = false ): void {
		// Get current user email.
		$session_customer      = WC()->session->get( self::CUSTOMER );
		$session_billing_email = is_array( $session_customer ) && key_exists( self::EMAIL, $session_customer ) ? $session_customer[ self::EMAIL ] : '';

		if ( empty($billing_email) ) {
			$billing_email = $session_billing_email;
		}
		if ( empty($billing_email) && isset($_POST['post_data']) && isset($_POST['security'])
			&& wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'update-order-review') ) {
			$post_data_array = rawurldecode(sanitize_text_field(wp_unslash($_POST['post_data'])));
			$post_data_array = explode('&', $post_data_array);
			foreach ( $post_data_array as $post_data ) {
				$post_data_value = explode('=', $post_data);
				if ( 'billing_email' == $post_data_value[0] ) {
					$billing_email = $post_data_value[1];
					break;
				}
			}
		}

		if ( empty($billing_email) ) {
			$billing_email = WC()->session->get( self::BILLING_EMAIL );
		}

		if ( ! $is_checkout ) {
			$is_checkout = is_checkout();
		}

		$uuid = WC()->session->get( self::CHECKOUT_UUID );

		if ( empty( $billing_email ) ) {
			return;
		}

		$has_no_consent = (bool) WC()->session->get( self::BILLING_EMAIL_NO_CONSENT );

		if ( true === $has_no_consent ) {
			return;
		}

		// Check for existing checkout session.
		if ( ! $uuid ) {
			// Only create session if cart is not empty.
			// This is to avoid re-creating checkout UUID during checkout process.
			if ( $is_checkout && empty( WC()->cart->get_cart() ) ) {
				return;
			}
			// Retrieve existing checkout UUID for registered users only.
			if ( is_user_logged_in() ) {
				$existing_uuid = $this->get_checkout_uuid_by_user();
			}
			// Only create session if currently on checkout page or if current user has an existing session saved.
			if ( ! $is_checkout && empty( $existing_uuid ) ) {
				return;
			}

			$uuid = isset( $existing_uuid ) && ! empty( $existing_uuid ) ? $existing_uuid : wp_generate_uuid4();
			WC()->session->set( 'checkout_uuid', $uuid );
		}

		$current_time = current_time( 'mysql', 1 );
		$user_id      = get_current_user_id();

		$cart_products  = WC()->cart->get_cart();
		$cart_coupons   = WC()->cart->get_applied_coupons();
		$shipping_total = WC()->cart->get_shipping_total();
		$shipping_taxes = WC()->cart->get_shipping_taxes();
		$data           = new CartData( $shipping_total, $shipping_taxes );

		$checkout_content = array(
			self::PRODUCTS       => array_values( $cart_products ),
			self::COUPONS        => $cart_coupons,
			self::SHIPPING_TOTAL => $shipping_total,
			self::SHIPPING_TAXES => $shipping_taxes,
		);

		CreativeMail::get_instance()->get_database_manager()->upsert_checkout( $uuid, $user_id, $billing_email, $checkout_content, $current_time );

		// Remote post to CE4WP create or update cart if email is provided.
		$requestItem                = new CheckoutSave();
		$requestItem->data          = wp_json_encode( $this->get_cart_data_for_endpoint( $data, $cart_products, $cart_coupons ) );
		$requestItem->uuid          = ! is_array($uuid) ? $uuid : '';
		$requestItem->user_id       = $user_id;
		$requestItem->billing_email = $billing_email;
		$requestItem->timestamp     = (string) strtotime( $current_time );
		$endpoint                   = EnvironmentHelper::get_app_gateway_url( 'wordpress/v1.0/checkout/upsert' );

		$consent = CreativeMail::get_instance()->get_database_manager()->has_checkout_consent( $uuid );
		if ( $consent ) {
			$this->ce4wp_remote_post($requestItem, $endpoint);
		}
	}

	/**
	 * Get cart object with data for each product and coupon.
	 *
	 * @param CartData $data Cart data object to receive products information.
	 * @param array    $cart_products List of products in cart.
	 * @param array    $cart_coupons List of coupons in cart.
	 *
	 * @return CartData
	 *
	 * @since 1.3.0
	 */
	private function get_cart_data_for_endpoint( CartData $data, $cart_products, $cart_coupons ): CartData {
		$data->set_currency_symbol( get_woocommerce_currency_symbol() );
		$data->set_currency( get_woocommerce_currency() );

		try {
			// Get user first and last name of available.
			$current_user = wp_get_current_user();
			if ( $current_user->exists() ) {
				$data->set_user_id($current_user->ID);
				$data->set_user_username( $current_user->user_login );
				$data->set_user_display_name( $current_user->display_name );
				$data->set_user_first_name( $current_user->user_firstname );
				$data->set_user_last_name( $current_user->user_lastname );
				$data->set_user_email($current_user->user_email);
			}

			foreach ( $cart_products as $value ) {
				$products_data = $this->format_product_data( $value );
				$data->set_products_data( $products_data );
			}

			foreach ( $cart_coupons as $coupon_code ) {
				$coupon_id = wc_get_coupon_id_by_code( $coupon_code );
				if ( $coupon_id ) {
					$wooCommerceCoupon = new WC_Coupon( $coupon_id );
					$coupon_data       = array(
						new Coupon(
							$wooCommerceCoupon->get_code(),
							$wooCommerceCoupon->get_amount(),
							$wooCommerceCoupon->get_discount_type(),
							$wooCommerceCoupon->get_description(),
							$wooCommerceCoupon->get_free_shipping()
						),
					);
					$data->set_coupons_data( $coupon_data );
				}
			}
		} catch ( Exception $e ) {
			DatadogManager::get_instance()->exception_handler( $e );
		}

		return $data;
	}

	/**
	 * Remove current checkout session data from db upon successful order submission.
	 *
	 * @param WC_Order $order Newly created order object.
	 *
	 * @return void
	 *
	 * @since 1.3.0
	 */
	public function clear_purchased_data( WC_Order $order ): void {
		$checkout_id = WC()->session->get( self::CHECKOUT_UUID );

		if ( empty( $checkout_id ) ) {
			return;
		}

		$order->update_meta_data( self::META_CHECKOUT_UUID, $checkout_id );

		// Get the recovery date if recovered.
		$recovery_date = $this->get_checkout_recovery_date( ! is_array($checkout_id) ? $checkout_id : '' );

		if ( ! empty( $recovery_date ) && self::DATETIME_ZERO !== $recovery_date ) {
			$order->update_meta_data( self::META_CHECKOUT_RECOVERED, $recovery_date );
		}

		CreativeMail::get_instance()->get_database_manager()->remove_checkout_data( $checkout_id );
		WC()->session->__unset(  self::CHECKOUT_UUID );
	}

	/**
	 * Recovery saved checkout from UUID.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function recover_checkout(): void {
		// Set checkout session UUID.
		WC()->session->set( self::CHECKOUT_UUID, $this->checkout_uuid );
		// Clear current checkout contents.
		WC()->cart->empty_cart();
		// Get saved checkout contents.
		$checkout_contents = $this->get_checkout_contents( $this->checkout_uuid );

		if ( empty( $checkout_contents ) ) {
			return;
		}

		// Mark checkout as recovered.
		CreativeMail::get_instance()->get_database_manager()->mark_checkout_recovered( $this->checkout_uuid );
		// Recover saved products.
		$this->recover_products( $checkout_contents[ self::PRODUCTS ] );
		// Apply coupons.
		foreach ( $checkout_contents[ self::COUPONS ] as $coupon ) {
			WC()->cart->apply_coupon( $coupon );
		}
		// Maybe recover checkout email.
		$this->maybe_recover_checkout_email();
		// Update totals.
		WC()->cart->calculate_totals();
		// Redirect to check out page.
		wp_safe_redirect( wc_get_page_permalink( 'cart' ) );

		exit();
	}

	/**
	 * Safely returns to shop Page.
	 *
	 * @return void
	 */
	public function return_to_shop(): void {
		wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
		exit();
	}


	/**
	 * Recover checkout email address if guest user and no email is set.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	protected function maybe_recover_checkout_email() : void {
		$checkout_email = CreativeMail::get_instance()->get_database_manager()->get_checkout_data( self::USER_EMAIL, self::CHECKOUT_UUID_PARAM, array( $this->checkout_uuid ) );
		$checkout_email = empty( $checkout_email ) ? '' : array_shift( $checkout_email )->user_email;

		if ( is_user_logged_in() || ! empty( WC()->session->get( self::BILLING_EMAIL ) ) || empty( $checkout_email ) ) {
			return;
		}

		WC()->session->set( self::BILLING_EMAIL, $checkout_email );
		WC()->customer->set_billing_email( $checkout_email );
	}

	/**
	 * Recover products from saved checkout data.
	 *
	 * @param array $products Array of product data.
	 *
	 * @throws Exception If product is not found.
	 *
	 * @since 1.3.0
	 */
	protected function recover_products( array $products ) {
		if ( empty( $products ) ) {
			return;
		}

		// Programmatically add each product to cart.
		$products_added = array();
		foreach ( $products as $product ) {
			$added = WC()->cart->add_to_cart(
				$product[ self::PRODUCT_ID ],
				$product[ self::QUANTITY ],
				empty( $product[ self::VARIATION_ID ] ) ? 0 : $product[ self::VARIATION_ID ],
				empty( $product[ self::VARIATION ] ) ? array() : $product[ self::VARIATION ]
			);
			if ( false !== $added ) {
				$products_added[ ( empty( $product[ self::VARIATION_ID ] ) ? $product[ self::PRODUCT_ID ] : $product[ self::VARIATION_ID ] ) ] = $product[ self::QUANTITY ];
			}
		}

		// Add product notices.
		if ( 0 < count( $products_added ) ) {
			wc_add_to_cart_message( $products_added );
		}
		if ( count( $products ) > count( $products_added ) ) {
			wc_add_notice(
				sprintf(
				/* translators: %d item count */
					_n(
						'%d item from your previous order is currently unavailable and could not be added to your cart.',
						'%d items from your previous order are currently unavailable and could not be added to your cart.',
						( count( $products ) - count( $products_added ) ),
						self::DOMAIN
					),
					( count( $products ) - count( $products_added ) )
				),
				'error'
			);
		}
	}

	/**
	 * Sends the remote request to the Creative Mail API.
	 *
	 * @param object $requestItem The request item.
	 * @param string $endpoint The endpoint.
	 *
	 * @return void
	 */
	private function ce4wp_remote_post( object $requestItem, string $endpoint ): void {
		try {
			// Check if abandoned cart email is managed by creative mail.
			$enabled = CreativeMail::get_instance()->get_email_manager()->is_email_managed( 'cart_abandoned_ce4wp' );
			if ( $enabled ) {
				wp_remote_post(
					$endpoint, array(
						'method'  => 'POST',
						'timeout' => 10,
						'headers' => array(
							'x-account-id' => OptionsHelper::get_connected_account_id(),
							'x-api-key'    => OptionsHelper::get_instance_api_key(),
							'content-type' => 'application/json',
						),
						'body'    => wp_json_encode( $requestItem ),
					)
				);
			}
		} catch ( Exception $e ) {
			DatadogManager::get_instance()->exception_handler( $e );
		}
	}

	/**
	 * Endpoint to delete specific abandoned cart.
	 *
	 * @param string $endpoint The endpoint.
	 *
	 * @return void
	 */
	private function ce4wp_remote_delete( string $endpoint ): void {
		try {
			wp_remote_request( $endpoint,
				array(
					'method'  => 'DELETE',
					'headers' => array(
						'x-account-id' => OptionsHelper::get_connected_account_id(),
						'x-api-key'    => OptionsHelper::get_instance_api_key(),
					),
				)
			);
		} catch ( Exception $e ) {
			DatadogManager::get_instance()->exception_handler( $e );
		}
	}

	/**
	 * Get the OptActionBy value
	 *
	 * @param mixed $products_detail The products detail.
	 *
	 * @return int
	 */
	private function get_opt_action_by( $products_detail ) {
		return OptActionBy::VISITOR;
	}

	/**
	 * Get the OptActionIn value from checkbox
	 *
	 * @param array $products_detail The products detail.
	 *
	 * @return mixed|null
	 */
	private function get_opt_in_checkbox_value( array $products_detail ) {
		if ( ! empty( $products_detail['ce4wp_checkout_consent'] ) ) {
			// This value appears to be in array.
			return $products_detail['ce4wp_checkout_consent'][0];
		}

		return null;
	}

	/**
	 * Returns the OptActionIn value.
	 *
	 * @param array $products_detail The products detail.
	 *
	 * @return bool|null
	 */
	private function get_opt_in( $products_detail ) {
		$checkbox_value = $this->get_opt_in_checkbox_value( $products_detail );

		if ( $checkbox_value ) {
			return true;
		}

		return null;
	}

	/**
	 * Returns if it's an OptOut value.
	 *
	 * @param mixed $products_detail The products detail.
	 *
	 * @return bool|null
	 */
	private function get_opt_out( $products_detail ) {
		$checkbox_value = $this->get_opt_in_checkbox_value( $products_detail );

		if ( ! $checkbox_value ) {
			return true;
		}
		return null;
	}

	/**
	 * Adds the Order Completed WC Hooks.
	 *
	 * @return void
	 */
	public function add_order_completed_wc_hooks(): void {
		add_action( 'woocommerce_order_status_completed', array( $this, 'order_completed_trigger_wc_hook' ), 10, 1 );
	}

	/**
	 * Hook to trigger the WC Order Completed event and send the requested data to CE.
	 *
	 * @param bool|WC_Order|WC_Order_Refund $order_id The order id or instance.
	 *
	 * @return void
	 */
	public function order_completed_trigger_wc_hook( $order_id ): void {
		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {
			return;
		}

		$endpoint        = '/v1.0/wc/order_completed';
		$decimal_point   = 2;
		$products_detail = get_post_meta($order_id);
		$all_orders      = wc_get_orders( array( 'email' => ( is_object($order) && is_a($order, 'WC_Order') ? $order->get_billing_email() : '' ) ) );

		// General Info.
		$requestItem   = new RequestItem();
		$order_model   = new Order();
		$order_billing = new OrderBilling();

		if ( ! is_bool($order) && is_a( $order, 'WC_Order' ) ) {
			$requestItem->order_id            = $order->get_id();
			$requestItem->order_number        = $order->get_order_number();
			$requestItem->date_created        = $order->get_date_created();
			$requestItem->date_modified       = $order->get_date_modified();
			$requestItem->date_completed      = $order->get_date_completed();
			$requestItem->status              = $order->get_status();
			$requestItem->order_url           = $order->get_checkout_order_received_url();
			$requestItem->note                = $order->get_customer_note();
			$requestItem->customer_ip         = $order->get_customer_ip_address();
			$requestItem->customer_user_agent = $order->get_customer_user_agent();
			$requestItem->customer_id         = $order->get_user_id();

			// Order Billing.
			$order_billing->email         = $order->get_billing_email();
			$order_billing->opt_action_by = $this->get_opt_action_by( $products_detail );
			$order_billing->opt_in        = $this->get_opt_in( $products_detail );
			$order_billing->opt_out       = $this->get_opt_out( $products_detail );

			$order_billing->first_name          = $order->get_billing_first_name();
			$order_billing->last_name           = $order->get_billing_last_name();
			$order_billing->is_first_time_buyer = count( is_array($all_orders) ? $all_orders : array( '' ) ) <= 1;
			$order_billing->company             = $order->get_billing_company();
			$order_billing->address_1           = $order->get_billing_address_1();
			$order_billing->address_2           = $order->get_billing_address_2();
			$order_billing->city                = $order->get_billing_city();
			$order_billing->state               = $order->get_billing_state();
			$order_billing->postcode            = $order->get_billing_postcode();
			$order_billing->country             = $order->get_billing_country();
			$order_billing->email               = $order->get_billing_email();
			$order_billing->phone               = $order->get_billing_phone();
			$order_billing->shipping            = array(
				'first_name'       => $order->get_shipping_first_name(),
				'last_name'        => $order->get_shipping_last_name(),
				'company'          => $order->get_shipping_company(),
				'address_1'        => $order->get_shipping_address_1(),
				'address_2'        => $order->get_shipping_address_2(),
				'city'             => $order->get_shipping_city(),
				'state'            => $order->get_shipping_state(),
				'postcode'         => $order->get_shipping_postcode(),
				'country'          => $order->get_shipping_country(),
				'shipping_methods' => $order->get_shipping_method(),
			);
			$order_billing->payment_details     = new OrderBillingPaymentDetails(
				$order->get_payment_method(),
				$order->get_payment_method_title(),
				! is_null($order->get_date_paid())
			);

			// Order Currency and Total Info.
			$requestItem->total           = wc_format_decimal( $order->get_total(), $decimal_point );
			$requestItem->subtotal        = wc_format_decimal( $order->get_subtotal(), $decimal_point );
			$requestItem->total_tax       = wc_format_decimal( $order->get_total_tax(), $decimal_point );
			$requestItem->shipping_total  = wc_format_decimal( $order->get_shipping_total(), $decimal_point );
			$requestItem->cart_tax        = (float) wc_format_decimal( $order->get_cart_tax(), $decimal_point );
			$requestItem->shipping_tax    = wc_format_decimal( $order->get_shipping_tax(), $decimal_point );
			$requestItem->discount_total  = wc_format_decimal( $order->get_total_discount(), $decimal_point );
			$order_model->currency_symbol = get_woocommerce_currency_symbol();
			$order_model->currency        = $order->get_currency();

			// Order Products Info.
			$order_model->total_line_items_quantity = $order->get_item_count();

			// Line Items / Products array for the expected endpoint.
			foreach ( $order->get_items() as $itemsKey => $item ) {
				// @phpstan-ignore-next-line
				$product = $item->get_product();

				if ( empty( $product ) ) {
					continue;
				}

				$item_meta = $item->get_formatted_meta_data();

				foreach ( $item_meta as $key => $values ) {
					$item_meta[ $key ]->label = $values->display_key;
					unset( $item_meta[ $key ]->display_key );
					unset( $item_meta[ $key ]->display_value );
				}

				try {
					$product_data   = array(
						'images'    => array(),
						'downloads' => array(),
					);
					$attachment_ids = $product->get_gallery_image_ids();
					foreach ( $attachment_ids as $attachment_id ) {
						$product_data['images'][] = wp_get_attachment_url( $attachment_id );
					}

					$product_data['on_sale']       = $product->is_on_sale();
					$product_data['sale_price']    = $product->get_sale_price();
					$product_data['regular_price'] = $product->get_regular_price();

					if ( $product->is_downloadable() ) {
						// @phpstan-ignore-next-line
						$item_downloads = $item->get_item_downloads();
						foreach ( $item_downloads as $item_download ) {
							$product_data['downloads'][] = array(
								'line_item_id'            => $item->get_id(),
								// @phpstan-ignore-next-line
								'product_id'              => $item->get_product_id(),
								'download_url'            => $item_download['download_url'],
								'download_file'           => $item_download['file'],
								'download_name'           => $item_download['name'],
								'download_id'             => $item_download['id'],
								'downloads_remaining'     => $item_download['downloads_remaining'],
								'download_access_expires' => wc_format_datetime( $item_download['access_expires'], 'U' ),
								'download_limit'          => $product->get_download_limit(),
								'download_expiry'         => $product->get_download_expiry(),
							);
						}
					}
				} catch ( Exception $ex ) {
					DatadogManager::get_instance()->exception_handler( $ex );
				}

				$src      = wc_placeholder_img_src();
				$image_id = $product->get_image_id();

				if ( $image_id ) {
					$image_src   = wp_get_attachment_image_src( $image_id, 'full' );
					list( $src ) = is_array($image_src) ? $image_src : array( '' );
				}

				$order_model->line_items[] = new OrderLineItem(
					$item,
					$decimal_point,
					$order,
					$item_meta,
					$src,
					$product_data,
					$product
				);
			}
		}
		$order_model->billing = $order_billing;
		$requestItem->order   = $order_model;

		$endpoint = EnvironmentHelper::get_app_gateway_url( 'wordpress' ) . $endpoint;
		try {
			wp_remote_post(
				$endpoint, array(
					'method'  => 'POST',
					'timeout' => 10,
					'headers' => array(
						'x-account-id' => OptionsHelper::get_connected_account_id(),
						'x-api-key'    => OptionsHelper::get_instance_api_key(),
						'content-type' => 'application/json',
					),
					'body'    => wp_json_encode( $requestItem ),
				)
			);
		} catch ( Exception $e ) {
			DatadogManager::get_instance()->exception_handler( $e );
		}
	}

	/**
	 * Formats de products raw data into a more readable format.
	 *
	 * @param mixed[] $product_raw_data The raw data of the product.
	 *
	 * @return array
	 */
	private function format_product_data( $product_raw_data ) {
		$decimal_point = 2;
		$product       = array_key_exists( 'data', $product_raw_data )
			? $product_raw_data['data']
			: wc_get_product( $product_raw_data[ self::PRODUCT_ID ] );

		$product_id     = $product->get_id();
		$attachment_ids = $product->get_gallery_image_ids();
		$product_data   = array(
			'images' => array(),
		);

		foreach ( $attachment_ids as $attachment_id ) {
			$product_data['images'][] = wp_get_attachment_url( $attachment_id );
		}

		$product_data['on_sale']       = $product->is_on_sale();
		$product_data['sale_price']    = $product->get_sale_price();
		$product_data['regular_price'] = $product->get_regular_price();
		$src                           = wc_placeholder_img_src();
		$image_id                      = $product->get_image_id();

		if ( $image_id ) {
			$image_src   = wp_get_attachment_image_src( $image_id, 'full' );
			list( $src ) = ( ! is_bool($image_src) ? $image_src : array( '' ) );
		}

		$line_subtotal     = empty( $product_raw_data['line_subtotal'] ) ? 0 : $product_raw_data['line_subtotal'];
		$line_subtotal_tax = empty( $product_raw_data['line_subtotal_tax'] ) ? 0 : $product_raw_data['line_subtotal_tax'];
		$line_total        = empty( $product_raw_data['line_total'] ) ? 0 : $product_raw_data['line_total'];
		$line_tax          = empty( $product_raw_data['line_tax'] ) ? 0 : $product_raw_data['line_tax'];

		return array(
			'name'          => $product->get_name(),
			'product_id'    => $product_id,
			'product_image' => $src,
			'product_data'  => $product_data,
			'sku'           => ( ! is_bool($product) && ! empty($product) ? $product->get_sku() : null ),
			'product_url'   => get_the_permalink( $product_id ),
			'variation_id'  => $product_raw_data[ self::VARIATION_ID ],
			'subtotal'      => wc_format_decimal( $line_subtotal, $decimal_point ),
			'subtotal_tax'  => wc_format_decimal( $line_subtotal_tax, $decimal_point ),
			'total'         => wc_format_decimal( $line_total, $decimal_point ),
			'total_tax'     => wc_format_decimal( $line_tax, $decimal_point ),
			'price'         => wc_format_decimal( $line_subtotal, $decimal_point ),
			'quantity'      => $product_raw_data[ self::QUANTITY ],
		);
	}
}
