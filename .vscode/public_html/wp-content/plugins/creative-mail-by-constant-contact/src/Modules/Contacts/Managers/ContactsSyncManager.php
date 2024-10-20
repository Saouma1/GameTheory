<?php

namespace CreativeMail\Modules\Contacts\Managers;

use CreativeMail\Modules\Contacts\Processors\ContactsSyncBackgroundProcessor;

final class ContactsSyncManager {

	/**
	 * Stores the background processor instance.
	 *
	 * @var ContactsSyncBackgroundProcessor
	 */
	private $contacts_sync_background_processor;

	public function __construct() {
		$this->contacts_sync_background_processor = new ContactsSyncBackgroundProcessor();
		add_action(CE4WP_SYNCHRONIZE_ACTION, array( $this, 'publish_contact_sync_request' ));
	}

	public function __destruct() {
		remove_action(CE4WP_SYNCHRONIZE_ACTION, array( $this, 'publish_contact_sync_request' ));
	}

	/**
	 * Publishes a contact sync request to the background processor.
	 *
	 * @return void
	 */
	public function publish_contact_sync_request(): void {
		$this->contacts_sync_background_processor->push_to_queue(null);
		// Start the queue.
		$this->contacts_sync_background_processor->save()->dispatch();
	}
}
