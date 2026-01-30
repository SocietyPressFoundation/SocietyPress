<?php
/**
 * Template part for displaying posts
 *
 * WHY: Reusable post content template for archive and search pages.
 *
 * @package SocietyPress
 * @since 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-thumbnail">
			<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php the_post_thumbnail( 'sp-featured', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
			</a>
		</div>
	<?php endif; ?>

	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;
		?>

		<div class="entry-meta">
			<?php
			societypress_posted_on();
			societypress_posted_by();
			?>
		</div>
	</header>

	<div class="entry-content">
		<?php
		if ( is_singular() ) :
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'societypress' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'societypress' ),
					'after'  => '</div>',
				)
			);
		else :
			the_excerpt();
			?>
			<a href="<?php the_permalink(); ?>" class="read-more">
				<?php esc_html_e( 'Read More', 'societypress' ); ?>
				<span class="screen-reader-text"> <?php echo esc_html( get_the_title() ); ?></span>
			</a>
			<?php
		endif;
		?>
	</div>

	<?php if ( is_singular() && ( has_category() || has_tag() ) ) : ?>
		<footer class="entry-footer">
			<?php societypress_entry_footer(); ?>
		</footer>
	<?php endif; ?>

</article>
