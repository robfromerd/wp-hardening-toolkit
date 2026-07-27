<?php
/**
 * Main plugin coordinator.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit;

use WPHardeningToolkit\Hardening\AuthorEnumeration;
use WPHardeningToolkit\Hardening\AuthorEnumerationPolicy;
use WPHardeningToolkit\Logging\Retention;
use WPHardeningToolkit\Logging\WpdbLogStore;
use WPHardeningToolkit\Logging\Logger;
use WPHardeningToolkit\Hardening\RestApi;
use WPHardeningToolkit\Hardening\RestRoutePolicy;
use WPHardeningToolkit\Hardening\XmlRpc;
use WPHardeningToolkit\Hardening\XmlRpcPolicy;
use wpdb;

/**
 * Coordinates plugin services and modules.
 */
final class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Registered modules.
	 *
	 * @var list<Module>
	 */
	private array $modules = array();

	/**
	 * Gets the shared plugin instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers plugin hooks.
	 *
	 * @return void
	 */
	public function boot(): void {
		/**
		 * Filters the modules loaded by the toolkit.
		 *
		 * @since 0.1.0
		 *
		 * @param list<Module> $modules Modules to register.
		 */
		$this->modules = apply_filters( 'wpht_modules', $this->default_modules() );

		foreach ( $this->modules as $module ) {
			$module->register();
		}
	}

	/**
	 * Creates built-in infrastructure modules.
	 *
	 * @return list<Module>
	 */
	private function default_modules(): array {
		global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
			return array();
		}

		$store  = new WpdbLogStore( $wpdb );
		$logger = new Logger( $store );

		return array(
			new Retention( $store ),
			new RestApi( new RestRoutePolicy(), $logger ),
			new XmlRpc( new XmlRpcPolicy() ),
			new AuthorEnumeration( new AuthorEnumerationPolicy(), $logger ),
		);
	}
}
