<?php
/**
 * Main Index Template
 *
 * WHY: This is WordPress's ultimate fallback template. If no more specific
 * template exists (page.php, single.php, etc.), WordPress uses this one.
 * It displays a list of posts with a sidebar — the standard blog layout.
 *
 * @package SocietyPress
 */

get_header();
?>

<div id="main-content" class="site-content">
    <div class="content-area-with-sidebar">

        <main class="main-content">
            <?php if ( have_posts() ) : ?>

                <?php while ( have_posts() ) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <h2 class="entry-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="entry-meta">
                            <?php echo get_the_date(); ?> &middot; <?php the_author(); ?>
                        </div>
                    </header>

                    <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                        <a href="<?php the_permalink(); ?>" class="read-more">Read more &rarr;</a>
                    </div>
                </article>

                <?php endwhile; ?>

                <div class="pagination">
                    <?php
                    the_posts_pagination([
                        'prev_text' => '&laquo; Previous',
                        'next_text' => 'Next &raquo;',
                    ]);
                    ?>
                </div>

            <?php else : ?>

                <article class="no-results">
                    <header class="entry-header">
                        <h2 class="entry-title">Nothing here yet</h2>
                    </header>
                    <div class="entry-content">
                        <p>There are no posts to display. Once you start publishing content, it will appear here.</p>
                    </div>
                </article>

            <?php endif; ?>
        </main>

        <?php get_sidebar(); ?>

    </div>
</div>

<?php get_footer(); ?>
