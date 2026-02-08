<?php
/**
 * SocietyPress Admin - Events List View
 *
 * Displays upcoming and past events with registration counts.
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get filter parameters
$view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'upcoming';
$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page = 20;

// Build query args
$query_args = [
    'post_type'      => 'sp_event',
    'posts_per_page' => $per_page,
    'paged'          => $paged,
    'post_status'    => 'publish',
    'meta_key'       => '_sp_event_date',
    'orderby'        => 'meta_value',
    'order'          => $view === 'past' ? 'DESC' : 'ASC',
];

// Filter by upcoming or past
if ( $view === 'upcoming' ) {
    $query_args['meta_query'] = [
        [
            'key'     => '_sp_event_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ],
    ];
} elseif ( $view === 'past' ) {
    $query_args['meta_query'] = [
        [
            'key'     => '_sp_event_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '<',
            'type'    => 'DATE',
        ],
    ];
}

$events_query = new WP_Query( $query_args );
$total_pages = $events_query->max_num_pages;

// Group events by month for display
$events_by_month = [];
if ( $events_query->have_posts() ) {
    while ( $events_query->have_posts() ) {
        $events_query->the_post();
        $event_id = get_the_ID();
        $event_date = get_post_meta( $event_id, '_sp_event_date', true );
        $month_key = $event_date ? date( 'F Y', strtotime( $event_date ) ) : 'No Date';
        
        // Get registration count
        $registered_count = 0;
        if ( societypress()->event_registrations ) {
            $registrations = societypress()->event_registrations->get_registrations_for_event( $event_id );
            $registered_count = count( array_filter( $registrations, function( $r ) {
                return $r->status === 'registered';
            } ) );
        }
        
        $events_by_month[ $month_key ][] = [
            'id'         => $event_id,
            'title'      => get_the_title(),
            'date'       => $event_date,
            'time'       => get_post_meta( $event_id, '_sp_event_time', true ),
            'end_time'   => get_post_meta( $event_id, '_sp_event_end_time', true ),
            'location'   => get_post_meta( $event_id, '_sp_event_location', true ),
            'registered' => $registered_count,
        ];
    }
    wp_reset_postdata();
}
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Events</h1>
    <a href="<?php echo esc_url( $router->url( 'events', [ 'action' => 'new' ] ) ); ?>" class="sp-button sp-button--primary">
        + Add New Event
    </a>
</header>

<!-- View Filter -->
<div class="sp-filters" style="margin-bottom: var(--sp-spacing-lg);">
    <a href="<?php echo esc_url( $router->url( 'events' ) ); ?>" 
       class="sp-button <?php echo $view === 'upcoming' ? 'sp-button--primary' : 'sp-button--secondary'; ?>">
        Upcoming
    </a>
    <a href="<?php echo esc_url( $router->url( 'events' ) . '?view=past' ); ?>" 
       class="sp-button <?php echo $view === 'past' ? 'sp-button--primary' : 'sp-button--secondary'; ?>">
        Past Events
    </a>
    <a href="<?php echo esc_url( $router->url( 'events' ) . '?view=all' ); ?>" 
       class="sp-button <?php echo $view === 'all' ? 'sp-button--primary' : 'sp-button--secondary'; ?>">
        All Events
    </a>
</div>

<?php if ( empty( $events_by_month ) ) : ?>
    <div class="sp-card">
        <p style="text-align: center; color: var(--sp-gray-500); padding: var(--sp-spacing-xl);">
            <?php if ( $view === 'upcoming' ) : ?>
                No upcoming events scheduled.
            <?php elseif ( $view === 'past' ) : ?>
                No past events found.
            <?php else : ?>
                No events found.
            <?php endif; ?>
            <a href="<?php echo esc_url( $router->url( 'events', [ 'action' => 'new' ] ) ); ?>">Create your first event</a>
        </p>
    </div>
<?php else : ?>
    <?php foreach ( $events_by_month as $month => $events ) : ?>
        <h2 style="margin: var(--sp-spacing-lg) 0 var(--sp-spacing-md); font-size: var(--sp-font-size-xl); color: var(--sp-gray-700);">
            <?php echo esc_html( $month ); ?>
        </h2>
        
        <?php foreach ( $events as $event ) : ?>
            <div class="sp-card" style="margin-bottom: var(--sp-spacing-md); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="display: flex; align-items: baseline; gap: var(--sp-spacing-md); margin-bottom: var(--sp-spacing-xs);">
                        <strong style="font-size: var(--sp-font-size-lg);">
                            <?php 
                            if ( $event['date'] ) {
                                echo esc_html( date( 'D j', strtotime( $event['date'] ) ) );
                            }
                            ?>
                        </strong>
                        <a href="<?php echo esc_url( $router->url( 'events', [ 'id' => $event['id'] ] ) ); ?>" 
                           style="font-size: var(--sp-font-size-lg); font-weight: 500;">
                            <?php echo esc_html( $event['title'] ); ?>
                        </a>
                        <?php if ( $event['registered'] > 0 ) : ?>
                            <span style="color: var(--sp-gray-500);">
                                (<?php echo $event['registered']; ?> registered)
                            </span>
                        <?php endif; ?>
                    </div>
                    <div style="color: var(--sp-gray-600);">
                        <?php 
                        $time_str = $event['time'] ?: '';
                        if ( $event['end_time'] ) {
                            $time_str .= ' - ' . $event['end_time'];
                        }
                        if ( $time_str ) {
                            echo esc_html( $time_str );
                        }
                        if ( $event['location'] ) {
                            if ( $time_str ) echo ' • ';
                            echo esc_html( $event['location'] );
                        }
                        ?>
                    </div>
                </div>
                <div class="sp-table-actions">
                    <a href="<?php echo esc_url( $router->url( 'events', [ 'id' => $event['id'] ] ) ); ?>" class="sp-button sp-button--secondary">
                        Manage
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    
    <!-- Pagination -->
    <?php if ( $total_pages > 1 ) : ?>
        <div class="sp-pagination" style="margin-top: var(--sp-spacing-xl);">
            <div class="sp-pagination-info">
                Page <?php echo $paged; ?> of <?php echo $total_pages; ?>
            </div>
            <div class="sp-pagination-links">
                <?php if ( $paged > 1 ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>" class="sp-pagination-link">‹ Prev</a>
                <?php endif; ?>
                <?php if ( $paged < $total_pages ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>" class="sp-pagination-link">Next ›</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
