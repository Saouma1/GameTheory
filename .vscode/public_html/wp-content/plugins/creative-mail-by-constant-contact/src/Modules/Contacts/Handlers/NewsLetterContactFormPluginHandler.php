<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_NL_EVENTTYPE', 'WordPress - NewsLetter');

use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use Exception;

final class NewsLetterContactFormPluginHandler extends BaseContactFormPluginHandler {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Converts to contact model.
	 *
	 * @param object $user The user.
	 *
	 * @return ContactModel
	 * @throws Exception
	 */
	public function convertToContactModel( $user ) {
		$contactModel = new ContactModel();
		$contactModel->setEventType(CE4WP_NL_EVENTTYPE);
		$contactModel->setOptIn(true);
		$contactModel->setOptActionBy(OptActionBy::VISITOR);

		$email = $user->email;

		if ( ! empty($email) ) {
			$contactModel->setEmail($email);
		}

		$name = $user->name;

		if ( ! empty($name) ) {
			$contactModel->setFirstName($name);
		}

		$surname = $user->surname;

		if ( ! empty($surname) ) {
			$contactModel->setLastName($surname);
		}

		return $contactModel;
	}

	/**
	 * Handles the contact form submission.
	 *
	 * @param object $user The user.
	 *
	 * @return void
	 */
	public function ceHandleContactNewsletterSubmit( $user ) {
		try {
			$this->upsertContact($this->convertToContactModel($user));
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	public function registerHooks() {
		add_action('newsletter_user_confirmed', array( $this, 'ceHandleContactNewsletterSubmit' ));
	}

	public function unregisterHooks() {
		remove_action('newsletter_user_confirmed', array( $this, 'ceHandleContactNewsletterSubmit' ));
	}

	/**
	 * Get all the contacts.
	 *
	 * @param ?int $limit The limit of contacts to be sent.
	 *
	 * @return array|null
	 */
	public function get_contacts( $limit = null ) {
		global $wpdb;

		$backfillArray = array();

		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}

		if ( null != $limit ) {
			$result = $wpdb->get_results($wpdb->prepare('select * from wp_newsletter order by id desc LIMIT %d', $limit));
		} else {
			$result = $wpdb->get_results($wpdb->prepare('select * from wp_newsletter order by id desc'));
		}

		if ( isset($result) && ! empty($result) ) {
			foreach ( $result as $contact ) {
				$contactModel = new ContactModel();
				try {
					$contactModel->setEventType(CE4WP_NL_EVENTTYPE);
					$contactModel->setOptIn( 'U' !== $contact->status );
					$contactModel->setOptOut( 'U' === $contact->status );
					$contactModel->setOptActionBy(OptActionBy::VISITOR);

					$email = $contact->email;
					if ( ! empty($email) ) {
						$contactModel->setEmail($email);
					}

					$name = $contact->name;
					if ( ! empty($name) ) {
						$contactModel->setFirstName($name);
					}

					$surname = $contact->surname;
					if ( ! empty($surname) ) {
						$contactModel->setLastName($surname);
					}
				} catch ( Exception $exception ) {
					DatadogManager::get_instance()->exception_handler($exception);
					continue;
				}

				if ( ! empty($contactModel->getEmail()) ) {
					array_push($backfillArray, $contactModel);
				}
			}
		}

		if ( ! empty($backfillArray) ) {
			return $backfillArray;
		}

		return null;
	}
}
