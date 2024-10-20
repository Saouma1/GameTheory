<?php

/**
 * Class MyPlugin_Blocks
 */
namespace CreativeMail\Blocks;

class LoadBlock {
	private static $instance;

	public function __construct() {
	}

	/**
	 * Add the blocks hooks.
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'create_blocks' ) );
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets, so they can be enqueued
	 * through the block editor in the corresponding context
	 */
	public function create_blocks() {
		register_block_type_from_metadata(__DIR__ . '/subscribe' );
	}

	/**
	 * Returns the instance of the LoadBlock class.
	 *
	 * @return LoadBlock
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new LoadBlock();
		}

		return self::$instance;
	}
}
