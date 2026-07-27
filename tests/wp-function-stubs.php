<?php
/**
 * Minimal pure-function WordPress test doubles.
 *
 * @package WPHardeningToolkit\Tests
 */

if ( ! function_exists( 'wp_parse_url' ) ) {
	/**
	 * Test double for wp_parse_url().
	 *
	 * @param string $url       URL to parse.
	 * @param int    $component Component constant.
	 * @return string|false|null
	 */
	function wp_parse_url( string $url, int $component = -1 ) {
		return parse_url( $url, $component ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- Deliberate test double.
	}
}
