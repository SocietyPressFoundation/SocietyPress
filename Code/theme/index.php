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

<div class="site-content">
    <div class="content-area-with-sidebar">

        <main id="main-content" class="main-content">
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
                        <a href="<?php the_permalink(); ?>" class="read-more"><?php esc_html_e( 'Read more', 'societypress' ); ?> &rarr;</a>
                    </div>
                </article>

                <?php endwhile; ?>

                <div class="pagination">
                    <?php
                    the_posts_pagination([
                        'prev_text' => '&laquo; ' . __( 'Previous', 'societypress' ),
                        'next_text' => __( 'Next', 'societypress' ) . ' &raquo;',
                    ]);
                    ?>
                </div>

            <?php else : ?>

                <article class="no-results">
                    <header class="entry-header">
                        <h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'societypress' ); ?></h2>
                    </header>
                    <div class="entry-content">
                        <p><?php esc_html_e( 'There are no posts to display. Once you start publishing content, it will appear here.', 'societypress' ); ?></p>
                    </div>
                </article>

            <?php endif; ?>
        </main>

        <?php get_sidebar(); ?>

    </div>
</div>

<?php get_footer(); ?>
