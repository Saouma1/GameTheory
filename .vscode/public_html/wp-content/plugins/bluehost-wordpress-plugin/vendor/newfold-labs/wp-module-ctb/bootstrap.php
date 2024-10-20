<?php

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\CTB\CTB;
use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			register(
				array(
					'name'     => 'ctb',
					'label'    => __( 'ctb', 'newfold-ctb-module' ),
					'callback' => function ( Container $container ) {
						return new CTB( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);

		}
	);

}
