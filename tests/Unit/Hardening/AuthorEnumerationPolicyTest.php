<?php
/**
 * Author enumeration policy tests.
 *
 * @package WPHardeningToolkit\Tests
 */

namespace WPHardeningToolkit\Tests\Unit\Hardening;

use PHPUnit\Framework\TestCase;
use WPHardeningToolkit\Hardening\AuthorEnumerationPolicy;

/**
 * Verifies author enumeration detection.
 */
final class AuthorEnumerationPolicyTest extends TestCase {
	/**
	 * Positive numeric author queries are blocked when enabled.
	 *
	 * @dataProvider numeric_authors
	 *
	 * @param int|string $author Author value.
	 * @return void
	 */
	public function test_blocks_numeric_author_queries( $author ): void {
		$policy = new AuthorEnumerationPolicy();

		self::assertTrue( $policy->should_block( $author, true ) );
	}

	/**
	 * Numeric author values.
	 *
	 * @return array<string, array{int|string}>
	 */
	public function numeric_authors(): array {
		return array(
			'integer' => array( 1 ),
			'string'  => array( '42' ),
			'padded'  => array( '007' ),
		);
	}

	/**
	 * Non-numeric author values remain available.
	 *
	 * @dataProvider safe_authors
	 *
	 * @param mixed $author Author value.
	 * @return void
	 */
	public function test_allows_non_numeric_author_queries( $author ): void {
		$policy = new AuthorEnumerationPolicy();

		self::assertFalse( $policy->should_block( $author, true ) );
	}

	/**
	 * Non-numeric author values.
	 *
	 * @return array<string, array{mixed}>
	 */
	public function safe_authors(): array {
		return array(
			'slug'     => array( 'admin' ),
			'zero'     => array( '0' ),
			'negative' => array( '-1' ),
			'missing'  => array( null ),
			'array'    => array( array( '1' ) ),
		);
	}

	/**
	 * The rule is disabled by default.
	 *
	 * @return void
	 */
	public function test_disabled_rule_allows_numeric_query(): void {
		$policy = new AuthorEnumerationPolicy();

		self::assertFalse( $policy->should_block( '1', false ) );
	}
}
