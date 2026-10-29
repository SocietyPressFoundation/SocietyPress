<?php
/**
 * Comments Template (comments.php)
 *
 * Discussion under blog posts. Kept to WordPress's own list and form so
 * moderation, spam checks and reply threading all work as they do anywhere
 * else in WordPress — only the markup classes are the theme's.
 *
 * @package getsocietypress
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
    return;
}
?>
<div id="comments" class="comments">

    <?php if ( have_comments() ) : ?>
        <h2 class="comments__title">
            <?php
            $count = get_comments_number();
            echo esc_html( 1 === (int) $count ? '1 comment' : $count . ' comments' );
            ?>
        </h2>

        <ol class="comments__list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 40,
            ) );
            ?>
        </ol>

        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() ) : ?>
        <p class="comments__closed">Comments are closed on this post.</p>
    <?php endif; ?>

    <?php
    comment_form( array(
        'title_reply'          => 'Leave a comment',
        'title_reply_before'   => '<h2 id="reply-title" class="comments__form-title">',
        'title_reply_after'    => '</h2>',
        'class_form'           => 'comments__form',
        'class_submit'         => 'btn btn-primary',
        'label_submit'         => 'Post Comment',
        'comment_notes_before' => '<p class="comments__notes">Your email address won\'t be shown. Your first comment waits for approval before it appears.</p>',
    ) );
    ?>

</div>
