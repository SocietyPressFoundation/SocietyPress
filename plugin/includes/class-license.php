<?php
/**
 * License / Support System
 *
 * WHY: Shareware model - no enforcement, just encouragement to support the project.
 * All features work without payment. Users are encouraged to donate if they find value.
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
 * Simplified "license" class for shareware model.
 * Always returns valid - no enforcement.
 */
class SocietyPress_License {

	/**
	 * Support/donation URL.
	 *
	 * @var string
	 */
	private string $support_url = 'https://stricklindevelopment.com/support-societypress';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// No hooks needed for enforcement - just support messaging
	}

	/**
	 * Get license status.
	 *
	 * WHY: Always returns 'active' - shareware model has no restrictions.
	 *
	 * @return string Always 'active'.
	 */
	public function get_license_status(): string {
		return 'active';
	}

	/**
	 * Check if license is valid.
	 *
	 * WHY: Always true - shareware model.
	 *
	 * @return bool Always true.
	 */
	public function is_valid(): bool {
		return true;
	}

	/**
	 * Check if in grace period.
	 *
	 * WHY: Never in grace period - no enforcement.
	 *
	 * @return bool Always false.
	 */
	public function is_in_grace_period(): bool {
		return false;
	}

	/**
	 * Get support URL.
	 *
	 * @return string Support/donation URL.
	 */
	public function get_support_url(): string {
		return $this->support_url;
	}

	/**
	 * Render support field for settings page.
	 *
	 * WHY: Shows appreciation message and support link instead of license key input.
	 */
	public function render_license_field(): void {
		?>
		<div class="sp-support-message">
			<p>
				<span class="dashicons dashicons-heart" style="color: #e25555;"></span>
				<strong><?php esc_html_e( 'SocietyPress is shareware!', 'societypress' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( 'All features are fully unlocked. If SocietyPress helps your organization, please consider supporting its continued development.', 'societypress' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $this->support_url ); ?>" target="_blank" class="button button-secondary">
					<span class="dashicons dashicons-external" style="vertical-align: middle;"></span>
					<?php esc_html_e( 'Support This Project', 'societypress' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Render support page.
	 *
	 * WHY: Full page explaining the shareware model and how to support.
	 */
	public function render_license_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Support SocietyPress', 'societypress' ); ?></h1>

			<div class="card" style="max-width: 600px;">
				<h2>
					<span class="dashicons dashicons-heart" style="color: #e25555;"></span>
					<?php esc_html_e( 'Shareware Model', 'societypress' ); ?>
				</h2>

				<p><?php esc_html_e( 'SocietyPress is developed specifically for genealogical and historical societies. All features are fully unlocked with no license key required.', 'societypress' ); ?></p>

				<p><?php esc_html_e( 'If SocietyPress saves your organization time and helps you serve your members better, please consider making a donation to support continued development.', 'societypress' ); ?></p>

				<h3><?php esc_html_e( 'Your support helps fund:', 'societypress' ); ?></h3>
				<ul style="list-style: disc; margin-left: 20px;">
					<li><?php esc_html_e( 'New features and improvements', 'societypress' ); ?></li>
					<li><?php esc_html_e( 'WordPress compatibility updates', 'societypress' ); ?></li>
					<li><?php esc_html_e( 'Security patches', 'societypress' ); ?></li>
					<li><?php esc_html_e( 'Documentation and tutorials', 'societypress' ); ?></li>
				</ul>

				<p style="margin-top: 20px;">
					<a href="<?php echo esc_url( $this->support_url ); ?>" target="_blank" class="button button-primary button-hero">
						<?php esc_html_e( 'Support SocietyPress', 'societypress' ); ?>
					</a>
				</p>

				<p class="description" style="margin-top: 15px;">
					<?php esc_html_e( 'Thank you to the societies and individuals who have supported this project!', 'societypress' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render activation page (alias for support page).
	 *
	 * WHY: Maintains compatibility with existing code that calls this method.
	 */
	public function render_activation_page(): void {
		$this->render_license_page();
	}

	// =========================================================================
	// Stub methods for backward compatibility
	// These do nothing but prevent errors if called by other code.
	// =========================================================================

	/**
	 * Activate license (no-op).
	 *
	 * @param string $key   Ignored.
	 * @param string $email Ignored.
	 * @return array Success response.
	 */
	public function activate_license( string $key, string $email ): array {
		return array( 'success' => true, 'message' => __( 'No license required - all features are unlocked!', 'societypress' ) );
	}

	/**
	 * Deactivate license (no-op).
	 *
	 * @return bool Always true.
	 */
	public function deactivate_license(): bool {
		return true;
	}

	/**
	 * Check license (no-op).
	 */
	public function check_license(): void {
		// Nothing to check in shareware model
	}

	/**
	 * Render license notice (no-op).
	 *
	 * WHY: No nag notices in shareware model.
	 */
	public function render_license_notice(): void {
		// No notices - we don't nag users
	}

	/**
	 * Handle license actions (no-op).
	 */
	public function handle_license_actions(): void {
		// No license actions needed
	}
}
