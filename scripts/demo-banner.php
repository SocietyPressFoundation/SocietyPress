<?php
/**
 * Demo Site Banner — View As... Role Switcher
 *
 * Lets evaluators see the demo site as different user types without
 * logging out. Thin bar at the top with Admin / Member / Visitor buttons.
 */

// Handle role switch requests.
// WHY the cookie check: when in visitor mode, determine_current_user returns 0
// so wp_get_current_user() has no caps. We can't verify admin status through
// the normal API. Instead, if the sp_view_as cookie already exists, the user
// must have been an admin to set it — so we allow the switch. On first switch
// (no cookie yet), we verify admin caps normally.
add_action( 'init', function () {
    if ( ! isset( $_GET['sp_view_as'] ) ) return;

    $has_existing_cookie = isset( $_COOKIE['sp_view_as'] ) && $_COOKIE['sp_view_as'] !== '';

    if ( ! $has_existing_cookie ) {
        // First switch — verify the user is actually an admin
        $user = wp_get_current_user();
        if ( ! $user || ! $user->has_cap( 'manage_options' ) ) return;
    }

    $role = sanitize_key( $_GET['sp_view_as'] );
    if ( $role === 'reset' ) {
        setcookie( 'sp_view_as', '', time() - 3600, '/' );
    } else {
        setcookie( 'sp_view_as', $role, 0, '/' );
    }

    wp_safe_redirect( remove_query_arg( 'sp_view_as' ) );
    exit;
} );

// Override current user for "visitor" mode on the frontend.
// Skip the override if the user is clicking a switch link (sp_view_as in URL)
// so the init handler above can process the switch first.
add_filter( 'determine_current_user', function ( $user_id ) {
    if ( is_admin() ) return $user_id;
    if ( isset( $_GET['sp_view_as'] ) ) return $user_id; // let the switch happen
    if ( ! isset( $_COOKIE['sp_view_as'] ) ) return $user_id;
    if ( $_COOKIE['sp_view_as'] === 'visitor' ) return 0;
    return $user_id;
}, 999 );

// Render the banner on the frontend for admins (or when a view-as cookie is set)
add_action( 'wp_head', function () {
    if ( is_admin() ) return;

    // Show banner if logged-in admin OR if view-as cookie exists
    $has_cookie = isset( $_COOKIE['sp_view_as'] ) && $_COOKIE['sp_view_as'] !== '';
    if ( ! current_user_can( 'manage_options' ) && ! $has_cookie ) return;

    $current = $_COOKIE['sp_view_as'] ?? 'admin';
    $roles   = [
        'admin'   => 'Admin',
        'member'  => 'Member',
        'visitor' => 'Visitor',
    ];
    ?>
    <style>
        #sp-demo-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 99999;
            background: #1a1a1a; color: #aaa; text-align: center;
            font: 13px/36px -apple-system, BlinkMacSystemFont, sans-serif;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        #sp-demo-bar a {
            color: #fff; text-decoration: none; padding: 3px 14px;
            border-radius: 3px; background: #333; font-size: 12px;
        }
        #sp-demo-bar a:hover { background: #555; }
        #sp-demo-bar a.active { background: #c4933f; color: #1a1a1a; font-weight: 600; }
        #sp-demo-bar .label { color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        body { margin-top: 36px !important; }
        body.admin-bar #sp-demo-bar { top: 32px; }
        body.admin-bar { margin-top: 36px !important; }
    </style>
    <div id="sp-demo-bar">
        <span class="label">SocietyPress Demo &mdash; View as:</span>
        <?php foreach ( $roles as $key => $label ) :
            $cls = ( $current === $key ) ? ' active' : '';
            $url = ( $key === 'admin' )
                ? esc_url( add_query_arg( 'sp_view_as', 'reset' ) )
                : esc_url( add_query_arg( 'sp_view_as', $key ) );
        ?>
        <a href="<?php echo $url; ?>" class="<?php echo $cls; ?>"><?php echo esc_html( $label ); ?></a>
        <?php endforeach; ?>
    </div>
    <?php
} );
