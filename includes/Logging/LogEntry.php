<?php
/**
 * Immutable security log entry.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

/**
 * Describes one security event without performing persistence.
 */
final class LogEntry {
	/**
	 * Creates a log entry.
	 *
	 * @param string $rule          Rule identifier.
	 * @param string $status        Event status.
	 * @param string $ip            Client IP address.
	 * @param string $method        HTTP method.
	 * @param string $uri           Request URI.
	 * @param string $route         Normalized route.
	 * @param string $referer       Referring URL.
	 * @param string $user_agent    User agent.
	 * @param bool   $authenticated Whether a user is authenticated.
	 * @param int    $user_id       WordPress user ID.
	 */
	public function __construct(
		public readonly string $rule,
		public readonly string $status,
		public readonly string $ip = '',
		public readonly string $method = '',
		public readonly string $uri = '',
		public readonly string $route = '',
		public readonly string $referer = '',
		public readonly string $user_agent = '',
		public readonly bool $authenticated = false,
		public readonly int $user_id = 0
	) {
	}

	/**
	 * Converts the event to a database row.
	 *
	 * @param string $logged_at UTC database timestamp.
	 * @return array<string, int|string>
	 */
	public function to_row( string $logged_at ): array {
		return array(
			'logged_at'     => $logged_at,
			'rule'          => $this->rule,
			'status'        => $this->status,
			'ip'            => $this->ip,
			'method'        => $this->method,
			'uri'           => $this->uri,
			'route'         => $this->route,
			'referer'       => $this->referer,
			'user_agent'    => $this->user_agent,
			'authenticated' => (int) $this->authenticated,
			'user_id'       => $this->user_id,
		);
	}
}
