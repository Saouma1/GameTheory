<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors Jetpack events
 */
class Jetpack extends Listener {

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Connected
		add_action( 'jetpack_site_registered', array( $this, 'connected' ), 10, 3 );

		// Module enabled/disabled
		add_action( 'jetpack_pre_activate_module', array( $this, 'module_enabled' ) );
		add_action( 'jetpack_pre_deactivate_module', array( $this, 'module_disabled' ) );

		// Publicize
		add_action( 'publicize_save_meta', array( $this, 'publicize' ), 10, 4 );
	}

	/**
	 * Jetpack connected
	 *
	 * @param integer        $id Jetpack Site ID
	 * @param string         $secret Jetpack blog token
	 * @param integer|boolan $public Whether the site is public
	 * @return void
	 */
	public function connected( $id, $secret, $public ) {
		$this->push(
			'jetpack_connected',
			array(
				'id'     => $id,
				'public' => $public,
			)
		);
	}

	/**
	 * Jetpack module enabled
	 *
	 * @param string $module Name of the module
	 * @return void
	 */
	public function module_enabled( $module ) {
		$this->push( 'jetpack_module_enabled', array( 'label_key' => 'module', 'module' => $module ) );
	}

	/**
	 * Jetpack module disabled
	 *
	 * @param string $module Name of the module
	 * @return void
	 */
	public function module_disabled( $module ) {
		$this->push( 'jetpack_module_disabled', array( 'label_key' => 'module', 'module' => $module ) );
	}

	/**
	 * Post publicized
	 *
	 * @param bool    $submit_post Whether to submit the post
	 * @param integer $post_id ID of the post being publicized
	 * @param string  $service_name Service name
	 * @param array   $connection Array of connection details
	 * @return void
	 */
	public function publicize( $submit_post, $post_id, $service_name, $connection ) {
		// Bail if it's not being publicized
		if ( ! $submit_post ) {
			return;
		}
		$this->push( 'jetpack_publicized', array( 'label_key' => 'service', 'service' => $service_name ) );
	}
}
