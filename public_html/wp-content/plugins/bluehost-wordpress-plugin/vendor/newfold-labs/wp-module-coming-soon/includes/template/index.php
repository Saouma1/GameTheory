<?php
header('Cache-Control: no-store, no-cache, must-revalidate');
/**
 * This template renders a coming soon page when the coming soon feature is active.
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta name="viewport" content="width=device-width">
		<meta name="robots" content="noindex, nofollow" />
		<title><?php echo esc_html( $args['template_page_title'] ); ?></title>
		<script src="<?php echo esc_url( includes_url( 'js/jquery/jquery.js' ) ); //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>"></script>
		<style type="text/css"><?php echo file_get_contents( 'default.css', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
		<?php if ( isset( $args['template_styles'] ) ) : ?>
			<link rel="stylesheet" href="<?php echo esc_url( $args['template_styles'] ); //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>" />
		<?php endif; ?>
	</head>
	<body>
		<div id="wrap">
			<main class="content">
				<?php if ( isset( $args['template_content'] ) && is_readable( $args['template_content'] ) ) : // check for custom template_constant ?>
					<?php load_template( $args['template_content'], true, $args ); ?>
				<?php else : // no template provided use default ?>
				<div class="subscription_widget">
					<h1><?php echo wp_kses_post( $args['template_h1'] ); ?></h1>
					<h2><?php echo wp_kses_post( $args['template_h2'] ); ?></h2>
					<?php if ( isset( $args['template_login_btn'] ) && true === $args['template_login_btn'] )  : // admin login button ?>
						<a class="btn" href="<?php echo esc_url( wp_login_url() ); ?>">
							<?php esc_html_e( 'Admin Login', 'newfold-module-coming-soon' ); ?>
						</a>
					<?php endif; // end admin login button ?>
					<?php if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'subscriptions' ) ) : ?>
						<div id="subscribe-text">
							<p><?php echo esc_html( $args['template_p'] ); ?></p>
						</div>
						<div id="success" class="status-message">
							<?php echo esc_html( $args['template_msg_success'] ); ?>
						</div>
						<div id="error-active" class="status-message">
							<?php echo esc_html( $args['template_msg_active'] ); ?>
						</div>
						<div id="error-invalid" class="status-message">
							<?php echo esc_html( $args['template_msg_invalid'] ); ?>
						</div>
						<form action="" method="post" accept-charset="utf-8" id="subscribe">
							<input type="hidden" name="action" value="newfold_coming_soon_subscribe">
							<?php wp_nonce_field( 'newfold_coming_soon_subscribe_nonce', 'newfold-nonce-coming-soon-subscribe' ); ?>
							<span class="inputs email" id="subscribe-email">
								<label id="subscribe-label" for="subscribe-field">
									<?php echo esc_html( $args['template_email_label'] ); ?>
								</label>
								<input
									type="email"
									name="email"
									required="required"
									value=""
									id="subscribe-field"
									placeholder="<?php echo esc_attr( $args['template_email_ph'] ); ?>"
								>
							</span>
							<span class="inputs submit" id="subscribe-submit">
								<input class="btn" type="submit" value="<?php echo esc_attr( $args['template_subscribe'] ); ?>" name="subscriptions_widget">
							</span>
						</form>
						<script>
							var ajaxscript = { 
								ajax_url: '<?php echo esc_url( admin_url() ); ?>admin-ajax.php'
							};
							<?php echo file_get_contents( 'script.js', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</script>
					<?php endif; // end subscribe form ?>
				</div>
				<?php endif; // end template ?>
			</main>
		</div>
		<footer>
			<p class="text-center">
				<?php echo wp_kses_post( $args['template_footer_t'] ); ?>
			</p>
		</footer>
	</body>
</html>
