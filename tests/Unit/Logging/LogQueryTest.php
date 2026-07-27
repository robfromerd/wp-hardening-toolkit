<?php
/**
 * Log query tests.
 *
 * @package WPHardeningToolkit\Tests
 */

namespace WPHardeningToolkit\Tests\Unit\Logging;

use PHPUnit\Framework\TestCase;
use WPHardeningToolkit\Logging\LogQuery;

/**
 * Verifies allowlisting of administrative log queries.
 */
final class LogQueryTest extends TestCase {
	/**
	 * Unsafe identifiers and invalid ranges fall back safely.
	 *
	 * @return void
	 */
	public function test_allowlists_sort_and_pagination(): void {
		$query = LogQuery::from_array(
			array(
				'orderby'  => 'id; DROP TABLE',
				'order'    => 'sideways',
				'paged'    => -10,
				'per_page' => 5000,
			)
		);

		self::assertSame( 'logged_at', $query->sort );
		self::assertSame( 'DESC', $query->direction );
		self::assertSame( 1, $query->page );
		self::assertSame( 200, $query->per_page );
		self::assertSame( 0, $query->offset() );
	}

	/**
	 * Invalid date filters are discarded.
	 *
	 * @return void
	 */
	public function test_validates_date_filters(): void {
		$query = LogQuery::from_array(
			array(
				'date_from' => 'yesterday',
				'date_to'   => '2026-07-26',
			)
		);

		self::assertSame( '', $query->date_from );
		self::assertSame( '2026-07-26', $query->date_to );
	}
}
