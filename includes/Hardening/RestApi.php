<?php
/**
 * REST API hardening module.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Hardening;

use WP_Error;
use WP_REST_Request;
use WP_REST_Server;
use WPHardeningToolkit\Logging\LogEntry;
use WPHardeningToolkit\Logging\Logger;
use WPHardeningToolkit\Module;

/**
 * Blocks configured high-risk REST routes.
 */
final class RestApi implements Module {
	/**
	 * Route policy.
	 *
	 * @var RestRoutePolicy
	 */
	private RestRoutePolicy $policy;

	/**
	 * Security logger.
	 *
	 * @var Logger
	 */
	private Logger $logger;

	/**
	 * Creates the module.
	 *
	 * @param RestRoutePolicy $policy Route policy.
	 * @param Logger          $logger Security logger.
	 */
	public function __construct( RestRoutePolicy $policy, Logger $logger ) {
		$this->policy = $policy;
		$this->logger = $logger;
	}

	/**
	 * Registers REST interception.
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'rest_pre_dispatch', array( $this, 'intercept' ), 10, 3 );
	}

	/**
	 * Blocks a configured REST request.
	 *
	 * @param mixed           $result  Existing response.
	 * @param WP_REST_Server  $server  REST server.
	 * @param WP_REST_Request $request REST request.
	 * @return mixed
	 */
	public function intercept( $result, WP_REST_Server $server, WP_REST_Request $request ) {
		unset( $server );
		$settings = get_option( 'wpht_settings', array() );
		$settings = is_array( $settings ) ? $settings : array();
		$rule     = $this->policy->blocked_rule(
			$request->get_route(),
			is_user_logged_in(),
			! empty( $settings['rest_block_batch'] ),
			! empty( $settings['rest_block_users'] )
		);

		if ( null === $rule ) {
			return $result;
		}

		$route = $this->policy->normalize( $request->get_route() );
		$this->logger->log(
			new LogEntry(
				$rule,
				'blocked',
				$this->server_value( 'REMOTE_ADDR' ),
				$request->get_method(),
				$this->server_value( 'REQUEST_URI' ),
				$route,
				$this->server_value( 'HTTP_REFERER' ),
				$this->server_value( 'HTTP_USER_AGENT' ),
				is_user_logged_in(),
				get_current_user_id()
			)
		);

		$status = 404 === (int) ( $settings['rest_response_code'] ?? 403 ) ? 404 : 403;

		return new WP_Error(
			'wpht_rest_blocked',
			__( 'This REST API route is unavailable.', 'wp-hardening-toolkit' ),
			array( 'status' => $status )
		);
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
