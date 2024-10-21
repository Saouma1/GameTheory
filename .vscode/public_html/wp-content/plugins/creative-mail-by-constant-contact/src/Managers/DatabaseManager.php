<?php


namespace CreativeMail\Managers;

use DateInterval;
use DateTime;

/**
 * Class DatabaseManager
 *
 * @package CreativeMail\Managers
 */
class DatabaseManager {

	/**
	 * Current version of abandoned checkouts table.
	 *
	 * @since 1.3.0
	 */
	const ABANDONED_CART_TABLE_VERSION = '1.0';

	/**
	 * Option name for abandoned checkouts db version.
	 *
	 * @since 1.3.0
	 */
	const ABANDONED_CART_TABLE_VERSION_OPTION_NAME = 'ce4wp_abandoned_checkout_db_version';

	const CHECKOUT_UUID = 'checkout_uuid';

	/**
	 * Abandoned checkouts table name.
	 *
	 * @since 1.3.0
	 */
	const ABANDONED_CART_TABLE_NAME = 'ce4wp_abandoned_checkout';

	/**
	 * Current version of the contacts table.
	 *
	 * @since 1.4.0
	 */
	const CONTACTS_TABLE_VERSION = '1.0';

	/**
	 * Option name for the contacts db version.
	 *
	 * @since 1.4.0
	 */
	const CONTACTS_TABLE_VERSION_OPTION_NAME = 'ce4wp_contacts_db_version';

	/**
	 * Contacts table name.
	 *
	 * @since 1.4.0
	 */
	const CONTACTS_TABLE_NAME = 'ce4wp_contacts';

	/**
	 * Add hooks.
	 *
	 * @return void
	 */
	public function add_hooks() {
		add_action('admin_init', array( $this, 'update_database_check' ), 10, 2);
	}

	/**
	 * Check if table exists and is up-to-date.
	 *
	 * @since 1.3.0
	 */
	public function update_database_check() {
		// Check if woocommerce is active.
		if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true) ) {
			if ( ! get_site_option(self::ABANDONED_CART_TABLE_VERSION_OPTION_NAME) ) {
				// Fresh install: create table.
				$this->create_abandoned_cart_table();
			} elseif ( current_user_can('administrator') ) {
				// Cleanup old expired checkouts.
				$this->delete_expired_checkouts();
			}
		}
		// Check if ce4wp_contacts table exists.
		if ( ! get_site_option(self::CONTACTS_TABLE_VERSION_OPTION_NAME) ) {
			// Fresh install: create table.
			$this->create_contacts_table();
		}
	}

	/**
	 * Helper function to remove checkout session data from db.
	 *
	 * @param string $checkout_uuid The checkout uuid.
	 *
	 * @since 1.3.0
	 */
	public function remove_checkout_data( $checkout_uuid ) {
		global $wpdb;

		// Delete current checkout data.
		$wpdb->delete(
			self::get_table_name(self::ABANDONED_CART_TABLE_NAME),
			array(
				self::CHECKOUT_UUID => $checkout_uuid,
			),
			array(
				'%s',
			)
		);
	}

	/**
	 * Create abandoned checkouts table.
	 *
	 * @since 1.3.0
	 */
	public function create_abandoned_cart_table() {
		global $wpdb;

		$table_name = self::get_table_name(self::ABANDONED_CART_TABLE_NAME);

		$sql = "CREATE TABLE {$table_name} (
			checkout_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			user_email varchar(200) NOT NULL DEFAULT '',
			checkout_contents longtext NOT NULL,
			checkout_updated datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			checkout_updated_ts int(11) unsigned NOT NULL DEFAULT 0,
			checkout_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			checkout_created_ts int(11) unsigned NOT NULL DEFAULT 0,
			checkout_recovered datetime NULL DEFAULT '0000-00-00 00:00:00',
			checkout_recovered_ts int(11) unsigned NULL DEFAULT 0,
			checkout_consent int(11) unsigned NOT NULL DEFAULT 1,
			checkout_uuid varchar(36) NOT NULL DEFAULT '',
			PRIMARY KEY (checkout_id),
			UNIQUE KEY checkout_uuid (checkout_uuid)
		) {$wpdb->get_charset_collate()}";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);

		add_option(self::ABANDONED_CART_TABLE_VERSION_OPTION_NAME, self::ABANDONED_CART_TABLE_VERSION);
	}

	/**
	 * Create contacts table
	 */
	public function create_contacts_table() {
		global $wpdb;

		$table_name = self::get_table_name(self::CONTACTS_TABLE_NAME);

		// keep column names equal to the form submission names so we don't need additional conversion.
		$sql = "CREATE TABLE {$table_name} (
			contact_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			email varchar(200) NOT NULL DEFAULT '',
			first_name varchar(200) DEFAULT '',
			last_name varchar(200) DEFAULT '',
			telephone varchar(200) DEFAULT '',
            consent varchar(200) DEFAULT '',
			PRIMARY KEY  (contact_id),
			UNIQUE KEY email (email)
		) {$wpdb->get_charset_collate()}";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);

		add_option(self::CONTACTS_TABLE_VERSION_OPTION_NAME, self::CONTACTS_TABLE_VERSION);
	}

	/**
	 * Delete expired checkouts.
	 *
	 * @since 1.3.0
	 */
	public function delete_expired_checkouts() {
		global $wpdb;

		// Delete all checkouts at least 30 days old.
		$table_name = $this->get_table_name(self::ABANDONED_CART_TABLE_NAME);

		$wpdb->query(
			$wpdb->prepare(
            // phpcs:disable WordPress.DB.PreparedSQL -- Okay use of unprepared variable for table name in SQL.
				"DELETE FROM {$table_name}
				WHERE `checkout_updated_ts` <= %s",
                // phpcs:enable
				( new DateTime() )->sub(new DateInterval('P30D'))->format('U')
			)
		);
	}

	/**
	 * Upsert the checkout content
	 *
	 * @param string     $checkout_uuid The checkout uuid.
	 * @param string     $user_id The user id.
	 * @param string     $billing_email The user email.
	 * @param array      $content The checkout content.
	 * @param int|string $current_time The consent value.
	 *
	 * @since 1.3.0
	 */
	public function upsert_checkout( $checkout_uuid, $user_id, $billing_email, $content, $current_time ) {
		global $wpdb;

		$table_name = $this->get_table_name(self::ABANDONED_CART_TABLE_NAME);

        // phpcs:disable WordPress.DB.PreparedSQL -- Okay use of unprepared variable for table name in SQL.
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table_name} (
					`user_id`,
					`user_email`,
					`checkout_contents`,
					`checkout_updated`,
					`checkout_updated_ts`,
					`checkout_created`,
					`checkout_created_ts`,
					`checkout_uuid`
				) VALUES (
					%d,
					%s,
					%s,
					%s,
					%d,
					%s,
					%d,
					%s
				) ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`), `user_email` = VALUES(`user_email`), `checkout_updated` = VALUES(`checkout_updated`), `checkout_updated_ts` = VALUES(`checkout_updated_ts`), `checkout_contents` = VALUES(`checkout_contents`)",
				$user_id,
				$billing_email,
				maybe_serialize($content),
				$current_time,
				strtotime($current_time),
				$current_time,
				strtotime($current_time),
				$checkout_uuid
			)
		);
        // phpcs:enable
	}

	/**
	 * Update the database record with current time for recovery
	 *
	 * @param string $checkout_uuid The checkout uuid.
	 *
	 * @since 1.3.0
	 */
	public function mark_checkout_recovered( $checkout_uuid ) {
		global $wpdb;

		$table_name = $this->get_table_name(self::ABANDONED_CART_TABLE_NAME);

		$current_time = current_time('mysql', 1);

		$wpdb->update($table_name, array(
			'checkout_recovered'    => $current_time,
			'checkout_recovered_ts' => strtotime($current_time),
		), array( self::CHECKOUT_UUID => $checkout_uuid ));
	}

	/**
	 * Update the database record with current consent
	 *
	 * @param string $checkout_uuid The checkout uuid.
	 * @param bool   $consent The consent value.
	 *
	 * @since 1.3.0
	 */
	public function change_checkout_consent( $checkout_uuid, $consent ) {
		global $wpdb;

		$table_name = $this->get_table_name(self::ABANDONED_CART_TABLE_NAME);

		$int_consent = $consent ? 1 : 0;

		$wpdb->update($table_name, array(
			'checkout_consent' => $int_consent,
		), array( self::CHECKOUT_UUID => $checkout_uuid ));
	}

	/**
	 * Checks if the checkout exists and has consent in the database.
	 *
	 * @param string $checkout_uuid The checkout uuid.
	 *
	 * @return bool
	 */
	public function has_checkout_consent( string $checkout_uuid ) {
		global $wpdb;

		$table_name    = $this->get_table_name(self::ABANDONED_CART_TABLE_NAME);
		$consent_value = $wpdb->get_var(
            // phpcs:disable WordPress.DB.PreparedSQL -- Okay use of unprepared variable for table name in SQL.
			$wpdb->prepare("SELECT checkout_consent FROM {$table_name} WHERE checkout_uuid = '{$checkout_uuid}'")
		);
		return '1' === $consent_value;
	}

	/**
	 * Retrieve specific user's checkout data.
	 *
	 * @param string $select Field to return.
	 * @param mixed  $where String or array of WHERE clause predicates, using placeholders for values.
	 * @param array  $where_args Array of WHERE clause arguments.
	 * @param string $order_by Order by column.
	 * @param string $order Order (ASC/DESC).
	 * @param string $limit LIMIT clause.
	 * @param array  $limit_args Array of LIMIT clause arguments.
	 *
	 * @return mixed              Checkout data if exists, else null.
	 * @since  1.3.0
	 */
	public function get_checkout_data( string $select, $where, array $where_args, string $order_by = 'checkout_updated_ts', string $order = 'DESC', string $limit = '', array $limit_args = array() ) {
		global $wpdb;

		$table_name = $this->get_table_name(self::ABANDONED_CART_TABLE_NAME);
		$where      = is_array($where) ? implode(' AND ', $where) : $where;
		$where      = empty($where) ? 1 : $where;

		// Construct query to return checkout data.
        // phpcs:disable -- Disabling a number of sniffs that erroneously flag following block of code.
        // $where often includes placeholders for replacement via $wpdb->prepare(). $where_values provides those values.
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT {$select}
				FROM {$table_name}
				WHERE {$where}
				ORDER BY {$order_by} {$order}
				{$limit}",
                array_merge($where_args, $limit_args)
            )
        );
        // phpcs:enable
	}

	/**
	 * A simple utility for grabbing the full table name, including the WPDB table prefix.
	 *
	 * @param string $OptionName The name of the option to retrieve.
	 *
	 * @return string
	 * @since  1.3.0
	 */
	public static function get_table_name( string $OptionName ): string {
		global $wpdb;
		$table_name = null;
		if ( self::ABANDONED_CART_TABLE_NAME == $OptionName ) {
			$table_name = $wpdb->prefix . self::ABANDONED_CART_TABLE_NAME;
		} elseif ( self::CONTACTS_TABLE_NAME == $OptionName ) {
			$table_name = $wpdb->prefix . self::CONTACTS_TABLE_NAME;
		}
		return is_string($table_name) ? $table_name : '';
	}

	/**
	 * Insert a new contact into the database.
	 *
	 * @param mixed $data The contact data.
	 *
	 * @return void
	 */
	public function insert_contact( $data ) {
		global $wpdb;

		$table_name = $this->get_table_name(self::CONTACTS_TABLE_NAME);

		$wpdb->query(
			$wpdb->prepare(
				'INSERT IGNORE INTO %s (
					`email`,
					`first_name`,
					`last_name`,
					`telephone`,
					`consent`
				) VALUES (
					%s,
					%s,
					%s,
					%s,
					%s
				)',
				$table_name,
				$data['email'],
				$data['first_name'],
				$data['last_name'],
				$data['telephone'],
				$data['consent']
			)
		);
	}
}
