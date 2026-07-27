<?php
/**
 * REST route policy.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Hardening;

/**
 * Makes testable blocking decisions for normalized REST routes.
 */
final class RestRoutePolicy {
	public const BATCH_RULE = 'rest_batch';
	public const USERS_RULE = 'rest_users';

	/**
	 * Normalizes a REST route for exact policy comparisons.
	 *
	 * @param string $route Raw route.
	 * @return string
	 */
	public function normalize( string $route ): string {
		$path = wp_parse_url( $route, PHP_URL_PATH );
		$path = is_string( $path ) ? $path : $route;
		$path = preg_replace( '#/+#', '/', '/' . ltrim( $path, '/' ) );
		$path = is_string( $path ) ? $path : '/';

		if ( str_starts_with( $path, '/wp-json/' ) ) {
			$path = substr( $path, 8 );
		}

		$normalized = '/' . trim( $path, '/' );

		return '/' === $normalized ? '/' : strtolower( $normalized );
	}

	/**
	 * Gets the matching block rule.
	 *
	 * @param string $route       REST route.
	 * @param bool   $logged_in   Whether the requester is authenticated.
	 * @param bool   $block_batch Whether batch blocking is enabled.
	 * @param bool   $block_users Whether user endpoint blocking is enabled.
	 * @return string|null
	 */
	public function blocked_rule( string $route, bool $logged_in, bool $block_batch, bool $block_users ): ?string {
		$normalized = $this->normalize( $route );

		if ( $block_batch && '/batch/v1' === $normalized ) {
			return self::BATCH_RULE;
		}

		if ( $block_users && ! $logged_in && ( '/wp/v2/users' === $normalized || str_starts_with( $normalized, '/wp/v2/users/' ) ) ) {
			return self::USERS_RULE;
		}

		return null;
	}
}
