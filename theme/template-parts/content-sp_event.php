<?php
/**
 * Template part for displaying events
 *
 * WHY: Custom display for sp_event post type with event-specific metadata.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Get event categories for filtering
$categories = get_the_terms( get_the_ID(), 'sp_event_category' );
$category_slugs = array();
if ( $categories && ! is_wp_error( $categories ) ) {
	foreach ( $categories as $category ) {
		$category_slugs[] = $category->slug;
	}
}
$data_categories = ! empty( $category_slugs ) ? implode( ' ', $category_slugs ) : '';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'event-card' ); ?> data-categories="<?php echo esc_attr( $data_categories ); ?>">

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="event-thumbnail">
			<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php the_post_thumbnail( 'sp-featured', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="event-content">
		<header class="event-header">
			<?php
			if ( is_singular() ) :
				the_title( '<h1 class="event-title">', '</h1>' );
			else :
				the_title( '<h2 class="event-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			endif;
			?>

			<div class="event-meta">
				<?php
				$event_date = sp_get_formatted_datetime( get_the_ID(), 'F j, Y' );
				$event_time = sp_get_event_time( get_the_ID() );
				$event_location = sp_get_event_location( get_the_ID() );
				$event_instructors = sp_get_event_instructors( get_the_ID() );
				?>

				<?php if ( $event_date ) : ?>
					<div class="event-date">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
							<path d="M11 1h3a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h3V0h2v1h4V0h2v1zm2 4H3v9h10V5z"/>
						</svg>
						<time datetime="<?php echo esc_attr( sp_get_event_date( get_the_ID() ) ); ?>">
							<?php echo esc_html( $event_date ); ?>
						</time>
					</div>
				<?php endif; ?>

				<?php if ( $event_time ) : ?>
					<div class="event-time">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
							<path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm0 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2zm1 3v4.414l2.293 2.293-1.414 1.414L7 10.243V5h2z"/>
						</svg>
						<span><?php echo esc_html( date( 'g:i A', strtotime( $event_time ) ) ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $event_location ) : ?>
					<div class="event-location">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
							<path d="M8 0a6 6 0 0 0-6 6c0 4.5 6 10 6 10s6-5.5 6-10a6 6 0 0 0-6-6zm0 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
						</svg>
						<span><?php echo esc_html( $event_location ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $event_instructors ) : ?>
					<div class="event-instructors">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
							<path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm6 7c0-3.314-2.686-6-6-6s-6 2.686-6 6h12z"/>
						</svg>
						<span><?php echo esc_html( $event_instructors ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( sp_is_recurring( get_the_ID() ) ) : ?>
					<div class="event-recurring">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
							<path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
							<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
						</svg>
						<span><?php echo esc_html( sp_get_recurring_description( get_the_ID() ) ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( sp_is_registration_required( get_the_ID() ) ) : ?>
					<div class="event-registration-badge">
						<span class="badge"><?php esc_html_e( 'Registration Required', 'societypress' ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</header>

		<div class="event-description">
			<?php
			if ( is_singular() ) :
				the_content();
			else :
				the_excerpt();
				?>
				<a href="<?php the_permalink(); ?>" class="event-details-link">
					<?php esc_html_e( 'View Details', 'societypress' ); ?>
					<span class="screen-reader-text"> <?php echo esc_html( get_the_title() ); ?></span>
				</a>
				<?php
			endif;
			?>
		</div>
	</div>

</article>
