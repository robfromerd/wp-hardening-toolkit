<?php
/**
 * Logging schema installer.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Logging;

use wpdb;

/**
 * Creates and upgrades the custom logging table.
 */
final class Installer {
	/**
	 * Installs the current schema.
	 *
	 * @return void
	 */
	public static function activate(): void {
		global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = $wpdb->prefix . 'wpht_log';
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			logged_at datetime NOT NULL,
			rule varchar(100) NOT NULL,
			status varchar(30) NOT NULL,
			ip varchar(45) NOT NULL DEFAULT '',
			method varchar(10) NOT NULL DEFAULT '',
			uri text NOT NULL,
			route varchar(255) NOT NULL DEFAULT '',
			referer text NOT NULL,
			user_agent text NOT NULL,
			authenticated tinyint(1) unsigned NOT NULL DEFAULT 0,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY logged_at (logged_at),
			KEY rule (rule),
			KEY ip (ip)
		) {$charset_collate};";

		dbDelta( $sql );
		update_option( 'wpht_db_version', '1' );
	}
}
