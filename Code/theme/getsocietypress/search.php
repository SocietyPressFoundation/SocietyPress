<?php
/**
 * Search Results Template (search.php)
 *
 * WordPress-native search across every public post type on the
 * getsocietypress.org install — pages (which is where most marketing
 * content lives: docs, FAQ, showcase, blog), blog posts, and bbPress
 * topics if the forum is active.
 *
 * Two-column layout: results on the left, a "search by source" filter
 * on the right so visitors can narrow to docs / FAQ / blog / forum.
 *
 * @package getsocietypress
 * @version 0.01d
 */

defined( 'ABSPATH' ) || exit;

get_header();

$query   = get_search_query();
$total   = (int) $GLOBALS['wp_query']->found_posts;
$source  = isset( $_GET['source'] ) ? sanitize_key( $_GET['source'] ) : '';

/**
 * Count results within a slug-prefix bucket. Mirrors WP's main query but
 * restricted to a parent-slug pattern. Cheap because WP already cached
 * the result set; we're just counting per facet.
 */
$gsp_count_in_section = static function ( $slug_prefix ) use ( $query ): int {
    $q = new WP_Query( [
        's'              => $query,
        'post_type'      => 'page',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
    ] );
    $count = 0;
    foreach ( $q->posts as $pid ) {
        if ( strpos( get_page_uri( $pid ), $slug_prefix ) === 0 ) $count++;
    }
    return $count;
};
?>

<div class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">
                <?php
                if ( $query !== '' ) {
                    /* translators: %s search query */
                    printf( esc_html__( 'Search results for &ldquo;%s&rdquo;', 'getsocietypress' ), esc_html( $query ) );
                } else {
                    esc_html_e( 'Search', 'getsocietypress' );
                }
                ?>
            </h1>
            <?php if ( $query !== '' ) : ?>
                <p class="page-hero__subtitle">
                    <?php
                    /* translators: %s number of results */
                    printf( esc_html( _n( '%s result found.', '%s results found.', $total, 'getsocietypress' ) ), number_format_i18n( $total ) );
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <div class="gsp-search-layout">
            <main class="gsp-search-results">

                <?php if ( ! have_posts() ) : ?>
                    <div class="gsp-search-empty">
                        <p><?php esc_html_e( 'Nothing matched your search.', 'getsocietypress' ); ?></p>
                        <ul class="ul-disc">
                            <li><?php esc_html_e( 'Try different or more general words.', 'getsocietypress' ); ?></li>
                            <li><?php esc_html_e( 'Check the spelling.', 'getsocietypress' ); ?></li>
                            <li><a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>"><?php esc_html_e( 'Browse the docs', 'getsocietypress' ); ?></a> &middot;
                                <a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'Check the FAQ', 'getsocietypress' ); ?></a>
                            </li>
                        </ul>
                    </div>
                <?php else : ?>
                    <?php while ( have_posts() ) : the_post();
                        $uri     = get_page_uri( get_the_ID() );
                        // Bucket the result by its top-level slug
                        $bucket  = explode( '/', (string) $uri )[0] ?? '';
                        $bucket  = sanitize_html_class( $bucket ?: get_post_type() );
                    ?>
                        <article class="gsp-search-result gsp-search-result--<?php echo esc_attr( $bucket ); ?>">
                            <header class="gsp-search-result__header">
                                <span class="gsp-search-result__source"><?php echo esc_html( ucfirst( $bucket ) ); ?></span>
                                <h2 class="gsp-search-result__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            </header>
                            <p class="gsp-search-result__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags( get_the_content() ), 35, '&hellip;' ) ); ?></p>
                            <p class="gsp-search-result__url"><code><?php echo esc_html( str_replace( home_url( '/' ), '/', get_permalink() ) ); ?></code></p>
                        </article>
                    <?php endwhile; ?>

                    <div class="section section--top-spaced">
                        <?php
                        the_posts_pagination( [
                            'mid_size'  => 2,
                            'prev_text' => '&larr; Previous',
                            'next_text' => 'Next &rarr;',
                        ] );
                        ?>
                    </div>
                <?php endif; ?>
            </main>

            <aside class="gsp-search-sidebar">
                <h3 class="gsp-search-sidebar__heading"><?php esc_html_e( 'Refine search', 'getsocietypress' ); ?></h3>
                <p class="gsp-search-sidebar__hint"><?php esc_html_e( 'Search the whole site, then jump to a section:', 'getsocietypress' ); ?></p>
                <ul class="gsp-search-sidebar__links">
                    <li><a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( $query ) ) ); ?>"><?php esc_html_e( 'All results', 'getsocietypress' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/docs/?s=' . rawurlencode( $query ) ) ); ?>"><?php esc_html_e( 'Docs', 'getsocietypress' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/faq/?s=' . rawurlencode( $query ) ) ); ?>"><?php esc_html_e( 'FAQ', 'getsocietypress' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/showcase/?s=' . rawurlencode( $query ) ) ); ?>"><?php esc_html_e( 'Showcase', 'getsocietypress' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/news/?s=' . rawurlencode( $query ) ) ); ?>"><?php esc_html_e( 'News &amp; Blog', 'getsocietypress' ); ?></a></li>
                </ul>
            </aside>
        </div>

    </div>
</div>

<?php get_footer(); ?>
