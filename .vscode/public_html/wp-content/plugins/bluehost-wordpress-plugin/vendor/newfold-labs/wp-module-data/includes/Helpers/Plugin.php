<?php

namespace NewfoldLabs\WP\Module\Data\Helpers;

/**
 * Helper class for gathering and formatting plugin data
 */
class Plugin {
	/**
	 * Prepare plugin data for a single plugin
	 *
	 * @param string $slug Name of the plugin
	 *
	 * @return array of data for plugin
	 */
	public static function collect( $slug ) {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require wp_normalize_path( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		return self::get_data( $slug, get_plugin_data( WP_PLUGIN_DIR . '/' . $slug ) );
	}

	/**
	 * Prepare plugin data for all plugins
	 *
	 * @return array of plugins
	 */
	public static function collect_installed() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require wp_normalize_path( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		$plugins = array();

		// Collect standard plugins
		foreach ( get_plugins() as $slug => $data ) {
			array_push( $plugins, self::get_data( $slug, $data ) );
		}

		// Collect mu plugins
		foreach ( get_mu_plugins() as $slug => $data ) {
			array_push( $plugins, self::get_data( $slug, $data, true ) );
		}

		return $plugins;
	}

	/**
	 * Grab relevant data from plugin data - and only what we want
	 *
	 * @param array $slug The slug for the plugin
	 * @param array $data The plugin meta data from the header
	 * @param array $mu   Whether the plugin is installed as an mu
	 *
	 * @return array Hiive relevant plugin details
	 */
	public static function get_data( $slug, $data, $mu = false ) {
		$plugin                 = array();
		$plugin['slug']         = $slug;
		$plugin['version']      = $data['Version'] ? $data['Version'] : '0.0';
		$plugin['title']        = $data['Name'] ? $data['Name'] : '';
		$plugin['url']          = $data['PluginURI'] ? $data['PluginURI'] : '';
		$plugin['active']       = is_plugin_active( $slug );
		$plugin['mu']           = $mu;
		$plugin['auto_updates'] = ( ! $mu && self::does_it_autoupdate( $slug ) );

		return $plugin;
	}

	/**
	 * Whether the plugin is set to auto update
	 *
	 * @param string $slug Name of the plugin
	 *
	 * @return boolean
	 */
	public static function does_it_autoupdate( $slug ) {
		// Check plugin setting for auto updates on all plugins
		if ( get_site_option( 'auto_update_plugin', 'true' ) ) {
			return true;
		}

		// check core setting for auto updates on this plugin
		$wp_auto_updates = (array) get_site_option( 'auto_update_plugins', array() );

		return in_array( $slug, $wp_auto_updates, true );
	}
}
