<?php

namespace NewfoldLabs\WP\Module\Marketplace;

use NewfoldLabs\WP\ModuleLoader\Container;

/**
 * Class for handling the initialization of the marketplace module.
 */
class Marketplace {

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container The plugin container.
	 */
	public function __construct( Container $container ) {

		$this->container = $container;

		// Module functionality goes here
		add_action( 'rest_api_init', array( MarketplaceApi::class, 'registerRoutes' ) );
		add_action( 'wp_loaded', array( Themes::class, 'init' ) );
		add_action( 'wp_loaded', array( PluginsMarketplace::class, 'init' ) );
	}

}
