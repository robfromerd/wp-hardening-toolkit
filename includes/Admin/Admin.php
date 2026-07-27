<?php
/**
 * WordPress administration module.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Admin;

use WPHardeningToolkit\Logging\LogQuery;
use WPHardeningToolkit\Logging\LogRepository;
use WPHardeningToolkit\Module;

/**
 * Provides settings, dashboard, and security log screens.
 */
final class Admin implements Module {
	/**
	 * Log repository.
	 *
	 * @var LogRepository
	 */
	private LogRepository $logs;

	/**
	 * Creates the admin module.
	 *
	 * @param LogRepository $logs Log repository.
	 */
	public function __construct( LogRepository $logs ) {
		$this->logs = $logs;
	}

	/**
	 * Registers administration hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_pages' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'maybe_export' ) );
		add_action( 'admin_post_wpht_clear_logs', array( $this, 'clear_logs' ) );
	}

	/**
	 * Registers plugin pages.
	 *
	 * @return void
	 */
	public function register_pages(): void {
		add_menu_page(
			__( 'WP Hardening Toolkit', 'wp-hardening-toolkit' ),
			__( 'Hardening', 'wp-hardening-toolkit' ),
			'manage_options',
			'wpht-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-shield-alt',
			80
		);
		add_submenu_page( 'wpht-dashboard', __( 'Security Dashboard', 'wp-hardening-toolkit' ), __( 'Dashboard', 'wp-hardening-toolkit' ), 'manage_options', 'wpht-dashboard', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'wpht-dashboard', __( 'Hardening Settings', 'wp-hardening-toolkit' ), __( 'Settings', 'wp-hardening-toolkit' ), 'manage_options', 'wpht-settings', array( $this, 'render_settings' ) );
		add_submenu_page( 'wpht-dashboard', __( 'Security Log', 'wp-hardening-toolkit' ), __( 'Security Log', 'wp-hardening-toolkit' ), 'manage_options', 'wpht-logs', array( $this, 'render_logs' ) );
	}

	/**
	 * Registers the Settings API schema.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'wpht_settings_group',
			'wpht_settings',
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);
		register_setting(
			'wpht_settings_group',
			'wpht_log_retention_days',
			array(
				'type'              => 'integer',
				'default'           => 90,
				'sanitize_callback' => array( $this, 'sanitize_retention' ),
			)
		);
	}

	/**
	 * Sanitizes hardening settings.
	 *
	 * @param mixed $input Submitted settings.
	 * @return array<string, int>
	 */
	public function sanitize_settings( $input ): array {
		$input   = is_array( $input ) ? $input : array();
		$clean   = array();
		$boolean = array( 'rest_block_batch', 'rest_block_users', 'xmlrpc_disable', 'block_author_enumeration', 'disable_file_editor', 'hide_generator', 'hide_version_queries' );

		foreach ( $boolean as $key ) {
			$clean[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
		}

		$clean['rest_response_code'] = 404 === (int) ( $input['rest_response_code'] ?? 403 ) ? 404 : 403;

		return $clean;
	}

	/**
	 * Sanitizes retention days.
	 *
	 * @param mixed $value Submitted value.
	 * @return int
	 */
	public function sanitize_retention( $value ): int {
		$days = (int) $value;

		return in_array( $days, array( 30, 90, 180, 365 ), true ) ? $days : 90;
	}

	/**
	 * Renders the hardening settings screen.
	 *
	 * @return void
	 */
	public function render_settings(): void {
		$this->require_capability();
		$settings = get_option( 'wpht_settings', array() );
		$settings = is_array( $settings ) ? $settings : array();
		$options  = array(
			'rest_block_batch'         => array( __( 'Block REST batch endpoint', 'wp-hardening-toolkit' ), __( 'Blocks /batch/v1 to reduce request amplification. May affect REST clients using batching. Test by requesting /wp-json/batch/v1 and expecting the selected error.', 'wp-hardening-toolkit' ) ),
			'rest_block_users'         => array( __( 'Block anonymous REST users', 'wp-hardening-toolkit' ), __( 'Prevents unauthenticated user listing while preserving logged-in REST consumers. Test logged out and logged in against /wp-json/wp/v2/users.', 'wp-hardening-toolkit' ) ),
			'xmlrpc_disable'           => array( __( 'Disable XML-RPC', 'wp-hardening-toolkit' ), __( 'Blocks XML-RPC, pingbacks, and its methods. This can break Jetpack, WordPress mobile apps, and legacy publishing clients. Test those integrations after enabling.', 'wp-hardening-toolkit' ) ),
			'block_author_enumeration' => array( __( 'Block numeric author enumeration', 'wp-hardening-toolkit' ), __( 'Returns 404 for numeric ?author= probes that reveal usernames. Author slug archives remain available. Test /?author=1.', 'wp-hardening-toolkit' ) ),
			'disable_file_editor'      => array( __( 'Disable plugin/theme editor', 'wp-hardening-toolkit' ), __( 'Prevents dashboard file editing if an administrator account is compromised. It does not affect filesystem or deployment tools. Test under Appearance and Plugins.', 'wp-hardening-toolkit' ) ),
			'hide_generator'           => array( __( 'Hide generator metadata', 'wp-hardening-toolkit' ), __( 'Removes WordPress generator metadata from pages and feeds. Some auditing tools may no longer detect the version. Inspect page and feed source to test.', 'wp-hardening-toolkit' ) ),
			'hide_version_queries'     => array( __( 'Hide asset version queries', 'wp-hardening-toolkit' ), __( 'Removes ?ver= values from enqueued assets to reduce version disclosure. This can change cache-busting behavior. Clear caches and verify styles/scripts after enabling.', 'wp-hardening-toolkit' ) ),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WP Hardening Toolkit Settings', 'wp-hardening-toolkit' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'wpht_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<?php foreach ( $options as $key => $option ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $option[0] ); ?></th>
							<td>
								<label><input type="checkbox" name="wpht_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?>> <?php esc_html_e( 'Enable', 'wp-hardening-toolkit' ); ?></label>
								<details><summary><?php esc_html_e( 'What does this do?', 'wp-hardening-toolkit' ); ?></summary><p><?php echo esc_html( $option[1] ); ?></p></details>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr><th scope="row"><?php esc_html_e( 'REST blocked response', 'wp-hardening-toolkit' ); ?></th><td><select name="wpht_settings[rest_response_code]"><option value="403" <?php selected( (int) ( $settings['rest_response_code'] ?? 403 ), 403 ); ?>>403</option><option value="404" <?php selected( (int) ( $settings['rest_response_code'] ?? 403 ), 404 ); ?>>404</option></select></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Log retention', 'wp-hardening-toolkit' ); ?></th><td><select name="wpht_log_retention_days">
					<?php
					foreach ( array( 30, 90, 180, 365 ) as $days ) :
						/* translators: %d: number of days logs are retained. */
						$days_label = sprintf( __( '%d days', 'wp-hardening-toolkit' ), $days );
						?>
							<option value="<?php echo esc_attr( (string) $days ); ?>" <?php selected( (int) get_option( 'wpht_log_retention_days', 90 ), $days ); ?>><?php echo esc_html( $days_label ); ?></option><?php endforeach; ?></select></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the security dashboard.
	 *
	 * @return void
	 */
	public function render_dashboard(): void {
		$this->require_capability();
		$settings       = get_option( 'wpht_settings', array() );
		$settings       = is_array( $settings ) ? $settings : array();
		$plugin_updates = get_plugin_updates();
		$theme_updates  = get_theme_updates();
		$checks         = array(
			__( 'WordPress version', 'wp-hardening-toolkit' ) => get_bloginfo( 'version' ),
			__( 'PHP version', 'wp-hardening-toolkit' )    => PHP_VERSION,
			__( 'Plugin updates', 'wp-hardening-toolkit' ) => (string) count( $plugin_updates ),
			__( 'Theme updates', 'wp-hardening-toolkit' )  => (string) count( $theme_updates ),
			__( 'REST batch', 'wp-hardening-toolkit' )     => $this->state( ! empty( $settings['rest_block_batch'] ) ),
			__( 'REST users', 'wp-hardening-toolkit' )     => $this->state( ! empty( $settings['rest_block_users'] ) ),
			__( 'XML-RPC', 'wp-hardening-toolkit' )        => $this->state( ! empty( $settings['xmlrpc_disable'] ) ),
			__( 'File editor', 'wp-hardening-toolkit' )    => $this->state( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ),
			__( 'Version hiding', 'wp-hardening-toolkit' ) => $this->state( ! empty( $settings['hide_version_queries'] ) ),
			__( 'Debug mode', 'wp-hardening-toolkit' )     => $this->state( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ),
			__( '2FA detected', 'wp-hardening-toolkit' )   => $this->state( $this->has_two_factor() ),
			__( 'Inactive plugins', 'wp-hardening-toolkit' ) => (string) max( 0, count( get_plugins() ) - count( get_option( 'active_plugins', array() ) ) ),
		);
		$enabled        = count( array_filter( array_intersect_key( $settings, array_flip( array( 'rest_block_batch', 'rest_block_users', 'xmlrpc_disable', 'block_author_enumeration', 'disable_file_editor', 'hide_generator', 'hide_version_queries' ) ) ) ) );
		$score          = (int) round( ( $enabled / 7 ) * 100 );
		/* translators: %d: hardening score from zero to 100. */
		$score_label = sprintf( __( 'Overall security score: %d/100', 'wp-hardening-toolkit' ), $score );
		?>
		<div class="wrap"><h1><?php esc_html_e( 'Security Dashboard', 'wp-hardening-toolkit' ); ?></h1>
			<p><strong><?php echo esc_html( $score_label ); ?></strong></p>
			<table class="widefat striped"><tbody>
			<?php
			foreach ( $checks as $label => $value ) :
				?>
				<tr><th><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( $value ); ?></td></tr><?php endforeach; ?></tbody></table>
		</div>
		<?php
	}

	/**
	 * Renders searchable and sortable security logs.
	 *
	 * @return void
	 */
	public function render_logs(): void {
		$this->require_capability();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters.
		$query = LogQuery::from_array( wp_unslash( $_GET ) );
		$rows  = $this->logs->find( $query );
		$total = $this->logs->count( $query );
		/* translators: %d: number of security events matching the filters. */
		$total_label = sprintf( __( '%d matching events', 'wp-hardening-toolkit' ), $total );
		?>
		<div class="wrap"><h1><?php esc_html_e( 'Security Log', 'wp-hardening-toolkit' ); ?></h1>
			<form method="get"><input type="hidden" name="page" value="wpht-logs">
				<input type="search" name="s" value="<?php echo esc_attr( $query->search ); ?>" placeholder="<?php esc_attr_e( 'Search logs', 'wp-hardening-toolkit' ); ?>">
				<input type="text" name="rule" value="<?php echo esc_attr( $query->rule ); ?>" placeholder="<?php esc_attr_e( 'Rule', 'wp-hardening-toolkit' ); ?>">
				<input type="text" name="ip" value="<?php echo esc_attr( $query->ip ); ?>" placeholder="<?php esc_attr_e( 'IP', 'wp-hardening-toolkit' ); ?>">
				<input type="date" name="date_from" value="<?php echo esc_attr( $query->date_from ); ?>"><input type="date" name="date_to" value="<?php echo esc_attr( $query->date_to ); ?>">
				<?php submit_button( __( 'Filter', 'wp-hardening-toolkit' ), 'secondary', '', false ); ?>
			</form>
				<p><?php echo esc_html( $total_label ); ?> |
				<a href="<?php echo esc_url( $this->export_url( 'csv', $query ) ); ?>"><?php esc_html_e( 'Export CSV', 'wp-hardening-toolkit' ); ?></a> |
				<a href="<?php echo esc_url( $this->export_url( 'json', $query ) ); ?>"><?php esc_html_e( 'Export JSON', 'wp-hardening-toolkit' ); ?></a>
			</p>
			<table class="widefat striped"><thead><tr>
			<?php
			foreach ( array( 'logged_at', 'rule', 'status', 'ip', 'method', 'uri', 'route', 'user_id' ) as $column ) :
				?>
				<th><?php echo esc_html( $column ); ?></th><?php endforeach; ?></tr></thead>
			<tbody>
			<?php
			foreach ( $rows as $row ) :
				?>
				<tr>
				<?php
				foreach ( array( 'logged_at', 'rule', 'status', 'ip', 'method', 'uri', 'route', 'user_id' ) as $column ) :
					?>
				<td><?php echo esc_html( (string) ( $row[ $column ] ?? '' ) ); ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table>
			<?php
			$pagination = paginate_links(
				array(
					'base'    => add_query_arg( 'paged', '%#%' ),
					'current' => $query->page,
					'total'   => max( 1, (int) ceil( $total / $query->per_page ) ),
				)
			);
			echo is_string( $pagination ) ? wp_kses_post( $pagination ) : '';
			?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="wpht_clear_logs"><?php wp_nonce_field( 'wpht_clear_logs' ); ?><?php submit_button( __( 'Clear log', 'wp-hardening-toolkit' ), 'delete' ); ?></form>
		</div>
		<?php
	}

	/**
	 * Streams a requested CSV or JSON export.
	 *
	 * @return void
	 */
	public function maybe_export(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce is verified below.
		if ( ! isset( $_GET['page'], $_GET['wpht_export'] ) || 'wpht-logs' !== $_GET['page'] ) {
			return;
		}
		// phpcs:enable
		$this->require_capability();
		check_admin_referer( 'wpht_export_logs' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified above.
		$query = LogQuery::from_array( wp_unslash( $_GET ) );
		$query = new LogQuery( 1, 200, $query->sort, $query->direction, $query->search, $query->rule, $query->ip, $query->date_from, $query->date_to );
		$rows  = $this->logs->find( $query );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Verified above.
		$format = sanitize_key( wp_unslash( (string) $_GET['wpht_export'] ) );

		nocache_headers();
		header( 'Content-Disposition: attachment; filename=wpht-log.' . ( 'json' === $format ? 'json' : 'csv' ) );

		if ( 'json' === $format ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			echo wp_json_encode( $rows, JSON_PRETTY_PRINT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Encoded download.
			exit;
		}

		header( 'Content-Type: text/csv; charset=utf-8' );
		$output = fopen( 'php://output', 'w' );

		if ( false !== $output ) {
			fputcsv( $output, array( 'id', 'logged_at', 'rule', 'status', 'ip', 'method', 'uri', 'route', 'referer', 'user_agent', 'authenticated', 'user_id' ) );
			foreach ( $rows as $row ) {
				fputcsv( $output, $row );
			}
				fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Streaming download handle.
		}
		exit;
	}

	/**
	 * Clears the log after capability and nonce verification.
	 *
	 * @return void
	 */
	public function clear_logs(): void {
		$this->require_capability();
		check_admin_referer( 'wpht_clear_logs' );
		$this->logs->clear();
		wp_safe_redirect( admin_url( 'admin.php?page=wpht-logs&cleared=1' ) );
		exit;
	}

	/**
	 * Builds a nonce-protected export URL.
	 *
	 * @param string   $format Export format.
	 * @param LogQuery $query  Current query.
	 * @return string
	 */
	private function export_url( string $format, LogQuery $query ): string {
		$url = add_query_arg(
			array(
				'page'        => 'wpht-logs',
				'wpht_export' => $format,
				's'           => $query->search,
				'rule'        => $query->rule,
				'ip'          => $query->ip,
				'date_from'   => $query->date_from,
				'date_to'     => $query->date_to,
			),
			admin_url( 'admin.php' )
		);

		return wp_nonce_url( $url, 'wpht_export_logs' );
	}

	/**
	 * Terminates access by unauthorized users.
	 *
	 * @return void
	 */
	private function require_capability(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to manage hardening settings.', 'wp-hardening-toolkit' ) );
		}
	}

	/**
	 * Formats a boolean state.
	 *
	 * @param bool $secure Whether the check is secure.
	 * @return string
	 */
	private function state( bool $secure ): string {
		return $secure ? __( 'Hardened', 'wp-hardening-toolkit' ) : __( 'Review', 'wp-hardening-toolkit' );
	}

	/**
	 * Detects common active two-factor plugins.
	 *
	 * @return bool
	 */
	private function has_two_factor(): bool {
		$active = get_option( 'active_plugins', array() );
		$active = is_array( $active ) ? $active : array();

		foreach ( $active as $plugin ) {
			if ( is_string( $plugin ) && ( str_contains( $plugin, 'two-factor' ) || str_contains( $plugin, 'wordfence' ) || str_contains( $plugin, 'ithemes-security' ) ) ) {
				return true;
			}
		}

		return false;
	}
}
