<?php
/**
 * Theme Auto-Updater
 *
 * Hooks into WordPress theme update system to check for SocietyPress theme updates
 * from StricklinDevelopment.com instead of WordPress.org.
 *
 * WHY: Commercial themes need custom update delivery tied to license validation.
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
	 * WHY: Points to localhost for testing, will be stricklindevelopment.com/studiopress for production.
	 *
	 * @var string
	 */
	private string $update_url = 'http://localhost/societypress-updates/api/v1/themes/societypress';

	/**
	 * License manager.
	 *
	 * @var SocietyPress_License
	 */
	private SocietyPress_License $license;

	/**
	 * Cache key for update data.
	 *
	 * @var string
	 */
	private string $cache_key = 'societypress_theme_update_data';

	/**
	 * Constructor.
	 *
	 * WHY: Initialize updater with theme info and license manager.
	 *
	 * @param string               $theme_slug Theme slug (folder name).
	 * @param string               $version    Current theme version.
	 * @param SocietyPress_License $license    License manager instance.
	 */
	public function __construct( string $theme_slug, string $version, SocietyPress_License $license ) {
		$this->theme_slug = $theme_slug;
		$this->version    = $version;
		$this->license    = $license;

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

		// Authenticate download
		add_filter( 'upgrader_pre_download', array( $this, 'authenticate_download' ), 10, 3 );

		// After update cleanup
		add_action( 'upgrader_process_complete', array( $this, 'after_update' ), 10, 2 );
	}

	/**
	 * Check for theme updates.
	 *
	 * WHY: Injects our update info into WordPress's theme update checker.
	 * Called when WordPress checks for updates (twice daily by default).
	 *
	 * @param object $transient Update transient.
	 * @return object Modified transient.
	 */
	public function check_for_update( $transient ) {
		// Bail if this is not the right transient
		if ( empty( $transient ) || ! isset( $transient->checked ) ) {
			return $transient;
		}

		// Get remote version info
		$remote = $this->get_remote_version();

		if ( ! $remote || is_wp_error( $remote ) ) {
			return $transient;
		}

		// Compare versions
		if ( version_compare( $this->version, $remote['new_version'], '<' ) ) {
			// Update available - inject it
			$transient->response[ $this->theme_slug ] = $remote;
		} else {
			// No update - mark as checked
			$transient->no_update[ $this->theme_slug ] = $remote;
		}

		return $transient;
	}

	/**
	 * Get remote version information.
	 *
	 * WHY: Fetches latest version info from update server with caching.
	 * Cached for 12 hours to reduce server load.
	 *
	 * @return array|WP_Error|false Version info array or error.
	 */
	private function get_remote_version() {
		// Check cache first
		$cached = get_transient( $this->cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Get license info
		$license_key = '';
		$settings    = get_option( 'societypress_settings', array() );

		if ( ! empty( $settings['license_key'] ) ) {
			$license_key = SocietyPress_Encryption::decrypt( $settings['license_key'] );
		}

		// Request update info from server
		// WHY: Send as JSON since the update server expects JSON format
		$response = wp_remote_post(
			$this->update_url . '/update-check',
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => json_encode( array(
					'theme_slug'      => $this->theme_slug,
					'current_version' => $this->version,
					'license_key'     => $license_key,
					'site_url'        => get_site_url(),
					'wp_version'      => get_bloginfo( 'version' ),
					'php_version'     => PHP_VERSION,
				) ),
			)
		);

		// Handle errors
		if ( is_wp_error( $response ) ) {
			error_log( 'SocietyPress Theme Update: Request failed - ' . $response->get_error_message() );
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			error_log( sprintf( 'SocietyPress Theme Update: Server returned %d - %s', $code, $body ) );
			return false;
		}

		$data = json_decode( $body, true );

		if ( empty( $data ) || ! isset( $data['version'] ) ) {
			error_log( 'SocietyPress Theme Update: Invalid response format' );
			return false;
		}

		// Generate download token
		$token = $this->generate_download_token( $license_key, $data['version'] );

		if ( ! $token ) {
			error_log( 'SocietyPress Theme Update: Failed to get download token' );
			return false;
		}

		// Build download URL with authentication
		$download_url = sprintf(
			'https://stricklindevelopment.com/api/v1/themes/societypress/download?license=%s&site=%s&token=%s',
			urlencode( $license_key ),
			urlencode( get_site_url() ),
			urlencode( $token )
		);

		// Build update array in WordPress format
		$update = array(
			'theme'       => $this->theme_slug,
			'new_version' => $data['version'],
			'url'         => $data['homepage'] ?? '',
			'package'     => $download_url,
			'requires'    => $data['requires'] ?? '',
			'requires_php' => $data['requires_php'] ?? '',
		);

		// Cache for 12 hours
		set_transient( $this->cache_key, $update, 12 * HOUR_IN_SECONDS );

		return $update;
	}

	/**
	 * Generate download token by calling server.
	 *
	 * WHY: Server generates short-lived tokens for secure downloads.
	 *
	 * @param string $license_key License key.
	 * @param string $version     Theme version.
	 * @return string|false Download token or false on failure.
	 */
	private function generate_download_token( string $license_key, string $version ) {
		// WHY: Send as JSON since the update server expects JSON format
		$response = wp_remote_post(
			$this->update_url . '/generate-token',
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => json_encode( array(
					'license_key' => $license_key,
					'site_url'    => get_site_url(),
					'version'     => $version,
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

		return $data['token'] ?? false;
	}

	/**
	 * Provide theme information for the update popup.
	 *
	 * WHY: WordPress needs detailed theme info for the "View details" link.
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The type of information being requested.
	 * @param object             $args   Theme API arguments.
	 * @return false|object Modified result.
	 */
	public function theme_info( $result, $action, $args ) {
		// Only for theme_information action
		if ( 'theme_information' !== $action ) {
			return $result;
		}

		// Only for our theme
		if ( $this->theme_slug !== $args->slug ) {
			return $result;
		}

		// Get remote info
		$remote = $this->get_remote_info();

		if ( ! $remote || is_wp_error( $remote ) ) {
			return $result;
		}

		return $remote;
	}

	/**
	 * Get detailed theme information from server.
	 *
	 * WHY: Fetches full theme details including changelog for update popup.
	 *
	 * @return object|WP_Error|false Theme info object or error.
	 */
	private function get_remote_info() {
		// Get license info
		$license_key = '';
		$settings    = get_option( 'societypress_settings', array() );

		if ( ! empty( $settings['license_key'] ) ) {
			$license_key = SocietyPress_Encryption::decrypt( $settings['license_key'] );
		}

		// Request full theme info
		// WHY: Send as JSON since the update server expects JSON format
		$response = wp_remote_post(
			$this->update_url . '/info',
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => json_encode( array(
					'theme_slug'  => $this->theme_slug,
					'license_key' => $license_key,
					'site_url'    => get_site_url(),
				) ),
			)
		);

		// Handle errors
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			return new WP_Error( 'server_error', 'Update server returned error code: ' . $code );
		}

		$data = json_decode( $body );

		if ( empty( $data ) ) {
			return new WP_Error( 'invalid_response', 'Invalid server response' );
		}

		return $data;
	}

	/**
	 * Authenticate download before WordPress downloads the update.
	 *
	 * WHY: Ensures download URL includes valid license authentication.
	 *
	 * @param bool        $reply   Whether to bail without returning the package.
	 * @param string      $package The package URL.
	 * @param WP_Upgrader $upgrader The WP_Upgrader instance.
	 * @return bool|WP_Error False to proceed, WP_Error to abort.
	 */
	public function authenticate_download( $reply, $package, $upgrader ) {
		// Only authenticate our theme's downloads
		if ( strpos( $package, $this->update_url ) === false ) {
			return $reply;
		}

		// Verify license is valid
		if ( ! $this->license->is_valid() ) {
			return new WP_Error(
				'invalid_license',
				__( 'Cannot download update: Invalid or expired license. Please activate a valid license.', 'societypress' )
			);
		}

		// License is valid, WordPress will proceed with download
		return $reply;
	}

	/**
	 * After update cleanup.
	 *
	 * WHY: Clear caches and perform post-update tasks.
	 *
	 * @param WP_Upgrader $upgrader WP_Upgrader instance.
	 * @param array       $options  Update options.
	 */
	public function after_update( $upgrader, $options ): void {
		// Only run for our theme
		if ( 'update' !== $options['action'] || 'theme' !== $options['type'] ) {
			return;
		}

		if ( empty( $options['themes'] ) || ! in_array( $this->theme_slug, $options['themes'], true ) ) {
			return;
		}

		// Clear update cache
		delete_transient( $this->cache_key );

		// Log update
		$remote = $this->get_remote_version();
		error_log( sprintf(
			'SocietyPress Theme: Updated from version %s to %s',
			$this->version,
			$remote['new_version'] ?? 'unknown'
		) );
	}

	/**
	 * Force check for updates (bypass cache).
	 *
	 * WHY: Allows manual update checks from admin interface.
	 */
	public function force_check(): void {
		delete_transient( $this->cache_key );
		wp_clean_themes_cache();
	}
}
