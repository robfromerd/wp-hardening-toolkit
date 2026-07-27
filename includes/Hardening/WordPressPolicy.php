<?php
/**
 * WordPress hardening policy.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Hardening;

/**
 * Provides pure transformations for WordPress information disclosure.
 */
final class WordPressPolicy {
	/**
	 * Removes a version query argument from an asset URL.
	 *
	 * @param string $source  Asset URL.
	 * @param bool   $enabled Whether version hiding is enabled.
	 * @return string
	 */
	public function strip_version( string $source, bool $enabled ): string {
		if ( ! $enabled ) {
			return $source;
		}

		$result = preg_replace_callback(
			'/([?&])ver=[^&#]*(&?)/i',
			static function ( array $matches ): string {
				if ( '?' === $matches[1] && '&' === $matches[2] ) {
					return '?';
				}

				if ( '&' === $matches[1] && '&' === $matches[2] ) {
					return '&';
				}

				return '';
			},
			$source
		);

		return is_string( $result ) ? $result : $source;
	}
}
