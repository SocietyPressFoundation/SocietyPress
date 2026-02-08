<?php
/**
 * SocietyPress Admin Dashboard
 *
 * The main landing page for /sp-admin/. Shows at-a-glance society status,
 * quick actions, upcoming events, and recent activity.
 *
 * WHY THIS LAYOUT:
 * - Stats at the top give immediate sense of society health
 * - Quick actions are the most common tasks (add member, create event)
 * - Upcoming events help with planning
 * - Recent activity shows what's happening
 *
 * @package SocietyPress
 * @since 0.59
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get member statistics
$members = societypress()->members;
$total_members  = $members ? $members->count_members() : 0;
$active_members = $members ? $members->count_members( [ 'status' => 'active' ] ) : 0;

// Count new members this year
$new_this_year = 0;
if ( $members ) {
    global $wpdb;
    $table = $wpdb->prefix . 'sp_members';
    $year_start = date( 'Y-01-01' );
    $new_this_year = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE join_date >= %s",
        $year_start
    ) );
}

// Count expiring soon (within 30 days)
$expiring_soon = 0;
if ( $members ) {
    global $wpdb;
    $table = $wpdb->prefix . 'sp_members';
    $today = date( 'Y-m-d' );
    $thirty_days = date( 'Y-m-d', strtotime( '+30 days' ) );
    $expiring_soon = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE status = 'active' AND expiration_date BETWEEN %s AND %s",
        $today,
        $thirty_days
    ) );
}

// Get upcoming events (next 5)
$upcoming_events = [];
$events_query = new WP_Query( [
    'post_type'      => 'sp_event',
    'posts_per_page' => 5,
    'post_status'    => 'publish',
    'meta_key'       => '_sp_event_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => [
        [
            'key'     => '_sp_event_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ],
    ],
] );

if ( $events_query->have_posts() ) {
    while ( $events_query->have_posts() ) {
        $events_query->the_post();
        $event_id = get_the_ID();

        // Get registration count if event registrations are available
        $registered_count = 0;
        if ( societypress()->event_registrations ) {
            $registrations = societypress()->event_registrations->get_registrations_for_event( $event_id );
            $registered_count = count( array_filter( $registrations, function( $r ) {
                return $r->status === 'registered';
            } ) );
        }

        $upcoming_events[] = [
            'id'         => $event_id,
            'title'      => get_the_title(),
            'date'       => get_post_meta( $event_id, '_sp_event_date', true ),
            'time'       => get_post_meta( $event_id, '_sp_event_time', true ),
            'location'   => get_post_meta( $event_id, '_sp_event_location', true ),
            'registered' => $registered_count,
        ];
    }
    wp_reset_postdata();
}

// Get recent activity (last 10 entries from audit log)
$recent_activity = [];
global $wpdb;
$audit_table = $wpdb->prefix . 'sp_audit_log';
if ( $wpdb->get_var( "SHOW TABLES LIKE '{$audit_table}'" ) === $audit_table ) {
    $activities = $wpdb->get_results(
        "SELECT * FROM {$audit_table} ORDER BY created_at DESC LIMIT 10"
    );

    foreach ( $activities as $activity ) {
        $recent_activity[] = [
            'action'      => $activity->action,
            'entity_type' => $activity->entity_type,
            'entity_id'   => $activity->entity_id,
            'created_at'  => $activity->created_at,
            'changed_by'  => $activity->changed_by,
        ];
    }
}

// Get current user first name for greeting
$current_user = wp_get_current_user();
$first_name = $current_user->first_name ?: $current_user->display_name;

// Helper function to get activity icon
function sp_get_activity_icon( $action ) {
    switch ( $action ) {
        case 'create':
            return '➕';
        case 'update':
            return '✏️';
        case 'delete':
            return '🗑️';
        default:
            return '📋';
    }
}
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Welcome, <?php echo esc_html( $first_name ); ?>!</h1>
</header>

<!-- Stats Cards -->
<div class="sp-stats-grid">
    <div class="sp-stat-card">
        <span class="sp-stat-value"><?php echo number_format( $active_members ); ?></span>
        <span class="sp-stat-label">Active Members</span>
    </div>
    <div class="sp-stat-card">
        <span class="sp-stat-value"><?php echo number_format( $new_this_year ); ?></span>
        <span class="sp-stat-label">New This Year</span>
    </div>
    <div class="sp-stat-card">
        <span class="sp-stat-value"><?php echo number_format( $expiring_soon ); ?></span>
        <span class="sp-stat-label">Expiring Soon</span>
    </div>
</div>

<!-- Quick Actions -->
<?php if ( $router->user_can_access_module( 'members' ) || $router->user_can_access_module( 'events' ) ) : ?>
<div class="sp-quick-actions">
    <?php if ( $router->user_can_access_module( 'members' ) ) : ?>
        <a href="<?php echo esc_url( $router->url( 'members', [ 'action' => 'new' ] ) ); ?>" class="sp-button sp-button--primary sp-button--large">
            + Add New Member
        </a>
    <?php endif; ?>

    <?php if ( $router->user_can_access_module( 'events' ) ) : ?>
        <a href="<?php echo esc_url( $router->url( 'events', [ 'action' => 'new' ] ) ); ?>" class="sp-button sp-button--secondary sp-button--large">
            + Create Event
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="sp-dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--sp-spacing-xl);">
    <!-- Upcoming Events -->
    <?php if ( $router->user_can_access_module( 'events' ) ) : ?>
    <div class="sp-card">
        <div class="sp-card-header">
            <h2 class="sp-card-title">Upcoming Events</h2>
            <a href="<?php echo esc_url( $router->url( 'events' ) ); ?>" class="sp-table-action">View All</a>
        </div>

        <?php if ( empty( $upcoming_events ) ) : ?>
            <p style="color: var(--sp-gray-500);">No upcoming events scheduled.</p>
        <?php else : ?>
            <ul class="sp-activity-list">
                <?php foreach ( $upcoming_events as $event ) : ?>
                    <li class="sp-activity-item">
                        <span class="sp-activity-icon">📅</span>
                        <div class="sp-activity-content">
                            <p class="sp-activity-text">
                                <strong><?php echo esc_html( $event['title'] ); ?></strong>
                                <?php if ( $event['registered'] > 0 ) : ?>
                                    <span style="color: var(--sp-gray-500);">(<?php echo (int) $event['registered']; ?> registered)</span>
                                <?php endif; ?>
                            </p>
                            <span class="sp-activity-time">
                                <?php
                                $date = $event['date'] ? date( 'M j, Y', strtotime( $event['date'] ) ) : 'TBD';
                                $time = $event['time'] ?: '';
                                echo esc_html( $date . ( $time ? ' at ' . $time : '' ) );
                                ?>
                            </span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Recent Activity -->
    <div class="sp-card">
        <div class="sp-card-header">
            <h2 class="sp-card-title">Recent Activity</h2>
        </div>

        <?php if ( empty( $recent_activity ) ) : ?>
            <p style="color: var(--sp-gray-500);">No recent activity to display.</p>
        <?php else : ?>
            <ul class="sp-activity-list">
                <?php foreach ( $recent_activity as $activity ) :
                    // Build activity description
                    $description = ucfirst( $activity['action'] ) . ' ' . str_replace( '_', ' ', $activity['entity_type'] );
                    if ( $activity['entity_id'] ) {
                        $description .= ' #' . $activity['entity_id'];
                    }

                    // Get user name who made the change
                    $changed_by = 'System';
                    if ( $activity['changed_by'] ) {
                        $user_data = get_userdata( $activity['changed_by'] );
                        $changed_by = $user_data ? $user_data->display_name : 'User #' . $activity['changed_by'];
                    }

                    // Format time ago
                    $time_ago = human_time_diff( strtotime( $activity['created_at'] ), current_time( 'timestamp' ) ) . ' ago';
                ?>
                    <li class="sp-activity-item">
                        <span class="sp-activity-icon"><?php echo sp_get_activity_icon( $activity['action'] ); ?></span>
                        <div class="sp-activity-content">
                            <p class="sp-activity-text"><?php echo esc_html( $description ); ?></p>
                            <span class="sp-activity-time">by <?php echo esc_html( $changed_by ); ?> • <?php echo esc_html( $time_ago ); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
