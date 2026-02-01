<?php
/**
 * SocietyPress Theme Functions
 *
 * WHY: Core theme setup, features, and functionality.
 * Handles theme support, assets, widgets, and plugin dependency checks.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version.
 *
 * WHY: Used for cache busting assets and tracking theme updates.
 */
define( 'SOCIETYPRESS_THEME_VERSION', '1.37d' );

/**
 * Require login to view the site.
 *
 * WHY: During development/testing, restrict site access to logged-in users only.
 *      Non-logged-in visitors are redirected to the WordPress login page.
 *      Set SOCIETYPRESS_REQUIRE_LOGIN to false (or remove it) to disable.
 */
if ( ! defined( 'SOCIETYPRESS_REQUIRE_LOGIN' ) ) {
	define( 'SOCIETYPRESS_REQUIRE_LOGIN', true ); // Change to false to open the site
}

if ( SOCIETYPRESS_REQUIRE_LOGIN ) {
	add_action( 'template_redirect', 'societypress_require_login' );
}

/**
 * Redirect non-logged-in users to login page.
 */
function societypress_require_login() {
	// Skip if user is logged in
	if ( is_user_logged_in() ) {
		return;
	}

	// Skip login page itself to avoid redirect loop
	if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
		return;
	}

	// Redirect to login, then back to the requested page
	wp_safe_redirect( wp_login_url( home_url( $_SERVER['REQUEST_URI'] ) ) );
	exit;
}

/**
 * Theme setup.
 *
 * WHY: Registers theme features, menus, image sizes, and WordPress support.
 */
function societypress_setup() {
	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails on posts and pages
	add_theme_support( 'post-thumbnails' );

	// Custom image sizes
	// WHY: Optimized sizes for specific use cases improve performance
	add_image_size( 'sp-hero', 1920, 800, true );        // Hero slider images
	add_image_size( 'sp-featured', 800, 600, true );     // Featured images
	add_image_size( 'sp-thumbnail', 400, 300, true );    // Card thumbnails
	add_image_size( 'sp-member-photo', 300, 300, true ); // Member directory photos

	// Register navigation menus
	register_nav_menus(
		array(
			'primary'   => __( 'Primary Menu', 'societypress' ),
			'footer'    => __( 'Footer Menu', 'societypress' ),
			'utility'   => __( 'Utility Menu (Top Bar)', 'societypress' ),
		)
	);

	// Switch default core markup to output valid HTML5
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Add theme support for selective refresh for widgets
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Add support for core custom logo
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 400,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	// Add support for responsive embedded content
	add_theme_support( 'responsive-embeds' );

	// Add support for editor styles
	add_theme_support( 'editor-styles' );

	// Load text domain for translations
	load_theme_textdomain( 'societypress', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'societypress_setup' );

/**
 * Set the content width in pixels.
 *
 * WHY: Prevents content overflow and ensures proper oEmbed sizing.
 */
function societypress_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'societypress_content_width', 1200 );
}
add_action( 'after_setup_theme', 'societypress_content_width', 0 );

/**
 * Enqueue scripts and styles.
 *
 * WHY: Loads theme assets with proper dependencies and versioning.
 */
function societypress_scripts() {
	// Main stylesheet
	wp_enqueue_style(
		'societypress-style',
		get_stylesheet_uri(),
		array(),
		SOCIETYPRESS_THEME_VERSION
	);

	// Main JavaScript
	wp_enqueue_script(
		'societypress-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		SOCIETYPRESS_THEME_VERSION,
		true
	);

	// Comment reply script for threaded comments
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// WHY: Swiper.js is now loaded by the Hero Slider widget when needed,
	// or by front-page.php when using the legacy Customizer-based slider.
	// This removes the front-page-only restriction so Swiper can be used anywhere.
}
add_action( 'wp_enqueue_scripts', 'societypress_scripts' );

/**
 * Register SocietyPress block category.
 *
 * WHY: Groups all SocietyPress widgets/blocks together at the TOP of the
 *      block inserter, making them easy to find for admins. Senior users
 *      appreciate having our tools prominently displayed.
 *
 * @param array                   $categories Block categories.
 * @param WP_Block_Editor_Context $context    Block editor context.
 * @return array Modified categories with SocietyPress at the top.
 */
function societypress_register_block_category( $categories, $context ) {
	// Add SocietyPress category at the beginning (top of list).
	return array_merge(
		array(
			array(
				'slug'  => 'societypress',
				'title' => __( 'SocietyPress', 'societypress' ),
				'icon'  => 'groups', // Dashicon name (people/community icon).
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'societypress_register_block_category', 10, 2 );

/**
 * Register widget areas.
 *
 * WHY: Provides widget-ready areas for sidebars and footer.
 */
function societypress_widgets_init() {
	// Hero Area widget area (for hero sliders)
	// WHY: Full-width area at top of page, ideal for hero sliders with the
	// Hero Slider widget. Allows different sliders on different pages.
	register_sidebar(
		array(
			'name'          => __( 'Hero Area', 'societypress' ),
			'id'            => 'hero-area',
			'description'   => __( 'Full-width area above main content. Add the Hero Slider widget here to display slides.', 'societypress' ),
			'before_widget' => '<div id="%1$s" class="hero-area-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<span class="screen-reader-text">',
			'after_title'   => '</span>',
		)
	);

	// Below Header widget area (for breadcrumbs, announcements, etc.)
	register_sidebar(
		array(
			'name'          => __( 'Below Header', 'societypress' ),
			'id'            => 'below-header',
			'description'   => __( 'Appears below the header on all pages. Ideal for breadcrumbs or site-wide notices.', 'societypress' ),
			'before_widget' => '<div id="%1$s" class="below-header-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<span class="screen-reader-text">',
			'after_title'   => '</span>',
		)
	);

	// Primary sidebar
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'societypress' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Add widgets here to appear in your sidebar.', 'societypress' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	// Footer widget areas (3 columns)
	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar(
			array(
				'name'          => sprintf( __( 'Footer %d', 'societypress' ), $i ),
				'id'            => 'footer-' . $i,
				'description'   => sprintf( __( 'Footer column %d widget area.', 'societypress' ), $i ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'societypress_widgets_init' );

/**
 * Filter menu items based on login state.
 *
 * WHY: Some menu items should only appear to certain users:
 *      - "Join" links hidden from logged-in users (they've already joined)
 *      - "Directory" links hidden from logged-out users (members only)
 *
 * @param array $items Menu items.
 * @return array Filtered menu items.
 */
function societypress_filter_menu_items_by_login_state( $items ) {
	$is_logged_in = is_user_logged_in();

	// Items to hide from logged-in users (they don't need these).
	$hide_when_logged_in = array( 'join', 'become a member', 'sign up', 'register' );

	// Items to hide from logged-out users (members only).
	$hide_when_logged_out = array( 'directory', 'member directory', 'members directory', 'directory of members' );

	foreach ( $items as $key => $item ) {
		$title_lower = strtolower( $item->title );

		// Hide "Join" type items from logged-in users.
		if ( $is_logged_in ) {
			foreach ( $hide_when_logged_in as $keyword ) {
				if ( strpos( $title_lower, $keyword ) !== false ) {
					unset( $items[ $key ] );
					break;
				}
			}
		}

		// Hide "Directory" type items from logged-out users.
		if ( ! $is_logged_in ) {
			foreach ( $hide_when_logged_out as $keyword ) {
				if ( strpos( $title_lower, $keyword ) !== false ) {
					unset( $items[ $key ] );
					break;
				}
			}
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'societypress_filter_menu_items_by_login_state' );

/**
 * Load Customizer functionality.
 *
 * WHY: Provides comprehensive theme customization options through WordPress Customizer.
 */
require_once get_template_directory() . '/inc/customizer.php';

/**
 * Load Breadcrumbs functionality.
 *
 * WHY: Provides breadcrumb navigation and widget for site hierarchy display.
 */
require_once get_template_directory() . '/inc/breadcrumbs.php';

/**
 * Load Hero Slider Post Type.
 *
 * WHY: Provides the sp_slide custom post type, Slide Groups taxonomy,
 * and admin UI for managing hero slides.
 */
require_once get_template_directory() . '/inc/slider-post-type.php';

/**
 * Load Hero Slider Widget.
 *
 * WHY: Provides the block-based Hero Slider widget for displaying
 * slide groups anywhere on the site via the widget system.
 */
require_once get_template_directory() . '/inc/slider-widget.php';

/**
 * Check if SocietyPress plugin is active.
 *
 * WHY: Theme requires plugin for full functionality.
 *
 * @return bool True if plugin is active.
 */
function societypress_plugin_is_active() {
	return class_exists( 'SocietyPress' );
}

/**
 * Display admin notice if plugin is not active.
 *
 * WHY: Alerts administrators that core functionality is missing.
 */
function societypress_plugin_dependency_notice() {
	if ( ! societypress_plugin_is_active() ) {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
					/* translators: %s: Plugin name */
					esc_html__( 'The %s theme requires the SocietyPress plugin to be installed and activated for full functionality.', 'societypress' ),
					'<strong>SocietyPress</strong>'
				);
				?>
			</p>
			<?php if ( current_user_can( 'install_plugins' ) ) : ?>
				<p>
					<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=societypress&tab=search&type=term' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Install SocietyPress Plugin', 'societypress' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'societypress_plugin_dependency_notice' );

/**
 * Get event helper functions.
 *
 * WHY: Provides safe access to plugin event functions even if plugin is inactive.
 */

/**
 * Get event date.
 *
 * @param int $post_id Post ID.
 * @return string Event date or empty string.
 */
function sp_get_event_date( int $post_id ): string {
	if ( societypress_plugin_is_active() && class_exists( 'SocietyPress_Events' ) ) {
		return SocietyPress_Events::get_event_date( $post_id );
	}
	return '';
}

/**
 * Get event time.
 *
 * @param int $post_id Post ID.
 * @return string Event time or empty string.
 */
function sp_get_event_time( int $post_id ): string {
	if ( societypress_plugin_is_active() && class_exists( 'SocietyPress_Events' ) ) {
		return SocietyPress_Events::get_event_time( $post_id );
	}
	return '';
}

/**
 * Get event location.
 *
 * @param int $post_id Post ID.
 * @return string Event location or empty string.
 */
function sp_get_event_location( int $post_id ): string {
	if ( societypress_plugin_is_active() && class_exists( 'SocietyPress_Events' ) ) {
		return SocietyPress_Events::get_event_location( $post_id );
	}
	return '';
}

/**
 * Get event instructors.
 *
 * @param int $post_id Post ID.
 * @return string Event instructors or empty string.
 */
function sp_get_event_instructors( int $post_id ): string {
	if ( societypress_plugin_is_active() && class_exists( 'SocietyPress_Events' ) ) {
		return SocietyPress_Events::get_event_instructors( $post_id );
	}
	return '';
}

/**
 * Get formatted event datetime.
 *
 * @param int    $post_id Post ID.
 * @param string $format  PHP date format.
 * @return string Formatted datetime or empty string.
 */
function sp_get_formatted_datetime( int $post_id, string $format = 'F j, Y g:i A' ): string {
	if ( societypress_plugin_is_active() && class_exists( 'SocietyPress_Events' ) ) {
		return SocietyPress_Events::get_formatted_datetime( $post_id, $format );
	}
	return '';
}

/**
 * Check if registration is required.
 *
 * @param int $post_id Post ID.
 * @return bool True if registration required.
 */
function sp_is_registration_required( int $post_id ): bool {
	if ( societypress_plugin_is_active() && class_exists( 'SocietyPress_Events' ) ) {
		return SocietyPress_Events::is_registration_required( $post_id );
	}
	return false;
}

/**
 * Check if event is recurring.
 *
 * @param int $post_id Post ID.
 * @return bool True if recurring.
 */
function sp_is_recurring( int $post_id ): bool {
	if ( societypress_plugin_is_active() && class_exists( 'SocietyPress_Events' ) ) {
		return SocietyPress_Events::is_recurring( $post_id );
	}
	return false;
}

/**
 * Get recurring event description.
 *
 * @param int $post_id Post ID.
 * @return string Recurrence description.
 */
function sp_get_recurring_description( int $post_id ): string {
	if ( societypress_plugin_is_active() && class_exists( 'SocietyPress_Events' ) ) {
		return SocietyPress_Events::get_recurring_description( $post_id );
	}
	return '';
}

/**
 * Template Tags
 *
 * WHY: Reusable functions for displaying post metadata in templates.
 */

/**
 * Prints HTML with meta information for the current post date/time.
 *
 * WHY: Consistent date display across templates.
 */
function societypress_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf(
		$time_string,
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( DATE_W3C ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		/* translators: %s: post date */
		esc_html_x( 'Posted on %s', 'post date', 'societypress' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Prints HTML with meta information for the current author.
 *
 * WHY: Displays author byline consistently.
 */
function societypress_posted_by() {
	$byline = sprintf(
		/* translators: %s: post author */
		esc_html_x( 'by %s', 'post author', 'societypress' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Prints HTML with meta information for categories and tags.
 *
 * WHY: Displays post taxonomy terms in footer.
 */
function societypress_entry_footer() {
	// Hide category and tag text for pages
	if ( 'post' === get_post_type() ) {
		$categories_list = get_the_category_list( esc_html__( ', ', 'societypress' ) );
		if ( $categories_list ) {
			/* translators: %s: list of categories */
			printf( '<span class="cat-links">' . esc_html__( 'Posted in %s', 'societypress' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'societypress' ) );
		if ( $tags_list ) {
			/* translators: %s: list of tags */
			printf( '<span class="tags-links">' . esc_html__( 'Tagged %s', 'societypress' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link(
			sprintf(
				wp_kses(
					/* translators: %s: post title */
					__( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'societypress' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			)
		);
		echo '</span>';
	}

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
}

/**
 * Clean up the WordPress admin dashboard.
 *
 * WHY: The Welcome panel and WordPress Events widget are distracting clutter
 * for society admins who just want to manage their content. Removing these
 * provides a cleaner, more focused admin experience.
 */
function societypress_clean_dashboard() {
	// Remove WordPress Events and News widget
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );

	// Remove Quick Draft widget (most users don't need this)
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
}
add_action( 'wp_dashboard_setup', 'societypress_clean_dashboard' );

/**
 * Remove the Welcome panel for all users.
 *
 * WHY: The Welcome panel takes up significant space and is not useful for
 * society administrators who are already familiar with WordPress.
 */
remove_action( 'welcome_panel', 'wp_welcome_panel' );
