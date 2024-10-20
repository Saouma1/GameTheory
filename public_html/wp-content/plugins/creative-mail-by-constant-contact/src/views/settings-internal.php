<?php
use CreativeMail\Helpers\EnvironmentHelper;
?>

<div class="ce4wp-card">
	<div class="ce4wp-px-4 ce4wp-py-4">
		<h2 class="ce4wp-typography-root ce4wp-typography-h2 ce4wp-mb-2">
			<?php esc_html_e( 'Technical details', 'creative-mail-by-constant-contact' ); ?>
		</h2>

		<div class="ce4wp-kvp">
			<h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2">
				<?php esc_html_e( 'Instance UUID', 'creative-mail-by-constant-contact' ); ?>

			</h4>
			<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
				<?php
				// @phpstan-ignore-next-line
					echo esc_html($this->instance_uuid)
				?>
			</p>
		</div>

		<div class="ce4wp-kvp">
			<h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2">
				<?php esc_html_e( 'Instance Id', 'creative-mail-by-constant-contact' ); ?>
			</h4>
			<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
				<?php
				// @phpstan-ignore-next-line
					echo esc_html($this->instance_id)
				?>
			</p>
		</div>

		<div class="ce4wp-kvp">
			<h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2">
				<?php esc_html_e( 'Environment', 'creative-mail-by-constant-contact' ); ?>
			</h4>
			<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
				<?php echo esc_html(EnvironmentHelper::get_environment()); ?>
			</p>
		</div>

		<div class="ce4wp-kvp">
			<h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2">
				<?php esc_html_e( 'Plugin version', 'creative-mail-by-constant-contact' ); ?>
			</h4>
			<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
				<?php echo esc_html(CE4WP_PLUGIN_VERSION) . '.' . esc_html(CE4WP_BUILD_NUMBER); ?>

			</p>
		</div>

		<div class="ce4wp-kvp">
			<h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2">
				<?php esc_html_e( 'App', 'creative-mail-by-constant-contact' ); ?>
			</h4>
			<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
				<?php echo esc_js(EnvironmentHelper::get_app_url()); ?>

			</p>
		</div>

		<div class="ce4wp-kvp">
			<h4 class="ce4wp-typography-root ce4wp-typography-h4 ce4wp-mb-2">
				<?php esc_html_e( 'App Gateway', 'creative-mail-by-constant-contact' ); ?>
			</h4>
			<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
				<?php echo esc_js(EnvironmentHelper::get_app_gateway_url()); ?>

			</p>
		</div>
	</div>
</div>
