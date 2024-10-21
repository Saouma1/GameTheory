<div id="ce4wp-admin-feedback-notice" class="notice notice-warning" hidden>
	<img class="icon" src="<?php echo esc_url( CE4WP_PLUGIN_URL . 'assets/images/airplane-purple.svg' ); ?>" />
	<section class="content">
		<p>
			<strong>
				<?php esc_html_e( 'Time for an audience shout out?', 'creative-mail-by-constant-contact' ); ?>
			</strong>
		</p>
		<p>
			<?php esc_html_e( 'These contacts are already in Creative Mail, send a quick campaign...', 'creative-mail-by-constant-contact' ); ?>
		</p>
	</section>
	<button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, 'd25f690a-217a-4d68-9c58-8693965d4673', { source: 'feedback_notice' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
		<?php esc_html_e( 'Get started', 'creative-mail-by-constant-contact' ); ?>
	</button>
	<span id="close" onclick="hideAdminFeedbackNotice('feedback_notice_few_contacts')"></span>
</div>
