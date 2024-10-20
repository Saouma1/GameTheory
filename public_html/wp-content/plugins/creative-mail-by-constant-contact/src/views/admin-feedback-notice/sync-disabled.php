<div id="ce4wp-admin-feedback-notice" class="notice notice-warning" hidden>
	<span class="icon dashicons dashicons-groups"></span>
	<section class="content">
		<p>
			<strong>
				<?php esc_html_e( 'Should we sync your contacts with', 'creative-mail-by-constant-contact' ); ?>
				<img class="ce-logo" src="<?php echo esc_url(CE4WP_PLUGIN_URL . 'assets/images/admin-dashboard-widget/logo.svg'); ?>" />
				?
			</strong>
		</p>
		<p>
			<?php esc_html_e( 'Grow your business or blog with the power of email marketing.', 'creative-mail-by-constant-contact' ); ?>
		</p>
	</section>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=creativemail_settings' ) ); ?>">
		<button class="button button-primary">
			<?php esc_html_e( 'Sync my contacts', 'creative-mail-by-constant-contact' ); ?>
		</button>
	</a>
	<span id="close" onclick="hideAdminFeedbackNotice('feedback_notice_sync_disabled')"></span>
</div>
