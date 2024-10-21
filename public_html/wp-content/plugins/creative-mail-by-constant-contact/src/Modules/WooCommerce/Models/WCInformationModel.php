<?php

namespace CreativeMail\Modules\WooCommerce\Models;

use CreativeMail\CreativeMail;

class WCInformationModel {

	public $wc_installed;
	public $wc_version;
	public $plugin_version;
	public $perma_links;

	public function __construct() {
		$this->wc_installed = in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true);
		if ( $this->wc_installed ) {
			global $woocommerce;
			$this->wc_version = $woocommerce->version;
		}

		$this->plugin_version = CE4WP_PLUGIN_VERSION;
		$this->perma_links    = CreativeMail::get_instance()->get_integration_manager()->get_permalinks_enabled();
	}
}
