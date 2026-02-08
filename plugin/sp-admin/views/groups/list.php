<?php
/**
 * SocietyPress Admin - Groups List View
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$groups_table = $wpdb->prefix . 'sp_groups';
$members_table = $wpdb->prefix . 'sp_group_members';

// Get all groups with member counts
$groups = $wpdb->get_results(
    "SELECT g.*, COUNT(gm.id) as member_count 
     FROM {$groups_table} g 
     LEFT JOIN {$members_table} gm ON g.id = gm.group_id 
     GROUP BY g.id 
     ORDER BY g.name"
);
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Groups</h1>
    <a href="<?php echo esc_url( $router->url( 'groups', [ 'action' => 'new' ] ) ); ?>" class="sp-button sp-button--primary">
        + Add New Group
    </a>
</header>

<div class="sp-table-wrapper">
    <table class="sp-table">
        <thead>
            <tr>
                <th>Group Name</th>
                <th>Members</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $groups ) ) : ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: var(--sp-spacing-xl); color: var(--sp-gray-500);">
                        No groups yet. <a href="<?php echo esc_url( $router->url( 'groups', [ 'action' => 'new' ] ) ); ?>">Create your first group</a>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $groups as $group ) : ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url( $router->url( 'groups', [ 'id' => $group->id ] ) ); ?>">
                                <strong><?php echo esc_html( $group->name ); ?></strong>
                            </a>
                            <?php if ( $group->description ) : ?>
                                <br><small style="color: var(--sp-gray-500);"><?php echo esc_html( wp_trim_words( $group->description, 10 ) ); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format( $group->member_count ); ?></td>
                        <td><?php echo $group->enable_email ? 'Yes' : 'No'; ?></td>
                        <td class="sp-table-actions">
                            <a href="<?php echo esc_url( $router->url( 'groups', [ 'id' => $group->id ] ) ); ?>" class="sp-button sp-button--secondary">
                                Manage
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
