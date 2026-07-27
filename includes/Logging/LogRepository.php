<?php
/**
 * Administrative log repository contract.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

/**
 * Provides log viewer operations within the logging layer.
 */
interface LogRepository {
	/**
	 * Finds matching log rows.
	 *
	 * @param LogQuery $query Validated query.
	 * @return list<array<string, int|string>>
	 */
	public function find( LogQuery $query ): array;

	/**
	 * Counts matching log rows.
	 *
	 * @param LogQuery $query Validated query.
	 * @return int
	 */
	public function count( LogQuery $query ): int;

	/**
	 * Deletes every log row.
	 *
	 * @return int
	 */
	public function clear(): int;
}
