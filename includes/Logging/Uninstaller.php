<?php
/**
 * Logging data uninstaller.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

use wpdb;

/**
 * Removes logging data when the plugin is deleted.
 */
final class Uninstaller {
	/**
	 * Removes the custom table and logging options.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		global $wpdb;

		if ( $wpdb instanceof wpdb ) {
			$table = $wpdb->prefix . 'wpht_log';
			// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
			// phpcs:enable
		}

		delete_option( 'wpht_db_version' );
		delete_option( 'wpht_log_retention_days' );
		wp_clear_scheduled_hook( Retention::HOOK );
	}
}
