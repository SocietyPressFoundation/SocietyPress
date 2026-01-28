<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * WHY: Provides helpful feedback and navigation options when content isn't found.
 *
 * @package SocietyPress
 * @since 1.01d
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="sp-container">
		<div class="content-area error-404-content">

			<section class="error-404 not-found">
				<div class="error-404-image">
					<?php
					// WHY: Display 404 genealogist image
					$upload_dir = wp_upload_dir();
					$image_url = $upload_dir['baseurl'] . '/2026/01/404.jpg';
					?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( 'Perplexed genealogist searching through records', 'societypress' ); ?>" />
				</div>

				<header class="page-header">
					<h1 class="page-title"><?php esc_html_e( 'Hmm... We Can\'t Find That Record', 'societypress' ); ?></h1>
				</header>

				<div class="page-content">
					<p><?php esc_html_e( 'Like a missing page from an old family Bible, this record seems to have vanished. The page you\'re looking for might have been moved, renamed, or may have never existed.', 'societypress' ); ?></p>

					<div class="error-404-search">
						<h2><?php esc_html_e( 'Let\'s search the archives:', 'societypress' ); ?></h2>
						<?php get_search_form(); ?>
					</div>

					<div class="error-404-suggestions">
						<h2><?php esc_html_e( 'Try these resources:', 'societypress' ); ?></h2>
						<ul>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return to Homepage', 'societypress' ); ?></a></li>
							<?php if ( societypress_plugin_is_active() ) : ?>
								<li><a href="<?php echo esc_url( get_post_type_archive_link( 'sp_event' ) ); ?>"><?php esc_html_e( 'Browse Upcoming Events', 'societypress' ); ?></a></li>
							<?php endif; ?>
							<?php
							// Link to blog page if set
							if ( get_option( 'page_for_posts' ) ) :
								?>
								<li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'View Recent News', 'societypress' ); ?></a></li>
							<?php endif; ?>
						</ul>
					</div>

					<?php
					// Show recent posts widget if there are posts
					if ( have_posts() || get_posts( array( 'posts_per_page' => 1 ) ) ) :
						?>
						<div class="error-404-recent-posts">
							<h2><?php esc_html_e( 'Recent Posts:', 'societypress' ); ?></h2>
							<ul>
								<?php
								$recent_posts = wp_get_recent_posts(
									array(
										'numberposts' => 5,
										'post_status' => 'publish',
									)
								);
								foreach ( $recent_posts as $recent ) :
									?>
									<li>
										<a href="<?php echo esc_url( get_permalink( $recent['ID'] ) ); ?>">
											<?php echo esc_html( $recent['post_title'] ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

				</div>
			</section>

		</div><!-- .content-area -->
	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
