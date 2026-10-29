<?php
/**
 * Blog follow box (template-parts/blog-follow.php)
 *
 * Shown under the blog list and under every post: email signup plus the RSS
 * feed. One partial so both places stay worded the same.
 *
 * WHY the SocietyPress subscribe shortcode rather than a mailing service:
 * the marketing site runs SocietyPress itself, so the list lives in the same
 * Subscribers screen and goes out through the same Send Email tool every
 * society uses — the project eats its own cooking.
 *
 * @package getsocietypress
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="blog-follow section">
    <div class="container container--narrow">
        <div class="blog-follow__box">
            <h2 class="blog-follow__title">Follow the blog</h2>
            <p class="blog-follow__text">
                Get new posts by email. Confirm once, unsubscribe any time.
            </p>
            <?php
            echo do_shortcode( '[societypress_subscribe title="" button="Subscribe" show_name="0"]' );
            ?>
            <p class="blog-follow__rss">
                Prefer a feed reader?
                <a href="<?php echo esc_url( get_feed_link() ); ?>">Subscribe by RSS</a>
            </p>
        </div>
    </div>
</section>
