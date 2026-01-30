<?php
/**
 * Template Name: Member Directory
 * Template Post Type: page
 *
 * WHY: Public member directory. Works with SocietyPress plugin.
 * Shows members who have opted in to the directory.
 *
 * @package SocietyPress
 * @since 1.28d
 */

get_header();

// Check if SocietyPress plugin is active
$plugin_active = function_exists( 'societypress' );
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

					// Render the directory via shortcode (plugin handles all logic)
					if ( $plugin_active ) {
						echo do_shortcode( '[societypress_directory]' );
					} else {
						?>
						<div class="sp-alert sp-alert--error">
							<p><?php esc_html_e( 'The SocietyPress plugin is required for the member directory.', 'societypress' ); ?></p>
						</div>
						<?php
					}
					?>
				</div>

			</article>

		</div><!-- .content-area -->

		<?php get_sidebar(); ?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
