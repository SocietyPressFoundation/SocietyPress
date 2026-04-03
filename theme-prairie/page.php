<?php
/**
 * Prairie Theme — Page Template (Two-Column Explorer Layout)
 *
 * WHY this overrides the parent page.php: The parent theme uses a single-column
 * full-width layout for pages. Prairie's Explorer archetype needs the left
 * sidebar + content area two-column grid on every page, so visitors always
 * have persistent navigation available.
 *
 * Structure:
 * - .prairie-layout wraps the sidebar and content in a CSS Grid
 * - Sidebar (via sidebar.php) shows vertical nav or widgets
 * - Content area shows the page title, featured image, and content
 * - The footer.php closes .prairie-content and .prairie-layout
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

            <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>

            <?php endwhile; ?>

<?php
/* WHY no closing divs here: The footer.php template closes .prairie-content
   and .prairie-layout. This pattern keeps the layout wrapper intact across
   the page template and footer, which is necessary because the footer sits
   OUTSIDE the two-column grid but the closing tags need to be in sequence. */
get_footer();
