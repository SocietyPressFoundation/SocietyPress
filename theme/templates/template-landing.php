<?php
/**
 * Template Name: Landing Page
 * Template Post Type: page
 *
 * WHY: Full-width layout with no sidebar and no page title.
 *      Perfect for signup forms, landing pages, and focused content
 *      where shortcodes provide their own headings.
 *
 * @package SocietyPress
 * @since 1.23d
 */

get_header();
?>

<main id="primary" class="site-main full-width landing-page">
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

		<?php endwhile; ?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
