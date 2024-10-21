<style>
.ce4wp-woocommerce {
	margin: 0 -12px -12px;
}

.ce4wp-woocommerce__item {
	display: flex;
	justify-content: space-between;
	background-color: #fafafa;
	padding: 0 12px;
	border-top: 1px solid #ddd;
}

.ce4wp-woocommerce__item p {
	margin: 0.5em 0;
}
</style>

<h3>
	<?php esc_html_e( 'Transactional WooCommerce email', 'creative-mail-by-constant-contact' ); ?>
</h3>
<section class="ce4wp-woocommerce">
	<div class="ce4wp-woocommerce__item">
		<p>
			<?php esc_html_e( 'Active', 'creative-mail-by-constant-contact' ); ?>:
		</p>
		<p>
			<strong>
				<?php
				// @phpstan-ignore-next-line
				echo esc_attr( $number_of_active_notifications );
				?>
			</strong>
		</p>
	</div>
	<div class="ce4wp-woocommerce__item">
		<p><?php esc_html_e( 'Inactive', 'creative-mail-by-constant-contact' ); ?>:</p>
		<p>
			<strong>
				<?php
				// @phpstan-ignore-next-line
				echo esc_attr( $number_of_possible_notifications - $number_of_active_notifications );
				?>
			</strong>
		</p>
	</div>
</section>
