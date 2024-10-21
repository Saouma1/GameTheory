<?php declare(strict_types = 1);

namespace CreativeMail\Helpers;

/**
 * Class GuidHelper
 *
 * @package CreativeMail\Helpers
 */
final class GuidHelper {

	/**
	 * Generates a GUID.
	 *
	 * @return string
	 */
	public static function generate_guid(): string {
		return sprintf(
			'%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
			wp_rand(0, 65535),
			wp_rand(0, 65535),
			wp_rand(0, 65535),
			wp_rand(16384, 20479),
			wp_rand(32768, 49151),
			wp_rand(0, 65535),
			wp_rand(0, 65535),
			wp_rand(0, 65535)
		);
	}
}
