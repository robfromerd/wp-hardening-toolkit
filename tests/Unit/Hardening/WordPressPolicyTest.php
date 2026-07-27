<?php
/**
 * WordPress hardening policy tests.
 *
 * @package WPHardeningToolkit\Tests
 */

namespace WPHardeningToolkit\Tests\Unit\Hardening;

use PHPUnit\Framework\TestCase;
use WPHardeningToolkit\Hardening\WordPressPolicy;

/**
 * Verifies WordPress information-disclosure transformations.
 */
final class WordPressPolicyTest extends TestCase {
	/**
	 * Version arguments are removed without damaging other query arguments.
	 *
	 * @dataProvider version_urls
	 *
	 * @param string $source   Input URL.
	 * @param string $expected Expected URL.
	 * @return void
	 */
	public function test_strips_version_query_argument( string $source, string $expected ): void {
		$policy = new WordPressPolicy();

		self::assertSame( $expected, $policy->strip_version( $source, true ) );
	}

	/**
	 * Asset URL variants.
	 *
	 * @return array<string, array{string, string}>
	 */
	public function version_urls(): array {
		return array(
			'only version'   => array( '/style.css?ver=6.9.4', '/style.css' ),
			'version first'  => array( '/app.js?ver=1.2&async=1', '/app.js?async=1' ),
			'version last'   => array( '/app.js?async=1&ver=1.2', '/app.js?async=1' ),
			'version middle' => array( '/app.js?a=1&ver=1.2&b=2', '/app.js?a=1&b=2' ),
			'fragment'       => array( '/app.js?ver=1.2#main', '/app.js#main' ),
			'unrelated'      => array( '/app.js?v=1.2', '/app.js?v=1.2' ),
		);
	}

	/**
	 * Disabled version hiding leaves the source unchanged.
	 *
	 * @return void
	 */
	public function test_disabled_rule_preserves_version(): void {
		$policy = new WordPressPolicy();
		$source = '/style.css?ver=6.9.4';

		self::assertSame( $source, $policy->strip_version( $source, false ) );
	}
}
