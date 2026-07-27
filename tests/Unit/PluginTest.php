<?php
/**
 * Plugin architecture tests.
 *
 * @package WPHardeningToolkit\Tests\Unit
 */

namespace WPHardeningToolkit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPHardeningToolkit\Plugin;

/**
 * Verifies the plugin coordinator.
 */
final class PluginTest extends TestCase {
	/**
	 * The coordinator exposes one shared instance.
	 *
	 * @return void
	 */
	public function test_instance_is_shared(): void {
		self::assertSame( Plugin::instance(), Plugin::instance() );
	}
}
