<div style="display: flex;">
	<section style="flex: 1;">
		<p style="margin-top: 0;">
			<?php esc_html_e( 'Easily manage and brand all of your important transactional WooCommerce store emails.', 'creative-mail-by-constant-contact' ); ?>
		</p>
		<button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, '1fabdbe2-95ed-4e1e-a2f3-ba0278f5096f', { source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
			<?php esc_html_e( "Let's go!", 'creative-mail-by-constant-contact' ); ?>
		</button>
	</section>
	<img
		src="<?php echo esc_url( CE4WP_PLUGIN_URL . 'assets/images/admin-dashboard-widget/no-woocommerce.svg' ); ?>"
		style="height: 8em; margin-left: 1em;"
		alt="
		<?php
		esc_attr_e(
			'Creative Mail WooCommerce logo and envelope with a truck on the right corner.',
			'creative-mail-by-constant-contact' );
		?>
			"
	/>
</div>
