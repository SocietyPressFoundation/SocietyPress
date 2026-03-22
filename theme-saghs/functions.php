<?php
/**
 * the society Child Theme Functions
 *
 * WHY: This is the bootstrap file for the the society child theme. It enqueues the
 * parent stylesheet first (so the base layout loads), then the child stylesheet
 * (so the society overrides win). It also registers a footer nav menu, loads the
 * Poppins font from Google Fonts, and enqueues the the society-specific JavaScript
 * for hamburger toggle, dropdown nav, and the hero slider.
 *
 * NOTE: All backend functionality (events, library, members, page builder, etc.)
 * lives in the SocietyPress plugin. This child theme only handles the society-specific
 * presentation — colors, layout, and branding.
 *
 * @package the society
 * @since   0.02d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'the society_THEME_VERSION', '0.02d' );


// ============================================================================
// ENQUEUE STYLES & SCRIPTS
// ============================================================================

/**
 * Load Google Fonts, parent + child stylesheets, and the society JavaScript.
 *
 * WHY the parent must be enqueued explicitly: When a child theme is active,
 * WordPress only auto-loads the CHILD's style.css. The parent's stylesheet
 * has all the base layout, forms, widgets, and user menu styles that the society
 * inherits. Without enqueuing it here, the site would lose all base styling.
 *
 * Load order:
 * 1. Google Fonts (Poppins — the the society brand font)
 * 2. Parent style.css (base layout, forms, widgets)
 * 3. Child style.css  (the society color overrides, custom components)
 * 4. society.js         (hamburger, dropdowns, hero slider)
 */
add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Poppins with weights 300–700
    // WHY Poppins: The reference the society site uses Poppins as its primary font.
    // It's clean, modern, and highly readable — important for an older
    // demographic that makes up most genealogical society members.
    wp_enqueue_style(
        'society-google-fonts',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
        [],
        null // No version — Google handles caching via the URL
    );

    // Parent stylesheet — use get_template_directory_uri() which always
    // points to the PARENT theme, even when a child theme is active.
    wp_enqueue_style(
        'societypress-parent-style',
        get_template_directory_uri() . '/style.css',
        [ 'society-google-fonts' ],
        // WHY wp_get_theme() instead of SOCIETYPRESS_THEME_VERSION constant:
        // Child themes load before parent themes in WordPress's bootstrap order.
        // If we reference the parent constant here, it may not be defined yet,
        // causing a fatal error. Reading the version from the theme's stylesheet
        // header is always safe regardless of load order.
        wp_get_theme( 'societypress' )->get( 'Version' )
    );

    // Child stylesheet — loads after parent so our overrides take effect.
    wp_enqueue_style(
        'society-style',
        get_stylesheet_uri(),
        [ 'societypress-parent-style' ],
        the society_THEME_VERSION
    );

    // the society JavaScript — hamburger menu, dropdown nav behavior, hero slider.
    // Loaded in footer (true) so it doesn't block page rendering.
    wp_enqueue_script(
        'society-script',
        get_stylesheet_directory_uri() . '/js/society.js',
        [],
        the society_THEME_VERSION,
        true
    );
});


// ============================================================================
// REGISTER ADDITIONAL MENU LOCATION
// ============================================================================

/**
 * Register a footer navigation menu location.
 *
 * WHY: the society shows quick links in the footer (About Us, Contact, Library Hours).
 * A dedicated menu location lets the admin manage these links from Appearance >
 * Menus without hardcoding them in the template.
 *
 * NOTE: The 'primary' menu is already registered by the parent theme. We don't
 * need to re-register it here — WordPress merges child theme registrations
 * with the parent's.
 */
add_action( 'after_setup_theme', function () {
    register_nav_menus([
        'footer' => 'Footer Navigation',
    ]);
});


// ============================================================================
// CUSTOM NAV WALKER — 3-LEVEL DROPDOWN SUPPORT
// ============================================================================

/**
 * Custom walker that adds 'menu-item-has-children' handling and clean markup
 * for the the society 3-level dropdown navigation.
 *
 * WHY a custom walker: The default WordPress walker works fine for basic menus,
 * but we need:
 * 1. A way to add a toggle button for mobile (items with children get a
 *    clickable arrow that JS can hook into)
 * 2. Clean class names we can target reliably in CSS
 *
 * WordPress already adds 'menu-item-has-children' to parent items, so our
 * walker just adds a small toggle <button> after the link text for items
 * that have sub-menus. The CSS and JS handle the rest.
 */
class the society_Nav_Walker extends Walker_Nav_Menu {

    /**
     * Starts an element (a single menu item).
     *
     * WHY we override this: To inject a toggle button inside <li> elements
     * that have children. On desktop this is invisible (CSS dropdowns use
     * :hover), but on mobile the JS toggles the .is-open class on click.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        // Let WordPress build the standard <li> and <a> markup
        parent::start_el( $output, $item, $depth, $args, $id );

        // If this item has children, add a toggle button for mobile
        if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
            $output .= '<button class="society-submenu-toggle" aria-expanded="false" aria-label="Toggle submenu">';
            $output .= '<span class="society-toggle-icon">&#9662;</span>';
            $output .= '</button>';
        }
    }
}
