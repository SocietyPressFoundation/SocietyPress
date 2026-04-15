<?php
/**
 * getsocietypress Theme Functions
 *
 * Sets up the theme: enqueues styles and fonts, registers nav menus,
 * disables the block editor, and configures theme supports.
 *
 * This is a classic PHP theme — no Gutenberg, no FSE, no theme.json.
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme Setup
 *
 * Runs on 'after_setup_theme' — registers all the core features WordPress
 * needs to know about: title tag, thumbnails, menus, custom logo, HTML5
 * markup, and content width.
 */
function gsp_theme_setup() {
    /* Let WordPress manage the document <title> tag */
    add_theme_support( 'title-tag' );

    /* Enable featured images on posts and pages */
    add_theme_support( 'post-thumbnails' );

    /* Custom logo support for the site header */
    add_theme_support( 'custom-logo', array(
        'height'      => 72,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    /* HTML5 markup for search forms, comments, galleries, etc. */
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );

    /* Register navigation menus */
    register_nav_menus( array(
        'primary' => __( 'Primary Navigation', 'getsocietypress' ),
        'footer'  => __( 'Footer Navigation', 'getsocietypress' ),
    ) );

    /* Set the content width for embeds and images */
    if ( ! isset( $GLOBALS['content_width'] ) ) {
        $GLOBALS['content_width'] = 1200;
    }
}
add_action( 'after_setup_theme', 'gsp_theme_setup' );


/**
 * Enqueue Styles and Scripts
 *
 * Loads the Inter font from Google Fonts, the theme stylesheet, and
 * the theme's vanilla JS file. No jQuery, no frameworks — ever.
 */
function gsp_enqueue_assets() {
    /* Google Fonts — Inter, weights 300–800 */
    wp_enqueue_style(
        'gsp-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        array(),
        null
    );

    /* Main theme stylesheet */
    wp_enqueue_style(
        'gsp-style',
        get_stylesheet_uri(),
        array( 'gsp-google-fonts' ),
        wp_get_theme()->get( 'Version' )
    );

    /* Theme JavaScript — vanilla JS, no jQuery dependency */
    wp_enqueue_script(
        'gsp-theme-js',
        get_template_directory_uri() . '/js/theme.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'gsp_enqueue_assets' );


/**
 * Disable the Block Editor (Gutenberg) Entirely
 *
 * SocietyPress uses the classic editor. The block editor, Full Site Editing,
 * and theme.json are all prohibited per CLAUDE.md. These filters ensure
 * the block editor never loads for any post type.
 */
add_filter( 'use_block_editor_for_post', '__return_false' );
add_filter( 'use_block_editor_for_post_type', '__return_false' );

/* Remove block-related inline CSS that WordPress injects by default */
function gsp_remove_block_css() {
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-blocks-style' );
    wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'gsp_remove_block_css', 100 );


/**
 * Disable WordPress Emojis
 *
 * We don't need the emoji scripts and styles — they add extra HTTP
 * requests for functionality the site doesn't use.
 */
function gsp_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'gsp_disable_emojis' );


/**
 * Clean Up wp_head
 *
 * Remove unnecessary meta tags and links that WordPress outputs by default.
 * Keeps the markup clean and reduces information leakage.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );


/**
 * Custom Nav Walker — Bare Links
 *
 * Strips the default <ul>/<li> wrapper from wp_nav_menu() and outputs
 * plain <a> tags. This keeps the nav markup identical to what we had
 * when the links were hardcoded, so the existing CSS works unchanged.
 * Also adds a "current" class to the active page link.
 */
class GSP_Nav_Walker extends Walker_Nav_Menu {

    /**
     * Skip the <li> open tag entirely — we only want <a> tags.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $item_classes = (array) $item->classes;
        $link_classes = array();

        /* Carry over WordPress built-in current-page CSS classes so theme CSS can target them */
        if ( in_array( 'current-menu-item', $item_classes, true ) ) {
            $link_classes[] = 'current';
            $link_classes[] = 'current-menu-item';
        }
        if ( in_array( 'current-menu-ancestor', $item_classes, true ) ) {
            $link_classes[] = 'current-menu-ancestor';
        }
        if ( in_array( 'current-menu-parent', $item_classes, true ) ) {
            $link_classes[] = 'current-menu-parent';
        }

        $class_attr = $link_classes ? ' class="' . implode( ' ', $link_classes ) . '"' : '';
        $output .= '<a href="' . esc_url( $item->url ) . '"' . $class_attr . '>' . esc_html( $item->title ) . '</a>';
    }

    /* We don't need closing </li> tags since we never opened them */
    public function end_el( &$output, $item, $depth = 0, $args = null ) {}

    /* We don't need <ul> wrappers for submenus (depth is 1 anyway) */
    public function start_lvl( &$output, $depth = 0, $args = null ) {}
    public function end_lvl( &$output, $depth = 0, $args = null ) {}
}


/**
 * Customizer — Announcement Bar
 *
 * Adds a "Site Announcement" section to Appearance > Customize with:
 * - Enable/disable toggle
 * - Text field for the announcement message
 *
 * When disabled (or empty), the bar simply doesn't render in header.php.
 */
function gsp_customizer_announcement( $wp_customize ) {

    /* Section */
    $wp_customize->add_section( 'gsp_announcement', array(
        'title'    => __( 'Site Announcement', 'getsocietypress' ),
        'priority' => 30,
    ) );

    /* Enable toggle */
    $wp_customize->add_setting( 'gsp_announce_enabled', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ) );

    $wp_customize->add_control( 'gsp_announce_enabled', array(
        'label'   => __( 'Show announcement bar', 'getsocietypress' ),
        'section' => 'gsp_announcement',
        'type'    => 'checkbox',
    ) );

    /* Hero badge text — the small pill above the hero headline */
    $wp_customize->add_setting( 'gsp_hero_badge', array(
        'default'           => 'Open Source & Free',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'gsp_hero_badge', array(
        'label'       => __( 'Hero badge text', 'getsocietypress' ),
        'description' => __( 'The small label above the homepage headline (e.g. "Open Source & Free"). Leave blank to hide.', 'getsocietypress' ),
        'section'     => 'gsp_announcement',
        'type'        => 'text',
    ) );

    /* Announcement text */
    $wp_customize->add_setting( 'gsp_announce_text', array(
        'default'           => 'SocietyPress v0.01d is here — a free, open-source platform for genealogical societies.',
        'sanitize_callback' => 'wp_kses_post',
    ) );

    $wp_customize->add_control( 'gsp_announce_text', array(
        'label'   => __( 'Announcement text', 'getsocietypress' ),
        'section' => 'gsp_announcement',
        'type'    => 'textarea',
    ) );
}
add_action( 'customize_register', 'gsp_customizer_announcement' );


/**
 * Custom Excerpt Length
 *
 * Keep excerpts concise for the "Latest Updates" cards on the homepage.
 */
function gsp_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'gsp_excerpt_length' );


/**
 * Current SocietyPress plugin version.
 *
 * Pulls the latest version from the raw plugin file on GitHub and caches it
 * for 12 hours. Falls back to the last known shipped version if the request
 * fails — the fallback MUST be kept current when a release ships so the site
 * never shows a stale version while GitHub is unreachable.
 *
 * Returns the version number as a plain string (e.g. "1.0.16"), no "v" prefix.
 */
function gsp_get_sp_version() {
    $ver = get_transient( 'sp_latest_version' );
    if ( $ver ) {
        return $ver;
    }
    $resp = wp_remote_get(
        'https://api.github.com/repos/societypress/SocietyPress/contents/Code/plugin/societypress.php',
        array(
            'timeout' => 5,
            'headers' => array( 'Accept' => 'application/vnd.github.v3.raw' ),
        )
    );
    if ( ! is_wp_error( $resp ) && preg_match( '/Version:\s*([\d.]+\w*)/', wp_remote_retrieve_body( $resp ), $m ) ) {
        $ver = $m[1];
    } else {
        // Last known shipped version — update on every release.
        $ver = '1.0.19';
    }
    set_transient( 'sp_latest_version', $ver, 12 * HOUR_IN_SECONDS );
    return $ver;
}


/**
 * Download URL for the latest SocietyPress bundle.
 *
 * The bundle lives at /downloads/societypress-latest.zip on this domain and
 * is rebuilt by scripts/deploy.sh bundle. Centralized here so every page can
 * link to the same URL without hardcoding.
 */
function gsp_get_download_url() {
    return home_url( '/downloads/societypress-latest.zip' );
}


/**
 * Custom Excerpt "Read More" Text
 *
 * Replace the default "[...]" with something cleaner.
 */
function gsp_excerpt_more( $more ) {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'gsp_excerpt_more' );
