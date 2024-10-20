<?php


namespace CreativeMail\Managers;

use CreativeMail\CreativeMail;
use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Models\CustomerNewAccount;
use CreativeMail\Models\CustomerNote;
use CreativeMail\Models\CustomerResetPassword;
use CreativeMail\Models\EmailNotification;
use CreativeMail\Models\TriggerExecution;
use CreativeMail\Modules\WooCommerce\Emails\AbandonedCartEmail;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use Exception;
use WC_Customer;
use WC_Email;

/**
 * Class EmailManager
 *
 * @package CreativeMail\Managers
 */
class EmailManager {

	/**
	 * Email ids being managed by CreativeMail
	 *
	 * @var array
	 */
	protected $managed_email_notifications;

	private $valid_email_notification_names = array(
		'customer_completed_order',
		'customer_refunded_order',
		'customer_processing_order',
		'customer_on_hold_order',
		'customer_new_account',
		'customer_reset_password',
		'customer_invoice',
		'customer_note',
		'cart_abandoned_ce4wp',
	);

	const CHECKOUT_CONSENT_CHECKBOX_ID    = 'ce4wp_checkout_consent_checkbox';
	const CHECKOUT_CONSENT_CHECKBOX_VALUE = 'ce4wp_checkout_consent';

	public function __construct() {
		$this->managed_email_notifications = $this->get_managed_email_notifications();
	}

	public function add_hooks() {
		// Check if woocommerce is active.
		if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
			add_action('init', array( $this, 'manage_emails' ));

			// Email settings table customizations.
			add_filter('woocommerce_email_setting_columns', array( $this, 'customize_email_setting_columns' ));
			add_action('woocommerce_email_setting_column_wc_ce_status', array( $this, 'render_email_status_column' ));
			add_action('woocommerce_email_settings_before', array( $this, 'redirect_managed_email_settings_to_creative_mail' ));

			// Woocommerce hooks.
			add_action('woocommerce_new_order', array( $this, 'ce_email_notification_new_order' ), 10, 2);
			add_action('woocommerce_order_status_cancelled', array( $this, 'ce_email_notification_cancelled' ), 10, 2);
			add_action('woocommerce_order_status_failed', array( $this, 'ce_email_notification_failed' ), 10, 2);
			add_action('woocommerce_order_status_on-hold', array( $this, 'ce_email_notification_hold' ), 10, 2);
			add_action('woocommerce_order_status_processing', array( $this, 'ce_email_notification_processing' ), 10, 2);
			add_action('woocommerce_order_status_completed', array( $this, 'ce_email_notification_completed' ), 10, 2);
			add_action('woocommerce_order_status_refunded', array( $this, 'ce_email_notification_refunded' ), 10, 2);
			add_action('woocommerce_after_resend_order_email', array( $this, 'ce_email_notification_invoice' ), 10, 2);

			// Payment complete.
			add_action('woocommerce_payment_complete', array( $this, 'ce_email_notification_payment_complete' ), 10, 1);
			// New customer Note.
			add_action('woocommerce_new_customer_note', array( $this, 'ce_email_notification_new_customer_note' ), 10, 2);
			// Password reset.
			add_action('woocommerce_reset_password_notification', array( $this, 'ce_email_notification_customer_reset_password' ), 10, 2);
			add_action('woocommerce_created_customer', array( $this, 'ce_email_notification_customer_new_account' ), 10, 3);
			// Replace WC Email settings.
			add_filter('woocommerce_email_settings', array( $this, 'replace_wc_email_settings' ));
			add_action('woocommerce_admin_field_ce_manage_button', array( $this, 'print_ce_manage_button' ));
			// Add checkbox to check out if enabled.
			if ( OptionsHelper::get_checkout_checkbox_enabled() == '1' ) {
				add_filter('woocommerce_after_order_notes', array( $this, 'add_checkout_field' ));
				add_action('woocommerce_checkout_update_order_meta', array( $this, 'ce4wp_checkout_order_meta' ));
			}
			// Modify emails emails.
			add_filter( 'woocommerce_email_classes', array( $this, 'add_emails' ), 20 );
		}
	}

	public function add_emails( $email_classes ) {
		// Add fake email.
		$email_classes['AbandonedCartEmail'] = new AbandonedCartEmail();
		return $email_classes;
	}

	public function ce4wp_checkout_order_meta( $order_id ) {
		if ( isset( $_REQUEST['woocommerce-process-checkout-nonce'] )
			&& wp_verify_nonce(
				sanitize_text_field( wp_unslash ( $_REQUEST['woocommerce-process-checkout-nonce'] ) ),
				'woocommerce-process_checkout')
		) {
			if ( isset($_POST[ self::CHECKOUT_CONSENT_CHECKBOX_ID ]) ) {
				$checkbox_value = esc_attr(sanitize_text_field ( wp_unslash( $_POST[ self::CHECKOUT_CONSENT_CHECKBOX_ID ] ) ) );
			} else {
				$checkbox_value = '0';
			}
			update_post_meta($order_id, self::CHECKOUT_CONSENT_CHECKBOX_VALUE, $checkbox_value);
		}
	}

	public function add_checkout_field( $checkout ) {
		$checked       = $checkout->get_value(self::CHECKOUT_CONSENT_CHECKBOX_ID) ? $checkout->get_value(self::CHECKOUT_CONSENT_CHECKBOX_ID) : 0;
		$checkbox_text = stripslashes(OptionsHelper::get_checkout_checkbox_text());

		woocommerce_form_field(
			self::CHECKOUT_CONSENT_CHECKBOX_ID, array(
				'type'  => 'checkbox',
				'class' => array( 'ce-field form-row-wide' ),
				'label' => $checkbox_text,
			), $checked
		);
	}

	public function replace_wc_email_settings( $settings ) {
		$default_setting_replacement = array(
			'woocommerce_email_header_image'          => 'header_image',
			'woocommerce_email_footer_text'           => 'footer_content_text',
			'woocommerce_email_base_color'            => null,
			'woocommerce_email_background_color'      => 'background_color',
			'woocommerce_email_body_background_color' => 'email_background_color',
			'woocommerce_email_text_color'            => 'text_color',
		);

		// Define options that need to be replaced.
		$replace = array_merge(array_keys($default_setting_replacement), array( 'email_template_options' ));

		// Remove settings.
		foreach ( $settings as $setting_key => $setting ) {
			if ( isset($setting['id']) && in_array($setting['id'], $replace, true) ) {
				unset($settings[ $setting_key ]);
			}
		}

		$settings[] = array(
			'id'    => 'ce_manage',
			'type'  => 'title',
			'title' => __('Creative Mail', 'creative-mail-by-constant-contact'),
		);

		$settings[] = array(
			'id'   => 'ce_manage_button',
			'type' => 'ce_manage_button',
		);

		$settings[] = array(
			'id'   => 'ce_manage',
			'type' => 'sectionend',
		);
		return $settings;
	}

	public function print_ce_manage_button( $options ) { ?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php esc_html_e('Customize Emails', 'creative-mail-by-constant-contact'); ?></th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title($options['type']) ); ?>">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=creativemail' ) ); ?>">
					<button type="button" class="button button-secondary" value="<?php esc_attr_e('Manage', 'creative-mail-by-constant-contact'); ?>">
						<?php esc_html_e('Manage', 'creative-mail-by-constant-contact'); ?>
					</button>
				</a>
				<p class="description">
					<?php esc_html_e('Manage all your email settings and templates with Creative Mail', 'creative-mail-by-constant-contact'); ?>
				</p>
			</td>
		</tr>
		<?php
	}

	public function get_managed_email_notifications() {
		return OptionsHelper::get_managed_email_notifications();
	}

	public function set_managed_email_notifications( $body ) {
		if ( empty($body) ) {
			return null;
		}

		if ( property_exists($body, 'name') && in_array( $body->name, $this->valid_email_notification_names, true ) ) {
			OptionsHelper::set_managed_email_notification($body->name, true == $body->active ? true : false);
			return $body;
		}

		return null;
	}

	/**
	 * Renders the custom email status column.
	 *
	 * @param WC_Email $email the email.
	 *
	 * @internal
	 * @since    1.1.0
	 */
	public function render_email_status_column( WC_Email $email ) {

		echo '<td class="wc-email-settings-table-status">';

		if ( $this->is_email_managed($email->id) ) {
			echo '<span class="status-creativemail tips" data-tip="' . esc_attr__('Managed by Creative Mail', 'creative-mail-by-constant-contact') . '">' . esc_html__('Managed by CreativeMail', 'creative-mail-by-constant-contact') . '</span>';
		} elseif ( 'cart_abandoned_ce4wp' === $email->id ) {
			echo '<span class="status-disabled tips" data-tip="' . esc_attr__('Disabled', 'woocommerce') . '">-</span>';
		} elseif ( $email->is_manual() ) {
			echo '<span class="status-manual tips" data-tip="' . esc_attr__('Manually sent', 'woocommerce') . '">' . esc_html__('Manual', 'woocommerce') . '</span>';
		} elseif ( $email->is_enabled() ) {
			echo '<span class="status-enabled tips" data-tip="' . esc_attr__('Enabled', 'woocommerce') . '">' . esc_html__('Yes', 'woocommerce') . '</span>';
		} else {
			echo '<span class="status-disabled tips" data-tip="' . esc_attr__('Disabled', 'woocommerce') . '">-</span>';
		}

		echo '</td>';
	}

	public function customize_email_setting_columns( $columns ) {

		$column_keys = array_keys($columns);

		// Replace the status column, or put at the beginning if status isn't found.
		$status_index = array_search('status', $column_keys, true);
		array_splice($column_keys, is_numeric($status_index) ? $status_index : 0, is_numeric($status_index) ? 1 : 0, 'wc_ce_status');

		$new_columns             = array();
		$columns['wc_ce_status'] = '';

		foreach ( $column_keys as $column_key ) {
			if ( isset($columns[ $column_key ]) ) {
				$new_columns[ $column_key ] = $columns[ $column_key ];
			}
		}

		return $new_columns;
	}

	public function ce_email_notification_new_customer_note( $array ) {
		$data            = new CustomerNote();
		$data->order_id  = $array['order_id'];
		$data->note      = $array['customer_note'];
		$data->order_url = $this->get_view_order_url( $data->order_id, null );

		$this->execute_trigger( 'customer_note', $data, wc_get_order( $data->order_id ) );
	}

	public function ce_email_notification_customer_new_account( $customer_id, $new_customer_data, $password_generated ) {
		$data              = new CustomerNewAccount();
		$data->customer_id = $customer_id;
		$data->account_url = $this->get_my_account_url();
		$data->customer    = $this->get_customer_data( $customer_id );

		if ( $password_generated && key_exists( 'user_pass', $new_customer_data ) ) {
			try {
				$salted = '';
				$dx     = '';
				$salt   = null;

				$generated_password       = $new_customer_data['user_pass'];
				$key                      = sha1( OptionsHelper::get_instance_api_key() . OptionsHelper::get_instance_uuid() );
				$openssl_cipher_iv_length = openssl_cipher_iv_length( 'aes-256-cbc' );

				if ( ! is_bool( $openssl_cipher_iv_length ) ) {
					$salt = openssl_random_pseudo_bytes( $openssl_cipher_iv_length );
				}
				if ( ! empty($salt) && is_string( $salt ) ) {
					$salt_len = strlen( $salted ) < 48;
					while ( $salt_len ) {
						$dx       = md5( $dx . $key . $salt, true );
						$salted  .= $dx;
						$salt_len = strlen( $salted ) < 48;
					}
				}
				$key                      = substr( $salted, 0, 32 );
				$iv                       = substr( $salted, 32, 16 );
				$gp                       = openssl_encrypt( $generated_password, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
				$data->salt               = bin2hex( is_string($salt) ? $salt : '' );
				$data->generated_password = base64_encode( is_string($gp) ? $gp : '' );
			} catch ( Exception $ex ) {
				DatadogManager::get_instance()->exception_handler( $ex );
			}
		}

		$this->execute_trigger( 'customer_new_account', $data, null, true );
	}

	public function ce_email_notification_customer_reset_password( $user_login = '', $reset_key = '' ) {
		if ( $user_login && $reset_key ) {
			$data = new CustomerResetPassword();
			$user = get_user_by( 'login', $user_login );
			if ( ! empty($user) ) {
				$data->customer_id = $user->ID;
				$data->customer    = $this->get_customer_data($user->ID);
			}
			$data->account_url = $this->get_my_account_url();
			$data->reset_url   = add_query_arg(
				array(
					'key' => $reset_key,
					'id'  => $data->customer_id,
				), wc_get_endpoint_url( 'lost-password', '', $data->account_url )
			);

			$this->execute_trigger( 'customer_reset_password', $data, null, true );
		}
	}

	public function ce_email_notification_payment_complete( $order_id ) {
		$data            = new EmailNotification();
		$data->order_id  = $order_id;
		$data->order_url = $this->get_view_order_url( $order_id, null );

		$this->execute_trigger( 'payment_received', $data, wc_get_order( $order_id ) );
	}

	public function ce_email_notification_failed( $order_id, $order ) {
		$data            = new EmailNotification();
		$data->order_id  = $order_id;
		$data->order_url = $this->get_view_order_url( $order_id, $order );

		$this->execute_trigger( 'failed_order', $data, $order );
	}
	public function ce_email_notification_hold( $order_id, $order ) {
		$data            = new EmailNotification();
		$data->order_id  = $order_id;
		$data->order_url = $this->get_view_order_url( $order_id, $order );

		$this->execute_trigger( 'customer_on_hold_order', $data, $order );
	}
	public function ce_email_notification_processing( $order_id, $order ) {
		$data            = new EmailNotification();
		$data->order_id  = $order_id;
		$data->order_url = $this->get_view_order_url( $order_id, $order );

		$this->execute_trigger( 'customer_processing_order', $data, $order );
	}
	public function ce_email_notification_completed( $order_id, $order ) {
		$data            = new EmailNotification();
		$data->order_id  = $order_id;
		$data->order_url = $this->get_view_order_url( $order_id, $order );

		$this->execute_trigger( 'customer_completed_order', $data, $order );
	}

	public function ce_email_notification_refunded( $order_id, $order ) {
		$data            = new EmailNotification();
		$data->order_id  = $order_id;
		$data->order_url = $this->get_view_order_url( $order_id, $order );

		$this->execute_trigger( 'customer_refunded_order', $data, $order );
	}
	public function ce_email_notification_cancelled( $order_id, $order ) {
		$data            = new EmailNotification();
		$data->order_id  = $order_id;
		$data->order_url = $this->get_view_order_url( $order_id, $order );

		$this->execute_trigger( 'cancelled_order', $data, $order );
	}

	public function ce_email_notification_new_order( $order_id, $order ) {
		$data            = new EmailNotification();
		$data->order_id  = $order_id;
		$data->order_url = $this->get_view_order_url( $order_id, $order );

		$this->execute_trigger( 'new_order', $data, $order );
	}

	public function ce_email_notification_invoice( $order, $type ) {
		if ( 'new_order' === $type ) {
			$this->ce_email_notification_new_order( $order->id, $order );
			return;
		}

		$data            = new EmailNotification();
		$data->order_id  = $order->get_id();
		$data->order_url = $this->get_view_order_url( $order->get_id(), $order );

		$this->execute_trigger( 'customer_invoice', $data, $order );
	}

	public function execute_trigger( $type, $data, $order = null, $with_data = false ) {
		// If not managed do not trigger.
		if ( ! $this->is_email_managed( $type ) ) {
			return;
		}

		$requestItem       = new TriggerExecution();
		$requestItem->type = $type;
		$decimal_point     = 2;

		if ( ! is_null( $order ) ) {
			try {
				$order_data = array(
					'id'                        => $order->get_id(),
					'number'                    => $order->get_order_number(),
					'date_created'              => $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0,
					'date_modified'             => $order->get_date_modified() ? $order->get_date_modified()->getTimestamp() : 0,
					'date_completed'            => $order->get_date_completed() ? $order->get_date_completed()->getTimestamp() : 0,
					'status'                    => $order->get_status(),
					'currency'                  => $order->get_currency(),
					'currency_symbol'           => get_woocommerce_currency_symbol( $order->get_currency()),
					'total'                     => wc_format_decimal( $order->get_total(), $decimal_point ),
					'subtotal'                  => wc_format_decimal( $order->get_subtotal(), $decimal_point ),
					'total_line_items_quantity' => $order->get_item_count(),
					'total_tax'                 => wc_format_decimal( $order->get_total_tax(), $decimal_point ),
					'shipping_total'            => wc_format_decimal( $order->get_shipping_total(), $decimal_point ),
					'cart_tax'                  => wc_format_decimal( $order->get_cart_tax(), $decimal_point ),
					'shipping_tax'              => wc_format_decimal( $order->get_shipping_tax(), $decimal_point ),
					'discount_total'            => wc_format_decimal( $order->get_total_discount(), $decimal_point ),
					'shipping_methods'          => $order->get_shipping_method(),
					'payment_details'           => array(
						'method_id'    => $order->get_payment_method(),
						'method_title' => $order->get_payment_method_title(),
						'paid'         => ! is_null($order->get_date_paid()),
					),
					'billing'                   => array(
						'first_name' => $order->get_billing_first_name(),
						'last_name'  => $order->get_billing_last_name(),
						'company'    => $order->get_billing_company(),
						'address_1'  => $order->get_billing_address_1(),
						'address_2'  => $order->get_billing_address_2(),
						'city'       => $order->get_billing_city(),
						'state'      => $order->get_billing_state(),
						'postcode'   => $order->get_billing_postcode(),
						'country'    => $order->get_billing_country(),
						'email'      => $order->get_billing_email(),
						'phone'      => $order->get_billing_phone(),
					),
					'shipping'                  => array(
						'first_name' => $order->get_shipping_first_name(),
						'last_name'  => $order->get_shipping_last_name(),
						'company'    => $order->get_shipping_company(),
						'address_1'  => $order->get_shipping_address_1(),
						'address_2'  => $order->get_shipping_address_2(),
						'city'       => $order->get_shipping_city(),
						'state'      => $order->get_shipping_state(),
						'postcode'   => $order->get_shipping_postcode(),
						'country'    => $order->get_shipping_country(),
					),
					'customer_note'             => $order->get_customer_note(),
					'customer_ip'               => $order->get_customer_ip_address(),
					'customer_user_agent'       => $order->get_customer_user_agent(),
					'customer_id'               => $order->get_user_id(),
					'view_order_url'            => $order->get_view_order_url(),
					'line_items'                => array(),
					'shipping_lines'            => array(),
					'tax_lines'                 => array(),
					'fee_lines'                 => array(),
					'coupon_lines'              => array(),
				);

				// Add line items.
				foreach ( $order->get_items() as $item_id => $item ) {
					$product   = $item->get_product();
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
							$item_downloads = $item->get_item_downloads();
							foreach ( $item_downloads as $item_download ) {
								$product_data['downloads'][] = array(
									'line_item_id'        => $item->get_id(),
									'product_id'          => $item->get_product_id(),
									'download_url'        => $item_download['download_url'],
									'download_file'       => $item_download['file'],
									'download_name'       => $item_download['name'],
									'download_id'         => $item_download['id'],
									'downloads_remaining' => $item_download['downloads_remaining'],
									'download_access_expires' => wc_format_datetime($item_download['access_expires'], 'U'),
									'download_limit'      => $product->get_download_limit(),
									'download_expiry'     => $product->get_download_expiry(),
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
						list( $src ) = is_array( $image_src ) ? $image_src : array( '' );
					}

					$order_data['line_items'][] = array(
						'id'            => $item_id,
						'subtotal'      => wc_format_decimal( $order->get_line_subtotal( $item, false, false ), $decimal_point ),
						'subtotal_tax'  => wc_format_decimal( $item->get_subtotal_tax(), $decimal_point ),
						'total'         => wc_format_decimal( $order->get_line_total( $item, false, false ), $decimal_point ),
						'total_tax'     => wc_format_decimal( $item->get_total_tax(), $decimal_point ),
						'price'         => wc_format_decimal( $order->get_item_total( $item, false, false ), $decimal_point ),
						'quantity'      => $item->get_quantity(),
						'tax_class'     => $item->get_tax_class(),
						'name'          => $item->get_name(),
						'product_id'    => $item->get_product_id(),
						'product_image' => $src,
						'product_data'  => $product_data,
						'sku'           => is_a($product, 'WC_Product') ? $product->get_sku() : null,
						'meta'          => array_values( $item_meta ),
						'product_url'   => get_the_permalink( $item->get_product_id() ),
						'variation_id'  => $item->get_variation_id(),
					);
				}

				// Add shipping.
				foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
					$order_data['shipping_lines'][] = array(
						'id'           => $shipping_item_id,
						'method_id'    => $shipping_item->get_method_id(),
						'method_title' => $shipping_item->get_name(),
						'total'        => wc_format_decimal( $shipping_item->get_total(), $decimal_point ),
					);
				}

				// Add taxes.
				foreach ( $order->get_tax_totals() as $tax_code => $tax ) {
					$order_data['tax_lines'][] = array(
						'id'       => $tax->id,
						'rate_id'  => $tax->rate_id,
						'code'     => $tax_code,
						'title'    => $tax->label,
						'total'    => wc_format_decimal($tax->amount, $decimal_point),
						'compound' => (bool) $tax->is_compound,
					);
				}

				// Add fees.
				foreach ( $order->get_fees() as $fee_item_id => $fee_item ) {
					$order_data['fee_lines'][] = array(
						'id'        => $fee_item_id,
						'title'     => $fee_item->get_name(),
						'tax_class' => $fee_item->get_tax_class(),
						'total'     => wc_format_decimal( $order->get_line_total( $fee_item ), $decimal_point ),
						'total_tax' => wc_format_decimal( $order->get_line_tax( $fee_item ), $decimal_point ),
					);
				}

				// Add coupons.
				foreach ( $order->get_items( 'coupon' ) as $coupon_item_id => $coupon_item ) {
					$order_data['coupon_lines'][] = array(
						'id'     => $coupon_item_id,
						'code'   => $coupon_item->get_code(),
						'amount' => wc_format_decimal( $coupon_item->get_discount(), $decimal_point ),
					);
				}
				$data->order = $order_data;
				$with_data   = true;
			} catch ( Exception $ex ) {
				DatadogManager::get_instance()->exception_handler( $ex );
				$with_data = false;
			}
		}

		$parsed_data       = wp_json_encode( $data );
		$requestItem->data = is_string( $parsed_data ) ? $parsed_data : '';
		$endpoint          = EnvironmentHelper::get_app_gateway_url( 'wordpress/v1.0/wc/trigger' );
		if ( $with_data ) {
			$endpoint = EnvironmentHelper::get_app_gateway_url( 'wordpress/v1.0/wc/trigger-with-data' );
		}

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

	/**
	 * Initializes email management.
	 *
	 * @internal
	 *
	 * @since 1.1.0
	 */
	public function manage_emails() {
		if ( empty( $this->managed_email_notifications ) || ! is_array( $this->managed_email_notifications) ) {
			return;
		}

		// Disable managed emails.
		foreach ( $this->managed_email_notifications as $notification ) {
			if ( $this->is_email_managed( $notification->name ) ) {
				add_filter( 'woocommerce_email_enabled_' . $notification->name . '', '__return_false');
			}
		}

		add_filter( 'woocommerce_email_title', array( $this, 'override_managed_email_title' ), 10, 2 );
		add_filter( 'woocommerce_email_description', array( $this, 'override_managed_email_description' ), 10, 2 );
	}


	/**
	 * Overrides email titles for managed emails.
	 *
	 * @param string   $title the email title.
	 * @param WC_Email $email the email object.
	 *
	 * @internal
	 * @since    1.1.0
	 *
	 * @return string
	 */
	public function override_managed_email_title( $title, $email ) {
		if ( ! empty( $email->id ) && $this->is_email_managed( $email->id ) ) {
			$title .= __( ' (Managed by Creative Mail)', 'creative-mail-by-constant-contact' );
		}

		return $title;
	}


	/**
	 * Overrides email description for managed emails.
	 *
	 * @param string   $description description text.
	 * @param WC_Email $email       the email object.
	 *
	 * @internal
	 * @since    1.1.0
	 * @return   string
	 */
	public function override_managed_email_description( $description, $email ) {
		if ( ! empty( $email->id ) && $this->is_email_managed( $email->id ) ) {
			$description .= __( ' This email is being managed and sent by Creative Mail.', 'creative-mail-by-constant-contact' );
		}

		return $description;
	}

	/**
	 * Redirects the settings page of a managed email to the CreativeMail transactional notification for that email.
	 *
	 * @param WC_Email $email the email object.
	 *
	 * @since 1.1.0
	 */
	public function redirect_managed_email_settings_to_creative_mail( $email ) {
		if ( $this->is_email_managed( $email->id ) ) {
			$url = CreativeMail::get_instance()->get_admin_manager()->request_single_sign_on_url_internal( '66eabdb1-5d55-4bc0-a435-0415c5ada60a', array(
				'woocommerceTemplateSlug' => $email->id,
			) );
			wp_safe_redirect( $url );
			exit;
		}

		if ( 'cart_abandoned_ce4wp' === $email->id ) {
			$url = CreativeMail::get_instance()->get_admin_manager()->request_single_sign_on_url_internal( '1fabdbe2-95ed-4e1e-a2f3-ba0278f5096f', array(
				'source' => 'woocommerce_emails',
			));
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Checks if a given email ID is being managed by CreativeMail and is active.
	 *
	 * @param string $email_id woocommerce email ID.
	 *
	 * @since  1.1.0
	 * @return bool
	 */
	public function is_email_managed( $email_id ) {
		return (bool) $this->get_managed_notification_param( $email_id, 'active' );
	}

	/**
	 * Gets a param from the managed email notification for the given email ID.
	 *
	 * @param string $email_id woocommerce email ID.
	 * @param string $param    param name.
	 *
	 * @since 1.1.0
	 *
	 * @return mixed|null
	 */
	public function get_managed_notification_param( $email_id, $param ) {
		foreach ( $this->managed_email_notifications as $managed_email_notification ) {
			if ( $email_id == $managed_email_notification->name && property_exists( $managed_email_notification, $param ) ) {
				return $managed_email_notification->$param;
			}
		}
		return null;
	}

	/**
	 * Gets the managed email notification for the given email ID.
	 *
	 * @param array  $items    managed email notifications.
	 * @param string $email_id woocommerce email ID.
	 *
	 * @since  1.1.0
	 * @return mixed|null
	 */
	public function get_managed_notification( $items, $email_id ) {

		foreach ( $items as $managed_email_notification ) {
			if ( $email_id == $managed_email_notification->name ) {
				return $managed_email_notification;
			}
		}
		return null;
	}

	/**
	 * Gets the transactional notification ID for a given notification.
	 *
	 * @param string $email_id woocommerce email ID.
	 *
	 * @since 1.1.0
	 *
	 * @return int|null
	 */
	public function get_transactional_notification_id( $email_id ) {
		return $this->get_managed_notification_param( $email_id, 'transactional_notification_id' );
	}

	/**
	 * Gets the transactional notification state.
	 *
	 * @param string $email_id woocommerce email ID.
	 *
	 * @return string|null
	 * @since  1.1.0
	 */
	public function get_managed_notification_state( $email_id ) {

		return $this->get_managed_notification_param( $email_id, 'state' );
	}

	public function get_valid_email_notification_names() {
		return $this->valid_email_notification_names;
	}

	private function get_view_order_url( $order_id, $order ) {
		try {
			if ( ! isset( $order ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( isset( $order ) && is_object($order) && method_exists( $order, 'get_view_order_url' ) ) {
				return $order->get_view_order_url();
			}
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler( $exception );
		}

		return null;
	}

	private function get_my_account_url() {
		try {
			return wc_get_page_permalink( 'myaccount' );
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler( $exception );
		}

		return null;
	}

	private function get_customer_data( $customer_id ) {
		try {
			$customer = new WC_Customer( $customer_id );
			$data     = $customer->get_data();

			if ( null != $data['date_created'] ) {
				$data['date_created'] = $customer->get_date_created();
				if ( ! empty($data['date_created']) && is_a($data['date_created'], 'WC_DateTime') ) {
					$data['date_created'] = $data['date_created']->getTimestamp();
				}
			}
			if ( null != $data['date_modified'] ) {
				$data['date_modified'] = $customer->get_date_modified();
				if ( ! empty($data['date_modified']) && is_a($data['date_modified'], 'WC_DateTime') ) {
					$data['date_modified'] = $data['date_modified']->getTimestamp();
				}
			}

			return $data;
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler( $exception );
		}
		return null;
	}
}
