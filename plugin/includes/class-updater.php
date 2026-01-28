<?php
/**
 * Auto-Updater
 *
 * Hooks into WordPress update system to check for plugin updates from
 * StricklinDevelopment.com instead of WordPress.org.
 *
 * WHY: Commercial plugins need custom update delivery tied to license validation.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Updater
 *
 * Handles plugin updates from remote server.
 */
class SocietyPress_Updater {

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	private string $plugin_basename;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private string $plugin_slug;

	/**
	 * Current version.
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
	private string $update_url = 'http://localhost/societypress-updates/api/v1/plugins/societypress';

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
	private string $cache_key = 'societypress_update_data';

	/**
	 * Constructor.
	 *
	 * WHY: Initialize updater with plugin info and license manager.
	 *
	 * @param string                 $plugin_basename Plugin basename (e.g., 'societypress/societypress.php').
	 * @param string                 $version         Current plugin version.
	 * @param SocietyPress_License   $license         License manager instance.
	 */
	public function __construct( string $plugin_basename, string $version, SocietyPress_License $license ) {
		$this->plugin_basename = $plugin_basename;
		$this->plugin_slug     = dirname( $plugin_basename );
		$this->version         = $version;
		$this->license         = $license;

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * WHY: Hook into WordPress update system to inject our update info.
	 */
	private function init_hooks(): void {
		// Check for updates
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );

		// Plugin information popup
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );

		// Authenticate download
		add_filter( 'upgrader_pre_download', array( $this, 'authenticate_download' ), 10, 3 );

		// After update cleanup
		add_action( 'upgrader_process_complete', array( $this, 'after_update' ), 10, 2 );
	}

	/**
	 * Check for plugin updates.
	 *
	 * WHY: Injects our update info into WordPress's update checker.
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
		// WHY: Remote object uses 'new_version' key per WordPress standard
		if ( version_compare( $this->version, $remote->new_version, '<' ) ) {
			// Update available - inject it
			$transient->response[ $this->plugin_basename ] = $remote;
		} else {
			// No update - mark as checked
			$transient->no_update[ $this->plugin_basename ] = $remote;
		}

		return $transient;
	}

	/**
	 * Get remote version information.
	 *
	 * WHY: Fetches latest version info from update server with caching.
	 * Cached for 12 hours to reduce server load.
	 *
	 * @return object|WP_Error|false Version info object or error.
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
					'plugin_slug'     => $this->plugin_slug,
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
			error_log( 'SocietyPress Update: Request failed - ' . $response->get_error_message() );
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			error_log( sprintf( 'SocietyPress Update: Server returned %d - %s', $code, $body ) );
			return false;
		}

		// WHY: Decode as associative array for easier access
		$data = json_decode( $body, true );

		if ( empty( $data ) || ! isset( $data['version'] ) ) {
			error_log( 'SocietyPress Update: Invalid response format' );
			return false;
		}

		// Build update object in WordPress format
		$update = (object) array(
			'slug'          => $this->plugin_slug,
			'plugin'        => $this->plugin_basename,
			'new_version'   => $data['version'],
			'url'           => $data['homepage'] ?? '',
			'package'       => $data['download_url'] ?? '',
			'icons'         => $data['icons'] ?? array(),
			'banners'       => $data['banners'] ?? array(),
			'tested'        => $data['tested'] ?? '',
			'requires_php'  => $data['requires_php'] ?? '',
			'requires'      => $data['requires'] ?? '',
		);

		// Cache for 12 hours
		set_transient( $this->cache_key, $update, 12 * HOUR_IN_SECONDS );

		return $update;
	}

	/**
	 * Provide plugin information for the update popup.
	 *
	 * WHY: WordPress needs detailed plugin info for the "View details" link.
	 * This fetches the full plugin info including changelog.
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The type of information being requested.
	 * @param object             $args   Plugin API arguments.
	 * @return false|object Modified result.
	 */
	public function plugin_info( $result, $action, $args ) {
		// Only for plugin_information action
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		// Only for our plugin
		if ( $this->plugin_slug !== $args->slug ) {
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
	 * Get detailed plugin information from server.
	 *
	 * WHY: Fetches full plugin details including changelog for update popup.
	 *
	 * @return object|WP_Error|false Plugin info object or error.
	 */
	private function get_remote_info() {
		// Get license info
		$license_key = '';
		$settings    = get_option( 'societypress_settings', array() );

		if ( ! empty( $settings['license_key'] ) ) {
			$license_key = SocietyPress_Encryption::decrypt( $settings['license_key'] );
		}

		// Request full plugin info
		// WHY: Send as JSON since the update server expects JSON format
		$response = wp_remote_post(
			$this->update_url . '/info',
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => json_encode( array(
					'plugin_slug' => $this->plugin_slug,
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
	 * WordPress passes this to wp_remote_get() to download the ZIP.
	 *
	 * @param bool        $reply   Whether to bail without returning the package.
	 * @param string      $package The package URL.
	 * @param WP_Upgrader $upgrader The WP_Upgrader instance.
	 * @return bool|WP_Error False to proceed, WP_Error to abort.
	 */
	public function authenticate_download( $reply, $package, $upgrader ) {
		// Only authenticate our plugin's downloads
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
		// Only run for our plugin
		if ( 'update' !== $options['action'] || 'plugin' !== $options['type'] ) {
			return;
		}

		if ( empty( $options['plugins'] ) || ! in_array( $this->plugin_basename, $options['plugins'], true ) ) {
			return;
		}

		// Clear update cache
		delete_transient( $this->cache_key );

		// Log update
		error_log( sprintf(
			'SocietyPress: Updated from version %s to %s',
			$this->version,
			$this->get_remote_version()->new_version ?? 'unknown'
		) );
	}

	/**
	 * Force check for updates (bypass cache).
	 *
	 * WHY: Allows manual update checks from admin interface.
	 */
	public function force_check(): void {
		delete_transient( $this->cache_key );
		wp_clean_plugins_cache();
	}
}
