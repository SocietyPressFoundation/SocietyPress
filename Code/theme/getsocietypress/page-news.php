<?php
/**
 * News & Updates Page Template (page-news.php)
 *
 * The /news/ page is for project announcements, release notes, conference
 * reports, and general news from the SocietyPress project. This template
 * queries blog posts directly (rather than relying on WordPress's "posts
 * page" setting) so /news/ stays a normal WP page you can also edit in
 * the admin if you want to add intro copy above the post list.
 *
 * If post content is entered in the WP admin editor for the /news/ page,
 * it renders above the posts list as an introduction.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

$paged = max( 1, (int) get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : (int) get_query_var( 'page' ) );

$news_query = new WP_Query( array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 9,
    'paged'          => $paged,
) );
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">News &amp; Updates</h1>
            <p class="page-hero__subtitle">
                Release announcements, conference reports, and project news
                from SocietyPress.
            </p>
        </div>
    </div>
</section>

<?php
/* Optional admin-written intro — if the /news/ page has editor content,
   render it above the news feed as context. */
while ( have_posts() ) : the_post();
    $intro = get_the_content();
    if ( ! empty( trim( $intro ) ) ) :
?>
<section class="news-intro section">
    <div class="container container--narrow">
        <div class="news-intro__content">
            <?php the_content(); ?>
        </div>
    </div>
</section>
<?php
    endif;
endwhile;
wp_reset_postdata();
?>

<section class="news-feed section">
    <div class="container">

        <?php if ( $news_query->have_posts() ) : ?>

            <div class="grid-3">
                <?php while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
                    <article class="update-card">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a class="update-card__image" href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="update-card__body">
                            <div class="update-card__date">
                                <?php echo esc_html( get_the_date( 'F j, Y' ) ); ?>
                            </div>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="update-card__excerpt"><?php the_excerpt(); ?></p>
                            <a href="<?php the_permalink(); ?>" class="update-card__more">Read more &rarr;</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php
            $big = 999999999;
            $links = paginate_links( array(
                'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format'    => '?paged=%#%',
                'current'   => $paged,
                'total'     => $news_query->max_num_pages,
                'mid_size'  => 2,
                'prev_text' => '&larr; Previous',
                'next_text' => 'Next &rarr;',
                'type'      => 'array',
            ) );
            if ( ! empty( $links ) ) : ?>
                <nav class="news-pagination" aria-label="News pagination">
                    <?php foreach ( $links as $link ) : ?>
                        <?php echo $link; // already escaped by paginate_links ?>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

        <?php else : ?>

            <div class="news-empty">
                <div class="news-empty__icon" aria-hidden="true">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 11a9 9 0 0 1 9 9"/>
                        <path d="M4 4a16 16 0 0 1 16 16"/>
                        <circle cx="5" cy="19" r="1"/>
                    </svg>
                </div>
                <h2>No posts yet</h2>
                <p>
                    News posts will land here as they're written. Release
                    notes will also appear at
                    <a href="<?php echo esc_url( home_url( '/changelog/' ) ); ?>">/changelog/</a>
                    and roadmap updates at
                    <a href="<?php echo esc_url( home_url( '/roadmap/' ) ); ?>">/roadmap/</a>.
                </p>
                <p>
                    In the meantime, the community is active on the
                    <a href="<?php echo esc_url( home_url( '/forums/' ) ); ?>">forums</a>.
                </p>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>
