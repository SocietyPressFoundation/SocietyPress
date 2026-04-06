<?php
/**
 * Prairie Theme — Front Page (Explorer Homepage)
 *
 * WHY a custom front page: The Explorer layout's homepage needs to do double
 * duty — provide the persistent left sidebar navigation AND present a welcoming
 * content area with featured sections. A generic page.php doesn't have the
 * card grid for highlighting key sections of the site (library, events,
 * records, etc.) that help first-time visitors find what they're looking for.
 *
 * The content area shows:
 * 1. Welcome text — pulled from the page content (the admin edits this in
 *    the page editor, keeping it maintainable without code changes)
 * 2. Featured image — if set on the front page
 * 3. Feature cards — hardcoded section links that showcase what the society
 *    has to offer. These use SocietyPress page template URLs when available,
 *    falling back gracefully when modules are disabled.
 *
 * @package Prairie
 * @since   1.1.0
 */

get_header();
?>

    <div class="prairie-layout">

        <?php get_sidebar(); ?>

        <div id="main-content" class="prairie-content" role="main">

            <?php while ( have_posts() ) : the_post(); ?>

            <!-- Welcome section — the page title and content the admin writes
                 in the page editor. This is the society's greeting to visitors. -->
            <div class="prairie-welcome">
                <h1><?php the_title(); ?></h1>
                <?php if ( get_the_content() ) : ?>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if ( has_post_thumbnail() ) : ?>
            <div class="prairie-featured-image">
                <?php the_post_thumbnail( 'large' ); ?>
            </div>
            <?php endif; ?>

            <?php endwhile; ?>

            <!-- Feature cards — highlight key sections of the society's site.
                 WHY hardcoded cards: These represent the core SocietyPress modules.
                 Each card checks if the module's frontend page exists before
                 rendering a link. If a module is disabled or no page is assigned,
                 the card degrades gracefully (shows text without a broken link).

                 WHY we use sp_get_template_page_url: The SocietyPress plugin
                 assigns frontend pages to specific templates. This function
                 returns the permalink for a given template slug, or empty
                 string if no page is assigned. Keeps us decoupled from
                 specific page IDs or slugs. -->
            <div class="prairie-features">

                <?php
                /* WHY we check function_exists: The theme must work even if the
                   SocietyPress plugin is deactivated. Without this guard, the
                   site would white-screen. sp_get_template_page_url() returns
                   the permalink for a page assigned to a given SP template slug,
                   or an empty string if no page is assigned. */
                $has_sp = function_exists( 'sp_get_template_page_url' );
                ?>

                <?php
                $events_url = $has_sp ? sp_get_template_page_url( 'sp-events' ) : '';
                ?>
                <div class="prairie-feature-card">
                    <h3><?php esc_html_e( 'Events', 'prairie' ); ?></h3>
                    <p><?php esc_html_e( 'Meetings, workshops, field trips, and special programs. See what\'s coming up and mark your calendar.', 'prairie' ); ?></p>
                    <?php if ( $events_url ) : ?>
                    <a href="<?php echo esc_url( $events_url ); ?>"><?php esc_html_e( 'View Events', 'prairie' ); ?> &rarr;</a>
                    <?php endif; ?>
                </div>

                <?php
                $library_url = $has_sp ? sp_get_template_page_url( 'sp-library-catalog' ) : '';
                ?>
                <div class="prairie-feature-card">
                    <h3><?php esc_html_e( 'Library', 'prairie' ); ?></h3>
                    <p><?php esc_html_e( 'Browse our catalog of books, periodicals, maps, and research materials available to members.', 'prairie' ); ?></p>
                    <?php if ( $library_url ) : ?>
                    <a href="<?php echo esc_url( $library_url ); ?>"><?php esc_html_e( 'Browse Library', 'prairie' ); ?> &rarr;</a>
                    <?php endif; ?>
                </div>

                <?php
                $records_url = $has_sp ? sp_get_template_page_url( 'sp-records' ) : '';
                ?>
                <div class="prairie-feature-card">
                    <h3><?php esc_html_e( 'Records', 'prairie' ); ?></h3>
                    <p><?php esc_html_e( 'Search our genealogical records collection — census data, vital records, cemetery transcriptions, and more.', 'prairie' ); ?></p>
                    <?php if ( $records_url ) : ?>
                    <a href="<?php echo esc_url( $records_url ); ?>"><?php esc_html_e( 'Search Records', 'prairie' ); ?> &rarr;</a>
                    <?php endif; ?>
                </div>

                <?php
                $members_url = $has_sp ? sp_get_template_page_url( 'sp-directory' ) : '';
                ?>
                <div class="prairie-feature-card">
                    <h3><?php esc_html_e( 'Members', 'prairie' ); ?></h3>
                    <p><?php esc_html_e( 'Connect with fellow members, find research partners, and explore shared interests.', 'prairie' ); ?></p>
                    <?php if ( $members_url ) : ?>
                    <a href="<?php echo esc_url( $members_url ); ?>"><?php esc_html_e( 'Member Directory', 'prairie' ); ?> &rarr;</a>
                    <?php endif; ?>
                </div>

                <?php
                $newsletters_url = $has_sp ? sp_get_template_page_url( 'sp-newsletter-archive' ) : '';
                ?>
                <div class="prairie-feature-card">
                    <h3><?php esc_html_e( 'Newsletters', 'prairie' ); ?></h3>
                    <p><?php esc_html_e( 'Read past issues of our newsletter — society news, research tips, member contributions, and more.', 'prairie' ); ?></p>
                    <?php if ( $newsletters_url ) : ?>
                    <a href="<?php echo esc_url( $newsletters_url ); ?>"><?php esc_html_e( 'Newsletter Archive', 'prairie' ); ?> &rarr;</a>
                    <?php endif; ?>
                </div>

                <?php
                $resources_url = $has_sp ? sp_get_template_page_url( 'sp-resources' ) : '';
                ?>
                <div class="prairie-feature-card">
                    <h3><?php esc_html_e( 'Resources', 'prairie' ); ?></h3>
                    <p><?php esc_html_e( 'Curated links to databases, archives, how-to guides, and other research tools.', 'prairie' ); ?></p>
                    <?php if ( $resources_url ) : ?>
                    <a href="<?php echo esc_url( $resources_url ); ?>"><?php esc_html_e( 'Browse Resources', 'prairie' ); ?> &rarr;</a>
                    <?php endif; ?>
                </div>

            </div>

<?php
/* WHY no closing divs here: footer.php closes .prairie-content and
   .prairie-layout — same pattern as page.php. */
get_footer();
