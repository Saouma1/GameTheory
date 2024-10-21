<script type="application/javascript">
	function ce4wpShowPluginModal() {
		document.getElementById('ce4wp-show-me-how-modal').style.display = "block";
	}

	function ce4wpClosePluginModal() {
		document.getElementById('ce4wp-show-me-how-modal').style.display = "none";
	}
</script>
<div class="ce4wp-banner">
	<div class="ce4wp-banner-image">
		<img src="<?php echo esc_url( CE4WP_PLUGIN_URL . 'assets/images/banner-image_collect.svg' ); ?>" alt="Creative mail form is here">
	</div>
	<div class="ce4wp-content">
		<h2>
			<?php esc_html_e( 'The Creative Mail form is here!', 'creative-mail-by-constant-contact' ); ?>
		</h2>
		<p class="ce4wp-typography-body">
			<?php esc_html_e( 'Contacts that sign up can now be assigned to a contact list automatically. Add the Creative Mail block anywhere on your site.', 'creative-mail-by-constant-contact' ); ?>
			<a class="ce4wp_bold_link" onclick="ce4wpShowPluginModal()">
			<?php esc_html_e( 'Show me how!', 'creative-mail-by-constant-contact' ); ?>
			</a>
		</p>
	</div>
</div>
<!-- show me how modal -->
<div id="ce4wp-show-me-how-modal"
		role="presentation"
		class="ce4wp-dialog-root ce4wp-show-me-how-modal"
		height="auto"
		variant="default"
		style="display: none;">
	<div class="ce4wp-backdrop-root" aria-hidden="true" style="opacity: 1; "></div>
	<div class="ce4wp-dialog-container" role="none presentation" tabindex="-1" style="opacity: 1; ">
		<div class="ce4wp-dialog-wrapper" style="max-width: 700px" role="dialog">
			<div width="100%" class="ce4wp-dialog-header ce4wp-show-me-how-header" style='background-image: url("<?php echo esc_url( CE4WP_PLUGIN_URL . 'assets/images/post-boxes.svg' ); ?>")'>
				<div class="ce4wp-dialog-header-close">
					<div class="ce4wp-dialog-header-close-wrapper" onclick="ce4wpClosePluginModal()">
						<div class="ce4wp-dialog-header-close-wrapper-button">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="black"
									xmlns="http://www.w3.org/2000/svg">
								<path
									d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
							</svg>
						</div>
					</div>
				</div>
			</div>
			<div height="auto" class="ce4wp-dialog-content">
				<div class="ce4wp-d-flex ">
					<div>
						<img class="ce4wp-show-me-how-screenshot" src="<?php echo esc_url( CE4WP_PLUGIN_URL . 'assets/images/screenshot-creative-mail-form.png' ); ?>" alt="Creative Mail screenshot">
					</div>
					<div class="ce4wp-ml-6">
						<p class="ce4wp-typography-body">
							<?php esc_html_e( 'Adding a subscription form to your WordPress site is easy. Actually we’ve already prepared a form for you.', 'creative-mail-by-constant-contact' ); ?>
						</p>
						</br>
						<p class="ce4wp-typography-body">
							<?php
							esc_html_e( 'Just go to any page on your site in the WordPress editor and click the “Add block” button. Select the
                                Creative Mail contact form from the block recipes and you’re set.', 'creative-mail-by-constant-contact' );
							?>
						</p>
						</br>
						<p class="ce4wp-typography-body">
							<?php esc_html_e( 'It’s that easy.', 'creative-mail-by-constant-contact' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
