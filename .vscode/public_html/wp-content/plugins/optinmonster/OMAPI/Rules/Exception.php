<?php
/**
 * OMAPI_Rules_Exception class.
 *
 * @since 1.5.0
 *
 * @package OMAPI
 * @author  Justin Sternberg
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rules exception base class.
 *
 * @since 1.5.0
 */
class OMAPI_Rules_Exception extends Exception {
	/**
	 * Whether the exception is a boolean.
	 *
	 * @var bool
	 */
	protected $bool = null;

	/**
	 * An array of exceptions.
	 *
	 * @var array
	 */
	protected $exceptions = array();

	/**
	 * Constructor.
	 *
	 * @param string    $message  Exception message.
	 * @param int       $code     Exception code.
	 *
	 * @param Exception $previous Previous exception.
	 */
	public function __construct( $message = null, $code = 0, Exception $previous = null ) {
		if ( is_bool( $message ) ) {
			$this->bool = $message;
			$message    = null;
		}

		if ( $previous ) {
			$this->add_exceptions( $previous );
		}

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Get boolean.
	 *
	 * @return bool
	 */
	public function get_bool() {
		return $this->bool;
	}

	/**
	 * Add exceptions.
	 *
	 * @param array|object $exceptions The array exceptions.
	 *
	 * @return void
	 */
	public function add_exceptions( $exceptions ) {
		$this->exceptions = array_merge(
			$this->exceptions,
			is_array( $exceptions ) ? $exceptions : array( $exceptions )
		);
	}

	/**
	 * Get exceptions.
	 *
	 * @return array
	 */
	public function get_exceptions() {
		return (array) $this->exceptions;
	}

	/**
	 * Get exception messages.
	 *
	 * @return array
	 */
	public function get_exception_messages() {
		$messages = array();
		foreach ( $this->get_exceptions() as $e ) {
			$messages[] = $e->getMessage();
		}

		return $messages;
	}
}
