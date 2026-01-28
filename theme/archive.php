<?php
/**
 * The template for displaying archive pages
 *
 * WHY: Displays category, tag, date, and author archives with proper context.
 *
 * @package SocietyPress
 * @since 1.01d
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="sp-container">
		<div class="content-area">

			<?php if ( have_posts() ) : ?>

				<header class="page-header">
					<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
					the_archive_description( '<div class="archive-description">', '</div>' );
					?>
				</header>

				<div class="posts-grid">
					<?php
					// Start the Loop
					while ( have_posts() ) :
						the_post();

						/*
						 * Include the Post-Type-specific template for the content.
						 * WHY: Allows different content layouts for different post types in archives.
						 */
						get_template_part( 'template-parts/content', get_post_type() );

					endwhile;
					?>
				</div>

				<?php
				// Previous/next page navigation
				the_posts_pagination(
					array(
						'mid_size'  => 2,
						'prev_text' => __( '&larr; Previous', 'societypress' ),
						'next_text' => __( 'Next &rarr;', 'societypress' ),
					)
				);

			else :

				get_template_part( 'template-parts/content', 'none' );

			endif;
			?>

		</div><!-- .content-area -->

		<?php get_sidebar(); ?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
