<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * WHY: Dedicated contact page template with form, contact info, and map.
 * Emails are routed to different departments based on dropdown selection.
 * Email addresses are never displayed publicly (anti-spam).
 * All contact info is pulled from SocietyPress plugin settings.
 *
 * @package SocietyPress
 * @since 1.28d
 */

get_header();

// Helper function to get plugin settings
if ( ! function_exists( 'sp_get_setting' ) ) {
	/**
	 * Get a SocietyPress plugin setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed Setting value.
	 */
	function sp_get_setting( $key, $default = '' ) {
		$settings = get_option( 'societypress_settings', array() );
		return $settings[ $key ] ?? $default;
	}
}

// Build list of available departments from plugin settings
$dept_settings = sp_get_setting( 'contact_departments', array() );
$departments   = array();
$dept_index    = 1;
foreach ( $dept_settings as $dept ) {
	if ( ! empty( $dept['label'] ) && ! empty( $dept['email'] ) ) {
		$departments[ $dept_index ] = array(
			'label' => $dept['label'],
			'email' => $dept['email'],
		);
		$dept_index++;
	}
}

// Fallback to organization email or admin email if no departments configured
if ( empty( $departments ) ) {
	$fallback_email = sp_get_setting( 'organization_email', get_option( 'admin_email' ) );
	$departments[1] = array(
		'label' => __( 'General Inquiry', 'societypress' ),
		'email' => $fallback_email,
	);
}

// Handle form submission
$result = null;
if ( isset( $_POST['sp_contact_submit'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['sp_contact_nonce'] ) ||
		 ! wp_verify_nonce( $_POST['sp_contact_nonce'], 'sp_contact_form' ) ) {
		$result = array(
			'success' => false,
			'message' => __( 'Security check failed. Please try again.', 'societypress' ),
		);
	}
	// Honeypot check
	elseif ( ! empty( $_POST['website_url'] ) ) {
		// Fake success for bots
		$result = array(
			'success' => true,
			'message' => __( 'Thank you for your message. We will get back to you soon.', 'societypress' ),
		);
	}
	else {
		// Sanitize inputs
		$name       = sanitize_text_field( $_POST['contact_name'] ?? '' );
		$email      = sanitize_email( $_POST['contact_email'] ?? '' );
		$dept_id    = absint( $_POST['contact_department'] ?? 1 );
		$subject    = sanitize_text_field( $_POST['contact_subject'] ?? '' );
		$message    = sanitize_textarea_field( $_POST['contact_message'] ?? '' );

		// Validate
		if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
			$result = array(
				'success' => false,
				'message' => __( 'Please fill in all required fields.', 'societypress' ),
			);
		} elseif ( ! is_email( $email ) ) {
			$result = array(
				'success' => false,
				'message' => __( 'Please enter a valid email address.', 'societypress' ),
			);
		} else {
			// Determine recipient email based on department selection
			$to = isset( $departments[ $dept_id ] )
				? $departments[ $dept_id ]['email']
				: reset( $departments )['email'];

			$dept_label = isset( $departments[ $dept_id ] )
				? $departments[ $dept_id ]['label']
				: reset( $departments )['label'];

			// Build and send email
			$site_name  = get_bloginfo( 'name' );
			$email_subj = ! empty( $subject )
				? sprintf( '[%s] %s', $site_name, $subject )
				: sprintf( '[%s] %s from %s', $site_name, $dept_label, $name );

			$email_body = sprintf(
				"Department: %s\nName: %s\nEmail: %s\n\nMessage:\n%s\n\n---\nSent from %s contact form",
				$dept_label,
				$name,
				$email,
				$message,
				$site_name
			);

			$headers = array(
				'Content-Type: text/plain; charset=UTF-8',
				sprintf( 'Reply-To: %s <%s>', $name, $email ),
			);

			$sent = wp_mail( $to, $email_subj, $email_body, $headers );

			$result = $sent
				? array( 'success' => true, 'message' => __( 'Thank you for your message. We will get back to you soon.', 'societypress' ) )
				: array( 'success' => false, 'message' => __( 'There was a problem sending your message. Please try again later.', 'societypress' ) );
		}
	}
}

// Get contact info from plugin settings
$org_name    = sp_get_setting( 'organization_name', get_bloginfo( 'name' ) );
$address     = sp_get_setting( 'organization_address', '' );
$phone       = sp_get_setting( 'organization_phone', '' );
$hours       = sp_get_setting( 'organization_hours', '' );
$directions  = sp_get_setting( 'organization_directions', '' );
$parking     = sp_get_setting( 'organization_parking', '' );
$facilities  = sp_get_setting( 'organization_facilities', '' );
$holidays    = sp_get_setting( 'organization_holidays', '' );

// Check if map should be shown (show if address exists)
$show_map = ! empty( $address );

// Build Google Maps embed URL from address
$map_url = '';
if ( $show_map ) {
	// Convert address to a single line for the embed
	$address_line = preg_replace( '/\s+/', ' ', str_replace( array( "\r\n", "\r", "\n" ), ', ', $address ) );
	$map_url = 'https://www.google.com/maps?q=' . urlencode( $address_line ) . '&output=embed';
}
?>

<main id="primary" class="site-main">
	<div class="sp-container">
		<div class="content-area">

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>

				<div class="entry-content">
					<?php
					// Display any content added in the page editor
					while ( have_posts() ) :
						the_post();
						the_content();
					endwhile;
					?>

					<div class="sp-contact-wrapper">
						<!-- Contact Information -->
						<div class="sp-contact-info">
							<h2><?php esc_html_e( 'Contact Information', 'societypress' ); ?></h2>

							<?php if ( ! empty( $address ) ) : ?>
							<div class="sp-contact-item sp-contact-address">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
								<div>
									<strong><?php esc_html_e( 'Mailing Address', 'societypress' ); ?></strong>
									<address><?php echo nl2br( esc_html( $address ) ); ?></address>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $phone ) ) : ?>
							<div class="sp-contact-item sp-contact-phone">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
								<div>
									<strong><?php esc_html_e( 'Telephone', 'societypress' ); ?></strong>
									<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
										<?php echo esc_html( $phone ); ?>
									</a>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $hours ) ) : ?>
							<div class="sp-contact-item sp-contact-hours">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
								<div>
									<strong><?php esc_html_e( 'Hours', 'societypress' ); ?></strong>
									<span><?php echo nl2br( esc_html( $hours ) ); ?></span>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $holidays ) ) : ?>
							<div class="sp-contact-item sp-contact-holidays">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
								<div>
									<strong><?php esc_html_e( 'Holiday Closures', 'societypress' ); ?></strong>
									<span><?php echo nl2br( esc_html( $holidays ) ); ?></span>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $map_url ) ) : ?>
							<!-- Map and Directions -->
							<div class="sp-contact-map">
								<h3><?php esc_html_e( 'Map & Directions', 'societypress' ); ?></h3>
								<div class="sp-map-embed">
									<iframe
										src="<?php echo esc_url( $map_url ); ?>"
										width="100%"
										height="300"
										style="border:0;"
										allowfullscreen=""
										loading="lazy"
										referrerpolicy="no-referrer-when-downgrade">
									</iframe>
								</div>
								<p class="sp-directions-link">
									<a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode( preg_replace( '/\s+/', ' ', str_replace( array( "\r\n", "\r", "\n" ), ', ', $address ) ) ); ?>" target="_blank" rel="noopener noreferrer">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
										<?php esc_html_e( 'Get Directions', 'societypress' ); ?>
									</a>
								</p>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $directions ) ) : ?>
							<div class="sp-contact-item sp-contact-directions">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
								<div>
									<strong><?php esc_html_e( 'Directions', 'societypress' ); ?></strong>
									<span><?php echo nl2br( esc_html( $directions ) ); ?></span>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $parking ) ) : ?>
							<div class="sp-contact-item sp-contact-parking">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 17V7h4a3 3 0 0 1 0 6H9"/></svg>
								<div>
									<strong><?php esc_html_e( 'Parking', 'societypress' ); ?></strong>
									<span><?php echo nl2br( esc_html( $parking ) ); ?></span>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( ! empty( $facilities ) ) : ?>
							<div class="sp-contact-item sp-contact-facilities">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
								<div>
									<strong><?php esc_html_e( 'Facilities', 'societypress' ); ?></strong>
									<span><?php echo nl2br( esc_html( $facilities ) ); ?></span>
								</div>
							</div>
							<?php endif; ?>
						</div>

						<!-- Contact Form -->
						<div class="sp-contact-form-container">
							<h2><?php esc_html_e( 'Send Us a Message', 'societypress' ); ?></h2>

							<?php if ( $result ) : ?>
							<div class="sp-alert sp-alert--<?php echo $result['success'] ? 'success' : 'error'; ?>">
								<?php echo esc_html( $result['message'] ); ?>
							</div>
							<?php endif; ?>

							<?php if ( ! $result || ! $result['success'] ) : ?>
							<form method="post" class="sp-contact-form">
								<?php wp_nonce_field( 'sp_contact_form', 'sp_contact_nonce' ); ?>

								<!-- Honeypot -->
								<div class="sp-hp" aria-hidden="true">
									<input type="text" name="website_url" tabindex="-1" autocomplete="off">
								</div>

								<div class="sp-form-row">
									<div class="sp-form-field">
										<label for="contact_name">
											<?php esc_html_e( 'Name', 'societypress' ); ?>
											<span class="required">*</span>
										</label>
										<input type="text"
											   name="contact_name"
											   id="contact_name"
											   required
											   value="<?php echo esc_attr( $_POST['contact_name'] ?? '' ); ?>">
									</div>

									<div class="sp-form-field">
										<label for="contact_email">
											<?php esc_html_e( 'Email', 'societypress' ); ?>
											<span class="required">*</span>
										</label>
										<input type="email"
											   name="contact_email"
											   id="contact_email"
											   required
											   value="<?php echo esc_attr( $_POST['contact_email'] ?? '' ); ?>">
									</div>
								</div>

								<?php if ( count( $departments ) > 1 ) : ?>
								<div class="sp-form-field">
									<label for="contact_department">
										<?php esc_html_e( 'Inquiry Type', 'societypress' ); ?>
										<span class="required">*</span>
									</label>
									<select name="contact_department" id="contact_department" required>
										<?php foreach ( $departments as $id => $dept ) : ?>
										<option value="<?php echo esc_attr( $id ); ?>" <?php selected( ( $_POST['contact_department'] ?? '' ), $id ); ?>>
											<?php echo esc_html( $dept['label'] ); ?>
										</option>
										<?php endforeach; ?>
									</select>
								</div>
								<?php else : ?>
								<input type="hidden" name="contact_department" value="<?php echo esc_attr( key( $departments ) ); ?>">
								<?php endif; ?>

								<div class="sp-form-field">
									<label for="contact_subject">
										<?php esc_html_e( 'Subject', 'societypress' ); ?>
									</label>
									<input type="text"
										   name="contact_subject"
										   id="contact_subject"
										   value="<?php echo esc_attr( $_POST['contact_subject'] ?? '' ); ?>">
								</div>

								<div class="sp-form-field">
									<label for="contact_message">
										<?php esc_html_e( 'Message', 'societypress' ); ?>
										<span class="required">*</span>
									</label>
									<textarea name="contact_message"
											  id="contact_message"
											  rows="6"
											  required><?php echo esc_textarea( $_POST['contact_message'] ?? '' ); ?></textarea>
								</div>

								<div class="sp-form-submit">
									<button type="submit" name="sp_contact_submit" class="sp-button sp-button--primary">
										<?php esc_html_e( 'Send Message', 'societypress' ); ?>
									</button>
								</div>
							</form>
							<?php endif; ?>
						</div>
					</div>
				</div>

			</article>

		</div><!-- .content-area -->

		<?php get_sidebar(); ?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
