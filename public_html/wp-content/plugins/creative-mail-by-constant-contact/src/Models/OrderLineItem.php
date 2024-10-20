<?php

namespace CreativeMail\Models;

use WC_Order;
use WC_Order_Item;
use WC_Product;

final class OrderLineItem {

	/**
	 * Stores the product ID of the order line item.
	 *
	 * @var int
	 */
	public $product_id;

	/**
	 * Stores the product name of the order line item.
	 *
	 * @var array
	 */
	public $item_meta;

	/**
	 * Stores the order subtotal.
	 *
	 * @var string
	 */
	public $subtotal;

	/**
	 * Stores the order subtotal tax.
	 *
	 * @var string
	 */
	public $subtotal_tax;

	/**
	 * Stores the order total.
	 *
	 * @var string
	 */
	public $total;

	/**
	 * Stores the order total tax.
	 *
	 * @var string
	 */
	public $total_tax;

	/**
	 * Stores the product price.
	 *
	 * @var string
	 */
	public $price;

	/**
	 * Stores the product quantity.
	 *
	 * @var int
	 */
	public $quantity;

	/**
	 * Stores the Tax Class.
	 *
	 * @var string
	 */
	public $tax_class;

	/**
	 * Stores the product name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Stores the product image.
	 *
	 * @var string|mixed
	 */
	public $product_image;

	/**
	 * Stores the product data.
	 *
	 * @var array<array<mixed>>
	 */
	public $product_data;

	/**
	 * Stores the product SKU.
	 *
	 * @var string|null
	 */
	public $sku;

	/**
	 * Stores the product variation meta.
	 *
	 * @var array<mixed>
	 */
	public $meta;

	/**
	 * Stores the product URL.
	 *
	 * @var string
	 */
	public $product_url;

	/**
	 * Stores the variation ID of the product.
	 *
	 * @var int
	 */
	public $variation_id;

	/**
	 * OrderLineItem constructor.
	 *
	 * @param WC_Order_Item $woocommerce_item The WooCommerce order item.
	 * @param int           $decimal_point   The decimal point.
	 * @param WC_Order      $woocommerce_order The WooCommerce order.
	 * @param array<mixed>  $item_meta      The item meta.
	 * @param mixed         $src           The source.
	 * @param array<mixed>  $product_data The product data.
	 * @param WC_Product    $woocommerce_product The WooCommerce product.
	 */
	public function __construct(
		WC_Order_Item $woocommerce_item,
		int $decimal_point,
		WC_Order $woocommerce_order,
		array $item_meta,
		$src,
		array $product_data,
		WC_Product $woocommerce_product
	) {
		$this->product_id    = $woocommerce_item->get_product_id();
		$this->item_meta     = $woocommerce_item->get_formatted_meta_data();
		$this->subtotal      = wc_format_decimal(
			$woocommerce_order->get_line_subtotal(
				$woocommerce_item,
				false,
				false
			), $decimal_point
		);
		$this->subtotal_tax  = wc_format_decimal( $woocommerce_item->get_subtotal_tax(), $decimal_point );
		$this->total         = wc_format_decimal(
			$woocommerce_order->get_line_total(
				$woocommerce_item,
				false,
				false
			), $decimal_point
		);
		$this->total_tax     = wc_format_decimal( $woocommerce_item->get_total_tax(), $decimal_point );
		$this->price         = wc_format_decimal(
			$woocommerce_order->get_item_total(
				$woocommerce_item,
				false,
				false
			), $decimal_point
		);
		$this->quantity      = $woocommerce_item->get_quantity();
		$this->tax_class     = $woocommerce_item->get_tax_class();
		$this->name          = $woocommerce_item->get_name();
		$this->meta          = array_values( $item_meta );
		$this->product_url   = get_the_permalink( $woocommerce_item->get_product_id() )
			? get_the_permalink( $woocommerce_item->get_product_id() )
			: '';
		$this->variation_id  = $woocommerce_item->get_variation_id();
		$this->product_image = $src;
		$this->product_data  = $product_data;
		$this->sku           = is_a($woocommerce_product, 'WC_Product') ? $woocommerce_product->get_sku() : null;
	}
}
