<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_WC_EVENTTYPE', 'WordPress - WooCommerce');

use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactAddressModel;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use Exception;
use WC_Order_Refund;

final class WooCommercePluginHandler extends BaseContactFormPluginHandler {

	const CHECKOUT_CONSENT_CHECKBOX_ID        = 'ce4wp_checkout_consent_checkbox';
	const CHECKOUT_CONSENT_CHECKBOX_VALUE     = 'ce4wp_checkout_consent';
	const CHECKOUT_CONSENT_CHECKBOX_VALUE_OLD = 'ce_checkout_consent';

	/**
	 * Checks if the plugin is sync.
	 *
	 * @var bool
	 */
	public $isSync = false;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Converts to contact model.
	 *
	 * @param int $orderId The order id.
	 *
	 * @return ContactModel
	 *
	 * @throws Exception
	 */
	public function convertToContactModel( $orderId ) {
		$all_orders      = null;
		$contactModel    = new ContactModel();
		$products_detail = get_post_meta($orderId);
		$order           = wc_get_order($orderId);

		if ( is_object($order) && is_a($order, 'WC_Order') ) {
			$all_orders = wc_get_orders(array( 'email' => $order->get_billing_email() ));
		}

		$number_of_orders = count(is_array($all_orders) ? $all_orders : array());

		if ( isset($products_detail) ) {
			if ( ! empty($products_detail['_billing_email']) && isset($products_detail['_billing_email'][0]) && ! empty($products_detail['_billing_email'][0]) ) {
				$contactModel->setEmail($products_detail['_billing_email'][0]);
			} else {
				return $contactModel;
			}

			if ( ! empty($products_detail['_billing_first_name']) ) {
				$contactModel->setFirstName($products_detail['_billing_first_name'][0]);
			}
			if ( ! empty($products_detail['_billing_last_name']) ) {
				$contactModel->setLastName($products_detail['_billing_last_name'][0]);
			}

			$contactAddress = $this->getContactAddressFromOrder($products_detail);

			if ( ! empty($contactAddress) ) {
				$contactModel->setContactAddress($contactAddress);
			}

			if ( ! empty($contactModel->getEmail()) ) {
				$contactModel->setEventType(CE4WP_WC_EVENTTYPE);
				$contactModel->setOptActionBy(2);
				$contactModel->setOptIn($this->isSync);
				$contactModel->setOptOut(false);
			}

			if ( ! empty($products_detail['_billing_phone']) ) {
				$contactModel->setPhone($products_detail['_billing_phone'][0]);
			}

			if ( ! empty($number_of_orders) ) {
				$contactModel->setNumberOfOrders($number_of_orders);
			}

			$this->setConsentValues($contactModel, $products_detail);
		}
		return $contactModel;
	}

	/**
	 * Sets the consent values.
	 *
	 * @param ContactModel $contactModel The contact model.
	 * @param array        $products_detail The product details.
	 *
	 * @return void
	 */
	public function setConsentValues( $contactModel, $products_detail ) {
		$checkbox_value = null;

		if ( isset( $_POST['woocommerce-process-checkout-nonce'] ) ) {
			wp_verify_nonce(
				sanitize_text_field( wp_unslash($_POST['woocommerce-process-checkout-nonce']) ),
				'woocommerce-process_checkout'
			);
		}

		if ( ! empty($_POST[ self::CHECKOUT_CONSENT_CHECKBOX_ID ]) ) {
			$checkbox_value = esc_attr(
				sanitize_text_field ( wp_unslash($_POST[ self::CHECKOUT_CONSENT_CHECKBOX_ID ]) )
			);
		} elseif ( ! empty($products_detail[ self::CHECKOUT_CONSENT_CHECKBOX_ID ]) ) {
			$checkbox_value = $products_detail[ self::CHECKOUT_CONSENT_CHECKBOX_ID ];
		} elseif ( ! empty($_POST[ self::CHECKOUT_CONSENT_CHECKBOX_VALUE ]) ) {
			$checkbox_value = esc_attr(
				sanitize_text_field ( wp_unslash( $_POST[ self::CHECKOUT_CONSENT_CHECKBOX_VALUE ] ))
			);
		} elseif ( ! empty($products_detail[ self::CHECKOUT_CONSENT_CHECKBOX_VALUE ]) ) {
			$checkbox_value = $products_detail[ self::CHECKOUT_CONSENT_CHECKBOX_VALUE ][0]; // This value appears to be in array.
		} elseif ( ! empty($products_detail[ self::CHECKOUT_CONSENT_CHECKBOX_VALUE_OLD ]) ) {
			$checkbox_value = $products_detail[ self::CHECKOUT_CONSENT_CHECKBOX_VALUE_OLD ][0]; // This value appears to be in array.
		}

		if ( ! is_null($checkbox_value) ) {
			$contactModel->setOptActionBy(1);
			if ( $checkbox_value ) {
				$contactModel->setOptIn(true);
			} elseif ( ! $checkbox_value ) {
				$contactModel->setOptIn(false);
			}
		}
	}

	/**
	 * Gets the contact address from order.
	 *
	 * @param array<string,mixed> $products_detail The product details.
	 *
	 * @return ContactAddressModel
	 */
	public function getContactAddressFromOrder( array $products_detail ): ContactAddressModel {
		$contactAddress = new ContactAddressModel();

		if ( isset($products_detail) ) {
			if ( ! empty($products_detail['_billing_address_1']) ) {
				$contactAddress->setAddress($products_detail['_billing_address_1'][0]);
			}
			if ( ! empty($products_detail['_billing_address_2']) ) {
				$contactAddress->setAddress2($products_detail['_billing_address_2'][0]);
			}
			if ( ! empty($products_detail['_billing_city']) ) {
				$contactAddress->setCity($products_detail['_billing_city'][0]);
			}
			if ( ! empty($products_detail['_billing_country']) ) {
				$contactAddress->setCountryCode($products_detail['_billing_country'][0]);
			}
			if ( ! empty($products_detail['_billing_postcode']) ) {
				$contactAddress->setPostalCode($products_detail['_billing_postcode'][0]);
			}
			if ( ! empty($products_detail['_billing_state']) ) {
				$contactAddress->setStateCode($products_detail['_billing_state'][0]);
			}
		}

		return $contactAddress;
	}

	/**
	 * WooCommerce Handler for new orders.
	 *
	 * @param WC_Order|WC_Order_Refund $order_id The order id.
	 *
	 * @return void
	 */
	public function ceHandlerWooCommerceNewOrder( $order_id ) {
		try {
			$order = wc_get_order($order_id);

			if ( is_object($order) && is_a($order, 'WC_Order') ) {
				$this->upsertContact($this->convertToContactModel($order->get_id()));
			}
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	/**
	 * Register Hooks.
	 *
	 * @return void
	 */
	public function registerHooks() {
		add_action('woocommerce_checkout_order_created', array( $this, 'ceHandlerWooCommerceNewOrder' ), 10, 1);
	}

	/**
	 * Unregister Hooks.
	 *
	 * @return void
	 */
	public function unregisterHooks() {
		remove_action('woocommerce_checkout_order_created', array( $this, 'ceHandlerWooCommerceOrder' ));
	}

	/**
	 * Gets the contact model from order.
	 *
	 * @param ?int $limit The limit.
	 *
	 * @return array|null
	 */
	public function get_contacts( $limit = null ) {
		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}

		$backfillArray = array();

		$args = array(
			'posts_per_page' => -1,
			'post_type'      => 'shop_order',
			'post_status'    => array_keys(wc_get_order_statuses()),
		);

		if ( null != $limit ) {
			$args['posts_per_page'] = $limit;
		}

		$products_orders = get_posts($args);

		foreach ( $products_orders as $products_order ) {

			$contactModel = null;
			try {
				$this->isSync = true;
				$contactModel = $this->convertToContactModel($products_order->ID);
			} catch ( Exception $exception ) {
				// Silent exception.
				continue;
			}

			if ( ! empty($contactModel->getEmail()) ) {
				array_push($backfillArray, $contactModel);
			}
		}

		if ( ! empty($backfillArray) ) {
			return $backfillArray;
		}

		return null;
	}
}
