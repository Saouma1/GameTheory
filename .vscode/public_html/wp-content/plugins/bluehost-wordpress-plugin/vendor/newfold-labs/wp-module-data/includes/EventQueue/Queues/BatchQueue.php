<?php

namespace NewfoldLabs\WP\Module\Data\EventQueue\Queues;

use NewfoldLabs\WP\Module\Data\Event;
use NewfoldLabs\WP\Module\Data\EventQueue\Queryable;
use NewfoldLabs\WP\ModuleLoader\Container;

class BatchQueue implements BatchQueueInterface {

	use Queryable;

	/**
	 * Dependency injection container
	 *
	 * @var Container $container
	 */
	protected $container;

	/**
	 * Constructor
	 *
	 * @param  Container  $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Push events onto the queue
	 *
	 * @param  Event[]  $events
	 *
	 * @return bool
	 */
	public function push( array $events ) {

		$time = current_time( 'mysql' );

		$inserts = [];
		foreach ( $events as $event ) {
			$inserts[] = [
				'event'        => serialize( $event ),
				'available_at' => current_time( 'mysql' ),
				'created_at'   => $time,
			];
		}

		return (bool) $this->bulkInsert( $this->table(), $inserts );
	}

	/**
	 * Pull events from the queue
	 *
	 * @return Event[]
	 */
	public function pull( int $count ) {

		$events = [];

		$rawEvents = $this
			->query()
			->select( '*' )
			->from( $this->table(), false )
			->whereNull( 'reserved_at' )
			->where( 'available_at', '<=', current_time( 'mysql' ) )
			->order_by( 'available_at' )
			->limit( $count )
			->get();

		if ( ! is_array( $rawEvents ) ) {
			return $events;
		}

		foreach ( $rawEvents as $rawEvent ) {
			if ( property_exists( $rawEvent, 'id' ) && property_exists( $rawEvent, 'event' ) ) {
				$events[ $rawEvent->id ] = maybe_unserialize( $rawEvent->event );
			}
		}

		return $events;
	}

	/**
	 * Remove events from the queue
	 *
	 * @param  int[]  $ids
	 *
	 * @return bool
	 */
	public function remove( array $ids ) {
		return (bool) $this
			->query()
			->table( $this->table(), false )
			->whereIn( 'id', $ids )
			->delete();
	}

	/**
	 * Reserve events in the queue
	 *
	 * @param  int[]  $ids
	 *
	 * @return bool
	 */
	public function reserve( array $ids ) {
		return (bool) $this
			->query()
			->table( $this->table(), false )
			->whereIn( 'id', $ids )
			->update( [ 'reserved_at' => current_time( 'mysql' ) ] );
	}

	/**
	 * Release events back onto the queue
	 *
	 * @param  int[]  $ids
	 *
	 * @return bool
	 */
	public function release( array $ids ) {
		return (bool) $this
			->query()
			->table( $this->table(), false )
			->whereIn( 'id', $ids )
			->update( [ 'reserved_at' => null ] );
	}

	/**
	 * Count the number of events in the queue
	 *
	 * @return int
	 */
	public function count() {
		return $this
			->query()
			->select( '*' )
			->from( $this->table(), false )
			->whereNull( 'reserved_at' )
			->where( 'available_at', '<=', current_time( 'mysql' ) )
			->count();
	}
}
