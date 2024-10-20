<?php
$ce4wp_weekly_notice_nonce = wp_create_nonce( 'ce4wp-weekly-notice-nonce' );
?>
<div class="updated">
	<p>
		<?php esc_html_e( 'Awesome, you\'ve been using', 'creative-mail-by-constant-contact' ); ?>
		<a href="admin.php?page=creativemail">
			<?php esc_html_e( 'Creative Mail', 'creative-mail-by-constant-contact' ); ?>
		</a>
		<?php esc_html_e( 'for more than 1 week. May we ask you to give it a 5-star rating on WordPress?', 'creative-mail-by-constant-contact' ); ?>
		| <a href="
				<?php echo esc_attr('https://wordpress.org/plugins/creative-mail-by-constant-contact/'); ?>
			" target="_blank">
			<?php esc_html_e( 'Ok, you deserved it', 'creative-mail-by-constant-contact' ); ?>
		</a>
		| <a href="<?php echo esc_attr('?ce4wp-ignore-notice=0&ce4wp-weekly-notice-nonce=' . $ce4wp_weekly_notice_nonce); ?>">
			<?php esc_html_e( 'I already did', 'creative-mail-by-constant-contact' ); ?>
		</a>
		| <a href="<?php echo esc_attr('?ce4wp-ignore-notice=0&ce4wp-weekly-notice-nonce=' . $ce4wp_weekly_notice_nonce); ?>">
			<?php esc_html_e( 'No, not good enough', 'creative-mail-by-constant-contact' ); ?>
		</a>
	</p>
</div>
