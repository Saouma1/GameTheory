<?php

namespace CreativeMail\Modules\Contacts\Services;

use CreativeMail\CreativeMail;
use CreativeMail\Exceptions\CreativeMailException;
use CreativeMail\Helpers\EnvironmentHelper;
use CreativeMail\Helpers\OptionsHelper;
use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Api\Models\ApiRequestItem;
use CreativeMail\Modules\Contacts\Models\ContactModel;
use Exception;
use stdClass;

class ContactsSyncService {

	const FAST_LANE_LIMIT          = 250;
	const CSV_FILE_MAX_MEMORY_SIZE = 1024 * 1024 * 5; // 5MB

	private function validate_email_address( $emailAddress ) {
		if ( ! isset($emailAddress) && empty($emailAddress) ) {
			throw new Exception('No valid email address provided');
		}
	}

	private function ensure_event_type( $eventType ) {
		// DEV: For now, we only support WordPress.
		if ( isset($eventType) && ! empty($eventType) ) {
			return $eventType;
		}

		return 'WordPress';
	}

	private function build_payload( $contactModels ) {
		$contacts = array();
		foreach ( $contactModels as $model ) {
			array_push($contacts, $model->toArray());
		}

		$data = array(
			'contacts' => $contacts,
		);

		return wp_json_encode($data);
	}

	public function upsertContact( ContactModel $contactModel ) {
		if ( null == $contactModel ) {
			return false;
		}

		$this->validate_email_address($contactModel->getEmail());
		$contactModel->setEventType($this->ensure_event_type($contactModel->getEventType()));

		$jsonData = $this->build_payload(array( $contactModel ));

		$creativ_email = CreativeMail::get_instance();

		$creativ_email->get_api_manager()->get_api_background_process()->push_to_queue(
			new ApiRequestItem(
				'POST',
				'application/json',
				'/v1.0/contacts',
				$jsonData
			)
		);

		// Start the queue.
		$creativ_email->get_api_manager()->get_api_background_process()->save()->dispatch();
		return true;
	}

	public function upsertContacts( $contactModels ) {
		try {
			if ( empty($contactModels) ) {
				$exception = new CreativeMailException('No contacts provided or empty Contact Model');
				DatadogManager::get_instance()->exception_handler($exception);
			}

			if ( count($contactModels) > self::FAST_LANE_LIMIT ) {
				$this->fast_lane_contacts_sync(array_slice($contactModels, 0, self::FAST_LANE_LIMIT));
				$this->slow_lane_contacts_sync(array_slice($contactModels, self::FAST_LANE_LIMIT, count($contactModels) - self::FAST_LANE_LIMIT));

			} else {
				$this->fast_lane_contacts_sync($contactModels);
			}
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}

		return true;
	}

	private function fast_lane_contacts_sync( $contactModels ) {
		if ( empty( $contactModels ) ) {
			$exception = new CreativeMailException('No contacts provided or empty Contact Model');
			DatadogManager::get_instance()->exception_handler($exception);
		}

		$url = EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/contacts');

		$jsonData = $this->build_payload($contactModels);

		$args = array(
			'headers' => array(
				'x-api-key'    => OptionsHelper::get_instance_api_key(),
				'x-account-id' => OptionsHelper::get_connected_account_id(),
				'content-type' => 'application/json',
			),
			'body'    => $jsonData,
		);

		wp_remote_post($url, $args);
	}

	private function slow_lane_contacts_sync( $contactModels ) {
		if ( empty($contactModels) ) {
			$exception = new CreativeMailException('No contacts provided or empty Contact Model');
			DatadogManager::get_instance()->exception_handler($exception);
		}

		// 1. Convert to csv file.
		$csv_file = $this->create_csv_file($contactModels);
		// 2. Request sas model (with url and uuid).
		$sas_request_model = $this->request_sas_model();
		// 3. Upload csv file using sas url.
		$this->upload_csv_file($csv_file, $sas_request_model->url);
		// 4. Call endpoint to start import (using uuid).
		$this->start_import_for_uuid($sas_request_model->uuid);

	}

	private function create_csv_file( $contactModels ) {
		$csv_content = '';
		if ( empty($contactModels) ) {
			$exception = new CreativeMailException('Error trying to create the CSV to get the contacts data');
			DatadogManager::get_instance()->exception_handler($exception);
		}

		$fd = fopen('php://temp/maxmemory:' . self::CSV_FILE_MAX_MEMORY_SIZE, 'w');
		if ( false === $fd ) {
			$exception = new CreativeMailException('No contacts provided or empty Contact Model');
			DatadogManager::get_instance()->exception_handler($exception);
		}

		foreach ( $contactModels as $contactModel ) {
			$contact_fields_array = $this->convert_contact_to_csv_array($contactModel);
			if ( ! empty($contact_fields_array) ) {
				if ( false !== $fd ) {
					fputcsv($fd, $contact_fields_array);
				}
			}
		}

		if ( false !== $fd ) {
			rewind($fd);
			$csv_content = stream_get_contents($fd);
			fclose($fd);
		}

		return $csv_content;
	}

	private function request_sas_model() {
		$url      = EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/contacts/request-import-sas-url');
		$args     = array(
			'headers' => array(
				'x-api-key'    => OptionsHelper::get_instance_api_key(),
				'x-account-id' => OptionsHelper::get_connected_account_id(),
			),
		);
		$response = wp_remote_get($url, $args);

		if ( is_wp_error($response) ) {
			$exception = new CreativeMailException('No contacts provided or empty Contact Model');
			DatadogManager::get_instance()->exception_handler($exception);
		}
		if ( ! $this->is_success_response($response) ) {
			$exception = new CreativeMailException('No contacts provided or empty Contact Model');
			DatadogManager::get_instance()->exception_handler($exception);
		}

		$json = json_decode(is_array($response) ? $response['body'] : '');

		$request_sas_model       = new stdClass();
		$request_sas_model->url  = $json->url;
		$request_sas_model->uuid = $json->uuid;

		return $request_sas_model;
	}

	private function upload_csv_file( $csv_file, $upload_url ) {
		$args = array(
			'headers' => array(
				'x-ms-blob-type' => 'BlockBlob',
				'content-type'   => 'text/plain',
			),
			'body'    => $csv_file,
			'method'  => 'PUT',
		);

		$response = wp_remote_request($upload_url, $args);

		if ( is_wp_error($response) ) {
			$exception = new CreativeMailException('No contacts provided or empty Contact Model');
			DatadogManager::get_instance()->exception_handler($exception);
		}

		if ( ! $this->is_success_response($response) ) {
			$exception = new CreativeMailException('No contacts provided or empty Contact Model');
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	private function start_import_for_uuid( $uuid ) {
		$url = EnvironmentHelper::get_app_gateway_url('wordpress/v1.0/contacts/import');

		$data = array(
			'uuid' => $uuid,
		);

		$args = array(
			'headers' => array(
				'x-api-key'    => OptionsHelper::get_instance_api_key(),
				'x-account-id' => OptionsHelper::get_connected_account_id(),
				'content-type' => 'application/json',
			),
			'body'    => wp_json_encode($data),
			'method'  => 'POST',
		);

		$response = wp_remote_post($url,
			$args);

		if ( is_wp_error($response) ) {
			$exception = new CreativeMailException('There was a WP_ERROR while trying to start import');
			DatadogManager::get_instance()->exception_handler($exception);
		}

		if ( ! $this->is_success_response($response) ) {
			$exception = new CreativeMailException('There was an error at the response.');
			DatadogManager::get_instance()->exception_handler($exception);
		}
	}

	private function convert_contact_to_csv_array( ContactModel $contactModel ) {
		if ( empty($contactModel) ) {
			return null;
		}

		$contact_fields = array(
			strval($contactModel->getEmail()),
			strval($contactModel->getFirstName()),
			strval($contactModel->getLastName()),
		);

		$contactAddressModel = $contactModel->getContactAddress();

		if ( empty($contactAddressModel) ) {
			array_push($contact_fields, '', '', '', '', '');
		} else {
			array_push($contact_fields, strval($contactAddressModel->getAddress()), strval($contactAddressModel->getCity()), strval($contactAddressModel->getPostalCode()),
				strval($contactAddressModel->getCountryCode()), strval($contactAddressModel->getState()));
		}

		// Job Title.
		array_push($contact_fields, strval($contactModel->getPhone()), strval($contactModel->getBirthday()), strval($contactModel->getCompanyName()), '',
			strval($contactModel->getOptIn()), strval($contactModel->getOptOut()), strval($contactModel->getEventType()));
		return $contact_fields;
	}

	private function is_success_response( $response ) {
		$response_code = wp_remote_retrieve_response_code($response);
		return $response_code >= 200 && $response_code <= 299;
	}
}
