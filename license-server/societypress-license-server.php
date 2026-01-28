<?php
/**
 * SocietyPress License Server
 *
 * Handles license activation, validation, and management for SocietyPress plugin.
 * Deploy this plugin to stricklindevelopment.com to provide license services.
 *
 * WHY: Commercial products need license validation. This server handles the validation
 * requests from SocietyPress client installations, tracking which sites have activated
 * each license and enforcing activation limits by license type.
 *
 * @package    SocietyPress_License_Server
 * @author     Charles Stricklin
 * @copyright  2026 Stricklin Development
 * @license    GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: SocietyPress License Server
 * Plugin URI:  https://stricklindevelopment.com/studiopress/societypress
 * Description: License activation and validation server for SocietyPress plugin.
 * Version:     1.0.0
 * Author:      Charles Stricklin
 * Author URI:  https://stricklindevelopment.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: sp-license-server
 */

// Prevent direct access - security measure to block direct file execution
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_License_Server
 *
 * Main plugin class implementing license management functionality.
 *
 * WHY single class: All license server logic is contained in one file for easy deployment.
 * The server only runs on stricklindevelopment.com, so keeping it simple reduces deployment complexity.
 */
class SocietyPress_License_Server {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Database table name (without prefix).
	 *
	 * @var string
	 */
	const TABLE_NAME = 'sp_licenses';

	/**
	 * Shared secret for HMAC signature verification.
	 *
	 * WHY hardcoded: This must match the client-side salt exactly. Both sides use this
	 * to verify request authenticity. In a more complex system, this could be configurable.
	 *
	 * @var string
	 */
	const LICENSE_SALT = 'societypress_license_salt';

	/**
	 * License type activation limits.
	 *
	 * WHY: Different license tiers allow different numbers of site activations.
	 * - site: Single site license (1 activation)
	 * - developer: For developers building client sites (5 activations)
	 * - agency: Unlimited activations for agencies managing many sites
	 *
	 * @var array
	 */
	const LICENSE_LIMITS = array(
		'site'      => 1,
		'developer' => 5,
		'agency'    => PHP_INT_MAX, // Unlimited
	);

	/**
	 * Database instance.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Singleton instance.
	 *
	 * @var SocietyPress_License_Server
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * WHY singleton: Ensures only one instance manages the license server,
	 * preventing duplicate hook registrations and database operations.
	 *
	 * @return SocietyPress_License_Server
	 */
	public static function get_instance(): SocietyPress_License_Server {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * WHY private: Forces use of get_instance() to ensure singleton pattern.
	 */
	private function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * WHY: WordPress hook system is how we integrate with the platform.
	 * We register our REST routes, admin menus, and activation hooks here.
	 */
	private function init_hooks(): void {
		// REST API endpoints for license operations
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Admin interface for license management
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Admin assets
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Handle admin form submissions
		add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
	}

	/**
	 * Get full table name with prefix.
	 *
	 * @return string
	 */
	public function get_table_name(): string {
		return $this->wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Plugin activation hook.
	 *
	 * WHY static: WordPress activation hooks require static methods or functions.
	 * Creates the database table to store license data.
	 */
	public static function activate(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Use WordPress's dbDelta for safe table creation/updates
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// License table schema
		// WHY this structure:
		// - license_key: Unique identifier, 19 chars (XXXX-XXXX-XXXX-XXXX format)
		// - license_email: Customer email for verification and support lookup
		// - license_type: Determines activation limits (site=1, developer=5, agency=unlimited)
		// - status: Current license state (active, expired, suspended, revoked)
		// - expires_at: NULL for lifetime licenses, date for annual subscriptions
		// - activated_sites: JSON array of site URLs currently using this license
		// - created_at: When license was created for auditing
		// - notes: Admin notes for customer service
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			license_key varchar(19) NOT NULL,
			license_email varchar(255) NOT NULL,
			license_type varchar(20) NOT NULL DEFAULT 'site',
			status varchar(20) NOT NULL DEFAULT 'active',
			expires_at date DEFAULT NULL,
			activated_sites longtext DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			notes text DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY license_key (license_key),
			KEY license_email (license_email),
			KEY status (status),
			KEY expires_at (expires_at)
		) {$charset_collate};";

		dbDelta( $sql );

		// Store schema version for future upgrades
		update_option( 'sp_license_server_version', self::VERSION );
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * WHY: Clean up scheduled events. We don't delete the table on deactivation
	 * because that would destroy license data - only delete on uninstall.
	 */
	public static function deactivate(): void {
		// Nothing to clean up currently
		// Database table is preserved for re-activation
	}

	/**
	 * Register REST API routes.
	 *
	 * WHY REST API: The SocietyPress client uses wp_remote_post to these endpoints.
	 * Using WordPress REST API provides standardized request handling, authentication
	 * hooks, and response formatting.
	 *
	 * Route structure matches client expectation: /studiopress/api/v1/licenses/{action}
	 */
	public function register_rest_routes(): void {
		$namespace = 'studiopress/api/v1';

		// POST /licenses/activate - Activate a license on a site
		register_rest_route(
			$namespace,
			'/licenses/activate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_activate' ),
				'permission_callback' => '__return_true', // Public endpoint, signature verified in handler
			)
		);

		// POST /licenses/check - Verify license is still valid (daily cron from clients)
		register_rest_route(
			$namespace,
			'/licenses/check',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_check' ),
				'permission_callback' => '__return_true',
			)
		);

		// POST /licenses/deactivate - Release license from a site
		register_rest_route(
			$namespace,
			'/licenses/deactivate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_deactivate' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Verify request signature.
	 *
	 * WHY: Prevents unauthorized license operations. The client signs requests
	 * with HMAC-SHA256 using the shared secret. We verify that signature here
	 * to ensure the request came from a legitimate SocietyPress installation.
	 *
	 * @param string $site_url    The requesting site's URL.
	 * @param string $license_key The license key being validated.
	 * @param string $signature   The HMAC signature to verify.
	 * @return bool Whether signature is valid.
	 */
	private function verify_signature( string $site_url, string $license_key, string $signature ): bool {
		// Calculate expected signature: HMAC-SHA256 of (site_url + license_key)
		$expected = hash_hmac( 'sha256', $site_url . $license_key, self::LICENSE_SALT );
		return hash_equals( $expected, $signature );
	}

	/**
	 * Get license by key.
	 *
	 * @param string $key License key.
	 * @return object|null License record or null.
	 */
	private function get_license( string $key ): ?object {
		$table = $this->get_table_name();

		$license = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$table} WHERE license_key = %s",
				$key
			)
		);

		return $license ?: null;
	}

	/**
	 * Get activated sites for a license.
	 *
	 * WHY JSON: Storing sites as JSON array allows unlimited sites per license
	 * without needing a separate activations table. For this use case, the
	 * simplicity outweighs the benefits of a normalized structure.
	 *
	 * @param object $license License record.
	 * @return array Array of site URLs.
	 */
	private function get_activated_sites( object $license ): array {
		if ( empty( $license->activated_sites ) ) {
			return array();
		}

		$sites = json_decode( $license->activated_sites, true );
		return is_array( $sites ) ? $sites : array();
	}

	/**
	 * Check if license is valid (not expired/suspended/revoked).
	 *
	 * WHY separate method: License validity check is used in multiple endpoints.
	 * Centralizing the logic ensures consistent validation rules.
	 *
	 * @param object $license License record.
	 * @return array Array with 'valid' bool and 'status' string.
	 */
	private function check_license_validity( object $license ): array {
		// Check status first
		if ( 'active' !== $license->status ) {
			return array(
				'valid'  => false,
				'status' => $license->status,
			);
		}

		// Check expiration (NULL = lifetime, so only check if set)
		if ( ! empty( $license->expires_at ) ) {
			$expires = strtotime( $license->expires_at );
			if ( $expires < time() ) {
				// Update status to expired in database
				$this->wpdb->update(
					$this->get_table_name(),
					array( 'status' => 'expired' ),
					array( 'id' => $license->id ),
					array( '%s' ),
					array( '%d' )
				);

				return array(
					'valid'  => false,
					'status' => 'expired',
				);
			}
		}

		return array(
			'valid'  => true,
			'status' => 'active',
		);
	}

	/**
	 * Normalize site URL for consistent comparison.
	 *
	 * WHY: Sites may send URLs with or without trailing slashes, with http or https,
	 * with or without www. Normalizing ensures we don't treat the same site as different.
	 *
	 * @param string $url Site URL.
	 * @return string Normalized URL.
	 */
	private function normalize_site_url( string $url ): string {
		// Parse the URL
		$url = strtolower( trim( $url ) );

		// Remove trailing slash
		$url = rtrim( $url, '/' );

		// Remove protocol for comparison (http://example.com and https://example.com are same site)
		$url = preg_replace( '#^https?://#', '', $url );

		// Remove www. prefix
		$url = preg_replace( '/^www\./', '', $url );

		return $url;
	}

	/**
	 * Build standard API response.
	 *
	 * WHY: Consistent response format makes client implementation simpler
	 * and debugging easier. All responses follow the same structure.
	 *
	 * @param bool        $success Whether operation succeeded.
	 * @param object|null $license License record (for license data in response).
	 * @param string      $message Optional message.
	 * @return WP_REST_Response
	 */
	private function build_response( bool $success, ?object $license = null, string $message = '' ): WP_REST_Response {
		$data = array(
			'success' => $success,
		);

		if ( $license ) {
			$data['license'] = array(
				'type'    => $license->license_type,
				'status'  => $license->status,
				'expires' => $license->expires_at, // NULL for lifetime, date string for annual
			);
		}

		if ( ! empty( $message ) ) {
			$data['message'] = $message;
		}

		$status_code = $success ? 200 : 400;
		return new WP_REST_Response( $data, $status_code );
	}

	/**
	 * Handle /activate endpoint.
	 *
	 * WHY: This is called when a customer enters their license key in SocietyPress settings.
	 * We verify the key exists, is valid, and hasn't exceeded its activation limit.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function handle_activate( WP_REST_Request $request ): WP_REST_Response {
		// Extract and sanitize parameters
		$license_key   = sanitize_text_field( $request->get_param( 'license_key' ) ?? '' );
		$license_email = sanitize_email( $request->get_param( 'license_email' ) ?? '' );
		$site_url      = esc_url_raw( $request->get_param( 'site_url' ) ?? '' );
		$signature     = sanitize_text_field( $request->get_param( 'signature' ) ?? '' );

		// Validate required fields
		if ( empty( $license_key ) || empty( $license_email ) || empty( $site_url ) || empty( $signature ) ) {
			return $this->build_response( false, null, 'Missing required fields.' );
		}

		// Verify signature
		if ( ! $this->verify_signature( $site_url, $license_key, $signature ) ) {
			// Log failed signature for security monitoring
			error_log( sprintf(
				'SocietyPress License Server: Invalid signature from %s for key %s',
				$site_url,
				substr( $license_key, 0, 9 ) . '...'
			) );
			return $this->build_response( false, null, 'Invalid request signature.' );
		}

		// Look up license
		$license = $this->get_license( $license_key );

		if ( ! $license ) {
			return $this->build_response( false, null, 'License key not found.' );
		}

		// Verify email matches
		if ( strtolower( $license->license_email ) !== strtolower( $license_email ) ) {
			return $this->build_response( false, null, 'License email does not match.' );
		}

		// Check license validity (not expired/suspended/revoked)
		$validity = $this->check_license_validity( $license );
		if ( ! $validity['valid'] ) {
			// Refresh license object to get updated status
			$license = $this->get_license( $license_key );
			return $this->build_response( false, $license, 'License is ' . $validity['status'] . '.' );
		}

		// Get current activations
		$sites          = $this->get_activated_sites( $license );
		$normalized_url = $this->normalize_site_url( $site_url );

		// Check if already activated on this site
		$already_activated = false;
		foreach ( $sites as $index => $existing_site ) {
			if ( $this->normalize_site_url( $existing_site ) === $normalized_url ) {
				$already_activated = true;
				// Update the URL in case format changed (e.g., http to https)
				$sites[ $index ] = $site_url;
				break;
			}
		}

		if ( ! $already_activated ) {
			// Check activation limit
			$limit = self::LICENSE_LIMITS[ $license->license_type ] ?? 1;
			if ( count( $sites ) >= $limit ) {
				return $this->build_response(
					false,
					$license,
					sprintf( 'Activation limit reached (%d sites).', $limit )
				);
			}

			// Add new site
			$sites[] = $site_url;
		}

		// Update activated sites
		$this->wpdb->update(
			$this->get_table_name(),
			array( 'activated_sites' => wp_json_encode( array_values( $sites ) ) ),
			array( 'id' => $license->id ),
			array( '%s' ),
			array( '%d' )
		);

		// Log activation
		error_log( sprintf(
			'SocietyPress License Server: Activated %s for site %s (total activations: %d)',
			substr( $license_key, 0, 9 ) . '...',
			$site_url,
			count( $sites )
		) );

		return $this->build_response(
			true,
			$license,
			$already_activated ? 'License already activated on this site.' : 'License activated successfully.'
		);
	}

	/**
	 * Handle /check endpoint.
	 *
	 * WHY: SocietyPress runs a daily cron to verify licenses are still valid.
	 * This catches expired licenses, revocations, and other status changes.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function handle_check( WP_REST_Request $request ): WP_REST_Response {
		$license_key = sanitize_text_field( $request->get_param( 'license_key' ) ?? '' );
		$site_url    = esc_url_raw( $request->get_param( 'site_url' ) ?? '' );
		$signature   = sanitize_text_field( $request->get_param( 'signature' ) ?? '' );

		// Validate required fields
		if ( empty( $license_key ) || empty( $site_url ) || empty( $signature ) ) {
			return $this->build_response( false, null, 'Missing required fields.' );
		}

		// Verify signature
		if ( ! $this->verify_signature( $site_url, $license_key, $signature ) ) {
			return $this->build_response( false, null, 'Invalid request signature.' );
		}

		// Look up license
		$license = $this->get_license( $license_key );

		if ( ! $license ) {
			return $this->build_response( false, null, 'License key not found.' );
		}

		// Check validity
		$validity = $this->check_license_validity( $license );
		if ( ! $validity['valid'] ) {
			$license = $this->get_license( $license_key );
			return $this->build_response( false, $license, 'License is ' . $validity['status'] . '.' );
		}

		// Verify site is actually activated
		$sites          = $this->get_activated_sites( $license );
		$normalized_url = $this->normalize_site_url( $site_url );
		$site_found     = false;

		foreach ( $sites as $existing_site ) {
			if ( $this->normalize_site_url( $existing_site ) === $normalized_url ) {
				$site_found = true;
				break;
			}
		}

		if ( ! $site_found ) {
			return $this->build_response( false, $license, 'Site not activated for this license.' );
		}

		return $this->build_response( true, $license, 'License is valid.' );
	}

	/**
	 * Handle /deactivate endpoint.
	 *
	 * WHY: When a customer deactivates their license (e.g., moving to a new server),
	 * we free up that activation slot so they can use it elsewhere.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function handle_deactivate( WP_REST_Request $request ): WP_REST_Response {
		$license_key = sanitize_text_field( $request->get_param( 'license_key' ) ?? '' );
		$site_url    = esc_url_raw( $request->get_param( 'site_url' ) ?? '' );
		$signature   = sanitize_text_field( $request->get_param( 'signature' ) ?? '' );

		// Validate required fields
		if ( empty( $license_key ) || empty( $site_url ) || empty( $signature ) ) {
			return $this->build_response( false, null, 'Missing required fields.' );
		}

		// Verify signature
		if ( ! $this->verify_signature( $site_url, $license_key, $signature ) ) {
			return $this->build_response( false, null, 'Invalid request signature.' );
		}

		// Look up license
		$license = $this->get_license( $license_key );

		if ( ! $license ) {
			// Even if license not found, we return success
			// WHY: The client clears local data regardless, and we don't want
			// to block deactivation due to database issues
			return $this->build_response( true, null, 'License deactivated.' );
		}

		// Remove site from activations
		$sites          = $this->get_activated_sites( $license );
		$normalized_url = $this->normalize_site_url( $site_url );

		$sites = array_filter( $sites, function ( $existing_site ) use ( $normalized_url ) {
			return $this->normalize_site_url( $existing_site ) !== $normalized_url;
		} );

		// Update database
		$this->wpdb->update(
			$this->get_table_name(),
			array( 'activated_sites' => wp_json_encode( array_values( $sites ) ) ),
			array( 'id' => $license->id ),
			array( '%s' ),
			array( '%d' )
		);

		// Log deactivation
		error_log( sprintf(
			'SocietyPress License Server: Deactivated %s from site %s (remaining activations: %d)',
			substr( $license_key, 0, 9 ) . '...',
			$site_url,
			count( $sites )
		) );

		return $this->build_response( true, $license, 'License deactivated successfully.' );
	}

	/**
	 * Add admin menu item.
	 *
	 * WHY under Tools: License management is an administrative tool, not a
	 * core content feature. Tools menu keeps it accessible without cluttering
	 * the main admin menu.
	 */
	public function add_admin_menu(): void {
		add_management_page(
			__( 'SocietyPress Licenses', 'sp-license-server' ),
			__( 'SocietyPress Licenses', 'sp-license-server' ),
			'manage_options',
			'sp-licenses',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ): void {
		// Only load on our page
		if ( 'tools_page_sp-licenses' !== $hook ) {
			return;
		}

		// Inline styles - simple enough to not need a separate CSS file
		wp_add_inline_style( 'common', '
			.sp-licenses-wrap { max-width: 1200px; }
			.sp-license-card { background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 20px 0; }
			.sp-license-card h2 { margin-top: 0; }
			.sp-license-key { font-family: monospace; font-size: 14px; background: #f0f0f1; padding: 8px 12px; border-radius: 4px; }
			.sp-status-active { color: #00a32a; font-weight: 600; }
			.sp-status-expired { color: #d63638; font-weight: 600; }
			.sp-status-suspended { color: #dba617; font-weight: 600; }
			.sp-status-revoked { color: #d63638; font-weight: 600; }
			.sp-sites-list { margin: 10px 0; padding: 0; }
			.sp-sites-list li { margin: 5px 0; padding: 5px 10px; background: #f6f7f7; }
			.sp-form-table th { width: 150px; }
		' );
	}

	/**
	 * Handle admin form actions.
	 *
	 * WHY: Process form submissions for creating, editing, and managing licenses.
	 * All actions are nonce-verified for security.
	 */
	public function handle_admin_actions(): void {
		// Check we're on our page
		if ( empty( $_GET['page'] ) || 'sp-licenses' !== $_GET['page'] ) {
			return;
		}

		// Create new license
		if ( isset( $_POST['sp_create_license'] ) ) {
			check_admin_referer( 'sp_create_license' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to manage licenses.', 'sp-license-server' ) );
			}

			$email        = sanitize_email( $_POST['license_email'] ?? '' );
			$type         = sanitize_text_field( $_POST['license_type'] ?? 'site' );
			$expires_type = sanitize_text_field( $_POST['expires_type'] ?? 'lifetime' );
			$expires_date = sanitize_text_field( $_POST['expires_date'] ?? '' );
			$notes        = sanitize_textarea_field( $_POST['notes'] ?? '' );

			if ( empty( $email ) || ! is_email( $email ) ) {
				add_settings_error( 'sp_licenses', 'invalid_email', __( 'Please enter a valid email address.', 'sp-license-server' ), 'error' );
				return;
			}

			// Validate license type
			if ( ! array_key_exists( $type, self::LICENSE_LIMITS ) ) {
				$type = 'site';
			}

			// Calculate expiration
			$expires_at = null;
			if ( 'annual' === $expires_type ) {
				if ( ! empty( $expires_date ) ) {
					$expires_at = $expires_date;
				} else {
					// Default to 1 year from now
					$expires_at = date( 'Y-m-d', strtotime( '+1 year' ) );
				}
			}

			// Generate unique license key
			$license_key = $this->generate_license_key();

			// Insert license
			$result = $this->wpdb->insert(
				$this->get_table_name(),
				array(
					'license_key'   => $license_key,
					'license_email' => $email,
					'license_type'  => $type,
					'status'        => 'active',
					'expires_at'    => $expires_at,
					'notes'         => $notes,
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s' )
			);

			if ( $result ) {
				add_settings_error(
					'sp_licenses',
					'license_created',
					sprintf(
						/* translators: %s: license key */
						__( 'License created: %s', 'sp-license-server' ),
						$license_key
					),
					'success'
				);
			} else {
				add_settings_error( 'sp_licenses', 'create_failed', __( 'Failed to create license.', 'sp-license-server' ), 'error' );
			}
		}

		// Update license status
		if ( isset( $_POST['sp_update_status'] ) ) {
			check_admin_referer( 'sp_update_status' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to manage licenses.', 'sp-license-server' ) );
			}

			$license_id = absint( $_POST['license_id'] ?? 0 );
			$new_status = sanitize_text_field( $_POST['new_status'] ?? '' );

			$valid_statuses = array( 'active', 'expired', 'suspended', 'revoked' );

			if ( $license_id && in_array( $new_status, $valid_statuses, true ) ) {
				$this->wpdb->update(
					$this->get_table_name(),
					array( 'status' => $new_status ),
					array( 'id' => $license_id ),
					array( '%s' ),
					array( '%d' )
				);

				add_settings_error( 'sp_licenses', 'status_updated', __( 'License status updated.', 'sp-license-server' ), 'success' );
			}
		}

		// Remove site from license
		if ( isset( $_POST['sp_remove_site'] ) ) {
			check_admin_referer( 'sp_remove_site' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to manage licenses.', 'sp-license-server' ) );
			}

			$license_id  = absint( $_POST['license_id'] ?? 0 );
			$site_to_remove = esc_url_raw( $_POST['site_url'] ?? '' );

			if ( $license_id && $site_to_remove ) {
				$license = $this->wpdb->get_row(
					$this->wpdb->prepare(
						"SELECT * FROM {$this->get_table_name()} WHERE id = %d",
						$license_id
					)
				);

				if ( $license ) {
					$sites = $this->get_activated_sites( $license );
					$normalized_remove = $this->normalize_site_url( $site_to_remove );

					$sites = array_filter( $sites, function ( $site ) use ( $normalized_remove ) {
						return $this->normalize_site_url( $site ) !== $normalized_remove;
					} );

					$this->wpdb->update(
						$this->get_table_name(),
						array( 'activated_sites' => wp_json_encode( array_values( $sites ) ) ),
						array( 'id' => $license_id ),
						array( '%s' ),
						array( '%d' )
					);

					add_settings_error( 'sp_licenses', 'site_removed', __( 'Site removed from license.', 'sp-license-server' ), 'success' );
				}
			}
		}

		// Delete license
		if ( isset( $_POST['sp_delete_license'] ) ) {
			check_admin_referer( 'sp_delete_license' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to manage licenses.', 'sp-license-server' ) );
			}

			$license_id = absint( $_POST['license_id'] ?? 0 );

			if ( $license_id ) {
				$this->wpdb->delete(
					$this->get_table_name(),
					array( 'id' => $license_id ),
					array( '%d' )
				);

				add_settings_error( 'sp_licenses', 'license_deleted', __( 'License deleted.', 'sp-license-server' ), 'success' );
			}
		}
	}

	/**
	 * Generate unique license key.
	 *
	 * WHY this format: XXXX-XXXX-XXXX-XXXX is human-readable and easy to type.
	 * Uses alphanumeric characters (no O, 0, I, 1 to avoid confusion).
	 *
	 * @return string License key.
	 */
	private function generate_license_key(): string {
		// Characters to use (excluding confusing ones: 0, O, I, 1, l)
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

		do {
			$key = '';
			for ( $i = 0; $i < 4; $i++ ) {
				if ( $i > 0 ) {
					$key .= '-';
				}
				for ( $j = 0; $j < 4; $j++ ) {
					$key .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
				}
			}

			// Check if key already exists
			$exists = $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->get_table_name()} WHERE license_key = %s",
					$key
				)
			);
		} while ( $exists > 0 );

		return $key;
	}

	/**
	 * Render admin page.
	 *
	 * WHY: Provides interface for admins to create and manage licenses.
	 * This includes listing all licenses, creating new ones, and viewing activation details.
	 */
	public function render_admin_page(): void {
		// Get all licenses
		$licenses = $this->wpdb->get_results(
			"SELECT * FROM {$this->get_table_name()} ORDER BY created_at DESC"
		);

		// Check if editing a specific license
		$editing_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
		$editing    = null;

		if ( $editing_id ) {
			foreach ( $licenses as $license ) {
				if ( (int) $license->id === $editing_id ) {
					$editing = $license;
					break;
				}
			}
		}

		?>
		<div class="wrap sp-licenses-wrap">
			<h1><?php esc_html_e( 'SocietyPress License Manager', 'sp-license-server' ); ?></h1>

			<?php settings_errors( 'sp_licenses' ); ?>

			<!-- Create New License Form -->
			<div class="sp-license-card">
				<h2><?php esc_html_e( 'Create New License', 'sp-license-server' ); ?></h2>

				<form method="post" action="">
					<?php wp_nonce_field( 'sp_create_license' ); ?>

					<table class="form-table sp-form-table">
						<tr>
							<th scope="row">
								<label for="license-email"><?php esc_html_e( 'Customer Email', 'sp-license-server' ); ?></label>
							</th>
							<td>
								<input
									type="email"
									id="license-email"
									name="license_email"
									class="regular-text"
									required
								>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="license-type"><?php esc_html_e( 'License Type', 'sp-license-server' ); ?></label>
							</th>
							<td>
								<select id="license-type" name="license_type">
									<option value="site"><?php esc_html_e( 'Site (1 activation)', 'sp-license-server' ); ?></option>
									<option value="developer"><?php esc_html_e( 'Developer (5 activations)', 'sp-license-server' ); ?></option>
									<option value="agency"><?php esc_html_e( 'Agency (unlimited)', 'sp-license-server' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Expiration', 'sp-license-server' ); ?></label>
							</th>
							<td>
								<fieldset>
									<label>
										<input type="radio" name="expires_type" value="lifetime" checked>
										<?php esc_html_e( 'Lifetime (never expires)', 'sp-license-server' ); ?>
									</label>
									<br>
									<label>
										<input type="radio" name="expires_type" value="annual">
										<?php esc_html_e( 'Annual (expires on date)', 'sp-license-server' ); ?>
									</label>
									<input
										type="date"
										name="expires_date"
										value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '+1 year' ) ) ); ?>"
										style="margin-left: 10px;"
									>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="license-notes"><?php esc_html_e( 'Notes', 'sp-license-server' ); ?></label>
							</th>
							<td>
								<textarea
									id="license-notes"
									name="notes"
									rows="3"
									class="large-text"
									placeholder="<?php esc_attr_e( 'Optional admin notes...', 'sp-license-server' ); ?>"
								></textarea>
							</td>
						</tr>
					</table>

					<p class="submit">
						<button type="submit" name="sp_create_license" class="button button-primary">
							<?php esc_html_e( 'Generate License', 'sp-license-server' ); ?>
						</button>
					</p>
				</form>
			</div>

			<!-- License List -->
			<div class="sp-license-card">
				<h2><?php esc_html_e( 'All Licenses', 'sp-license-server' ); ?></h2>

				<?php if ( empty( $licenses ) ) : ?>
					<p><?php esc_html_e( 'No licenses created yet.', 'sp-license-server' ); ?></p>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th style="width: 180px;"><?php esc_html_e( 'License Key', 'sp-license-server' ); ?></th>
								<th><?php esc_html_e( 'Email', 'sp-license-server' ); ?></th>
								<th style="width: 100px;"><?php esc_html_e( 'Type', 'sp-license-server' ); ?></th>
								<th style="width: 100px;"><?php esc_html_e( 'Status', 'sp-license-server' ); ?></th>
								<th style="width: 120px;"><?php esc_html_e( 'Expires', 'sp-license-server' ); ?></th>
								<th style="width: 100px;"><?php esc_html_e( 'Activations', 'sp-license-server' ); ?></th>
								<th style="width: 120px;"><?php esc_html_e( 'Actions', 'sp-license-server' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $licenses as $license ) :
								$sites      = $this->get_activated_sites( $license );
								$site_count = count( $sites );
								$limit      = self::LICENSE_LIMITS[ $license->license_type ] ?? 1;
								$limit_text = $limit === PHP_INT_MAX ? '∞' : $limit;
							?>
								<tr>
									<td><code class="sp-license-key"><?php echo esc_html( $license->license_key ); ?></code></td>
									<td><?php echo esc_html( $license->license_email ); ?></td>
									<td><?php echo esc_html( ucfirst( $license->license_type ) ); ?></td>
									<td>
										<span class="sp-status-<?php echo esc_attr( $license->status ); ?>">
											<?php echo esc_html( ucfirst( $license->status ) ); ?>
										</span>
									</td>
									<td>
										<?php
										if ( empty( $license->expires_at ) ) {
											esc_html_e( 'Lifetime', 'sp-license-server' );
										} else {
											echo esc_html( date_i18n( 'M j, Y', strtotime( $license->expires_at ) ) );
										}
										?>
									</td>
									<td><?php echo esc_html( $site_count . ' / ' . $limit_text ); ?></td>
									<td>
										<a href="<?php echo esc_url( add_query_arg( 'edit', $license->id ) ); ?>" class="button button-small">
											<?php esc_html_e( 'Manage', 'sp-license-server' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<?php if ( $editing ) : ?>
				<!-- Edit License Details -->
				<div class="sp-license-card" id="edit-license">
					<h2>
						<?php
						printf(
							/* translators: %s: license key */
							esc_html__( 'Manage License: %s', 'sp-license-server' ),
							esc_html( $editing->license_key )
						);
						?>
					</h2>

					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Email:', 'sp-license-server' ); ?></th>
							<td><?php echo esc_html( $editing->license_email ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Type:', 'sp-license-server' ); ?></th>
							<td><?php echo esc_html( ucfirst( $editing->license_type ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Created:', 'sp-license-server' ); ?></th>
							<td><?php echo esc_html( date_i18n( 'F j, Y g:i a', strtotime( $editing->created_at ) ) ); ?></td>
						</tr>
						<?php if ( ! empty( $editing->notes ) ) : ?>
							<tr>
								<th><?php esc_html_e( 'Notes:', 'sp-license-server' ); ?></th>
								<td><?php echo esc_html( $editing->notes ); ?></td>
							</tr>
						<?php endif; ?>
					</table>

					<!-- Status Update -->
					<h3><?php esc_html_e( 'Update Status', 'sp-license-server' ); ?></h3>
					<form method="post" action="" style="display: inline-block; margin-right: 10px;">
						<?php wp_nonce_field( 'sp_update_status' ); ?>
						<input type="hidden" name="license_id" value="<?php echo esc_attr( $editing->id ); ?>">
						<select name="new_status">
							<option value="active" <?php selected( $editing->status, 'active' ); ?>><?php esc_html_e( 'Active', 'sp-license-server' ); ?></option>
							<option value="suspended" <?php selected( $editing->status, 'suspended' ); ?>><?php esc_html_e( 'Suspended', 'sp-license-server' ); ?></option>
							<option value="revoked" <?php selected( $editing->status, 'revoked' ); ?>><?php esc_html_e( 'Revoked', 'sp-license-server' ); ?></option>
							<option value="expired" <?php selected( $editing->status, 'expired' ); ?>><?php esc_html_e( 'Expired', 'sp-license-server' ); ?></option>
						</select>
						<button type="submit" name="sp_update_status" class="button">
							<?php esc_html_e( 'Update Status', 'sp-license-server' ); ?>
						</button>
					</form>

					<!-- Activated Sites -->
					<h3><?php esc_html_e( 'Activated Sites', 'sp-license-server' ); ?></h3>
					<?php
					$sites = $this->get_activated_sites( $editing );
					if ( empty( $sites ) ) :
					?>
						<p><?php esc_html_e( 'No sites currently activated.', 'sp-license-server' ); ?></p>
					<?php else : ?>
						<ul class="sp-sites-list">
							<?php foreach ( $sites as $site ) : ?>
								<li>
									<?php echo esc_html( $site ); ?>
									<form method="post" action="" style="display: inline; margin-left: 10px;">
										<?php wp_nonce_field( 'sp_remove_site' ); ?>
										<input type="hidden" name="license_id" value="<?php echo esc_attr( $editing->id ); ?>">
										<input type="hidden" name="site_url" value="<?php echo esc_attr( $site ); ?>">
										<button
											type="submit"
											name="sp_remove_site"
											class="button button-small button-link-delete"
											onclick="return confirm('<?php esc_attr_e( 'Remove this site?', 'sp-license-server' ); ?>');"
										>
											<?php esc_html_e( 'Remove', 'sp-license-server' ); ?>
										</button>
									</form>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<!-- Delete License -->
					<h3><?php esc_html_e( 'Delete License', 'sp-license-server' ); ?></h3>
					<form method="post" action="">
						<?php wp_nonce_field( 'sp_delete_license' ); ?>
						<input type="hidden" name="license_id" value="<?php echo esc_attr( $editing->id ); ?>">
						<p>
							<button
								type="submit"
								name="sp_delete_license"
								class="button button-link-delete"
								onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to permanently delete this license? This cannot be undone.', 'sp-license-server' ); ?>');"
							>
								<?php esc_html_e( 'Delete License Permanently', 'sp-license-server' ); ?>
							</button>
						</p>
					</form>

					<p>
						<a href="<?php echo esc_url( remove_query_arg( 'edit' ) ); ?>" class="button">
							<?php esc_html_e( '← Back to All Licenses', 'sp-license-server' ); ?>
						</a>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}

// Initialize plugin
add_action( 'plugins_loaded', array( 'SocietyPress_License_Server', 'get_instance' ) );

// Activation hook
register_activation_hook( __FILE__, array( 'SocietyPress_License_Server', 'activate' ) );

// Deactivation hook
register_deactivation_hook( __FILE__, array( 'SocietyPress_License_Server', 'deactivate' ) );
