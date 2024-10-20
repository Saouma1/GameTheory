<?php

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Marketplace\Marketplace;

use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			register(
				array(
					'name'     => 'marketplace',
					'label'    => __( 'Marketplace', 'newfold-module-marketplace' ),
					'callback' => function ( Container $container ) {
						new Marketplace( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);

		}
	);

}
