<?php

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Staging\Staging;
use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			register(
				array(
					'name'     => 'staging',
					'label'    => __( 'Staging', 'newfold-staging-module' ),
					'callback' => function ( Container $container ) {
						return new Staging( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);

		}
	);

}
