<?php
/**
 * Security log retention.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use WPHardeningToolkit\Module;

/**
 * Schedules and performs daily retention cleanup.
 */
final class Retention implements Module {
	public const HOOK = 'wpht_daily_log_cleanup';

	/**
	 * Supported retention periods.
	 *
	 * @var list<int>
	 */
	private const ALLOWED_DAYS = array( 30, 90, 180, 365 );

	/**
	 * Log persistence.
	 *
	 * @var LogStore
	 */
	private LogStore $store;

	/**
	 * Creates retention management.
	 *
	 * @param LogStore $store Log persistence.
	 */
	public function __construct( LogStore $store ) {
		$this->store = $store;
	}

	/**
	 * Registers cron hooks and ensures an event is scheduled.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( self::HOOK, array( $this, 'run_cleanup' ) );

		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::HOOK );
		}
	}

	/**
	 * Runs cleanup as a WordPress action callback.
	 *
	 * @return void
	 */
	public function run_cleanup(): void {
		$this->cleanup();
	}

	/**
	 * Deletes expired rows.
	 *
	 * @param int|null               $days Retention days, or configured value.
	 * @param DateTimeImmutable|null $now  Optional test clock.
	 * @return int
	 */
	public function cleanup( ?int $days = null, ?DateTimeImmutable $now = null ): int {
		$retention = $days ?? (int) get_option( 'wpht_log_retention_days', 90 );

		if ( ! in_array( $retention, self::ALLOWED_DAYS, true ) ) {
			$retention = 90;
		}

		$current = $now ?? new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
		$cutoff  = $current->setTimezone( new DateTimeZone( 'UTC' ) )
			->sub( new DateInterval( 'P' . $retention . 'D' ) )
			->format( 'Y-m-d H:i:s' );

		return $this->store->delete_older_than( $cutoff );
	}
}
