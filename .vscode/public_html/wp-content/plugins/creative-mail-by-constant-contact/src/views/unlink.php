<div class="ce4wp-kvp">
	<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
		<?php esc_html_e( 'Your WordPress instance is connected to your Creative Mail account. If you would like to
		unlink your WordPress instance from your account, please click the "Unlink" button below.', 'creative-mail-by-constant-contact' ); ?>
		<strong>
			<?php esc_html_e('Unlinking your account is permanent and cannot be undone.', 'creative-mail-by-constant-contact' ); ?>
		</strong>
	</p>
</div>

<div class="ce4wp-kvp">
	<form name="disconnect" action="" method="post">
		<input name="disconnect_nonce" type="hidden" value="<?php echo esc_attr(wp_create_nonce('disconnect')); ?>" />
		<input name="action" type="hidden" value="disconnect" />
		<input id="disconnect-instance"
				name="disconnect_button"
				type="submit"
				class="ce4wp-button-text-primary destructive ce4wp-right"
				value="Unlink"
				onclick="return confirm(
					<?php
						esc_html_e(
							'Are you sure you want to unlink your CreativeMail account from your WordPress site? This action is permanent and cannot be undone.',
							'creative-mail-by-constant-contact'
						);
						?>
					"
		/>
	</form>
</div>
