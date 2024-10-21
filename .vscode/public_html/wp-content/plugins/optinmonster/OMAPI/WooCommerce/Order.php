<?php
/**
 * WooCommerce Order class.
 *
 * @since 2.13.8
 *
 * @package OMAPI
 * @author  Justin Sternberg
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Order class.
 *
 * @since 2.13.8
 */
class OMAPI_WooCommerce_Order {

	/**
	 * Holds instances of this order object.
	 *
	 * @since 2.13.8
	 *
	 * @var OMAPI_WooCommerce_Order[]
	 */
	protected static $instances = array();

	/**
	 * Holds the ID of the order.
	 *
	 * @since 2.13.8
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Holds the order object.
	 *
	 * @since 2.13.8
	 *
	 * @var WC_Order
	 */
	protected $order;

	/**
	 * Get instance of the OMAPI_WooCommerce_Order and cache it.
	 *
	 * @since 2.13.8
	 *
	 * @param  string  $id    The order ID.
	 * @param  boolean $cached Whether to use the cached instance or not.
	 *
	 * @return self
	 */
	public static function get( $id = '', $cached = true ) {
		if ( empty( $id ) ) {
			return new self( $id );
		}

		if ( $id instanceof WC_Order || $id instanceof WC_Abstract_Order ) {

			$order = $id;
			$id    = (int) $order->get_id();
			if ( ! isset( self::$instances[ $id ] ) ) {
				new self( $id );
			}

			self::$instances[ $id ]->set_order( $order );

		} elseif ( ! empty( $id->ID ) ) {
			$id = (int) $id->ID;
		} else {
			$id = (int) $id;
		}

		if ( ! $cached || ! isset( self::$instances[ $id ] ) ) {
			$me = new self( $id );
			$me->fetch_and_set_order();
		}

		return self::$instances[ $id ];
	}

	/**
	 * Class constructor.
	 *
	 * @since 2.13.8
	 *
	 * @param string $id The order ID.
	 */
	protected function __construct( $id = '' ) {

		// If no data has been passed, don't setup anything. Maybe we are in test or create mode?
		if ( empty( $id ) ) {
			return;
		}

		// Prepare properties.
		$this->id = $id;

		self::$instances[ $id ] = $this;
	}

	/**
	 * Fetches the order object and sets it.
	 *
	 * @since 2.13.8
	 *
	 * @return self
	 */
	protected function fetch_and_set_order() {
		$this->set_order( wc_get_order( $this->id ) );

		return $this;
	}

	/**
	 * Sets the order object.
	 *
	 * @since 2.13.8
	 *
	 * @param WC_Order $order The order object.
	 *
	 * @return self
	 */
	protected function set_order( $order ) {
		$this->order = $order;

		return $this;
	}

	/**
	 * Checks if the order exists/is valid.
	 *
	 * @since 2.13.8
	 *
	 * @return boolean
	 */
	public function is() {
		if ( empty( $this->order ) ) {
			return false;
		}
		$id = $this->order->get_id();

		return ! empty( $id );
	}

	/**
	 * Gets order meta, use HPOS API when possible.
	 *
	 * @since 2.13.8
	 *
	 * @param  string $key Meta Key.
	 * @param  bool   $single return first found meta with key, or all with $key.
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	public function get_meta( $key = '', $single = true, $context = 'edit' ) {
		return ! empty( $this->order ) && method_exists( $this->order, 'get_meta' )
			? $this->order->get_meta( $key, $single, $context )
			: get_post_meta( $this->id, $key, $single );
	}

	/**
	 * Updates order meta, use HPOS API when possible.
	 *
	 * If using HPOS, can pass $save = false to not save the order (for bulk updates).
	 *
	 * @since 2.13.8
	 *
	 * @param string $key   The meta key.
	 * @param mixed  $value The meta value.
	 * @param bool   $save  Whether to save the order after meta update (if using HPOS).
	 *
	 * @return boolean
	 */
	public function update_meta_data( $key, $value, $save = true ) {
		if ( ! empty( $this->order ) && method_exists( $this->order, 'update_meta_data' ) ) {
			$this->order->update_meta_data( $key, $value );
			return $save ? $this->order->save() : false;
		}

		return update_post_meta( $this->id, $key, $value );
	}

	/**
	 * Proxy calls to the order object.
	 *
	 * @since 2.13.8
	 *
	 * @param string $method The method name.
	 * @param array  $args   The method arguments.
	 *
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		return $this->order
			? call_user_func_array( array( $this->order, $method ), $args )
			: null;
	}
}
