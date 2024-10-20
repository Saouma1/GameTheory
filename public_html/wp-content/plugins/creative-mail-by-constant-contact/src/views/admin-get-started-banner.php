<script>
function hideAdminGetStartedBanner () {
	document.querySelector('.notice-ce4wp-getting-started').hidden = true;
	fetch('<?php // @phpstan-ignore-next-line
				echo esc_html($ce_hide_banner_url);
	?>', { method: 'POST' })
}
</script>

<style>
.notice-ce4wp-getting-started {
	padding: 1em 3em 1.5em 2em;
	display: flex;
	position: relative;
	overflow: hidden;
}

.notice-ce4wp-getting-started .content {
	flex: 1;
}

.notice-ce4wp-getting-started .content p {
	margin-top: 0;
}

.notice-ce4wp-getting-started img {
	margin: -1em 0 -3em;
	align-self: flex-start;
}

.notice-ce4wp-getting-started #close {
	position: absolute;
	top: .25em;
	right: .25em;
	font-size: 2em;
	user-select: none;
	cursor: pointer;
	color: rgba(0, 0, 0, .5);
}

.notice-ce4wp-getting-started[hidden] {
	display: none !important;
}
</style>

<div class="notice notice-warning notice-ce4wp-getting-started">
	<section class="content">
		<h1>
			<strong>
				<?php esc_html_e( 'Grow your business with Creative Mail', 'creative-mail-by-constant-contact'); ?>
			</strong>
		</h1>
		<p>
			<?php esc_html_e( 'Our intelligent email editor makes it easy to create a professional email.', 'creative-mail-by-constant-contact'); ?>
		</p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=creativemail' ) ); ?>">
			<button class="button button-primary">
				<?php esc_html_e( 'Start free', 'creative-mail-by-constant-contact'); ?>
			</button>
		</a>
	</section>
	<img src="<?php echo esc_url(CE4WP_PLUGIN_URL . 'assets/images/admin-get-started-banner.svg'); ?>" />
	<span id="close" onclick="hideAdminGetStartedBanner()">&#10005;</span>
</div>
