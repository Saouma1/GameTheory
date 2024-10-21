<?php

namespace CreativeMail\Models;

class ApiSchema {

	/**
	 * Key used to identify the consumer
	 *
	 * @var string
	 */
	public $consumer_key;
	/**
	 * Secret used for the consumer
	 *
	 * @var string
	 */
	public $consumer_secret;
	/**
	 * ID of the key
	 *
	 * @var int
	 */
	public $key_id;
	/**
	 * ID of the user
	 *
	 * @var int
	 */
	public $user_id;
}
