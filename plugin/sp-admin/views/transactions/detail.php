<?php
/**
 * SocietyPress Admin - Transaction Detail/Record Payment View
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$trans_id = $router->get_param( 'id' );
$action   = $router->get_param( 'action', '' );
$is_new   = ( $action === 'new' );

$trans_table = $wpdb->prefix . 'sp_transactions';
$members_table = $wpdb->prefix . 'sp_members';
$tiers_table = $wpdb->prefix . 'sp_membership_tiers';

$transaction = null;
$member = null;

if ( $trans_id && ! $is_new ) {
    $transaction = $wpdb->get_row( $wpdb->prepare(
        "SELECT t.*, m.first_name, m.last_name FROM {$trans_table} t 
         LEFT JOIN {$members_table} m ON t.member_id = m.id WHERE t.id = %d",
        $trans_id
    ) );
    if ( ! $transaction ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Transaction not found.</div>';
        return;
    }
}

// Get all tiers for membership level dropdown
$tiers = $wpdb->get_results( "SELECT * FROM {$tiers_table} WHERE is_active = 1 ORDER BY sort_order" );

// Handle form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['sp_trans_nonce'] ) ) {
    if ( ! wp_verify_nonce( $_POST['sp_trans_nonce'], 'sp_save_transaction' ) ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Security check failed.</div>';
    } else {
        $member_id = absint( $_POST['member_id'] ?? 0 );
        $invoice_amount = floatval( $_POST['invoice_amount'] ?? 0 );
        $amount_paid = floatval( $_POST['amount_paid'] ?? 0 );
        
        $data = [
            'member_id'        => $member_id,
            'transaction_type' => sanitize_key( $_POST['transaction_type'] ?? 'dues' ),
            'description'      => sanitize_text_field( $_POST['description'] ?? '' ),
            'invoice_amount'   => $invoice_amount,
            'amount_paid'      => $amount_paid,
            'payment_method'   => sanitize_text_field( $_POST['payment_method'] ?? '' ),
            'reference_number' => sanitize_text_field( $_POST['reference_number'] ?? '' ),
            'notes'            => sanitize_textarea_field( $_POST['notes'] ?? '' ),
            'transaction_date' => sanitize_text_field( $_POST['transaction_date'] ?? date( 'Y-m-d' ) ),
        ];
        
        // Determine status
        if ( $amount_paid >= $invoice_amount ) {
            $data['status'] = 'paid';
        } elseif ( $amount_paid > 0 ) {
            $data['status'] = 'partial';
        } else {
            $data['status'] = 'pending';
        }
        
        if ( $is_new ) {
            $data['created_at'] = current_time( 'mysql' );
            $data['created_by'] = get_current_user_id();
            $wpdb->insert( $trans_table, $data );
            $trans_id = $wpdb->insert_id;
            
            // Update member expiration if checkbox is set and it's a dues payment
            if ( isset( $_POST['update_expiration'] ) && $data['transaction_type'] === 'dues' && $data['status'] === 'paid' ) {
                $new_exp = sanitize_text_field( $_POST['new_expiration'] ?? '' );
                if ( $new_exp ) {
                    $wpdb->update( $members_table, [ 'expiration_date' => $new_exp, 'status' => 'active' ], [ 'id' => $member_id ] );
                }
            }
        } else {
            $wpdb->update( $trans_table, $data, [ 'id' => $trans_id ] );
        }
        
        wp_redirect( $router->url( 'transactions', [ 'id' => $trans_id ] ) . '?success=saved' );
        exit;
    }
}

// Get members for dropdown (active members first)
$members_list = $wpdb->get_results(
    "SELECT id, first_name, last_name, status FROM {$members_table} ORDER BY status = 'active' DESC, last_name, first_name"
);
?>

<a href="<?php echo esc_url( $router->url( 'transactions' ) ); ?>" class="sp-back-link">
    ← Back to Transactions
</a>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">
        <?php echo $is_new ? 'Record Payment' : 'Transaction #' . $trans_id; ?>
    </h1>
</header>

<form method="post" action="" data-sp-form>
    <?php wp_nonce_field( 'sp_save_transaction', 'sp_trans_nonce' ); ?>
    
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Payment Details</legend>
        
        <div class="sp-form-group">
            <label for="member_id" class="sp-form-label sp-form-label--required">Member</label>
            <?php if ( $is_new ) : ?>
                <select id="member_id" name="member_id" class="sp-select" required>
                    <option value="">Select a member...</option>
                    <?php foreach ( $members_list as $m ) : ?>
                        <option value="<?php echo $m->id; ?>">
                            <?php echo esc_html( $m->last_name . ', ' . $m->first_name ); ?>
                            <?php if ( $m->status !== 'active' ) echo ' (' . ucfirst( $m->status ) . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <div class="sp-form-value">
                    <a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $transaction->member_id ] ) ); ?>">
                        <?php echo esc_html( $transaction->last_name . ', ' . $transaction->first_name ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="transaction_type" class="sp-form-label">Type</label>
                <select id="transaction_type" name="transaction_type" class="sp-select">
                    <option value="dues" <?php selected( $transaction->transaction_type ?? '', 'dues' ); ?>>Membership Dues</option>
                    <option value="donation" <?php selected( $transaction->transaction_type ?? '', 'donation' ); ?>>Donation</option>
                    <option value="event" <?php selected( $transaction->transaction_type ?? '', 'event' ); ?>>Event Registration</option>
                    <option value="other" <?php selected( $transaction->transaction_type ?? '', 'other' ); ?>>Other</option>
                </select>
            </div>
            
            <div class="sp-form-group">
                <label for="transaction_date" class="sp-form-label">Date</label>
                <input type="date" id="transaction_date" name="transaction_date" class="sp-input"
                       value="<?php echo esc_attr( $transaction->transaction_date ?? date( 'Y-m-d' ) ); ?>">
            </div>
        </div>
        
        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="invoice_amount" class="sp-form-label">Amount Due</label>
                <input type="number" id="invoice_amount" name="invoice_amount" class="sp-input"
                       value="<?php echo esc_attr( $transaction->invoice_amount ?? '45.00' ); ?>" step="0.01" min="0">
            </div>
            
            <div class="sp-form-group">
                <label for="amount_paid" class="sp-form-label">Amount Paid</label>
                <input type="number" id="amount_paid" name="amount_paid" class="sp-input"
                       value="<?php echo esc_attr( $transaction->amount_paid ?? '' ); ?>" step="0.01" min="0">
            </div>
        </div>
        
        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="payment_method" class="sp-form-label">Payment Method</label>
                <select id="payment_method" name="payment_method" class="sp-select">
                    <option value="">Select...</option>
                    <option value="check" <?php selected( $transaction->payment_method ?? '', 'check' ); ?>>Check</option>
                    <option value="cash" <?php selected( $transaction->payment_method ?? '', 'cash' ); ?>>Cash</option>
                    <option value="credit_card" <?php selected( $transaction->payment_method ?? '', 'credit_card' ); ?>>Credit Card</option>
                    <option value="paypal" <?php selected( $transaction->payment_method ?? '', 'paypal' ); ?>>PayPal</option>
                    <option value="other" <?php selected( $transaction->payment_method ?? '', 'other' ); ?>>Other</option>
                </select>
            </div>
            
            <div class="sp-form-group">
                <label for="reference_number" class="sp-form-label">Check/Reference #</label>
                <input type="text" id="reference_number" name="reference_number" class="sp-input"
                       value="<?php echo esc_attr( $transaction->reference_number ?? '' ); ?>">
            </div>
        </div>
        
        <div class="sp-form-group">
            <label for="notes" class="sp-form-label">Notes</label>
            <textarea id="notes" name="notes" class="sp-textarea" rows="3"><?php echo esc_textarea( $transaction->notes ?? '' ); ?></textarea>
        </div>
    </fieldset>
    
    <?php if ( $is_new ) : ?>
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Membership Update</legend>
        
        <label class="sp-checkbox-label">
            <input type="checkbox" name="update_expiration" value="1" class="sp-checkbox" checked>
            Update membership expiration date
        </label>
        
        <div class="sp-form-group" style="margin-top: var(--sp-spacing-md);">
            <label for="new_expiration" class="sp-form-label">New Expiration Date</label>
            <input type="date" id="new_expiration" name="new_expiration" class="sp-input"
                   value="<?php echo date( 'Y-12-31' ); ?>">
        </div>
    </fieldset>
    <?php endif; ?>
    
    <?php if ( ! $is_new && $transaction ) : ?>
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Transaction Info</legend>
        <p><strong>Status:</strong> 
            <span class="sp-status sp-status--<?php echo $transaction->status === 'paid' ? 'active' : 'expired'; ?>">
                <?php echo ucfirst( $transaction->status ); ?>
            </span>
        </p>
        <p><strong>Recorded:</strong> <?php echo date( 'F j, Y g:i A', strtotime( $transaction->created_at ) ); ?></p>
        <?php if ( $transaction->created_by ) : 
            $creator = get_userdata( $transaction->created_by );
        ?>
            <p><strong>Recorded by:</strong> <?php echo $creator ? esc_html( $creator->display_name ) : 'Unknown'; ?></p>
        <?php endif; ?>
    </fieldset>
    <?php endif; ?>
    
    <div class="sp-form-actions" style="display: flex; gap: var(--sp-spacing-md); margin-top: var(--sp-spacing-xl);">
        <button type="submit" class="sp-button sp-button--primary sp-button--large">
            <?php echo $is_new ? 'Record Payment' : 'Update Transaction'; ?>
        </button>
        <a href="<?php echo esc_url( $router->url( 'transactions' ) ); ?>" class="sp-button sp-button--secondary sp-button--large">
            Cancel
        </a>
    </div>
</form>

<style>
.sp-form-value { padding: var(--sp-spacing-sm) 0; font-size: var(--sp-font-size-base); }
</style>
