<?php
/**
 * The template for displaying single posts
 *
 * WHY: Custom layout for individual blog posts with full content and comments.
 *
 * @package SocietyPress
 * @since 1.01d
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="sp-container">
		<div class="content-area">

			<?php
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content', get_post_type() );

				// Post navigation (previous/next)
				the_post_navigation(
					array(
						'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'societypress' ) . '</span> <span class="nav-title">%title</span>',
						'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'societypress' ) . '</span> <span class="nav-title">%title</span>',
					)
				);

				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile;
			?>

		</div><!-- .content-area -->

		<?php get_sidebar(); ?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
