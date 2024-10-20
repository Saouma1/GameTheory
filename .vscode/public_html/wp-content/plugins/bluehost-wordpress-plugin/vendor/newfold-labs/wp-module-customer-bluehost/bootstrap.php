<?php
use NewfoldLabs\WP\Module\CustomerBluehost\CustomerBluehost;
use NewfoldLabs\WP\Module\CustomerBluehost\SiteMeta;
use NewfoldLabs\WP\ModuleLoader\Container;

use function NewfoldLabs\WP\ModuleLoader\register as registerModule;

/**
 * Register the newfold customer data module for bluehost
 */
if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			registerModule(
				array(
					'name'     => 'newfold-customer-bluehost',
					'label'    => __( 'Customer Bluehost', 'newfold-customer-bluehost' ),
					'callback' => 'newfold_module_load_customer_bluehost',
					'isActive' => true,
					'isHidden' => true,
				)
			);

		}
	);
}

/**
 * Initialize CustomerBluehost class instance and add required filters
 * 
 * @param Container the module loader container
 * @return void
 */
function newfold_module_load_customer_bluehost( Container $container ) {
    new CustomerBluehost( $container );
    

    if ( function_exists( 'add_filter' ) ) {
        // Add filter for adding bluehost customer data to data module in cron event data
        add_filter( 
            'newfold_wp_data_module_cron_data_filter',
            function( $data ) {
                // Filter the cron event data object with bluehost specific customer data
                $data['customer'] = CustomerBluehost::collect();
                return $data;
            }
        );

        // Add filter to add site_id to core data module data
        add_filter( 
            'newfold_wp_data_module_core_data_filter',
            function( $data ) {
                $data['site_id'] = SiteMeta::get_id();
                return $data;
            }
        );
    }

}
