<?php
/**
 * Retention unit tests.
 *
 * @package WPHardeningToolkit\Tests
 */

namespace WPHardeningToolkit\Tests\Unit\Logging;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use WPHardeningToolkit\Logging\Retention;

/**
 * Verifies deterministic retention cleanup.
 */
final class RetentionTest extends TestCase {
	/**
	 * Cleanup calculates the requested UTC cutoff.
	 *
	 * @return void
	 */
	public function test_cleanup_deletes_rows_before_cutoff(): void {
		$store     = new FakeLogStore();
		$retention = new Retention( $store );
		$now       = new DateTimeImmutable( '2026-07-26 15:00:00', new DateTimeZone( 'UTC' ) );

		self::assertSame( 2, $retention->cleanup( 30, $now ) );
		self::assertSame( '2026-06-26 15:00:00', $store->cutoff );
	}
}
