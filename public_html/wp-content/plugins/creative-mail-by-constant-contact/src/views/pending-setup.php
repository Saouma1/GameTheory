<p class="ce4wp-typography-root ce4wp-body2 ce4wp-mt-3" style="color: rgba(0, 0, 0, 0.6);">
	<?php esc_html_e( 'Power your WooCommerce Store or WordPress Blog with our simple & free email marketing tool.
    With the official Creative Mail for WooCommerce plugin, your products, blog posts, images and store links
    are automatically included as rich shoppable email marketing content for your customers.', 'creative-mail-by-constant-contact' ); ?>
</p>
<p class="ce4wp-typography-root ce4wp-body2 ce4wp-mt-3" style="color: rgba(0, 0, 0, 0.6);">
	<?php
	esc_html_e( 'Our included CRM also intelligently pulls in and identifies your WordPress site contacts and WooCommerce store customers.
    That makes it easy to build audiences and send targeted customer campaigns.', 'creative-mail-by-constant-contact' );
	?>
</p>
<p class="ce4wp-typography-root ce4wp-body2 ce4wp-mt-3" style="color: rgba(0, 0, 0, 0.6);">
	<?php esc_html_e( 'Get free email marketing, 98% deliverability, and the rock solid reliability all without ever needing to leave your WP Admin.', 'creative-mail-by-constant-contact' ); ?>
</p>
<p class="ce4wp-typography-root ce4wp-body2 ce4wp-mt-3" style="color: rgba(0, 0, 0, 0.6);">
	<?php esc_html_e( 'Having problems setting up your account, click the \'reset\' button below to start over.', 'creative-mail-by-constant-contact' ); ?>
</p>
<div class="ce4wp-kvp">
	<form name="disconnect" action="" method="post">
		<input name="disconnect_nonce" type="hidden" value="<?php echo esc_html(wp_create_nonce('disconnect')); ?>" />
		<input type="hidden" name="action" value="disconnect" />
		<input id="disconnect-instance"
				name="disconnect_button"
				type="submit"
				class="ce4wp-button-text-primary destructive ce4wp-right"
				value="Reset" />
	</form>
</div>
