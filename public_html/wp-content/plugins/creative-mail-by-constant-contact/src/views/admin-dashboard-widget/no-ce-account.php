<div style="display: flex;">
	<section style="flex: 1;">
		<p style="margin-top: 0;">
			<?php esc_html_e('Our intelligent email editor makes it easy to create professional emails to engage your audience.', 'creative-mail-by-constant-contact'); ?>
		</p>
		<button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, 'd25f690a-217a-4d68-9c58-8693965d4673', { source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
			<?php esc_html_e("Let's go!", 'creative-mail-by-constant-contact'); ?>
		</button>
	</section>
	<img
		src="<?php echo esc_url( CE4WP_PLUGIN_URL . 'assets/images/admin-dashboard-widget/creative-mail.png' ); ?>"
		style="margin-top: -11px; margin-right: -12px; height: 10em;"
		alt="<?php esc_attr_e('Creative Mail Dashboard Widget image.', 'creative-mail-by-constant-contact'); ?>"
	/>
</div>
