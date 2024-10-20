<?php
/**
 * Business Reviews module bootstrap.
 *
 * Provides functionality to add Business Reviews to WP Pro accounts.
 * A new WordPress Widget becomes available for use inside the WordPress Admin.
 * (must be enabled via bluerock)
 *
 * @package Newfold\WP\Module\BusinessReviews
 */
use NewfoldLabs\WP\ModuleLoader\Container;
use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			register(
				[
					'name'     => 'business-reviews',
					'label'    => __( 'Business Reviews', 'newfold-business-reviews' ),
					'callback' => function ( Container $container ) {
						require __DIR__ . '/business-reviews.php';
					},
					'isActive' => false,
					'isHidden' => true,
				]
			);

		}
	);

}
