<?php
/**
 * Log storage contract.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

/**
 * Isolates all log persistence from hardening modules.
 */
interface LogStore {
	/**
	 * Inserts a security log row.
	 *
	 * @param array<string, int|string> $row Row to insert.
	 * @return bool
	 */
	public function insert( array $row ): bool;

	/**
	 * Deletes rows older than a UTC timestamp.
	 *
	 * @param string $cutoff UTC database timestamp.
	 * @return int Number of deleted rows.
	 */
	public function delete_older_than( string $cutoff ): int;
}
