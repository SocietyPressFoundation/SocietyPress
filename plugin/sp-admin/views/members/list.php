<?php
/**
 * SocietyPress Admin - Members List View
 *
 * Displays the member list with search, filtering, sorting, and pagination.
 * This is the main view volunteers will use to find and manage members.
 *
 * WHY THIS DESIGN:
 * - Search is prominent at the top - finding a specific member is the #1 task
 * - Filters are simple dropdowns, not complex facets
 * - Table shows essential info at a glance
 * - Large click targets for elderly users
 *
 * @package SocietyPress
 * @since 0.59
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

// Get filter parameters
$search      = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$status      = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
$tier        = isset( $_GET['tier'] ) ? absint( $_GET['tier'] ) : 0;
$orderby     = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'last_name';
$order       = isset( $_GET['order'] ) && strtoupper( $_GET['order'] ) === 'DESC' ? 'DESC' : 'ASC';
$paged       = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page    = 25;

// Build the query
$members_table = $wpdb->prefix . 'sp_members';
$contact_table = $wpdb->prefix . 'sp_member_contact';
$tiers_table   = $wpdb->prefix . 'sp_membership_tiers';

// Base query
$select = "SELECT m.*, c.primary_email, c.cell_phone, c.home_phone, t.name as tier_name";
$from   = " FROM {$members_table} m";
$join   = " LEFT JOIN {$contact_table} c ON m.id = c.member_id";
$join  .= " LEFT JOIN {$tiers_table} t ON m.membership_tier_id = t.id";
$where  = " WHERE 1=1";
$params = [];

// Search filter
if ( ! empty( $search ) ) {
    $search_like = '%' . $wpdb->esc_like( $search ) . '%';
    $where .= " AND (m.first_name LIKE %s OR m.last_name LIKE %s OR c.primary_email LIKE %s)";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
}

// Status filter
if ( ! empty( $status ) ) {
    $where .= " AND m.status = %s";
    $params[] = $status;
}

// Tier filter
if ( $tier > 0 ) {
    $where .= " AND m.membership_tier_id = %d";
    $params[] = $tier;
}

// Ordering - validate column names
$valid_orderby = [ 'last_name', 'first_name', 'join_date', 'expiration_date', 'status' ];
if ( ! in_array( $orderby, $valid_orderby ) ) {
    $orderby = 'last_name';
}
$order_clause = " ORDER BY m.{$orderby} {$order}";

// Count total for pagination
$count_query = "SELECT COUNT(DISTINCT m.id)" . $from . $join . $where;
if ( ! empty( $params ) ) {
    $count_query = $wpdb->prepare( $count_query, $params );
}
$total_items = (int) $wpdb->get_var( $count_query );
$total_pages = ceil( $total_items / $per_page );

// Add pagination
$offset = ( $paged - 1 ) * $per_page;
$limit  = " LIMIT {$per_page} OFFSET {$offset}";

// Get members
$query = $select . $from . $join . $where . $order_clause . $limit;
if ( ! empty( $params ) ) {
    $query = $wpdb->prepare( $query, $params );
}
$members = $wpdb->get_results( $query );

// Get all tiers for filter dropdown
$all_tiers = $wpdb->get_results( "SELECT id, name FROM {$tiers_table} WHERE is_active = 1 ORDER BY sort_order" );

// Helper to build sorted URL
function sp_sort_url( $column, $current_orderby, $current_order ) {
    $new_order = ( $current_orderby === $column && $current_order === 'ASC' ) ? 'DESC' : 'ASC';
    $params = $_GET;
    $params['orderby'] = $column;
    $params['order'] = $new_order;
    unset( $params['paged'] ); // Reset to page 1 when sorting
    return '?' . http_build_query( $params );
}

// Helper to get sort indicator
function sp_sort_indicator( $column, $current_orderby, $current_order ) {
    if ( $current_orderby !== $column ) {
        return '';
    }
    return $current_order === 'ASC' ? ' ↑' : ' ↓';
}
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Members</h1>
    <?php if ( $router->user_can_access_module( 'members' ) ) : ?>
        <a href="<?php echo esc_url( $router->url( 'members', [ 'action' => 'new' ] ) ); ?>" class="sp-button sp-button--primary">
            + Add New Member
        </a>
    <?php endif; ?>
</header>

<div class="sp-table-wrapper">
    <!-- Toolbar: Search and Filters -->
    <div class="sp-table-toolbar">
        <form method="get" action="" class="sp-search-box">
            <input type="hidden" name="sp_admin_route" value="members">
            <input type="text"
                   name="s"
                   value="<?php echo esc_attr( $search ); ?>"
                   placeholder="Search by name or email..."
                   class="sp-input sp-search-input">
            <button type="submit" class="sp-button sp-button--secondary">Search</button>
            <?php if ( $search ) : ?>
                <a href="<?php echo esc_url( $router->url( 'members' ) ); ?>" class="sp-button sp-button--secondary">Clear</a>
            <?php endif; ?>
        </form>

        <div class="sp-filters">
            <form method="get" action="" class="sp-filter-form" style="display: flex; gap: var(--sp-spacing-sm); align-items: center;">
                <input type="hidden" name="sp_admin_route" value="members">
                <?php if ( $search ) : ?>
                    <input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>">
                <?php endif; ?>

                <select name="status" class="sp-select sp-filter-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" <?php selected( $status, 'active' ); ?>>Active</option>
                    <option value="expired" <?php selected( $status, 'expired' ); ?>>Expired</option>
                    <option value="pending" <?php selected( $status, 'pending' ); ?>>Pending</option>
                    <option value="cancelled" <?php selected( $status, 'cancelled' ); ?>>Cancelled</option>
                    <option value="deceased" <?php selected( $status, 'deceased' ); ?>>Deceased</option>
                </select>

                <select name="tier" class="sp-select sp-filter-select" onchange="this.form.submit()">
                    <option value="">All Membership Levels</option>
                    <?php foreach ( $all_tiers as $t ) : ?>
                        <option value="<?php echo $t->id; ?>" <?php selected( $tier, $t->id ); ?>>
                            <?php echo esc_html( $t->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Members Table -->
    <table class="sp-table">
        <thead>
            <tr>
                <th>
                    <a href="<?php echo esc_url( sp_sort_url( 'last_name', $orderby, $order ) ); ?>">
                        Name<?php echo sp_sort_indicator( 'last_name', $orderby, $order ); ?>
                    </a>
                </th>
                <th>Email</th>
                <th>Phone</th>
                <th>Level</th>
                <th>
                    <a href="<?php echo esc_url( sp_sort_url( 'join_date', $orderby, $order ) ); ?>">
                        Joined<?php echo sp_sort_indicator( 'join_date', $orderby, $order ); ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo esc_url( sp_sort_url( 'expiration_date', $orderby, $order ) ); ?>">
                        Expires<?php echo sp_sort_indicator( 'expiration_date', $orderby, $order ); ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo esc_url( sp_sort_url( 'status', $orderby, $order ) ); ?>">
                        Status<?php echo sp_sort_indicator( 'status', $orderby, $order ); ?>
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $members ) ) : ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: var(--sp-spacing-xl); color: var(--sp-gray-500);">
                        <?php if ( $search || $status || $tier ) : ?>
                            No members found matching your criteria.
                            <a href="<?php echo esc_url( $router->url( 'members' ) ); ?>">Clear filters</a>
                        <?php else : ?>
                            No members yet.
                            <a href="<?php echo esc_url( $router->url( 'members', [ 'action' => 'new' ] ) ); ?>">Add your first member</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $members as $member ) : ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $member->id ] ) ); ?>">
                                    <?php echo esc_html( $member->last_name . ', ' . $member->first_name ); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <?php if ( $member->primary_email ) : ?>
                                <a href="mailto:<?php echo esc_attr( $member->primary_email ); ?>">
                                    <?php echo esc_html( $member->primary_email ); ?>
                                </a>
                            <?php else : ?>
                                <span style="color: var(--sp-gray-400);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $phone = $member->cell_phone ?: $member->home_phone;
                            echo $phone ? esc_html( $phone ) : '<span style="color: var(--sp-gray-400);">—</span>';
                            ?>
                        </td>
                        <td><?php echo esc_html( $member->tier_name ?: '—' ); ?></td>
                        <td>
                            <?php echo $member->join_date ? date( 'm/d/Y', strtotime( $member->join_date ) ) : '—'; ?>
                        </td>
                        <td>
                            <?php
                            if ( $member->expiration_date ) {
                                $exp_date = strtotime( $member->expiration_date );
                                $is_expired = $exp_date < time();
                                $is_expiring_soon = ! $is_expired && $exp_date < strtotime( '+30 days' );
                                $style = '';
                                if ( $is_expired ) {
                                    $style = 'color: var(--sp-danger); font-weight: 500;';
                                } elseif ( $is_expiring_soon ) {
                                    $style = 'color: var(--sp-warning); font-weight: 500;';
                                }
                                echo '<span style="' . $style . '">' . date( 'm/d/Y', $exp_date ) . '</span>';
                            } else {
                                echo '—';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="sp-status sp-status--<?php echo esc_attr( $member->status ); ?>">
                                <?php echo esc_html( ucfirst( $member->status ) ); ?>
                            </span>
                        </td>
                        <td class="sp-table-actions">
                            <a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $member->id ] ) ); ?>"
                               class="sp-table-action">
                                View
                            </a>
                            <a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $member->id, 'action' => 'edit' ] ) ); ?>"
                               class="sp-table-action">
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ( $total_pages > 1 ) : ?>
        <div class="sp-pagination">
            <div class="sp-pagination-info">
                Showing <?php echo ( ( $paged - 1 ) * $per_page ) + 1; ?>–<?php echo min( $paged * $per_page, $total_items ); ?>
                of <?php echo number_format( $total_items ); ?> members
            </div>
            <div class="sp-pagination-links">
                <?php
                // Build base URL for pagination
                $base_params = $_GET;
                unset( $base_params['paged'] );
                $base_url = '?' . http_build_query( $base_params );
                $base_url .= empty( $base_params ) ? 'paged=' : '&paged=';
                ?>

                <?php if ( $paged > 1 ) : ?>
                    <a href="<?php echo esc_url( $base_url . '1' ); ?>" class="sp-pagination-link">« First</a>
                    <a href="<?php echo esc_url( $base_url . ( $paged - 1 ) ); ?>" class="sp-pagination-link">‹ Prev</a>
                <?php else : ?>
                    <span class="sp-pagination-link sp-pagination-link--disabled">« First</span>
                    <span class="sp-pagination-link sp-pagination-link--disabled">‹ Prev</span>
                <?php endif; ?>

                <?php
                // Show page numbers around current page
                $start = max( 1, $paged - 2 );
                $end = min( $total_pages, $paged + 2 );

                for ( $i = $start; $i <= $end; $i++ ) :
                ?>
                    <?php if ( $i == $paged ) : ?>
                        <span class="sp-pagination-link sp-pagination-link--active"><?php echo $i; ?></span>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $base_url . $i ); ?>" class="sp-pagination-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ( $paged < $total_pages ) : ?>
                    <a href="<?php echo esc_url( $base_url . ( $paged + 1 ) ); ?>" class="sp-pagination-link">Next ›</a>
                    <a href="<?php echo esc_url( $base_url . $total_pages ); ?>" class="sp-pagination-link">Last »</a>
                <?php else : ?>
                    <span class="sp-pagination-link sp-pagination-link--disabled">Next ›</span>
                    <span class="sp-pagination-link sp-pagination-link--disabled">Last »</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
