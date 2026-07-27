<?php
/**
 * Author enumeration policy.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Hardening;

/**
 * Detects numeric WordPress author archive probes.
 */
final class AuthorEnumerationPolicy {
	/**
	 * Determines whether a query is a blocked author probe.
	 *
	 * @param mixed $author  Author query value.
	 * @param bool  $enabled Whether blocking is enabled.
	 * @return bool
	 */
	public function should_block( $author, bool $enabled ): bool {
		if ( ! $enabled || ( ! is_int( $author ) && ! is_string( $author ) ) ) {
			return false;
		}

		$value = (string) $author;

		return '' !== $value && ctype_digit( $value ) && (int) $value > 0;
	}
}
