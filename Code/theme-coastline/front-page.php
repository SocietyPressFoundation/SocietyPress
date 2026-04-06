<?php
/**
 * Coastline Child Theme — Front Page Template (Magazine Homepage)
 *
 * WHY: The magazine homepage is the heart of the Coastline archetype. It uses
 * the same two-column layout (content + sidebar) as every other page, but
 * the content area is structured for a homepage: welcome message from the
 * page editor content at top, followed by upcoming events.
 *
 * The sidebar carries persistent info: newsletter covers, quick links, and
 * social links — giving visitors an at-a-glance summary of what the society
 * has going on.
 *
 * Content sourcing strategy:
 * - Welcome: WordPress editor content from the front page (whatever the admin
 *   types in the page editor shows here).
 * - Events: Live data from the SocietyPress events tables via the plugin's
 *   sp_render_builder_widget_upcoming_events() function.
 * - Sidebar: Uses the coastline-sidebar widget area if configured, otherwise
 *   shows defaults (see sidebar.php).
 *
 * @package Coastline
 * @since   1.1.0
 */

get_header();
?>

<div id="main-content" class="coastline-layout">

    <!-- Main content area: welcome + events -->
    <main class="coastline-content">

        <?php
        // =====================================================================
        // WELCOME / HERO SECTION
        // WHY: The front page's WordPress editor content becomes the welcome
        // message. This gives the admin full control over the greeting without
        // touching template files. If there's a featured image, we show it
        // above the content for visual interest.
        // =====================================================================
        ?>
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

            <div class="coastline-welcome">

                <?php if ( has_post_thumbnail() ) : ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
                <?php endif; ?>

                <?php
                $content = get_the_content();
                if ( ! empty( trim( $content ) ) ) :
                ?>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                <?php else : ?>
                    <!-- Default welcome when no editor content exists yet.
                         WHY: The homepage shouldn't look empty on first activation. -->
                    <h2><?php printf( esc_html__( 'Welcome to %s', 'coastline' ), esc_html( get_bloginfo( 'name' ) ) ); ?></h2>
                    <p><?php echo esc_html( get_bloginfo( 'description', 'display' ) ); ?></p>
                <?php endif; ?>

            </div>

        <?php endwhile; endif; ?>


        <?php
        // =====================================================================
        // UPCOMING EVENTS SECTION
        // WHY: For an active society, showing upcoming events on the homepage
        // is the single most valuable piece of dynamic content. It signals
        // that the society is alive and engaged, and gives visitors a reason
        // to come back. We show up to 4 events with dates and times.
        // =====================================================================
        ?>
        <section class="coastline-events-section">
            <h2 class="coastline-section-heading"><?php esc_html_e( 'Upcoming Events', 'coastline' ); ?></h2>
            <hr class="coastline-section-divider">

            <?php
            if ( function_exists( 'sp_render_builder_widget_upcoming_events' ) ) {
                sp_render_builder_widget_upcoming_events( [
                    'count'         => 4,
                    'category_id'   => 0,     // All categories
                    'show_date'     => true,
                    'show_time'     => true,
                    'show_location' => true,
                ] );
            } else {
                echo '<p class="coastline-plugin-notice">' . esc_html__( 'Events will appear here once the SocietyPress plugin is active.', 'coastline' ) . '</p>';
            }
            ?>

            <?php
            // Link to the full events page if one exists.
            // WHY we look up by page template: The events page is created by
            // SocietyPress using a custom page template. This is the reliable
            // way to find it regardless of what the admin named the page.
            $events_pages = get_pages( [
                'meta_key'   => '_wp_page_template',
                'meta_value' => 'sp-events',
            ] );
            if ( ! empty( $events_pages ) ) :
            ?>
                <a href="<?php echo esc_url( get_permalink( $events_pages[0]->ID ) ); ?>" class="coastline-more-link">
                    <?php esc_html_e( 'View All Events', 'coastline' ); ?> &rarr;
                </a>
            <?php endif; ?>

        </section>

    </main>

    <!-- Sidebar — loads sidebar.php which outputs the coastline-sidebar
         widget area, or default content if no widgets are configured. -->
    <?php get_sidebar(); ?>

</div>

<?php get_footer(); ?>
