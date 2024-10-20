<?php

namespace CreativeMail\Modules\Blog\Models;

use CreativeMail\Modules\WooCommerce\Models\WCStoreInformation;

class BlogInformation {

	public $plugin_version;
	public $title;
	public $description;
	public $url;
	public $admin_email;
	public $language;
	public $rss2_url;
	public $logo;
	public $template;

	public $first_name;
	public $last_name;
	public $company;
	public $email;

	public $woocommerce;

	public function __construct() {
		$this->plugin_version = CE4WP_PLUGIN_VERSION;
		$this->title          = get_bloginfo('name');
		$this->description    = get_bloginfo('description');
		$this->url            = get_bloginfo('url');
		$this->admin_email    = get_bloginfo('admin_email');
		$this->language       = get_bloginfo('language');
		$this->rss2_url       = get_bloginfo('rss2_url');
		if ( has_custom_logo() ) {
			$this->logo = get_custom_logo();
		}
		$this->template    = get_template();
		$this->woocommerce = new WCStoreInformation();
	}
}
