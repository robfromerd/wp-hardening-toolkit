<?php
/**
 * Logger unit tests.
 *
 * @package WPHardeningToolkit\Tests
 */

namespace WPHardeningToolkit\Tests\Unit\Logging;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use WPHardeningToolkit\Logging\LogEntry;
use WPHardeningToolkit\Logging\Logger;

/**
 * Verifies security event persistence.
 */
final class LoggerTest extends TestCase {
	/**
	 * All required event fields are persisted.
	 *
	 * @return void
	 */
	public function test_logs_complete_security_event(): void {
		$store  = new FakeLogStore();
		$clock  = static fn (): DateTimeImmutable => new DateTimeImmutable( '2026-07-26 12:30:00', new DateTimeZone( 'UTC' ) );
		$logger = new Logger( $store, $clock );
		$entry  = new LogEntry(
			'rest_users',
			'blocked',
			'203.0.113.10',
			'GET',
			'/wp-json/wp/v2/users',
			'/wp/v2/users',
			'https://example.test/',
			'Test Agent',
			false,
			0
		);

		self::assertTrue( $logger->log( $entry ) );
		self::assertCount( 1, $store->rows );
		self::assertSame( '2026-07-26 12:30:00', $store->rows[0]['logged_at'] );
		self::assertSame( 'rest_users', $store->rows[0]['rule'] );
		self::assertSame( 0, $store->rows[0]['authenticated'] );
	}
}
