<?php
/**
 * XML-RPC hardening module.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Hardening;

use WPHardeningToolkit\Module;

/**
 * Disables every WordPress XML-RPC entry point when opted in.
 */
final class XmlRpc implements Module {
	/**
	 * XML-RPC policy.
	 *
	 * @var XmlRpcPolicy
	 */
	private XmlRpcPolicy $policy;

	/**
	 * Creates the module.
	 *
	 * @param XmlRpcPolicy $policy XML-RPC policy.
	 */
	public function __construct( XmlRpcPolicy $policy ) {
		$this->policy = $policy;
	}

	/**
	 * Registers XML-RPC hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'xmlrpc_enabled', array( $this, 'filter_enabled' ) );
		add_filter( 'xmlrpc_methods', array( $this, 'filter_methods' ) );
		add_filter( 'wp_headers', array( $this, 'filter_headers' ) );
		add_action( 'plugins_loaded', array( $this, 'maybe_block_direct' ), 1 );
	}

	/**
	 * Filters the XML-RPC enabled flag.
	 *
	 * @param bool $enabled Existing state.
	 * @return bool
	 */
	public function filter_enabled( bool $enabled ): bool {
		return $this->policy->filter_enabled( $enabled, $this->is_enabled() );
	}

	/**
	 * Filters registered XML-RPC methods.
	 *
	 * @param array<string, callable|string> $methods Registered methods.
	 * @return array<string, callable|string>
	 */
	public function filter_methods( array $methods ): array {
		return $this->policy->filter_methods( $methods, $this->is_enabled() );
	}

	/**
	 * Filters response headers.
	 *
	 * @param array<string, string> $headers Response headers.
	 * @return array<string, string>
	 */
	public function filter_headers( array $headers ): array {
		return $this->policy->filter_headers( $headers, $this->is_enabled() );
	}

	/**
	 * Blocks execution of xmlrpc.php before WordPress dispatch.
	 *
	 * @return void
	 */
	public function maybe_block_direct(): void {
		$script = isset( $_SERVER['SCRIPT_FILENAME'] ) && is_string( $_SERVER['SCRIPT_FILENAME'] )
			? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) )
			: '';

		if ( ! $this->is_enabled() || 'xmlrpc.php' !== strtolower( basename( $script ) ) ) {
			return;
		}

		status_header( 403 );
		nocache_headers();
		exit;
	}

	/**
	 * Determines whether XML-RPC blocking is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled(): bool {
		$settings = get_option( 'wpht_settings', array() );

		return is_array( $settings ) && ! empty( $settings['xmlrpc_disable'] );
	}
}
