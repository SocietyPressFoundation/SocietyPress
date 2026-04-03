<?php
/**
 * Parlor Child Theme — Page Template
 *
 * WHY this overrides the parent: Parlor supports an optional right sidebar.
 * The parent theme renders pages as full-width only. This template checks
 * whether the parlor-sidebar widget area has widgets — if yes, it uses a
 * two-column layout (content + sidebar); if no, it renders full-width.
 * This means the admin gets a sidebar simply by adding widgets to it,
 * with no template switching or configuration needed.
 *
 * @package Parlor
 * @since   1.0.0
 */

get_header();
?>

<div id="main-content" class="site-content">
    <?php
    // WHY: Detect sidebar state once and apply the appropriate wrapper class.
    // The CSS uses .parlor-has-sidebar to trigger the two-column grid layout.
    // Without widgets, the content area gets the full width automatically.
    $has_sidebar = is_active_sidebar( 'parlor-sidebar' );
    ?>
    <div class="parlor-content-wrap<?php echo $has_sidebar ? ' parlor-has-sidebar' : ''; ?>">

        <div class="content-area">
            <?php while ( have_posts() ) : the_post(); ?>

            <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                <div class="post-thumbnail">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>

            <?php endwhile; ?>
        </div>

        <?php if ( $has_sidebar ) : ?>
            <?php get_sidebar(); ?>
        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>
