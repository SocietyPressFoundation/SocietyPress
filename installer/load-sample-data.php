<?php
/**
 * SocietyPress — Load Sample Data (with progress bar)
 *
 * Standalone script that imports 2,500 fake members via AJAX batches with a
 * real-time progress bar. Lives alongside the installer, NOT inside the plugin.
 *
 * Flow:
 * 1. Page loads, shows member count + Load/Clear buttons
 * 2. User clicks "Load Sample Data"
 * 3. JS reads the CSV, sends batches of 50 rows via AJAX
 * 4. Each batch returns imported count, progress bar updates
 * 5. When done, redirect to dashboard
 *
 * @package    SocietyPress
 * @license    GPL-2.0-or-later
 */

define( 'WP_USE_THEMES', false );

$wp_load = __DIR__ . '/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    $wp_load = dirname( __DIR__ ) . '/wp-load.php';
}
if ( ! file_exists( $wp_load ) ) {
    die( 'Could not find WordPress.' );
}

require_once $wp_load;

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You must be logged in as an administrator.' );
}

// ============================================================================
// CSV LOCATION
// ============================================================================

$csv_candidates = [
    __DIR__ . '/data/demo-members.csv',
    __DIR__ . '/demo-members.csv',
    dirname( __DIR__ ) . '/data/demo-members.csv',
    ABSPATH . 'data/demo-members.csv',
    ABSPATH . 'demo-members.csv',
];
$csv_path = '';
foreach ( $csv_candidates as $candidate ) {
    if ( file_exists( $candidate ) ) {
        $csv_path = $candidate;
        break;
    }
}

// ============================================================================
// AJAX: PROCESS A BATCH
// ============================================================================

if ( isset( $_POST['sp_batch_action'] ) && $_POST['sp_batch_action'] === 'import_batch' ) {
    check_ajax_referer( 'sp_sample_data_nonce', 'nonce' );

    $offset    = (int) ( $_POST['offset'] ?? 0 );
    $batch_size = (int) ( $_POST['batch_size'] ?? 50 );

    if ( ! $csv_path ) {
        wp_send_json_error( [ 'message' => 'CSV file not found.' ] );
    }

    $csv_data = file_get_contents( $csv_path );
    $lines    = str_getcsv( $csv_data, "\n" );
    $header   = str_getcsv( array_shift( $lines ) );
    $total    = count( $lines );

    // Map header columns
    $col = [];
    foreach ( $header as $i => $name ) {
        $col[ trim( $name ) ] = $i;
    }

    // Slice the batch
    $batch = array_slice( $lines, $offset, $batch_size );

    if ( empty( $batch ) ) {
        wp_send_json_success( [ 'imported' => 0, 'done' => true, 'total' => $total ] );
    }

    global $wpdb;
    $prefix = $wpdb->prefix . 'sp_';

    // Get tier map
    $tiers = $wpdb->get_results( "SELECT id, name FROM {$prefix}membership_tiers" );
    $tier_map = [];
    foreach ( $tiers as $t ) {
        $tier_map[ strtolower( trim( $t->name ) ) ] = (int) $t->id;
    }

    $imported = 0;

    foreach ( $batch as $line ) {
        if ( empty( trim( $line ) ) ) continue;
        $row = str_getcsv( $line );

        $first_name  = trim( $row[ $col['First Name'] ?? 0 ] ?? '' );
        $last_name   = trim( $row[ $col['Last Name'] ?? 1 ] ?? '' );
        $pref_name   = trim( $row[ $col['Preferred Name'] ?? 2 ] ?? '' );
        $email       = trim( $row[ $col['Email'] ?? 3 ] ?? '' );
        $phone       = trim( $row[ $col['Phone'] ?? 4 ] ?? '' );
        $cell        = trim( $row[ $col['Cell Phone'] ?? 5 ] ?? '' );
        $address     = trim( $row[ $col['Address 1'] ?? 6 ] ?? '' );
        $city        = trim( $row[ $col['City'] ?? 7 ] ?? '' );
        $state       = trim( $row[ $col['State'] ?? 8 ] ?? '' );
        $zip         = trim( $row[ $col['Zip'] ?? 9 ] ?? '' );
        $country     = trim( $row[ $col['Country'] ?? 10 ] ?? '' );
        $gender      = trim( $row[ $col['Gender'] ?? 11 ] ?? '' );
        $birthday    = trim( $row[ $col['Birthday'] ?? 12 ] ?? '' );
        $join_date   = trim( $row[ $col['Join Date'] ?? 13 ] ?? '' );
        $exp_date    = trim( $row[ $col['Expiration Date'] ?? 14 ] ?? '' );
        $status      = strtolower( trim( $row[ $col['Status'] ?? 15 ] ?? 'active' ) );
        $plan_name   = trim( $row[ $col['Membership Plan'] ?? 16 ] ?? '' );
        $amount      = trim( $row[ $col['Amount Paid'] ?? 17 ] ?? '' );
        $pay_method  = trim( $row[ $col['Payment Method'] ?? 18 ] ?? '' );
        $pay_date    = trim( $row[ $col['Payment Date'] ?? 19 ] ?? '' );
        $marital     = trim( $row[ $col['Marital Status'] ?? 20 ] ?? '' );
        $surnames    = trim( $row[ $col['Surnames Researched'] ?? 21 ] ?? '' );
        $notes       = trim( $row[ $col['Notes'] ?? 22 ] ?? '' );

        if ( empty( $first_name ) && empty( $last_name ) ) continue;

        $birthday  = sp_sample_convert_date( $birthday );
        $join_date = sp_sample_convert_date( $join_date );
        $exp_date  = sp_sample_convert_date( $exp_date );
        $pay_date  = sp_sample_convert_date( $pay_date );

        $tier_id = $tier_map[ strtolower( $plan_name ) ] ?? ( $tier_map['individual'] ?? 1 );

        // Find or create the WordPress user
        // WHY: If a previous partial import created the user but not the member record,
        // we reuse the existing user instead of failing on duplicate username/email.
        $user_id = null;

        // Check by email first (most reliable match)
        if ( $email && email_exists( $email ) ) {
            $user_id = email_exists( $email );
        }

        // Check by username pattern if no email match
        if ( ! $user_id ) {
            $username = sanitize_user( strtolower( $first_name . '.' . $last_name ) );
            $username = preg_replace( '/[^a-z0-9._-]/', '', $username );

            $existing_user = get_user_by( 'login', $username );
            if ( $existing_user ) {
                $user_id = $existing_user->ID;
            } else {
                $base_username = $username;
                $counter = 1;
                while ( username_exists( $username ) ) {
                    $username = $base_username . $counter;
                    $counter++;
                }

                $user_email = $email ?: $username . '@placeholder.invalid';
                $user_id = wp_insert_user( [
                    'user_login' => $username,
                    'user_email' => $user_email,
                    'user_pass'  => wp_generate_password( 24 ),
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'role'       => 'subscriber',
                ] );
            }
        }

        if ( ! $user_id || is_wp_error( $user_id ) ) continue;

        // Skip if this user already has a member record (prevents duplicates on re-run)
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$prefix}members WHERE user_id = %d", $user_id
        ) );
        if ( $exists ) continue;

        // WHY: email lives on the WordPress user (wp_users.user_email), not on sp_members.
        // Column names: postal_code (not zip), date_of_birth (not birthday), no marital_status.
        $wpdb->insert( "{$prefix}members", [
            'user_id'          => $user_id,
            'first_name'       => $first_name,
            'last_name'        => $last_name,
            'preferred_name'   => $pref_name,
            'phone'            => $phone,
            'cell'             => $cell,
            'address_1'        => $address,
            'city'             => $city,
            'state'            => $state,
            'postal_code'      => $zip,
            'country'          => $country ?: 'US',
            'gender'           => $gender,
            'date_of_birth'    => $birthday ?: null,
            'join_date'        => $join_date ?: current_time( 'Y-m-d' ),
            'expiration_date'  => $exp_date ?: null,
            'status'           => in_array( $status, [ 'active', 'expired', 'lapsed', 'grace', 'pending', 'inactive', 'deceased' ] ) ? $status : 'active',
            'tier_id'          => $tier_id,
            'member_type'      => 'individual',
            'created_at'       => current_time( 'mysql' ),
        ] );

        if ( $surnames ) {
            foreach ( array_map( 'trim', explode( ';', $surnames ) ) as $sname ) {
                if ( empty( $sname ) ) continue;
                $wpdb->insert( "{$prefix}member_surnames", [
                    'user_id'        => $user_id,
                    'surname'        => $sname,
                    'soundex_code'   => soundex( $sname ),
                    'metaphone_code' => metaphone( $sname ),
                ] );
            }
        }

        if ( $amount && (float) $amount > 0 ) {
            $wpdb->insert( "{$prefix}member_payments", [
                'user_id'    => $user_id,
                'amount'     => (float) $amount,
                'type'       => 'dues',
                'method'     => strtolower( $pay_method ) ?: 'check',
                'note'       => '',
                'date'       => $pay_date ?: $join_date ?: current_time( 'Y-m-d' ),
                'created_at' => current_time( 'mysql' ),
            ] );
        }

        if ( $notes ) {
            $wpdb->insert( "{$prefix}member_notes", [
                'user_id'    => $user_id,
                'note'       => $notes,
                'author'     => 'Sample Data Import',
                'created_at' => current_time( 'mysql' ),
            ] );
        }

        $imported++;
    }

    wp_cache_flush();

    $new_offset = $offset + $batch_size;
    wp_send_json_success( [
        'imported' => $imported,
        'offset'   => $new_offset,
        'total'    => $total,
        'done'     => $new_offset >= $total,
    ] );
}

// ============================================================================
// AJAX: CLEAR ALL MEMBERS
// ============================================================================

if ( isset( $_POST['sp_batch_action'] ) && $_POST['sp_batch_action'] === 'clear' ) {
    check_ajax_referer( 'sp_sample_data_nonce', 'nonce' );

    global $wpdb;
    $prefix = $wpdb->prefix . 'sp_';
    $member_ids = $wpdb->get_col( "SELECT user_id FROM {$prefix}members" );
    $cleared = 0;

    if ( $member_ids ) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
        $current_user_id = get_current_user_id();

        foreach ( $member_ids as $uid ) {
            $uid = (int) $uid;
            if ( $uid === $current_user_id ) continue;
            if ( user_can( $uid, 'manage_options' ) ) continue;

            if ( function_exists( 'sp_cascade_delete_member_data' ) ) {
                sp_cascade_delete_member_data( $uid );
            } else {
                $wpdb->delete( "{$prefix}members", [ 'user_id' => $uid ] );
                $wpdb->delete( "{$prefix}member_surnames", [ 'user_id' => $uid ] );
            }
            wp_delete_user( $uid );
            $cleared++;
        }
    }

    wp_send_json_success( [ 'cleared' => $cleared ] );
}

// ============================================================================
// CONFIRMATION PAGE WITH PROGRESS BAR
// ============================================================================

global $wpdb;
$prefix = $wpdb->prefix . 'sp_';
$member_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}members" );

// Count total rows in CSV for the progress bar
$total_rows = 0;
if ( $csv_path ) {
    $total_rows = max( 0, count( file( $csv_path ) ) - 1 ); // minus header
}

$nonce = wp_create_nonce( 'sp_sample_data_nonce' );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Data — SocietyPress</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .sp-panel {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
        }
        .sp-panel-header {
            background: #0D1F3C;
            color: #fff;
            padding: 24px 32px;
            text-align: center;
        }
        .sp-panel-header h1 {
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #C9973A;
            margin-bottom: 4px;
        }
        .sp-panel-header .title { font-size: 22px; font-weight: 700; }
        .sp-panel-body { padding: 32px; }
        .sp-panel-body p { color: #6B7280; margin-bottom: 16px; line-height: 1.6; }
        .sp-stat {
            background: #F9FAFB;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
            text-align: center;
        }
        .sp-stat-number { font-size: 36px; font-weight: 800; color: #0D1F3C; }
        .sp-stat-label { font-size: 13px; color: #6B7280; }
        .sp-btn {
            display: inline-block;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
        }
        .sp-btn-load { background: #C9973A; color: #fff; }
        .sp-btn-load:hover { background: #B8862F; }
        .sp-btn-clear { background: #DC2626; color: #fff; }
        .sp-btn-clear:hover { background: #B91C1C; }
        .sp-btn-back { background: #E5E7EB; color: #374151; }
        .sp-btn-back:hover { background: #D1D5DB; }
        .sp-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .sp-actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
        .sp-warning {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #991B1B;
        }

        /* Progress bar */
        .sp-progress-wrap {
            display: none;
            margin: 24px 0;
        }
        .sp-progress-wrap.active { display: block; }
        .sp-progress-bar-bg {
            background: #E5E7EB;
            border-radius: 999px;
            height: 24px;
            overflow: hidden;
            position: relative;
        }
        .sp-progress-bar {
            background: linear-gradient(90deg, #C9973A, #E5B960);
            height: 100%;
            width: 0%;
            border-radius: 999px;
            transition: width 0.3s ease;
        }
        .sp-progress-text {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #0D1F3C;
        }
        .sp-progress-detail {
            text-align: center;
            margin-top: 8px;
            font-size: 13px;
            color: #6B7280;
        }
        .sp-done {
            text-align: center;
            padding: 20px 0;
            display: none;
        }
        .sp-done.active { display: block; }
        .sp-done-icon { font-size: 48px; margin-bottom: 12px; }
        .sp-done h3 { font-size: 20px; color: #0D1F3C; margin-bottom: 8px; }
        .sp-done p { color: #6B7280; }
    </style>
</head>
<body>
    <div class="sp-panel">
        <div class="sp-panel-header">
            <h1>SocietyPress</h1>
            <div class="title">Sample Data</div>
        </div>
        <div class="sp-panel-body">

            <!-- Initial state -->
            <div id="sp-initial">
                <div class="sp-stat">
                    <div class="sp-stat-number" id="sp-count"><?php echo number_format( $member_count ); ?></div>
                    <div class="sp-stat-label">members currently in the system</div>
                </div>

                <?php if ( $member_count > 0 ) : ?>
                    <p>
                        This site already has member data. You can clear it and start fresh,
                        or load sample data on top of what's already here.
                    </p>
                    <div class="sp-warning">
                        <strong>Clear</strong> will permanently delete all members and their associated data
                        (except your administrator account). This cannot be undone.
                    </div>
                    <div class="sp-actions">
                        <button class="sp-btn sp-btn-clear" id="sp-clear-btn"
                                onclick="clearData()">
                            Clear All Members
                        </button>
                        <button class="sp-btn sp-btn-load" id="sp-load-btn"
                                onclick="loadData()">
                            Load <?php echo number_format( $total_rows ); ?> Sample Members
                        </button>
                    </div>
                <?php else : ?>
                    <p>
                        We've prepared <?php echo number_format( $total_rows ); ?> sample members so you can explore
                        SocietyPress without using your society's real data. Names, addresses, emails,
                        surnames, payments — all realistic but entirely fictional.
                    </p>
                    <p style="font-size: 13px;">
                        This is the best way to see what a fully populated society looks like.
                        After you install SocietyPress on your own server, you'll be starting
                        fresh — the first thing you'll do is add yourself as a member, then
                        import your society's membership roll.
                    </p>
                    <div class="sp-actions">
                        <button class="sp-btn sp-btn-load" id="sp-load-btn"
                                onclick="loadData()">
                            Load Sample Data
                        </button>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress' ) ); ?>" class="sp-btn sp-btn-back">
                            Skip — Go to Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Progress bar -->
            <div class="sp-progress-wrap" id="sp-progress">
                <p style="text-align: center; font-weight: 600; color: #0D1F3C; margin-bottom: 16px;">
                    Importing members...
                </p>
                <div class="sp-progress-bar-bg">
                    <div class="sp-progress-bar" id="sp-bar"></div>
                    <div class="sp-progress-text" id="sp-bar-text">0%</div>
                </div>
                <div class="sp-progress-detail" id="sp-detail">
                    Starting import...
                </div>
            </div>

            <!-- Done state -->
            <div class="sp-done" id="sp-done">
                <div class="sp-done-icon">&#127881;</div>
                <h3 id="sp-done-title">Import Complete!</h3>
                <p id="sp-done-text"></p>
                <div style="margin-top: 20px;">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress' ) ); ?>" class="sp-btn sp-btn-load">
                        Go to Dashboard
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script>
    var NONCE      = '<?php echo esc_js( $nonce ); ?>';
    var TOTAL_ROWS = <?php echo (int) $total_rows; ?>;
    var BATCH_SIZE = 50;
    var SCRIPT_URL = '<?php echo esc_js( home_url( '/load-sample-data.php' ) ); ?>';
    var totalImported = 0;

    function loadData() {
        document.getElementById('sp-initial').style.display = 'none';
        document.getElementById('sp-progress').classList.add('active');
        totalImported = 0;
        sendBatch(0);
    }

    function sendBatch(offset) {
        var formData = new FormData();
        formData.append('sp_batch_action', 'import_batch');
        formData.append('nonce', NONCE);
        formData.append('offset', offset);
        formData.append('batch_size', BATCH_SIZE);

        fetch(SCRIPT_URL, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                if (!resp.success) {
                    document.getElementById('sp-detail').textContent = 'Error: ' + (resp.data?.message || 'Unknown error');
                    return;
                }

                totalImported += resp.data.imported;
                var pct = Math.min(100, Math.round((totalImported / TOTAL_ROWS) * 100));

                document.getElementById('sp-bar').style.width = pct + '%';
                document.getElementById('sp-bar-text').textContent = pct + '%';
                document.getElementById('sp-detail').textContent =
                    totalImported.toLocaleString() + ' of ' + TOTAL_ROWS.toLocaleString() + ' members imported...';

                if (resp.data.done) {
                    showDone(totalImported + ' members imported successfully.');
                } else {
                    sendBatch(resp.data.offset);
                }
            })
            .catch(function(err) {
                document.getElementById('sp-detail').textContent = 'Network error: ' + err.message;
            });
    }

    function clearData() {
        if (!confirm('Delete all member data? This cannot be undone.')) return;

        var btn = document.getElementById('sp-clear-btn');
        btn.disabled = true;
        btn.textContent = 'Clearing...';

        var formData = new FormData();
        formData.append('sp_batch_action', 'clear');
        formData.append('nonce', NONCE);

        fetch(SCRIPT_URL, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                if (resp.success) {
                    showDone(resp.data.cleared + ' members removed.');
                    document.getElementById('sp-done-title').textContent = 'Data Cleared';
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Clear All Members';
                    alert('Error clearing data.');
                }
            });
    }

    function showDone(message) {
        document.getElementById('sp-initial').style.display = 'none';
        document.getElementById('sp-progress').classList.remove('active');
        document.getElementById('sp-done').classList.add('active');
        document.getElementById('sp-done-text').textContent = message;
    }
    </script>
</body>
</html>
<?php

function sp_sample_convert_date( string $date ): string {
    if ( empty( $date ) ) return '';
    $parts = explode( '/', $date );
    if ( count( $parts ) === 3 ) {
        return sprintf( '%04d-%02d-%02d', (int) $parts[2], (int) $parts[0], (int) $parts[1] );
    }
    $ts = strtotime( $date );
    return $ts ? date( 'Y-m-d', $ts ) : '';
}
