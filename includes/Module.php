<?php
/**
 * Module contract.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit;

/**
 * Contract implemented by independently registered plugin modules.
 */
interface Module {
	/**
	 * Registers WordPress hooks for the module.
	 *
	 * @return void
	 */
	public function register(): void;
}
