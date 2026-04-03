<?php
/**
 * Heritage Child Theme — Front Page Template
 *
 * WHY: The "Classic" layout archetype opens with a full-width hero section
 * (site name + tagline + CTA), followed by three feature cards pointing
 * visitors to key pages (Events, Library, Join/Membership), and closes with
 * an "About Us" content block pulled from the page editor — so Harold can
 * update it without touching code.
 *
 * The hero section checks for the heritage-hero widget area first. If widgets
 * are active there, they render instead of the default hero. This gives
 * advanced admins full control while keeping the out-of-box experience clean.
 *
 * Feature cards auto-detect SocietyPress pages by their page template meta
 * (_wp_page_template). If a page with template 'sp-events' exists, the Events
 * card links to it. Same for Library and Join. If a page doesn't exist, that
 * card simply doesn't render — no broken links.
 *
 * @package Heritage
 * @since   1.1.0
 */

get_header();
?>

<main id="main-content" class="heritage-front-page">

    <?php
    /* ====================================================================
       HERO SECTION
       WHY widget area check first: If the admin has added widgets to the
       "Front Page Hero" area (registered in functions.php), those take
       priority. This allows advanced customization (custom images, sliders,
       etc.) without modifying the template. If no widgets exist, we render
       the default hero: site name, tagline, and a CTA button.
       ==================================================================== */
    ?>
    <section class="heritage-hero">
        <?php if ( is_active_sidebar( 'heritage-hero' ) ) : ?>
            <div class="heritage-hero-widgets">
                <?php dynamic_sidebar( 'heritage-hero' ); ?>
            </div>
        <?php else : ?>
            <div class="heritage-hero-inner">
                <h1 class="heritage-hero-title"><?php bloginfo( 'name' ); ?></h1>

                <?php
                $tagline = get_bloginfo( 'description', 'display' );
                if ( $tagline ) :
                ?>
                    <p class="heritage-hero-subtitle"><?php echo esc_html( $tagline ); ?></p>
                <?php endif; ?>

                <?php
                /* WHY join page detection: The CTA button should link to the
                   Join page if it exists (created by SocietyPress on activation).
                   We look for a published page with slug "join" — the standard
                   slug the plugin uses. If it doesn't exist, we fall back to the
                   events page, then the home URL. The button always goes somewhere
                   useful. */
                $join_page = get_page_by_path( 'join' );
                if ( $join_page && 'publish' === $join_page->post_status ) {
                    $cta_url  = get_permalink( $join_page );
                    $cta_text = esc_html__( 'Join Us', 'heritage' );
                } else {
                    $cta_url  = home_url( '/' );
                    $cta_text = esc_html__( 'Learn More', 'heritage' );
                }
                ?>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="heritage-hero-cta">
                    <?php echo $cta_text; ?>
                </a>
            </div>
        <?php endif; ?>
    </section>


    <?php
    /* ====================================================================
       FEATURE CARDS SECTION
       WHY auto-detection: Instead of hardcoding links that might break if
       the admin renames or deletes a page, we query for pages that have
       specific SocietyPress page templates assigned. If a page with
       template "sp-events" exists and is published, we show the Events
       card. If not, we skip it. This means the cards always reflect the
       actual site structure.

       Each card gets an SVG icon, a title, a short description, and a
       link. The descriptions are generic enough to work for any society.
       ==================================================================== */
    $feature_cards = [];

    // Events card — looks for a page using the sp-events template
    $events_pages = get_pages( [
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'sp-events',
        'number'     => 1,
    ] );
    if ( ! empty( $events_pages ) ) {
        $feature_cards[] = [
            'title' => esc_html__( 'Events', 'heritage' ),
            'desc'  => esc_html__( 'Browse upcoming meetings, workshops, and programs.', 'heritage' ),
            'url'   => get_permalink( $events_pages[0]->ID ),
            /* Calendar icon — represents events/scheduling */
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        ];
    }

    // Library card — looks for a page using the sp-library-catalog template
    $library_pages = get_pages( [
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'sp-library-catalog',
        'number'     => 1,
    ] );
    if ( ! empty( $library_pages ) ) {
        $feature_cards[] = [
            'title' => esc_html__( 'Library', 'heritage' ),
            'desc'  => esc_html__( 'Search our catalog of books, periodicals, and research materials.', 'heritage' ),
            'url'   => get_permalink( $library_pages[0]->ID ),
            /* Book icon — represents the library */
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        ];
    }

    // Membership / Join card — looks for the join page by slug
    $join_page_card = get_page_by_path( 'join' );
    if ( $join_page_card && 'publish' === $join_page_card->post_status ) {
        $feature_cards[] = [
            'title' => esc_html__( 'Membership', 'heritage' ),
            'desc'  => esc_html__( 'Become a member and support our mission of preserving history.', 'heritage' ),
            'url'   => get_permalink( $join_page_card ),
            /* Users icon — represents membership/community */
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        ];
    }
    ?>

    <?php if ( ! empty( $feature_cards ) ) : ?>
    <section class="heritage-features">
        <div class="heritage-features-inner">
            <h2 class="heritage-features-heading"><?php esc_html_e( 'Explore Our Society', 'heritage' ); ?></h2>

            <div class="heritage-features-grid">
                <?php foreach ( $feature_cards as $card ) : ?>
                <div class="heritage-card">
                    <div class="heritage-card-icon">
                        <?php
                        /* WHY echo without escaping: These SVG strings are
                           hardcoded above — not user input. Escaping would
                           destroy the SVG markup. */
                        echo $card['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                    </div>
                    <h3 class="heritage-card-title"><?php echo $card['title']; ?></h3>
                    <p class="heritage-card-desc"><?php echo $card['desc']; ?></p>
                    <a href="<?php echo esc_url( $card['url'] ); ?>" class="heritage-card-link">
                        <?php
                        printf(
                            /* translators: %s: feature name (Events, Library, Membership) */
                            esc_html__( 'Visit %s', 'heritage' ),
                            $card['title']
                        );
                        ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>


    <?php
    /* ====================================================================
       ABOUT US CONTENT SECTION
       WHY the_content(): This pulls whatever Harold has written in the
       page editor for the front page. It gives the admin full control over
       this section's text without touching template files. If the page has
       no content, the entire section is skipped — no empty box.

       We use get_the_content() + apply_filters to check for emptiness
       before rendering, so we can conditionally skip the section wrapper.
       ==================================================================== */
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            $page_content = get_the_content();

            /* WHY trim check: get_the_content() can return whitespace or
               empty paragraph tags from the block editor. We strip tags
               and trim to check if there's actual readable content. */
            if ( trim( wp_strip_all_tags( $page_content ) ) ) :
    ?>
    <section class="heritage-about">
        <div class="heritage-about-inner">
            <h2 class="heritage-about-heading"><?php esc_html_e( 'About Us', 'heritage' ); ?></h2>
            <div class="heritage-about-content">
                <?php
                /* WHY the_content() instead of echo: the_content() applies
                   all WordPress content filters (shortcodes, oEmbed, blocks,
                   wpautop) which get_the_content() does not. We called
                   get_the_content() above only to check for emptiness. */
                the_content();
                ?>
            </div>
        </div>
    </section>
    <?php
            endif;
        endwhile;
    endif;
    ?>

</main>

<?php get_footer(); ?>
