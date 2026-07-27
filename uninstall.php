<?php
/**
 * Plugin uninstall routine.
 *
 * @package WPHardeningToolkit
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$autoloader = __DIR__ . '/vendor/autoload.php';

if ( is_readable( $autoloader ) ) {
	require_once $autoloader;
	\WPHardeningToolkit\Logging\Uninstaller::uninstall();
}
