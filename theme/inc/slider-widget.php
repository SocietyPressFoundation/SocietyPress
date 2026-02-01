<?php
/**
 * Hero Slider Block Widget
 *
 * WHY: Provides a flexible, draggable widget for displaying hero sliders anywhere
 * on the site. Users can select which Slide Group to display, making it possible
 * to show different slides on different pages (e.g., Homepage slider vs Events slider).
 *
 * @package SocietyPress
 * @since 1.37d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Hero Slider block widget.
 *
 * WHY: Block widgets are the modern WordPress standard, providing a better
 * editing experience in both the Customizer and the block-based widget editor.
 * We register the block with just the render callback here; the JS handles
 * the editor-side registration.
 */
function societypress_register_hero_slider_widget() {
	// Register the editor script first
	wp_register_script(
		'societypress-hero-slider-editor',
		get_template_directory_uri() . '/assets/js/hero-slider-editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
		SOCIETYPRESS_THEME_VERSION,
		true
	);

	// Register the block
	register_block_type(
		'societypress/hero-slider',
		array(
			'api_version'     => 2,
			'editor_script'   => 'societypress-hero-slider-editor',
			'render_callback' => 'societypress_render_hero_slider_widget',
			'attributes'      => array(
				'slideGroup'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'height'          => array(
					'type'    => 'number',
					'default' => 600,
				),
				'autoplay'        => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'autoplayDelay'   => array(
					'type'    => 'number',
					'default' => 5000,
				),
				'showNavigation'  => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'showPagination'  => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
		)
	);
}
add_action( 'init', 'societypress_register_hero_slider_widget' );

/**
 * Pass slide group options to the editor script.
 *
 * WHY: The JavaScript needs to know what slide groups exist to populate
 * the dropdown. We pass this data via wp_localize_script.
 */
function societypress_hero_slider_editor_data() {
	// Get all slide groups for the dropdown
	$groups = get_terms(
		array(
			'taxonomy'   => 'sp_slide_group',
			'hide_empty' => false,
		)
	);

	$group_options = array(
		array(
			'label' => __( '— Select a Slide Group —', 'societypress' ),
			'value' => '',
		),
	);

	if ( ! is_wp_error( $groups ) && ! empty( $groups ) ) {
		foreach ( $groups as $group ) {
			$group_options[] = array(
				'label' => $group->name,
				'value' => $group->slug,
			);
		}
	}

	wp_localize_script(
		'societypress-hero-slider-editor',
		'societypressHeroSlider',
		array(
			'groupOptions' => $group_options,
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'societypress_hero_slider_editor_data' );

/**
 * Render the Hero Slider widget on the frontend.
 *
 * WHY: Generates the Swiper markup and enqueues necessary assets only when
 * the widget is actually used, improving performance on pages without sliders.
 *
 * @param array  $attributes Block attributes.
 * @param string $content    Block content (empty for this block).
 * @return string HTML output.
 */
function societypress_render_hero_slider_widget( $attributes, $content ) {
	// Extract attributes with defaults
	$slide_group     = isset( $attributes['slideGroup'] ) ? $attributes['slideGroup'] : '';
	$height          = isset( $attributes['height'] ) ? absint( $attributes['height'] ) : 600;
	$autoplay        = isset( $attributes['autoplay'] ) ? (bool) $attributes['autoplay'] : true;
	$autoplay_delay  = isset( $attributes['autoplayDelay'] ) ? absint( $attributes['autoplayDelay'] ) : 5000;
	$show_navigation = isset( $attributes['showNavigation'] ) ? (bool) $attributes['showNavigation'] : true;
	$show_pagination = isset( $attributes['showPagination'] ) ? (bool) $attributes['showPagination'] : true;

	// If no slide group selected, show helpful message in editor
	if ( empty( $slide_group ) ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// In block editor preview
			return '<div style="padding: 40px; background: #f0f0f0; text-align: center; border: 2px dashed #ccc;">
				<p style="margin: 0; color: #666;">' . esc_html__( 'Please select a Slide Group in the block settings sidebar.', 'societypress' ) . '</p>
			</div>';
		}
		// On frontend, return nothing if no group selected
		return '';
	}

	// Query slides from the selected group
	$slides_query = new WP_Query(
		array(
			'post_type'      => 'sp_slide',
			'posts_per_page' => 20, // Reasonable limit
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'sp_slide_group',
					'field'    => 'slug',
					'terms'    => $slide_group,
				),
			),
		)
	);

	// No slides found
	if ( ! $slides_query->have_posts() ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// In block editor preview
			return '<div style="padding: 40px; background: #fff3cd; text-align: center; border: 2px dashed #ffc107;">
				<p style="margin: 0; color: #856404;">' . sprintf(
					/* translators: %s: slide group name */
					esc_html__( 'No slides found in the "%s" group. Add some slides first!', 'societypress' ),
					esc_html( $slide_group )
				) . '</p>
			</div>';
		}
		return '';
	}

	// Enqueue Swiper CSS and JS (only when widget is actually rendered)
	societypress_enqueue_swiper_assets();

	// Generate unique ID for this slider instance
	$slider_id = 'hero-slider-' . wp_unique_id();

	// Build the slider HTML
	ob_start();
	?>
	<section class="hero-slider hero-slider-widget" id="<?php echo esc_attr( $slider_id ); ?>">
		<div class="swiper hero-swiper" style="height: <?php echo esc_attr( $height ); ?>px;">
			<div class="swiper-wrapper">

				<?php
				while ( $slides_query->have_posts() ) :
					$slides_query->the_post();
					$slide_id        = get_the_ID();
					$slide_url       = get_post_meta( $slide_id, '_sp_slide_url', true );
					$new_tab         = get_post_meta( $slide_id, '_sp_slide_new_tab', true );
					$video_id        = get_post_meta( $slide_id, '_sp_slide_video', true );
					$text_color      = get_post_meta( $slide_id, '_sp_slide_text_color', true );
					$slide_content   = get_the_content();

					// Default text color
					if ( empty( $text_color ) ) {
						$text_color = '#ffffff';
					}

					// Get video URL if present
					$video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';

					// Check if slide has a link
					$has_link = ! empty( $slide_url );
					?>
					<div class="swiper-slide">
						<?php if ( $has_link ) : ?>
							<a href="<?php echo esc_url( $slide_url ); ?>" class="slide-link"<?php echo $new_tab ? ' target="_blank" rel="noopener"' : ''; ?>>
						<?php endif; ?>

						<div class="hero-image">
							<?php if ( $video_url ) : ?>
								<!-- Video takes priority over image -->
								<video autoplay muted loop playsinline class="hero-video">
									<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
								</video>
							<?php elseif ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'sp-hero' ); ?>
							<?php endif; ?>
						</div>

						<?php if ( $slide_content ) : ?>
							<div class="hero-content">
								<div class="sp-container">
									<div class="hero-text" style="color: <?php echo esc_attr( $text_color ); ?>;">
										<div class="hero-excerpt">
											<?php echo wp_kses_post( $slide_content ); ?>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $has_link ) : ?>
							</a>
						<?php endif; ?>
					</div>
					<?php
				endwhile;
				wp_reset_postdata();
				?>

			</div><!-- .swiper-wrapper -->

			<?php if ( $show_navigation ) : ?>
				<!-- Navigation -->
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			<?php endif; ?>

			<?php if ( $show_pagination ) : ?>
				<!-- Pagination -->
				<div class="swiper-pagination"></div>
			<?php endif; ?>
		</div><!-- .swiper -->
	</section><!-- .hero-slider -->

	<?php
	// Initialize this specific slider instance
	societypress_add_swiper_init( $slider_id, $autoplay, $autoplay_delay, $show_navigation, $show_pagination );
	?>
	<?php
	return ob_get_clean();
}

/**
 * Enqueue Swiper assets.
 *
 * WHY: Loads Swiper CSS and JS only when a slider widget is rendered,
 * improving page load performance on pages without sliders.
 */
function societypress_enqueue_swiper_assets() {
	static $enqueued = false;

	// Only enqueue once per page
	if ( $enqueued ) {
		return;
	}

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

	$enqueued = true;
}

/**
 * Add Swiper initialization script for a slider instance.
 *
 * WHY: Each slider instance needs its own initialization with the correct
 * settings. This outputs inline JS that runs after Swiper loads.
 *
 * @param string $slider_id      Unique slider ID.
 * @param bool   $autoplay       Enable autoplay.
 * @param int    $autoplay_delay Autoplay delay in ms.
 * @param bool   $navigation     Show navigation arrows.
 * @param bool   $pagination     Show pagination dots.
 */
function societypress_add_swiper_init( $slider_id, $autoplay, $autoplay_delay, $navigation, $pagination ) {
	// Build Swiper options
	$options = array(
		'loop'       => true,
		'effect'     => 'fade',
		'fadeEffect' => array( 'crossFade' => true ),
	);

	if ( $autoplay ) {
		$options['autoplay'] = array(
			'delay'                => $autoplay_delay,
			'disableOnInteraction' => false,
		);
	}

	if ( $navigation ) {
		$options['navigation'] = array(
			'nextEl' => '#' . $slider_id . ' .swiper-button-next',
			'prevEl' => '#' . $slider_id . ' .swiper-button-prev',
		);
	}

	if ( $pagination ) {
		$options['pagination'] = array(
			'el'        => '#' . $slider_id . ' .swiper-pagination',
			'clickable' => true,
		);
	}

	$options_json = wp_json_encode( $options );

	// Output inline initialization script
	// WHY: Inline script ensures it runs after the HTML element exists
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		if (typeof Swiper !== 'undefined') {
			new Swiper('#<?php echo esc_js( $slider_id ); ?> .hero-swiper', <?php echo $options_json; ?>);
		}
	});
	</script>
	<?php
}
