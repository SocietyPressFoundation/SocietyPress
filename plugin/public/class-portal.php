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

		// Add Search to nav menu (priority 50 = before My Account at 99)
		add_filter( 'wp_nav_menu_items', array( $this, 'add_search_to_menu' ), 50, 2 );

		// Add dynamic menu items for logged-in members
		// Priority 99 ensures this runs after most other menu filters
		add_filter( 'wp_nav_menu_items', array( $this, 'add_member_menu_items' ), 99, 2 );
	}

	/**
	 * Add Search to nav menu before My Account.
	 *
	 * WHY: Keeps utility items (Search, Account) grouped on the right side
	 *      while main navigation stays together on the left.
	 *      Runs at priority 50, after nav items but before My Account (priority 99).
	 *
	 * @param string   $items Menu HTML.
	 * @param stdClass $args  Menu arguments.
	 * @return string Modified menu HTML.
	 */
	public function add_search_to_menu( string $items, $args ): string {
		// Only add to primary/main menu
		$target_menus = apply_filters( 'societypress_portal_menu_locations', array( 'primary', 'main', 'menu-1', 'primary-menu' ) );

		if ( ! isset( $args->theme_location ) || ! in_array( $args->theme_location, $target_menus, true ) ) {
			return $items;
		}

		// Build the search menu item with dropdown
		$search_item = '<li class="menu-item menu-item-has-children menu-item-sp-search">
			<a href="#" class="search-toggle" aria-label="' . esc_attr__( 'Toggle search', 'societypress' ) . '">' . esc_html__( 'Search', 'societypress' ) . '</a>
			<ul class="sub-menu search-dropdown">
				<li class="menu-item">' . get_search_form( array( 'echo' => false ) ) . '</li>
			</ul>
		</li>';

		// Append to end (will appear before My Account since that's added later at priority 99)
		return $items . $search_item;
	}

	/**
	 * Add dynamic menu items for logged-in members.
	 *
	 * WHY: Shows member-related links grouped together only when member is logged in.
	 *      Creates a dropdown containing: My Profile, Directory of Members, Log Out.
	 *      Keeps menu clean for visitors while providing easy access for members.
	 *
	 * @param string   $items Menu HTML.
	 * @param stdClass $args  Menu arguments.
	 * @return string Modified menu HTML.
	 */
	public function add_member_menu_items( string $items, $args ): string {
		// Only add to primary/main menu
		$target_menus = apply_filters( 'societypress_portal_menu_locations', array( 'primary', 'main', 'menu-1', 'primary-menu' ) );

		if ( ! isset( $args->theme_location ) || ! in_array( $args->theme_location, $target_menus, true ) ) {
			return $items;
		}

		// Only for logged-in users who are members
		if ( ! is_user_logged_in() ) {
			return $items;
		}

		$user_id = get_current_user_id();
		$member = societypress()->members->get_by_user_id( $user_id );

		if ( ! $member ) {
			return $items;
		}

		// Menu parent text - simple and clear for all audiences
		$parent_text = apply_filters( 'societypress_member_menu_parent_text', __( 'My Account', 'societypress' ), $member );

		// Build submenu items
		$submenu_items = '';

		// My Profile - only if portal page exists
		$portal_url = $this->get_portal_url();
		if ( $portal_url ) {
			$profile_text = apply_filters( 'societypress_portal_menu_text', __( 'My Profile', 'societypress' ) );
			$current_class = $this->is_portal_page() ? ' current-menu-item' : '';

			$submenu_items .= sprintf(
				'<li class="menu-item menu-item-sp-profile%s"><a href="%s">%s</a></li>',
				esc_attr( $current_class ),
				esc_url( $portal_url ),
				esc_html( $profile_text )
			);
		}

		// Log Out - always shown
		$logout_text = apply_filters( 'societypress_logout_menu_text', __( 'Log Out', 'societypress' ) );
		$logout_url = wp_logout_url( home_url() );

		$submenu_items .= sprintf(
			'<li class="menu-item menu-item-sp-logout"><a href="%s">%s</a></li>',
			esc_url( $logout_url ),
			esc_html( $logout_text )
		);

		// Build the parent menu item with submenu
		// Uses WordPress standard classes for dropdown styling
		$member_menu = sprintf(
			'<li class="menu-item menu-item-has-children menu-item-sp-member-area">
				<a href="#">%s</a>
				<ul class="sub-menu">%s</ul>
			</li>',
			esc_html( $parent_text ),
			$submenu_items
		);

		return $items . $member_menu;
	}

	/**
	 * Get directory page URL.
	 *
	 * WHY: Finds the member directory page for menu linking.
	 *
	 * @return string|null Directory URL or null if not found.
	 */
	private function get_directory_url(): ?string {
		// First check settings for configured directory page
		$directory_page_id = societypress_get_setting( 'directory_page_id', 0 );
		if ( $directory_page_id ) {
			$url = get_permalink( $directory_page_id );
			if ( $url ) {
				return $url;
			}
		}

		// Fall back to finding a page with the shortcode
		$page_id = $this->wpdb->get_var(
			"SELECT ID FROM {$this->wpdb->posts}
			 WHERE post_type = 'page'
			 AND post_status = 'publish'
			 AND post_content LIKE '%[societypress_directory%'
			 LIMIT 1"
		);

		if ( $page_id ) {
			return get_permalink( $page_id );
		}

		return null;
	}

	/**
	 * Check if current page is the directory page.
	 *
	 * @return bool
	 */
	private function is_directory_page(): bool {
		global $post;
		return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'societypress_directory' );
	}

	/**
	 * Check if current page is the portal page.
	 *
	 * @return bool
	 */
	private function is_portal_page(): bool {
		global $post;
		return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'societypress_portal' );
	}

	/**
	 * Get portal page URL.
	 *
	 * @return string|null Portal URL or null if not found.
	 */
	private function get_portal_url(): ?string {
		// First check settings for configured portal page
		$portal_page_id = societypress_get_setting( 'portal_page_id', 0 );
		if ( $portal_page_id ) {
			$url = get_permalink( $portal_page_id );
			if ( $url ) {
				return $url;
			}
		}

		// Fall back to finding a page with the shortcode
		$page_id = $this->wpdb->get_var(
			"SELECT ID FROM {$this->wpdb->posts}
			 WHERE post_type = 'page'
			 AND post_status = 'publish'
			 AND post_content LIKE '%[societypress_portal%'
			 LIMIT 1"
		);

		if ( $page_id ) {
			return get_permalink( $page_id );
		}

		return null;
	}

	/**
	 * Register shortcodes.
	 */
	public function register_shortcode(): void {
		add_shortcode( 'societypress_portal', array( $this, 'render_portal' ) );
		add_shortcode( 'societypress_contact', array( $this, 'render_contact' ) );
	}

	/**
	 * Render contact information shortcode.
	 *
	 * WHY: Displays organization contact info from settings.
	 *      Keeps contact info in one place, used everywhere.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_contact( $atts ): string {
		$atts = shortcode_atts(
			array(
				'show_name'       => 'yes',
				'show_address'    => 'yes',
				'show_phone'      => 'yes',
				'show_email'      => 'yes',
				'show_hours'      => 'yes',
				'show_holidays'   => 'yes',
				'show_directions' => 'yes',
				'show_parking'    => 'yes',
				'show_facilities' => 'yes',
				'show_social'     => 'yes',
				'show_form'       => 'yes',
			),
			$atts,
			'societypress_contact'
		);

		$settings = get_option( 'societypress_settings', array() );

		ob_start();
		?>
		<div class="sp-contact-info">
			<?php if ( 'yes' === $atts['show_name'] && ! empty( $settings['organization_name'] ) ) : ?>
				<h2 class="sp-contact-name"><?php echo esc_html( $settings['organization_name'] ); ?></h2>
			<?php endif; ?>

			<div class="sp-contact-details">
				<?php if ( 'yes' === $atts['show_address'] && ! empty( $settings['organization_address'] ) ) : ?>
					<div class="sp-contact-address">
						<h3><?php esc_html_e( 'Location', 'societypress' ); ?></h3>
						<address>
							<?php echo nl2br( esc_html( $settings['organization_address'] ) ); ?>
						</address>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $atts['show_phone'] && ! empty( $settings['organization_phone'] ) ) : ?>
					<div class="sp-contact-phone">
						<h3><?php esc_html_e( 'Phone', 'societypress' ); ?></h3>
						<p>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $settings['organization_phone'] ) ); ?>">
								<?php echo esc_html( $settings['organization_phone'] ); ?>
							</a>
						</p>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $atts['show_email'] && ! empty( $settings['organization_email'] ) ) : ?>
					<div class="sp-contact-email">
						<h3><?php esc_html_e( 'Email', 'societypress' ); ?></h3>
						<p>
							<a href="mailto:<?php echo esc_attr( $settings['organization_email'] ); ?>">
								<?php echo esc_html( $settings['organization_email'] ); ?>
							</a>
						</p>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $atts['show_hours'] && ! empty( $settings['organization_hours'] ) ) : ?>
					<div class="sp-contact-hours">
						<h3><?php esc_html_e( 'Hours', 'societypress' ); ?></h3>
						<p><?php echo nl2br( esc_html( $settings['organization_hours'] ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $atts['show_holidays'] && ! empty( $settings['organization_holidays'] ) ) : ?>
					<div class="sp-contact-holidays">
						<h3><?php esc_html_e( 'Holiday Closures', 'societypress' ); ?></h3>
						<p><?php echo nl2br( esc_html( $settings['organization_holidays'] ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $atts['show_facilities'] && ! empty( $settings['organization_facilities'] ) ) : ?>
					<div class="sp-contact-facilities">
						<h3><?php esc_html_e( 'Our Facilities', 'societypress' ); ?></h3>
						<p><?php echo nl2br( esc_html( $settings['organization_facilities'] ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $atts['show_directions'] && ! empty( $settings['organization_directions'] ) ) : ?>
					<div class="sp-contact-directions">
						<h3><?php esc_html_e( 'Directions', 'societypress' ); ?></h3>
						<p><?php echo nl2br( esc_html( $settings['organization_directions'] ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $atts['show_parking'] && ! empty( $settings['organization_parking'] ) ) : ?>
					<div class="sp-contact-parking">
						<h3><?php esc_html_e( 'Parking', 'societypress' ); ?></h3>
						<p><?php echo nl2br( esc_html( $settings['organization_parking'] ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php
				if ( 'yes' === $atts['show_social'] && ! empty( $settings['organization_social'] ) ) :
					$social = $settings['organization_social'];
					$social_labels = array(
						'facebook'  => 'Facebook',
						'twitter'   => 'X (Twitter)',
						'instagram' => 'Instagram',
						'youtube'   => 'YouTube',
						'linkedin'  => 'LinkedIn',
					);
					$has_social = false;
					foreach ( $social as $url ) {
						if ( ! empty( $url ) ) {
							$has_social = true;
							break;
						}
					}
					if ( $has_social ) :
					?>
					<div class="sp-contact-social">
						<h3><?php esc_html_e( 'Follow Us', 'societypress' ); ?></h3>
						<ul class="sp-social-links">
							<?php foreach ( $social as $platform => $url ) : ?>
								<?php if ( ! empty( $url ) ) : ?>
									<li>
										<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo esc_html( $social_labels[ $platform ] ?? ucfirst( $platform ) ); ?>
										</a>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php
					endif;
				endif;
				?>
			</div>

			<?php if ( 'yes' === $atts['show_form'] ) : ?>
				<div class="sp-contact-form">
					<h3><?php esc_html_e( 'Send Us a Message', 'societypress' ); ?></h3>
					<?php
					// Check for popular contact form plugins and display their shortcode
					if ( shortcode_exists( 'wpforms' ) ) {
						// WPForms - user needs to specify form ID in the shortcode or we show a message
						echo '<p class="sp-contact-form-notice">' . esc_html__( 'Add a WPForms contact form here by editing this page.', 'societypress' ) . '</p>';
					} elseif ( shortcode_exists( 'contact-form-7' ) ) {
						echo '<p class="sp-contact-form-notice">' . esc_html__( 'Add a Contact Form 7 form here by editing this page.', 'societypress' ) . '</p>';
					} else {
						// Simple built-in contact form
						echo $this->render_simple_contact_form( $settings );
					}
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render simple contact form.
	 *
	 * WHY: Provides a basic contact form when no form plugin is installed.
	 *
	 * @param array $settings Plugin settings.
	 * @return string HTML output.
	 */
	private function render_simple_contact_form( array $settings ): string {
		$to_email = $settings['organization_email'] ?? get_option( 'admin_email' );

		ob_start();
		?>
		<form class="sp-simple-contact-form" method="post" action="">
			<?php wp_nonce_field( 'sp_contact_form', 'sp_contact_nonce' ); ?>
			<input type="hidden" name="sp_contact_action" value="send">

			<p>
				<label for="sp-contact-name"><?php esc_html_e( 'Your Name', 'societypress' ); ?> <span class="required">*</span></label>
				<input type="text" id="sp-contact-name" name="sp_contact_name" required>
			</p>

			<p>
				<label for="sp-contact-email"><?php esc_html_e( 'Your Email', 'societypress' ); ?> <span class="required">*</span></label>
				<input type="email" id="sp-contact-email" name="sp_contact_email" required>
			</p>

			<p>
				<label for="sp-contact-subject"><?php esc_html_e( 'Subject', 'societypress' ); ?></label>
				<input type="text" id="sp-contact-subject" name="sp_contact_subject">
			</p>

			<p>
				<label for="sp-contact-message"><?php esc_html_e( 'Message', 'societypress' ); ?> <span class="required">*</span></label>
				<textarea id="sp-contact-message" name="sp_contact_message" rows="6" required></textarea>
			</p>

			<p>
				<button type="submit" class="sp-contact-submit">
					<?php esc_html_e( 'Send Message', 'societypress' ); ?>
				</button>
			</p>
		</form>
		<?php
		return ob_get_clean();
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
								<label for="sp-phone-cell"><?php esc_html_e( 'Phone', 'societypress' ); ?></label>
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
