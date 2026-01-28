<?php
/**
 * Template part for displaying search results
 *
 * WHY: Simplified post display for search results with excerpt and metadata.
 *
 * @package SocietyPress
 * @since 1.01d
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'search-result' ); ?>>

	<header class="entry-header">
		<?php
		// Show post type badge if not a standard post
		if ( 'post' !== get_post_type() ) :
			$post_type_obj = get_post_type_object( get_post_type() );
			if ( $post_type_obj ) :
				?>
				<span class="post-type-badge"><?php echo esc_html( $post_type_obj->labels->singular_name ); ?></span>
				<?php
			endif;
		endif;

		the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
		?>

		<div class="entry-meta">
			<?php societypress_posted_on(); ?>
		</div>
	</header>

	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div>

	<div class="entry-footer">
		<a href="<?php the_permalink(); ?>" class="read-more">
			<?php esc_html_e( 'Read More', 'societypress' ); ?>
			<span class="screen-reader-text"> <?php echo esc_html( get_the_title() ); ?></span>
		</a>
	</div>

</article>
