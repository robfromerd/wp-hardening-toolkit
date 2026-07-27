<?php
/**
 * Plugin Name:       WP Hardening Toolkit
 * Plugin URI:        https://github.com/wp-hardening-toolkit/wp-hardening-toolkit
 * Description:       Modular, opt-in WordPress security hardening with auditable logging.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            WP Hardening Toolkit Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-hardening-toolkit
 *
 * @package WPHardeningToolkit
 */

defined( 'ABSPATH' ) || exit;

define( 'WPHT_VERSION', '0.1.0' );
define( 'WPHT_FILE', __FILE__ );
define( 'WPHT_PATH', plugin_dir_path( __FILE__ ) );

$wpht_autoloader = WPHT_PATH . 'vendor/autoload.php';

if ( is_readable( $wpht_autoloader ) ) {
	require_once $wpht_autoloader;
	\WPHardeningToolkit\Plugin::instance()->boot();
}
