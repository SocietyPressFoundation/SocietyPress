<?php
/**
 * Single Post Template (single.php)
 *
 * Displays individual blog posts / news articles. Clean, readable
 * layout with a narrow content column.
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- Post header with dark background matching other page headers -->
<div class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <div class="single-meta">
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date( 'F j, Y' ) ); ?>
                </time>
            </div>
            <h1 class="page-hero__title"><?php the_title(); ?></h1>
        </div>
    </div>
</div>

<!-- Post content -->
<article class="single-post section">
    <div class="container">

        <div class="single-post__content">
            <?php the_content(); ?>
        </div>

        <?php
        // Only render the prev/next nav block if at least one neighbor exists.
        // Otherwise the empty wrapper still renders its top+bottom borders,
        // showing as two stray horizontal lines at the bottom of the post.
        $prev_post = get_previous_post();
        $next_post = get_next_post();
        if ( $prev_post || $next_post ) :
        ?>
        <!-- Post navigation — previous/next post links -->
        <nav class="single-post__nav" aria-label="Post navigation">
            <div class="single-post__nav-prev">
                <?php if ( $prev_post ) : ?>
                    <span class="single-post__nav-label">&larr; Previous</span>
                    <a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>">
                        <?php echo esc_html( $prev_post->post_title ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="single-post__nav-next">
                <?php if ( $next_post ) : ?>
                    <span class="single-post__nav-label">Next &rarr;</span>
                    <a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>">
                        <?php echo esc_html( $next_post->post_title ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
        <?php endif; ?>

        <!-- Back to news link -->
        <div class="single-post__back">
            <a href="<?php echo esc_url( home_url( '/news/' ) ); ?>">&larr; Back to News</a>
        </div>

    </div>
</article>

<?php get_footer(); ?>
