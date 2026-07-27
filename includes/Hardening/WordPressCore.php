<?php
/**
 * WordPress core hardening module.
 *
 * @package WPHardeningToolkit
 */

namespace WPHardeningToolkit\Hardening;

use WPHardeningToolkit\Module;

/**
 * Applies optional WordPress information and editor hardening.
 */
final class WordPressCore implements Module {
	/**
	 * Transformation policy.
	 *
	 * @var WordPressPolicy
	 */
	private WordPressPolicy $policy;

	/**
	 * Creates the module.
	 *
	 * @param WordPressPolicy $policy Transformation policy.
	 */
	public function __construct( WordPressPolicy $policy ) {
		$this->policy = $policy;
	}

	/**
	 * Registers WordPress hardening hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'plugins_loaded', array( $this, 'maybe_disable_file_editor' ), 1 );
		add_action( 'init', array( $this, 'maybe_hide_generator' ), 1 );
		add_filter( 'the_generator', array( $this, 'filter_generator' ) );
		add_filter( 'script_loader_src', array( $this, 'filter_asset_version' ) );
		add_filter( 'style_loader_src', array( $this, 'filter_asset_version' ) );
	}

	/**
	 * Defines the WordPress file editor constant when opted in.
	 *
	 * @return void
	 */
	public function maybe_disable_file_editor(): void {
		if ( $this->setting_enabled( 'disable_file_editor' ) && ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}
	}

	/**
	 * Removes generator output actions when opted in.
	 *
	 * @return void
	 */
	public function maybe_hide_generator(): void {
		if ( ! $this->setting_enabled( 'hide_generator' ) ) {
			return;
		}

		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'rss2_head', 'the_generator' );
		remove_action( 'rss_head', 'the_generator' );
		remove_action( 'rdf_header', 'the_generator' );
		remove_action( 'atom_head', 'the_generator' );
	}

	/**
	 * Removes generator text when opted in.
	 *
	 * @param string $generator Existing generator text.
	 * @return string
	 */
	public function filter_generator( string $generator ): string {
		return $this->setting_enabled( 'hide_generator' ) ? '' : $generator;
	}

	/**
	 * Removes version arguments from enqueued asset URLs.
	 *
	 * @param string $source Asset URL.
	 * @return string
	 */
	public function filter_asset_version( string $source ): string {
		return $this->policy->strip_version( $source, $this->setting_enabled( 'hide_version_queries' ) );
	}

	/**
	 * Reads one opt-in hardening setting.
	 *
	 * @param string $key Setting key.
	 * @return bool
	 */
	private function setting_enabled( string $key ): bool {
		$settings = get_option( 'wpht_settings', array() );

		return is_array( $settings ) && ! empty( $settings[ $key ] );
	}
}
