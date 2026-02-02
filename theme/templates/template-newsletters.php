<?php
/**
 * Template Name: Newsletters
 * Template Post Type: page
 *
 * WHY: Displays newsletter archive for members. Non-members see the list
 *      but cannot download — encourages membership sign-up.
 *
 * Newsletters are stored in: wp-content/newsletters/
 * Naming convention: YYYY_MM_Month_Newsletter.pdf
 *
 * Cover images are auto-generated from the PDF first page using PDF.js.
 * No manual cover uploads required.
 *
 * Admins see an upload form at the top of the page to add new newsletters
 * without needing FTP access.
 *
 * @package SocietyPress
 * @since 1.31d
 */

/**
 * Handle newsletter upload.
 *
 * WHY: Process the upload form before any output so we can redirect cleanly.
 */
function sp_handle_newsletter_upload(): ?string {
	// Only process if form was submitted
	if ( ! isset( $_POST['sp_newsletter_upload_nonce'] ) ) {
		return null;
	}

	// Verify nonce
	if ( ! wp_verify_nonce( $_POST['sp_newsletter_upload_nonce'], 'sp_newsletter_upload' ) ) {
		return __( 'Security check failed. Please try again.', 'societypress' );
	}

	// Check permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		return __( 'You do not have permission to upload newsletters.', 'societypress' );
	}

	// Check if file was uploaded
	if ( empty( $_FILES['newsletter_pdf'] ) || $_FILES['newsletter_pdf']['error'] !== UPLOAD_ERR_OK ) {
		$error_messages = array(
			UPLOAD_ERR_INI_SIZE   => __( 'File is too large (server limit).', 'societypress' ),
			UPLOAD_ERR_FORM_SIZE  => __( 'File is too large.', 'societypress' ),
			UPLOAD_ERR_PARTIAL    => __( 'File was only partially uploaded.', 'societypress' ),
			UPLOAD_ERR_NO_FILE    => __( 'No file was selected.', 'societypress' ),
			UPLOAD_ERR_NO_TMP_DIR => __( 'Server configuration error (no temp directory).', 'societypress' ),
			UPLOAD_ERR_CANT_WRITE => __( 'Failed to write file to disk.', 'societypress' ),
		);
		$error_code = $_FILES['newsletter_pdf']['error'] ?? UPLOAD_ERR_NO_FILE;
		return $error_messages[ $error_code ] ?? __( 'File upload failed.', 'societypress' );
	}

	// Validate file type
	$file_type = wp_check_filetype( $_FILES['newsletter_pdf']['name'], array( 'pdf' => 'application/pdf' ) );
	if ( $file_type['ext'] !== 'pdf' ) {
		return __( 'Only PDF files are allowed.', 'societypress' );
	}

	// Get form data
	$year        = isset( $_POST['newsletter_year'] ) ? absint( $_POST['newsletter_year'] ) : 0;
	$month_start = isset( $_POST['newsletter_month_start'] ) ? absint( $_POST['newsletter_month_start'] ) : 0;
	$month_end   = isset( $_POST['newsletter_month_end'] ) ? absint( $_POST['newsletter_month_end'] ) : 0;

	// Validate year and month
	if ( $year < 1900 || $year > 2100 ) {
		return __( 'Please select a valid year.', 'societypress' );
	}
	if ( $month_start < 1 || $month_start > 12 ) {
		return __( 'Please select a valid starting month.', 'societypress' );
	}

	// If no end month selected, use start month (single month issue)
	if ( $month_end < 1 || $month_end > 12 ) {
		$month_end = $month_start;
	}

	// Ensure end month is not before start month
	if ( $month_end < $month_start ) {
		$month_end = $month_start;
	}

	// Build filename
	$month_names = array(
		1  => 'January',
		2  => 'February',
		3  => 'March',
		4  => 'April',
		5  => 'May',
		6  => 'June',
		7  => 'July',
		8  => 'August',
		9  => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December',
	);

	if ( $month_start === $month_end ) {
		// Single month: 2025_02_February_Newsletter.pdf
		$filename = sprintf(
			'%d_%02d_%s_Newsletter.pdf',
			$year,
			$month_start,
			$month_names[ $month_start ]
		);
	} else {
		// Combined issue: 2025_07-08_Newsletter.pdf
		$filename = sprintf(
			'%d_%02d-%02d_Newsletter.pdf',
			$year,
			$month_start,
			$month_end
		);
	}

	// Ensure newsletters directory exists
	$newsletters_dir = WP_CONTENT_DIR . '/newsletters';
	if ( ! is_dir( $newsletters_dir ) ) {
		if ( ! wp_mkdir_p( $newsletters_dir ) ) {
			return __( 'Could not create newsletters directory.', 'societypress' );
		}
	}

	// Check if file already exists
	$destination = $newsletters_dir . '/' . $filename;
	$overwrite_confirmed = isset( $_POST['confirm_overwrite'] ) && $_POST['confirm_overwrite'] === '1';

	if ( file_exists( $destination ) && ! $overwrite_confirmed ) {
		// Store upload info in transient so we can show confirmation form
		$upload_key = 'sp_newsletter_pending_' . get_current_user_id();
		set_transient( $upload_key, array(
			'tmp_file'    => $_FILES['newsletter_pdf']['tmp_name'],
			'filename'    => $filename,
			'title'       => $month_start === $month_end
				? $month_names[ $month_start ] . ' ' . $year
				: $month_names[ $month_start ] . '–' . $month_names[ $month_end ] . ' ' . $year,
			'year'        => $year,
			'month_start' => $month_start,
			'month_end'   => $month_end,
		), 5 * MINUTE_IN_SECONDS );

		// Move temp file to a safe location before PHP cleans it up
		$pending_dir = $newsletters_dir . '/.pending';
		if ( ! is_dir( $pending_dir ) ) {
			wp_mkdir_p( $pending_dir );
		}
		$pending_file = $pending_dir . '/' . $filename;
		move_uploaded_file( $_FILES['newsletter_pdf']['tmp_name'], $pending_file );

		// Return special marker so template can show confirmation
		return 'CONFIRM_OVERWRITE';
	}

	// If overwriting, check for pending file first
	if ( $overwrite_confirmed ) {
		$pending_file = $newsletters_dir . '/.pending/' . $filename;
		if ( file_exists( $pending_file ) ) {
			// Remove old file and move pending file to final location
			if ( file_exists( $destination ) ) {
				unlink( $destination );
			}
			if ( ! rename( $pending_file, $destination ) ) {
				return __( 'Failed to save the newsletter file.', 'societypress' );
			}
		} else {
			return __( 'Upload session expired. Please try again.', 'societypress' );
		}
		// Clear the transient since we're done with it
		delete_transient( 'sp_newsletter_pending_' . get_current_user_id() );
	} else {
		// New file, just move it
		if ( ! move_uploaded_file( $_FILES['newsletter_pdf']['tmp_name'], $destination ) ) {
			return __( 'Failed to save the newsletter file.', 'societypress' );
		}
	}

	// Success - redirect to avoid form resubmission
	wp_safe_redirect( add_query_arg( 'uploaded', '1', get_permalink() ) );
	exit;
}

/**
 * Clean up orphaned pending newsletter uploads.
 *
 * WHY: If a user uploads a file but never confirms/cancels, the pending file
 *      could sit there forever. This cleans up files older than 10 minutes.
 */
function sp_cleanup_pending_newsletters(): void {
	$pending_dir = WP_CONTENT_DIR . '/newsletters/.pending';

	if ( ! is_dir( $pending_dir ) ) {
		return;
	}

	$files = glob( $pending_dir . '/*.pdf' );

	if ( empty( $files ) ) {
		return;
	}

	$max_age = 10 * MINUTE_IN_SECONDS;
	$now     = time();

	foreach ( $files as $file ) {
		if ( $now - filemtime( $file ) > $max_age ) {
			unlink( $file );
		}
	}
}

/**
 * Handle newsletter deletion.
 *
 * WHY: Allow admins to remove newsletters directly from the page.
 */
function sp_handle_newsletter_delete(): ?string {
	// Only process if delete was requested
	if ( ! isset( $_GET['delete_newsletter'] ) || ! isset( $_GET['_wpnonce'] ) ) {
		return null;
	}

	// Verify nonce
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_newsletter' ) ) {
		return __( 'Security check failed. Please try again.', 'societypress' );
	}

	// Check permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		return __( 'You do not have permission to delete newsletters.', 'societypress' );
	}

	// Sanitize filename - only allow expected characters
	$filename = sanitize_file_name( $_GET['delete_newsletter'] );

	// Validate it looks like a newsletter filename
	if ( ! preg_match( '/^\d{4}_\d{2}(?:-\d{2})?.*\.pdf$/', $filename ) ) {
		return __( 'Invalid newsletter filename.', 'societypress' );
	}

	$file_path = WP_CONTENT_DIR . '/newsletters/' . $filename;

	// Check file exists and is within newsletters directory (prevent directory traversal)
	$real_path = realpath( $file_path );
	$newsletters_dir = realpath( WP_CONTENT_DIR . '/newsletters' );

	if ( ! $real_path || strpos( $real_path, $newsletters_dir ) !== 0 ) {
		return __( 'Newsletter not found.', 'societypress' );
	}

	// Delete the file
	if ( ! unlink( $real_path ) ) {
		return __( 'Could not delete the newsletter file.', 'societypress' );
	}

	// Success - redirect to avoid issues with refresh
	wp_safe_redirect( add_query_arg( 'deleted', '1', remove_query_arg( array( 'delete_newsletter', '_wpnonce' ) ) ) );
	exit;
}

// Handle cancel of pending upload
if ( isset( $_GET['cancel_upload'] ) && is_user_logged_in() ) {
	$pending_upload = get_transient( 'sp_newsletter_pending_' . get_current_user_id() );
	if ( $pending_upload ) {
		// Delete the pending file
		$pending_file = WP_CONTENT_DIR . '/newsletters/.pending/' . $pending_upload['filename'];
		if ( file_exists( $pending_file ) ) {
			unlink( $pending_file );
		}
		// Delete the transient
		delete_transient( 'sp_newsletter_pending_' . get_current_user_id() );
	}
	// Redirect to clean URL
	wp_safe_redirect( get_permalink() );
	exit;
}

// Process uploads and deletions before any output
$upload_error = sp_handle_newsletter_upload();
$delete_error = sp_handle_newsletter_delete();

// Clean up any orphaned pending uploads
sp_cleanup_pending_newsletters();

get_header();

/**
 * Check if current user can access newsletters.
 *
 * WHY: Members with sp_access_member_portal capability can download.
 *      Also allow admins and editors.
 *
 * @return bool True if user can download newsletters.
 */
function sp_can_access_newsletters(): bool {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	// Allow members, admins, and editors
	return current_user_can( 'sp_access_member_portal' ) || current_user_can( 'edit_posts' );
}

/**
 * Get newsletters from the newsletters directory.
 *
 * WHY: Scans directory, parses filenames for metadata, returns sorted array.
 *
 * @return array Array of newsletter data, newest first.
 */
function sp_get_newsletters(): array {
	$newsletters_dir = WP_CONTENT_DIR . '/newsletters';
	$newsletters_url = content_url( '/newsletters' );

	if ( ! is_dir( $newsletters_dir ) ) {
		return array();
	}

	$files = glob( $newsletters_dir . '/*.pdf' );

	if ( empty( $files ) ) {
		return array();
	}

	$newsletters = array();

	foreach ( $files as $file ) {
		$filename  = basename( $file );
		$parsed    = sp_parse_newsletter_filename( $filename );

		if ( ! $parsed ) {
			continue;
		}

		$newsletters[] = array(
			'filename'    => $filename,
			'url'         => $newsletters_url . '/' . $filename,
			'year'        => $parsed['year'],
			'month_start' => $parsed['month_start'],
			'month_end'   => $parsed['month_end'],
			'title'       => $parsed['title'],
			'sort_key'    => $parsed['sort_key'],
		);
	}

	// Sort by sort_key descending (newest first)
	usort( $newsletters, function( $a, $b ) {
		return strcmp( $b['sort_key'], $a['sort_key'] );
	});

	return $newsletters;
}

/**
 * Parse newsletter filename for metadata.
 *
 * WHY: Extracts year, month(s), and generates display title from filename.
 *
 * Expected formats:
 *   2025_02_February_Newsletter.pdf
 *   2025_07-08_Newsletter_Final.pdf
 *   2025_11-12_Nov_Dec_Newsletter.pdf
 *
 * @param string $filename The PDF filename.
 * @return array|null Parsed data or null if not parseable.
 */
function sp_parse_newsletter_filename( string $filename ): ?array {
	// Pattern: YYYY_MM or YYYY_MM-MM at the start
	if ( ! preg_match( '/^(\d{4})_(\d{2})(?:-(\d{2}))?/', $filename, $matches ) ) {
		return null;
	}

	$year        = $matches[1];
	$month_start = $matches[2];
	$month_end   = isset( $matches[3] ) ? $matches[3] : $month_start;

	// Month names for display
	$month_names = array(
		'01' => 'January',
		'02' => 'February',
		'03' => 'March',
		'04' => 'April',
		'05' => 'May',
		'06' => 'June',
		'07' => 'July',
		'08' => 'August',
		'09' => 'September',
		'10' => 'October',
		'11' => 'November',
		'12' => 'December',
	);

	// Generate title
	if ( $month_start === $month_end ) {
		$title = $month_names[ $month_start ] . ' ' . $year;
	} else {
		$title = $month_names[ $month_start ] . '–' . $month_names[ $month_end ] . ' ' . $year;
	}

	// Sort key: YYYYMM (use end month for combined issues)
	$sort_key = $year . $month_end;

	return array(
		'year'        => $year,
		'month_start' => $month_start,
		'month_end'   => $month_end,
		'title'       => $title,
		'sort_key'    => $sort_key,
	);
}

$can_access  = sp_can_access_newsletters();
$newsletters = sp_get_newsletters();
$is_admin    = current_user_can( 'manage_options' );
?>

<main id="primary" class="site-main full-width">
	<div class="sp-container">

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>

				<div class="entry-content">
					<?php
					// Output any content from the page editor first
					the_content();

					// Show admin upload form
					if ( $is_admin ) :
						// Check for pending overwrite confirmation
						$pending_upload = get_transient( 'sp_newsletter_pending_' . get_current_user_id() );

						// Display success/error messages
						if ( isset( $_GET['uploaded'] ) ) : ?>
							<div class="sp-newsletter-message sp-newsletter-success">
								<?php esc_html_e( 'Newsletter uploaded successfully!', 'societypress' ); ?>
							</div>
						<?php elseif ( isset( $_GET['deleted'] ) ) : ?>
							<div class="sp-newsletter-message sp-newsletter-success">
								<?php esc_html_e( 'Newsletter deleted.', 'societypress' ); ?>
							</div>
						<?php endif;

						if ( $upload_error && $upload_error !== 'CONFIRM_OVERWRITE' ) : ?>
							<div class="sp-newsletter-message sp-newsletter-error">
								<?php echo esc_html( $upload_error ); ?>
							</div>
						<?php endif;

						// Show overwrite confirmation if needed
						if ( $upload_error === 'CONFIRM_OVERWRITE' && $pending_upload ) : ?>
							<div class="sp-newsletter-message sp-newsletter-warning">
								<form method="post">
									<?php wp_nonce_field( 'sp_newsletter_upload', 'sp_newsletter_upload_nonce' ); ?>
									<input type="hidden" name="newsletter_year" value="<?php echo esc_attr( $pending_upload['year'] ); ?>">
									<input type="hidden" name="newsletter_month_start" value="<?php echo esc_attr( $pending_upload['month_start'] ); ?>">
									<input type="hidden" name="newsletter_month_end" value="<?php echo esc_attr( $pending_upload['month_end'] ); ?>">
									<input type="hidden" name="confirm_overwrite" value="1">

									<p>
										<strong>
											<?php
											printf(
												/* translators: %s: newsletter title like "March 2026" */
												esc_html__( 'Overwrite the current %s newsletter?', 'societypress' ),
												esc_html( $pending_upload['title'] )
											);
											?>
										</strong>
									</p>
									<p><?php esc_html_e( 'This will replace the existing file. This cannot be undone.', 'societypress' ); ?></p>
									<p>
										<button type="submit" class="button button-primary">
											<?php esc_html_e( 'Yes, Replace It', 'societypress' ); ?>
										</button>
										<a href="<?php echo esc_url( add_query_arg( 'cancel_upload', '1', get_permalink() ) ); ?>" class="button">
											<?php esc_html_e( 'Cancel', 'societypress' ); ?>
										</a>
									</p>
								</form>
							</div>
						<?php endif;

						if ( $delete_error ) : ?>
							<div class="sp-newsletter-message sp-newsletter-error">
								<?php echo esc_html( $delete_error ); ?>
							</div>
						<?php endif; ?>

						<div class="sp-newsletter-upload-form">
							<h3><?php esc_html_e( 'Upload New Newsletter', 'societypress' ); ?></h3>
							<form method="post" enctype="multipart/form-data">
								<?php wp_nonce_field( 'sp_newsletter_upload', 'sp_newsletter_upload_nonce' ); ?>

								<div class="sp-newsletter-upload-fields">
									<div class="sp-newsletter-field">
										<label for="newsletter_year"><?php esc_html_e( 'Year', 'societypress' ); ?></label>
										<select name="newsletter_year" id="newsletter_year" required>
											<?php
											$current_year = (int) gmdate( 'Y' );
											for ( $y = $current_year + 1; $y >= $current_year - 10; $y-- ) :
												?>
												<option value="<?php echo esc_attr( $y ); ?>" <?php selected( $y, $current_year ); ?>>
													<?php echo esc_html( $y ); ?>
												</option>
											<?php endfor; ?>
										</select>
									</div>

									<div class="sp-newsletter-field">
										<label for="newsletter_month_start"><?php esc_html_e( 'Month', 'societypress' ); ?></label>
										<select name="newsletter_month_start" id="newsletter_month_start" required>
											<?php
											$months = array(
												1  => __( 'January', 'societypress' ),
												2  => __( 'February', 'societypress' ),
												3  => __( 'March', 'societypress' ),
												4  => __( 'April', 'societypress' ),
												5  => __( 'May', 'societypress' ),
												6  => __( 'June', 'societypress' ),
												7  => __( 'July', 'societypress' ),
												8  => __( 'August', 'societypress' ),
												9  => __( 'September', 'societypress' ),
												10 => __( 'October', 'societypress' ),
												11 => __( 'November', 'societypress' ),
												12 => __( 'December', 'societypress' ),
											);
											$current_month = (int) gmdate( 'n' );
											foreach ( $months as $num => $name ) :
												?>
												<option value="<?php echo esc_attr( $num ); ?>" <?php selected( $num, $current_month ); ?>>
													<?php echo esc_html( $name ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>

									<div class="sp-newsletter-field">
										<label for="newsletter_month_end"><?php esc_html_e( 'Through (optional)', 'societypress' ); ?></label>
										<select name="newsletter_month_end" id="newsletter_month_end">
											<option value=""><?php esc_html_e( '— Single month —', 'societypress' ); ?></option>
											<?php foreach ( $months as $num => $name ) : ?>
												<option value="<?php echo esc_attr( $num ); ?>">
													<?php echo esc_html( $name ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>

									<div class="sp-newsletter-field sp-newsletter-field-file">
										<label for="newsletter_pdf"><?php esc_html_e( 'PDF File', 'societypress' ); ?></label>
										<input type="file" name="newsletter_pdf" id="newsletter_pdf" accept=".pdf,application/pdf" required>
									</div>

									<div class="sp-newsletter-field sp-newsletter-field-submit">
										<button type="submit" class="button button-primary">
											<?php esc_html_e( 'Upload Newsletter', 'societypress' ); ?>
										</button>
									</div>
								</div>
							</form>
						</div>
					<?php endif; ?>

					<?php if ( ! $can_access ) : ?>
						<div class="sp-newsletters-cta">
							<p>
								<strong><?php esc_html_e( 'Members have access to our complete newsletter archive.', 'societypress' ); ?></strong>
							</p>
							<p>
								<?php
								printf(
									/* translators: 1: login URL, 2: membership/join URL */
									wp_kses(
										__( '<a href="%1$s">Log in</a> to access newsletters, or <a href="%2$s">become a member</a> to join our society and gain access to this and other member benefits.', 'societypress' ),
										array( 'a' => array( 'href' => array() ) )
									),
									esc_url( wp_login_url( get_permalink() ) ),
									esc_url( home_url( '/join' ) )
								);
								?>
							</p>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $newsletters ) ) : ?>
						<div class="sp-newsletters-grid">
							<?php foreach ( $newsletters as $index => $newsletter ) : ?>
								<div class="sp-newsletter-card">
									<?php if ( $can_access ) : ?>
										<a href="#" class="sp-newsletter-link" data-pdf-view="<?php echo esc_url( $newsletter['url'] ); ?>" data-pdf-title="<?php echo esc_attr( $newsletter['title'] ); ?>">
									<?php endif; ?>

										<div class="sp-newsletter-cover" id="cover-<?php echo esc_attr( $index ); ?>">
											<canvas data-pdf-url="<?php echo esc_url( $newsletter['url'] ); ?>"></canvas>
											<div class="sp-newsletter-loading">
												<span class="dashicons dashicons-pdf"></span>
											</div>
										</div>

										<div class="sp-newsletter-title">
											<?php echo esc_html( $newsletter['title'] ); ?>
										</div>

									<?php if ( $can_access ) : ?>
										</a>
									<?php endif; ?>

									<?php if ( $is_admin ) : ?>
										<div class="sp-newsletter-admin-actions">
											<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'delete_newsletter', $newsletter['filename'] ), 'delete_newsletter' ) ); ?>"
											   class="sp-newsletter-delete"
											   onclick="return confirm('<?php esc_attr_e( 'Delete this newsletter? This cannot be undone.', 'societypress' ); ?>');">
												<?php esc_html_e( 'Delete', 'societypress' ); ?>
											</a>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p><?php esc_html_e( 'No newsletters available yet.', 'societypress' ); ?></p>
					<?php endif; ?>

				</div><!-- .entry-content -->

			</article>

			<?php
		endwhile;
		?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<style>
/**
 * Newsletter Grid Styles
 *
 * WHY: Inline styles to keep template self-contained.
 */
.sp-newsletters-cta {
	background: #f7f7f7;
	border-left: 4px solid #0073aa;
	padding: 1.5rem;
	margin-bottom: 2rem;
}

.sp-newsletters-cta p {
	margin: 0 0 0.5rem 0;
}

.sp-newsletters-cta p:last-child {
	margin-bottom: 0;
}

.sp-newsletters-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
	gap: 1.5rem;
	margin-top: 2rem;
}

.sp-newsletter-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 4px;
	overflow: hidden;
	transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.sp-newsletter-card:hover {
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.sp-newsletter-link {
	display: block;
	text-decoration: none;
	color: inherit;
}

.sp-newsletter-link:hover {
	text-decoration: none;
}

.sp-newsletter-link:hover .sp-newsletter-title {
	color: #0073aa;
}

.sp-newsletter-cover {
	aspect-ratio: 8.5 / 11;
	overflow: hidden;
	background: #f5f5f5;
	position: relative;
}

.sp-newsletter-cover canvas {
	width: 100%;
	height: 100%;
	object-fit: contain;
	display: none;
}

.sp-newsletter-cover canvas.loaded {
	display: block;
}

.sp-newsletter-loading {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
}

.sp-newsletter-loading .dashicons {
	font-size: 64px;
	width: 64px;
	height: 64px;
}

.sp-newsletter-cover canvas.loaded + .sp-newsletter-loading {
	display: none;
}

.sp-newsletter-title {
	padding: 1rem;
	font-weight: 600;
	text-align: center;
	background: #fafafa;
	border-top: 1px solid #eee;
	transition: color 0.2s ease;
}

/* Cursor styling for non-members */
.sp-newsletter-card:not(:has(.sp-newsletter-link)) {
	cursor: default;
}

.sp-newsletter-card:not(:has(.sp-newsletter-link)):hover {
	transform: none;
	box-shadow: none;
}

/* Mobile adjustments */
@media (max-width: 480px) {
	.sp-newsletters-grid {
		grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
		gap: 1rem;
	}

	.sp-newsletter-title {
		padding: 0.75rem;
		font-size: 0.9rem;
	}

	.sp-newsletter-loading .dashicons {
		font-size: 48px;
		width: 48px;
		height: 48px;
	}
}

/* ==========================================================================
   Admin Upload Form
   ========================================================================== */

.sp-newsletter-upload-form {
	background: #f0f6fc;
	border: 1px solid #c3c4c7;
	border-left: 4px solid #2271b1;
	padding: 1.5rem;
	margin-bottom: 2rem;
	border-radius: 0 4px 4px 0;
}

.sp-newsletter-upload-form h3 {
	margin: 0 0 1rem 0;
	font-size: 1.1rem;
	color: #1d2327;
}

.sp-newsletter-upload-fields {
	display: flex;
	flex-wrap: wrap;
	gap: 1rem;
	align-items: flex-end;
}

.sp-newsletter-field {
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
}

.sp-newsletter-field label {
	font-size: 0.85rem;
	font-weight: 600;
	color: #50575e;
}

.sp-newsletter-field select,
.sp-newsletter-field input[type="file"] {
	padding: 0.5rem;
	border: 1px solid #8c8f94;
	border-radius: 4px;
	font-size: 1rem;
	min-height: 40px;
}

.sp-newsletter-field select:focus,
.sp-newsletter-field input[type="file"]:focus {
	border-color: #2271b1;
	box-shadow: 0 0 0 1px #2271b1;
	outline: none;
}

.sp-newsletter-field-file {
	flex: 1;
	min-width: 200px;
}

.sp-newsletter-field-submit {
	justify-content: flex-end;
}

.sp-newsletter-upload-form .button-primary {
	background: #2271b1;
	border-color: #2271b1;
	color: #fff;
	padding: 0.5rem 1.5rem;
	font-size: 1rem;
	min-height: 40px;
	cursor: pointer;
	border-radius: 4px;
}

.sp-newsletter-upload-form .button-primary:hover {
	background: #135e96;
	border-color: #135e96;
}

/* Success/Error Messages */
.sp-newsletter-message {
	padding: 1rem 1.5rem;
	margin-bottom: 1.5rem;
	border-radius: 4px;
	font-weight: 500;
}

.sp-newsletter-success {
	background: #edfaef;
	border: 1px solid #46b450;
	color: #1e4620;
}

.sp-newsletter-error {
	background: #fcf0f1;
	border: 1px solid #d63638;
	color: #8a1c1f;
}

.sp-newsletter-warning {
	background: #fef8ee;
	border: 1px solid #dba617;
	color: #6e4e00;
}

.sp-newsletter-warning p {
	margin: 0 0 0.75rem 0;
}

.sp-newsletter-warning p:last-child {
	margin-bottom: 0;
}

.sp-newsletter-warning .button {
	margin-right: 0.5rem;
}

/* Admin Delete Button */
.sp-newsletter-admin-actions {
	padding: 0.5rem;
	text-align: center;
	border-top: 1px solid #eee;
	background: #fff;
}

.sp-newsletter-delete {
	color: #b32d2e;
	text-decoration: none;
	font-size: 0.85rem;
}

.sp-newsletter-delete:hover {
	color: #8a1c1f;
	text-decoration: underline;
}

/* Mobile adjustments for upload form */
@media (max-width: 600px) {
	.sp-newsletter-upload-fields {
		flex-direction: column;
	}

	.sp-newsletter-field {
		width: 100%;
	}

	.sp-newsletter-field select,
	.sp-newsletter-field input[type="file"] {
		width: 100%;
	}
}
</style>

<!-- PDF.js library from CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
/**
 * Render PDF first pages as cover thumbnails.
 *
 * WHY: Automatically generates cover images from PDFs without requiring
 *      manual image uploads. Uses PDF.js to render page 1 to canvas.
 */
document.addEventListener('DOMContentLoaded', function() {
	// Set worker source for PDF.js
	pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

	// Find all canvases with PDF URLs
	const canvases = document.querySelectorAll('.sp-newsletter-cover canvas[data-pdf-url]');

	canvases.forEach(function(canvas) {
		const pdfUrl = canvas.dataset.pdfUrl;

		// Load PDF and render first page
		pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
			return pdf.getPage(1);
		}).then(function(page) {
			// Calculate scale to fit the canvas container
			const container = canvas.parentElement;
			const containerWidth = container.offsetWidth;
			const containerHeight = container.offsetHeight;

			const viewport = page.getViewport({ scale: 1 });
			const scaleX = containerWidth / viewport.width;
			const scaleY = containerHeight / viewport.height;
			const scale = Math.min(scaleX, scaleY) * 2; // 2x for retina sharpness

			const scaledViewport = page.getViewport({ scale: scale });

			canvas.width = scaledViewport.width;
			canvas.height = scaledViewport.height;

			const context = canvas.getContext('2d');

			page.render({
				canvasContext: context,
				viewport: scaledViewport
			}).promise.then(function() {
				canvas.classList.add('loaded');
			});
		}).catch(function(error) {
			// On error, leave the placeholder visible
			console.warn('Could not load PDF thumbnail:', pdfUrl, error);
		});
	});
});
</script>

<!-- PDF Viewer Modal -->
<?php if ( $can_access ) : ?>
<div id="sp-pdf-modal" class="sp-pdf-modal" style="display: none;">
	<div class="sp-pdf-modal-backdrop"></div>
	<div class="sp-pdf-modal-container">
		<div class="sp-pdf-modal-header">
			<h3 class="sp-pdf-modal-title"></h3>
			<div class="sp-pdf-modal-controls">
				<button type="button" class="sp-pdf-zoom-out" title="<?php esc_attr_e( 'Zoom Out', 'societypress' ); ?>">−</button>
				<span class="sp-pdf-zoom-level">100%</span>
				<button type="button" class="sp-pdf-zoom-in" title="<?php esc_attr_e( 'Zoom In', 'societypress' ); ?>">+</button>
				<span class="sp-pdf-separator">|</span>
				<button type="button" class="sp-pdf-prev" title="<?php esc_attr_e( 'Previous Page', 'societypress' ); ?>">‹</button>
				<span class="sp-pdf-page-info">
					<input type="number" class="sp-pdf-page-input" min="1" value="1"> / <span class="sp-pdf-page-total">1</span>
				</span>
				<button type="button" class="sp-pdf-next" title="<?php esc_attr_e( 'Next Page', 'societypress' ); ?>">›</button>
				<span class="sp-pdf-separator">|</span>
				<a href="#" class="sp-pdf-download" download title="<?php esc_attr_e( 'Download', 'societypress' ); ?>">↓ <?php esc_html_e( 'Download', 'societypress' ); ?></a>
				<button type="button" class="sp-pdf-close" title="<?php esc_attr_e( 'Close', 'societypress' ); ?>">✕</button>
			</div>
		</div>
		<div class="sp-pdf-modal-body">
			<canvas id="sp-pdf-canvas"></canvas>
			<div class="sp-pdf-loading"><?php esc_html_e( 'Loading...', 'societypress' ); ?></div>
		</div>
	</div>
</div>

<style>
/* PDF Viewer Modal */
.sp-pdf-modal {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 100000;
	display: flex;
	align-items: center;
	justify-content: center;
}

.sp-pdf-modal-backdrop {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.85);
}

.sp-pdf-modal-container {
	position: relative;
	background: #fff;
	border-radius: 8px;
	width: 95vw;
	height: 95vh;
	max-width: 1200px;
	display: flex;
	flex-direction: column;
	box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
}

.sp-pdf-modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 0.75rem 1rem;
	border-bottom: 1px solid #ddd;
	background: #f5f5f5;
	border-radius: 8px 8px 0 0;
	flex-wrap: wrap;
	gap: 0.5rem;
}

.sp-pdf-modal-title {
	margin: 0;
	font-size: 1.1rem;
	font-weight: 600;
	color: #1d2327;
}

.sp-pdf-modal-controls {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	flex-wrap: wrap;
}

.sp-pdf-modal-controls button,
.sp-pdf-modal-controls a {
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	padding: 0.4rem 0.75rem;
	font-size: 0.9rem;
	cursor: pointer;
	color: #1d2327;
	text-decoration: none;
	display: inline-flex;
	align-items: center;
	gap: 0.25rem;
}

.sp-pdf-modal-controls button:hover,
.sp-pdf-modal-controls a:hover {
	background: #f0f0f0;
	border-color: #8c8f94;
}

.sp-pdf-modal-controls button:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.sp-pdf-close {
	background: #d63638 !important;
	border-color: #d63638 !important;
	color: #fff !important;
}

.sp-pdf-close:hover {
	background: #b32d2e !important;
	border-color: #b32d2e !important;
}

.sp-pdf-separator {
	color: #c3c4c7;
	margin: 0 0.25rem;
}

.sp-pdf-zoom-level {
	font-size: 0.85rem;
	min-width: 45px;
	text-align: center;
}

.sp-pdf-page-info {
	display: flex;
	align-items: center;
	gap: 0.25rem;
	font-size: 0.9rem;
}

.sp-pdf-page-input {
	width: 50px;
	padding: 0.25rem;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	text-align: center;
	font-size: 0.9rem;
}

.sp-pdf-modal-body {
	flex: 1;
	overflow: auto;
	display: flex;
	justify-content: center;
	align-items: flex-start;
	padding: 1rem;
	background: #525659;
	border-radius: 0 0 8px 8px;
	position: relative;
}

#sp-pdf-canvas {
	max-width: 100%;
	box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
	background: #fff;
}

.sp-pdf-loading {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	color: #fff;
	font-size: 1.2rem;
	display: none;
}

.sp-pdf-modal.loading .sp-pdf-loading {
	display: block;
}

.sp-pdf-modal.loading #sp-pdf-canvas {
	opacity: 0.3;
}

/* Mobile adjustments for modal */
@media (max-width: 768px) {
	.sp-pdf-modal-container {
		width: 100vw;
		height: 100vh;
		max-width: none;
		border-radius: 0;
	}

	.sp-pdf-modal-header {
		border-radius: 0;
		padding: 0.5rem;
	}

	.sp-pdf-modal-title {
		width: 100%;
		font-size: 1rem;
		margin-bottom: 0.5rem;
	}

	.sp-pdf-modal-controls {
		width: 100%;
		justify-content: center;
	}

	.sp-pdf-modal-body {
		border-radius: 0;
		padding: 0.5rem;
	}

	.sp-pdf-download span {
		display: none;
	}
}
</style>

<script>
/**
 * PDF Viewer Modal
 *
 * WHY: Allows members to read newsletters inline without downloading.
 *      Uses PDF.js for cross-browser rendering with zoom and navigation.
 */
(function() {
	var modal = document.getElementById('sp-pdf-modal');
	if (!modal) return;

	var canvas = document.getElementById('sp-pdf-canvas');
	var ctx = canvas.getContext('2d');

	var titleEl = modal.querySelector('.sp-pdf-modal-title');
	var downloadEl = modal.querySelector('.sp-pdf-download');
	var pageInput = modal.querySelector('.sp-pdf-page-input');
	var pageTotalEl = modal.querySelector('.sp-pdf-page-total');
	var zoomLevelEl = modal.querySelector('.sp-pdf-zoom-level');

	var prevBtn = modal.querySelector('.sp-pdf-prev');
	var nextBtn = modal.querySelector('.sp-pdf-next');
	var zoomInBtn = modal.querySelector('.sp-pdf-zoom-in');
	var zoomOutBtn = modal.querySelector('.sp-pdf-zoom-out');
	var closeBtn = modal.querySelector('.sp-pdf-close');
	var backdrop = modal.querySelector('.sp-pdf-modal-backdrop');

	var currentPdf = null;
	var currentPage = 1;
	var totalPages = 1;
	var currentScale = 1.5;
	var baseScale = 1.5;

	// Open modal when clicking a newsletter link
	document.querySelectorAll('.sp-newsletter-link[data-pdf-view]').forEach(function(link) {
		link.addEventListener('click', function(e) {
			e.preventDefault();
			var pdfUrl = this.dataset.pdfView;
			var pdfTitle = this.dataset.pdfTitle;
			openPdfViewer(pdfUrl, pdfTitle);
		});
	});

	function openPdfViewer(url, title) {
		modal.style.display = 'flex';
		modal.classList.add('loading');
		document.body.style.overflow = 'hidden';

		titleEl.textContent = title;
		downloadEl.href = url;

		currentPage = 1;
		currentScale = baseScale;
		updateZoomDisplay();

		pdfjsLib.getDocument(url).promise.then(function(pdf) {
			currentPdf = pdf;
			totalPages = pdf.numPages;
			pageTotalEl.textContent = totalPages;
			pageInput.max = totalPages;
			pageInput.value = 1;
			renderPage(1);
		}).catch(function(error) {
			console.error('Error loading PDF:', error);
			alert('<?php echo esc_js( __( 'Could not load the PDF. Please try downloading it instead.', 'societypress' ) ); ?>');
			closeModal();
		});
	}

	function renderPage(pageNum) {
		if (!currentPdf) return;

		modal.classList.add('loading');
		currentPage = pageNum;
		pageInput.value = pageNum;

		currentPdf.getPage(pageNum).then(function(page) {
			var viewport = page.getViewport({ scale: currentScale });

			canvas.width = viewport.width;
			canvas.height = viewport.height;

			page.render({
				canvasContext: ctx,
				viewport: viewport
			}).promise.then(function() {
				modal.classList.remove('loading');
				updateNavButtons();
			});
		});
	}

	function updateNavButtons() {
		prevBtn.disabled = currentPage <= 1;
		nextBtn.disabled = currentPage >= totalPages;
	}

	function updateZoomDisplay() {
		var percent = Math.round((currentScale / baseScale) * 100);
		zoomLevelEl.textContent = percent + '%';
	}

	function closeModal() {
		modal.style.display = 'none';
		document.body.style.overflow = '';
		currentPdf = null;
	}

	// Navigation
	prevBtn.addEventListener('click', function() {
		if (currentPage > 1) renderPage(currentPage - 1);
	});

	nextBtn.addEventListener('click', function() {
		if (currentPage < totalPages) renderPage(currentPage + 1);
	});

	pageInput.addEventListener('change', function() {
		var page = parseInt(this.value, 10);
		if (page >= 1 && page <= totalPages) {
			renderPage(page);
		} else {
			this.value = currentPage;
		}
	});

	// Zoom
	zoomInBtn.addEventListener('click', function() {
		currentScale = Math.min(currentScale + 0.25, 4);
		updateZoomDisplay();
		renderPage(currentPage);
	});

	zoomOutBtn.addEventListener('click', function() {
		currentScale = Math.max(currentScale - 0.25, 0.5);
		updateZoomDisplay();
		renderPage(currentPage);
	});

	// Close
	closeBtn.addEventListener('click', closeModal);
	backdrop.addEventListener('click', closeModal);

	// Keyboard navigation
	document.addEventListener('keydown', function(e) {
		if (modal.style.display !== 'flex') return;

		switch (e.key) {
			case 'Escape':
				closeModal();
				break;
			case 'ArrowLeft':
				if (currentPage > 1) renderPage(currentPage - 1);
				break;
			case 'ArrowRight':
				if (currentPage < totalPages) renderPage(currentPage + 1);
				break;
			case '+':
			case '=':
				currentScale = Math.min(currentScale + 0.25, 4);
				updateZoomDisplay();
				renderPage(currentPage);
				break;
			case '-':
				currentScale = Math.max(currentScale - 0.25, 0.5);
				updateZoomDisplay();
				renderPage(currentPage);
				break;
		}
	});
})();
</script>
<?php endif; ?>

<?php
get_footer();
