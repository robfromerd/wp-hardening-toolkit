<?php
/**
 * Author enumeration hardening module.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Hardening;

use WP_Query;
use WPHardeningToolkit\Logging\LogEntry;
use WPHardeningToolkit\Logging\Logger;
use WPHardeningToolkit\Module;

/**
 * Converts numeric author enumeration requests to a logged 404.
 */
final class AuthorEnumeration implements Module {
	/**
	 * Detection policy.
	 *
	 * @var AuthorEnumerationPolicy
	 */
	private AuthorEnumerationPolicy $policy;

	/**
	 * Security logger.
	 *
	 * @var Logger
	 */
	private Logger $logger;

	/**
	 * Creates the module.
	 *
	 * @param AuthorEnumerationPolicy $policy Detection policy.
	 * @param Logger                  $logger Security logger.
	 */
	public function __construct( AuthorEnumerationPolicy $policy, Logger $logger ) {
		$this->policy = $policy;
		$this->logger = $logger;
	}

	/**
	 * Registers request interception.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'template_redirect', array( $this, 'maybe_block' ), 0 );
	}

	/**
	 * Converts a numeric author query to a 404.
	 *
	 * @return void
	 */
	public function maybe_block(): void {
		$settings = get_option( 'wpht_settings', array() );
		$enabled  = is_array( $settings ) && ! empty( $settings['block_author_enumeration'] );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only request inspection.
		$author = isset( $_GET['author'] ) && is_scalar( $_GET['author'] )
			? sanitize_text_field( wp_unslash( (string) $_GET['author'] ) )
			: null;
		// phpcs:enable

		if ( ! $this->policy->should_block( $author, $enabled ) ) {
			return;
		}

		$this->logger->log(
			new LogEntry(
				'author_enumeration',
				'blocked',
				$this->server_value( 'REMOTE_ADDR' ),
				$this->server_value( 'REQUEST_METHOD' ),
				$this->server_value( 'REQUEST_URI' ),
				'',
				$this->server_value( 'HTTP_REFERER' ),
				$this->server_value( 'HTTP_USER_AGENT' ),
				is_user_logged_in(),
				get_current_user_id()
			)
		);

		global $wp_query;

		if ( $wp_query instanceof WP_Query ) {
			$wp_query->set_404();
		}

		status_header( 404 );
		nocache_headers();
	}

	/**
	 * Gets and sanitizes a request server value.
	 *
	 * @param string $key Server key.
	 * @return string
	 */
	private function server_value( string $key ): string {
		if ( ! isset( $_SERVER[ $key ] ) || ! is_string( $_SERVER[ $key ] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
	}
}
