<?php
/**
 * XML-RPC policy tests.
 *
 * @package WPHardeningToolkit\Tests
 */

namespace WPHardeningToolkit\Tests\Unit\Hardening;

use PHPUnit\Framework\TestCase;
use WPHardeningToolkit\Hardening\XmlRpcPolicy;

/**
 * Verifies every XML-RPC hardening layer.
 */
final class XmlRpcPolicyTest extends TestCase {
	/**
	 * Enabled hardening disables the XML-RPC flag.
	 *
	 * @return void
	 */
	public function test_disables_xml_rpc(): void {
		$policy = new XmlRpcPolicy();

		self::assertFalse( $policy->filter_enabled( true, true ) );
		self::assertTrue( $policy->filter_enabled( true, false ) );
	}

	/**
	 * Enabled hardening removes registered methods.
	 *
	 * @return void
	 */
	public function test_removes_xml_rpc_methods(): void {
		$policy  = new XmlRpcPolicy();
		$methods = array( 'pingback.ping' => 'pingback_ping' );

		self::assertSame( array(), $policy->filter_methods( $methods, true ) );
		self::assertSame( $methods, $policy->filter_methods( $methods, false ) );
	}

	/**
	 * Enabled hardening removes case variants of X-Pingback.
	 *
	 * @return void
	 */
	public function test_removes_pingback_header(): void {
		$policy  = new XmlRpcPolicy();
		$headers = array(
			'X-Pingback'   => 'https://example.test/xmlrpc.php',
			'Content-Type' => 'text/html',
		);

		self::assertSame( array( 'Content-Type' => 'text/html' ), $policy->filter_headers( $headers, true ) );
		self::assertSame( $headers, $policy->filter_headers( $headers, false ) );
	}
}
