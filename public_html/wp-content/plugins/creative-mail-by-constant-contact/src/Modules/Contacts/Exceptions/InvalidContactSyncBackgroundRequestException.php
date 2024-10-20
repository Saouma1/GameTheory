<?php

namespace CreativeMail\Modules\Contacts\Exceptions;

use Exception;

class InvalidContactSyncBackgroundRequestException extends Exception {

	public function __construct( $message ) {
		parent::__construct('[CreativeMail - Contact sync] ' . $message);
	}
}
