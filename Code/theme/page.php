<?php
/**
 * Page Template
 *
 * WHY: Static pages (About, Contact, etc.) get a single-column layout
 * with no sidebar. This keeps the content front and center without
 * distractions — which is what users expect from a "page" vs a "post."
 *
 * @package SocietyPress
 */

get_header();
?>

<main id="main-content" class="site-content">
    <div class="content-area-full">

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
</main>

<?php get_footer(); ?>
