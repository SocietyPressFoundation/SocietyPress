<?php
/**
 * Ledger Child Theme — Front Page (Dashboard Layout)
 *
 * WHY: The Ledger theme uses a "dashboard" homepage pattern — hero banner at
 * top, then a card grid pointing to the site's key sections, optionally an
 * upcoming events section, and a CTA band at the bottom. This is the modern
 * SaaS/NGS-style layout that looks professional and gets visitors to content
 * quickly.
 *
 * The card grid is SMART: it auto-detects which SocietyPress page templates
 * are actually assigned to pages on this site and builds cards for them. If
 * no SP pages exist, it falls back to a curated list of common sections so
 * the homepage is never empty.
 *
 * @package Ledger
 * @since   1.1.0
 */

get_header();

// ============================================================================
// HERO SECTION
// ============================================================================
// WHY: The hero is the first thing visitors see. Large text + CTA button
// immediately communicates what this site is and what to do next.
// ============================================================================

$site_name = get_bloginfo( 'name' );
$site_desc = get_bloginfo( 'description', 'display' );

// WHY: Look for a join page to link the hero CTA button. The SocietyPress
// join form uses the [societypress_join] shortcode, so we search for a page
// containing it. Fall back to the membership directory or home page.
$join_page_url = home_url( '/' );
$join_pages = get_posts( [
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    's'              => '[societypress_join',
    'fields'         => 'ids',
] );
if ( ! empty( $join_pages ) ) {
    $join_page_url = get_permalink( $join_pages[0] );
}
?>

<div id="main-content">

    <!-- Hero banner -->
    <section class="ledger-hero">
        <div class="ledger-hero-content">
            <h1><?php echo esc_html( $site_name ); ?></h1>
            <?php if ( $site_desc ) : ?>
                <p><?php echo esc_html( $site_desc ); ?></p>
            <?php else : ?>
                <p><?php esc_html_e( 'Preserving our heritage, connecting our community, and building a legacy for future generations.', 'ledger' ); ?></p>
            <?php endif; ?>
            <a href="<?php echo esc_url( $join_page_url ); ?>" class="ledger-hero-btn">
                <?php esc_html_e( 'Join Our Society', 'ledger' ); ?>
            </a>
        </div>
    </section>

    <?php
    // ============================================================================
    // CARD GRID — AUTO-DETECT SP PAGES
    // ============================================================================
    // WHY auto-detect: Rather than hardcoding a fixed set of cards, we look at
    // which SocietyPress page templates are actually assigned to published pages
    // on this site. This means the homepage automatically reflects whatever
    // modules the admin has enabled and configured — no manual card management.
    //
    // If no SP pages are found (fresh install, or admin hasn't created pages yet),
    // we show a fallback set of cards with the most common sections so the
    // homepage looks complete even before setup is finished.
    // ============================================================================

    // Card definitions: template slug => card metadata
    // WHY this map: Each SP page template needs an icon, a human-readable title,
    // and a short description for the card. The icon SVGs are inline so there's
    // no external dependency — they render instantly and scale cleanly.
    $card_defs = [
        'sp-events' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
            'title' => __( 'Events', 'ledger' ),
            'desc'  => __( 'Browse upcoming meetings, workshops, and community gatherings.', 'ledger' ),
        ],
        'sp-calendar' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>',
            'title' => __( 'Calendar', 'ledger' ),
            'desc'  => __( 'View the full calendar of society events and activities.', 'ledger' ),
        ],
        'sp-directory' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
            'title' => __( 'Member Directory', 'ledger' ),
            'desc'  => __( 'Find and connect with fellow society members.', 'ledger' ),
        ],
        'sp-library-catalog' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>',
            'title' => __( 'Library Catalog', 'ledger' ),
            'desc'  => __( 'Search our collection of books, periodicals, and research materials.', 'ledger' ),
        ],
        'sp-newsletter-archive' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
            'title' => __( 'Newsletters', 'ledger' ),
            'desc'  => __( 'Read past issues of our society newsletter.', 'ledger' ),
        ],
        'sp-resources' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>',
            'title' => __( 'Resources', 'ledger' ),
            'desc'  => __( 'Explore curated links to genealogical databases and research tools.', 'ledger' ),
        ],
        'sp-records' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
            'title' => __( 'Records', 'ledger' ),
            'desc'  => __( 'Search cemetery, census, and other genealogical record collections.', 'ledger' ),
        ],
        'sp-groups' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
            'title' => __( 'Interest Groups', 'ledger' ),
            'desc'  => __( 'Join special interest groups focused on specific research topics.', 'ledger' ),
        ],
        'sp-store' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>',
            'title' => __( 'Store', 'ledger' ),
            'desc'  => __( 'Browse publications, merchandise, and other items for sale.', 'ledger' ),
        ],
        'sp-documents' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13,2 13,9 20,9"/></svg>',
            'title' => __( 'Documents', 'ledger' ),
            'desc'  => __( 'Access meeting minutes, bylaws, and other society documents.', 'ledger' ),
        ],
        'sp-help-requests' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            'title' => __( 'Research Help', 'ledger' ),
            'desc'  => __( 'Submit research questions and get help from fellow members.', 'ledger' ),
        ],
        'sp-search' => [
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
            'title' => __( 'Search', 'ledger' ),
            'desc'  => __( 'Search across all sections of the site at once.', 'ledger' ),
        ],
    ];

    // WHY: Query all published pages that have an SP template assigned.
    // This gives us the actual pages the admin has set up, so cards link
    // to real content rather than placeholder URLs.
    $sp_pages = get_posts( [
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => '_wp_page_template',
                'value'   => 'sp-',
                'compare' => 'LIKE',
            ],
        ],
    ] );

    // Build a map of template => page object for found pages.
    // WHY: Multiple pages could use the same template (unlikely but possible).
    // We only want the first one for each template type.
    $found_templates = [];
    foreach ( $sp_pages as $sp_page ) {
        $tpl = get_page_template_slug( $sp_page->ID );
        if ( $tpl && ! isset( $found_templates[ $tpl ] ) ) {
            $found_templates[ $tpl ] = $sp_page;
        }
    }

    // Build the cards array from found pages
    $cards = [];
    foreach ( $found_templates as $tpl_slug => $page_obj ) {
        if ( isset( $card_defs[ $tpl_slug ] ) ) {
            $def = $card_defs[ $tpl_slug ];
            $cards[] = [
                'icon'  => $def['icon'],
                'title' => $def['title'],
                'desc'  => $def['desc'],
                'url'   => get_permalink( $page_obj->ID ),
            ];
        }
    }

    // WHY fallback: If no SP pages are found (fresh install, or pages not yet
    // created), show a curated set of cards for the most common sections so the
    // homepage has visual content. These link to the homepage since the actual
    // pages don't exist yet — once the admin creates pages, the auto-detected
    // cards replace these.
    if ( empty( $cards ) ) {
        $fallback_templates = [ 'sp-events', 'sp-directory', 'sp-library-catalog', 'sp-newsletter-archive', 'sp-resources', 'sp-documents' ];
        foreach ( $fallback_templates as $tpl_slug ) {
            if ( isset( $card_defs[ $tpl_slug ] ) ) {
                $def = $card_defs[ $tpl_slug ];
                $cards[] = [
                    'icon'  => $def['icon'],
                    'title' => $def['title'],
                    'desc'  => $def['desc'],
                    'url'   => home_url( '/' ),
                ];
            }
        }
    }

    // Don't show search in the card grid — it's in the header already.
    // Also skip calendar if events is already present (they're related).
    $shown_templates = array_column( $cards, 'title' );
    $cards = array_filter( $cards, function( $card ) use ( $shown_templates ) {
        // Always skip Search card — search is in the header
        if ( $card['title'] === __( 'Search', 'ledger' ) ) {
            return false;
        }
        // Skip Calendar card if Events card is already showing
        if ( $card['title'] === __( 'Calendar', 'ledger' )
             && in_array( __( 'Events', 'ledger' ), $shown_templates, true ) ) {
            return false;
        }
        return true;
    } );
    ?>

    <?php if ( ! empty( $cards ) ) : ?>
    <!-- Card grid section -->
    <section class="ledger-cards-section">
        <h2 class="ledger-cards-section-title"><?php esc_html_e( 'Explore Our Society', 'ledger' ); ?></h2>
        <div class="ledger-card-grid">
            <?php foreach ( $cards as $card ) : ?>
            <a href="<?php echo esc_url( $card['url'] ); ?>" class="ledger-card">
                <div class="ledger-card-icon">
                    <?php echo $card['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — static SVG markup ?>
                </div>
                <h3><?php echo esc_html( $card['title'] ); ?></h3>
                <p><?php echo esc_html( $card['desc'] ); ?></p>
                <span class="ledger-card-link"><?php esc_html_e( 'Explore', 'ledger' ); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php
    // ============================================================================
    // UPCOMING EVENTS SECTION (optional)
    // ============================================================================
    // WHY: If the Events module is enabled and there are upcoming events, show
    // the next 3 as a preview. This gives the homepage dynamic, timely content
    // that encourages visitors to come back. If no events exist or the module
    // is disabled, this section silently skips.
    // ============================================================================

    if ( function_exists( 'sp_module_enabled' ) && sp_module_enabled( 'events' ) ) :
        global $wpdb;
        $events_table = $wpdb->prefix . 'sp_events';

        // WHY: Check if the events table exists before querying. On a fresh
        // install, the tables might not be created yet.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $events_table )
        );

        if ( $table_exists ) :
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $upcoming_events = $wpdb->get_results( $wpdb->prepare(
                "SELECT id, title, event_date, location
                 FROM {$events_table}
                 WHERE event_date >= %s
                 AND status = 'published'
                 ORDER BY event_date ASC
                 LIMIT 3",
                wp_date( 'Y-m-d' )
            ) );

            if ( ! empty( $upcoming_events ) ) :
                // WHY: Find the events page URL so the "View All" link goes
                // to the right place.
                $events_page_url = home_url( '/' );
                foreach ( $found_templates as $tpl_slug => $page_obj ) {
                    if ( $tpl_slug === 'sp-events' ) {
                        $events_page_url = get_permalink( $page_obj->ID );
                        break;
                    }
                }
    ?>
    <!-- Upcoming Events -->
    <section class="ledger-events-section">
        <h2 class="ledger-events-section-title"><?php esc_html_e( 'Upcoming Events', 'ledger' ); ?></h2>
        <div class="ledger-events-grid">
            <?php foreach ( $upcoming_events as $event ) :
                $event_ts = strtotime( $event->event_date );
            ?>
            <div class="ledger-event-card">
                <div class="ledger-event-date">
                    <span class="ledger-event-date-month"><?php echo esc_html( wp_date( 'M', $event_ts ) ); ?></span>
                    <span class="ledger-event-date-day"><?php echo esc_html( wp_date( 'j', $event_ts ) ); ?></span>
                </div>
                <div class="ledger-event-info">
                    <h4><?php echo esc_html( $event->title ); ?></h4>
                    <?php if ( ! empty( $event->location ) ) : ?>
                        <p><?php echo esc_html( $event->location ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="ledger-events-view-all">
            <a href="<?php echo esc_url( $events_page_url ); ?>" class="ledger-card-link"><?php esc_html_e( 'View All Events', 'ledger' ); ?></a>
        </p>
    </section>
    <?php
            endif; // has events
        endif; // table exists
    endif; // module enabled
    ?>

    <?php
    // ============================================================================
    // CTA BAND — JOIN CALL TO ACTION
    // ============================================================================
    // WHY: A clear call to action near the bottom of the page converts casual
    // visitors into members. The contrasting burgundy background creates visual
    // urgency and breaks up the page rhythm.
    // ============================================================================
    ?>
    <section class="ledger-cta-band">
        <h2><?php esc_html_e( 'Join Our Society', 'ledger' ); ?></h2>
        <p><?php esc_html_e( 'Become a member and gain access to our full library, events, member directory, and more.', 'ledger' ); ?></p>
        <a href="<?php echo esc_url( $join_page_url ); ?>" class="ledger-cta-btn">
            <?php esc_html_e( 'Become a Member', 'ledger' ); ?>
        </a>
    </section>

</div><!-- #main-content -->

<?php get_footer(); ?>
