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
final class WpdbLogStore implements LogStore, LogRepository {
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

	/**
	 * Finds matching rows.
	 *
	 * @param LogQuery $query Validated query.
	 * @return list<array<string, int|string>>
	 */
	public function find( LogQuery $query ): array {
		$where  = $this->where_sql( $query );
		$sql    = "SELECT * FROM {$this->table}{$where['sql']} ORDER BY {$query->sort} {$query->direction} LIMIT %d OFFSET %d";
		$params = array_merge( $where['params'], array( $query->per_page, $query->offset() ) );
		// @phpstan-ignore-next-line Dynamic identifiers are strictly allowlisted or derived from wpdb::prefix.
		$prepared = $this->database->prepare( $sql, $params );

		if ( ! is_string( $prepared ) ) {
			return array();
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		$rows = $this->database->get_results( $prepared, ARRAY_A );
		// phpcs:enable

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Counts matching rows.
	 *
	 * @param LogQuery $query Validated query.
	 * @return int
	 */
	public function count( LogQuery $query ): int {
		$where = $this->where_sql( $query );
		$sql   = "SELECT COUNT(*) FROM {$this->table}{$where['sql']}";

		if ( array() !== $where['params'] ) {
			// @phpstan-ignore-next-line Dynamic table is derived exclusively from wpdb::prefix.
			$sql = $this->database->prepare( $sql, $where['params'] );
		}

		if ( ! is_string( $sql ) ) {
			return 0;
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $this->database->get_var( $sql );
		// phpcs:enable
	}

	/**
	 * Deletes every log row.
	 *
	 * @return int
	 */
	public function clear(): int {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$deleted = $this->database->query( "DELETE FROM {$this->table}" );
		// phpcs:enable

		return false === $deleted ? 0 : (int) $deleted;
	}

	/**
	 * Builds prepared WHERE fragments and parameters.
	 *
	 * @param LogQuery $query Validated query.
	 * @return array{sql: string, params: list<string>}
	 */
	private function where_sql( LogQuery $query ): array {
		$clauses = array();
		$params  = array();

		if ( '' !== $query->search ) {
			$like      = '%' . $this->database->esc_like( $query->search ) . '%';
			$clauses[] = '(rule LIKE %s OR ip LIKE %s OR uri LIKE %s OR route LIKE %s OR user_agent LIKE %s)';
			array_push( $params, $like, $like, $like, $like, $like );
		}

		foreach ( array( 'rule', 'ip' ) as $field ) {
			if ( '' !== $query->{$field} ) {
				$clauses[] = "{$field} = %s";
				$params[]  = $query->{$field};
			}
		}

		if ( '' !== $query->date_from ) {
			$clauses[] = 'logged_at >= %s';
			$params[]  = $query->date_from . ' 00:00:00';
		}

		if ( '' !== $query->date_to ) {
			$clauses[] = 'logged_at <= %s';
			$params[]  = $query->date_to . ' 23:59:59';
		}

		return array(
			'sql'    => array() === $clauses ? '' : ' WHERE ' . implode( ' AND ', $clauses ),
			'params' => $params,
		);
	}
}
