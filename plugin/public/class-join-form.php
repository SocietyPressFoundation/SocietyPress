<?php
/**
 * Public Join Form
 *
 * Provides membership signup form via [societypress_join] shortcode.
 * Creates member record, WordPress user account, and sends welcome email.
 *
 * WHY: Allows prospective members to sign up directly from the website
 *      without requiring admin intervention.
 *
 * @package SocietyPress
 * @since 0.27d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Join_Form
 *
 * Public-facing membership join form functionality.
 */
class SocietyPress_Join_Form {

	/**
	 * Form submission result.
	 *
	 * @var array|null
	 */
	private ?array $result = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		add_action( 'init', array( $this, 'process_form_submission' ) );
	}

	/**
	 * Register shortcode.
	 */
	public function register_shortcode(): void {
		add_shortcode( 'societypress_join', array( $this, 'render_form' ) );
	}

	/**
	 * Enqueue public assets.
	 *
	 * WHY: Only load join form CSS/JS on pages that use the shortcode.
	 *      Also loads PayPal SDK when payment is enabled.
	 */
	public function enqueue_public_assets(): void {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'societypress_join' ) ) {
			return;
		}

		wp_enqueue_style(
			'societypress-join',
			SOCIETYPRESS_URL . 'assets/css/join.css',
			array(),
			SOCIETYPRESS_VERSION
		);

		// Get payment settings
		$payment_mode = societypress_get_setting( 'payment_mode', 'disabled' );
		$paypal_mode = societypress_get_setting( 'paypal_mode', 'sandbox' );
		$paypal_client_id = societypress_get_setting( 'paypal_client_id', '' );

		// Load PayPal SDK if payment is enabled and client ID is configured
		$paypal_enabled = ( 'disabled' !== $payment_mode ) && ! empty( $paypal_client_id );
		if ( $paypal_enabled ) {
			// Get enabled payment methods from settings
			$payment_methods = societypress_get_setting( 'payment_methods', array( 'paypal', 'venmo', 'card' ) );
			if ( ! is_array( $payment_methods ) ) {
				$payment_methods = array( 'paypal' );
			}

			// Build enable-funding and disable-funding parameters
			// PayPal SDK uses specific funding source names
			$all_funding_sources = array(
				'venmo'    => 'venmo',
				'card'     => 'card',
				'paylater' => 'paylater',
			);

			$enable_funding = array();
			$disable_funding = array();

			foreach ( $all_funding_sources as $setting_key => $sdk_key ) {
				if ( in_array( $setting_key, $payment_methods, true ) ) {
					$enable_funding[] = $sdk_key;
				} else {
					$disable_funding[] = $sdk_key;
				}
			}

			// Build SDK URL parameters
			$sdk_params = array(
				'client-id' => $paypal_client_id,
				'currency'  => 'USD',
			);

			if ( ! empty( $enable_funding ) ) {
				$sdk_params['enable-funding'] = implode( ',', $enable_funding );
			}
			if ( ! empty( $disable_funding ) ) {
				$sdk_params['disable-funding'] = implode( ',', $disable_funding );
			}

			// PayPal SDK URL
			$paypal_sdk_url = add_query_arg( $sdk_params, 'https://www.paypal.com/sdk/js' );

			wp_enqueue_script(
				'paypal-sdk',
				$paypal_sdk_url,
				array(),
				null, // No version for external script
				true
			);
		}

		wp_enqueue_script(
			'societypress-join',
			SOCIETYPRESS_URL . 'assets/js/join.js',
			$paypal_enabled ? array( 'jquery', 'paypal-sdk' ) : array( 'jquery' ),
			SOCIETYPRESS_VERSION,
			true
		);

		// Build tier data with prices for JavaScript
		$tiers = societypress()->tiers->get_all( true ); // Only active tiers
		$student_tier_id = 0;
		$tier_prices = array();
		foreach ( $tiers as $tier ) {
			if ( 'student' === $tier->slug ) {
				$student_tier_id = $tier->id;
			}
			$tier_prices[ $tier->id ] = (float) $tier->price;
		}

		// Organization name for PayPal description
		$org_name = societypress_get_setting( 'organization_name', get_bloginfo( 'name' ) );

		// Get payment methods for JS (default if not in paypal_enabled block)
		$payment_methods_js = $paypal_enabled
			? ( isset( $payment_methods ) ? $payment_methods : array( 'paypal' ) )
			: array();

		wp_localize_script(
			'societypress-join',
			'societypressJoin',
			array(
				'studentTierId'    => $student_tier_id,
				'paymentMode'      => $payment_mode,
				'paypalEnabled'    => $paypal_enabled,
				'paypalMode'       => $paypal_mode,
				'paymentMethods'   => $payment_methods_js,
				'tierPrices'       => $tier_prices,
				'organizationName' => $org_name,
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'societypress_payment' ),
				'strings'          => array(
					'paymentRequired'  => __( 'Please complete payment to submit your application.', 'societypress' ),
					'paymentSuccess'   => __( 'Payment successful! Submitting your application...', 'societypress' ),
					'paymentError'     => __( 'Payment could not be processed. Please try again.', 'societypress' ),
					'paymentCancelled' => __( 'Payment was cancelled. You can try again or choose to pay later.', 'societypress' ),
					'processing'       => __( 'Processing...', 'societypress' ),
				),
			)
		);
	}

	/**
	 * Process form submission.
	 *
	 * WHY: Runs early on init to process before rendering, allowing redirects.
	 */
	public function process_form_submission(): void {
		// Only process if our form was submitted
		if ( ! isset( $_POST['societypress_join_nonce'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['societypress_join_nonce'], 'societypress_join' ) ) {
			$this->result = array(
				'success' => false,
				'message' => __( 'Security check failed. Please try again.', 'societypress' ),
			);
			return;
		}

		// Validate required fields
		$required = array( 'first_name', 'last_name', 'email', 'membership_tier_id' );
		foreach ( $required as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				$this->result = array(
					'success' => false,
					'message' => __( 'Please fill in all required fields.', 'societypress' ),
				);
				return;
			}
		}

		// Validate email
		$email = sanitize_email( $_POST['email'] );
		if ( ! is_email( $email ) ) {
			$this->result = array(
				'success' => false,
				'message' => __( 'Please enter a valid email address.', 'societypress' ),
			);
			return;
		}

		// Check if email already exists as a member
		$existing = societypress()->members->get_by_email( $email );
		if ( $existing ) {
			$this->result = array(
				'success' => false,
				'message' => __( 'A member with this email address already exists. Please contact us if you need assistance.', 'societypress' ),
			);
			return;
		}

		// Validate tier exists and is active
		$tier_id = absint( $_POST['membership_tier_id'] );
		$tier = societypress()->tiers->get( $tier_id );
		if ( ! $tier || ! $tier->is_active ) {
			$this->result = array(
				'success' => false,
				'message' => __( 'Please select a valid membership tier.', 'societypress' ),
			);
			return;
		}

		// Check payment settings and status
		$payment_mode = societypress_get_setting( 'payment_mode', 'disabled' );
		$paypal_client_id = societypress_get_setting( 'paypal_client_id', '' );
		$payment_enabled = ( 'disabled' !== $payment_mode ) && ! empty( $paypal_client_id );

		// Determine member status based on payment
		$member_status = 'pending'; // Default status
		$paypal_order_id = sanitize_text_field( $_POST['paypal_order_id'] ?? '' );
		$payment_status = sanitize_text_field( $_POST['payment_status'] ?? '' );
		$pay_later = ! empty( $_POST['pay_later'] );

		// If tier is free, no payment needed
		$tier_is_free = ( (float) $tier->price <= 0 );

		if ( $payment_enabled && ! $tier_is_free ) {
			// Payment is configured and tier has a price
			if ( 'required' === $payment_mode ) {
				// Payment is required - must have completed payment
				if ( 'completed' !== $payment_status || empty( $paypal_order_id ) ) {
					$this->result = array(
						'success' => false,
						'message' => __( 'Payment is required to complete your membership application.', 'societypress' ),
					);
					return;
				}
				// Payment completed - set status to active
				$member_status = 'active';
			} elseif ( 'optional' === $payment_mode ) {
				// Payment is optional
				if ( 'completed' === $payment_status && ! empty( $paypal_order_id ) ) {
					// They paid - activate immediately
					$member_status = 'active';
				} elseif ( $pay_later ) {
					// They chose to pay later - stay pending
					$member_status = 'pending';
				} else {
					// Neither paid nor chose pay later - require a choice
					$this->result = array(
						'success' => false,
						'message' => __( 'Please complete payment or select "Pay later" to continue.', 'societypress' ),
					);
					return;
				}
			}
		} elseif ( $tier_is_free ) {
			// Free tier - could auto-approve or keep pending based on admin preference
			// For now, keep pending for admin review
			$member_status = 'pending';
		}

		// Prepare member data
		$member_data = array(
			'first_name'         => sanitize_text_field( $_POST['first_name'] ),
			'middle_name'        => sanitize_text_field( $_POST['middle_name'] ?? '' ) ?: null,
			'last_name'          => sanitize_text_field( $_POST['last_name'] ),
			'membership_tier_id' => $tier_id,
			'status'             => $member_status,
			'join_date'          => current_time( 'Y-m-d' ),
			'directory_visible'  => 1, // Default to visible
		);

		// Calculate expiration date based on tier
		if ( $tier->duration_months > 0 ) {
			$expiration = date( 'Y-m-d', strtotime( '+' . $tier->duration_months . ' months' ) );

			// If using calendar year model, set to Dec 31
			$expiration_model = societypress_get_setting( 'expiration_model', 'calendar_year' );
			if ( 'calendar_year' === $expiration_model ) {
				$expiration = date( 'Y' ) . '-12-31';
			}

			$member_data['expiration_date'] = $expiration;
		}

		// Create the member
		$members = societypress()->members;
		$member_id = $members->create( $member_data );

		if ( ! $member_id ) {
			$this->result = array(
				'success' => false,
				'message' => __( 'An error occurred while creating your membership. Please try again.', 'societypress' ),
			);
			return;
		}

		// Save contact information
		$contact_data = array(
			'primary_email'   => $email,
			'cell_phone'      => $this->sanitize_phone( $_POST['phone'] ?? '' ),
			'street_address'  => sanitize_text_field( $_POST['street_address'] ?? '' ),
			'address_line_2'  => sanitize_text_field( $_POST['address_line_2'] ?? '' ),
			'city'            => sanitize_text_field( $_POST['city'] ?? '' ),
			'state_province'  => sanitize_text_field( $_POST['state_province'] ?? '' ),
			'postal_code'     => sanitize_text_field( $_POST['postal_code'] ?? '' ),
			'country'         => sanitize_text_field( $_POST['country'] ?? 'USA' ),
		);

		$members->save_contact( $member_id, $contact_data );

		// Save student school info as member meta if provided
		if ( ! empty( $_POST['school_name'] ) ) {
			$members->save_meta( $member_id, 'school_name', sanitize_text_field( $_POST['school_name'] ) );
		}

		// Save PayPal payment info if payment was made
		if ( ! empty( $paypal_order_id ) && 'completed' === $payment_status ) {
			$members->save_meta( $member_id, 'paypal_order_id', $paypal_order_id );
			$members->save_meta( $member_id, 'payment_date', current_time( 'Y-m-d H:i:s' ) );
			$members->save_meta( $member_id, 'payment_amount', (float) $tier->price );
		}

		// Create WordPress user account
		$user_manager = societypress()->user_manager;
		$user_result = $user_manager->create_or_link_user(
			$member_id,
			$email,
			$member_data['first_name'],
			$member_data['last_name']
		);

		// Send welcome email
		$this->send_welcome_email( $member_id, $email, $member_data['first_name'], $tier );

		// Success! Customize message based on status
		if ( 'active' === $member_status ) {
			$success_message = sprintf(
				/* translators: %s: member first name */
				__( 'Welcome, %s! Your payment has been received and your membership is now active. You will receive an email confirmation shortly.', 'societypress' ),
				esc_html( $member_data['first_name'] )
			);
		} else {
			$success_message = sprintf(
				/* translators: %s: member first name */
				__( 'Welcome, %s! Your membership application has been received. You will receive an email confirmation shortly.', 'societypress' ),
				esc_html( $member_data['first_name'] )
			);
		}

		$this->result = array(
			'success'   => true,
			'message'   => $success_message,
			'member_id' => $member_id,
		);
	}

	/**
	 * Send welcome email to new member.
	 *
	 * @param int    $member_id  Member ID.
	 * @param string $email      Email address.
	 * @param string $first_name First name.
	 * @param object $tier       Tier object.
	 */
	private function send_welcome_email( int $member_id, string $email, string $first_name, object $tier ): void {
		$org_name = societypress_get_setting( 'organization_name', get_bloginfo( 'name' ) );

		$subject = sprintf(
			/* translators: %s: organization name */
			__( 'Welcome to %s!', 'societypress' ),
			$org_name
		);

		$message = sprintf(
			/* translators: 1: first name, 2: organization name, 3: tier name, 4: site URL */
			__(
				"Hi %1\$s,\n\n" .
				"Thank you for joining %2\$s!\n\n" .
				"Your %3\$s membership application has been received and is pending review.\n\n" .
				"Once approved, you'll have full access to member benefits.\n\n" .
				"If you have any questions, please don't hesitate to contact us.\n\n" .
				"Best regards,\n%2\$s\n%4\$s",
				'societypress'
			),
			$first_name,
			$org_name,
			$tier->name,
			home_url()
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		$from_name = societypress_get_setting( 'email_from_name', $org_name );
		$from_email = societypress_get_setting( 'email_from_email', get_option( 'admin_email' ) );
		$headers[] = sprintf( 'From: %s <%s>', $from_name, $from_email );

		wp_mail( $email, $subject, $message, $headers );

		// Also notify admin
		$admin_email = societypress_get_setting( 'admin_email', get_option( 'admin_email' ) );
		$admin_subject = sprintf(
			/* translators: %s: member name */
			__( 'New Membership Application: %s', 'societypress' ),
			$first_name . ' ' . sanitize_text_field( $_POST['last_name'] ?? '' )
		);

		$admin_message = sprintf(
			/* translators: 1: member name, 2: tier name, 3: email, 4: admin URL */
			__(
				"A new membership application has been received.\n\n" .
				"Name: %1\$s\n" .
				"Tier: %2\$s\n" .
				"Email: %3\$s\n\n" .
				"Review and approve: %4\$s",
				'societypress'
			),
			$first_name . ' ' . sanitize_text_field( $_POST['last_name'] ?? '' ),
			$tier->name,
			$email,
			admin_url( 'admin.php?page=societypress-members&action=edit&member=' . $member_id )
		);

		wp_mail( $admin_email, $admin_subject, $admin_message, $headers );
	}

	/**
	 * Sanitize phone number.
	 *
	 * @param string $phone Phone number.
	 * @return string Sanitized phone.
	 */
	private function sanitize_phone( string $phone ): string {
		// Remove all non-digits
		return preg_replace( '/\D/', '', $phone );
	}

	/**
	 * Render the join form.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Form HTML.
	 */
	public function render_form( array $atts = array() ): string {
		// Get organization name for placeholder replacement
		$org_name = societypress_get_setting( 'organization_name', get_bloginfo( 'name' ) );

		$atts = shortcode_atts(
			array(
				'title' => sprintf(
					/* translators: %s: organization name */
					__( 'Join the %s', 'societypress' ),
					$org_name
				),
			),
			$atts,
			'societypress_join'
		);

		// Replace {{organization_name}} placeholder if used in title attribute
		$atts['title'] = str_replace( '{{organization_name}}', $org_name, $atts['title'] );

		// Get active tiers
		$tiers = societypress()->tiers->get_all( true );

		// Find student tier ID for conditional field
		$student_tier_id = 0;
		foreach ( $tiers as $tier ) {
			if ( 'student' === $tier->slug ) {
				$student_tier_id = $tier->id;
				break;
			}
		}

		ob_start();
		?>
		<div class="sp-join-form-wrapper">
			<?php if ( ! empty( $atts['title'] ) ) : ?>
				<h2 class="sp-join-title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( $this->result ) : ?>
				<div class="sp-join-message <?php echo $this->result['success'] ? 'sp-success' : 'sp-error'; ?>">
					<?php echo esc_html( $this->result['message'] ); ?>
				</div>
				<?php if ( $this->result['success'] ) : ?>
					<?php return ob_get_clean(); // Don't show form after successful submission ?>
				<?php endif; ?>
			<?php endif; ?>

			<form method="post" class="sp-join-form" id="sp-join-form">
				<?php wp_nonce_field( 'societypress_join', 'societypress_join_nonce' ); ?>

				<!-- Membership Tier Selection -->
				<fieldset class="sp-fieldset sp-tier-selection">
					<legend><?php esc_html_e( 'Select Membership Level', 'societypress' ); ?></legend>

					<div class="sp-tier-options">
						<?php foreach ( $tiers as $tier ) : ?>
							<label class="sp-tier-option" data-tier-id="<?php echo esc_attr( $tier->id ); ?>">
								<input type="radio" name="membership_tier_id" value="<?php echo esc_attr( $tier->id ); ?>"
									<?php checked( isset( $_POST['membership_tier_id'] ) && absint( $_POST['membership_tier_id'] ) === (int) $tier->id ); ?>
									required>
								<span class="sp-tier-card">
									<span class="sp-tier-name"><?php echo esc_html( $tier->name ); ?></span>
									<span class="sp-tier-price">
										<?php if ( $tier->price > 0 ) : ?>
											$<?php echo esc_html( number_format( $tier->price, 2 ) ); ?>
											<?php if ( $tier->duration_months > 0 ) : ?>
												<span class="sp-tier-duration">/ <?php echo esc_html( $tier->duration_months ); ?> <?php esc_html_e( 'months', 'societypress' ); ?></span>
											<?php endif; ?>
										<?php else : ?>
											<?php esc_html_e( 'Free', 'societypress' ); ?>
										<?php endif; ?>
									</span>
									<?php if ( ! empty( $tier->description ) ) : ?>
										<span class="sp-tier-description"><?php echo esc_html( $tier->description ); ?></span>
									<?php endif; ?>
									<?php if ( 'student' === $tier->slug ) : ?>
										<span class="sp-tier-note"><?php esc_html_e( 'Verification required', 'societypress' ); ?></span>
									<?php endif; ?>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<!-- Student Verification (conditional) -->
				<fieldset class="sp-fieldset sp-student-fields" id="sp-student-fields" style="display: none;" data-student-tier="<?php echo esc_attr( $student_tier_id ); ?>">
					<legend><?php esc_html_e( 'Student Verification', 'societypress' ); ?></legend>

					<div class="sp-form-row">
						<label for="sp-school-name"><?php esc_html_e( 'School / University Name', 'societypress' ); ?> *</label>
						<input type="text" id="sp-school-name" name="school_name"
							value="<?php echo esc_attr( $_POST['school_name'] ?? '' ); ?>"
							placeholder="<?php esc_attr_e( 'Enter your school or university', 'societypress' ); ?>">
						<p class="sp-field-note"><?php esc_html_e( 'Your student status will be verified before your membership is activated.', 'societypress' ); ?></p>
					</div>
				</fieldset>

				<!-- Personal Information -->
				<fieldset class="sp-fieldset">
					<legend><?php esc_html_e( 'Personal Information', 'societypress' ); ?></legend>

					<div class="sp-form-row sp-form-row-thirds">
						<div class="sp-form-field">
							<label for="sp-first-name"><?php esc_html_e( 'First Name', 'societypress' ); ?> *</label>
							<input type="text" id="sp-first-name" name="first_name" required
								value="<?php echo esc_attr( $_POST['first_name'] ?? '' ); ?>">
						</div>
						<div class="sp-form-field">
							<label for="sp-middle-name"><?php esc_html_e( 'Middle Name', 'societypress' ); ?></label>
							<input type="text" id="sp-middle-name" name="middle_name"
								value="<?php echo esc_attr( $_POST['middle_name'] ?? '' ); ?>">
						</div>
						<div class="sp-form-field">
							<label for="sp-last-name"><?php esc_html_e( 'Last Name', 'societypress' ); ?> *</label>
							<input type="text" id="sp-last-name" name="last_name" required
								value="<?php echo esc_attr( $_POST['last_name'] ?? '' ); ?>">
						</div>
					</div>

					<div class="sp-form-row sp-form-row-halves">
						<div class="sp-form-field">
							<label for="sp-email"><?php esc_html_e( 'Email Address', 'societypress' ); ?> *</label>
							<input type="email" id="sp-email" name="email" required
								value="<?php echo esc_attr( $_POST['email'] ?? '' ); ?>">
						</div>
						<div class="sp-form-field">
							<label for="sp-phone"><?php esc_html_e( 'Phone', 'societypress' ); ?></label>
							<input type="tel" id="sp-phone" name="phone"
								value="<?php echo esc_attr( $_POST['phone'] ?? '' ); ?>"
								placeholder="(555) 555-5555">
						</div>
					</div>
				</fieldset>

				<!-- Address -->
				<fieldset class="sp-fieldset">
					<legend><?php esc_html_e( 'Mailing Address', 'societypress' ); ?></legend>

					<div class="sp-form-row">
						<label for="sp-street-address"><?php esc_html_e( 'Street Address', 'societypress' ); ?></label>
						<input type="text" id="sp-street-address" name="street_address"
							value="<?php echo esc_attr( $_POST['street_address'] ?? '' ); ?>">
					</div>

					<div class="sp-form-row">
						<label for="sp-address-line-2"><?php esc_html_e( 'Address Line 2', 'societypress' ); ?></label>
						<input type="text" id="sp-address-line-2" name="address_line_2"
							value="<?php echo esc_attr( $_POST['address_line_2'] ?? '' ); ?>"
							placeholder="<?php esc_attr_e( 'Apartment, suite, unit, etc.', 'societypress' ); ?>">
					</div>

					<div class="sp-form-row sp-form-row-city-state">
						<div class="sp-form-field sp-field-city">
							<label for="sp-city"><?php esc_html_e( 'City', 'societypress' ); ?></label>
							<input type="text" id="sp-city" name="city"
								value="<?php echo esc_attr( $_POST['city'] ?? '' ); ?>">
						</div>
						<div class="sp-form-field sp-field-state">
							<label for="sp-state"><?php esc_html_e( 'State / Province', 'societypress' ); ?></label>
							<input type="text" id="sp-state" name="state_province"
								value="<?php echo esc_attr( $_POST['state_province'] ?? '' ); ?>">
						</div>
						<div class="sp-form-field sp-field-postal">
							<label for="sp-postal"><?php esc_html_e( 'ZIP / Postal Code', 'societypress' ); ?></label>
							<input type="text" id="sp-postal" name="postal_code"
								value="<?php echo esc_attr( $_POST['postal_code'] ?? '' ); ?>">
						</div>
					</div>

					<div class="sp-form-row">
						<label for="sp-country"><?php esc_html_e( 'Country', 'societypress' ); ?></label>
						<select id="sp-country" name="country">
							<option value="USA" <?php selected( $_POST['country'] ?? 'USA', 'USA' ); ?>><?php esc_html_e( 'United States', 'societypress' ); ?></option>
							<option value="CAN" <?php selected( $_POST['country'] ?? '', 'CAN' ); ?>><?php esc_html_e( 'Canada', 'societypress' ); ?></option>
							<option value="MEX" <?php selected( $_POST['country'] ?? '', 'MEX' ); ?>><?php esc_html_e( 'Mexico', 'societypress' ); ?></option>
							<option value="GBR" <?php selected( $_POST['country'] ?? '', 'GBR' ); ?>><?php esc_html_e( 'United Kingdom', 'societypress' ); ?></option>
							<option value="DEU" <?php selected( $_POST['country'] ?? '', 'DEU' ); ?>><?php esc_html_e( 'Germany', 'societypress' ); ?></option>
							<option value="OTHER" <?php selected( $_POST['country'] ?? '', 'OTHER' ); ?>><?php esc_html_e( 'Other', 'societypress' ); ?></option>
						</select>
					</div>
				</fieldset>

				<?php
				// Get payment settings to determine which buttons to show
				$payment_mode = societypress_get_setting( 'payment_mode', 'disabled' );
				$paypal_client_id = societypress_get_setting( 'paypal_client_id', '' );
				$payment_enabled = ( 'disabled' !== $payment_mode ) && ! empty( $paypal_client_id );
			?>

				<?php if ( $payment_enabled ) : ?>
				<?php
				// Get enabled payment methods for icons
				$enabled_methods = societypress_get_setting( 'payment_methods', array( 'paypal', 'venmo', 'card' ) );
				if ( ! is_array( $enabled_methods ) ) {
					$enabled_methods = array( 'paypal' );
				}
				?>
				<!-- Payment Section -->
				<fieldset class="sp-fieldset sp-payment-section" id="sp-payment-section">
					<legend><?php esc_html_e( 'Payment', 'societypress' ); ?></legend>

					<!-- Accepted Payment Methods -->
					<div class="sp-payment-methods-accepted">
						<span class="sp-payment-methods-label"><?php esc_html_e( 'We accept:', 'societypress' ); ?></span>
						<span class="sp-payment-icons">
							<?php if ( in_array( 'paypal', $enabled_methods, true ) ) : ?>
								<span class="sp-payment-icon sp-icon-paypal" title="PayPal">
									<svg viewBox="0 0 24 24" width="32" height="20"><path fill="#003087" d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.774.774 0 0 1 .763-.642h6.163c2.046 0 3.532.458 4.417 1.363.832.853 1.108 2.013.821 3.447l-.011.059v.506l.394.222c.334.176.6.38.803.613.288.333.478.736.565 1.199.09.477.08 1.045-.029 1.69-.126.749-.352 1.4-.676 1.936-.296.49-.678.9-1.138 1.219a4.633 4.633 0 0 1-1.483.67 7.346 7.346 0 0 1-1.767.193H12.57a.946.946 0 0 0-.934.799l-.029.148-.483 3.058-.022.108a.946.946 0 0 1-.933.799H7.076z"/><path fill="#0070E0" d="M18.854 7.066l-.011.059c-.897 4.596-3.966 6.185-7.886 6.185h-1.997a.97.97 0 0 0-.958.819l-1.021 6.476-.29 1.836a.51.51 0 0 0 .504.591h3.538a.852.852 0 0 0 .841-.718l.035-.179.667-4.229.043-.233a.852.852 0 0 1 .841-.718h.53c3.432 0 6.118-1.394 6.904-5.427.328-1.684.159-3.089-.708-4.077a3.387 3.387 0 0 0-.972-.765l-.06.38z"/></svg>
								</span>
							<?php endif; ?>
							<?php if ( in_array( 'venmo', $enabled_methods, true ) ) : ?>
								<span class="sp-payment-icon sp-icon-venmo" title="Venmo">
									<svg viewBox="0 0 24 24" width="32" height="20"><path fill="#3D95CE" d="M19.5 2.25c.69 1.14 1 2.31 1 3.79 0 4.72-4.03 10.86-7.31 15.17H6.81L4.5 3.33l5.55-.52 1.31 10.54c1.22-1.99 2.73-5.12 2.73-7.26 0-1.4-.24-2.35-.61-3.12l6.02-.72z"/></svg>
								</span>
							<?php endif; ?>
							<?php if ( in_array( 'card', $enabled_methods, true ) ) : ?>
								<span class="sp-payment-icon sp-icon-visa" title="Visa">
									<svg viewBox="0 0 24 24" width="32" height="20"><rect fill="#1A1F71" width="24" height="16" rx="2" y="4"/><path fill="#fff" d="M9.5 14.5H8l1-5h1.5l-1 5zm4.5 0H12l1-5h2l-1 5zm-6-5L6.5 14.5H5L4 11l-.5-1.5H5l.5 1.5L7 9.5h1zm9 0l-1.5 5h-1.5l1.5-5h1.5zm2.5 0l1.5 5h-1.5l-1.5-5h1.5z"/></svg>
								</span>
								<span class="sp-payment-icon sp-icon-mastercard" title="Mastercard">
									<svg viewBox="0 0 24 24" width="32" height="20"><rect fill="#000" width="24" height="16" rx="2" y="4"/><circle fill="#EB001B" cx="9" cy="12" r="5"/><circle fill="#F79E1B" cx="15" cy="12" r="5"/><path fill="#FF5F00" d="M12 8.5a5 5 0 0 0 0 7 5 5 0 0 0 0-7z"/></svg>
								</span>
							<?php endif; ?>
							<?php if ( in_array( 'paylater', $enabled_methods, true ) ) : ?>
								<span class="sp-payment-icon sp-icon-paylater" title="<?php esc_attr_e( 'Pay Later', 'societypress' ); ?>">
									<svg viewBox="0 0 24 24" width="32" height="20"><rect fill="#003087" width="24" height="16" rx="2" y="4"/><text x="12" y="14" fill="#fff" font-size="6" text-anchor="middle" font-weight="bold">PAY LATER</text></svg>
								</span>
							<?php endif; ?>
						</span>
					</div>

					<div class="sp-payment-summary" id="sp-payment-summary">
						<div class="sp-payment-tier-name" id="sp-payment-tier-name"></div>
						<div class="sp-payment-amount" id="sp-payment-amount"></div>
					</div>

					<div class="sp-payment-free-notice" id="sp-payment-free-notice" style="display: none;">
						<p><?php esc_html_e( 'This membership tier is free. No payment required.', 'societypress' ); ?></p>
					</div>

					<div class="sp-paypal-container" id="sp-paypal-container">
						<!-- PayPal button will be rendered here -->
					</div>

					<?php if ( 'optional' === $payment_mode ) : ?>
					<div class="sp-pay-later-option" id="sp-pay-later-option">
						<p class="sp-pay-later-or"><?php esc_html_e( '— or —', 'societypress' ); ?></p>
						<label class="sp-pay-later-label">
							<input type="checkbox" name="pay_later" id="sp-pay-later" value="1">
							<?php esc_html_e( 'Pay later (your membership will be pending until payment is received)', 'societypress' ); ?>
						</label>
					</div>
					<?php endif; ?>

					<!-- Hidden field to store PayPal order ID after successful payment -->
					<input type="hidden" name="paypal_order_id" id="sp-paypal-order-id" value="">
					<input type="hidden" name="payment_status" id="sp-payment-status" value="">
				</fieldset>
				<?php endif; ?>

				<!-- Submit -->
				<div class="sp-form-submit">
					<button type="submit" class="sp-submit-button" id="sp-submit-button"
						<?php if ( $payment_enabled ) : ?>data-payment-mode="<?php echo esc_attr( $payment_mode ); ?>"<?php endif; ?>>
						<?php esc_html_e( 'Submit Application', 'societypress' ); ?>
					</button>
					<p class="sp-submit-note">
						<?php if ( $payment_enabled && 'required' === $payment_mode ) : ?>
							<?php esc_html_e( 'Complete payment above, then submit your application.', 'societypress' ); ?>
						<?php elseif ( $payment_enabled && 'optional' === $payment_mode ) : ?>
							<?php esc_html_e( 'Pay now to activate immediately, or choose to pay later.', 'societypress' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'Your membership will be pending until reviewed by an administrator.', 'societypress' ); ?>
						<?php endif; ?>
					</p>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}
