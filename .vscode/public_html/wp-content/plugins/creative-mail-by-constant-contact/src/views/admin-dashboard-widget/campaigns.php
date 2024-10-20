<style>
.ce4wp-campaigns {
	margin: 0 -12px;
}

.ce4wp-campaigns__item {
	display: flex;
	justify-content: space-between;
	background-color: #fafafa;
	padding: 0 12px;
	border-top: 1px solid #ddd;
}

.ce4wp-campaigns__item:last-of-type {
	border-bottom: 1px solid #ddd;
}

.ce4wp-campaigns__item p {
	margin: 0.5em 0;
}

.ce4wp-campaign-actions {
	margin-top: 12px;
}
</style>

<h3>
	<?php esc_html_e( 'Your recent campaigns', 'creative-mail-by-constant-contact' ); ?>
</h3>
<section class="ce4wp-campaigns">
	<div class="ce4wp-campaigns__item">
		<p>
			<?php esc_html_e('Active:', 'creative-mail-by-constant-contact' ); ?>
		</p>
		<p>
			<strong>8</strong>
		</p>
	</div>
	<div class="ce4wp-campaigns__item">
		<p>
			<?php esc_html_e( 'Inactive:', 'creative-mail-by-constant-contact' ); ?>
		</p>
		<p>
			<strong>2</strong>
		</p>
	</div>
</section>
<section class="ce4wp-campaign-actions">
	<button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, '93b1417d-2efb-406d-a9a6-aa8af8f813a3', { source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
		<?php esc_html_e( 'Create a new campaign', 'creative-mail-by-constant-contact' ); ?>
	</button>
	<button class="button" onclick="ce4wpNavigateToDashboard(this, '5166faec-1dbb-4434-bad0-bb2f75898f92', { source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
		<?php esc_html_e( 'View all campaigns', 'creative-mail-by-constant-contact' ); ?>
	</button>
</section>
