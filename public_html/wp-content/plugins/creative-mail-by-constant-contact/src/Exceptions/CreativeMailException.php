<?php

namespace CreativeMail\Exceptions;

use Exception;

final class CreativeMailException extends Exception {

	/**
	 * Constructs the message of the CreativeMailException class.
	 *
	 * @param string $message Receives the message of the exception.
	 */
	public function __construct( string $message ) {
		parent::__construct( '[Creative Mail] ' . $message );
	}
}
