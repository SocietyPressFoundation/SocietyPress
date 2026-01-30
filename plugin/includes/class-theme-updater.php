<?php
/**
 * Theme Auto-Updater
 *
 * Hooks into WordPress theme update system to check for SocietyPress theme updates
 * from StricklinDevelopment.com instead of WordPress.org.
 *
 * WHY: Allows self-hosted update distribution for shareware model.
 * No license authentication - updates are freely available.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Theme_Updater
 *
 * Handles theme updates from remote server.
 */
class SocietyPress_Theme_Updater {

	/**
	 * Theme slug (folder name).
	 *
	 * @var string
	 */
	private string $theme_slug = 'societypress';

	/**
	 * Current theme version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Update server URL.
	 *
	 * @var string
	 */
	private string $update_url = 'https://getsocietypress.org/api/v1/themes/societypress';

	/**
	 * Cache key for update data.
	 *
	 * @var string
	 */
	private string $cache_key = 'societypress_theme_update_data';

	/**
	 * Constructor.
	 *
	 * @param string $theme_slug Theme slug (folder name).
	 * @param string $version    Current theme version.
	 */
	public function __construct( string $theme_slug, string $version ) {
		$this->theme_slug = $theme_slug;
		$this->version    = $version;

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * WHY: Hook into WordPress theme update system to inject our update info.
	 */
	private function init_hooks(): void {
		// Check for updates
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_update' ) );

		// Theme information popup (for "View details" link)
		add_filter( 'themes_api', array( $this, 'theme_info' ), 10, 3 );

		// After update cleanup
		add_action( 'upgrader_process_complete', array( $this, 'after_update' ), 10, 2 );
	}

	/**
	 * Check for theme updates.
	 *
	 * WHY: Injects our update info into WordPress's theme update checker.
	 *
	 * @param object $transient Update transient.
	 * @return object Modified transient.
	 */
	public function check_for_update( $transient ) {
		if ( empty( $transient ) || ! isset( $transient->checked ) ) {
			return $transient;
		}

		$remote = $this->get_remote_version();

		if ( ! $remote || is_wp_error( $remote ) ) {
			return $transient;
		}

		if ( version_compare( $this->version, $remote['new_version'], '<' ) ) {
			$transient->response[ $this->theme_slug ] = $remote;
		} else {
			$transient->no_update[ $this->theme_slug ] = $remote;
		}

		return $transient;
	}

	/**
	 * Get remote version information.
	 *
	 * WHY: Fetches latest version info from update server with caching.
	 *
	 * @return array|false Version info array or false on error.
	 */
	private function get_remote_version() {
		$cached = get_transient( $this->cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_post(
			$this->update_url . '/update-check',
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( array(
					'theme_slug'      => $this->theme_slug,
					'current_version' => $this->version,
					'site_url'        => get_site_url(),
					'wp_version'      => get_bloginfo( 'version' ),
					'php_version'     => PHP_VERSION,
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			return false;
		}

		$data = json_decode( $body, true );

		if ( empty( $data ) || ! isset( $data['version'] ) ) {
			return false;
		}

		// Build update array in WordPress format
		$update = array(
			'theme'        => $this->theme_slug,
			'new_version'  => $data['version'],
			'url'          => $data['homepage'] ?? '',
			'package'      => $data['download_url'] ?? '',
			'requires'     => $data['requires'] ?? '',
			'requires_php' => $data['requires_php'] ?? '',
		);

		set_transient( $this->cache_key, $update, 12 * HOUR_IN_SECONDS );

		return $update;
	}

	/**
	 * Provide theme information for the update popup.
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The type of information being requested.
	 * @param object             $args   Theme API arguments.
	 * @return false|object Modified result.
	 */
	public function theme_info( $result, $action, $args ) {
		if ( 'theme_information' !== $action ) {
			return $result;
		}

		if ( $this->theme_slug !== $args->slug ) {
			return $result;
		}

		$remote = $this->get_remote_info();

		if ( ! $remote || is_wp_error( $remote ) ) {
			return $result;
		}

		return $remote;
	}

	/**
	 * Get detailed theme information from server.
	 *
	 * @return object|false Theme info object or false on error.
	 */
	private function get_remote_info() {
		$response = wp_remote_post(
			$this->update_url . '/info',
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( array(
					'theme_slug' => $this->theme_slug,
					'site_url'   => get_site_url(),
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			return false;
		}

		$data = json_decode( $body );

		return ! empty( $data ) ? $data : false;
	}

	/**
	 * After update cleanup.
	 *
	 * @param WP_Upgrader $upgrader WP_Upgrader instance.
	 * @param array       $options  Update options.
	 */
	public function after_update( $upgrader, $options ): void {
		if ( 'update' !== $options['action'] || 'theme' !== $options['type'] ) {
			return;
		}

		if ( empty( $options['themes'] ) || ! in_array( $this->theme_slug, $options['themes'], true ) ) {
			return;
		}

		delete_transient( $this->cache_key );
	}

	/**
	 * Force check for updates (bypass cache).
	 */
	public function force_check(): void {
		delete_transient( $this->cache_key );
		wp_clean_themes_cache();
	}
}
