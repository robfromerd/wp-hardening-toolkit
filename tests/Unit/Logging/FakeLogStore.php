<?php
/**
 * In-memory log store used by unit tests.
 *
 * @package WPHardeningToolkit\Tests
 */

namespace WPHardeningToolkit\Tests\Unit\Logging;

use WPHardeningToolkit\Logging\LogStore;

/**
 * Records logging calls without a database.
 */
final class FakeLogStore implements LogStore {
	/**
	 * Inserted rows.
	 *
	 * @var list<array<string, int|string>>
	 */
	public array $rows = array();

	/**
	 * Last retention cutoff.
	 *
	 * @var string
	 */
	public string $cutoff = '';

	/**
	 * Inserts a row.
	 *
	 * @param array<string, int|string> $row Row to insert.
	 * @return bool
	 */
	public function insert( array $row ): bool {
		$this->rows[] = $row;

		return true;
	}

	/**
	 * Records a retention cutoff.
	 *
	 * @param string $cutoff UTC timestamp.
	 * @return int
	 */
	public function delete_older_than( string $cutoff ): int {
		$this->cutoff = $cutoff;

		return 2;
	}
}
