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
						<div class="ce4wp-px-4 ce4wp-pt-4">
							<h1 class="ce4wp-typography-root ce4wp-typography-h1 ce4wp-inline-block ce4wp-mb-3">
								<?php esc_html_e( 'Intelligent email marketing for', 'creative-mail-by-constant-contact'); ?>
									<br>
								<?php esc_html_e( 'WordPress and WooCommerce', 'creative-mail-by-constant-contact'); ?>
							</h1>
							<?php
							if ( in_array('password-protected/password-protected.php', apply_filters('active_plugins', get_option('active_plugins')), true)
								&& (bool) get_option( 'password_protected_status' ) ) {
								include 'password-protected-notice.php';
							} else {
								include 'dashboard-open-creative-mail.php';
							}
							?>
							<div id="ce4wpskeleton" style="display: none;">
								<div class="ce4wp-button-base-root ce4wp-button-root ce4wp-button-contained ce4wp-mt-2 skeleton-pulse" style="width: 300px; color: #8C8C8C;">
									<span class="ce4wp-button-label" style="width: 100%;">
										<?php esc_html_e( 'Loading your account...', 'creative-mail-by-constant-contact'); ?>
									</span>
								</div>
								<div class="ce4wp-typography-root ce4wp-typography-h6 ce4wp-mt-4 ce4wp-mb-3 skeleton-pulse ce4wp-subapps-skeleton"></div>
								<div class="ce4wp-grid ce4wp-mt-3">
									<div class="ce4wp-grid-item">
										<div class="ce4wp-grid-item-card ce4wp-mb-4">
											<div class="ce4wp-grid-item-card-media skeleton-pulse ce4wp-grid-item-card-media-skeleton"></div>
											<div class="ce4wp-grid-item-card-content-root skeleton-pulse">
												<div class="ce4wp-grid-item-card-content-skeleton-title"></div>
												<div class="ce4wp-grid-item-card-content-skeleton-description"></div>
											</div>
										</div>
									</div>
									<div class="ce4wp-grid-item">
										<div class="ce4wp-grid-item-card ce4wp-mb-4">
											<div class="ce4wp-grid-item-card-media skeleton-pulse ce4wp-grid-item-card-media-skeleton"></div>
											<div class="ce4wp-grid-item-card-content-root skeleton-pulse">
												<div class="ce4wp-grid-item-card-content-skeleton-title"></div>
												<div class="ce4wp-grid-item-card-content-skeleton-description"></div>
											</div>
										</div>
									</div>
									<div class="ce4wp-grid-item">
										<div class="ce4wp-grid-item-card ce4wp-mb-4">
											<div class="ce4wp-grid-item-card-media skeleton-pulse ce4wp-grid-item-card-media-skeleton"></div>
											<div class="ce4wp-grid-item-card-content-root skeleton-pulse">
												<div class="ce4wp-grid-item-card-content-skeleton-title"></div>
												<div class="ce4wp-grid-item-card-content-skeleton-description"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
