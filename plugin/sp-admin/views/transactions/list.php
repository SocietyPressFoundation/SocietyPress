<?php
/**
 * SocietyPress Admin - Transactions List View
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

// Get filter parameters
$search  = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$status  = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
$year    = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : date( 'Y' );
$paged   = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page = 25;

$trans_table = $wpdb->prefix . 'sp_transactions';
$members_table = $wpdb->prefix . 'sp_members';

// Check if table exists
if ( $wpdb->get_var( "SHOW TABLES LIKE '$trans_table'" ) !== $trans_table ) {
    echo '<div class="sp-admin-notice sp-admin-notice--warning">Transactions table not found. Please reactivate the plugin.</div>';
    return;
}

// Build query
$where = "WHERE YEAR(t.transaction_date) = %d";
$params = [ $year ];

if ( $status ) {
    $where .= " AND t.status = %s";
    $params[] = $status;
}

if ( $search ) {
    $search_like = '%' . $wpdb->esc_like( $search ) . '%';
    $where .= " AND (m.first_name LIKE %s OR m.last_name LIKE %s OR t.reference_number LIKE %s)";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
}

// Get totals
$totals = $wpdb->get_row( $wpdb->prepare(
    "SELECT 
        SUM(invoice_amount) as total_invoiced,
        SUM(amount_paid) as total_paid,
        SUM(invoice_amount - amount_paid) as total_due
     FROM {$trans_table} t
     LEFT JOIN {$members_table} m ON t.member_id = m.id
     {$where}",
    $params
) );

// Count for pagination
$count_query = $wpdb->prepare(
    "SELECT COUNT(*) FROM {$trans_table} t LEFT JOIN {$members_table} m ON t.member_id = m.id {$where}",
    $params
);
$total_items = (int) $wpdb->get_var( $count_query );
$total_pages = ceil( $total_items / $per_page );

// Get transactions
$offset = ( $paged - 1 ) * $per_page;
$query = $wpdb->prepare(
    "SELECT t.*, m.first_name, m.last_name 
     FROM {$trans_table} t 
     LEFT JOIN {$members_table} m ON t.member_id = m.id 
     {$where}
     ORDER BY t.transaction_date DESC, t.id DESC
     LIMIT {$per_page} OFFSET {$offset}",
    $params
);
$transactions = $wpdb->get_results( $query );

// Get available years for filter
$years = $wpdb->get_col( "SELECT DISTINCT YEAR(transaction_date) FROM {$trans_table} ORDER BY 1 DESC" );
if ( empty( $years ) ) {
    $years = [ date( 'Y' ) ];
}
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Transactions</h1>
    <a href="<?php echo esc_url( $router->url( 'transactions', [ 'action' => 'new' ] ) ); ?>" class="sp-button sp-button--primary">
        + Record Payment
    </a>
</header>

<!-- Summary Cards -->
<div class="sp-stats-grid" style="margin-bottom: var(--sp-spacing-xl);">
    <div class="sp-stat-card">
        <span class="sp-stat-value">$<?php echo number_format( $totals->total_invoiced ?? 0, 2 ); ?></span>
        <span class="sp-stat-label">Invoiced</span>
    </div>
    <div class="sp-stat-card">
        <span class="sp-stat-value" style="color: var(--sp-success);">$<?php echo number_format( $totals->total_paid ?? 0, 2 ); ?></span>
        <span class="sp-stat-label">Paid</span>
    </div>
    <div class="sp-stat-card">
        <span class="sp-stat-value" style="color: <?php echo ( $totals->total_due ?? 0 ) > 0 ? 'var(--sp-danger)' : 'var(--sp-gray-500)'; ?>;">
            $<?php echo number_format( $totals->total_due ?? 0, 2 ); ?>
        </span>
        <span class="sp-stat-label">Outstanding</span>
    </div>
</div>

<div class="sp-table-wrapper">
    <div class="sp-table-toolbar">
        <form method="get" action="" class="sp-search-box">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" 
                   placeholder="Search by name or reference..." class="sp-input sp-search-input">
            <button type="submit" class="sp-button sp-button--secondary">Search</button>
        </form>
        
        <div class="sp-filters">
            <form method="get" style="display: flex; gap: var(--sp-spacing-sm);">
                <?php if ( $search ) : ?>
                    <input type="hidden" name="s" value="<?php echo esc_attr( $search ); ?>">
                <?php endif; ?>
                
                <select name="year" class="sp-select" onchange="this.form.submit()">
                    <?php foreach ( $years as $y ) : ?>
                        <option value="<?php echo $y; ?>" <?php selected( $year, $y ); ?>><?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status" class="sp-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="paid" <?php selected( $status, 'paid' ); ?>>Paid</option>
                    <option value="pending" <?php selected( $status, 'pending' ); ?>>Pending</option>
                    <option value="partial" <?php selected( $status, 'partial' ); ?>>Partial</option>
                    <option value="voided" <?php selected( $status, 'voided' ); ?>>Voided</option>
                </select>
            </form>
        </div>
    </div>
    
    <table class="sp-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Member</th>
                <th>Type</th>
                <th style="text-align: right;">Amount</th>
                <th style="text-align: right;">Paid</th>
                <th style="text-align: right;">Due</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $transactions ) ) : ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: var(--sp-spacing-xl); color: var(--sp-gray-500);">
                        No transactions found.
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $transactions as $trans ) : 
                    $due = $trans->invoice_amount - $trans->amount_paid;
                ?>
                    <tr>
                        <td><?php echo date( 'm/d/y', strtotime( $trans->transaction_date ) ); ?></td>
                        <td>
                            <a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $trans->member_id ] ) ); ?>">
                                <?php echo esc_html( $trans->last_name . ', ' . $trans->first_name ); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html( ucfirst( $trans->transaction_type ) ); ?></td>
                        <td style="text-align: right;">$<?php echo number_format( $trans->invoice_amount, 2 ); ?></td>
                        <td style="text-align: right;">$<?php echo number_format( $trans->amount_paid, 2 ); ?></td>
                        <td style="text-align: right; <?php echo $due > 0 ? 'color: var(--sp-danger); font-weight: 500;' : ''; ?>">
                            $<?php echo number_format( $due, 2 ); ?>
                        </td>
                        <td>
                            <span class="sp-status sp-status--<?php echo $trans->status === 'paid' ? 'active' : ( $trans->status === 'pending' ? 'expired' : 'pending' ); ?>">
                                <?php echo ucfirst( $trans->status ); ?>
                            </span>
                        </td>
                        <td class="sp-table-actions">
                            <a href="<?php echo esc_url( $router->url( 'transactions', [ 'id' => $trans->id ] ) ); ?>" class="sp-table-action">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ( $total_pages > 1 ) : ?>
        <div class="sp-pagination">
            <div class="sp-pagination-info">
                Showing <?php echo ( ( $paged - 1 ) * $per_page ) + 1; ?>–<?php echo min( $paged * $per_page, $total_items ); ?>
                of <?php echo number_format( $total_items ); ?>
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
</div>
