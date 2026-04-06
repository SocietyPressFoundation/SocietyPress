<?php
/**
 * Coastline Child Theme — Page Template
 *
 * WHY: Overrides the parent's single-column page template to add the magazine
 * sidebar. Every page on a Coastline-powered site gets the two-column layout:
 * main content on the left, sidebar on the right. This keeps the sidebar's
 * at-a-glance info (events, newsletters, links) visible no matter what page
 * the visitor is on.
 *
 * The CSS grid in style.css handles the layout: 1fr + 300px on desktop,
 * collapsing to a single column at 768px.
 *
 * @package Coastline
 * @since   1.1.0
 */

get_header();
?>

<div id="main-content" class="coastline-layout">

    <!-- Main content area -->
    <main class="coastline-content">

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

    </main>

    <!-- Sidebar — loads sidebar.php which outputs the coastline-sidebar
         widget area, or default content if no widgets are configured. -->
    <?php get_sidebar(); ?>

</div>

<?php get_footer(); ?>
