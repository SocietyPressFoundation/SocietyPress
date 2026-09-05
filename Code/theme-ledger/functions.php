<?php
/**
 * Ledger Child Theme — Functions
 *
 * WHY: This file bootstraps the Ledger child theme:
 * 1. On activation, pushes Ledger's color palette and font into the plugin's
 *    design settings so the plugin's override CSS outputs OUR values.
 * 2. Loads Source Sans 3 from Google Fonts (Ledger's formal sans-serif)
 * 3. Enqueues the child stylesheet for layout overrides and components
 * 4. Registers 3 footer widget areas for the 3-column footer layout
 * 5. Provides a nav walker for 2-level dropdown navigation
 * 6. Enqueues Ledger's JS for hamburger toggle and dropdown behavior
 *
 * @package Ledger
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'LEDGER_THEME_VERSION' ) ) {
    define( 'LEDGER_THEME_VERSION', '1.5.32' );
}


// ============================================================================
// THEME ACTIVATION — PUSH PALETTE TO PLUGIN SETTINGS
// ============================================================================

/**
 * Push Ledger's palette into the plugin's design settings on activation.
 *
 * WHY: The SocietyPress plugin outputs a :root {} block AFTER all theme
 *      stylesheets with the saved design settings. If we only set --sp-*
 *      vars in our style.css, the plugin's override stomps them. By writing
 *      our colors into the settings option, the plugin outputs OUR palette
 *      and the Design settings page shows the correct values too.
 */
add_action( 'after_switch_theme', function () {
    // Read the option directly, not the plugin's sp_settings() wrapper: the
    // plugin may be inactive when a theme is switched, so its functions can't
    // be assumed to exist here.
    $settings = get_option( 'societypress_settings', [] );

    $settings['design_color_primary']       = '#2C2C2C';
    $settings['design_color_primary_hover'] = '#7B2D3B';
    $settings['design_color_accent']        = '#7B2D3B';
    $settings['design_color_header_bg']     = '#2C2C2C';
    $settings['design_color_header_text']   = '#F8F5F0';
    $settings['design_color_footer_bg']     = '#2C2C2C';
    $settings['design_color_footer_text']   = '#F8F5F0';
    $settings['design_font_body']           = 'source-sans';
    $settings['design_font_heading']        = 'source-sans';

    update_option( 'societypress_settings', $settings );
} );


// ============================================================================
// ENQUEUE STYLES & SCRIPTS
// ============================================================================

add_action( 'wp_enqueue_scripts', function () {

    // Google Fonts — Source Sans 3 with italic support
    wp_enqueue_style(
        'ledger-google-fonts',
        'https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap',
        [],
        null
    );

    // Child stylesheet — loads after parent for layout overrides and components.
    wp_enqueue_style(
        'ledger-style',
        get_stylesheet_uri(),
        [ 'societypress-style', 'ledger-google-fonts' ],
        LEDGER_THEME_VERSION
    );

    // WHY dashicons: The card grid uses dashicons for section icons. WordPress
    // only loads dashicons on the admin side by default. We need them on the
    // frontend for the card icons to render.
    wp_enqueue_style( 'dashicons' );

    // Ledger JavaScript — hamburger menu toggle, dropdown nav behavior.
    // Loaded in footer so it doesn't block page rendering.
    wp_enqueue_script(
        'ledger-script',
        get_stylesheet_directory_uri() . '/js/ledger.js',
        [],
        LEDGER_THEME_VERSION,
        true
    );
} );


// ============================================================================
// REGISTER FOOTER WIDGET AREAS
// ============================================================================

/**
 * Register three footer widget areas for the 3-column footer layout.
 *
 * WHY three areas: The dashboard footer pattern uses About / Navigation /
 * Contact as its three columns. Widget areas give the admin full control
 * over what appears in each column without editing template files. If no
 * widgets are added, the footer.php template renders sensible defaults
 * so the footer is never empty.
 */
add_action( 'widgets_init', function () {

    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 1 (About)', 'ledger' ),
        'id'            => 'ledger-footer-1',
        'description'   => esc_html__( 'First footer column — typically organization info or mission.', 'ledger' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 2 (Navigation)', 'ledger' ),
        'id'            => 'ledger-footer-2',
        'description'   => esc_html__( 'Second footer column — typically quick links or navigation.', 'ledger' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    register_sidebar( [
        'name'          => esc_html__( 'Footer Column 3 (Contact)', 'ledger' ),
        'id'            => 'ledger-footer-3',
        'description'   => esc_html__( 'Third footer column — typically contact info or hours.', 'ledger' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );
} );


// ============================================================================
// CUSTOM NAV WALKER — LEDGER'S MOBILE TOGGLE, ON TOP OF THE SHARED WALKER
// ============================================================================

/**
 * Ledger's walker adds a mobile submenu toggle button to items with children.
 *
 * WHY it extends the parent theme's walker rather than WordPress's: the level
 * classes and aria-haspopup that make arbitrary nesting readable and navigable
 * are defined once, in SocietyPress_Nav_Walker. A child theme that re-derived
 * them from Walker_Nav_Menu would drift out of step with the parent the first
 * time either changed — which is exactly how the six themes ended up with five
 * different menu depths.
 *
 * WHY the declaration is deferred to after_setup_theme: WordPress loads a child
 * theme's functions.php before its parent's, so SocietyPress_Nav_Walker does not
 * exist yet while this file is being read. Declaring the subclass inside a hook
 * that fires after both files are loaded is what makes the extension legal.
 *
 * On desktop: The toggle button is hidden via CSS. Dropdowns use :hover and
 * :focus-within for keyboard accessibility.
 *
 * On mobile: The toggle button is visible. JS adds/removes .is-open on the
 * parent <li> to show/hide the submenu.
 */
add_action( 'after_setup_theme', function () {

// WHY a class: Walker_Nav_Menu has no non-OOP equivalent in WordPress. This
// extension is the same justified exception as the plugin's WP_List_Table and
// WP_Widget subclasses — the API forces it.
class Ledger_Nav_Walker extends SocietyPress_Nav_Walker {

    /**
     * Starts an element (a single menu item).
     *
     * WHY we override this: To inject a toggle button inside <li> elements
     * that have children. The button is invisible on desktop (CSS) and
     * functional on mobile (JS toggles .is-open class on click).
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param WP_Post  $item   Menu item data object.
     * @param int      $depth  Depth of menu item. 0 = top level.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     * @param int      $id     Current item ID.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        // Let WordPress build the standard <li> and <a> markup
        parent::start_el( $output, $item, $depth, $args, $id );

        // If this item has children, add a toggle button for mobile
        if ( in_array( 'menu-item-has-children', $item->classes, true ) ) {
            $output .= '<button class="ledger-submenu-toggle" aria-expanded="false" aria-label="'
                     . esc_attr__( 'Toggle submenu', 'ledger' ) . '">'
                     . '<span class="ledger-toggle-icon">&#9662;</span>'
                     . '</button>';
        }
    }
}

} );
