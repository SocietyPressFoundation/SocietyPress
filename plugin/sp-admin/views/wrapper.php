<?php
/**
 * SocietyPress Admin Wrapper Template
 *
 * This is the main layout template for all /sp-admin/ pages. It provides:
 * - Sidebar navigation with module links
 * - Header with society name and user info
 * - Content area where individual views are rendered
 *
 * WHY THIS DESIGN:
 * - Fixed sidebar means navigation is always visible and in the same place
 * - Large text and high contrast for elderly users
 * - No clutter - only the modules they need
 * - Clear visual hierarchy
 *
 * Variables available:
 * - $router: SP_Admin_Router instance
 * - $module: Current module name
 * - $route: Current route string
 * - $params: Route parameters array
 * - $navigation: Array of accessible modules
 * - $user: Current WP_User object
 * - $view_file: Path to the content view file
 *
 * @package SocietyPress
 * @since 0.59
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get organization name from settings
$organization_name = get_option( 'societypress_organization_name', '' );
if ( empty( $organization_name ) ) {
    $settings = get_option( 'societypress_settings', [] );
    $organization_name = $settings['organization_name'] ?? get_bloginfo( 'name' );
}
$organization_logo = get_option( 'societypress_organization_logo', '' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo esc_html( $navigation[ $module ]['label'] ?? 'Dashboard' ); ?> - <?php echo esc_html( $organization_name ); ?></title>

    <?php
    // Enqueue our admin styles and scripts
    wp_enqueue_style(
        'sp-admin-styles',
        SOCIETYPRESS_URL . 'sp-admin/css/sp-admin.css',
        [],
        SOCIETYPRESS_VERSION
    );

    wp_enqueue_script(
        'sp-admin-scripts',
        SOCIETYPRESS_URL . 'sp-admin/js/sp-admin.js',
        [],
        SOCIETYPRESS_VERSION,
        true
    );

    // Localize script with useful data
    wp_localize_script( 'sp-admin-scripts', 'spAdmin', [
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'sp_admin_nonce' ),
        'homeUrl'  => home_url( '/sp-admin/' ),
    ] );

    wp_head();
    ?>
</head>
<body class="sp-admin sp-admin-<?php echo esc_attr( $module ); ?>">
    <div class="sp-admin-container">

        <!-- Sidebar Navigation -->
        <aside class="sp-admin-sidebar">
            <div class="sp-admin-sidebar-header">
                <?php if ( $organization_logo ) : ?>
                    <img src="<?php echo esc_url( $organization_logo ); ?>" alt="" class="sp-admin-logo">
                <?php endif; ?>
                <span class="sp-admin-org-name"><?php echo esc_html( $organization_name ); ?></span>
            </div>

            <nav class="sp-admin-nav">
                <ul class="sp-admin-nav-list">
                    <?php foreach ( $navigation as $nav_slug => $nav_item ) : ?>
                        <li class="sp-admin-nav-item <?php echo $nav_slug === $module ? 'sp-admin-nav-item--active' : ''; ?>">
                            <a href="<?php echo esc_url( $router->url( $nav_slug ) ); ?>" class="sp-admin-nav-link">
                                <span class="sp-admin-nav-icon"><?php echo esc_html( $nav_item['icon'] ); ?></span>
                                <span class="sp-admin-nav-label"><?php echo esc_html( $nav_item['label'] ); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="sp-admin-sidebar-footer">
                <div class="sp-admin-user-info">
                    <span class="sp-admin-user-greeting">Logged in as:</span>
                    <span class="sp-admin-user-name"><?php echo esc_html( $user->display_name ); ?></span>
                </div>
                <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="sp-admin-logout-link">
                    Log Out
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="sp-admin-main">
            <!-- Flash Messages -->
            <?php if ( isset( $_GET['success'] ) ) : ?>
                <div class="sp-admin-notice sp-admin-notice--success">
                    <span class="sp-admin-notice-icon">✓</span>
                    <span class="sp-admin-notice-message">
                        <?php echo esc_html( $router->get_success_message( sanitize_key( $_GET['success'] ) ) ); ?>
                    </span>
                    <button type="button" class="sp-admin-notice-dismiss" aria-label="Dismiss">×</button>
                </div>
            <?php endif; ?>

            <?php if ( isset( $_GET['error'] ) ) : ?>
                <div class="sp-admin-notice sp-admin-notice--error">
                    <span class="sp-admin-notice-icon">⚠</span>
                    <span class="sp-admin-notice-message">
                        <?php echo esc_html( urldecode( $_GET['error'] ) ); ?>
                    </span>
                    <button type="button" class="sp-admin-notice-dismiss" aria-label="Dismiss">×</button>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="sp-admin-content">
                <?php
                // Include the view file for this route
                if ( file_exists( $view_file ) ) {
                    include $view_file;
                } else {
                    echo '<div class="sp-admin-error"><h2>Page Not Found</h2><p>The requested page could not be found.</p></div>';
                }
                ?>
            </div>
        </main>

    </div>

    <?php wp_footer(); ?>
</body>
</html>
