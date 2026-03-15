<?php
/**
 * SocietyPress Theme Functions
 *
 * WHY: This is the theme's bootstrap file. It tells WordPress what features
 * we support, loads our stylesheet, and registers the navigation menu
 * and sidebar widget area.
 *
 * IMPORTANT: All functionality — CSS custom properties, Google Fonts loading,
 * admin bar hiding, dashicons, user menu, My Account helpers, form processing,
 * and custom avatar overrides — lives in the SocietyPress PLUGIN, not here.
 * The theme handles presentation only. The plugin handles logic. This keeps
 * child themes clean: they inherit the parent theme's layout and can override
 * templates, while all backend functionality comes from the plugin regardless
 * of which theme is active.
 *
 * @package SocietyPress
 * @since   1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SOCIETYPRESS_THEME_VERSION', '1.0.5' );


// ============================================================================
// THEME SETUP
// ============================================================================

/**
 * Set up theme defaults and register support for WordPress features.
 *
 * WHY each feature:
 * - title-tag:       Let WordPress manage the <title> tag (best for SEO)
 * - post-thumbnails:  Enable featured images on posts and pages
 * - html5:           Use modern HTML5 markup for forms, comments, etc.
 * - custom-logo:     Let the site admin upload a logo through the Customizer
 *
 * WHY register_nav_menus here and not in the plugin: Navigation menus are
 * a presentation concern — different themes might want different menu
 * locations. The parent theme defines 'primary', and child themes can
 * add their own via their own functions.php.
 */
add_action( 'after_setup_theme', function () {

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );

    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ]);

    add_theme_support( 'custom-logo', [
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // Register the primary navigation menu (appears in the header)
    register_nav_menus([
        'primary' => 'Primary Navigation',
    ]);
});


// ============================================================================
// ENQUEUE STYLES & SCRIPTS
// ============================================================================

/**
 * Load the theme's stylesheet.
 *
 * WHY: We use the theme version number as a cache-buster so browsers
 * always load the latest CSS after a theme update. Without this,
 * users might see stale styles for hours or days.
 *
 * NOTE: When a child theme is active, get_stylesheet_uri() returns the
 * CHILD theme's style.css, not the parent's. The child theme is
 * responsible for enqueuing the parent stylesheet if it needs it
 * (see the the society child theme's functions.php for an example).
 */
add_action( 'wp_enqueue_scripts', function () {
    // WHY get_template_directory_uri: When a child theme is active,
    // get_stylesheet_uri() returns the CHILD theme's style.css — not ours.
    // We must explicitly point to the parent directory so our base styles
    // always load, regardless of which child theme (if any) is active.
    wp_enqueue_style(
        'societypress-style',
        get_template_directory_uri() . '/style.css',
        [],
        SOCIETYPRESS_THEME_VERSION
    );
});


// ============================================================================
// WIDGET AREAS
// ============================================================================

/**
 * Register the sidebar widget area.
 *
 * WHY: A sidebar gives site admins a place to add search, recent posts,
 * categories, and other widgets. It appears on single post pages by default.
 *
 * Child themes can register additional widget areas (like the society's three
 * footer columns) in their own functions.php without conflicting with this.
 */
add_action( 'widgets_init', function () {
    register_sidebar([
        'name'          => 'Sidebar',
        'id'            => 'sidebar-1',
        'description'   => 'Widgets in this area appear on blog posts and archive pages.',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
});
