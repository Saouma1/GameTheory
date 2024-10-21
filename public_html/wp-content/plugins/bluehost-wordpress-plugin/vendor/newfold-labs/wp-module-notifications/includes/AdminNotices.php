<?php

namespace NewFoldLabs\WP\Module\Notifications;

use wpscholar\Url;
use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Class AdminNotices
 */
class AdminNotices {

	/**
	 * Render admin notices where appropriate.
	 */
	public static function maybeRenderAdminNotices() {

		$screen = get_current_screen();

		// Bail if we're in the plugin app, since we already handle notifications in our React app.
		if ( false !== strpos( $screen->id, container()->plugin()->id ) ) {
			return;
		}

		// Handle realtime notifications
		if ( 'plugin-install' === $screen->id ) {
			?>
			<style>
				.newfold-realtime-notice {
					margin: 5px 0 15px 0;
				}
			</style>
			<?php
		}

		$page          = str_replace( admin_url(), '', Url::getCurrentUrl() );
		$notifications = new NotificationsRepository( false );
		$collection    = $notifications->collection();
		
		// Constant container for admin notices
		self::openContainer();

		if ( $collection->count() ) {
			$collection->each(
				function ( Notification $notification ) use ( $page ) {
					if ( $notification->shouldShow( 'wp-admin-notice', array( 'page' => $page ) ) ) {
						?>
						<div class="newfold-notice" data-id="<?php echo esc_attr( $notification->id ); ?>">
							<?php echo $notification->content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<?php
					}
				}
			);
		}

		self::closeContainer();
		
		self::adminScripts();

	}


	/**
	 * Open the notifactions container
	 */
	public static function openContainer() {
		echo wp_kses_post( '<div id="newfold-notifications" class="newfold-notifications-wrapper">' );
	}

	/**
	 * Close the notifications container
	 */
	public static function closeContainer() {
		echo wp_kses_post( '</div>' );
	}

	/**
	 * Handle scripts
	 */
	public static function adminScripts(){
		
		// Handle realtime notifications
		$screen = get_current_screen();
		if ( 'plugin-install' === $screen->id ) {
			// Enqueue and set local values for realtime script on plugin install page only
			wp_enqueue_script(
				'newfold-plugin-realtime-notices',
				plugins_url( 'vendor/newfold-labs/wp-module-notifications/assets/js/realtime-notices.js', container()->plugin()->file ),
				array( 'lodash' ),
				container()->plugin()->version,
				true
			);
			wp_localize_script(
				'newfold-plugin-realtime-notices',
				'newfoldRealtimeNotices',
				array(
					'restApiUrl'   => esc_url_raw( rest_url() ),
					'restApiNonce' => wp_create_nonce( 'wp_rest' ),
				)
			);
		}

		// Enqueue and set local values for dismiss script
		wp_enqueue_script(
			'newfold-dismiss-notices',
			plugins_url( 'vendor/newfold-labs/wp-module-notifications/assets/js/dismiss-notices.js', container()->plugin()->file ),
			array(),
			container()->plugin()->version,
			true
		);
		wp_localize_script(
			'newfold-dismiss-notices',
			'newfoldNotices',
			array(
				'restApiUrl'   => esc_url_raw( rest_url() ),
				'restApiNonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

}
