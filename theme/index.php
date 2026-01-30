<?php
/**
 * The main template file
 *
 * WHY: WordPress fallback template when no specific template exists.
 * Displays the blog archive.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="sp-container">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
				if ( is_home() && ! is_front_page() ) :
					?>
					<h1 class="page-title"><?php single_post_title(); ?></h1>
					<?php
				elseif ( is_archive() ) :
					the_archive_title( '<h1 class="page-title">', '</h1>' );
					the_archive_description( '<div class="archive-description">', '</div>' );
				elseif ( is_search() ) :
					?>
					<h1 class="page-title">
						<?php
						/* translators: %s: search query */
						printf( esc_html__( 'Search Results for: %s', 'societypress' ), '<span>' . get_search_query() . '</span>' );
						?>
					</h1>
				<?php else : ?>
					<h1 class="page-title"><?php esc_html_e( 'Blog', 'societypress' ); ?></h1>
					<?php
				endif;
				?>
			</header>

			<div class="posts-grid">
				<?php
				// Start the Loop
				while ( have_posts() ) :
					the_post();

					/*
					 * Include the Post-Type-specific template for the content.
					 * WHY: Allows different content layouts for different post types.
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

			// No content found
			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
