<?php
/**
 * Search Results Template
 *
 * WHY this exists: without a search.php, a native WordPress search (?s=…) fell
 * through to index.php, which has no page <h1>, never echoes what was searched,
 * and — on zero results — showed "Once you start publishing content, it will
 * appear here." That message is wrong for a failed search: it implies the site
 * is empty rather than that the search found nothing. This template gives search
 * its own heading (echoing the query), its own results list, and an empty state
 * that acknowledges the search and invites the visitor to try again.
 *
 * @package SocietyPress
 */

get_header();
?>

<div class="site-content">
    <div class="content-area-with-sidebar">

        <main id="main-content" class="main-content">

            <header class="entry-header search-results-header">
                <h1 class="entry-title">
                    <?php
                    printf(
                        /* translators: %s: the search term the visitor entered */
                        esc_html__( 'Search results for: %s', 'societypress' ),
                        '<span class="search-query">' . esc_html( get_search_query() ) . '</span>'
                    );
                    ?>
                </h1>
            </header>

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
                    <div class="entry-content">
                        <p>
                            <?php
                            printf(
                                /* translators: %s: the search term the visitor entered */
                                esc_html__( 'We could not find anything matching %s. Try a different word, or fewer words.', 'societypress' ),
                                '<strong>' . esc_html( get_search_query() ) . '</strong>'
                            );
                            ?>
                        </p>
                        <?php get_search_form(); ?>
                    </div>
                </article>

            <?php endif; ?>
        </main>

        <?php get_sidebar(); ?>

    </div>
</div>

<?php get_footer(); ?>
