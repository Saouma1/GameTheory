<?php

namespace CreativeMail\Modules\Blog\Models;

class BlogAttachment {

	public $id;
	public $date;
	public $name;
	public $modified;
	public $url;
	public $thumbnail;
	public $meta_data;

	public function __construct( $wp_attachment ) {
		$this->id        = $wp_attachment->ID;
		$this->name      = $wp_attachment->post_title;
		$this->date      = $wp_attachment->post_date;
		$this->modified  = $wp_attachment->post_modified;
		$this->url       = wp_get_attachment_url($wp_attachment->ID);
		$this->thumbnail = wp_get_attachment_image_url($wp_attachment->ID, 'medium_large');
		$this->meta_data = wp_get_attachment_metadata($wp_attachment->ID);

		// In case false on failure.
		if ( false === $this->meta_data ) {
			$this->meta_data = array();
		}
	}
}
