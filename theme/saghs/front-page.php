<?php
/**
 * the society Child Theme — Front Page Template
 *
 * WHY: The the society homepage has a unique layout that doesn't match the parent's
 * generic page.php. It needs a hero slider, events section, library callout,
 * and feature cards — all specific to the society's identity and content.
 *
 * Layout pattern (matching reference site):
 * - Hero: full-width photo/slider with burgundy overlay
 * - Events: white background
 * - Library: cream background (alternating)
 * - Features: cream background (Volunteer, Learn, Benefits cards)
 * - Editor content: white background (if any)
 *
 * Content sourcing strategy:
 * - Hero: Uses the page builder hero_slider widget if configured, otherwise
 *   falls back to a static hero with featured image or burgundy background.
 * - Events: Calls sp_render_builder_widget_upcoming_events() to pull live
 *   event data from the SocietyPress events tables.
 * - Library: Static callout section (image + text + link). The actual library
 *   catalog lives on its own page via the library_catalog widget.
 * - Features: Static cards (Volunteer, Learn, Benefits) — content is in the
 *   template because it rarely changes and doesn't need a database.
 *
 * @package the society
 * @since   0.02d
 */

get_header();
?>

<main id="main-content" class="society-front-page">

    <?php
    // =========================================================================
    // HERO SECTION
    // =========================================================================
    // WHY we check for a page builder hero_slider widget first: If the admin
    // has set up a hero slider through the page builder, we use that. It gives
    // them full control over slides, headings, buttons, and autoplay settings.
    // If no hero_slider widget exists on this page, we render a static hero
    // that uses the page's featured image as the background.

    $has_builder_hero = false;

    if ( function_exists( 'sp_render_builder_widget_hero_slider' ) ) {
        $post_id = get_the_ID();
        $widgets = get_post_meta( $post_id, '_sp_builder_widgets', true );

        if ( is_array( $widgets ) ) {
            foreach ( $widgets as $widget ) {
                if ( ( $widget['type'] ?? '' ) === 'hero_slider' ) {
                    sp_render_builder_widget_hero_slider( $widget );
                    $has_builder_hero = true;
                    break;
                }
            }
        }
    }

    if ( ! $has_builder_hero ) :
        // Static hero fallback — uses featured image or a placeholder
        $hero_image = get_the_post_thumbnail_url( get_the_ID(), 'full' );
    ?>
        <div class="society-hero" id="society-hero">
            <div class="society-hero-slide is-active"
                 <?php if ( $hero_image ) : ?>
                 style="background-image: url('<?php echo esc_url( $hero_image ); ?>');"
                 <?php else : ?>
                 style="background: var(--society-burgundy);"
                 <?php endif; ?>
            >
            </div>
            <div class="society-hero-overlay">
                <h2>Welcome to the <?php bloginfo( 'name' ); ?></h2>
                <p><?php echo esc_html( get_bloginfo( 'description', 'display' ) ); ?></p>
            </div>
        </div>
    <?php endif; ?>


    <?php
    // =========================================================================
    // CLASSES / UPCOMING EVENTS SECTION — WHITE BACKGROUND
    // =========================================================================
    // WHY: the society runs regular classes and workshops. Showing the next few events
    // on the front page encourages attendance and gives the site a "living,
    // active" feel. White background follows the hero for contrast.
    ?>
    <section class="society-section society-section-white">
        <div class="society-section-inner">
            <h2 class="society-section-heading">Classes &amp; Events</h2>
            <hr class="society-divider">

            <?php
            if ( function_exists( 'sp_render_builder_widget_upcoming_events' ) ) {
                sp_render_builder_widget_upcoming_events([
                    'count'         => 4,
                    'category_id'   => 0,     // All categories
                    'show_date'     => true,
                    'show_time'     => true,
                    'show_location' => true,
                ]);
            } else {
                echo '<p style="text-align:center;">Events will appear here once the SocietyPress plugin is active.</p>';
            }
            ?>

            <?php
            // Link to the full events page if one exists
            $events_pages = get_pages([ 'meta_key' => '_wp_page_template', 'meta_value' => 'sp-events' ]);
            if ( ! empty( $events_pages ) ) :
            ?>
                <a href="<?php echo esc_url( get_permalink( $events_pages[0]->ID ) ); ?>" class="society-more-link">
                    More Future Events &rarr;
                </a>
            <?php endif; ?>
        </div>
    </section>


    <?php
    // =========================================================================
    // LIBRARY CALLOUT SECTION — CREAM BACKGROUND
    // =========================================================================
    // WHY: The the society library is a major draw — over 18,000 items. This callout
    // gives a visual preview and links to the full catalog. Cream background
    // creates the alternating rhythm matching the reference site.

    // Find the library page so we can link to it
    $library_pages = get_pages([ 'meta_key' => '_wp_page_template', 'meta_value' => 'sp-library' ]);
    $library_url   = ! empty( $library_pages ) ? get_permalink( $library_pages[0]->ID ) : '#';
    ?>
    <section class="society-section society-section-cream">
        <div class="society-section-inner">
            <h2 class="society-section-heading">Our Library</h2>
            <hr class="society-divider">

            <div class="society-library-callout">
                <div class="society-library-image">
                    <!--
                        WHY placeholder: The library foyer photo needs to be uploaded
                        to the Media Library and this src updated. For now, we use
                        a descriptive placeholder so the layout is visible.
                    -->
                    <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/library-placeholder.jpg' ); ?>"
                         alt="the society Library interior"
                         onerror="this.style.display='none'">
                </div>
                <div class="society-library-text">
                    <h3>Over 18,000 Books &amp; Periodicals</h3>
                    <p>
                        Our library houses an extensive collection of genealogical and
                        historical resources, including access to online databases like
                        Ancestry.com, Fold3.com, and more. Members can visit in person
                        or search our catalog online.
                    </p>
                    <a href="<?php echo esc_url( $library_url ); ?>" class="society-btn society-btn-primary">
                        Browse the Catalog
                    </a>
                </div>
            </div>
        </div>
    </section>


    <?php
    // =========================================================================
    // FEATURE CARDS — CREAM BACKGROUND (Volunteer, Learn, Benefits)
    // =========================================================================
    // WHY: These three value propositions sit on a cream background with white
    // cards, matching the reference site's layout. The cards pop visually
    // against the warm background.
    ?>
    <section class="society-section society-section-cream">
        <div class="society-section-inner">
            <div class="society-features-grid">

                <div class="society-feature-card">
                    <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/volunteer-placeholder.jpg' ); ?>"
                         alt="Volunteers at the society"
                         onerror="this.style.background='var(--society-cream)'; this.style.minHeight='160px';">
                    <h3>Volunteer</h3>
                    <p>
                        Share your skills and passion for genealogy. Our volunteers
                        help with scanning documents, planning events, teaching a
                        class, and community outreach.
                    </p>
                    <a href="<?php echo esc_url( home_url( '/volunteer/' ) ); ?>" class="society-btn society-btn-dark">
                        Get Involved
                    </a>
                </div>

                <div class="society-feature-card">
                    <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/learn-placeholder.jpg' ); ?>"
                         alt="the society workshop in progress"
                         onerror="this.style.background='var(--society-cream)'; this.style.minHeight='160px';">
                    <h3>Learn</h3>
                    <p>
                        Attend classes and workshops on genealogical research methods,
                        DNA analysis, computer skills, and more — led by experienced
                        researchers.
                    </p>
                    <?php
                    $events_url = ! empty( $events_pages ) ? get_permalink( $events_pages[0]->ID ) : home_url( '/events/' );
                    ?>
                    <a href="<?php echo esc_url( $events_url ); ?>" class="society-btn society-btn-dark">
                        View Classes
                    </a>
                </div>

                <div class="society-feature-card">
                    <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/benefits-placeholder.jpg' ); ?>"
                         alt="the society library resources"
                         onerror="this.style.background='var(--society-cream)'; this.style.minHeight='160px';">
                    <h3>Member Benefits</h3>
                    <p>
                        Free, unlimited use of the the society Library, access to online
                        databases, monthly newsletters, research assistance, and
                        affiliation with state and national societies.
                    </p>
                    <a href="<?php echo esc_url( home_url( '/join/' ) ); ?>" class="society-btn society-btn-dark">
                        Join Today
                    </a>
                </div>

            </div>
        </div>
    </section>


    <?php
    // =========================================================================
    // PAGE CONTENT (WordPress editor content, if any) — WHITE BACKGROUND
    // =========================================================================
    // WHY: If the admin adds content to the front page via the WordPress editor,
    // we render it here below the custom sections. This gives them a place to
    // add announcements, welcome text, or other content without editing the
    // template file.
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            $content = get_the_content();
            if ( ! empty( trim( $content ) ) ) :
    ?>
                <section class="society-section society-section-white">
                    <div class="society-section-inner">
                        <div class="entry-content">
                            <?php the_content(); ?>
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
