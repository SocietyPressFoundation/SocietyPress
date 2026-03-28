<?php
/**
 * SAGHS Child Theme — Front Page Template
 *
 * WHY: Renders the Home page using page builder widgets, giving Harold full
 * control over every section (hero slider, events, feature cards, map) from
 * the Page Builder admin — no template editing needed.
 *
 * This template still exists as a child theme override (rather than letting
 * the parent's sp-builder template handle it) so we can:
 * - Skip the page title on the front page (no "Home" heading above the hero)
 * - Apply SAGHS-specific section wrappers if needed in the future
 *
 * @package SAGHS
 * @since   0.01d
 */

// WHY: The builder template normally hooks sp_builder_frontend_styles into
// wp_head, but our front-page.php bypasses that template. We need to call
// the styles function directly so the widget CSS actually loads.
// WHY has_action guard: Prevents duplicate registration if the parent template
// or another hook already added it — double-firing would output the CSS twice.
if ( function_exists( 'sp_builder_frontend_styles' ) && ! has_action( 'wp_head', 'sp_builder_frontend_styles' ) ) {
    add_action( 'wp_head', 'sp_builder_frontend_styles' );
}

// WHY: Same bypass issue as styles — the builder template hooks
// sp_builder_frontend_scripts into wp_footer for the contact form AJAX handler
// and any other widget JS. Without this, those scripts never load on the
// front page. Guard with has_action() to prevent duplicate output.
if ( function_exists( 'sp_builder_frontend_scripts' ) && ! has_action( 'wp_footer', 'sp_builder_frontend_scripts' ) ) {
    add_action( 'wp_footer', 'sp_builder_frontend_scripts' );
}

get_header();
?>

<main class="saghs-front-page">
    <?php
    // Render all page builder widgets — hero slider, events, feature cards,
    // map, and anything else Harold adds through the builder.
    // WHY get_queried_object_id() instead of get_the_ID(): On a static front
    // page, get_the_ID() can return the wrong ID if called after a secondary
    // WP_Query (e.g., from a widget or sidebar). get_queried_object_id() always
    // returns the main queried page ID regardless of loop state.
    if ( function_exists( 'sp_render_builder_widgets' ) ) {
        sp_render_builder_widgets( get_queried_object_id() );
    }
    ?>
</main>

<?php get_footer(); ?>
