<?php
/**
 * REST route policy tests.
 *
 * @package WPHardeningToolkit\Tests
 */

namespace WPHardeningToolkit\Tests\Unit\Hardening;

use PHPUnit\Framework\TestCase;
use WPHardeningToolkit\Hardening\RestRoutePolicy;

/**
 * Verifies REST blocking decisions.
 */
final class RestRoutePolicyTest extends TestCase {
	/**
	 * WordPress URL helper stand-in is loaded by the test bootstrap.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'wp_parse_url' ) ) {
			require_once dirname( __DIR__, 3 ) . '/tests/wp-function-stubs.php';
		}
	}

	/**
	 * Route variants normalize before batch comparison.
	 *
	 * @dataProvider batch_routes
	 *
	 * @param string $route Route variant.
	 * @return void
	 */
	public function test_blocks_normalized_batch_routes( string $route ): void {
		$policy = new RestRoutePolicy();

		self::assertSame( RestRoutePolicy::BATCH_RULE, $policy->blocked_rule( $route, false, true, false ) );
	}

	/**
	 * Batch route variants.
	 *
	 * @return array<string, array{string}>
	 */
	public function batch_routes(): array {
		return array(
			'canonical'  => array( '/batch/v1' ),
			'trailing'   => array( '/batch/v1/' ),
			'wp-json'    => array( '/wp-json/batch/v1' ),
			'full URL'   => array( 'https://example.test/wp-json//batch/v1/' ),
			'mixed case' => array( '/BATCH/V1' ),
		);
	}

	/**
	 * Anonymous user collection and item routes are blocked.
	 *
	 * @return void
	 */
	public function test_blocks_anonymous_users_routes(): void {
		$policy = new RestRoutePolicy();

		self::assertSame( RestRoutePolicy::USERS_RULE, $policy->blocked_rule( '/wp/v2/users', false, false, true ) );
		self::assertSame( RestRoutePolicy::USERS_RULE, $policy->blocked_rule( '/wp/v2/users/42', false, false, true ) );
	}

	/**
	 * Authenticated administrators remain unaffected by the users rule.
	 *
	 * @return void
	 */
	public function test_logged_in_users_are_unaffected(): void {
		$policy = new RestRoutePolicy();

		self::assertNull( $policy->blocked_rule( '/wp/v2/users', true, false, true ) );
	}

	/**
	 * Every REST rule is disabled by default.
	 *
	 * @return void
	 */
	public function test_disabled_rules_do_not_block(): void {
		$policy = new RestRoutePolicy();

		self::assertNull( $policy->blocked_rule( '/batch/v1', false, false, false ) );
		self::assertNull( $policy->blocked_rule( '/wp/v2/users', false, false, false ) );
	}
}
