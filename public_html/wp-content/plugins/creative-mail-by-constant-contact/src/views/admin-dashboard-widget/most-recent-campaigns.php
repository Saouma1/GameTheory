<style>
	.ce4wp-campaigns {
		margin: 0 -12px;
	}
	.ce4wp-campaigns .dashicons {
		color: #606a73;
	}
	.ce4wp-campaigns__item {
		display: flex;
		background-color: #fafafa;
		padding: 6px 12px;
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

	.ce4wp-campaigns__item__section {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		justify-content: center;
	}

	.ce4wp-campaigns__item__section + .ce4wp-campaigns__item__section {
		margin-left: 12px;
	}

	.ce4wp-campaigns__item__section.ce4wp-campaigns__item__section--grow {
		flex: 1;
	}

	.ce4wp-campaigns__item__title {
		text-decoration: none;
	}
	.ce4wp-campaigns__item__title:hover {
		text-decoration: underline;
		cursor: pointer;
	}

	p.ce4wp-campaigns__item__subtitle {
		margin: 0;
	}

	.no-decoration {
		text-decoration: none;
	}
</style>

<h3>
	<?php esc_html_e( 'Your recent campaigns', 'creative-mail-by-constant-contact' ); ?>
</h3>
<section class="ce4wp-campaigns">
	<?php
	// @phpstan-ignore-next-line
	foreach ( $ce_most_recent_campaigns as $campaign ) {
		?>
		<div class="ce4wp-campaigns__item">
			<section class="ce4wp-campaigns__item__section">
				<?php if ( $campaign->is_draft ) { ?>
					<span class="dashicons dashicons-edit"></span>
				<?php } else { ?>
					<span class="dashicons dashicons-email"></span>
				<?php } ?>
			</section>
			<section class="ce4wp-campaigns__item__section ce4wp-campaigns__item__section--grow">
				<?php if ( $campaign->is_draft ) { ?>
				<a class="ce4wp-campaigns__item__title" onclick="ce4wpNavigateToDashboard(this, 'c182bb37-9cef-4962-a706-7fa14ffef01e', { campaignId: '<?php echo esc_attr( $campaign->id ); ?>', source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
					<strong><?php echo esc_html( $campaign->name ); ?></strong>
				</a>
				<?php } else { ?>
					<a class="ce4wp-campaigns__item__title" onclick="ce4wpNavigateToDashboard(this, 'bd38068c-329b-4c9f-9b2d-fb03a9278bbb', { campaignId: '<?php echo esc_attr( $campaign->id ); ?>', source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
						<strong><?php echo esc_html( $campaign->name ); ?></strong>
					</a>
				<?php } ?>
				<p class="ce4wp-campaigns__item__subtitle">
					<?php echo esc_html( $campaign->status ); ?>
				</p>
			</section>
			<?php if ( isset( $campaign->open_rate ) ) { ?>
				<section class="ce4wp-campaigns__item__section">
					<p>
						<?php esc_html_e( 'Open Rate', 'creative-mail-by-constant-contact' ); ?>:
						<strong><?php echo esc_html( $campaign->open_rate ); ?></strong>%
					</p>
				</section>
			<?php } ?>
		</div>
	<?php } ?>
</section>
<section class="ce4wp-campaign-actions">
	<button class="button button-primary" onclick="ce4wpNavigateToDashboard(this, '93b1417d-2efb-406d-a9a6-aa8af8f813a3', { source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
		<?php esc_html_e( 'Create a new campaign', 'creative-mail-by-constant-contact' ); ?>
	</button>
	<button class="button" onclick="ce4wpNavigateToDashboard(this, '5166faec-1dbb-4434-bad0-bb2f75898f92', { source: 'dashboard_widget' }, ce4wpWidgetStartCallback, ce4wpWidgetFinishCallback)">
		<?php esc_html_e( 'View all campaigns', 'creative-mail-by-constant-contact' ); ?>
	</button>
</section>
