<?php

namespace CreativeMail\Integrations;

use CreativeMail\Modules\Contacts\Handlers\BaseContactFormPluginHandler;

/**
 * Class Integration
 *
 * Describes an integration between Creative Mail and WordPress.
 *
 * @package CreativeMail\Integrations
 */
final class Integration {

	/**
	 * The display name of the plugin.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The path the plugin class that should be used to check if the plugin required for this integration is installed.
	 *
	 * @var string
	 */
	private $class;

	/**
	 * The class that should be instantiated when this integration gets activated.
	 *
	 * @var string
	 */
	private $integrationHandler;

	/**
	 * The slug that you want to use for this integration.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * If you want to hide this plugin from the suggestion list, set this to true.
	 *
	 * @var bool
	 */
	private $hide_from_suggestions;

	/**
	 * The link to the plugin store, default will set it based on the slug.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * If true the plugin will search using the basename (some plugins have different directories depending on license).
	 *
	 * @var bool
	 */
	private $has_multiple_plugins;

	/**
	 * If you want to hide this plugin from the active plugins list, set this to true.
	 *
	 * @var bool
	 */
	private $hide_from_active_list;

	/**
	 * Integration constructor.
	 *
	 * @param string      $slug                The slug that you want to use for this integration.
	 * @param string      $name                The display name of the plugin.
	 * @param string      $class               The path the plugin class that should be used to check if the plugin required for this integration is installed.
	 * @param string      $integration_handler The class that should be instantiated when this integration gets activated.
	 * @param bool        $hide_from_suggestions If you want to hide this plugin from the suggestion list, set this to true.
	 * @param string|null $url            The link to the plugin store, default will set it based on the slug.
	 * @param bool        $has_multiple_plugins  If true the plugin will search using the basename (some plugins have different directories depending on license).
	 * @param bool        $hide_from_active_list If you want to hide this plugin from the active plugins list, set this to true.
	 */
	public function __construct(
		string $slug,
		string $name,
		string $class,
		string $integration_handler,
		bool $hide_from_suggestions,
		?string $url = null,
		bool $has_multiple_plugins = false,
		bool $hide_from_active_list = false
	) {
		$this->slug                  = $slug;
		$this->name                  = $name;
		$this->class                 = $class;
		$this->integrationHandler    = $integration_handler;
		$this->hide_from_suggestions = $hide_from_suggestions;
		$this->url                   = is_null($url) ? admin_url("plugin-install.php?tab=plugin-information&plugin=$slug&TB_iframe=true&width=772&height=1144") : $url;
		$this->has_multiple_plugins  = $has_multiple_plugins;
		$this->hide_from_active_list = $hide_from_active_list;
	}

	/**
	 * Gets the slug assigned to this integration.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Gets the display name assigned to this integration.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Gets the path to the main class of the plugin that is required for this integration.
	 *
	 * @return string
	 */
	public function get_class(): string {
		return $this->class;
	}

	/**
	 * Gets the name of the class that should be instantiated when activating this integration.
	 *
	 * @return string
	 */
	public function get_integration_handler(): string {
		return $this->integrationHandler;
	}

	/**
	 * Gets if this integration should be hidden from the suggestion list
	 *
	 * @return bool
	 */
	public function is_hidden_from_suggestions(): bool {
		return $this->hide_from_suggestions;
	}

	/**
	 * Gets if this integration should be hidden from the active plugins list
	 *
	 * @return bool
	 */
	public function is_hidden_from_active_list(): bool {
		return $this->hide_from_active_list;
	}

	/**
	 * Gets the market url of the plugin
	 *
	 * @return string
	 */
	public function get_url(): string {
		return $this->url;
	}

	/**
	 * Use basename if integration has multiple plugins with different directories
	 *
	 * @return bool
	 */
	public function use_basename(): bool {
		return $this->has_multiple_plugins;
	}
}
