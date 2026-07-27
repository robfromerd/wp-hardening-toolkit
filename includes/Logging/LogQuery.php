<?php
/**
 * Validated log query.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

/**
 * Carries allowlisted log viewer filters and pagination.
 */
final class LogQuery {
	/**
	 * Allowed sort columns.
	 *
	 * @var list<string>
	 */
	private const SORT_COLUMNS = array( 'id', 'logged_at', 'rule', 'status', 'ip', 'method' );

	/**
	 * Creates a validated query.
	 *
	 * @param int    $page      One-based page.
	 * @param int    $per_page  Rows per page.
	 * @param string $sort      Sort column.
	 * @param string $direction Sort direction.
	 * @param string $search    General search.
	 * @param string $rule      Rule filter.
	 * @param string $ip        IP filter.
	 * @param string $date_from Inclusive start date.
	 * @param string $date_to   Inclusive end date.
	 */
	public function __construct(
		public readonly int $page = 1,
		public readonly int $per_page = 25,
		public readonly string $sort = 'logged_at',
		public readonly string $direction = 'DESC',
		public readonly string $search = '',
		public readonly string $rule = '',
		public readonly string $ip = '',
		public readonly string $date_from = '',
		public readonly string $date_to = ''
	) {
	}

	/**
	 * Creates a query from untrusted request values.
	 *
	 * @param array<string, mixed> $values Request values.
	 * @return self
	 */
	public static function from_array( array $values ): self {
		$sort      = isset( $values['orderby'] ) && is_string( $values['orderby'] ) ? $values['orderby'] : 'logged_at';
		$direction = isset( $values['order'] ) && is_string( $values['order'] ) ? strtoupper( $values['order'] ) : 'DESC';

		return new self(
			max( 1, (int) ( $values['paged'] ?? 1 ) ),
			min( 200, max( 1, (int) ( $values['per_page'] ?? 25 ) ) ),
			in_array( $sort, self::SORT_COLUMNS, true ) ? $sort : 'logged_at',
			in_array( $direction, array( 'ASC', 'DESC' ), true ) ? $direction : 'DESC',
			self::text( $values['s'] ?? '' ),
			self::text( $values['rule'] ?? '' ),
			self::text( $values['ip'] ?? '' ),
			self::date( $values['date_from'] ?? '' ),
			self::date( $values['date_to'] ?? '' )
		);
	}

	/**
	 * Gets the SQL offset.
	 *
	 * @return int
	 */
	public function offset(): int {
		return ( $this->page - 1 ) * $this->per_page;
	}

	/**
	 * Converts a scalar to a trimmed string.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	private static function text( $value ): string {
		return is_scalar( $value ) ? trim( (string) $value ) : '';
	}

	/**
	 * Accepts only ISO dates.
	 *
	 * @param mixed $value Raw date.
	 * @return string
	 */
	private static function date( $value ): string {
		$date = self::text( $value );

		return 1 === preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '';
	}
}
