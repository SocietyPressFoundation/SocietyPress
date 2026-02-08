<?php
/**
 * SocietyPress Admin - Group Detail View
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$group_id = $router->get_param( 'id' );
$action   = $router->get_param( 'action', '' );
$is_new   = ( $action === 'new' );
$is_edit  = ( $action === 'edit' ) || $is_new;

$groups_table = $wpdb->prefix . 'sp_groups';
$gm_table = $wpdb->prefix . 'sp_group_members';
$members_table = $wpdb->prefix . 'sp_members';
$contact_table = $wpdb->prefix . 'sp_member_contact';

$group = null;
if ( $group_id && ! $is_new ) {
    $group = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$groups_table} WHERE id = %d", $group_id ) );
    if ( ! $group ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Group not found.</div>';
        return;
    }
}

// Handle form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['sp_group_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['sp_group_nonce'], 'sp_save_group' ) ) {
        $data = [
            'name'         => sanitize_text_field( $_POST['name'] ?? '' ),
            'description'  => sanitize_textarea_field( $_POST['description'] ?? '' ),
            'enable_email' => isset( $_POST['enable_email'] ) ? 1 : 0,
        ];
        
        if ( $is_new ) {
            $data['created_at'] = current_time( 'mysql' );
            $data['created_by'] = get_current_user_id();
            $wpdb->insert( $groups_table, $data );
            $group_id = $wpdb->insert_id;
        } else {
            $wpdb->update( $groups_table, $data, [ 'id' => $group_id ] );
        }
        
        wp_redirect( $router->url( 'groups', [ 'id' => $group_id ] ) . '?success=saved' );
        exit;
    }
}

// Handle add member
if ( isset( $_POST['add_member'] ) && isset( $_POST['member_id'] ) && $group_id ) {
    $member_id = absint( $_POST['member_id'] );
    if ( $member_id ) {
        $wpdb->insert( $gm_table, [
            'group_id'  => $group_id,
            'member_id' => $member_id,
            'added_at'  => current_time( 'mysql' ),
            'added_by'  => get_current_user_id(),
        ] );
    }
    wp_redirect( $router->url( 'groups', [ 'id' => $group_id ] ) . '?success=added' );
    exit;
}

// Handle remove member
if ( isset( $_GET['remove_member'] ) && $group_id ) {
    $member_id = absint( $_GET['remove_member'] );
    $wpdb->delete( $gm_table, [ 'group_id' => $group_id, 'member_id' => $member_id ] );
    wp_redirect( $router->url( 'groups', [ 'id' => $group_id ] ) . '?success=removed' );
    exit;
}

// Get group members
$group_members = [];
if ( $group_id ) {
    $group_members = $wpdb->get_results( $wpdb->prepare(
        "SELECT m.*, c.primary_email, gm.added_at 
         FROM {$gm_table} gm 
         JOIN {$members_table} m ON gm.member_id = m.id 
         LEFT JOIN {$contact_table} c ON m.id = c.member_id 
         WHERE gm.group_id = %d 
         ORDER BY m.last_name, m.first_name",
        $group_id
    ) );
}

// Get all members for dropdown
$all_members = $wpdb->get_results(
    "SELECT id, first_name, last_name FROM {$members_table} WHERE status = 'active' ORDER BY last_name, first_name"
);

// Default for new
if ( $is_new ) {
    $group = (object)[ 'name' => '', 'description' => '', 'enable_email' => 0 ];
}
?>

<a href="<?php echo esc_url( $router->url( 'groups' ) ); ?>" class="sp-back-link">← Back to Groups</a>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title"><?php echo $is_new ? 'Add New Group' : esc_html( $group->name ); ?></h1>
    <?php if ( ! $is_new && ! $is_edit ) : ?>
        <a href="<?php echo esc_url( $router->url( 'groups', [ 'id' => $group_id, 'action' => 'edit' ] ) ); ?>" class="sp-button sp-button--secondary">Edit Group</a>
    <?php endif; ?>
</header>

<?php if ( $is_edit ) : ?>
<form method="post" data-sp-form>
    <?php wp_nonce_field( 'sp_save_group', 'sp_group_nonce' ); ?>
    
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Group Settings</legend>
        
        <div class="sp-form-group">
            <label for="name" class="sp-form-label sp-form-label--required">Group Name</label>
            <input type="text" id="name" name="name" class="sp-input" value="<?php echo esc_attr( $group->name ); ?>" required>
        </div>
        
        <div class="sp-form-group">
            <label for="description" class="sp-form-label">Description</label>
            <textarea id="description" name="description" class="sp-textarea" rows="3"><?php echo esc_textarea( $group->description ); ?></textarea>
        </div>
        
        <label class="sp-checkbox-label">
            <input type="checkbox" name="enable_email" value="1" class="sp-checkbox" <?php checked( $group->enable_email, 1 ); ?>>
            Enable blast email for this group
        </label>
    </fieldset>
    
    <div class="sp-form-actions" style="margin-top: var(--sp-spacing-xl);">
        <button type="submit" class="sp-button sp-button--primary sp-button--large"><?php echo $is_new ? 'Create Group' : 'Save Changes'; ?></button>
        <a href="<?php echo esc_url( $router->url( 'groups' ) ); ?>" class="sp-button sp-button--secondary sp-button--large">Cancel</a>
    </div>
</form>
<?php else : ?>

<!-- Group Members -->
<fieldset class="sp-fieldset">
    <legend class="sp-legend">Members (<?php echo count( $group_members ); ?>)</legend>
    
    <form method="post" style="margin-bottom: var(--sp-spacing-lg); display: flex; gap: var(--sp-spacing-sm);">
        <select name="member_id" class="sp-select" style="flex: 1;">
            <option value="">Add a member...</option>
            <?php foreach ( $all_members as $m ) : 
                // Skip if already in group
                $in_group = false;
                foreach ( $group_members as $gm ) {
                    if ( $gm->id == $m->id ) { $in_group = true; break; }
                }
                if ( $in_group ) continue;
            ?>
                <option value="<?php echo $m->id; ?>"><?php echo esc_html( $m->last_name . ', ' . $m->first_name ); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_member" value="1" class="sp-button sp-button--primary">Add</button>
    </form>
    
    <?php if ( empty( $group_members ) ) : ?>
        <p style="color: var(--sp-gray-500);">No members in this group yet.</p>
    <?php else : ?>
        <table class="sp-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Added</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ( $group_members as $m ) : ?>
                    <tr>
                        <td><a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $m->id ] ) ); ?>"><?php echo esc_html( $m->last_name . ', ' . $m->first_name ); ?></a></td>
                        <td><?php echo esc_html( $m->primary_email ); ?></td>
                        <td><?php echo date( 'm/d/Y', strtotime( $m->added_at ) ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( $router->url( 'groups', [ 'id' => $group_id ] ) . '?remove_member=' . $m->id ); ?>" 
                               class="sp-table-action" style="color: var(--sp-danger);"
                               data-sp-confirm="Remove this member from the group?">×</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</fieldset>
<?php endif; ?>
