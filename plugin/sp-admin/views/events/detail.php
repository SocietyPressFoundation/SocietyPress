<?php
/**
 * SocietyPress Admin - Event Detail/Edit View
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$event_id = $router->get_param( 'id' );
$action   = $router->get_param( 'action', '' );
$is_new   = ( $action === 'new' );
$is_edit  = ( $action === 'edit' ) || $is_new;

$event = null;
if ( $event_id && ! $is_new ) {
    $event = get_post( $event_id );
    if ( ! $event || $event->post_type !== 'sp_event' ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Event not found.</div>';
        return;
    }
}

// Handle form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['sp_event_nonce'] ) ) {
    if ( ! wp_verify_nonce( $_POST['sp_event_nonce'], 'sp_save_event' ) ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Security check failed.</div>';
    } else {
        $post_data = [
            'post_title'   => sanitize_text_field( $_POST['post_title'] ?? '' ),
            'post_content' => wp_kses_post( $_POST['post_content'] ?? '' ),
            'post_status'  => 'publish',
            'post_type'    => 'sp_event',
        ];
        
        if ( $is_new ) {
            $event_id = wp_insert_post( $post_data );
        } else {
            $post_data['ID'] = $event_id;
            wp_update_post( $post_data );
        }
        
        if ( $event_id && ! is_wp_error( $event_id ) ) {
            // Save meta
            update_post_meta( $event_id, '_sp_event_date', sanitize_text_field( $_POST['event_date'] ?? '' ) );
            update_post_meta( $event_id, '_sp_event_time', sanitize_text_field( $_POST['event_time'] ?? '' ) );
            update_post_meta( $event_id, '_sp_event_end_time', sanitize_text_field( $_POST['event_end_time'] ?? '' ) );
            update_post_meta( $event_id, '_sp_event_location', sanitize_text_field( $_POST['event_location'] ?? '' ) );
            update_post_meta( $event_id, '_sp_event_address', sanitize_textarea_field( $_POST['event_address'] ?? '' ) );
            update_post_meta( $event_id, '_sp_registration_required', isset( $_POST['registration_required'] ) ? 1 : 0 );
            update_post_meta( $event_id, '_sp_event_capacity', absint( $_POST['event_capacity'] ?? 0 ) );
            
            wp_redirect( $router->url( 'events', [ 'id' => $event_id ] ) . '?success=saved' );
            exit;
        }
    }
}

// Get event data
if ( $event ) {
    $event_date = get_post_meta( $event_id, '_sp_event_date', true );
    $event_time = get_post_meta( $event_id, '_sp_event_time', true );
    $event_end_time = get_post_meta( $event_id, '_sp_event_end_time', true );
    $event_location = get_post_meta( $event_id, '_sp_event_location', true );
    $event_address = get_post_meta( $event_id, '_sp_event_address', true );
    $registration_required = get_post_meta( $event_id, '_sp_registration_required', true );
    $event_capacity = get_post_meta( $event_id, '_sp_event_capacity', true );
} else {
    // Defaults for new event
    $event_date = '';
    $event_time = '';
    $event_end_time = '';
    $event_location = 'Dwyer Center Classroom';
    $event_address = "the society\n911 Melissa Dr\nSpringfield, TX 78213-2024";
    $registration_required = 0;
    $event_capacity = 0;
}

// Get registrations if viewing existing event
$registrations = [];
if ( $event_id && societypress()->event_registrations ) {
    $registrations = societypress()->event_registrations->get_registrations_for_event( $event_id );
}
$registered_count = count( array_filter( $registrations, function( $r ) { return $r->status === 'registered'; } ) );
?>

<a href="<?php echo esc_url( $router->url( 'events' ) ); ?>" class="sp-back-link">
    ← Back to Events
</a>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">
        <?php echo $is_new ? 'Add New Event' : esc_html( $event->post_title ?? 'Event' ); ?>
    </h1>
    <?php if ( ! $is_new && ! $is_edit ) : ?>
        <a href="<?php echo esc_url( $router->url( 'events', [ 'id' => $event_id, 'action' => 'edit' ] ) ); ?>" 
           class="sp-button sp-button--primary">
            Edit Event
        </a>
    <?php endif; ?>
</header>

<?php if ( $is_edit ) : ?>
<form method="post" action="" data-sp-form>
    <?php wp_nonce_field( 'sp_save_event', 'sp_event_nonce' ); ?>
<?php endif; ?>

    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Event Details</legend>
        
        <div class="sp-form-group">
            <label for="post_title" class="sp-form-label sp-form-label--required">Event Title</label>
            <?php if ( $is_edit ) : ?>
                <input type="text" id="post_title" name="post_title" class="sp-input"
                       value="<?php echo esc_attr( $event->post_title ?? '' ); ?>" required>
            <?php else : ?>
                <div class="sp-form-value"><?php echo esc_html( $event->post_title ); ?></div>
            <?php endif; ?>
        </div>
        
        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="event_date" class="sp-form-label">Date</label>
                <?php if ( $is_edit ) : ?>
                    <input type="date" id="event_date" name="event_date" class="sp-input"
                           value="<?php echo esc_attr( $event_date ); ?>">
                <?php else : ?>
                    <div class="sp-form-value">
                        <?php echo $event_date ? date( 'l, F j, Y', strtotime( $event_date ) ) : '—'; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="sp-form-group">
                <label for="event_time" class="sp-form-label">Start Time</label>
                <?php if ( $is_edit ) : ?>
                    <input type="time" id="event_time" name="event_time" class="sp-input"
                           value="<?php echo esc_attr( $event_time ); ?>">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $event_time ) ?: '—'; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="sp-form-group">
                <label for="event_end_time" class="sp-form-label">End Time</label>
                <?php if ( $is_edit ) : ?>
                    <input type="time" id="event_end_time" name="event_end_time" class="sp-input"
                           value="<?php echo esc_attr( $event_end_time ); ?>">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $event_end_time ) ?: '—'; ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="sp-form-group">
            <label for="event_location" class="sp-form-label">Location</label>
            <?php if ( $is_edit ) : ?>
                <input type="text" id="event_location" name="event_location" class="sp-input"
                       value="<?php echo esc_attr( $event_location ); ?>">
            <?php else : ?>
                <div class="sp-form-value"><?php echo esc_html( $event_location ) ?: '—'; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="sp-form-group">
            <label for="event_address" class="sp-form-label">Address</label>
            <?php if ( $is_edit ) : ?>
                <textarea id="event_address" name="event_address" class="sp-textarea" rows="3"><?php echo esc_textarea( $event_address ); ?></textarea>
            <?php else : ?>
                <div class="sp-form-value"><?php echo nl2br( esc_html( $event_address ) ) ?: '—'; ?></div>
            <?php endif; ?>
        </div>
    </fieldset>
    
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Description</legend>
        
        <div class="sp-form-group">
            <?php if ( $is_edit ) : ?>
                <textarea id="post_content" name="post_content" class="sp-textarea" rows="6"><?php echo esc_textarea( $event->post_content ?? '' ); ?></textarea>
            <?php else : ?>
                <div class="sp-form-value"><?php echo wp_kses_post( $event->post_content ) ?: '<em>No description</em>'; ?></div>
            <?php endif; ?>
        </div>
    </fieldset>
    
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Registration</legend>
        
        <div class="sp-form-row">
            <div class="sp-form-group">
                <?php if ( $is_edit ) : ?>
                    <label class="sp-checkbox-label">
                        <input type="checkbox" name="registration_required" value="1" class="sp-checkbox"
                               <?php checked( $registration_required, 1 ); ?>>
                        Enable Registration
                    </label>
                <?php else : ?>
                    <label class="sp-form-label">Registration</label>
                    <div class="sp-form-value"><?php echo $registration_required ? 'Required' : 'Not required'; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="sp-form-group">
                <label for="event_capacity" class="sp-form-label">Capacity (0 = unlimited)</label>
                <?php if ( $is_edit ) : ?>
                    <input type="number" id="event_capacity" name="event_capacity" class="sp-input"
                           value="<?php echo esc_attr( $event_capacity ); ?>" min="0" style="width: 100px;">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo $event_capacity ? $event_capacity : 'Unlimited'; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>
    
    <?php if ( ! $is_new && ! empty( $registrations ) ) : ?>
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Registrations (<?php echo $registered_count; ?>)</legend>
        
        <table class="sp-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                global $wpdb;
                foreach ( $registrations as $reg ) : 
                    $member = $wpdb->get_row( $wpdb->prepare(
                        "SELECT m.*, c.primary_email FROM {$wpdb->prefix}sp_members m 
                         LEFT JOIN {$wpdb->prefix}sp_member_contact c ON m.id = c.member_id 
                         WHERE m.id = %d", 
                        $reg->member_id 
                    ) );
                    if ( ! $member ) continue;
                ?>
                    <tr>
                        <td><?php echo esc_html( $member->first_name . ' ' . $member->last_name ); ?></td>
                        <td><?php echo esc_html( $member->primary_email ); ?></td>
                        <td><?php echo date( 'm/d/Y', strtotime( $reg->registered_at ) ); ?></td>
                        <td>
                            <span class="sp-status sp-status--<?php echo $reg->status === 'registered' ? 'active' : 'pending'; ?>">
                                <?php echo ucfirst( $reg->status ); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </fieldset>
    <?php endif; ?>

<?php if ( $is_edit ) : ?>
    <div class="sp-form-actions" style="display: flex; gap: var(--sp-spacing-md); margin-top: var(--sp-spacing-xl);">
        <button type="submit" class="sp-button sp-button--primary sp-button--large">
            <?php echo $is_new ? 'Create Event' : 'Save Changes'; ?>
        </button>
        <a href="<?php echo esc_url( $is_new ? $router->url( 'events' ) : $router->url( 'events', [ 'id' => $event_id ] ) ); ?>"
           class="sp-button sp-button--secondary sp-button--large">
            Cancel
        </a>
    </div>
</form>
<?php endif; ?>

<style>
.sp-form-value { padding: var(--sp-spacing-sm) 0; font-size: var(--sp-font-size-base); color: var(--sp-gray-800); }
</style>
