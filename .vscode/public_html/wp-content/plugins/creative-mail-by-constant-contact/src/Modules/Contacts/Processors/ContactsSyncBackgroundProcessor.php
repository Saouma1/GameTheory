<?php

namespace CreativeMail\Modules\Contacts\Processors;

use CreativeMail\Exceptions\CreativeMailException;
use CreativeMail\Managers\IntegrationManager;
use CreativeMail\Managers\Logs\DatadogManager;
use CreativeMail\Modules\Contacts\Services\ContactsSyncService;
use CreativeMail\Integrations\Integration;
use Exception;
use ReflectionClass;
use ReflectionException;
use WP_Background_Process;

class ContactsSyncBackgroundProcessor extends WP_Background_Process {

	protected $action = 'ce_contact_sync_background_process';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return false
	 */
	protected function task( $item ) {
		try {
			// Synchronize contacts based on the active plugins.
			$integrationManager     = new IntegrationManager();
			$activated_integrations = $integrationManager->get_activated_integrations();

			if ( ! empty($activated_integrations) ) {
				$this->start_contacts_sync_for_all_integrations($activated_integrations);
			}
		} catch ( Exception $exception ) {
			DatadogManager::get_instance()->exception_handler($exception);
		}

		return false;
	}

	/**
	 * Starts the contacts sync for all the activated plugins
	 *
	 * @param Integration[] $activated_integrations The activated integrations.
	 *
	 * @throws ReflectionException
	 */
	private function start_contacts_sync_for_all_integrations( $activated_integrations ) {
		$total_contacts = array();

		if ( ! empty($activated_integrations) ) {
			$exception = new CreativeMailException('No activated integrations available');
			DatadogManager::get_instance()->exception_handler($exception);
		}

		// Get all contacts.
		foreach ( $activated_integrations as $activated_integration ) {
			$class          = new ReflectionClass($activated_integration->get_integration_handler());
			$plugin_handler = $class->newInstance();
			if ( method_exists($plugin_handler, 'get_contacts') ) {
				$plugin_contacts = $plugin_handler->get_contacts(null);
			}
			if ( isset($plugin_contacts) && ! empty($plugin_contacts) ) {
				foreach ( $plugin_contacts as $plugin_contact ) {
					$total_contacts[] = $plugin_contact;
				}
			}
		}

		if ( ! empty($total_contacts) ) {
			// Start contact sync.
			$contactSyncService = new ContactsSyncService();
			$contactSyncService->upsertContacts($total_contacts);
		}
	}
}
