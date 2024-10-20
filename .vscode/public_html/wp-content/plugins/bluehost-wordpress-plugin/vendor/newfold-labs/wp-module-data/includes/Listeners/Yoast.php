<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors Yoast events
 */
class Yoast extends Listener {
	// We don't want to track these fields
	private $skip_fields = [ 'company_logo_id', 'person_logo_id', 'description' ];

	// The names used for Hiive events tracking are different from the names used for the Yoast options
	private $map         = [
		'company_or_person'         => 'site_representation',
		'company_name'              => 'organization_name',
		'company_logo'              => 'organization_logo',
		'person_logo'               => 'logo',
		'company_or_person_user_id' => 'name',
		'website_name'              => 'website_name',
	];

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		// First time configuration
		add_action('wpseo_ftc_post_update_site_representation', array( $this, 'site_representation_updated' ), 10, 3 );
		add_action('wpseo_ftc_post_update_social_profiles', array( $this, 'site_representation_updated' ), 10, 3 );
		add_action('wpseo_ftc_post_update_enable_tracking', array( $this, 'tracking_updated' ), 10, 3 );
	}

	/**
	 * The user just updated their site representation
	 *
	 * @param array $new_values The new values for the options related to the site representation
	 * @param array $old_values The old values for the options related to the site representation
	 * @param array $failures   The failures that occurred during the update
	 *
	 * @return void
	 */
	public function site_representation_updated( $new_values, $old_values, $failures ) {
		// All the options are unchanged, opt out
		if ( $new_values === $old_values ) {
			return;
		}

		$mapped_new_values = $this->map_site_representation_params_names_to_hiive_names( $new_values );
		$mapped_old_values = $this->map_site_representation_params_names_to_hiive_names( $old_values );
		$mapped_failures   = $this->map_site_representation_failures_to_hiive_names( $failures );
		
		foreach ($mapped_new_values as $key => $value) {
			$this->maybe_push_event( $key, $value, $mapped_old_values[ $key ], \in_array( $key, $mapped_failures ), 'ftc_site_representation' );
		}
	}

	/**
	 * The user just updated their personal profiles
	 *
	 * @param array $new_values The new values for the options related to the site representation
	 * @param array $old_values The old values for the options related to the site representation
	 * @param array $failures   The failures that occurred during the update
	 *
	 * @return void
	 */
	public function personal_profiles_updated( $new_values, $old_values, $failures ) {
		// All the options are unchanged, opt out
		if ( $new_values === $old_values ) {
			return;
		}

		foreach ($new_values as $key => $value) {
			$this->maybe_push_event( $key, $value, $old_values[ $key ], \in_array( $key, $failures ), 'ftc_personal_profiles' );
		}
	}

	/**
	 * The user updated their tracking preference
	 *
	 * @param string $new_value The new value for the option related to tracking
	 * @param string $old_value The old value for the option related to tracking
	 * @param bool   $failed    Whether the option update failed
	 *
	 * @return void
	 */
	public function tracking_updated( $new_value, $old_value, $failed ) {
		$category = 'ftc_tracking';

		if ( $failed ) {
			$this->push( "failed_usage_tracking", [ 'category' => $category ] );
			return;
		}


		// All the options are unchanged, opt out
		if ( $new_value !== $old_value ) {
			$this->push( "changed_usage_tracking", [ 'category' => $category ] );
		}
	}

	/**
	 * A method used to (maybe) push an event to the queue
	 *
	 * @param string $key       The option key
	 * @param string $value     The new option value
	 * @param string $old_value The old option value
	 * @param bool   $failure   Whether the option update failed
	 * @param string $category  The category of the event
	 *
	 * @return void
	 */
	private function maybe_push_event( $key, $value, $old_value, $failure, $category ) {
		// The option update failed
		if ( $failure ) {
			$this->push( "failed_$key", [ 'category' => $category] );
			return;
		}

		// The option value changed
		if ( $value !== $old_value ) {
			// The option was set for the first time
			if (strlen($old_value) === 0 ) {
				$this->push( "set_$key", [ 'category' => $category] );
				return;
			}

			// The option was updated
			$data = array(
				'category' => $category,
				'data'     => array(
					'label_key' => $key,
					'new_value' => $value
				),
			);

			$this->push(
				"changed_$key",
				$data
			);
		}
	}

	/**
	 * Maps the param names to the names used for Hiive events tracking.
	 *
	 * @param array $params The params to map.
	 *
	 * @return array The mapped params.
	 */
	private function map_site_representation_params_names_to_hiive_names( $params ) {
		$mapped_params = [];

		foreach ( $params as $param_name => $param_value ) {
			if ( in_array( $param_name, $this->skip_fields, true ) ) {
				continue;
			}

			$new_name                   = $this->map[ $param_name ];
			$mapped_params[ $new_name ] = $param_value;
		}

		return $mapped_params;
	}

	/**
	 * Maps the names of the params which failed the update to the names used for Hiive events tracking.
	 *
	 * @param array $failures The params names to map.
	 *
	 * @return array The mapped params names.
	 */
	private function map_site_representation_failures_to_hiive_names( $failures ) {
		$mapped_failures = [];

		foreach ( $failures as $failed_filed_name) {
			if ( in_array( $failed_filed_name, $this->skip_fields, true ) ) {
				continue;
			}

			$mapped_failures = $this->map[ $failed_filed_name ];
		}

		return $mapped_failures;
	}
}
