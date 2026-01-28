<?php
/**
 * License Validation
 *
 * Commercial license enforcement with remote server validation.
 * Implements grace period to handle temporary network issues gracefully.
 *
 * WHY: Protects commercial product while being fair to legitimate customers.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_License
 *
 * License validation and enforcement.
 */
class SocietyPress_License {

	/**
	 * Database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * License server URL.
	 *
	 * @var string
	 */
	private string $api_url = 'https://stricklindevelopment.com/studiopress/api/v1/licenses';

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks(): void {
		add_action( 'admin_notices', array( $this, 'render_license_notice' ) );
		add_action( 'admin_init', array( $this, 'handle_license_actions' ) );
		add_action( 'societypress_check_license', array( $this, 'check_license' ) );
	}

	/**
	 * Activate license.
	 *
	 * WHY: Validates license key with remote server and stores locally.
	 *
	 * @param string $key   License key.
	 * @param string $email License email.
	 * @return array|WP_Error Result array or error.
	 */
	public function activate_license( string $key, string $email ) {
		// Validate inputs
		$key   = sanitize_text_field( $key );
		$email = sanitize_email( $email );

		if ( empty( $key ) || ! is_email( $email ) ) {
			return new WP_Error( 'invalid_input', __( 'Invalid license key or email.', 'societypress' ) );
		}

		// Call remote activation API
		$response = $this->remote_request( 'activate', array(
			'license_key'  => $key,
			'license_email' => $email,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Store license locally
		$table = SocietyPress::table( 'licenses' );

		$this->wpdb->replace(
			$table,
			array(
				'license_key'     => SocietyPress_Encryption::encrypt( $key ),
				'license_email'   => $email,
				'license_type'    => $response['license']['type'] ?? 'site',
				'status'          => $response['license']['status'] ?? 'active',
				'site_url'        => get_site_url(),
				'activation_date' => current_time( 'mysql' ),
				'expiration_date' => $response['license']['expires'] ?? null,
				'last_check'      => current_time( 'mysql' ),
				'check_failures'  => 0,
				'remote_data'     => wp_json_encode( $response ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		// Store in settings for quick access
		$settings                   = get_option( 'societypress_settings', array() );
		$settings['license_key']    = SocietyPress_Encryption::encrypt( $key );
		$settings['license_email']  = $email;
		$settings['license_status'] = $response['license']['status'] ?? 'active';
		update_option( 'societypress_settings', $settings );

		return $response;
	}

	/**
	 * Deactivate license.
	 *
	 * WHY: Frees up license slot for use on another site.
	 *
	 * @return bool|WP_Error Success or error.
	 */
	public function deactivate_license() {
		$license = $this->get_license();

		if ( ! $license ) {
			return new WP_Error( 'no_license', __( 'No license to deactivate.', 'societypress' ) );
		}

		// Call remote deactivation API
		$key = SocietyPress_Encryption::decrypt( $license->license_key );
		$response = $this->remote_request( 'deactivate', array(
			'license_key'  => $key,
		) );

		// Clear local data regardless of remote response
		$table = SocietyPress::table( 'licenses' );
		$this->wpdb->delete( $table, array( 'id' => $license->id ), array( '%d' ) );

		// Clear settings
		$settings = get_option( 'societypress_settings', array() );
		unset( $settings['license_key'], $settings['license_email'], $settings['license_status'] );
		update_option( 'societypress_settings', $settings );

		return true;
	}

	/**
	 * Check license (daily cron).
	 *
	 * WHY: Verifies license is still valid.
	 * Implements grace period to handle temporary network issues.
	 */
	public function check_license(): void {
		$license = $this->get_license();

		if ( ! $license ) {
			return;
		}

		$key = SocietyPress_Encryption::decrypt( $license->license_key );

		// Call remote check API
		$response = $this->remote_request( 'check', array(
			'license_key'  => $key,
		) );

		$table = SocietyPress::table( 'licenses' );

		if ( is_wp_error( $response ) ) {
			// Failed check - increment failures
			$this->handle_failed_check( $license );
			return;
		}

		// Successful check - update status
		$this->wpdb->update(
			$table,
			array(
				'status'          => $response['license']['status'] ?? 'active',
				'expiration_date' => $response['license']['expires'] ?? null,
				'last_check'      => current_time( 'mysql' ),
				'check_failures'  => 0,
				'grace_period_ends' => null,
				'remote_data'     => wp_json_encode( $response ),
			),
			array( 'id' => $license->id ),
			array( '%s', '%s', '%s', '%d', '%s', '%s' ),
			array( '%d' )
		);

		// Update settings cache
		$settings                   = get_option( 'societypress_settings', array() );
		$settings['license_status'] = $response['license']['status'] ?? 'active';
		update_option( 'societypress_settings', $settings );
	}

	/**
	 * Handle failed license check.
	 *
	 * WHY: Implements grace period (7 days after 3 failures).
	 * Prevents immediate cutoff due to temporary network issues.
	 *
	 * @param object $license License record.
	 */
	private function handle_failed_check( object $license ): void {
		$table    = SocietyPress::table( 'licenses' );
		$failures = (int) $license->check_failures + 1;

		// After 3 failures, enter grace period
		if ( $failures >= 3 && empty( $license->grace_period_ends ) ) {
			$grace_ends = date( 'Y-m-d H:i:s', strtotime( '+7 days' ) );

			$this->wpdb->update(
				$table,
				array(
					'check_failures'     => $failures,
					'grace_period_ends'  => $grace_ends,
					'last_check'         => current_time( 'mysql' ),
				),
				array( 'id' => $license->id ),
				array( '%d', '%s', '%s' ),
				array( '%d' )
			);
		} else {
			// Increment failure count
			$this->wpdb->update(
				$table,
				array(
					'check_failures' => $failures,
					'last_check'     => current_time( 'mysql' ),
				),
				array( 'id' => $license->id ),
				array( '%d', '%s' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Get license status.
	 *
	 * WHY: Quick cached check for license validity.
	 *
	 * @return string Status: active, expired, invalid, grace_period, developer, none.
	 */
	public function get_license_status(): string {
		// Development environment bypass
		if ( $this->is_development_environment() ) {
			return 'developer';
		}

		$settings = get_option( 'societypress_settings', array() );

		// No license key
		if ( empty( $settings['license_key'] ) ) {
			return 'none';
		}

		$license = $this->get_license();

		if ( ! $license ) {
			return 'none';
		}

		// Check if in grace period
		if ( $this->is_in_grace_period() ) {
			return 'grace_period';
		}

		// Check if grace period expired
		if ( ! empty( $license->grace_period_ends ) && strtotime( $license->grace_period_ends ) < time() ) {
			return 'invalid';
		}

		return $license->status ?? 'invalid';
	}

	/**
	 * Check if license is valid.
	 *
	 * WHY: Boolean check for feature gating.
	 *
	 * @return bool Whether license is valid.
	 */
	public function is_valid(): bool {
		$status = $this->get_license_status();
		return in_array( $status, array( 'active', 'grace_period', 'developer' ), true );
	}

	/**
	 * Check if in grace period.
	 *
	 * WHY: Grace period allows continued use during temporary validation failures.
	 *
	 * @return bool Whether in grace period.
	 */
	public function is_in_grace_period(): bool {
		$license = $this->get_license();

		if ( ! $license || empty( $license->grace_period_ends ) ) {
			return false;
		}

		return strtotime( $license->grace_period_ends ) > time();
	}

	/**
	 * Check if in development environment.
	 *
	 * WHY: Auto-bypass license checks on localhost and dev domains.
	 *
	 * @return bool Whether in development environment.
	 */
	private function is_development_environment(): bool {
		// WP_DEBUG bypass
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return true;
		}

		// Check hostname
		$host = wp_parse_url( get_site_url(), PHP_URL_HOST );

		// Common local development hosts
		$dev_hosts = array( 'localhost', '127.0.0.1', '::1' );

		if ( in_array( $host, $dev_hosts, true ) ) {
			return true;
		}

		// Check for .local or .test TLD
		if ( preg_match( '/\.(local|test)$/i', $host ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get license record.
	 *
	 * WHY: Retrieve stored license from database.
	 *
	 * @return object|null License record.
	 */
	private function get_license(): ?object {
		$table = SocietyPress::table( 'licenses' );

		$license = $this->wpdb->get_row(
			"SELECT * FROM {$table} ORDER BY id DESC LIMIT 1"
		);

		return $license ?: null;
	}

	/**
	 * Make remote API request.
	 *
	 * WHY: Centralized communication with license server.
	 * Includes request signing for security.
	 *
	 * @param string $action API action (activate, check, deactivate).
	 * @param array  $data   Request data.
	 * @return array|WP_Error Response or error.
	 */
	private function remote_request( string $action, array $data ) {
		$url = $this->api_url . '/' . $action;

		// Add site info
		$data['site_url']       = get_site_url();
		$data['wp_version']     = get_bloginfo( 'version' );
		$data['php_version']    = PHP_VERSION;
		$data['plugin_version'] = SOCIETYPRESS_VERSION;

		// Sign request (HMAC SHA256)
		// NOTE: In production, this salt should be a shared secret with the license server
		$salt            = 'societypress_license_salt';
		$data['signature'] = hash_hmac( 'sha256', $data['site_url'] . ( $data['license_key'] ?? '' ), $salt );

		// Make request
		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 10,
				'body'    => $data,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		// Handle errors
		if ( is_wp_error( $response ) ) {
			error_log( 'SocietyPress License: Request failed - ' . $response->get_error_message() );
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code !== 200 ) {
			error_log( sprintf( 'SocietyPress License: Server returned %d - %s', $code, $body ) );
			return new WP_Error( 'server_error', __( 'License server error. Please try again later.', 'societypress' ) );
		}

		$result = json_decode( $body, true );

		if ( empty( $result['success'] ) ) {
			$message = $result['message'] ?? __( 'License validation failed.', 'societypress' );
			return new WP_Error( 'validation_failed', $message );
		}

		return $result;
	}

	/**
	 * Render license admin notice.
	 *
	 * WHY: Inform admins about license issues.
	 */
	public function render_license_notice(): void {
		// Only show to admins on SocietyPress pages
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'societypress' ) === false ) {
			return;
		}

		// Check if user dismissed notice
		$settings = get_option( 'societypress_settings', array() );
		if ( ! empty( $settings['license_hide_notice'] ) ) {
			return;
		}

		$status = $this->get_license_status();

		// Developer license - no notice needed
		if ( 'developer' === $status ) {
			return;
		}

		// No license
		if ( 'none' === $status ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'SocietyPress License Required', 'societypress' ); ?></strong><br>
					<?php
					printf(
						/* translators: %s: link to license page */
						esc_html__( 'Please %s to activate all features.', 'societypress' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=societypress-license' ) ) . '">' . esc_html__( 'enter your license key', 'societypress' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
			return;
		}

		// Grace period
		if ( 'grace_period' === $status ) {
			$license    = $this->get_license();
			$days_left  = max( 0, ceil( ( strtotime( $license->grace_period_ends ) - time() ) / DAY_IN_SECONDS ) );
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'SocietyPress License Verification Failed', 'societypress' ); ?></strong><br>
					<?php
					printf(
						/* translators: %d: days remaining */
						esc_html__( 'Unable to verify your license. Features will be disabled in %d days if this issue is not resolved.', 'societypress' ),
						absint( $days_left )
					);
					?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-license' ) ); ?>"><?php esc_html_e( 'Check license status', 'societypress' ); ?></a>
				</p>
			</div>
			<?php
			return;
		}

		// Invalid/Expired
		if ( in_array( $status, array( 'invalid', 'expired', 'suspended' ), true ) ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'SocietyPress License Invalid', 'societypress' ); ?></strong><br>
					<?php
					printf(
						/* translators: %s: link to license page */
						esc_html__( 'Your license is no longer valid. Please %s to restore full functionality.', 'societypress' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=societypress-license' ) ) . '">' . esc_html__( 'renew your license', 'societypress' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Handle license form actions.
	 *
	 * WHY: Process activation/deactivation from admin page.
	 */
	public function handle_license_actions(): void {
		// Check page
		if ( empty( $_GET['page'] ) || 'societypress-license' !== $_GET['page'] ) {
			return;
		}

		// Handle activation
		if ( isset( $_POST['sp_activate_license'] ) ) {
			check_admin_referer( 'sp_activate_license' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to manage licenses.', 'societypress' ) );
			}

			$key   = isset( $_POST['license_key'] ) ? sanitize_text_field( $_POST['license_key'] ) : '';
			$email = isset( $_POST['license_email'] ) ? sanitize_email( $_POST['license_email'] ) : '';

			$result = $this->activate_license( $key, $email );

			if ( is_wp_error( $result ) ) {
				add_settings_error(
					'societypress_license',
					'activation_failed',
					$result->get_error_message(),
					'error'
				);
			} else {
				add_settings_error(
					'societypress_license',
					'activation_success',
					__( 'License activated successfully!', 'societypress' ),
					'success'
				);
			}
		}

		// Handle deactivation
		if ( isset( $_POST['sp_deactivate_license'] ) ) {
			check_admin_referer( 'sp_deactivate_license' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have permission to manage licenses.', 'societypress' ) );
			}

			$result = $this->deactivate_license();

			if ( is_wp_error( $result ) ) {
				add_settings_error(
					'societypress_license',
					'deactivation_failed',
					$result->get_error_message(),
					'error'
				);
			} else {
				add_settings_error(
					'societypress_license',
					'deactivation_success',
					__( 'License deactivated successfully.', 'societypress' ),
					'success'
				);
			}
		}
	}

	/**
	 * Render license key field for settings page.
	 *
	 * WHY: Provides a compact license status/input field for the main settings page,
	 * as opposed to the full activation page. Used by WordPress Settings API callback.
	 */
	public function render_license_field(): void {
		$status  = $this->get_license_status();
		$license = $this->get_license();

		// Development mode - just show status
		if ( 'developer' === $status ) {
			echo '<span class="sp-license-badge sp-license-developer">';
			esc_html_e( 'Developer Mode', 'societypress' );
			echo '</span>';
			echo '<p class="description">';
			esc_html_e( 'License checks bypassed in development environment.', 'societypress' );
			echo '</p>';
			return;
		}

		// Active license - show status and masked key
		if ( $license && in_array( $status, array( 'active', 'grace_period' ), true ) ) {
			$status_class = 'active' === $status ? 'sp-license-active' : 'sp-license-warning';
			$status_label = 'active' === $status
				? __( 'Active', 'societypress' )
				: __( 'Grace Period', 'societypress' );

			echo '<span class="sp-license-badge ' . esc_attr( $status_class ) . '">';
			echo esc_html( $status_label );
			echo '</span>';
			echo ' <code>••••-••••-••••-' . esc_html( substr( SocietyPress_Encryption::decrypt( $license->license_key ), -4 ) ) . '</code>';
			echo '<p class="description">';
			printf(
				/* translators: %s: link to license page */
				esc_html__( 'Manage your license on the %s.', 'societypress' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=societypress-license' ) ) . '">' . esc_html__( 'License page', 'societypress' ) . '</a>'
			);
			echo '</p>';
			return;
		}

		// No license or invalid - show input prompt
		echo '<span class="sp-license-badge sp-license-inactive">';
		esc_html_e( 'Not Activated', 'societypress' );
		echo '</span>';
		echo '<p class="description">';
		printf(
			/* translators: %s: link to license page */
			esc_html__( '%s to unlock all features.', 'societypress' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=societypress-license' ) ) . '">' . esc_html__( 'Activate your license', 'societypress' ) . '</a>'
		);
		echo '</p>';
	}

	/**
	 * Render license activation page.
	 *
	 * WHY: Admin UI for license management.
	 */
	public function render_activation_page(): void {
		$license = $this->get_license();
		$status  = $this->get_license_status();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SocietyPress License', 'societypress' ); ?></h1>

			<?php settings_errors( 'societypress_license' ); ?>

			<?php if ( 'developer' === $status ) : ?>
				<!-- Developer License -->
				<div class="notice notice-info inline">
					<p>
						<strong><?php esc_html_e( 'Developer License Active', 'societypress' ); ?></strong><br>
						<?php esc_html_e( 'Running in development mode. License checks are bypassed on localhost and when WP_DEBUG is enabled.', 'societypress' ); ?>
					</p>
				</div>
			<?php elseif ( 'none' === $status ) : ?>
				<!-- Activation Form -->
				<div class="card">
					<h2><?php esc_html_e( 'Activate Your License', 'societypress' ); ?></h2>
					<p><?php esc_html_e( 'Enter your license key and email to activate SocietyPress.', 'societypress' ); ?></p>

					<form method="post" action="">
						<?php wp_nonce_field( 'sp_activate_license' ); ?>

						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="license-key"><?php esc_html_e( 'License Key', 'societypress' ); ?></label>
								</th>
								<td>
									<input
										type="text"
										id="license-key"
										name="license_key"
										class="regular-text"
										placeholder="XXXX-XXXX-XXXX-XXXX"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="license-email"><?php esc_html_e( 'License Email', 'societypress' ); ?></label>
								</th>
								<td>
									<input
										type="email"
										id="license-email"
										name="license_email"
										class="regular-text"
										placeholder="your@email.com"
										required
									>
									<p class="description"><?php esc_html_e( 'The email address used when purchasing.', 'societypress' ); ?></p>
								</td>
							</tr>
						</table>

						<p class="submit">
							<button type="submit" name="sp_activate_license" class="button button-primary">
								<?php esc_html_e( 'Activate License', 'societypress' ); ?>
							</button>
						</p>
					</form>

					<p>
						<?php
						printf(
							/* translators: %s: purchase URL */
							esc_html__( "Don't have a license? %s", 'societypress' ),
							'<a href="https://stricklindevelopment.com/studiopress/societypress" target="_blank">' . esc_html__( 'Purchase one here', 'societypress' ) . '</a>'
						);
						?>
					</p>
				</div>
			<?php else : ?>
				<!-- License Status -->
				<div class="card">
					<h2><?php esc_html_e( 'License Status', 'societypress' ); ?></h2>

					<table class="form-table">
						<tr>
							<th><?php esc_html_e( 'Status:', 'societypress' ); ?></th>
							<td>
								<span class="sp-license-status sp-license-<?php echo esc_attr( $status ); ?>">
									<?php echo esc_html( ucfirst( str_replace( '_', ' ', $status ) ) ); ?>
								</span>
							</td>
						</tr>
						<?php if ( $license ) : ?>
							<tr>
								<th><?php esc_html_e( 'Type:', 'societypress' ); ?></th>
								<td><?php echo esc_html( ucfirst( $license->license_type ) ); ?></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Email:', 'societypress' ); ?></th>
								<td><?php echo esc_html( $license->license_email ); ?></td>
							</tr>
							<?php if ( ! empty( $license->expiration_date ) ) : ?>
								<tr>
									<th><?php esc_html_e( 'Expires:', 'societypress' ); ?></th>
									<td><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $license->expiration_date ) ) ); ?></td>
								</tr>
							<?php endif; ?>
							<tr>
								<th><?php esc_html_e( 'Last Check:', 'societypress' ); ?></th>
								<td><?php echo esc_html( date_i18n( 'F j, Y g:i a', strtotime( $license->last_check ) ) ); ?></td>
							</tr>
						<?php endif; ?>
					</table>

					<form method="post" action="" onsubmit="return confirm('<?php esc_attr_e( 'Are you sure you want to deactivate this license?', 'societypress' ); ?>');">
						<?php wp_nonce_field( 'sp_deactivate_license' ); ?>
						<p class="submit">
							<button type="submit" name="sp_deactivate_license" class="button button-secondary">
								<?php esc_html_e( 'Deactivate License', 'societypress' ); ?>
							</button>
						</p>
					</form>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
