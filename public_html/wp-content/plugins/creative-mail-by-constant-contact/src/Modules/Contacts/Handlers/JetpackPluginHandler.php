<?php

namespace CreativeMail\Modules\Contacts\Handlers;

define('CE4WP_JP_EVENTTYPE', 'WordPress - Jetpack');

use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use CreativeMail\Modules\Contacts\Models\OptActionBy;
use Exception;
use stdClass;

final class JetpackPluginHandler extends BaseContactFormPluginHandler {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Converts to contact model.
	 *
	 * @param object $contact The contact.
	 *
	 * @return ContactModel
	 * @throws Exception
	 */
	public function convertToContactModel( $contact ) {
		$contactModel = new ContactModel();
		$contactModel->setEventType(CE4WP_JP_EVENTTYPE);

		// Email Marketing Consent.
		if ( $contact->opt_in ) {
			$contactModel->setOptIn(true);
			$contactModel->setOptOut(false);
			$contactModel->setOptActionBy(OptActionBy::VISITOR);
		}

		$email = $contact->email;
		if ( ! empty($email) ) {
			$contactModel->setEmail($email);
		}

		$values    = explode(' ', $contact->name);
		$firstName = array_shift($values);
		$lastName  = implode(' ', $values);

		if ( ! empty($firstName) ) {
			$contactModel->setFirstName($firstName);
		}
		if ( ! empty($lastName) ) {
			$contactModel->setLastName($lastName);
		}
		if ( ! empty($contact->phone) ) {
			$contactModel->setPhone($contact->phone);
		}
		if ( ! empty($contact->birthday) ) {
			$contactModel->setBirthday($contact->birthday);
		}
		return $contactModel;
	}

	/**
	 * Handles the contact form submission.
	 *
	 * @param string $header The header.
	 *
	 * @return array<string,mixed>
	 */
	private function GetNameAndEmailFromHeader( string $header ): array {
		$headerRegex = '/(?:^Reply-To: ")(.*)(?:" <)(.*)(?:>)/mi';
		preg_match($headerRegex, $header, $regexMatches);
		$values = null;

		if ( count($regexMatches) == 0 ) {
			$headerRegexWithNoQuote = '/(?:^Reply-To: )(.*)(?: <)(.*)(?:>)/mi';
			preg_match($headerRegexWithNoQuote, $header, $regexMatches);
		}

		// Check if the name isn't an email address (happens when no name is supplied).
		$values['name'] = ( ! filter_var($regexMatches[1], FILTER_VALIDATE_EMAIL) ) ? $regexMatches[1] : null;
		// Check if email is valid.
		$values['email'] = ( filter_var($regexMatches[2], FILTER_VALIDATE_EMAIL) ) ? $regexMatches[2] : null;

		return $values;
	}

	/**
	 * Handles the contact form submission.
	 *
	 * @param int    $post_id The post id.
	 * @param string $to The to.
	 * @param string $subject  The subject.
	 * @param string $message The message.
	 * @param string $headers The headers.
	 * @param array  $all_values The all values.
	 * @param array  $extra_values The extra values.
	 *
	 * @return void
	 */
	public function ceHandleJetpackFormSubmission( $post_id, $to, $subject, $message, $headers, $all_values, $extra_values ): void {
		try {
			$contact         = new stdClass();
			$nameAndEmail    = $this->GetNameAndEmailFromHeader($headers);
			$contact->email  = $nameAndEmail['email'];
			$contact->name   = $nameAndEmail['name'];
			$contact->phone  = $this->GetPhoneNumber($all_values);
			$contact->opt_in = boolval(isset($all_values['email_marketing_consent']) && $all_values['email_marketing_consent']);

			if ( empty($contact->email) ) {
				return;
			}

			$this->upsertContact($this->convertToContactModel($contact));
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	public function registerHooks() {
		add_action('grunion_after_message_sent', array( $this, 'ceHandleJetpackFormSubmission' ), 10, 7);
	}

	public function unregisterHooks() {
		remove_action('grunion_after_message_sent', array( $this, 'ceHandleJetpackFormSubmission' ));
	}

	/**
	 * Get all the contacts.
	 *
	 * @param int $limit The limit of contacts to be returned.
	 *
	 * @return array|null
	 */
	public function get_contacts( $limit = null ) {
		if ( ! is_int($limit) || $limit <= 0 ) {
			$limit = null;
		}

		// Relies on plugin => Jetpack or Jetpack beta.
		if ( in_array('jetpack/jetpack.php', apply_filters('active_plugins', get_option('active_plugins')), true)
			|| in_array('jetpack-beta-master/jetpack-beta.php', apply_filters('active_plugins', get_option('active_plugins')), true)
		) {
			$authorRegex           = '/(?:^AUTHOR: )(.*)/mi';
			$authorMailRegex       = '/(?:^AUTHOR EMAIL: )(.*)/mi';
			$additionalFieldsRegex = '/(?:^\s{4})\[\d_(.*)\](?:.*;\s)(.*)/mi';

			$consentRegex  = '/(?:\[email_marketing_consent] =&gt; )(.*)/mi';
			$contactsArray = array();

			// Get all posts with type->feedback (i think these are all the contact forms submitted).
			$feedbackResults = get_posts(array( 'post_type' => 'feedback' ));

			// Loop through the feedbacks and get each blocks innerHTML.
			foreach ( $feedbackResults as $feedback ) {
				foreach ( parse_blocks($feedback->post_content) as $block ) {
					$feedbackHtml = $block['innerHTML'];

					// Extract name, email and consent from submission.
					$author = '';
					preg_match($authorRegex, $feedbackHtml, $authorMatches);
					if ( count($authorMatches) > 1 ) {
						$author = $authorMatches[1];
					}

					$authorEmail = '';
					preg_match($authorMailRegex, $feedbackHtml, $authorEmailMatches);
					if ( count($authorEmailMatches) > 1 ) {
						$authorEmail = $authorEmailMatches[1];
					}

					$consentValue = false;
					preg_match($consentRegex, $feedbackHtml, $consentMatches);
					if ( count($consentMatches) > 1 ) {
						$consentValue = $consentMatches[1];
					}

					$contact        = new stdClass();
					$contact->email = filter_var($authorEmail, FILTER_VALIDATE_EMAIL);
					if ( empty($contact->email) ) {
						continue;
					}

					// If the author field also contains an email, ignore it (this happens when no name is provided).
					if ( ! filter_var($author, FILTER_VALIDATE_EMAIL) ) {
						$contact->name = $author;
					} else {
						$contact->name = null;
					}

					$contact->opt_in = boolval($consentValue);

					preg_match_all($additionalFieldsRegex, $feedbackHtml, $additionalFieldMatches);
					if ( count($additionalFieldMatches) > 1 ) {
						foreach ( $additionalFieldMatches[1] as $index => $label ) {
							$fieldValue = $additionalFieldMatches[2][ $index ];
							if ( in_array(strtolower($label), $this->phoneFields, true) ) {
								$contact->phone = $fieldValue;
							} elseif ( in_array(strtolower($label), $this->birthdayFields, true) ) {
								$contact->birthday = $fieldValue;
							}
						}
					}

					// Convert to contactModel and push to the array.
					$contactModel = null;
					try {
						$contactModel = $this->convertToContactModel($contact);
					} catch ( Exception $exception ) {
						DatadogManager::get_instance()->exception_handler($exception);
						continue;
					}

					array_push($contactsArray, $contactModel);

					if ( isset($limit) && count($contactsArray) >= $limit ) {
						break;
					}
				}

				if ( isset($limit) && count($contactsArray) >= $limit ) {
					break;
				}
			}

			// Upsert the contacts.
			if ( ! empty($contactsArray) ) {
				return $contactsArray;
			}
		}

		return null;
	}

	/**
	 * Returns the Phone Number.
	 *
	 * @param array $all_values The array of all the values.
	 *
	 * @return void|string
	 */
	public function GetPhoneNumber( $all_values ) {
		$target_substring = 'phone';
		// Loop through every field of the form.
		foreach ( $all_values as $key => $value ) {
			// If the name of the key contains the substring "phone" then the value will be the phone number.
			if ( mb_strpos(strtolower($key), $target_substring) !== false ) {
				return $value;
			}
		}
	}
}
