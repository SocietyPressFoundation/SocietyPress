<?php
/**
 * SocietyPress Admin - Leadership Position Detail View
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$position_id = $router->get_param( 'id' );
$action = $router->get_param( 'action', '' );
$is_new = ( $action === 'new' );

$positions_table = $wpdb->prefix . 'sp_positions';
$holders_table = $wpdb->prefix . 'sp_position_holders';
$members_table = $wpdb->prefix . 'sp_members';

$position = null;
$current_holder = null;

if ( $position_id && ! $is_new ) {
    $position = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$positions_table} WHERE id = %d", $position_id ) );
    if ( ! $position ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Position not found.</div>';
        return;
    }
    
    // Get current holder
    $current_holder = $wpdb->get_row( $wpdb->prepare(
        "SELECT ph.*, m.first_name, m.last_name FROM {$holders_table} ph 
         JOIN {$members_table} m ON ph.member_id = m.id 
         WHERE ph.position_id = %d AND (ph.term_end IS NULL OR ph.term_end > CURDATE())
         ORDER BY ph.term_start DESC LIMIT 1",
        $position_id
    ) );
}

// Handle form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['sp_position_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['sp_position_nonce'], 'sp_save_position' ) ) {
        $data = [
            'title'           => sanitize_text_field( $_POST['title'] ?? '' ),
            'description'     => sanitize_textarea_field( $_POST['description'] ?? '' ),
            'is_board_member' => isset( $_POST['is_board_member'] ) ? 1 : 0,
            'is_officer'      => isset( $_POST['is_officer'] ) ? 1 : 0,
            'sort_order'      => absint( $_POST['sort_order'] ?? 0 ),
        ];
        
        if ( $is_new ) {
            $data['created_at'] = current_time( 'mysql' );
            $wpdb->insert( $positions_table, $data );
            $position_id = $wpdb->insert_id;
        } else {
            $wpdb->update( $positions_table, $data, [ 'id' => $position_id ] );
        }
        
        // Handle holder assignment
        $new_holder_id = absint( $_POST['holder_id'] ?? 0 );
        if ( $new_holder_id && ( ! $current_holder || $current_holder->member_id != $new_holder_id ) ) {
            // End current term if exists
            if ( $current_holder ) {
                $wpdb->update( $holders_table, [ 'term_end' => date( 'Y-m-d' ) ], [ 'id' => $current_holder->id ] );
            }
            // Add new holder
            $wpdb->insert( $holders_table, [
                'position_id' => $position_id,
                'member_id'   => $new_holder_id,
                'term_start'  => date( 'Y-m-d' ),
                'created_at'  => current_time( 'mysql' ),
            ] );
        } elseif ( ! $new_holder_id && $current_holder ) {
            // Clear holder
            $wpdb->update( $holders_table, [ 'term_end' => date( 'Y-m-d' ) ], [ 'id' => $current_holder->id ] );
        }
        
        wp_redirect( $router->url( 'leadership' ) . '?success=saved' );
        exit;
    }
}

// Get all members for dropdown
$all_members = $wpdb->get_results(
    "SELECT id, first_name, last_name FROM {$members_table} WHERE status = 'active' ORDER BY last_name, first_name"
);

// Default for new
if ( $is_new ) {
    $position = (object)[ 'title' => '', 'description' => '', 'is_board_member' => 0, 'is_officer' => 0, 'sort_order' => 0 ];
}
?>

<a href="<?php echo esc_url( $router->url( 'leadership' ) ); ?>" class="sp-back-link">← Back to Leadership</a>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title"><?php echo $is_new ? 'Add Position' : 'Edit Position'; ?></h1>
</header>

<form method="post" data-sp-form>
    <?php wp_nonce_field( 'sp_save_position', 'sp_position_nonce' ); ?>
    
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Position Details</legend>
        
        <div class="sp-form-group">
            <label for="title" class="sp-form-label sp-form-label--required">Position Title</label>
            <input type="text" id="title" name="title" class="sp-input" value="<?php echo esc_attr( $position->title ); ?>" required>
        </div>
        
        <div class="sp-form-group">
            <label for="description" class="sp-form-label">Description</label>
            <textarea id="description" name="description" class="sp-textarea" rows="3"><?php echo esc_textarea( $position->description ?? '' ); ?></textarea>
        </div>
        
        <div class="sp-form-row">
            <div class="sp-form-group">
                <label class="sp-checkbox-label">
                    <input type="checkbox" name="is_board_member" value="1" class="sp-checkbox" <?php checked( $position->is_board_member, 1 ); ?>>
                    Board Member
                </label>
            </div>
            <div class="sp-form-group">
                <label class="sp-checkbox-label">
                    <input type="checkbox" name="is_officer" value="1" class="sp-checkbox" <?php checked( $position->is_officer, 1 ); ?>>
                    Officer
                </label>
            </div>
            <div class="sp-form-group">
                <label for="sort_order" class="sp-form-label">Display Order</label>
                <input type="number" id="sort_order" name="sort_order" class="sp-input" value="<?php echo esc_attr( $position->sort_order ); ?>" min="0" style="width: 80px;">
            </div>
        </div>
    </fieldset>
    
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Current Holder</legend>
        
        <div class="sp-form-group">
            <label for="holder_id" class="sp-form-label">Assigned To</label>
            <select id="holder_id" name="holder_id" class="sp-select">
                <option value="">(Vacant)</option>
                <?php foreach ( $all_members as $m ) : ?>
                    <option value="<?php echo $m->id; ?>" <?php selected( $current_holder->member_id ?? 0, $m->id ); ?>>
                        <?php echo esc_html( $m->last_name . ', ' . $m->first_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </fieldset>
    
    <div class="sp-form-actions" style="display: flex; gap: var(--sp-spacing-md); margin-top: var(--sp-spacing-xl);">
        <button type="submit" class="sp-button sp-button--primary sp-button--large"><?php echo $is_new ? 'Create Position' : 'Save Changes'; ?></button>
        <a href="<?php echo esc_url( $router->url( 'leadership' ) ); ?>" class="sp-button sp-button--secondary sp-button--large">Cancel</a>
        <?php if ( ! $is_new ) : ?>
            <a href="<?php echo esc_url( $router->url( 'leadership' ) . '?delete=' . $position_id ); ?>" 
               class="sp-button sp-button--danger sp-button--large" style="margin-left: auto;"
               data-sp-confirm="Delete this position? This cannot be undone.">Delete</a>
        <?php endif; ?>
    </div>
</form>
