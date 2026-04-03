<?php
/**
 * Ledger Child Theme — Page Template
 *
 * WHY this overrides the parent: Ledger subpages use a narrow reading column
 * (max-width 760px) centered on the page. No sidebar, no distractions — just
 * clean content with generous padding. This is the standard layout for modern
 * documentation sites and long-form reading (Medium, Notion, etc.).
 *
 * The 760px width ensures optimal line length (50-75 characters per line at
 * our base font size) for comfortable reading.
 *
 * @package Ledger
 * @since   1.1.0
 */

get_header();
?>

<div id="main-content" class="site-content">
    <div class="ledger-page-content">

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

    </div>
</div>

<?php get_footer(); ?>
