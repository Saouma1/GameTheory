<?php

namespace CreativeMail\Helpers;

use CreativeMail\Models\OptionsSchema;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;

/**
 * Class CE4WP_OptionsHelper
 * Exposes a wrapper around all the options that we register within the plugin.
 *
 * @package CreativeMail\Helpers
 * @access  private
 */
final class OptionsHelper {

	/**
	 * Gets the generated unique id for this WP instance, or will generate a new unique id if none is present.
	 *
	 * @return string
	 */
	public static function get_instance_uuid(): string {
		// Do we already have a UUID?
		$instanceUuid = get_option(CE4WP_INSTANCE_UUID_KEY, null);

		if ( null === $instanceUuid ) {
			// Just generate one and store it.
			$instanceUuid = uniqid();
			add_option(CE4WP_INSTANCE_UUID_KEY, $instanceUuid);
		} else {
			$instanceUuid = strval($instanceUuid);
		}

		return $instanceUuid;
	}

	/**
	 * Gets the generated handshake token that should be used during setup.
	 *
	 * @return string
	 */
	public static function get_handshake_token(): string {
		// Do we already have a UUID?
		$token      = get_option(CE4WP_INSTANCE_HANDSHAKE_TOKEN, null);
		$expiration = self::get_handshake_expiration();

		if ( null === $token || null === $expiration || $expiration < time() ) {
			// No token is known, or it expired, generate a new one.
			$token = GuidHelper::generate_guid();

			update_option(CE4WP_INSTANCE_HANDSHAKE_TOKEN, $token);
			update_option(CE4WP_INSTANCE_HANDSHAKE_EXPIRATION, time() + 3600);
		}

		return $token;
	}

	/**
	 * Gets the expiration time associated with the generated handshake token.
	 *
	 * @return int|null
	 */
	public static function get_handshake_expiration(): ?int {
		$handshake_expiration = get_option(CE4WP_INSTANCE_HANDSHAKE_EXPIRATION, null);

		return ! empty($handshake_expiration) ? intval($handshake_expiration) : null;
	}

	/**
	 * Gets the consumer API key that can be used to interact with the Creative Mail platform.
	 *
	 * @return string|null
	 */
	public static function get_wc_consumer_key(): ?string {
		$wc_consumer_key = get_option(CE4WP_WC_API_CONSUMER_KEY, null);

		return is_string($wc_consumer_key) ? $wc_consumer_key : null;
	}

	/**
	 * Sets the consumer key that can be used to interact with the Creative Mail platform.
	 *
	 * @param string $value The consumer key that should be stored.
	 *
	 * @throws BadFormatException
	 * @throws EnvironmentIsBrokenException
	 */
	public static function set_wc_consumer_key( string $value ): void {
		EncryptionHelper::add_option(CE4WP_WC_API_CONSUMER_KEY, $value);
	}

	/**
	 * Deletes the consumer key.
	 *
	 * @return bool
	 */
	public static function delete_wc_consumer_key(): bool {
		return delete_option(CE4WP_WC_API_CONSUMER_KEY);
	}

	/**
	 * Gets the assigned api key id.
	 *
	 * @return int|null
	 */
	public static function get_wc_api_key_id(): ?int {
		$wc_api_key_id = get_option(CE4WP_WC_API_KEY_ID, null);

		return ! empty($wc_api_key_id) && is_numeric($wc_api_key_id) ? (int) $wc_api_key_id : null;
	}

	/**
	 * Sets the assigned api key id that is generated when connecting this WP instance to the Creative Mail account.
	 *
	 * @param int $value The api key id that should be stored.
	 */
	public static function set_wc_api_key_id( int $value ): void {
		add_option(CE4WP_WC_API_KEY_ID, $value);
	}

	/**
	 * Deletes the api key id.
	 *
	 * @return bool
	 */
	public static function delete_wc_api_key_id(): bool {
		return delete_option(CE4WP_WC_API_KEY_ID);
	}

	/**
	 * Gets the assigned instance id.
	 *
	 * @return int|null
	 */
	public static function get_instance_id(): ?int {
		return get_option(CE4WP_INSTANCE_ID_KEY, null);
	}

	/**
	 * Sets the assigned instance id that is generated when connecting this WP instance to the Creative Mail account.
	 *
	 * @param int $value The instance id that should be stored.
	 */
	public static function set_instance_id( int $value ): void {
		add_option(CE4WP_INSTANCE_ID_KEY, $value);
	}

	/**
	 * Gets the assigned checkbox text.
	 *
	 * @return string
	 */
	public static function get_checkout_checkbox_text(): string {
		return get_option(CE4WP_CHECKOUT_CHECKBOX_TEXT, "Yes, I'm ok with you sending me additional newsletter and email content");
	}

	/**
	 * Sets the assigned checkout checkbox text.
	 *
	 * @param string $value The checkout checkbox text that should be stored.
	 */
	public static function set_checkout_checkbox_text( string $value ): void {
		update_option(CE4WP_CHECKOUT_CHECKBOX_TEXT, $value);
	}

	/**
	 * Sets the assigned checkout checkbox enabled.
	 *
	 * @param string $value The checkout checkbox enabled that should be stored.
	 */
	public static function set_checkout_checkbox_enabled( string $value ): void {
		if ( '0' != $value && '1' != $value ) {
			return;
		}

		update_option(CE4WP_CHECKOUT_CHECKBOX_ENABLED, $value);
	}

	/**
	 * Gets the  assigned checkout checkbox enabled value
	 *
	 * @return int|bool
	 */
	public static function get_checkout_checkbox_enabled() {
		return get_option(CE4WP_CHECKOUT_CHECKBOX_ENABLED, '1');
	}

	/**
	 * Gets the id of the account that is connected to the combination of this WP unique id and Creative Mail account id.
	 *
	 * @return int|null
	 */
	public static function get_connected_account_id(): ?int {
		return get_option(CE4WP_CONNECTED_ACCOUNT_ID, null);
	}

	/**
	 * Sets the id of the account that is connected to the combination of this WP unique id and Creative Mail account id.
	 *
	 * @param int $value The account id that should be stored.
	 */
	public static function set_connected_account_id( int $value ): void {
		add_option(CE4WP_CONNECTED_ACCOUNT_ID, $value);
	}

	/**
	 * Gets the API key that can be used to interact with the Creative Mail platform.
	 *
	 * @return string
	 */
	public static function get_instance_api_key(): string {
		return EncryptionHelper::get_option(CE4WP_INSTANCE_API_KEY_KEY);
	}

	/**
	 * Sets the API key that can be used to interact with the Creative Mail platform.
	 *
	 * @param string $value The API key that should be stored.
	 *
	 * @throws BadFormatException
	 * @throws EnvironmentIsBrokenException
	 */
	public static function set_instance_api_key( string $value ): void {
		EncryptionHelper::add_option(CE4WP_INSTANCE_API_KEY_KEY, $value);
	}

	/**
	 * Gets a string representing all the plugins that were activated for synchronization during the setup process.
	 *
	 * @return string|array
	 */
	public static function get_activated_plugins() {
		return get_option(CE4WP_ACTIVATED_PLUGINS, array());
	}

	/**
	 * Sets a string representing all the plugins that were activated for synchronization during the setup process.
	 *
	 * @param mixed $plugins The plugins that should be stored.
	 */
	public static function set_activated_plugins( $plugins ): void {
		update_option(CE4WP_ACTIVATED_PLUGINS, $plugins);
	}

	/**
	 * Get managed email notification array or string
	 *
	 * @return array<OptionsSchema>
	 */
	public static function get_managed_email_notifications(): array {
		global $wpdb;

		$rows   = $wpdb->get_results($wpdb->prepare("SELECT option_name, option_value FROM $wpdb->options WHERE option_name like %s", CE4WP_MANAGED_EMAIL_NOTIFICATIONS . '%'));
		$result = array();

		foreach ( $rows as $row ) {
			$name = $row->option_name;
			if ( CE4WP_MANAGED_EMAIL_NOTIFICATIONS === $name ) {
				// Convert old to new format.
				return self::convert_managed_email_notifications($row->option_value);
			}

			$item         = new OptionsSchema();
			$item->name   = str_replace(CE4WP_MANAGED_EMAIL_NOTIFICATIONS . '_', '', $name);
			$item->active = 'true' == $row->option_value;

			$result[] = $item;
		}

		return $result;
	}

	/**
	 * One time converts the email notifications to the new format.
	 *
	 * @param mixed $items The items that should be converted.
	 *
	 * @return array
	 */
	private static function convert_managed_email_notifications( $items ): array {
		$items = maybe_unserialize($items);

		if ( empty($items) ) {
			return array();
		}

		$result = array();

		foreach ( $items as $item ) {
			if ( property_exists($item, 'name') ) {
				self::set_managed_email_notification($item->name, $item->active ? 'true' : 'false');
				array_push($result, $item);
			}
		}

		delete_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS);

		return $result;
	}

	/**
	 * Deletes all the email notifications options
	 */
	private static function delete_managed_email_notifications(): void {
		$managed_notifications = self::get_managed_email_notifications();

		foreach ( $managed_notifications as $item ) {
			if ( property_exists($item, 'name') ) {
				delete_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS . '_' . $item->name);
			}
		}
	}

	/**
	 * Set managed email notification by name
	 *
	 * @param string $name The name of the email notification.
	 * @param bool   $active The active state of the email notification.
	 */
	public static function set_managed_email_notification( string $name, bool $active ): void {
		$is_active = var_export($active, true);
		update_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS . '_' . $name, $is_active);
	}

	/**
	 * Gets an int value representing when the user did accept the terms on our consent screen.
	 *
	 * @return int|null
	 */
	public static function get_consent_accept_date(): ?int {
		return get_option(CE4WP_ACCEPTED_CONSENT, null);
	}

	/**
	 * Sets the current time value indicated the user accepted the terms on the consent screen.
	 */
	public static function set_did_accept_consent(): void {
		update_option(CE4WP_ACCEPTED_CONSENT, time());
	}

	/**
	 * Gets a string value representing who referred this customer
	 *
	 * @return string|array
	 */
	public static function get_referred_by() {
		return get_option(CE4WP_REFERRED_BY, '');
	}

	/**
	 * Gets the hide banner option for the given banner.
	 *
	 * @param string $banner The banner to get the hide option for.
	 *
	 * @return bool
	 */
	public static function get_hide_banner( string $banner ): bool {
		return get_option(CE4WP_HIDE_BANNER . ':' . $banner, false);
	}

	/**
	 * Sets the hide banner option for the given banner.
	 *
	 * @param string $banner The banner to hide.
	 * @param bool   $hide  Whether the banner should be hidden or not.
	 */
	public static function set_hide_banner( string $banner, bool $hide = true ): void {
		$is_hidden = var_export($hide, true);
		update_option(CE4WP_HIDE_BANNER . ':' . $banner, $is_hidden);
	}

	/**
	 * Will clear all the registered options for this plugin.
	 * Only the Unique Id won't be cleared so that we can restore the link when the plugin is reactivated.
	 *
	 * @param bool $clear_all When set to 'true' the instance UUID will be re-generated, this will cause the link between the plugin and the user account to break.
	 */
	public static function clear_options( bool $clear_all ): void {
		delete_option(CE4WP_INSTANCE_ID_KEY);
		delete_option(CE4WP_INSTANCE_API_KEY_KEY);
		delete_option(CE4WP_CONNECTED_ACCOUNT_ID);
		delete_option(CE4WP_ACTIVATED_PLUGINS);
		delete_option(CE4WP_ACCEPTED_CONSENT);
		delete_option(CE4WP_WC_API_KEY_ID);
		delete_option(CE4WP_WC_API_CONSUMER_KEY);
		delete_option(CE4WP_INSTANCE_HANDSHAKE_TOKEN);
		delete_option(CE4WP_INSTANCE_HANDSHAKE_EXPIRATION);
		delete_option(CE4WP_MANAGED_EMAIL_NOTIFICATIONS);
		delete_option(CE4WP_CHECKOUT_CHECKBOX_TEXT);
		self::delete_managed_email_notifications();

		if ( true === $clear_all ) {
			delete_option(CE4WP_INSTANCE_UUID_KEY);
			delete_option(CE4WP_ENCRYPTION_KEY_KEY);
		}
	}
}
