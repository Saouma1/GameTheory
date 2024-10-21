<?php

namespace CreativeMail\Models;

class HashSchema {

	/**
	 * Hash key string
	 *
	 * @var string
	 */
	public $key;
	/**
	 * Hash salt string
	 *
	 * @var string
	 */
	public $salt;
	/**
	 * Hash secret string
	 *
	 * @var string
	 */
	public $secret;
	/**
	 * Hash version
	 *
	 * @var string
	 */
	public $version;
}
