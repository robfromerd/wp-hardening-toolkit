<?php
/**
 * Main plugin coordinator.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit;

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
		$this->modules = apply_filters( 'wpht_modules', array() );

		foreach ( $this->modules as $module ) {
			$module->register();
		}
	}
}
