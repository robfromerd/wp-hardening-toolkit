<?php
/**
 * XML-RPC hardening policy.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Hardening;

/**
 * Provides pure transformations for XML-RPC hardening.
 */
final class XmlRpcPolicy {
	/**
	 * Disables XML-RPC when the rule is enabled.
	 *
	 * @param bool $current Existing enabled state.
	 * @param bool $enabled Hardening setting.
	 * @return bool
	 */
	public function filter_enabled( bool $current, bool $enabled ): bool {
		return $enabled ? false : $current;
	}

	/**
	 * Removes all XML-RPC methods when enabled.
	 *
	 * @param array<string, callable|string> $methods Registered methods.
	 * @param bool                           $enabled Hardening setting.
	 * @return array<string, callable|string>
	 */
	public function filter_methods( array $methods, bool $enabled ): array {
		return $enabled ? array() : $methods;
	}

	/**
	 * Removes the X-Pingback response header when enabled.
	 *
	 * @param array<string, string> $headers Response headers.
	 * @param bool                  $enabled Hardening setting.
	 * @return array<string, string>
	 */
	public function filter_headers( array $headers, bool $enabled ): array {
		if ( $enabled ) {
			unset( $headers['X-Pingback'], $headers['x-pingback'] );
		}

		return $headers;
	}
}
