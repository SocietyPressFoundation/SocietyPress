<?php
/**
 * Event Calendar
 *
 * Provides a month-view calendar displaying events. Rendered server-side
 * for simplicity and accessibility, with AJAX navigation between months.
 *
 * WHY: Societies need a visual calendar so members (many of whom are seniors)
 *      can see at a glance what's happening this month. A grid calendar is
 *      the most universally understood format for date-based information.
 *
 * Usage: [societypress_calendar]
 *
 * @package SocietyPress
 * @since 0.62d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Calendar
 *
 * Renders a month-view event calendar via shortcode.
 */
class SocietyPress_Calendar {

	/**
	 * Constructor.
	 *
	 * WHY: Registers the shortcode and the AJAX endpoint for month navigation.
	 */
	public function __construct() {
		add_shortcode( 'societypress_calendar', array( $this, 'render_calendar_shortcode' ) );

		// AJAX for both logged-in and non-logged-in users (calendar is public)
		add_action( 'wp_ajax_societypress_calendar_navigate', array( $this, 'ajax_navigate' ) );
		add_action( 'wp_ajax_nopriv_societypress_calendar_navigate', array( $this, 'ajax_navigate' ) );
	}

	/**
	 * Render the calendar shortcode.
	 *
	 * WHY: Entry point for [societypress_calendar]. Enqueues assets and
	 *      renders the initial month view.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Calendar HTML.
	 */
	public function render_calendar_shortcode( $atts = array() ): string {
		// Enqueue calendar-specific styles and scripts
		wp_enqueue_style(
			'societypress-calendar',
			SOCIETYPRESS_URL . 'assets/css/calendar.css',
			array(),
			SOCIETYPRESS_VERSION
		);

		wp_enqueue_script(
			'societypress-calendar',
			SOCIETYPRESS_URL . 'assets/js/calendar.js',
			array(),
			SOCIETYPRESS_VERSION,
			true
		);

		wp_localize_script( 'societypress-calendar', 'spCalendar', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'sp_calendar_nonce' ),
		) );

		// Default to the current month
		$year  = (int) current_time( 'Y' );
		$month = (int) current_time( 'n' );

		return '<div id="sp-calendar-wrapper">' . $this->render_month( $year, $month ) . '</div>';
	}

	/**
	 * AJAX handler for month navigation.
	 *
	 * WHY: Lets users navigate between months without a full page reload.
	 *      Keeps the experience smooth, especially on slower connections.
	 */
	public function ajax_navigate(): void {
		check_ajax_referer( 'sp_calendar_nonce', 'nonce' );

		$year  = isset( $_POST['year'] )  ? absint( $_POST['year'] )  : (int) current_time( 'Y' );
		$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : (int) current_time( 'n' );

		// Clamp to reasonable range (5 years past to 2 years future)
		$current_year = (int) current_time( 'Y' );
		$year = max( $current_year - 5, min( $current_year + 2, $year ) );
		$month = max( 1, min( 12, $month ) );

		wp_send_json_success( array(
			'html' => $this->render_month( $year, $month ),
		) );
	}

	/**
	 * Render a single month's calendar grid.
	 *
	 * WHY: This is the core rendering method. It builds a standard 7-column
	 *      calendar grid with events placed on their dates. Events are shown
	 *      as clickable links so members can quickly navigate to event details.
	 *
	 * @param int $year  The year to display.
	 * @param int $month The month to display (1-12).
	 * @return string Calendar HTML.
	 */
	public function render_month( int $year, int $month ): string {
		// Get events for this month
		$events_by_date = $this->get_month_events( $year, $month );

		// Calculate previous and next month for navigation
		$prev_month = $month - 1;
		$prev_year  = $year;
		if ( $prev_month < 1 ) {
			$prev_month = 12;
			$prev_year--;
		}

		$next_month = $month + 1;
		$next_year  = $year;
		if ( $next_month > 12 ) {
			$next_month = 1;
			$next_year++;
		}

		// The first day of this month (0 = Sunday, 6 = Saturday)
		$first_day_of_month = (int) gmdate( 'w', mktime( 0, 0, 0, $month, 1, $year ) );
		$days_in_month      = (int) gmdate( 't', mktime( 0, 0, 0, $month, 1, $year ) );
		$month_name         = date_i18n( 'F Y', mktime( 0, 0, 0, $month, 1, $year ) );

		// Today's date for highlighting
		$today = current_time( 'Y-m-d' );

		// Start building the HTML
		$html = '<div class="sp-calendar" data-year="' . esc_attr( $year ) . '" data-month="' . esc_attr( $month ) . '">';

		// Navigation header
		$html .= '<div class="sp-cal-header">';
		$html .= '<button type="button" class="sp-cal-nav sp-cal-prev" '
		       . 'data-year="' . esc_attr( $prev_year ) . '" '
		       . 'data-month="' . esc_attr( $prev_month ) . '" '
		       . 'aria-label="' . esc_attr__( 'Previous month', 'societypress' ) . '">'
		       . '&larr; ' . esc_html( date_i18n( 'M', mktime( 0, 0, 0, $prev_month, 1, $prev_year ) ) )
		       . '</button>';
		$html .= '<h2 class="sp-cal-title">' . esc_html( $month_name ) . '</h2>';
		$html .= '<button type="button" class="sp-cal-nav sp-cal-next" '
		       . 'data-year="' . esc_attr( $next_year ) . '" '
		       . 'data-month="' . esc_attr( $next_month ) . '" '
		       . 'aria-label="' . esc_attr__( 'Next month', 'societypress' ) . '">'
		       . esc_html( date_i18n( 'M', mktime( 0, 0, 0, $next_month, 1, $next_year ) ) ) . ' &rarr;'
		       . '</button>';
		$html .= '</div>';

		// Day-of-week headers
		$html .= '<div class="sp-cal-grid">';
		$day_names = array(
			__( 'Sun', 'societypress' ),
			__( 'Mon', 'societypress' ),
			__( 'Tue', 'societypress' ),
			__( 'Wed', 'societypress' ),
			__( 'Thu', 'societypress' ),
			__( 'Fri', 'societypress' ),
			__( 'Sat', 'societypress' ),
		);
		foreach ( $day_names as $day_name ) {
			$html .= '<div class="sp-cal-day-header">' . esc_html( $day_name ) . '</div>';
		}

		// Empty cells before the first day of the month
		for ( $i = 0; $i < $first_day_of_month; $i++ ) {
			$html .= '<div class="sp-cal-cell sp-cal-empty"></div>';
		}

		// Day cells with events
		for ( $day = 1; $day <= $days_in_month; $day++ ) {
			$date_str  = sprintf( '%04d-%02d-%02d', $year, $month, $day );
			$is_today  = ( $date_str === $today );
			$has_events = isset( $events_by_date[ $date_str ] );

			$cell_classes = array( 'sp-cal-cell' );
			if ( $is_today ) {
				$cell_classes[] = 'sp-cal-today';
			}
			if ( $has_events ) {
				$cell_classes[] = 'sp-cal-has-events';
			}

			$html .= '<div class="' . esc_attr( implode( ' ', $cell_classes ) ) . '">';
			$html .= '<span class="sp-cal-day-number">' . esc_html( $day ) . '</span>';

			// List events for this day
			if ( $has_events ) {
				$html .= '<div class="sp-cal-events">';
				foreach ( $events_by_date[ $date_str ] as $event ) {
					$html .= '<a href="' . esc_url( get_permalink( $event->ID ) ) . '" '
					       . 'class="sp-cal-event" '
					       . 'title="' . esc_attr( $event->post_title ) . '">'
					       . esc_html( $event->post_title )
					       . '</a>';
				}
				$html .= '</div>';
			}

			$html .= '</div>';
		}

		// Empty cells after the last day to complete the grid row
		$total_cells = $first_day_of_month + $days_in_month;
		$remaining   = $total_cells % 7;
		if ( $remaining > 0 ) {
			for ( $i = 0; $i < ( 7 - $remaining ); $i++ ) {
				$html .= '<div class="sp-cal-cell sp-cal-empty"></div>';
			}
		}

		$html .= '</div>'; // .sp-cal-grid
		$html .= '</div>'; // .sp-calendar

		return $html;
	}

	/**
	 * Get all events for a given month, grouped by date.
	 *
	 * WHY: One query per month is efficient. We group results by date so
	 *      the rendering loop can quickly look up events for each day cell.
	 *
	 * @param int $year  The year.
	 * @param int $month The month (1-12).
	 * @return array Associative array keyed by 'Y-m-d' date strings, each containing array of post objects.
	 */
	private function get_month_events( int $year, int $month ): array {
		$first_day = sprintf( '%04d-%02d-01', $year, $month );
		$last_day  = sprintf( '%04d-%02d-%02d', $year, $month, gmdate( 't', mktime( 0, 0, 0, $month, 1, $year ) ) );

		$query = new WP_Query( array(
			'post_type'      => 'sp_event',
			'posts_per_page' => 100, // A society won't have more than ~100 events in a month
			'post_status'    => 'publish',
			'meta_key'       => '_sp_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_sp_event_date',
					'value'   => array( $first_day, $last_day ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				),
			),
		) );

		$events_by_date = array();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$event_date = SocietyPress_Events::get_event_date( get_the_ID() );
				if ( $event_date ) {
					$events_by_date[ $event_date ][] = $query->post;
				}
			}
			wp_reset_postdata();
		}

		return $events_by_date;
	}
}
