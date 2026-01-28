<?php
/**
 * Template Name: Full Width
 * Template Post Type: page
 *
 * WHY: Provides full-width layout without sidebar for landing pages and special content.
 *
 * @package SocietyPress
 * @since 1.01d
 */

get_header();
?>

<main id="primary" class="site-main full-width">
	<div class="sp-container">

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="page-thumbnail">
						<?php the_post_thumbnail( 'sp-hero' ); ?>
					</div>
				<?php endif; ?>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>

				<div class="entry-content">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'societypress' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>

				<?php if ( get_edit_post_link() ) : ?>
					<footer class="entry-footer">
						<?php
						edit_post_link(
							sprintf(
								wp_kses(
									/* translators: %s: Name of current post */
									__( 'Edit <span class="screen-reader-text">%s</span>', 'societypress' ),
									array(
										'span' => array(
											'class' => array(),
										),
									)
								),
								wp_kses_post( get_the_title() )
							),
							'<span class="edit-link">',
							'</span>'
						);
						?>
					</footer>
				<?php endif; ?>

			</article>

			<?php
			// If comments are open or we have at least one comment, load up the comment template
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile;
		?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
