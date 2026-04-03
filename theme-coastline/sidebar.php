<?php
/**
 * Coastline Child Theme — Sidebar Template
 *
 * WHY: Overrides the parent sidebar to use the Coastline-specific
 * 'coastline-sidebar' widget area instead of the parent's 'sidebar-1'.
 *
 * If no widgets have been configured yet, we show sensible default content
 * (upcoming events and newsletter archive) so the magazine layout doesn't
 * look broken on first activation. Once the admin adds widgets via
 * Appearance > Widgets, the defaults disappear and their widgets take over.
 *
 * @package Coastline
 * @since   1.1.0
 */
?>

<aside class="coastline-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'coastline' ); ?>">

    <?php if ( is_active_sidebar( 'coastline-sidebar' ) ) : ?>

        <?php
        // Admin has configured widgets — output them.
        dynamic_sidebar( 'coastline-sidebar' );
        ?>

    <?php else : ?>

        <!-- Default sidebar content — shown until the admin adds widgets.
             WHY defaults: A magazine layout with an empty sidebar looks broken.
             These defaults showcase what the sidebar CAN do and give the site
             a complete feel right out of the box. -->

        <!-- Upcoming Events — pulls live data from the SocietyPress events tables -->
        <div class="coastline-default-widget">
            <h3 class="coastline-default-widget-title"><?php esc_html_e( 'Upcoming Events', 'coastline' ); ?></h3>
            <div class="coastline-default-widget-body">
                <?php
                if ( function_exists( 'sp_render_builder_widget_upcoming_events' ) ) {
                    sp_render_builder_widget_upcoming_events( [
                        'count'         => 3,
                        'category_id'   => 0,
                        'show_date'     => true,
                        'show_time'     => true,
                        'show_location' => false,
                    ] );
                } else {
                    echo '<p>' . esc_html__( 'Events will appear here once the SocietyPress plugin is active.', 'coastline' ) . '</p>';
                }
                ?>
            </div>
        </div>

        <!-- Newsletter Archive — pulls from the SocietyPress newsletters table -->
        <div class="coastline-default-widget">
            <h3 class="coastline-default-widget-title"><?php esc_html_e( 'Recent Newsletters', 'coastline' ); ?></h3>
            <div class="coastline-default-widget-body">
                <?php
                if ( function_exists( 'sp_render_builder_widget_newsletter_archive' ) ) {
                    sp_render_builder_widget_newsletter_archive( [
                        'count'      => 3,
                        'show_cover' => true,
                    ] );
                } else {
                    echo '<p>' . esc_html__( 'Newsletters will appear here once the SocietyPress plugin is active.', 'coastline' ) . '</p>';
                }
                ?>
            </div>
        </div>

    <?php endif; ?>

</aside>
