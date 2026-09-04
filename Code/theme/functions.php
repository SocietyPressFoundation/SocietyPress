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

// WHY the defined() guard: Child themes are sometimes cloned from the
// parent template and inadvertently kept the same SOCIETYPRESS_THEME_VERSION
// define at the top of their functions.php. In WordPress, child theme
// functions.php loads BEFORE the parent's, so the child defines the
// constant first and the parent's unguarded define() triggers a PHP
// warning on every page load ("Constant already defined"). Wrapping the
// define in an if-not-defined is the standard defensive pattern and keeps
// the parent theme safe against child theme copies. If a child theme wants
// its own version it should define a differently-named constant.
if ( ! defined( 'SOCIETYPRESS_THEME_VERSION' ) ) {
	define( 'SOCIETYPRESS_THEME_VERSION', '1.5.17' );
}


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

    // Register navigation menus
    register_nav_menus([
        'primary' => __( 'Primary Navigation', 'societypress' ),
        'footer'  => __( 'Footer Links', 'societypress' ),
    ]);
});


// ============================================================================
// CONDITIONAL MENU ITEMS — LOGIN / MEMBERSHIP STATE
// ============================================================================
/**
 * Show or hide nav menu items based on the current visitor's login and
 * membership state. Driven entirely by CSS classes set on each menu item
 * via Appearance → Menus (enable the CSS Classes field under Screen
 * Options if it isn't already visible).
 *
 * Supported classes:
 *
 *   sp-hide-when-logged-in
 *     Item vanishes for anyone who is signed in. Pair with sp-nav-cta on a
 *     "Become a Member" entry so prospective members see the CTA but
 *     existing members don't get re-pitched on every page.
 *
 *   sp-show-when-expiring
 *     Item ONLY appears when the current user is a member whose membership
 *     is within the renewal window (or already expired). Use on a "Renew
 *     Now" entry so members get a timely nudge without pestering members
 *     who just paid. Renewal window reuses the longest enabled interval
 *     from Settings → Renewals (30d / 15d / 7d), default 30.
 *
 * We also prune orphaned submenu items whose parent was removed, so we
 * never leave dangling children rooted at a dropped ancestor.
 */
add_filter( 'wp_nav_menu_objects', function ( $items ) {

    $is_logged_in = is_user_logged_in();

    // Only compute renewal status if we actually have menu items that care,
    // and only when someone is logged in (the check short-circuits for
    // guests). Avoids a DB query on every menu render for the common case.
    $renewal_status = null;
    $needs_renewal_check = false;
    foreach ( $items as $item ) {
        $classes = isset( $item->classes ) && is_array( $item->classes ) ? $item->classes : [];
        if ( in_array( 'sp-show-when-expiring', $classes, true ) ) {
            $needs_renewal_check = true;
            break;
        }
    }
    if ( $needs_renewal_check && $is_logged_in && function_exists( 'sp_user_renewal_status' ) ) {
        $renewal_status = sp_user_renewal_status();
    }
    $show_renewal = in_array( $renewal_status, [ 'expiring', 'expired' ], true );

    $removed_ids = [];

    foreach ( $items as $key => $item ) {
        $classes = isset( $item->classes ) && is_array( $item->classes ) ? $item->classes : [];

        $hide_self = false;

        // Hide "Become a Member"-style items for signed-in visitors
        if ( $is_logged_in && in_array( 'sp-hide-when-logged-in', $classes, true ) ) {
            $hide_self = true;
        }

        // Show "Renew Now"-style items ONLY when the current member is
        // within (or past) the renewal window. Hide otherwise.
        if ( in_array( 'sp-show-when-expiring', $classes, true ) && ! $show_renewal ) {
            $hide_self = true;
        }

        $parent_gone = in_array( (int) $item->menu_item_parent, $removed_ids, true );

        if ( $hide_self || $parent_gone ) {
            $removed_ids[] = (int) $item->ID;
            unset( $items[ $key ] );
        }
    }

    return $items;
} );


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
 * (see any of the bundled child themes' functions.php for an example).
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

    // WHY wp_add_inline_style here: The SocietyPress plugin exposes a
    // "Custom CSS" field on the Design settings page (Settings → Appearance
    // → Custom CSS). The admin pastes CSS into that field and it's stored
    // in societypress_settings['design_custom_css']. We attach it to our
    // stylesheet handle via wp_add_inline_style, which prints it in a
    // <style id="societypress-style-inline-css"> tag immediately after the
    // main stylesheet is loaded. Because it comes AFTER the theme CSS in
    // the cascade, it wins specificity ties without requiring !important,
    // which is exactly what an admin expects when they "override" a style.
    //
    // The plugin's sanitizer scrubs </style> and PHP tags on save, so the
    // value here is safe to print inline. We still check function_exists
    // and get_option defensively — the theme has to work if the plugin is
    // deactivated, even if the custom CSS disappears in that case.
    if ( function_exists( 'get_option' ) ) {
        $sp_settings      = get_option( 'societypress_settings', array() );
        $sp_custom_css    = isset( $sp_settings['design_custom_css'] ) ? (string) $sp_settings['design_custom_css'] : '';
        if ( $sp_custom_css !== '' ) {
            wp_add_inline_style( 'societypress-style', $sp_custom_css );
        }
    }

    // WHY theme.js: Handles the hamburger menu toggle on mobile screens.
    // Loaded in the footer (true) so it doesn't block page rendering.
    // Uses get_template_directory_uri() for the same reason as the stylesheet —
    // child themes need the parent's JS to load regardless.
    wp_enqueue_script(
        'societypress-theme',
        get_template_directory_uri() . '/js/theme.js',
        [],
        SOCIETYPRESS_THEME_VERSION,
        true
    );

    // The drop down caret buttons are built in JavaScript, so their screen
    // reader labels cannot be wrapped in __() where they are written. Passing
    // the pattern through here keeps them translatable with everything else.
    wp_localize_script(
        'societypress-theme',
        'SocietyPressTheme',
        [
            /* translators: %s: the name of the menu item the drop down belongs to. */
            'submenuLabel' => __( 'Show the drop down menu for %s', 'societypress' ),
        ]
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
 * Child themes can register additional widget areas (e.g., a three-column
 * footer) in their own functions.php without conflicting with this one.
 */
add_action( 'widgets_init', function () {
    register_sidebar([
        'name'          => esc_html__( 'Sidebar', 'societypress' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Widgets in this area appear on blog posts and archive pages.', 'societypress' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        // <h2>, not <h3>: on a single post the only other heading is the post's
        // <h1>, so <h3> widget titles skipped a level (WCAG 1.3.1). Styling is by
        // the .widget-title class, so the tag change is invisible visually.
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ]);

    /*
     * Footer columns.
     *
     * WHY the parent theme has these at all: every child theme already
     * registered its own three footer columns, so a society running the plain
     * SocietyPress theme was the only one with no way to put anything of its
     * own in the footer — the three columns were fixed in the template. That
     * asymmetry meant advice that worked on Heritage was simply wrong on the
     * parent.
     *
     * WHY the footer still shows its built-in content: leaving a column empty
     * keeps the organization details, quick links and social icons exactly as
     * they were. A column only gives way once a widget is put in it, so a site
     * that never opens this screen looks no different, and a site that does
     * gets the column without losing anything it did not choose to replace.
     */
    $footer_columns = [
        1 => esc_html__( 'Footer Column 1', 'societypress' ),
        2 => esc_html__( 'Footer Column 2', 'societypress' ),
        3 => esc_html__( 'Footer Column 3', 'societypress' ),
    ];

    foreach ( $footer_columns as $number => $label ) {
        register_sidebar([
            'name'          => $label,
            'id'            => 'sp-footer-' . $number,
            'description'   => esc_html__( 'Widgets here replace this column of the footer. Leave it empty to keep what the footer already shows. To add logos of organizations you belong to, use Website → Affiliations instead.', 'societypress' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            // <h3> matches the .footer-heading level the built-in columns use,
            // so a widgetised column does not jump heading levels next to a
            // column that is still showing the default content.
            'before_title'  => '<h3 class="footer-heading">',
            'after_title'   => '</h3>',
        ]);
    }
});

/* =============================================================================
   PRIMARY NAVIGATION — ONE IMPLEMENTATION, EVERY THEME
   =============================================================================
   WHY this exists at all: the depth of a society's menu used to be decided
   separately in every theme's header.php, and the six of them disagreed —
   three levels in the parent, two in Ledger and Parlor, one in Coastline.
   WordPress does not warn about a menu item deeper than the cap; it simply
   renders nothing for it. So a society that built Resources > Research >
   Bexar County > the five record pages saw the record pages vanish, and a
   society that switched from the parent theme to Coastline lost every drop
   down on the site at once, silently, with no way to tell what had happened.

   A menu item somebody added should appear. That is the whole rule, so depth
   is unlimited here and no theme sets it again. Themes still style their
   navigation however they like; they no longer get to decide how much of the
   society's menu is allowed to exist.
   ========================================================================== */

// WHY a class: Walker_Nav_Menu has no non-OOP equivalent in WordPress. This is
// the same forced exception as the plugin's WP_List_Table subclasses.
class SocietyPress_Nav_Walker extends Walker_Nav_Menu {

    /**
     * Open a drop down list, tagged with how deep it is.
     *
     * WHY the level class: styling arbitrary nesting from descendant selectors
     * alone means every rule applies to every level below the one it was
     * written for. A level number in the class lets the stylesheet indent the
     * mobile panel one step per level without JavaScript, and gives child
     * themes a predictable hook rather than a chain of .sub-menu .sub-menu.
     */
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        // $depth is 0 for the first drop down, which reads as level 1.
        $level  = (int) $depth + 1;
        $indent = str_repeat( "\t", $depth );

        $output .= "\n{$indent}<ul class=\"sub-menu sp-submenu-level-{$level}\">\n";
    }

    /**
     * Draw one menu item, telling assistive technology when it opens a menu.
     *
     * WHY aria-haspopup on the link rather than only on the caret button: the
     * caret is added by JavaScript, and a keyboard or screen reader user who
     * arrives before the script does — or with it blocked — still needs to be
     * told the item leads somewhere further. The caret carries aria-expanded
     * once it exists, because it is the control that changes the state.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $before = $output;
        parent::start_el( $output, $item, $depth, $args, $id );

        if ( ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
            return;
        }

        $added = substr( $output, strlen( $before ) );
        $added = preg_replace( '/<a\b/', '<a aria-haspopup="true"', $added, 1 );
        $output = $before . $added;
    }
}

/**
 * Render a society's navigation menu.
 *
 * Every theme calls this instead of wp_nav_menu() for navigation a visitor
 * browses — the header menu, Prairie's sidebar menu. Footer link lists are
 * flat by design and stay on wp_nav_menu().
 *
 * @param array<string,mixed> $args Anything wp_nav_menu() takes. 'depth' is
 *                                  ignored: no theme caps the society's menu.
 */
function sp_nav_menu( array $args = [] ): void {
    unset( $args['depth'] );

    wp_nav_menu( array_merge(
        [
            'container' => false,
            'walker'    => new SocietyPress_Nav_Walker(),
        ],
        $args,
        [ 'depth' => 0 ]
    ) );
}
