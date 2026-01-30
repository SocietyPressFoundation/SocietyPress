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
 * @package SocietyPress
 * @since 1.31d
 */

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
					?>

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
										<a href="<?php echo esc_url( $newsletter['url'] ); ?>" class="sp-newsletter-link" download>
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

<?php
get_footer();
