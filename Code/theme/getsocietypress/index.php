<?php
/**
 * Index Template (Fallback)
 *
 * This is the default template WordPress uses if no more specific template
 * matches. For this theme it serves as a simple blog listing — showing
 * the latest posts with excerpts.
 *
 * The homepage uses front-page.php instead.
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo is_home() ? 'News &amp; Updates' : wp_title( '', false ); ?></h1>
        <p>The latest from the SocietyPress project.</p>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <?php if ( have_posts() ) : ?>

            <div class="grid-3">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="update-card">
                        <div class="update-card__image">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            <?php else : ?>
                                [Post image]
                            <?php endif; ?>
                        </div>
                        <div class="update-card__body">
                            <div class="update-card__date">
                                <?php echo esc_html( get_the_date( 'F j, Y' ) ); ?>
                            </div>
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="update-card__excerpt"><?php the_excerpt(); ?></p>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="section section--top-spaced">
                <?php
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '&larr; Previous',
                    'next_text' => 'Next &rarr;',
                ) );
                ?>
            </div>

        <?php else : ?>

            <p class="text-center text-secondary">No posts yet. Check back soon for updates on SocietyPress.</p>

        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>
