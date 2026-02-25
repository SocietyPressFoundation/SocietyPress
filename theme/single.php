<?php
/**
 * Single Post Template
 *
 * WHY: Individual blog posts get a two-column layout (content + sidebar).
 * The sidebar provides navigation to other content (recent posts,
 * categories, search) which helps visitors explore the site.
 *
 * @package SocietyPress
 */

get_header();
?>

<div class="site-content">
    <div class="content-area-with-sidebar">

        <main class="main-content">
            <?php while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    <div class="entry-meta">
                        <?php echo get_the_date(); ?> &middot; <?php the_author(); ?>
                    </div>
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

            <?php
            // Show comments section if comments are open or there are existing comments
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
            ?>

            <?php endwhile; ?>
        </main>

        <?php get_sidebar(); ?>

    </div>
</div>

<?php get_footer(); ?>
