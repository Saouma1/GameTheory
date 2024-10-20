<?php


namespace CreativeMail\Helpers;

use CreativeMail\Managers\Logs\DatadogManager;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Exception;

final class EncryptionHelper {

	/**
	 * Will get the previously used encryption key, or will generate a new key of no key is present.
	 *
	 * @return Key
	 * @throws BadFormatException
	 * @throws EnvironmentIsBrokenException
	 */
	private static function get_encryption_key(): Key {
		$key = get_option(CE4WP_ENCRYPTION_KEY_KEY, null);

		if ( null === $key ) {
			$key = Key::createNewRandomKey();

			update_option(CE4WP_ENCRYPTION_KEY_KEY, $key->saveToAsciiSafeString());
		} else {
			$key = Key::loadFromAsciiSafeString($key);
		}

		return $key;
	}

	/**
	 * Will update an existing option or create the option if it is not available.
	 *
	 * @param string $option    The name of the option.
	 * @param mixed  $value      The value that should be stored encrypted.
	 * @param bool   $autoload    Should this option be autoloaded.
	 *
	 * @return bool
	 * @throws BadFormatException
	 * @throws EnvironmentIsBrokenException
	 */
	public static function update_option( string $option, $value, ?bool $autoload = null ): bool {
		return update_option($option, Crypto::encrypt($value, self::get_encryption_key()), $autoload);
	}

	/**
	 * Will store and encrypt the option.
	 *
	 * @param string $option   string The name of the option.
	 * @param mixed  $value    mixed  The value that should be stored encrypted.
	 * @param bool   $autoload bool Should this option be autoloaded.
	 *
	 * @throws BadFormatException
	 * @throws EnvironmentIsBrokenException
	 */
	public static function add_option( string $option, $value, bool $autoload = true ): void {
		add_option($option, Crypto::encrypt($value, self::get_encryption_key()), '', $autoload);
	}

	/**
	 * Will load and decrypt the option.
	 *
	 * @param string $option The name of the option you want to load.
	 * @param bool   $default The fallback value that should be used when the option is not available.
	 *
	 * @return mixed
	 */
	public static function get_option( string $option, bool $default = false ) {
		$encrypted = get_option($option, $default);

		if ( $encrypted === $default ) {
			return $default;
		} else {
			try {
				if ( is_string($encrypted) ) {
					return Crypto::decrypt($encrypted, self::get_encryption_key());
				}
			} catch ( Exception $e ) {
				DatadogManager::get_instance()->exception_handler($e);
			}
		}

		return $encrypted;
	}

	public static function generate_x_builder_id() {
		$builder_id = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', wp_rand(0, 65535), wp_rand(0, 65535), wp_rand(0, 65535), wp_rand(16384, 20479), wp_rand(32768, 49151), wp_rand(0, 65535), wp_rand(0, 65535), wp_rand(0, 65535));

		if ( function_exists('com_create_guid') === true ) {
			$builder_id = trim(com_create_guid(), '{}');
		}
		return $builder_id;
	}
}
