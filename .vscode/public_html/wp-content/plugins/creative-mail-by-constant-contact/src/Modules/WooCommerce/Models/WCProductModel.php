<?php

namespace CreativeMail\Modules\WooCommerce\Models;

class WCProductModel {

	public $id;
	public $name;
	public $sku;
	public $slug;
	public $description;
	public $short_description;
	public $price;
	public $regular_price;
	public $sale_price;
	public $date_created;
	public $date_modified;
	public $status;
	public $stock_status;
	public $url;
	public $image_url;

	public function __construct( $data ) {
		$this->id                = $data['id'];
		$this->name              = $data['name'];
		$this->sku               = $data['sku'];
		$this->slug              = $data['slug'];
		$this->description       = $data['description'];
		$this->short_description = $data['short_description'];
		$this->price             = $data['price'];
		$this->regular_price     = $data['regular_price'];
		$this->sale_price        = $data['sale_price'];
		$this->date_created      = $data['date_created'];
		$this->date_modified     = $data['date_modified'];
		$this->status            = $data['status'];
		$this->stock_status      = $data['stock_status'];
		$this->url               = get_permalink($data['id']);
		$this->image_url         = null;
		if ( '' !== $data['image_id'] ) {
			$this->image_url = wp_get_attachment_url($data['image_id']);
		}
	}
}
