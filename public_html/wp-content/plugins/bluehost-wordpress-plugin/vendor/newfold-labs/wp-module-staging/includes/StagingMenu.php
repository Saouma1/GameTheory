<?php

namespace NewfoldLabs\WP\Module\Staging;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Class StagingMenu
 */
class StagingMenu {
	/**
	 * Initialize.
	 */
	public static function init() {
		// add admin menu
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_staging_toolbar_items' ) );
	}

	/**
	 * Customize the admin bar.
	 *
	 * @param \WP_Admin_Bar $admin_bar An instance of the WP_Admin_Bar class.
	 */
	public static function add_staging_toolbar_items( \WP_Admin_Bar $admin_bar ) {
		if ( current_user_can( 'manage_options' ) ) {

			if ( container()->get( 'isStaging' ) ) {
				$args = array(
					'id'    => 'newfold-staging',
					'href'  => admin_url( 'admin.php?page=' . container()->plugin()->id . '#/staging' ),
					'title' => '<div style="background-color: #ce0000; padding: 0 10px;color:#fff;">' . esc_html__( 'Staging Environment', 'newfold-staging' ) . '</div>',
					'meta'  => array(
						'title' => esc_attr__( 'Staging Actions', 'newfold-staging' ),
					),
				);
				$admin_bar->add_menu( $args );
			}
		}
	}

}
