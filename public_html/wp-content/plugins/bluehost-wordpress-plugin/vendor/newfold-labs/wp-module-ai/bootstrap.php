<?php

use NewfoldLabs\WP\Module\AI\AI;
use NewfoldLabs\WP\ModuleLoader\Container;

use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {
			// Set Global Constants
			if ( ! defined( 'NFD_MODULE_AI_DIR' ) ) {
				define( 'NFD_MODULE_AI_DIR', __DIR__ );
			}

			if ( ! defined( 'NFD_AI_SERVICE_BASE' ) ) {
				define( 'NFD_AI_SERVICE_BASE', 'https://hiive.cloud/workers/ai-proxy/v1/' );
			}

			register(
				[
					'name'     => 'ai',
					'label'    => __( 'ai', 'newfold-ai-module' ),
					'callback' => function ( Container $container ) {
						return new AI( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				]
			);

		}
	);

}
