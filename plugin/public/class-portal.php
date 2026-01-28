<?php
/**
 * Member Self-Service Portal
 *
 * Allows members to log in and update their own contact info, research interests,
 * and directory preferences without admin intervention.
 *
 * WHY: Reduces admin workload and empowers members to keep their info current.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Portal
 *
 * Member portal functionality.
 */
class SocietyPress_Portal {

	/**
	 * Database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

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
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_portal_assets' ) );
		add_action( 'wp_ajax_societypress_portal_save_field', array( $this, 'handle_ajax_save_field' ) );
		add_action( 'wp_ajax_societypress_portal_update_profile', array( $this, 'handle_profile_update' ) );
	}

	/**
	 * Register shortcode.
	 */
	public function register_shortcode(): void {
		add_shortcode( 'societypress_portal', array( $this, 'render_portal' ) );
	}

	/**
	 * Enqueue portal assets.
	 */
	public function enqueue_portal_assets(): void {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'societypress_portal' ) ) {
			return;
		}

		wp_enqueue_style(
			'societypress-portal',
			SOCIETYPRESS_URL . 'assets/css/portal.css',
			array(),
			SOCIETYPRESS_VERSION
		);

		wp_enqueue_script(
			'societypress-portal',
			SOCIETYPRESS_URL . 'assets/js/portal.js',
			array( 'jquery' ),
			SOCIETYPRESS_VERSION,
			true
		);

		wp_localize_script(
			'societypress-portal',
			'societypressPortal',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'societypress_portal' ),
				'strings' => array(
					'saved'       => __( 'Saved!', 'societypress' ),
					'saving'      => __( 'Saving...', 'societypress' ),
					'error'       => __( 'Error saving. Please try again.', 'societypress' ),
					'rateLimit'   => __( 'Too many updates. Please wait a moment.', 'societypress' ),
				),
			)
		);
	}

	/**
	 * Render portal shortcode.
	 *
	 * WHY: Main entry point - shows login form or member dashboard based on auth status.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_portal( $atts ): string {
		$settings = get_option( 'societypress_settings', array() );

		// Check if portal is enabled
		if ( empty( $settings['portal_enabled'] ) ) {
			return '<div class="sp-portal-disabled"><p>' . esc_html__( 'The member portal is currently unavailable.', 'societypress' ) . '</p></div>';
		}

		// Not logged in - show login form
		if ( ! is_user_logged_in() ) {
			return $this->render_login_form();
		}

		// Get member from current user
		$user_id   = get_current_user_id();
		$member_id = $this->get_member_from_user( $user_id );

		// Logged in but not a member
		if ( ! $member_id ) {
			return '<div class="sp-portal-error"><p>' . esc_html__( 'Your account is not associated with a membership. Please contact us for assistance.', 'societypress' ) . '</p></div>';
		}

		// Check capability
		if ( ! current_user_can( 'access_member_portal' ) ) {
			return '<div class="sp-portal-error"><p>' . esc_html__( 'You do not have permission to access the member portal.', 'societypress' ) . '</p></div>';
		}

		// Log portal access
		$this->log_portal_access( $member_id );

		// Render dashboard
		return $this->render_dashboard( $member_id );
	}

	/**
	 * Render login form.
	 *
	 * WHY: Uses WordPress native login for security and compatibility.
	 *
	 * @return string HTML output.
	 */
	private function render_login_form(): string {
		ob_start();
		?>
		<div class="sp-portal-login">
			<h2><?php esc_html_e( 'Member Login', 'societypress' ); ?></h2>
			<p><?php esc_html_e( 'Please log in to access your member portal.', 'societypress' ); ?></p>

			<?php
			wp_login_form(
				array(
					'echo'           => true,
					'redirect'       => get_permalink(),
					'label_username' => __( 'Email Address', 'societypress' ),
					'label_password' => __( 'Password', 'societypress' ),
					'label_remember' => __( 'Remember Me', 'societypress' ),
					'label_log_in'   => __( 'Log In', 'societypress' ),
				)
			);
			?>

			<p class="sp-login-links">
				<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>">
					<?php esc_html_e( 'Forgot your password?', 'societypress' ); ?>
				</a>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render member dashboard.
	 *
	 * WHY: Shows membership status and editable profile form.
	 *
	 * @param int $member_id Member ID.
	 * @return string HTML output.
	 */
	private function render_dashboard( int $member_id ): string {
		$member = societypress()->members->get_full( $member_id );

		if ( ! $member ) {
			return '<div class="sp-portal-error"><p>' . esc_html__( 'Error loading your membership information.', 'societypress' ) . '</p></div>';
		}

		ob_start();
		?>
		<div class="sp-portal-dashboard">
			<div class="sp-portal-header">
				<h2><?php printf( esc_html__( 'Welcome, %s!', 'societypress' ), esc_html( $member['first_name'] ) ); ?></h2>
				<a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" class="sp-logout-link">
					<?php esc_html_e( 'Log Out', 'societypress' ); ?>
				</a>
			</div>

			<div class="sp-portal-content">
				<!-- Membership Status Widget -->
				<div class="sp-dashboard-widget sp-membership-status">
					<h3><?php esc_html_e( 'Membership Status', 'societypress' ); ?></h3>
					<table class="sp-status-table">
						<tr>
							<th><?php esc_html_e( 'Status:', 'societypress' ); ?></th>
							<td>
								<span class="sp-status-badge sp-status-<?php echo esc_attr( $member['status'] ); ?>">
									<?php echo esc_html( ucfirst( $member['status'] ) ); ?>
								</span>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Tier:', 'societypress' ); ?></th>
							<td><?php echo esc_html( $member['tier']['name'] ?? '' ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Member Since:', 'societypress' ); ?></th>
							<td><?php echo esc_html( date( 'Y', strtotime( $member['join_date'] ) ) ); ?></td>
						</tr>
						<?php if ( ! empty( $member['expiration_date'] ) ) : ?>
							<tr>
								<th><?php esc_html_e( 'Expires:', 'societypress' ); ?></th>
								<td><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $member['expiration_date'] ) ) ); ?></td>
							</tr>
						<?php endif; ?>
					</table>
				</div>

				<!-- Editable Profile -->
				<div class="sp-dashboard-widget sp-profile-form">
					<h3><?php esc_html_e( 'Your Profile', 'societypress' ); ?></h3>
					<?php echo $this->render_profile_form( $member ); ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render editable profile form.
	 *
	 * WHY: Allows members to update their own information.
	 * Rate limiting prevents abuse (max 10 updates per hour).
	 *
	 * @param array $member Member data.
	 * @return string HTML output.
	 */
	private function render_profile_form( array $member ): string {
		$settings = get_option( 'societypress_settings', array() );
		$editable_fields = $settings['portal_editable_fields'] ?? array( 'email', 'phone', 'address', 'surnames', 'research_areas' );

		ob_start();
		?>
		<form id="sp-portal-profile-form" method="post">
			<?php wp_nonce_field( 'sp_update_profile', 'sp_profile_nonce' ); ?>

			<!-- Contact Information -->
			<?php if ( in_array( 'email', $editable_fields, true ) || in_array( 'phone', $editable_fields, true ) || in_array( 'address', $editable_fields, true ) ) : ?>
				<div class="sp-form-section">
					<h4><?php esc_html_e( 'Contact Information', 'societypress' ); ?></h4>

					<?php if ( in_array( 'email', $editable_fields, true ) ) : ?>
						<div class="sp-form-field">
							<label for="sp-primary-email"><?php esc_html_e( 'Email Address', 'societypress' ); ?></label>
							<input
								type="email"
								id="sp-primary-email"
								name="primary_email"
								class="sp-portal-field"
								data-field="primary_email"
								value="<?php echo esc_attr( $member['contact']['primary_email'] ?? '' ); ?>"
							>
						</div>
					<?php endif; ?>

					<?php if ( in_array( 'phone', $editable_fields, true ) ) : ?>
						<div class="sp-form-row">
							<div class="sp-form-field">
								<label for="sp-phone-home"><?php esc_html_e( 'Home Phone', 'societypress' ); ?></label>
								<input
									type="tel"
									id="sp-phone-home"
									name="phone_home"
									class="sp-portal-field"
									data-field="phone_home"
									value="<?php echo esc_attr( $member['contact']['phone_home'] ?? '' ); ?>"
								>
							</div>
							<div class="sp-form-field">
								<label for="sp-phone-cell"><?php esc_html_e( 'Cell Phone', 'societypress' ); ?></label>
								<input
									type="tel"
									id="sp-phone-cell"
									name="phone_cell"
									class="sp-portal-field"
									data-field="phone_cell"
									value="<?php echo esc_attr( $member['contact']['phone_cell'] ?? '' ); ?>"
								>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( in_array( 'address', $editable_fields, true ) ) : ?>
						<div class="sp-form-field">
							<label for="sp-street-address"><?php esc_html_e( 'Street Address', 'societypress' ); ?></label>
							<input
								type="text"
								id="sp-street-address"
								name="street_address"
								class="sp-portal-field"
								data-field="street_address"
								value="<?php echo esc_attr( $member['contact']['street_address'] ?? '' ); ?>"
							>
						</div>

						<div class="sp-form-row">
							<div class="sp-form-field">
								<label for="sp-city"><?php esc_html_e( 'City', 'societypress' ); ?></label>
								<input
									type="text"
									id="sp-city"
									name="city"
									class="sp-portal-field"
									data-field="city"
									value="<?php echo esc_attr( $member['contact']['city'] ?? '' ); ?>"
								>
							</div>
							<div class="sp-form-field">
								<label for="sp-state"><?php esc_html_e( 'State/Province', 'societypress' ); ?></label>
								<input
									type="text"
									id="sp-state"
									name="state_province"
									class="sp-portal-field"
									data-field="state_province"
									value="<?php echo esc_attr( $member['contact']['state_province'] ?? '' ); ?>"
								>
							</div>
							<div class="sp-form-field">
								<label for="sp-postal-code"><?php esc_html_e( 'Postal Code', 'societypress' ); ?></label>
								<input
									type="text"
									id="sp-postal-code"
									name="postal_code"
									class="sp-portal-field"
									data-field="postal_code"
									value="<?php echo esc_attr( $member['contact']['postal_code'] ?? '' ); ?>"
								>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<!-- Preferences -->
			<div class="sp-form-section">
				<h4><?php esc_html_e( 'Preferences', 'societypress' ); ?></h4>

				<div class="sp-form-field">
					<label>
						<input
							type="checkbox"
							name="directory_visible"
							class="sp-portal-field"
							data-field="directory_visible"
							value="1"
							<?php checked( ! empty( $member['directory_visible'] ) ); ?>
						>
						<?php esc_html_e( 'Show my profile in the member directory', 'societypress' ); ?>
					</label>
				</div>
			</div>

			<div class="sp-form-actions">
				<button type="submit" class="sp-save-profile-btn">
					<?php esc_html_e( 'Save Changes', 'societypress' ); ?>
				</button>
				<span class="sp-save-status"></span>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle profile update (form submission).
	 *
	 * WHY: Process and validate all profile updates securely.
	 */
	public function handle_profile_update(): void {
		// Verify nonce
		check_ajax_referer( 'societypress_portal', 'nonce' );

		// Check rate limiting
		$user_id = get_current_user_id();
		if ( ! $this->check_rate_limit( $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Too many updates. Please wait a moment.', 'societypress' ) ) );
		}

		// Get member
		$member_id = $this->get_member_from_user( $user_id );
		if ( ! $member_id ) {
			wp_send_json_error( array( 'message' => __( 'Member not found.', 'societypress' ) ) );
		}

		// Get editable fields setting
		$settings = get_option( 'societypress_settings', array() );
		$editable_fields = $settings['portal_editable_fields'] ?? array( 'email', 'phone', 'address' );

		// Prepare contact data update
		$contact_data = array();

		if ( in_array( 'email', $editable_fields, true ) && isset( $_POST['primary_email'] ) ) {
			$contact_data['primary_email'] = sanitize_email( $_POST['primary_email'] );
		}

		if ( in_array( 'phone', $editable_fields, true ) ) {
			if ( isset( $_POST['phone_home'] ) ) {
				$contact_data['phone_home'] = sanitize_text_field( $_POST['phone_home'] );
			}
			if ( isset( $_POST['phone_cell'] ) ) {
				$contact_data['phone_cell'] = sanitize_text_field( $_POST['phone_cell'] );
			}
		}

		if ( in_array( 'address', $editable_fields, true ) ) {
			if ( isset( $_POST['street_address'] ) ) {
				$contact_data['street_address'] = sanitize_text_field( $_POST['street_address'] );
			}
			if ( isset( $_POST['city'] ) ) {
				$contact_data['city'] = sanitize_text_field( $_POST['city'] );
			}
			if ( isset( $_POST['state_province'] ) ) {
				$contact_data['state_province'] = sanitize_text_field( $_POST['state_province'] );
			}
			if ( isset( $_POST['postal_code'] ) ) {
				$contact_data['postal_code'] = sanitize_text_field( $_POST['postal_code'] );
			}
		}

		// Update contact info
		if ( ! empty( $contact_data ) ) {
			societypress()->members->save_contact( $member_id, $contact_data );
		}

		// Update member preferences
		$member_data = array();

		if ( isset( $_POST['directory_visible'] ) ) {
			$member_data['directory_visible'] = ! empty( $_POST['directory_visible'] ) ? 1 : 0;
		}

		if ( ! empty( $member_data ) ) {
			societypress()->members->update( $member_id, $member_data );
		}

		// Increment rate limit counter
		$this->increment_rate_limit( $user_id );

		wp_send_json_success( array( 'message' => __( 'Profile updated successfully.', 'societypress' ) ) );
	}

	/**
	 * Handle AJAX field save (auto-save on blur).
	 *
	 * WHY: Provides immediate feedback and saves changes without full form submission.
	 */
	public function handle_ajax_save_field(): void {
		check_ajax_referer( 'societypress_portal', 'nonce' );

		$user_id   = get_current_user_id();
		$member_id = $this->get_member_from_user( $user_id );

		if ( ! $member_id ) {
			wp_send_json_error( array( 'message' => __( 'Member not found.', 'societypress' ) ) );
		}

		// Check rate limiting
		if ( ! $this->check_rate_limit( $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Too many updates. Please wait.', 'societypress' ) ) );
		}

		$field = isset( $_POST['field'] ) ? sanitize_text_field( $_POST['field'] ) : '';
		$value = isset( $_POST['value'] ) ? sanitize_text_field( $_POST['value'] ) : '';

		// Validate field is editable
		$settings = get_option( 'societypress_settings', array() );
		$editable_fields = $settings['portal_editable_fields'] ?? array();

		$contact_fields = array( 'primary_email', 'phone_home', 'phone_cell', 'street_address', 'city', 'state_province', 'postal_code' );
		$member_fields = array( 'directory_visible' );

		if ( in_array( $field, $contact_fields, true ) && in_array( 'email', $editable_fields, true ) || in_array( 'phone', $editable_fields, true ) || in_array( 'address', $editable_fields, true ) ) {
			// Update contact field
			societypress()->members->save_contact( $member_id, array( $field => $value ) );
		} elseif ( in_array( $field, $member_fields, true ) ) {
			// Update member field
			societypress()->members->update( $member_id, array( $field => $value ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid field.', 'societypress' ) ) );
		}

		$this->increment_rate_limit( $user_id );

		wp_send_json_success( array( 'message' => __( 'Saved!', 'societypress' ) ) );
	}

	/**
	 * Get member ID from WordPress user ID.
	 *
	 * WHY: Links WordPress user accounts to member records.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return int|false Member ID or false.
	 */
	public function get_member_from_user( int $user_id ) {
		$member_id = get_user_meta( $user_id, 'societypress_member_id', true );
		return $member_id ? (int) $member_id : false;
	}

	/**
	 * Link WordPress user to member record.
	 *
	 * WHY: Creates association between WordPress user and member.
	 * Called when member is created/imported.
	 *
	 * @param int $user_id   WordPress user ID.
	 * @param int $member_id Member ID.
	 * @return bool Success.
	 */
	public function link_wordpress_user( int $user_id, int $member_id ): bool {
		return update_user_meta( $user_id, 'societypress_member_id', $member_id );
	}

	/**
	 * Log portal access.
	 *
	 * WHY: Track when members last accessed the portal.
	 * Updates portal_last_login timestamp.
	 *
	 * @param int $member_id Member ID.
	 */
	private function log_portal_access( int $member_id ): void {
		$table = SocietyPress::table( 'members' );

		$this->wpdb->update(
			$table,
			array( 'portal_last_login' => current_time( 'mysql' ) ),
			array( 'id' => $member_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Check rate limiting.
	 *
	 * WHY: Prevents abuse by limiting to 10 updates per hour per user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return bool Whether user is within rate limit.
	 */
	private function check_rate_limit( int $user_id ): bool {
		$key   = 'sp_portal_rate_' . $user_id;
		$count = get_transient( $key );

		// First update or expired transient
		if ( false === $count ) {
			return true;
		}

		// Check if over limit
		return $count < 10;
	}

	/**
	 * Increment rate limit counter.
	 *
	 * WHY: Track number of updates in current hour.
	 *
	 * @param int $user_id WordPress user ID.
	 */
	private function increment_rate_limit( int $user_id ): void {
		$key   = 'sp_portal_rate_' . $user_id;
		$count = get_transient( $key );

		if ( false === $count ) {
			set_transient( $key, 1, HOUR_IN_SECONDS );
		} else {
			set_transient( $key, $count + 1, HOUR_IN_SECONDS );
		}
	}
}
