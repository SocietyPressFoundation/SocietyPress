<?php
/**
 * Parlor Child Theme — Front Page Template
 *
 * WHY: This is the classic organizational homepage — the layout ENS migrants
 * will feel immediately at home with. No flashy hero sliders or page builder
 * widgets. Just well-organized content: welcome text, next meeting, upcoming
 * events, and recent news. The kind of homepage every traditional society
 * website has had for 20 years.
 *
 * Sections:
 * 1. Welcome text (from the page content in the editor)
 * 2. "Next Meeting" callout box (auto-detected from events table)
 * 3. Upcoming events list (next 5 events, compact format)
 * 4. Recent news/posts (latest 3 blog posts)
 * 5. Optional right sidebar (same as page.php)
 *
 * The "Next Meeting" callout is the anchor. This is what ENS societies are
 * used to seeing front and center on their homepage — when and where the
 * next meeting is. We query it directly from the events table.
 *
 * @package Parlor
 * @since   1.0.0
 */

get_header();

// WHY: We need $wpdb for direct queries to the SocietyPress events table.
// The events module stores data in custom tables, not in wp_posts.
global $wpdb;

// WHY: Check if the events module is active before running any queries.
// If the plugin isn't active or events are disabled, we skip the events
// sections entirely rather than throwing errors.
$events_available = function_exists( 'sp_module_enabled' ) && sp_module_enabled( 'events' );

// Query the next upcoming event for the "Next Meeting" callout.
// WHY direct query: The events data lives in a custom table, not wp_posts.
// We grab the single next event from today forward. If there's no upcoming
// event, $next_event will be null and we skip the section gracefully.
$next_event = null;
if ( $events_available ) {
    $events_table = $wpdb->prefix . 'sp_events';
    $today        = wp_date( 'Y-m-d' );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $next_event = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT title, event_date, start_time, end_time, location_name, location_address, is_virtual, virtual_url, description
             FROM {$events_table}
             WHERE event_date >= %s
               AND status = 'scheduled'
               AND visibility = 'public'
             ORDER BY event_date ASC, start_time ASC
             LIMIT 1",
            $today
        )
    );
}

// Query the next 5 upcoming events for the events list section.
// WHY 5: Enough to show activity without overwhelming the homepage.
// If the next meeting is included in the 5, that's fine — it appears
// in both the callout and the list, which reinforces it.
$upcoming_events = [];
if ( $events_available ) {
    $events_table = $wpdb->prefix . 'sp_events';
    $today        = wp_date( 'Y-m-d' );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $upcoming_events = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT title, event_date, start_time, location_name, is_virtual
             FROM {$events_table}
             WHERE event_date >= %s
               AND status = 'scheduled'
               AND visibility = 'public'
             ORDER BY event_date ASC, start_time ASC
             LIMIT 5",
            $today
        )
    );
}

// Query the latest 3 blog posts for the "Recent News" section.
// WHY WP_Query: Blog posts ARE in wp_posts, so we use the standard API.
$recent_posts = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<div id="main-content" class="site-content">
    <?php
    $has_sidebar = is_active_sidebar( 'parlor-sidebar' );
    ?>
    <div class="parlor-content-wrap<?php echo $has_sidebar ? ' parlor-has-sidebar' : ''; ?>">

        <div class="content-area">

            <!-- Section 1: Welcome text from the page editor.
                 WHY: Lets the admin write their own welcome message without
                 touching any code. They just edit the "Home" page content. -->
            <?php while ( have_posts() ) : the_post(); ?>
                <?php
                $content = get_the_content();
                if ( ! empty( trim( $content ) ) ) :
                ?>
                <section class="parlor-welcome">
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </section>
                <?php endif; ?>
            <?php endwhile; ?>

            <?php
            // ================================================================
            // Section 2: "Next Meeting" callout box
            //
            // WHY this is the anchor of the page: ENS societies always show
            // "next meeting" front and center. Their members visit the site
            // primarily to check when the next meeting is. This callout gives
            // them that answer immediately.
            // ================================================================
            if ( $next_event ) :
            ?>
            <section class="parlor-next-meeting">
                <h2><?php esc_html_e( 'Next Meeting', 'parlor' ); ?></h2>

                <div class="parlor-meeting-details">
                    <div class="parlor-meeting-date-block">
                        <?php
                        // WHY separate month/day: A large visual date block
                        // (month abbreviation over day number) is immediately
                        // scannable — visitors don't have to read a full
                        // date string to find what they need.
                        $event_timestamp = strtotime( $next_event->event_date );
                        ?>
                        <span class="parlor-meeting-month"><?php echo esc_html( wp_date( 'M', $event_timestamp ) ); ?></span>
                        <span class="parlor-meeting-day"><?php echo esc_html( wp_date( 'j', $event_timestamp ) ); ?></span>
                    </div>

                    <div class="parlor-meeting-info">
                        <h3 class="parlor-meeting-title"><?php echo esc_html( $next_event->title ); ?></h3>

                        <p class="parlor-meeting-datetime">
                            <?php
                            // Full date (e.g., "Saturday, April 12, 2026")
                            echo esc_html( wp_date( 'l, F j, Y', $event_timestamp ) );

                            // Time range if available
                            if ( $next_event->start_time ) :
                                echo ' &mdash; ';
                                echo esc_html( wp_date( 'g:i A', strtotime( $next_event->start_time ) ) );
                                if ( $next_event->end_time ) :
                                    echo ' – ';
                                    echo esc_html( wp_date( 'g:i A', strtotime( $next_event->end_time ) ) );
                                endif;
                            endif;
                            ?>
                        </p>

                        <?php if ( $next_event->location_name || $next_event->is_virtual ) : ?>
                        <p class="parlor-meeting-location">
                            <?php
                            if ( $next_event->is_virtual && $next_event->virtual_url ) :
                                printf(
                                    /* translators: %s: virtual meeting link */
                                    esc_html__( 'Virtual Meeting: %s', 'parlor' ),
                                    '<a href="' . esc_url( $next_event->virtual_url ) . '">' . esc_html__( 'Join Online', 'parlor' ) . '</a>'
                                );
                            elseif ( $next_event->is_virtual ) :
                                esc_html_e( 'Virtual Meeting', 'parlor' );
                            else :
                                echo esc_html( $next_event->location_name );
                                if ( $next_event->location_address ) :
                                    echo '<br>';
                                    echo esc_html( $next_event->location_address );
                                endif;
                            endif;
                            ?>
                        </p>
                        <?php endif; ?>

                        <?php
                        // WHY: Show a brief description excerpt if one exists.
                        // Keeps the callout informative without being overwhelming.
                        if ( ! empty( $next_event->description ) ) :
                            $excerpt = wp_trim_words( wp_strip_all_tags( $next_event->description ), 30, '&hellip;' );
                        ?>
                        <p class="parlor-meeting-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // Section 3: Upcoming Events list
            //
            // WHY: After the next meeting callout, show a compact list of
            // what's coming up. Date on the left, event name and location on
            // the right. Simple, scannable, traditional.
            // ================================================================
            if ( ! empty( $upcoming_events ) ) :
            ?>
            <section class="parlor-upcoming-events">
                <h2><?php esc_html_e( 'Upcoming Events', 'parlor' ); ?></h2>

                <ul class="parlor-events-list">
                    <?php foreach ( $upcoming_events as $event ) : ?>
                    <li class="parlor-event-item">
                        <div class="parlor-event-date">
                            <?php
                            $evt_timestamp = strtotime( $event->event_date );
                            ?>
                            <span class="parlor-event-month"><?php echo esc_html( wp_date( 'M', $evt_timestamp ) ); ?></span>
                            <span class="parlor-event-day"><?php echo esc_html( wp_date( 'j', $evt_timestamp ) ); ?></span>
                        </div>

                        <div class="parlor-event-details">
                            <strong class="parlor-event-title"><?php echo esc_html( $event->title ); ?></strong>
                            <?php if ( $event->start_time ) : ?>
                                <span class="parlor-event-time"><?php echo esc_html( wp_date( 'g:i A', strtotime( $event->start_time ) ) ); ?></span>
                            <?php endif; ?>
                            <?php if ( $event->location_name ) : ?>
                                <span class="parlor-event-location"><?php echo esc_html( $event->location_name ); ?></span>
                            <?php elseif ( $event->is_virtual ) : ?>
                                <span class="parlor-event-location"><?php esc_html_e( 'Virtual', 'parlor' ); ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // Section 4: Recent News / Blog Posts
            //
            // WHY: Many societies post announcements, meeting recaps, or
            // organizational news as blog posts. Showing the latest 3 on the
            // homepage gives the site a sense of activity and currency.
            // If no posts exist, we skip the section entirely.
            // ================================================================
            if ( $recent_posts->have_posts() ) :
            ?>
            <section class="parlor-recent-news">
                <h2><?php esc_html_e( 'Recent News', 'parlor' ); ?></h2>

                <div class="parlor-news-list">
                    <?php while ( $recent_posts->have_posts() ) : $recent_posts->the_post(); ?>
                    <article class="parlor-news-item">
                        <h3 class="parlor-news-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="parlor-news-meta">
                            <?php
                            printf(
                                /* translators: %s: post date */
                                esc_html__( 'Posted %s', 'parlor' ),
                                esc_html( get_the_date() )
                            );
                            ?>
                        </p>
                        <div class="parlor-news-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php
            endif;
            wp_reset_postdata();
            ?>

        </div><!-- .content-area -->

        <?php if ( $has_sidebar ) : ?>
            <?php get_sidebar(); ?>
        <?php endif; ?>

    </div><!-- .parlor-content-wrap -->
</div><!-- .site-content -->

<?php get_footer(); ?>
