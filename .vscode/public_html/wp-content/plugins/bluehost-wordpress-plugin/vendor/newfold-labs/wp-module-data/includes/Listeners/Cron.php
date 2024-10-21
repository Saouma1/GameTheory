<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

use NewfoldLabs\WP\Module\Data\Helpers\Plugin;

/**
 * Schedules Cron event listeners
 */
class Cron extends Listener {

	/**
	 * Register all required hooks for the listener category
	 *
	 * @return void
	 */
	public function register_hooks() {

		// Ensure there is a weekly option in the cron schedules
		add_filter( 'cron_schedules', array( $this, 'add_weekly_schedule' ) );

		// Weekly cron hook
		add_action( 'nfd_data_cron', array( $this, 'update' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_data_cron' ) ) {
			wp_schedule_event( time() + DAY_IN_SECONDS, 'weekly', 'nfd_data_cron' );
		}

	}

	/**
	 * Cron event
	 *
	 * @return void
	 */
	public function update() {
		$data = array(
			'plugins' => Plugin::collect_installed(),
		);

		$data = apply_filters( 'newfold_wp_data_module_cron_data_filter', $data );

		$this->push( 'cron', $data );
	}

	/**
	 * Add the weekly option to cron schedules if it doesn't exist
	 *
	 * @param array $schedules List of cron schedule options
	 * @return array
	 */
	public function add_weekly_schedule( $schedules ) {
		if ( ! array_key_exists( 'weekly', $schedules ) || WEEK_IN_SECONDS !== $schedules['weekly']['interval'] ) {
			$schedules['weekly'] = array(
				'interval' => WEEK_IN_SECONDS,
				'display'  => __( 'Once Weekly' ),
			);
		}
		return $schedules;
	}
}
