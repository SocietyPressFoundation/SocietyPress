<?php
/**
 * The template for displaying search results
 *
 * WHY: Custom layout for search results with query info and result count.
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
					<h1 class="page-title">
						<?php
						/* translators: %s: search query */
						printf( esc_html__( 'Search Results for: %s', 'societypress' ), '<span>' . get_search_query() . '</span>' );
						?>
					</h1>
					<?php
					global $wp_query;
					if ( $wp_query->found_posts ) :
						?>
						<p class="search-results-count">
							<?php
							/* translators: %d: number of search results */
							printf(
								esc_html( _n( 'Found %d result', 'Found %d results', $wp_query->found_posts, 'societypress' ) ),
								number_format_i18n( $wp_query->found_posts )
							);
							?>
						</p>
					<?php endif; ?>
				</header>

				<div class="search-results">
					<?php
					// Start the Loop
					while ( have_posts() ) :
						the_post();

						/*
						 * Include the Post-Type-specific template for the content.
						 * WHY: Different post types can have different search result displays.
						 */
						get_template_part( 'template-parts/content-search', get_post_type() );

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

				?>
				<header class="page-header">
					<h1 class="page-title">
						<?php
						/* translators: %s: search query */
						printf( esc_html__( 'Search Results for: %s', 'societypress' ), '<span>' . get_search_query() . '</span>' );
						?>
					</h1>
				</header>

				<?php
				get_template_part( 'template-parts/content', 'none' );

			endif;
			?>

		</div><!-- .content-area -->

		<?php get_sidebar(); ?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
