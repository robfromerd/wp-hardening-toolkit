<?php
/**
 * Security event logger.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Dedicated entry point for writing security events.
 */
final class Logger {
	/**
	 * Log persistence.
	 *
	 * @var LogStore
	 */
	private LogStore $store;

	/**
	 * UTC clock.
	 *
	 * @var callable(): DateTimeImmutable
	 */
	private $clock;

	/**
	 * Creates a logger.
	 *
	 * @param LogStore                           $store Log persistence.
	 * @param callable(): DateTimeImmutable|null $clock Optional test clock.
	 */
	public function __construct( LogStore $store, ?callable $clock = null ) {
		$this->store = $store;
		$this->clock = $clock ?? static fn (): DateTimeImmutable => new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Writes an event.
	 *
	 * @param LogEntry $entry Security event.
	 * @return bool
	 */
	public function log( LogEntry $entry ): bool {
		$now = ( $this->clock )();

		return $this->store->insert( $entry->to_row( $now->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' ) ) );
	}
}
