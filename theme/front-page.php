<?php
/**
 * The homepage template
 *
 * WHY: Displays the site homepage with hero slider and featured content.
 * This template automatically takes precedence for the front page in WordPress.
 *
 * @package SocietyPress
 * @since 1.09d
 */

get_header();
?>

<main id="primary" class="site-main">

	<?php
	/**
	 * Hero Area Widget Zone
	 *
	 * WHY: The Hero Slider widget system is the preferred way to display sliders.
	 * It allows different slide groups on different pages and is more flexible.
	 * If the Hero Area widget zone has widgets, display those.
	 * Otherwise, fall back to the legacy Customizer-based slider for backward compatibility.
	 */
	if ( is_active_sidebar( 'hero-area' ) ) :
		?>
		<div class="hero-area-wrapper">
			<?php dynamic_sidebar( 'hero-area' ); ?>
		</div>
		<?php
	else :
		/**
		 * Legacy Customizer Slider (Fallback)
		 *
		 * WHY: Maintains backward compatibility for existing sites using the
		 * Customizer-based slider. New sites should use the Hero Slider widget instead.
		 * Slides are managed in Appearance → Customize → Hero Slider
		 * Supports both images and MP4 videos as backgrounds.
		 */

		// Collect slides with images or videos (only show slides that have media)
		$slides = array();
		for ( $i = 1; $i <= 6; $i++ ) {
			$image = get_theme_mod( "societypress_slide_{$i}_image", '' );
			$video = get_theme_mod( "societypress_slide_{$i}_video", '' );

			// Only add slide if it has an image or video
			if ( $image || $video ) {
				$slides[] = array(
					'image'      => $image,
					'video'      => $video,
					'text'       => get_theme_mod( "societypress_slide_{$i}_text", '' ),
					'url'        => get_theme_mod( "societypress_slide_{$i}_url", '' ),
					'text_color' => get_theme_mod( "societypress_slide_{$i}_text_color", '#ffffff' ),
				);
			}
		}

		// Only display slider if there are slides
		if ( ! empty( $slides ) ) :
			// Enqueue Swiper for legacy slider
			wp_enqueue_style(
				'swiper',
				'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
				array(),
				'11.0.0'
			);
			wp_enqueue_script(
				'swiper',
				'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
				array(),
				'11.0.0',
				true
			);
			?>
			<section class="hero-slider hero-slider-legacy">
				<div class="swiper hero-swiper">
					<div class="swiper-wrapper">

						<?php foreach ( $slides as $slide ) : ?>
							<?php
							// Wrap entire slide in link if URL provided
							$has_link = ! empty( $slide['url'] );
							?>
							<div class="swiper-slide">
								<?php if ( $has_link ) : ?>
									<a href="<?php echo esc_url( $slide['url'] ); ?>" class="slide-link">
								<?php endif; ?>

								<div class="hero-image">
									<?php if ( $slide['video'] ) : ?>
										<?php
										// Video takes priority over image
										$video_url = wp_get_attachment_url( $slide['video'] );
										?>
										<video autoplay muted loop playsinline class="hero-video">
											<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
										</video>
									<?php elseif ( $slide['image'] ) : ?>
										<?php echo wp_get_attachment_image( $slide['image'], 'sp-hero' ); ?>
									<?php endif; ?>
								</div>

								<?php if ( $slide['text'] ) : ?>
									<div class="hero-content">
										<div class="sp-container">
											<div class="hero-text" style="color: <?php echo esc_attr( $slide['text_color'] ); ?>;">
												<div class="hero-excerpt">
													<?php echo wp_kses_post( $slide['text'] ); ?>
												</div>
											</div>
										</div>
									</div>
								<?php endif; ?>

								<?php if ( $has_link ) : ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>

					</div><!-- .swiper-wrapper -->

					<!-- Navigation -->
					<div class="swiper-button-prev"></div>
					<div class="swiper-button-next"></div>

					<!-- Pagination -->
					<div class="swiper-pagination"></div>
				</div><!-- .swiper -->
			</section><!-- .hero-slider -->

			<script>
			document.addEventListener('DOMContentLoaded', function() {
				if (typeof Swiper !== 'undefined') {
					new Swiper('.hero-slider-legacy .hero-swiper', {
						loop: true,
						effect: 'fade',
						fadeEffect: { crossFade: true },
						autoplay: {
							delay: 5000,
							disableOnInteraction: false
						},
						navigation: {
							nextEl: '.hero-slider-legacy .swiper-button-next',
							prevEl: '.hero-slider-legacy .swiper-button-prev'
						},
						pagination: {
							el: '.hero-slider-legacy .swiper-pagination',
							clickable: true
						}
					});
				}
			});
			</script>
		<?php endif;
	endif; ?>

	<?php
	/**
	 * Upcoming Events Section
	 *
	 * WHY: Highlight upcoming events to drive registrations and attendance.
	 */
	if ( post_type_exists( 'sp_event' ) ) :
		$events_query = new WP_Query(
			array(
				'post_type'      => 'sp_event',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				'meta_key'       => 'sp_event_date',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'     => 'sp_event_date',
						'value'   => current_time( 'Y-m-d' ),
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			)
		);

		if ( $events_query->have_posts() ) :
			?>
			<section class="home-events">
				<div class="sp-container">
					<h2 class="section-title"><?php esc_html_e( 'Upcoming Events', 'societypress' ); ?></h2>
					<div class="events-grid">
						<?php
						while ( $events_query->have_posts() ) :
							$events_query->the_post();
							get_template_part( 'template-parts/content', 'sp_event' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
					<div class="section-cta">
						<?php
						// Link to events archive
						$events_archive_link = get_post_type_archive_link( 'sp_event' );
						if ( $events_archive_link ) :
							?>
							<a href="<?php echo esc_url( $events_archive_link ); ?>" class="button">
								<?php esc_html_e( 'View All Events', 'societypress' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</section>
			<?php
		endif;
	endif;
	?>

	<?php
	/**
	 * Recent News/Blog Posts Section
	 *
	 * WHY: Keep visitors informed with latest news and updates.
	 */
	$recent_posts = new WP_Query(
		array(
			'posts_per_page' => 3,
			'post_status'    => 'publish',
			'post_type'      => 'post',
		)
	);

	if ( $recent_posts->have_posts() ) :
		?>
		<section class="home-news">
			<div class="sp-container">
				<h2 class="section-title"><?php esc_html_e( 'Latest News', 'societypress' ); ?></h2>
				<div class="posts-grid">
					<?php
					while ( $recent_posts->have_posts() ) :
						$recent_posts->the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="post-thumbnail">
									<a href="<?php the_permalink(); ?>">
										<?php the_post_thumbnail( 'sp-featured' ); ?>
									</a>
								</div>
							<?php endif; ?>
							<div class="post-card-content">
								<header class="entry-header">
									<?php the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h3>' ); ?>
								</header>
								<div class="entry-summary">
									<?php the_excerpt(); ?>
								</div>
								<a href="<?php the_permalink(); ?>" class="read-more">
									<?php esc_html_e( 'Read More', 'societypress' ); ?>
								</a>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<div class="section-cta">
					<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="button">
						<?php esc_html_e( 'View All News', 'societypress' ); ?>
					</a>
				</div>
			</div>
		</section>
		<?php
	endif;
	?>

	<?php
	/**
	 * Homepage Content (if set as static page)
	 *
	 * WHY: Allow custom content to be added via the page editor.
	 */
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			if ( get_the_content() ) :
				?>
				<section class="home-content">
					<div class="sp-container">
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</div>
				</section>
				<?php
			endif;
		endwhile;
	endif;
	?>

</main><!-- #primary -->

<?php
get_footer();
