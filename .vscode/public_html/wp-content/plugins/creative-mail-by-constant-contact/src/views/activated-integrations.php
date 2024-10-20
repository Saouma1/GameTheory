<?php

use CreativeMail\CreativeMail;
use CreativeMail\Helpers\EnvironmentHelper;

$available_integrations = CreativeMail::get_instance()->get_integration_manager()->get_active_plugins();
$activated_integrations = CreativeMail::get_instance()->get_integration_manager()->get_activated_integrations();
$activated_templates    = CreativeMail::get_instance()->get_email_manager()->get_managed_email_notifications();

?>

<script type="application/javascript">
	function showConsentModal () {
		var form = document.getElementById("activated_plugins_form");
		var checkboxes = form.querySelectorAll("input[type='checkbox']:checked");
		if (checkboxes.length > 0) {
			document.getElementById('consent-modal').style.display = "block";
		} else {
			submitForm();
		}
	}

	function closeConsentModal () {
		document.getElementById('consent-modal').style.display = "none";
	}

	function submitForm() {
		document.getElementById('consent-modal-activated-loader').classList.remove("ce4wp-hidden");
		document.getElementById('consent-modal-activated-content').style.display = "none";
		document.getElementById('activated_plugins_form').submit();
	}

	// Let customer know that contacts will be synced if WooCommerce email templates are enabled even if the integration is disabled.
	function showWooCommerceTemplateConsentModal() {
		var wooCommerceModal = document.getElementById('woocommerce-consent-modal');

		if (wooCommerceModal) {
			wooCommerceModal.style.display = "block";
		}
	}

	function closeWooCommerceTemplateConsentModal (activateCheckbox) {
		var wooCommerceModal = document.getElementById('woocommerce-consent-modal');
		var wooCommerceCheckbox = document.getElementById('activated-plugins-check-woocommerce');

		if (wooCommerceCheckbox && activateCheckbox === true) {
			document.getElementById('activated-plugins-check-woocommerce').checked = true;
		}
		if (wooCommerceModal) {
			document.getElementById('woocommerce-consent-modal').style.display = "none";
		}
	}

	function onChecked(slug){
		var card = document.getElementById('activated-plugins-' + slug);
		var checkbox = document.getElementById('activated-plugins-check-' + slug);

		if( card !== undefined && card !== null) {
			card.classList.toggle("ce4wp-selected")
		}
		if ( card.id === 'activated-plugins-woocommerce' && checkbox.checked === false) {
			showWooCommerceTemplateConsentModal();
		}
	}
</script>

<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
	<?php esc_html_e( 'Select one or more plugins to enable the synchronization of its contacts with Creative Mail.', 'creative-mail-by-constant-contact'); ?>
</p>
<br />
<form id="activated_plugins_form" name="plugins" action="" method="post">
	<input type="hidden" name="action" value="change_activated_plugins" />
	<input name="activated_plugins_nonce" type="hidden" value="<?php echo esc_html(wp_create_nonce('activated_plugins')); ?>" />

	<div style="color: rgba(0, 0, 0, 0.6);" class="ce4wp-grid">
		<?php
		foreach ( $available_integrations as $available_integration ) {
			if ( $available_integration->is_hidden_from_active_list() ) {
				continue;
			}
			$active       = in_array($available_integration, $activated_integrations, true);
			$checked      = true === $active ? 'checked' : '';
			$ce4wp_path   = '/assets/p/universal/wordpress-plugin/' . $available_integration->get_slug() . '.png';
			$plugin_image = EnvironmentHelper::get_app_url() . $ce4wp_path;

			echo '<div class="ce4wp-grid-item">
                        <div id="activated-plugins-' . esc_attr($available_integration->get_slug()) . '" class="ce4wp-settings-card" >
                            <label for="activated-plugins-check-' . esc_attr($available_integration->get_slug()) . '">
                                <div class="ce4wp-grid">
                                    <div class="ce4wp-grid-item ce4wp-grid-xs-2">
                                        <div class="ce4wp-settings-card-image" style="background-image: url(' . esc_attr($plugin_image) . ')" title="' . esc_attr($available_integration->get_slug()) . '"></div>
                                    </div>
                                    <div class="ce4wp-grid-item ce4wp-grid-xs-8">
                                            <span class="ce4wp-settings-card-title">' . esc_html($available_integration->get_name()) . '</span>
                                    </div>
                                    <div class="ce4wp-grid-item ce4wp-grid-xs-2"  style="line-height: 48px;">
                                    <label class="ce4wp-checkbox">
                                        <input onclick="onChecked(&quot;' . esc_attr($available_integration->get_slug()) . '&quot;)" type="checkbox" name="activated_plugins[]" id="activated-plugins-check-' . esc_attr($available_integration->get_slug()) . '" value="' . esc_attr($available_integration->get_slug()) . '" ' . esc_attr($checked) . ' />
                                        <span></span>
                                    </label>
                                    </div>
                                </div>
                            </label>
                        </div>
                </div>';
		}
		?>
</div>
	<div class="ce-kvp">
		<br />
		<input name="save_button" type="submit" class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary ce4wp-mt-2" id="save-activated-plugins" value="Save" onclick="showConsentModal(); return false;" />
	</div>

<!-- Consent modal -->
<div id="consent-modal" role="presentation" class="ce4wp-dialog-root" height="auto" variant="default" style="display: none;">
	<div class="ce4wp-backdrop-root" aria-hidden="true" style="opacity: 1; "></div>
	<div class="ce4wp-dialog-container" role="none presentation" tabindex="-1" style="opacity: 1; ">
		<div class="ce4wp-dialog-wrapper" role="dialog">
			<div width="100%" class="ce4wp-dialog-header">
				<div class="ce4wp-dialog-header-title">
					<div class="ce4wp-dialog-header-title-wrapper">
						<div class="ce4wp-dialog-header-title-wrapper-content">
							<h3 class="ce4wp-typography-root ce4wp-typography-h3">
								<?php esc_html_e( 'Yes, these contacts expect to hear from me', 'creative-mail-by-constant-contact'); ?>
							</h3>
						</div>
					</div>
				</div>
				<div class="ce4wp-dialog-header-close">
					<div class="ce4wp-dialog-header-close-wrapper" onclick="closeConsentModal()">
						<div class="ce4wp-dialog-header-close-wrapper-button">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="black" xmlns="http://www.w3.org/2000/svg">
								<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
							</svg>
						</div>
					</div>
				</div>
			</div>
			<div  id='consent-modal-activated-loader' height="auto" class="ce4wp-dialog-content  ce4wp-hidden">
				<div class="ce4wp-loader" role="progressbar" style="width: 40px; height: 40px;">
					<svg class="core-test-MuiCircularProgress-svg" viewBox="22 22 44 44">
						<circle class="core-test-MuiCircularProgress-circle core-test-MuiCircularProgress-circleIndeterminate"
									cx="44"
									cy="44" r="20.2"
									fill="none"
									stroke-width="3.6">
						</circle>
					</svg>
				</div>
			</div>
			<div id='consent-modal-activated-content'>
				<div height="auto" class="ce4wp-dialog-content">
					<div>
						<div class="ce4wp-pb-3">
							<span>
								<?php esc_html_e( 'Each time you add contacts, they must meet the following conditions.', 'creative-mail-by-constant-contact'); ?>
							</span>
						</div>
						<div class="ce4wp-consent">
							<div class="ce4wp-pb-3">
								<h4 class="ce4wp-typography-root ce4wp-typography-h4">
									<?php esc_html_e('I have the consent of each contact on my list', 'creative-mail-by-constant-contact'); ?>
								</h4>
								<span>
									<?php esc_html_e( 'You must have the prior consent of each contact added to your Newfold Digital account. Your account cannot contain purchased, rented, third party or appended lists. In addition, you may not add auto-response addresses, transactional addresses, or user group addresses.', 'creative-mail-by-constant-contact'); ?>
								</span>
							</div>
							<h4 class="ce4wp-typography-root ce4wp-typography-h4">
								<?php esc_html_e('I am not adding role addresses or distribution lists', 'creative-mail-by-constant-contact'); ?>
							</h4>
							<span>
								<?php esc_html_e( 'Role addresses, such as sales@ or marketing@, and distribution lists often mail to more than one person and result in higher than normal spam complaints. You must remove these from your list prior to upload.', 'creative-mail-by-constant-contact'); ?>
							</span>
						</div>
						<div class="ce4wp-pb-3">
							<span>
								<?php esc_html_e('Getting your email delivered is important to us. We may contact you to review your list before we send your email, if you add contacts that are likely to cause higher than normal bounces or for other reasons that we know may cause spam complaints. Thanks for helping to eliminate spam.', 'creative-mail-by-constant-contact'); ?>
							</span>
						</div>
					</div>
				</div>
				<div class="ce4wp-dialog-footer">
					<div class="ce4wp-dialog-footer-close">
						<div class="ce4wp-dialog-footer-close-wrapper">
							<button class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary" type="button" onclick="submitForm()" >
								<span class="MuiButton-label">
									<?php esc_html_e( 'Got it!', 'creative-mail-by-constant-contact'); ?>
								</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- WooCommerce Template Consent modal. -->
<?php
function get_active_options( $option ) {
	return true === $option->active;
}

if ( count(array_filter($activated_templates, 'get_active_options')) > 0 ) {
	echo '
    <div id="woocommerce-consent-modal" role="presentation" class="ce4wp-dialog-root" height="auto" variant="default" style="display: none;">
        <div class="ce4wp-backdrop-root" aria-hidden="true" style="opacity: 1; "></div>
        <div class="ce4wp-dialog-container" role="none presentation" tabindex="-1" style="opacity: 1; ">
            <div class="ce4wp-dialog-wrapper" role="dialog">
                <div width="100%" class="ce4wp-dialog-header">
                    <div class="ce4wp-dialog-header-title">
                        <div class="ce4wp-dialog-header-title-wrapper">
                            <div class="ce4wp-dialog-header-title-wrapper-content">
                                <h3 class="ce4wp-typography-root ce4wp-typography-h3">' . esc_html__('Disabling WooCommerce', 'creative-mail-by-constant-contact') . '</h3>
                            </div>
                        </div>
                    </div>
                    <div class="ce4wp-dialog-header-close">
                        <div class="ce4wp-dialog-header-close-wrapper" onclick="closeWooCommerceTemplateConsentModal(true)">
                            <div class="ce4wp-dialog-header-close-wrapper-button">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="black" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="consent-modal-activated-loader" height="auto" class="ce4wp-dialog-content  ce4wp-hidden">
                    <div class="ce4wp-loader" role="progressbar" style="width: 40px; height: 40px;">
                        <svg class="core-test-MuiCircularProgress-svg" viewBox="22 22 44 44">
                            <circle class="core-test-MuiCircularProgress-circle core-test-MuiCircularProgress-circleIndeterminate" cx="44" cy="44" r="20.2" fill="none" stroke-width="3.6"></circle>
                        </svg>
                    </div>
                </div>
                <div id="consent-modal-activated-content">
                    <div height="auto" class="ce4wp-dialog-content">
                        <div>
                            <div class="ce4wp-pb-3">
                                <span>' . esc_html__( 'Before you disable the WooCommerce integration, please keep in mind the following:', 'creative-mail-by-constant-contact') . '</span>
                            </div>
                            <div class="ce4wp-pb-3">
                                <span>' . esc_html__( 'If you have enabled CreativeMail to handle WooCommerce emails, contacts\' email addresses will continue to be synced.', 'creative-mail-by-constant-contact') . '</span>
                            </div>
                            <div class="ce4wp-pb-3">
                                <span>' . esc_html__( 'If you wish to stop contacts from being synced, please make sure to disable all WooCommerce emails from being handled by CreativeMail.', 'creative-mail-by-constant-contact') . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="ce4wp-dialog-footer">
                        <div class="ce4wp-dialog-footer-close">
                            <div class="ce4wp-dialog-footer-close-wrapper">
                                <button class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-button-contained-primary" type="button" onclick="closeWooCommerceTemplateConsentModal(false)">
                                    <span class="MuiButton-label">' . esc_html__('Got it!', 'creative-mail-by-constant-contact') . '</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        ';
}
?>
</form>

