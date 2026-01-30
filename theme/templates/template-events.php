<?php
/**
 * Template Name: Events Calendar
 * Template Post Type: page
 *
 * WHY: Dedicated page template for displaying upcoming events in calendar format.
 *
 * @package SocietyPress
 * @since 1.01d
 */

get_header();
?>

<main id="primary" class="site-main events-page">
	<div class="sp-container">

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<header class="page-header">
				<?php the_title( '<h1 class="page-title">', '</h1>' ); ?>

				<?php if ( get_the_content() ) : ?>
					<div class="page-description">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
			</header>

			<?php
		endwhile;
		?>

		<?php if ( societypress_plugin_is_active() && post_type_exists( 'sp_event' ) ) : ?>

			<div class="events-filter">
				<?php
				// Event category filter
				$categories = get_terms(
					array(
						'taxonomy'   => 'sp_event_category',
						'hide_empty' => true,
					)
				);

				if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
					?>
					<div class="event-categories">
						<button class="category-filter active" data-category="all">
							<?php esc_html_e( 'All Events', 'societypress' ); ?>
						</button>
						<?php foreach ( $categories as $category ) : ?>
							<button class="category-filter" data-category="<?php echo esc_attr( $category->slug ); ?>">
								<?php echo esc_html( $category->name ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php
			// Query upcoming events
			$events_query = new WP_Query(
				array(
					'post_type'      => 'sp_event',
					'posts_per_page' => -1,
					'meta_key'       => 'sp_event_date',
					'orderby'        => 'meta_value',
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'     => 'sp_event_date',
							'value'   => date( 'Y-m-d' ),
							'compare' => '>=',
							'type'    => 'DATE',
						),
					),
				)
			);

			if ( $events_query->have_posts() ) :
				?>
				<div class="events-grid">
					<?php
					while ( $events_query->have_posts() ) :
						$events_query->the_post();
						get_template_part( 'template-parts/content', 'sp_event' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>

			<?php else : ?>

				<div class="no-events">
					<p><?php esc_html_e( 'No upcoming events at this time. Please check back later.', 'societypress' ); ?></p>
				</div>

			<?php endif; ?>

			<?php
			// Past events section
			$past_events = new WP_Query(
				array(
					'post_type'      => 'sp_event',
					'posts_per_page' => 6,
					'meta_key'       => 'sp_event_date',
					'orderby'        => 'meta_value',
					'order'          => 'DESC',
					'meta_query'     => array(
						array(
							'key'     => 'sp_event_date',
							'value'   => date( 'Y-m-d' ),
							'compare' => '<',
							'type'    => 'DATE',
						),
					),
				)
			);

			if ( $past_events->have_posts() ) :
				?>
				<section class="past-events">
					<h2><?php esc_html_e( 'Past Events', 'societypress' ); ?></h2>
					<div class="events-grid">
						<?php
						while ( $past_events->have_posts() ) :
							$past_events->the_post();
							get_template_part( 'template-parts/content', 'sp_event' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endif; ?>

		<?php else : ?>

			<div class="plugin-required-notice">
				<p><?php esc_html_e( 'The SocietyPress plugin is required to display events.', 'societypress' ); ?></p>
			</div>

		<?php endif; ?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
