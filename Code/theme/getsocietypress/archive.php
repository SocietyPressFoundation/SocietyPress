<?php
/**
 * Archive Template (archive.php)
 *
 * Handles category, tag, date, and author archives. Uses the same
 * card grid layout as the blog index.
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title"><?php the_archive_title(); ?></h1>
            <?php if ( get_the_archive_description() ) : ?>
                <p class="page-hero__subtitle"><?php echo wp_kses_post( get_the_archive_description() ); ?></p>
            <?php endif; ?>
        </div>
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
            <div class="section" style="padding-top: var(--spacing-xl);">
                <?php
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '&larr; Previous',
                    'next_text' => 'Next &rarr;',
                ) );
                ?>
            </div>

        <?php else : ?>

            <p class="text-center text-secondary">No posts found.</p>

        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>
