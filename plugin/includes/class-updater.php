<?php
/**
 * Auto-Updater
 *
 * Hooks into WordPress update system to check for plugin updates from
 * StricklinDevelopment.com instead of WordPress.org.
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
	 * @var string
	 */
	private string $update_url = 'https://stricklindevelopment.com/api/v1/plugins/societypress';

	/**
	 * Cache key for update data.
	 *
	 * @var string
	 */
	private string $cache_key = 'societypress_update_data';

	/**
	 * Constructor.
	 *
	 * @param string $plugin_basename Plugin basename (e.g., 'societypress/societypress.php').
	 * @param string $version         Current plugin version.
	 */
	public function __construct( string $plugin_basename, string $version ) {
		$this->plugin_basename = $plugin_basename;
		$this->plugin_slug     = dirname( $plugin_basename );
		$this->version         = $version;

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

		// After update cleanup
		add_action( 'upgrader_process_complete', array( $this, 'after_update' ), 10, 2 );
	}

	/**
	 * Check for plugin updates.
	 *
	 * WHY: Injects our update info into WordPress's update checker.
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

		if ( version_compare( $this->version, $remote->new_version, '<' ) ) {
			$transient->response[ $this->plugin_basename ] = $remote;
		} else {
			$transient->no_update[ $this->plugin_basename ] = $remote;
		}

		return $transient;
	}

	/**
	 * Get remote version information.
	 *
	 * WHY: Fetches latest version info from update server with caching.
	 *
	 * @return object|false Version info object or false on error.
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
					'plugin_slug'     => $this->plugin_slug,
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

		$update = (object) array(
			'slug'         => $this->plugin_slug,
			'plugin'       => $this->plugin_basename,
			'new_version'  => $data['version'],
			'url'          => $data['homepage'] ?? '',
			'package'      => $data['download_url'] ?? '',
			'icons'        => $data['icons'] ?? array(),
			'banners'      => $data['banners'] ?? array(),
			'tested'       => $data['tested'] ?? '',
			'requires_php' => $data['requires_php'] ?? '',
			'requires'     => $data['requires'] ?? '',
		);

		set_transient( $this->cache_key, $update, 12 * HOUR_IN_SECONDS );

		return $update;
	}

	/**
	 * Provide plugin information for the update popup.
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The type of information being requested.
	 * @param object             $args   Plugin API arguments.
	 * @return false|object Modified result.
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( $this->plugin_slug !== $args->slug ) {
			return $result;
		}

		$remote = $this->get_remote_info();

		if ( ! $remote || is_wp_error( $remote ) ) {
			return $result;
		}

		return $remote;
	}

	/**
	 * Get detailed plugin information from server.
	 *
	 * @return object|false Plugin info object or false on error.
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
					'plugin_slug' => $this->plugin_slug,
					'site_url'    => get_site_url(),
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
		if ( 'update' !== $options['action'] || 'plugin' !== $options['type'] ) {
			return;
		}

		if ( empty( $options['plugins'] ) || ! in_array( $this->plugin_basename, $options['plugins'], true ) ) {
			return;
		}

		delete_transient( $this->cache_key );
	}

	/**
	 * Force check for updates (bypass cache).
	 */
	public function force_check(): void {
		delete_transient( $this->cache_key );
		wp_clean_plugins_cache();
	}
}
