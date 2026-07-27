<?php
/**
 * WordPress database-backed log store.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

use wpdb;

/**
 * Owns runtime SQL access for the logging subsystem.
 */
final class WpdbLogStore implements LogStore {
	/**
	 * Database connection.
	 *
	 * @var wpdb
	 */
	private wpdb $database;

	/**
	 * Fully prefixed log table name.
	 *
	 * @var string
	 */
	private string $table;

	/**
	 * Creates the store.
	 *
	 * @param wpdb $database WordPress database connection.
	 */
	public function __construct( wpdb $database ) {
		$this->database = $database;
		$this->table    = $database->prefix . 'wpht_log';
	}

	/**
	 * Inserts a security log row.
	 *
	 * @param array<string, int|string> $row Row to insert.
	 * @return bool
	 */
	public function insert( array $row ): bool {
		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' );

		return false !== $this->database->insert( $this->table, $row, $formats );
	}

	/**
	 * Deletes rows older than a UTC timestamp.
	 *
	 * @param string $cutoff UTC database timestamp.
	 * @return int
	 */
	public function delete_older_than( string $cutoff ): int {
		// @phpstan-ignore-next-line Dynamic table is derived exclusively from wpdb::prefix.
		$sql = $this->database->prepare( "DELETE FROM {$this->table} WHERE logged_at < %s", $cutoff );

		if ( ! is_string( $sql ) ) {
			return 0;
		}

		$deleted = $this->database->query( $sql );

		return false === $deleted ? 0 : (int) $deleted;
	}
}
