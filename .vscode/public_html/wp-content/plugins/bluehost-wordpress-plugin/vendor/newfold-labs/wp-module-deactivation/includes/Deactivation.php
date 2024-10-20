<?php
/**
 * Deactivation class file.
 *
 * @package NewfoldLabs\WP\Module\Deactivation
 */

namespace NewfoldLabs\WP\Module\Deactivation;

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Deactivation\DeactivationSurvey;

/**
 * Deactivation class.
 */
class Deactivation {

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

		// Plugin deactivation survey.
		add_action( 'admin_head-plugins.php', function () {
			new DeactivationSurvey();
		} );
	}
}
