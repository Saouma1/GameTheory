<?php

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;

if ( isset ( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset ( $_POST['action'] ) ) {
	if ( 'disconnect' === $_POST['action']
		&& isset( $_POST['disconnect_nonce'] )
		&& wp_verify_nonce( sanitize_text_field ( wp_unslash ( $_POST['disconnect_nonce'] ) ), 'disconnect' ) ) {
		OptionsHelper::clear_options(true);
		// @phpstan-ignore-next-line
		$this->instance_id = null;
	}

	if ( 'change_activated_plugins' === $_POST['action']
		&& isset ( $_POST['activated_plugins_nonce'] )
		&& wp_verify_nonce(
			sanitize_text_field ( wp_unslash ( $_POST['activated_plugins_nonce'] ) ), 'activated_plugins' ) ) {
		$activated_plugins = array();
		if ( isset($_POST['activated_plugins']) ) {
			$activated_plugins = array_map( 'sanitize_text_field', wp_unslash( $_POST['activated_plugins'] ) );
		}
		CreativeMail::get_instance()->get_integration_manager()->set_activated_plugins($activated_plugins);
	}

	if ( 'change_marketing_information' === $_POST['action']
		&& isset ( $_POST['marketing_consent_nonce'] )
		&& wp_verify_nonce(
			sanitize_text_field( wp_unslash($_POST['marketing_consent_nonce']) ), 'marketing_consent' ) ) {
		if ( array_key_exists( 'ce4wp_show_marketing_checkbox', $_POST )
			&& sanitize_key( $_POST['ce4wp_show_marketing_checkbox'] ) === 'on' ) {
			OptionsHelper::set_checkout_checkbox_enabled('1');
		} else {
			OptionsHelper::set_checkout_checkbox_enabled('0');
		}
		OptionsHelper::set_checkout_checkbox_text(
			sanitize_text_field( wp_unslash( $_POST['ce4wp_checkbox_text'] ?? '' ) )
		);
	}
}

	$contact_sync_available     = ! empty(CreativeMail::get_instance()->get_integration_manager()->get_active_plugins());
	$supported_plugin_available = ! empty(CreativeMail::get_instance()->get_integration_manager()->get_supported_integrations(true))
?>

<div class="ce4wp-admin-wrapper">
	<header class="ce4wp-swoosh-header"></header>

	<div class="ce4wp-swoosh-container">
		<div style="margin-top: 0;">
			<div class="ce4wp-backdrop">
				<div class="ce4wp-backdrop-container">
					<div class="ce4wp-backdrop-header">
						<div class="ce4wp-logo-poppins"></div>
						<div>
							<img src="<?php echo esc_url( CE4WP_PLUGIN_URL . 'assets/images/airplane.svg' ); ?>" class="ce4wp-airplane" alt="Paper airplane decoration">
						</div>
					</div>

					<div class="ce4wp-card">
						<div class="ce4wp-px-4 ce4wp-py-4">
							<h2 class="ce4wp-typography-root ce4wp-typography-h2 ce4wp-mb-2">
								<?php esc_html_e( 'Creative Mail does it all!', 'creative-mail-by-constant-contact' ); ?>
							</h2>

							<?php
							if ( OptionsHelper::get_instance_id() ) {
								include 'unlink.php';
							} else {
								include 'pending-setup.php';
							}
							?>
						</div>
					</div>
					<?php
					if ( OptionsHelper::get_instance_id() ) {
						include 'contact-sync.php';
					}
					if ( EnvironmentHelper::is_test_environment() ) {
						include 'settings-internal.php';
					}
					?>

					<div class="ce4wp-card">
						<div class="ce4wp-px-4 ce4wp-py-4">
							<h2 class="ce4wp-typography-root ce4wp-typography-h2 ce4wp-mb-2">
								<?php esc_html_e( 'Customer Email Marketing', 'creative-mail-by-constant-contact' ); ?>
							</h2>

							<form name="plugins" action="" method="post">
								<input name="marketing_consent_nonce"
									type="hidden"
									value="<?php echo esc_html(wp_create_nonce('marketing_consent')); ?>"
								/>
								<input type="hidden" name="action" value="change_marketing_information" />
								<table class="form-table">
									<tbody>
										<tr>
											<td class="forminp forminp-text ce4wp-px-0">
												<label class="ce4wp-checkbox">
													<input type="checkbox" name="ce4wp_show_marketing_checkbox" <?php echo esc_attr ( OptionsHelper::get_checkout_checkbox_enabled() == '1' ? 'checked' : '' ); ?> />
													<span class="ce4wp-typography-root ce4wp-body2">
														<?php esc_html_e('Yes I want to ask my customers in the WooCommerce Checkout for consent to send marketing emails.', 'creative-mail-by-constant-contact'); ?>
													</span>
												</label>
											</td>
										</tr>
										<tr>
											<td class="forminp forminp-text ce4wp-px-0">
												<label for="ce4wp_checkbox_text">
													<span class="ce4wp-typography-root ce4wp-body2">
														<?php esc_html_e('Consent Text', 'creative-mail-by-constant-contact'); ?>
													<span>
												</label>
												<br />
												<input type="text"
														name="ce4wp_checkbox_text"
														value="<?php echo esc_attr(stripslashes(OptionsHelper::get_checkout_checkbox_text())); ?>" />
											</td>
										</tr>
									</tbody>
								</table>

								<div class="ce-kvp">
									<input id="save_customer_information"
											name="save_button"
											type="submit"
											class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary ce4wp-mt-2"
											value="Save" />
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
