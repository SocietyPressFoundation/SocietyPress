<?php
/**
 * SocietyPress Admin - Leadership List View
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$positions_table = $wpdb->prefix . 'sp_positions';
$holders_table = $wpdb->prefix . 'sp_position_holders';
$members_table = $wpdb->prefix . 'sp_members';

// Get positions grouped by category (is_board_member, is_officer)
$positions = $wpdb->get_results(
    "SELECT p.*, ph.member_id, m.first_name, m.last_name
     FROM {$positions_table} p
     LEFT JOIN {$holders_table} ph ON p.id = ph.position_id AND (ph.term_end IS NULL OR ph.term_end > CURDATE())
     LEFT JOIN {$members_table} m ON ph.member_id = m.id
     ORDER BY p.is_board_member DESC, p.is_officer DESC, p.sort_order, p.title"
);

// Group by category
$board_officers = [];
$committee_chairs = [];
$other = [];

foreach ( $positions as $pos ) {
    if ( $pos->is_board_member || $pos->is_officer ) {
        $board_officers[] = $pos;
    } elseif ( strpos( strtolower( $pos->title ), 'chair' ) !== false || strpos( strtolower( $pos->title ), 'committee' ) !== false ) {
        $committee_chairs[] = $pos;
    } else {
        $other[] = $pos;
    }
}
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Leadership</h1>
    <a href="<?php echo esc_url( $router->url( 'leadership', [ 'action' => 'new' ] ) ); ?>" class="sp-button sp-button--primary">
        + Add Position
    </a>
</header>

<?php if ( ! empty( $board_officers ) ) : ?>
<div class="sp-card" style="margin-bottom: var(--sp-spacing-lg);">
    <div class="sp-card-header">
        <h2 class="sp-card-title">Board Officers</h2>
    </div>
    <table class="sp-table">
        <thead>
            <tr><th>Position</th><th>Current Holder</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ( $board_officers as $pos ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $pos->title ); ?></strong></td>
                    <td>
                        <?php if ( $pos->member_id ) : ?>
                            <a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $pos->member_id ] ) ); ?>">
                                <?php echo esc_html( $pos->first_name . ' ' . $pos->last_name ); ?>
                            </a>
                        <?php else : ?>
                            <span style="color: var(--sp-warning);">(Vacant)</span>
                        <?php endif; ?>
                    </td>
                    <td class="sp-table-actions">
                        <a href="<?php echo esc_url( $router->url( 'leadership', [ 'id' => $pos->id, 'action' => 'edit' ] ) ); ?>" class="sp-table-action">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if ( ! empty( $committee_chairs ) ) : ?>
<div class="sp-card" style="margin-bottom: var(--sp-spacing-lg);">
    <div class="sp-card-header">
        <h2 class="sp-card-title">Committee Chairs</h2>
    </div>
    <table class="sp-table">
        <thead>
            <tr><th>Position</th><th>Current Holder</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ( $committee_chairs as $pos ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $pos->title ); ?></strong></td>
                    <td>
                        <?php if ( $pos->member_id ) : ?>
                            <a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $pos->member_id ] ) ); ?>">
                                <?php echo esc_html( $pos->first_name . ' ' . $pos->last_name ); ?>
                            </a>
                        <?php else : ?>
                            <span style="color: var(--sp-warning);">(Vacant)</span>
                        <?php endif; ?>
                    </td>
                    <td class="sp-table-actions">
                        <a href="<?php echo esc_url( $router->url( 'leadership', [ 'id' => $pos->id, 'action' => 'edit' ] ) ); ?>" class="sp-table-action">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if ( ! empty( $other ) ) : ?>
<div class="sp-card">
    <div class="sp-card-header">
        <h2 class="sp-card-title">Other Positions</h2>
    </div>
    <table class="sp-table">
        <thead>
            <tr><th>Position</th><th>Current Holder</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ( $other as $pos ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $pos->title ); ?></strong></td>
                    <td>
                        <?php if ( $pos->member_id ) : ?>
                            <?php echo esc_html( $pos->first_name . ' ' . $pos->last_name ); ?>
                        <?php else : ?>
                            <span style="color: var(--sp-warning);">(Vacant)</span>
                        <?php endif; ?>
                    </td>
                    <td class="sp-table-actions">
                        <a href="<?php echo esc_url( $router->url( 'leadership', [ 'id' => $pos->id, 'action' => 'edit' ] ) ); ?>" class="sp-table-action">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if ( empty( $positions ) ) : ?>
<div class="sp-card">
    <p style="text-align: center; padding: var(--sp-spacing-xl); color: var(--sp-gray-500);">
        No positions defined yet. <a href="<?php echo esc_url( $router->url( 'leadership', [ 'action' => 'new' ] ) ); ?>">Add your first position</a>
    </p>
</div>
<?php endif; ?>
