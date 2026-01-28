<?php
/**
 * Theme Customizer
 *
 * WHY: Provides comprehensive customization options for colors, typography, layout,
 * and all visual aspects of the theme through WordPress Customizer.
 *
 * @package SocietyPress
 * @since 1.08d
 */

/**
 * Register customizer settings and controls.
 *
 * WHY: Allows site admins to customize every aspect of the theme visually.
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function societypress_customize_register( $wp_customize ) {

	// Remove default sections we don't need
	$wp_customize->remove_section( 'colors' );
	$wp_customize->remove_section( 'background_image' );

	/* ==========================================================================
	   COLORS
	   ========================================================================== */

	$wp_customize->add_section(
		'societypress_colors',
		array(
			'title'    => __( 'Colors', 'societypress' ),
			'priority' => 30,
		)
	);

	// Primary Color
	$wp_customize->add_setting(
		'societypress_primary_color',
		array(
			'default'           => '#0284c7',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_primary_color',
			array(
				'label'   => __( 'Primary Color', 'societypress' ),
				'section' => 'societypress_colors',
			)
		)
	);

	// Header Background Color
	$wp_customize->add_setting(
		'societypress_header_bg_color',
		array(
			'default'           => '#ffffff',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_header_bg_color',
			array(
				'label'   => __( 'Header Background Color', 'societypress' ),
				'section' => 'societypress_colors',
			)
		)
	);

	// Header Text Color
	$wp_customize->add_setting(
		'societypress_header_text_color',
		array(
			'default'           => '#44403c',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_header_text_color',
			array(
				'label'   => __( 'Header Text Color', 'societypress' ),
				'section' => 'societypress_colors',
			)
		)
	);

	// Footer Background Color
	$wp_customize->add_setting(
		'societypress_footer_bg_color',
		array(
			'default'           => '#292524',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_footer_bg_color',
			array(
				'label'   => __( 'Footer Background Color', 'societypress' ),
				'section' => 'societypress_colors',
			)
		)
	);

	// Footer Text Color
	$wp_customize->add_setting(
		'societypress_footer_text_color',
		array(
			'default'           => '#d6d3d1',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_footer_text_color',
			array(
				'label'   => __( 'Footer Text Color', 'societypress' ),
				'section' => 'societypress_colors',
			)
		)
	);

	// Body Text Color
	$wp_customize->add_setting(
		'societypress_body_text_color',
		array(
			'default'           => '#1c1917',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_body_text_color',
			array(
				'label'   => __( 'Body Text Color', 'societypress' ),
				'section' => 'societypress_colors',
			)
		)
	);

	// Link Color
	$wp_customize->add_setting(
		'societypress_link_color',
		array(
			'default'           => '#0284c7',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_link_color',
			array(
				'label'   => __( 'Link Color', 'societypress' ),
				'section' => 'societypress_colors',
			)
		)
	);

	// Page Background Color
	$wp_customize->add_setting(
		'societypress_page_bg_color',
		array(
			'default'           => '#f5f5f4',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_page_bg_color',
			array(
				'label'   => __( 'Page Background Color', 'societypress' ),
				'section' => 'societypress_colors',
			)
		)
	);

	/* ==========================================================================
	   TYPOGRAPHY
	   ========================================================================== */

	$wp_customize->add_section(
		'societypress_typography',
		array(
			'title'    => __( 'Typography', 'societypress' ),
			'priority' => 40,
		)
	);

	// Body Font Family
	$wp_customize->add_setting(
		'societypress_body_font',
		array(
			'default'           => 'system',
			'sanitize_callback' => 'societypress_sanitize_font',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_body_font',
		array(
			'label'   => __( 'Body Font', 'societypress' ),
			'section' => 'societypress_typography',
			'type'    => 'select',
			'choices' => societypress_get_font_choices(),
		)
	);

	// Body Font Size
	$wp_customize->add_setting(
		'societypress_body_font_size',
		array(
			'default'           => 16,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_body_font_size',
		array(
			'label'       => __( 'Body Font Size (px)', 'societypress' ),
			'section'     => 'societypress_typography',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 12,
				'max'  => 24,
				'step' => 1,
			),
		)
	);

	// Heading Font Family
	$wp_customize->add_setting(
		'societypress_heading_font',
		array(
			'default'           => 'system',
			'sanitize_callback' => 'societypress_sanitize_font',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_heading_font',
		array(
			'label'   => __( 'Heading Font', 'societypress' ),
			'section' => 'societypress_typography',
			'type'    => 'select',
			'choices' => societypress_get_font_choices(),
		)
	);

	// Menu Font Size
	$wp_customize->add_setting(
		'societypress_menu_font_size',
		array(
			'default'           => 16,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_menu_font_size',
		array(
			'label'       => __( 'Menu Font Size (px)', 'societypress' ),
			'section'     => 'societypress_typography',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 12,
				'max'  => 20,
				'step' => 1,
			),
		)
	);

	// Menu Font Weight
	$wp_customize->add_setting(
		'societypress_menu_font_weight',
		array(
			'default'           => '500',
			'sanitize_callback' => 'societypress_sanitize_font_weight',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_menu_font_weight',
		array(
			'label'   => __( 'Menu Font Weight', 'societypress' ),
			'section' => 'societypress_typography',
			'type'    => 'select',
			'choices' => array(
				'300' => __( 'Light (300)', 'societypress' ),
				'400' => __( 'Normal (400)', 'societypress' ),
				'500' => __( 'Medium (500)', 'societypress' ),
				'600' => __( 'Semi-Bold (600)', 'societypress' ),
				'700' => __( 'Bold (700)', 'societypress' ),
			),
		)
	);

	/* ==========================================================================
	   HEADER
	   ========================================================================== */

	$wp_customize->add_section(
		'societypress_header',
		array(
			'title'    => __( 'Header Settings', 'societypress' ),
			'priority' => 50,
		)
	);

	// Logo Max Height
	$wp_customize->add_setting(
		'societypress_logo_height',
		array(
			'default'           => 100,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_logo_height',
		array(
			'label'       => __( 'Logo Max Height (px)', 'societypress' ),
			'section'     => 'societypress_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 40,
				'max'  => 200,
				'step' => 5,
			),
		)
	);

	// Header Padding
	$wp_customize->add_setting(
		'societypress_header_padding',
		array(
			'default'           => 24,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_header_padding',
		array(
			'label'       => __( 'Header Top Padding (px)', 'societypress' ),
			'description' => __( 'Top padding for the header (bottom is always 0)', 'societypress' ),
			'section'     => 'societypress_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 10,
				'max'  => 60,
				'step' => 2,
			),
		)
	);

	// Sticky Header
	$wp_customize->add_setting(
		'societypress_sticky_header',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'societypress_sticky_header',
		array(
			'label'   => __( 'Enable Sticky Header', 'societypress' ),
			'section' => 'societypress_header',
			'type'    => 'checkbox',
		)
	);

	/* ==========================================================================
	   LAYOUT
	   ========================================================================== */

	$wp_customize->add_section(
		'societypress_layout',
		array(
			'title'    => __( 'Layout Settings', 'societypress' ),
			'priority' => 60,
		)
	);

	// Content Width
	$wp_customize->add_setting(
		'societypress_content_width',
		array(
			'default'           => 1200,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_content_width',
		array(
			'label'       => __( 'Content Max Width (px)', 'societypress' ),
			'section'     => 'societypress_layout',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 960,
				'max'  => 1920,
				'step' => 20,
			),
		)
	);

	// Sidebar Width
	$wp_customize->add_setting(
		'societypress_sidebar_width',
		array(
			'default'           => 320,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_sidebar_width',
		array(
			'label'       => __( 'Sidebar Width (px)', 'societypress' ),
			'section'     => 'societypress_layout',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 250,
				'max'  => 400,
				'step' => 10,
			),
		)
	);

	/* ==========================================================================
	   FOOTER
	   ========================================================================== */

	$wp_customize->add_section(
		'societypress_footer',
		array(
			'title'    => __( 'Footer Settings', 'societypress' ),
			'priority' => 70,
		)
	);

	// Footer Widget Columns
	$wp_customize->add_setting(
		'societypress_footer_columns',
		array(
			'default'           => 3,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_footer_columns',
		array(
			'label'   => __( 'Footer Widget Columns', 'societypress' ),
			'section' => 'societypress_footer',
			'type'    => 'select',
			'choices' => array(
				'1' => __( '1 Column', 'societypress' ),
				'2' => __( '2 Columns', 'societypress' ),
				'3' => __( '3 Columns', 'societypress' ),
				'4' => __( '4 Columns', 'societypress' ),
			),
		)
	);

	// Footer Padding
	$wp_customize->add_setting(
		'societypress_footer_padding',
		array(
			'default'           => 48,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_footer_padding',
		array(
			'label'       => __( 'Footer Padding (px)', 'societypress' ),
			'description' => __( 'Top and bottom padding for footer widgets', 'societypress' ),
			'section'     => 'societypress_footer',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 20,
				'max'  => 100,
				'step' => 4,
			),
		)
	);

	/* ==========================================================================
	   BUTTONS
	   ========================================================================== */

	$wp_customize->add_section(
		'societypress_buttons',
		array(
			'title'    => __( 'Button Settings', 'societypress' ),
			'priority' => 80,
		)
	);

	// Button Background Color
	$wp_customize->add_setting(
		'societypress_button_bg_color',
		array(
			'default'           => '#0284c7',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_button_bg_color',
			array(
				'label'   => __( 'Button Background Color', 'societypress' ),
				'section' => 'societypress_buttons',
			)
		)
	);

	// Button Text Color
	$wp_customize->add_setting(
		'societypress_button_text_color',
		array(
			'default'           => '#ffffff',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_button_text_color',
			array(
				'label'   => __( 'Button Text Color', 'societypress' ),
				'section' => 'societypress_buttons',
			)
		)
	);

	// Button Border Radius
	$wp_customize->add_setting(
		'societypress_button_radius',
		array(
			'default'           => 4,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_button_radius',
		array(
			'label'       => __( 'Button Border Radius (px)', 'societypress' ),
			'section'     => 'societypress_buttons',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 50,
				'step' => 1,
			),
		)
	);

	/* ==========================================================================
	   HERO SLIDER
	   ========================================================================== */

	$wp_customize->add_section(
		'societypress_slider',
		array(
			'title'       => __( 'Hero Slider', 'societypress' ),
			'description' => __( 'Configure up to 6 hero slides for your homepage. Each slide needs an image, optional text, and optional link.', 'societypress' ),
			'priority'    => 35,
		)
	);

	// Slider Height
	$wp_customize->add_setting(
		'societypress_slider_height',
		array(
			'default'           => 600,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'societypress_slider_height',
		array(
			'label'       => __( 'Slider Height (px)', 'societypress' ),
			'description' => __( 'Height of the hero slider', 'societypress' ),
			'section'     => 'societypress_slider',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 300,
				'max'  => 1000,
				'step' => 50,
			),
		)
	);

	// Slider Text Color
	$wp_customize->add_setting(
		'societypress_slider_text_color',
		array(
			'default'           => '#ffffff',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'societypress_slider_text_color',
			array(
				'label'   => __( 'Slider Text Color', 'societypress' ),
				'section' => 'societypress_slider',
			)
		)
	);

	// Loop through 6 slides
	for ( $i = 1; $i <= 6; $i++ ) {

		// Slide Image
		$wp_customize->add_setting(
			"societypress_slide_{$i}_image",
			array(
				'default'           => '',
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				"societypress_slide_{$i}_image",
				array(
					'label'       => sprintf( __( 'Slide %d - Image', 'societypress' ), $i ),
					'description' => __( 'Recommended: 1920 x 800 pixels. Use image OR video, not both.', 'societypress' ),
					'section'     => 'societypress_slider',
					'mime_type'   => 'image',
				)
			)
		);

		// Slide Video
		$wp_customize->add_setting(
			"societypress_slide_{$i}_video",
			array(
				'default'           => '',
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				"societypress_slide_{$i}_video",
				array(
					'label'       => sprintf( __( 'Slide %d - Video (MP4)', 'societypress' ), $i ),
					'description' => __( 'Optional. Video background instead of image. MP4 format recommended.', 'societypress' ),
					'section'     => 'societypress_slider',
					'mime_type'   => 'video',
				)
			)
		);

		// Slide Text
		$wp_customize->add_setting(
			"societypress_slide_{$i}_text",
			array(
				'default'           => '',
				'sanitize_callback' => 'wp_kses_post',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"societypress_slide_{$i}_text",
			array(
				'label'       => sprintf( __( 'Slide %d - Text', 'societypress' ), $i ),
				'description' => __( 'HTML allowed: <h1>Largest</h1> <h2>Large</h2> <h3>Medium</h3> Regular text. Use <strong>bold</strong>', 'societypress' ),
				'section'     => 'societypress_slider',
				'type'        => 'textarea',
			)
		);

		// Slide Link URL
		$wp_customize->add_setting(
			"societypress_slide_{$i}_url",
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"societypress_slide_{$i}_url",
			array(
				'label'       => sprintf( __( 'Slide %d - Link URL', 'societypress' ), $i ),
				'description' => __( 'Optional. Where to go when slide is clicked.', 'societypress' ),
				'section'     => 'societypress_slider',
				'type'        => 'url',
			)
		);

		// Slide Text Color
		$wp_customize->add_setting(
			"societypress_slide_{$i}_text_color",
			array(
				'default'           => '#ffffff',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				"societypress_slide_{$i}_text_color",
				array(
					'label'   => sprintf( __( 'Slide %d - Text Color', 'societypress' ), $i ),
					'section' => 'societypress_slider',
				)
			)
		);
	}
}
add_action( 'customize_register', 'societypress_customize_register' );

/**
 * Get font choices for select dropdowns.
 *
 * WHY: Provides curated list of web-safe and popular Google Fonts.
 *
 * @return array Font choices.
 */
function societypress_get_font_choices() {
	return array(
		'system'      => __( 'System Default', 'societypress' ),
		'georgia'     => __( 'Georgia (Serif)', 'societypress' ),
		'times'       => __( 'Times New Roman (Serif)', 'societypress' ),
		'arial'       => __( 'Arial (Sans-serif)', 'societypress' ),
		'helvetica'   => __( 'Helvetica (Sans-serif)', 'societypress' ),
		'verdana'     => __( 'Verdana (Sans-serif)', 'societypress' ),
		'trebuchet'   => __( 'Trebuchet MS (Sans-serif)', 'societypress' ),
		'courier'     => __( 'Courier New (Monospace)', 'societypress' ),
	);
}

/**
 * Sanitize font family selection.
 *
 * @param string $input Font family value.
 * @return string Sanitized value.
 */
function societypress_sanitize_font( $input ) {
	$valid = array_keys( societypress_get_font_choices() );
	return in_array( $input, $valid, true ) ? $input : 'system';
}

/**
 * Sanitize font weight selection.
 *
 * @param string $input Font weight value.
 * @return string Sanitized value.
 */
function societypress_sanitize_font_weight( $input ) {
	$valid = array( '300', '400', '500', '600', '700' );
	return in_array( $input, $valid, true ) ? $input : '500';
}

/**
 * Output customizer CSS in head.
 *
 * WHY: Applies customizer settings by overriding CSS custom properties.
 */
function societypress_customizer_css() {
	?>
	<style type="text/css" id="societypress-customizer-styles">
		:root {
			/* Colors */
			--sp-primary-600: <?php echo esc_attr( get_theme_mod( 'societypress_primary_color', '#0284c7' ) ); ?>;
			--sp-primary-700: <?php echo esc_attr( societypress_adjust_color( get_theme_mod( 'societypress_primary_color', '#0284c7' ), -20 ) ); ?>;
			--sp-primary-500: <?php echo esc_attr( societypress_adjust_color( get_theme_mod( 'societypress_primary_color', '#0284c7' ), 20 ) ); ?>;
			--sp-primary-50: <?php echo esc_attr( societypress_adjust_color( get_theme_mod( 'societypress_primary_color', '#0284c7' ), 220 ) ); ?>;
			--sp-primary-100: <?php echo esc_attr( societypress_adjust_color( get_theme_mod( 'societypress_primary_color', '#0284c7' ), 180 ) ); ?>;
		}

		body {
			font-size: <?php echo absint( get_theme_mod( 'societypress_body_font_size', 16 ) ); ?>px;
			color: <?php echo esc_attr( get_theme_mod( 'societypress_body_text_color', '#1c1917' ) ); ?>;
			background-color: <?php echo esc_attr( get_theme_mod( 'societypress_page_bg_color', '#f5f5f4' ) ); ?>;
			<?php echo societypress_get_font_stack( get_theme_mod( 'societypress_body_font', 'system' ) ); ?>
		}

		h1, h2, h3, h4, h5, h6 {
			<?php echo societypress_get_font_stack( get_theme_mod( 'societypress_heading_font', 'system' ) ); ?>
		}

		a {
			color: <?php echo esc_attr( get_theme_mod( 'societypress_link_color', '#0284c7' ) ); ?>;
		}

		/* Header */
		.site-header {
			background-color: <?php echo esc_attr( get_theme_mod( 'societypress_header_bg_color', '#ffffff' ) ); ?>;
			<?php if ( ! get_theme_mod( 'societypress_sticky_header', true ) ) : ?>
			position: relative;
			<?php endif; ?>
		}

		.site-branding-navigation {
			padding-top: <?php echo absint( get_theme_mod( 'societypress_header_padding', 24 ) ); ?>px;
			padding-bottom: 0;
		}

		.custom-logo {
			max-height: <?php echo absint( get_theme_mod( 'societypress_logo_height', 100 ) ); ?>px;
		}

		.main-navigation .primary-menu a,
		.search-toggle,
		.account-link {
			color: <?php echo esc_attr( get_theme_mod( 'societypress_header_text_color', '#44403c' ) ); ?>;
			font-size: <?php echo absint( get_theme_mod( 'societypress_menu_font_size', 16 ) ); ?>px;
			font-weight: <?php echo esc_attr( get_theme_mod( 'societypress_menu_font_weight', '500' ) ); ?>;
		}

		/* Layout */
		.sp-container {
			max-width: <?php echo absint( get_theme_mod( 'societypress_content_width', 1200 ) ); ?>px;
		}

		.widget-area {
			flex: 0 0 <?php echo absint( get_theme_mod( 'societypress_sidebar_width', 320 ) ); ?>px;
		}

		/* Footer */
		.site-footer {
			background-color: <?php echo esc_attr( get_theme_mod( 'societypress_footer_bg_color', '#292524' ) ); ?>;
			color: <?php echo esc_attr( get_theme_mod( 'societypress_footer_text_color', '#d6d3d1' ) ); ?>;
		}

		.footer-widgets {
			padding: <?php echo absint( get_theme_mod( 'societypress_footer_padding', 48 ) ); ?>px 0;
		}

		.footer-widgets-inner {
			grid-template-columns: repeat(<?php echo absint( get_theme_mod( 'societypress_footer_columns', 3 ) ); ?>, 1fr);
		}

		/* Buttons */
		.button,
		.wp-block-button__link,
		button[type="submit"],
		input[type="submit"],
		.read-more,
		.event-details-link {
			background-color: <?php echo esc_attr( get_theme_mod( 'societypress_button_bg_color', '#0284c7' ) ); ?>;
			color: <?php echo esc_attr( get_theme_mod( 'societypress_button_text_color', '#ffffff' ) ); ?>;
			border-radius: <?php echo absint( get_theme_mod( 'societypress_button_radius', 4 ) ); ?>px;
		}

		.button:hover,
		.button:focus,
		button[type="submit"]:hover,
		button[type="submit"]:focus,
		input[type="submit"]:hover,
		input[type="submit"]:focus,
		.read-more:hover,
		.read-more:focus,
		.event-details-link:hover,
		.event-details-link:focus {
			background-color: <?php echo esc_attr( societypress_adjust_color( get_theme_mod( 'societypress_button_bg_color', '#0284c7' ), -20 ) ); ?>;
			color: <?php echo esc_attr( get_theme_mod( 'societypress_button_text_color', '#ffffff' ) ); ?>;
		}

		/* Hero Slider */
		.hero-swiper {
			height: <?php echo absint( get_theme_mod( 'societypress_slider_height', 600 ) ); ?>px;
		}

		.hero-text,
		.hero-excerpt,
		.hero-excerpt p,
		.hero-excerpt h1,
		.hero-excerpt h2,
		.hero-excerpt h3,
		.hero-excerpt h4,
		.hero-excerpt h5,
		.hero-excerpt h6 {
			color: <?php echo esc_attr( get_theme_mod( 'societypress_slider_text_color', '#ffffff' ) ); ?>;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'societypress_customizer_css' );

/**
 * Get font stack CSS for a given font choice.
 *
 * WHY: Converts font selection to proper CSS font-family declaration.
 *
 * @param string $font Font choice.
 * @return string CSS font-family declaration.
 */
function societypress_get_font_stack( $font ) {
	$stacks = array(
		'system'    => 'font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;',
		'georgia'   => 'font-family: Georgia, Cambria, "Times New Roman", Times, serif;',
		'times'     => 'font-family: "Times New Roman", Times, serif;',
		'arial'     => 'font-family: Arial, Helvetica, sans-serif;',
		'helvetica' => 'font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;',
		'verdana'   => 'font-family: Verdana, Geneva, sans-serif;',
		'trebuchet' => 'font-family: "Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", sans-serif;',
		'courier'   => 'font-family: "Courier New", Courier, monospace;',
	);

	return isset( $stacks[ $font ] ) ? $stacks[ $font ] : $stacks['system'];
}

/**
 * Adjust color brightness.
 *
 * WHY: Creates lighter/darker variations of colors for hover states and accents.
 *
 * @param string $hex    Hex color code.
 * @param int    $steps  Amount to adjust (-255 to 255).
 * @return string Adjusted hex color.
 */
function societypress_adjust_color( $hex, $steps ) {
	// Remove # if present
	$hex = str_replace( '#', '', $hex );

	// Convert to RGB
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	// Adjust
	$r = max( 0, min( 255, $r + $steps ) );
	$g = max( 0, min( 255, $g + $steps ) );
	$b = max( 0, min( 255, $b + $steps ) );

	// Convert back to hex
	return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) . str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) . str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * WHY: Provides instant visual feedback as user adjusts settings.
 */
function societypress_customize_preview_js() {
	wp_enqueue_script(
		'societypress-customizer',
		get_template_directory_uri() . '/assets/js/customizer.js',
		array( 'customize-preview' ),
		SOCIETYPRESS_THEME_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'societypress_customize_preview_js' );
