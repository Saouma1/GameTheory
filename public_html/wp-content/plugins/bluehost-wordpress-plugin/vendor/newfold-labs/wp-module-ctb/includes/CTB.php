<?php
namespace NewfoldLabs\WP\Module\CTB;

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\CustomerBluehost\CustomerBluehost;
use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * This class adds click to buy functionality.
 **/
class CTB {

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;


	/**
	 * Constructor.
	 *
	 * @param Container $container The module container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;

		// Module functionality goes here
		add_action( 'rest_api_init', array( CTBApi::class, 'registerRoutes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'ctb_scripts' ) );
		add_action( 'admin_footer', array( $this, 'ctb_footer' ) );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @return void
	 */
	public function ctb_scripts() {
		$assetsDir = container()->plugin()->url . 'vendor/newfold-labs/wp-module-ctb/includes/assets/';

		// load the a11y dialog lib
		wp_register_script(
			'a11y-dialog',
			$assetsDir . 'a11y-dialog.min.js',
			array(),
			'7.4.0',
			false
		);

		// load ctb script
		wp_enqueue_script(
			'newfold-ctb',
			$assetsDir . 'ctb.js',
			array( 'a11y-dialog' ),
			container()->plugin()->version,
			true
		);

		// Calculate and add admin inline values
		$hasToken      = ! empty( get_option( 'nfd_data_token' ) );
		// $customerData  = container()->plugin()->customer;
		$customerData  = CustomerBluehost::collect();
		$hasCustomerId = ! empty( $customerData ) && ! empty( $customerData['customer_id'] );
		$supportsCTB   = $hasToken && $hasCustomerId;

		// Inline script for global vars for ctb
		wp_localize_script(
			'newfold-ctb', // script handle
			'nfdctb',      // js object
			array(
				'supportsCTB' => $supportsCTB,
			)
		);

		// Styles
		wp_enqueue_style(
			'newfold-ctb-style',
			$assetsDir . 'ctb.css',
			array(),
			container()->plugin()->version
		);
	}

	/**
	 * Add container to footer for modal components
	 *
	 * @return void
	 */
	public function ctb_footer() {
		echo "<div id='nfd-ctb-container' aria-hidden='true'></div>";
	}

}
