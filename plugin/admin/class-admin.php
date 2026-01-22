<?php
/**
 * Admin Interface
 *
 * Handles all WordPress admin functionality: menus, pages, scripts, styles.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Admin
 *
 * Main admin controller for SocietyPress.
 */
class SocietyPress_Admin {

    /**
     * Members list table instance.
     *
     * @var SocietyPress_Members_List_Table|null
     */
    private ?SocietyPress_Members_List_Table $members_table = null;

    /**
     * Import handler instance.
     *
     * @var SocietyPress_Import|null
     */
    private ?SocietyPress_Import $import = null;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->import = new SocietyPress_Import();
        $this->init_hooks();
    }

    /**
     * Register hooks.
     */
    private function init_hooks(): void {
        add_action( 'admin_menu', array( $this, 'add_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_init', array( $this, 'handle_actions' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Register plugin settings using the WordPress Settings API.
     *
     * All SocietyPress settings are stored in a single option: 'societypress_settings'
     */
    public function register_settings(): void {
        // Register the main settings option
        register_setting(
            'societypress_settings_group',
            'societypress_settings',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'default'           => $this->get_default_settings(),
            )
        );

        // Display Settings Section
        add_settings_section(
            'societypress_display_section',
            __( 'Display Settings', 'societypress' ),
            array( $this, 'render_display_section' ),
            'societypress-settings'
        );

        add_settings_field(
            'members_per_page',
            __( 'Members per page', 'societypress' ),
            array( $this, 'render_members_per_page_field' ),
            'societypress-settings',
            'societypress_display_section'
        );

        // Organization Settings Section
        add_settings_section(
            'societypress_organization_section',
            __( 'Organization', 'societypress' ),
            array( $this, 'render_organization_section' ),
            'societypress-settings'
        );

        add_settings_field(
            'organization_name',
            __( 'Organization Name', 'societypress' ),
            array( $this, 'render_organization_name_field' ),
            'societypress-settings',
            'societypress_organization_section'
        );

        // Email Settings Section
        add_settings_section(
            'societypress_email_section',
            __( 'Email Settings', 'societypress' ),
            array( $this, 'render_email_section' ),
            'societypress-settings'
        );

        add_settings_field(
            'admin_email',
            __( 'Admin Email', 'societypress' ),
            array( $this, 'render_admin_email_field' ),
            'societypress-settings',
            'societypress_email_section'
        );
    }

    /**
     * Get default settings values.
     *
     * @return array Default settings.
     */
    public function get_default_settings(): array {
        return array(
            'members_per_page'  => 20,
            'organization_name' => get_bloginfo( 'name' ),
            'admin_email'       => get_option( 'admin_email' ),
        );
    }

    /**
     * Sanitize settings before saving.
     *
     * @param array $input Raw input from form.
     * @return array Sanitized settings.
     */
    public function sanitize_settings( array $input ): array {
        $sanitized = array();

        $sanitized['members_per_page'] = isset( $input['members_per_page'] )
            ? max( 1, absint( $input['members_per_page'] ) )
            : 20;

        $sanitized['organization_name'] = isset( $input['organization_name'] )
            ? sanitize_text_field( $input['organization_name'] )
            : '';

        $sanitized['admin_email'] = isset( $input['admin_email'] )
            ? sanitize_email( $input['admin_email'] )
            : get_option( 'admin_email' );

        return $sanitized;
    }

    /**
     * Get a single setting value.
     *
     * @param string $key     Setting key.
     * @param mixed  $default Default value if not set.
     * @return mixed Setting value.
     */
    public static function get_setting( string $key, $default = null ) {
        $settings = get_option( 'societypress_settings', array() );
        $defaults = array(
            'members_per_page'  => 20,
            'organization_name' => get_bloginfo( 'name' ),
            'admin_email'       => get_option( 'admin_email' ),
        );

        if ( null === $default && isset( $defaults[ $key ] ) ) {
            $default = $defaults[ $key ];
        }

        return $settings[ $key ] ?? $default;
    }

    /**
     * Render Display section description.
     */
    public function render_display_section(): void {
        echo '<p>' . esc_html__( 'Configure how data is displayed in the admin area.', 'societypress' ) . '</p>';
    }

    /**
     * Render Organization section description.
     */
    public function render_organization_section(): void {
        echo '<p>' . esc_html__( 'Basic information about your organization.', 'societypress' ) . '</p>';
    }

    /**
     * Render Email section description.
     */
    public function render_email_section(): void {
        echo '<p>' . esc_html__( 'Email notification settings.', 'societypress' ) . '</p>';
    }

    /**
     * Render members_per_page field.
     */
    public function render_members_per_page_field(): void {
        $value = self::get_setting( 'members_per_page', 20 );
        ?>
        <input type="number" name="societypress_settings[members_per_page]"
               value="<?php echo esc_attr( $value ); ?>" min="1" max="9999" class="small-text">
        <p class="description">
            <?php esc_html_e( 'Number of members to show per page in the admin list.', 'societypress' ); ?>
        </p>
        <?php
    }

    /**
     * Render organization_name field.
     */
    public function render_organization_name_field(): void {
        $value = self::get_setting( 'organization_name', get_bloginfo( 'name' ) );
        ?>
        <input type="text" name="societypress_settings[organization_name]"
               value="<?php echo esc_attr( $value ); ?>" class="regular-text">
        <p class="description">
            <?php esc_html_e( 'Used in emails and member-facing pages.', 'societypress' ); ?>
        </p>
        <?php
    }

    /**
     * Render admin_email field.
     */
    public function render_admin_email_field(): void {
        $value = self::get_setting( 'admin_email', get_option( 'admin_email' ) );
        ?>
        <input type="email" name="societypress_settings[admin_email]"
               value="<?php echo esc_attr( $value ); ?>" class="regular-text">
        <p class="description">
            <?php esc_html_e( 'Receives membership notifications and admin alerts.', 'societypress' ); ?>
        </p>
        <?php
    }

    /**
     * Add admin menu pages.
     *
     * Creates the main SocietyPress menu and submenus.
     */
    public function add_menus(): void {
        // Main menu
        add_menu_page(
            __( 'SocietyPress', 'societypress' ),
            __( 'SocietyPress', 'societypress' ),
            'manage_society_members',
            'societypress',
            array( $this, 'render_dashboard' ),
            'dashicons-groups',
            30
        );

        // Dashboard (same as main)
        add_submenu_page(
            'societypress',
            __( 'Dashboard', 'societypress' ),
            __( 'Dashboard', 'societypress' ),
            'manage_society_members',
            'societypress',
            array( $this, 'render_dashboard' )
        );

        // Members list
        add_submenu_page(
            'societypress',
            __( 'Members', 'societypress' ),
            __( 'Members', 'societypress' ),
            'manage_society_members',
            'societypress-members',
            array( $this, 'render_members_page' )
        );

        // Add/Edit member
        add_submenu_page(
            'societypress',
            __( 'Add Member', 'societypress' ),
            __( 'Add Member', 'societypress' ),
            'manage_society_members',
            'societypress-member-edit',
            array( $this, 'render_member_edit' )
        );

        // Import members
        add_submenu_page(
            'societypress',
            __( 'Import Members', 'societypress' ),
            __( 'Import', 'societypress' ),
            'manage_society_members',
            'societypress-import',
            array( $this, 'render_import_page' )
        );

        // Membership tiers
        add_submenu_page(
            'societypress',
            __( 'Membership Tiers', 'societypress' ),
            __( 'Tiers', 'societypress' ),
            'manage_society_members',
            'societypress-tiers',
            array( $this, 'render_tiers_page' )
        );

        // Settings
        add_submenu_page(
            'societypress',
            __( 'Settings', 'societypress' ),
            __( 'Settings', 'societypress' ),
            'manage_society_members',
            'societypress-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets( string $hook ): void {
        // Only load on our pages
        if ( strpos( $hook, 'societypress' ) === false ) {
            return;
        }

        // Admin styles
        wp_enqueue_style(
            'societypress-admin',
            SOCIETYPRESS_CORE_URL . 'assets/css/admin.css',
            array(),
            SOCIETYPRESS_CORE_VERSION
        );

        // Admin scripts
        wp_enqueue_script(
            'societypress-admin',
            SOCIETYPRESS_CORE_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            SOCIETYPRESS_CORE_VERSION,
            true
        );

        // Localize script with data
        wp_localize_script(
            'societypress-admin',
            'societypressAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'societypress_admin' ),
                'strings' => array(
                    'confirmDelete'            => __( 'Are you sure you want to delete this member? This cannot be undone.', 'societypress' ),
                    'confirmDeleteAll'         => __( 'WARNING: This will PERMANENTLY DELETE ALL MEMBERS from the database. This action CANNOT be undone. Are you absolutely sure?', 'societypress' ),
                    'confirmDeleteAllSelected' => __( 'You are about to delete ALL members across all pages. Are you sure?', 'societypress' ),
                    'saving'                   => __( 'Saving...', 'societypress' ),
                    'saved'                    => __( 'Saved!', 'societypress' ),
                    'error'                    => __( 'An error occurred. Please try again.', 'societypress' ),
                ),
            )
        );

        // Load import script on import page
        if ( strpos( $hook, 'societypress-import' ) !== false ) {
            wp_enqueue_script(
                'societypress-import',
                SOCIETYPRESS_CORE_URL . 'assets/js/import.js',
                array( 'jquery' ),
                SOCIETYPRESS_CORE_VERSION,
                true
            );
        }
    }

    /**
     * Handle admin actions (form submissions, etc.).
     */
    public function handle_actions(): void {
        // Check for member form submission
        if ( isset( $_POST['societypress_action'] ) && 'save_member' === $_POST['societypress_action'] ) {
            $this->save_member();
        }

        // Check for tier form submission
        if ( isset( $_POST['societypress_save_tier'] ) ) {
            $this->save_tier();
        }

        // Check for single member delete
        if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['member'] ) ) {
            $this->delete_member();
        }

        // Check for bulk actions
        if ( isset( $_POST['action'] ) && -1 !== (int) $_POST['action'] ) {
            $this->handle_bulk_action( sanitize_text_field( $_POST['action'] ) );
        } elseif ( isset( $_POST['action2'] ) && -1 !== (int) $_POST['action2'] ) {
            $this->handle_bulk_action( sanitize_text_field( $_POST['action2'] ) );
        }
    }

    /**
     * Delete a single member.
     */
    private function delete_member(): void {
        $member_id = absint( $_GET['member'] ?? 0 );

        // Verify nonce
        if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'delete_member_' . $member_id ) ) {
            wp_die( __( 'Security check failed.', 'societypress' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_society_members' ) ) {
            wp_die( __( 'You do not have permission to delete members.', 'societypress' ) );
        }

        $members = societypress()->members;
        $member = $members->get( $member_id );

        if ( ! $member ) {
            wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=not_found' ) );
            exit;
        }

        $result = $members->delete( $member_id );

        if ( $result ) {
            wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=deleted' ) );
        } else {
            wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=delete_error' ) );
        }
        exit;
    }

    /**
     * Handle bulk actions on members.
     *
     * @param string $action The bulk action to perform.
     */
    private function handle_bulk_action( string $action ): void {
        // Only process on members page
        if ( ! isset( $_GET['page'] ) || 'societypress-members' !== $_GET['page'] ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'manage_society_members' ) ) {
            return;
        }

        // Verify bulk action nonce
        check_admin_referer( 'bulk-members' );

        $members = societypress()->members;
        $count = 0;

        // Handle "Delete ALL Members" action - this doesn't require selected members
        if ( 'delete_all' === $action ) {
            // Get all member IDs
            $all_members = $members->get_members( array( 'limit' => 0 ) );

            foreach ( $all_members as $member ) {
                if ( $members->delete( $member->id ) ) {
                    $count++;
                }
            }

            wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=all_deleted&count=' . $count ) );
            exit;
        }

        // For other bulk actions, we need selected member IDs
        // Check if "select all" was triggered (hidden field set by JavaScript)
        if ( isset( $_POST['societypress_select_all'] ) && '1' === $_POST['societypress_select_all'] ) {
            // Build filter args to match what was displayed (respects current filters)
            $filter_args = array( 'limit' => 0 );

            if ( ! empty( $_POST['societypress_filter_status'] ) ) {
                $filter_args['status'] = sanitize_text_field( $_POST['societypress_filter_status'] );
            }
            if ( ! empty( $_POST['societypress_filter_tier'] ) ) {
                $filter_args['tier_id'] = absint( $_POST['societypress_filter_tier'] );
            }
            if ( ! empty( $_POST['societypress_filter_search'] ) ) {
                $filter_args['search'] = sanitize_text_field( $_POST['societypress_filter_search'] );
            }

            // Get filtered member IDs from the database
            $all_members = $members->get_members( $filter_args );
            $member_ids = array_map( function( $m ) { return $m->id; }, $all_members );
        } else {
            // Use the selected checkboxes
            $member_ids = array_map( 'absint', $_POST['member'] ?? array() );
        }

        if ( empty( $member_ids ) ) {
            return;
        }

        switch ( $action ) {
            case 'delete':
                foreach ( $member_ids as $id ) {
                    if ( $members->delete( $id ) ) {
                        $count++;
                    }
                }
                $message = 'bulk_deleted';
                break;

            case 'activate':
                foreach ( $member_ids as $id ) {
                    if ( $members->update_status( $id, 'active' ) ) {
                        $count++;
                    }
                }
                $message = 'bulk_activated';
                break;

            case 'deactivate':
                foreach ( $member_ids as $id ) {
                    if ( $members->update_status( $id, 'expired' ) ) {
                        $count++;
                    }
                }
                $message = 'bulk_deactivated';
                break;

            default:
                return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=' . $message . '&count=' . $count ) );
        exit;
    }

    /**
     * Render the dashboard page.
     */
    public function render_dashboard(): void {
        // Get some basic stats
        $members = societypress()->members;
        $tiers   = societypress()->tiers;

        $stats = array(
            'total'   => count( $members->get_members( array( 'limit' => 0 ) ) ),
            'active'  => count( $members->get_members( array( 'status' => 'active', 'limit' => 0 ) ) ),
            'expired' => count( $members->get_members( array( 'status' => 'expired', 'limit' => 0 ) ) ),
            'pending' => count( $members->get_members( array( 'status' => 'pending', 'limit' => 0 ) ) ),
        );

        $tier_counts = $tiers->get_member_counts();
        $all_tiers   = $tiers->get_all();

        ?>
        <div class="wrap societypress-admin">
            <h1><?php esc_html_e( 'SocietyPress Dashboard', 'societypress' ); ?></h1>

            <div class="societypress-dashboard-stats">
                <div class="stat-card">
                    <span class="stat-number"><?php echo esc_html( $stats['total'] ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Total Members', 'societypress' ); ?></span>
                </div>
                <div class="stat-card stat-active">
                    <span class="stat-number"><?php echo esc_html( $stats['active'] ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Active', 'societypress' ); ?></span>
                </div>
                <div class="stat-card stat-expired">
                    <span class="stat-number"><?php echo esc_html( $stats['expired'] ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Expired', 'societypress' ); ?></span>
                </div>
                <div class="stat-card stat-pending">
                    <span class="stat-number"><?php echo esc_html( $stats['pending'] ); ?></span>
                    <span class="stat-label"><?php esc_html_e( 'Pending', 'societypress' ); ?></span>
                </div>
            </div>

            <div class="societypress-dashboard-sections">
                <div class="dashboard-section">
                    <h2><?php esc_html_e( 'Members by Tier', 'societypress' ); ?></h2>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Tier', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Members', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Price', 'societypress' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $all_tiers as $tier ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $tier->name ); ?></td>
                                    <td><?php echo esc_html( $tier_counts[ $tier->id ] ?? 0 ); ?></td>
                                    <td>$<?php echo esc_html( number_format( $tier->price, 2 ) ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="dashboard-section">
                    <h2><?php esc_html_e( 'Quick Actions', 'societypress' ); ?></h2>
                    <p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-member-edit' ) ); ?>" class="button button-primary">
                            <?php esc_html_e( 'Add New Member', 'societypress' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-members' ) ); ?>" class="button">
                            <?php esc_html_e( 'View All Members', 'societypress' ); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the members list page.
     */
    public function render_members_page(): void {
        $this->members_table = new SocietyPress_Members_List_Table();
        $this->members_table->prepare_items();

        // Build filter args to match what's currently being displayed
        $filter_args = array( 'limit' => 0 );
        $current_status = '';
        $current_tier = '';
        $current_search = '';

        if ( ! empty( $_REQUEST['status'] ) ) {
            $current_status = sanitize_text_field( $_REQUEST['status'] );
            $filter_args['status'] = $current_status;
        }
        if ( ! empty( $_REQUEST['tier'] ) ) {
            $current_tier = absint( $_REQUEST['tier'] );
            $filter_args['tier_id'] = $current_tier;
        }
        if ( ! empty( $_REQUEST['s'] ) ) {
            $current_search = sanitize_text_field( $_REQUEST['s'] );
            $filter_args['search'] = $current_search;
        }

        // Get filtered total for "select all" functionality
        $total_members = count( societypress()->members->get_members( $filter_args ) );
        $items_on_page = count( $this->members_table->items );

        ?>
        <div class="wrap societypress-admin">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Members', 'societypress' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-member-edit' ) ); ?>" class="page-title-action">
                <?php esc_html_e( 'Add New', 'societypress' ); ?>
            </a>
            <hr class="wp-header-end">

            <?php $this->render_admin_notices(); ?>

            <form method="post" id="societypress-members-form">
                <input type="hidden" name="page" value="societypress-members">
                <input type="hidden" name="societypress_select_all" id="societypress_select_all" value="0">
                <!-- Pass current filters so bulk actions respect them -->
                <input type="hidden" name="societypress_filter_status" value="<?php echo esc_attr( $current_status ); ?>">
                <input type="hidden" name="societypress_filter_tier" value="<?php echo esc_attr( $current_tier ); ?>">
                <input type="hidden" name="societypress_filter_search" value="<?php echo esc_attr( $current_search ); ?>">
                <?php
                wp_nonce_field( 'bulk-members' );
                $this->members_table->search_box( __( 'Search Members', 'societypress' ), 'member' );
                ?>

                <!-- Select All Banner (hidden by default, shown via JS) -->
                <?php if ( $total_members > $items_on_page ) : ?>
                <div id="societypress-select-all-banner" class="notice notice-info inline" style="display: none; margin: 10px 0;">
                    <p>
                        <span id="societypress-select-all-page-msg">
                            <?php
                            printf(
                                /* translators: %d: number of items on current page */
                                esc_html__( 'All %d items on this page are selected.', 'societypress' ),
                                $items_on_page
                            );
                            ?>
                            <a href="#" id="societypress-select-all-link">
                                <?php
                                printf(
                                    /* translators: %d: total number of members matching current filters */
                                    esc_html__( 'Select all %d members', 'societypress' ),
                                    $total_members
                                );
                                ?>
                            </a>
                        </span>
                        <span id="societypress-all-selected-msg" style="display: none;">
                            <?php
                            printf(
                                /* translators: %d: total number of members matching current filters */
                                esc_html__( 'All %d members are selected.', 'societypress' ),
                                $total_members
                            );
                            ?>
                            <a href="#" id="societypress-clear-selection-link">
                                <?php esc_html_e( 'Clear selection', 'societypress' ); ?>
                            </a>
                        </span>
                    </p>
                </div>
                <?php endif; ?>

                <?php $this->members_table->display(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the member edit/add page.
     */
    public function render_member_edit(): void {
        $member_id = isset( $_GET['member_id'] ) ? absint( $_GET['member_id'] ) : 0;
        $member    = null;
        $contact   = null;

        if ( $member_id ) {
            $member  = societypress()->members->get( $member_id );
            $contact = societypress()->members->get_contact( $member_id );
        }

        $tiers = societypress()->tiers->get_active();

        ?>
        <div class="wrap societypress-admin">
            <h1>
                <?php
                echo $member_id
                    ? esc_html__( 'Edit Member', 'societypress' )
                    : esc_html__( 'Add New Member', 'societypress' );
                ?>
            </h1>

            <?php $this->render_admin_notices(); ?>

            <form method="post" action="" class="societypress-member-form">
                <?php wp_nonce_field( 'societypress_member', 'societypress_member_nonce' ); ?>
                <input type="hidden" name="societypress_action" value="save_member">
                <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">

                <div class="societypress-form-sections">
                    <!-- Basic Information -->
                    <div class="societypress-form-section">
                        <h2><?php esc_html_e( 'Basic Information', 'societypress' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><label for="first_name"><?php esc_html_e( 'First Name', 'societypress' ); ?> *</label></th>
                                <td>
                                    <input type="text" name="first_name" id="first_name" class="regular-text"
                                           value="<?php echo esc_attr( $member->first_name ?? '' ); ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="last_name"><?php esc_html_e( 'Last Name', 'societypress' ); ?> *</label></th>
                                <td>
                                    <input type="text" name="last_name" id="last_name" class="regular-text"
                                           value="<?php echo esc_attr( $member->last_name ?? '' ); ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="membership_tier_id"><?php esc_html_e( 'Membership Tier', 'societypress' ); ?> *</label></th>
                                <td>
                                    <select name="membership_tier_id" id="membership_tier_id" required>
                                        <?php foreach ( $tiers as $tier ) : ?>
                                            <option value="<?php echo esc_attr( $tier->id ); ?>"
                                                <?php selected( $member->membership_tier_id ?? '', $tier->id ); ?>>
                                                <?php echo esc_html( $tier->name ); ?> - $<?php echo esc_html( number_format( $tier->price, 2 ) ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="status"><?php esc_html_e( 'Status', 'societypress' ); ?></label></th>
                                <td>
                                    <select name="status" id="status">
                                        <?php foreach ( SocietyPress_Members::STATUSES as $status ) : ?>
                                            <option value="<?php echo esc_attr( $status ); ?>"
                                                <?php selected( $member->status ?? 'pending', $status ); ?>>
                                                <?php echo esc_html( ucfirst( $status ) ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="join_date"><?php esc_html_e( 'Join Date', 'societypress' ); ?></label></th>
                                <td>
                                    <input type="date" name="join_date" id="join_date"
                                           value="<?php echo esc_attr( $member->join_date ?? gmdate( 'Y-m-d' ) ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="expiration_date"><?php esc_html_e( 'Expiration Date', 'societypress' ); ?></label></th>
                                <td>
                                    <input type="date" name="expiration_date" id="expiration_date"
                                           value="<?php echo esc_attr( $member->expiration_date ?? '' ); ?>">
                                    <p class="description"><?php esc_html_e( 'Leave blank for lifetime memberships.', 'societypress' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Contact Information -->
                    <div class="societypress-form-section">
                        <h2><?php esc_html_e( 'Contact Information', 'societypress' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><label for="primary_email"><?php esc_html_e( 'Email', 'societypress' ); ?> *</label></th>
                                <td>
                                    <input type="email" name="primary_email" id="primary_email" class="regular-text"
                                           value="<?php echo esc_attr( $contact->primary_email ?? '' ); ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="cell_phone"><?php esc_html_e( 'Cell Phone', 'societypress' ); ?></label></th>
                                <td>
                                    <input type="tel" name="cell_phone" id="cell_phone" class="regular-text"
                                           value="<?php echo esc_attr( $contact->cell_phone ?? '' ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="street_address"><?php esc_html_e( 'Street Address', 'societypress' ); ?></label></th>
                                <td>
                                    <input type="text" name="street_address" id="street_address" class="large-text"
                                           value="<?php echo esc_attr( $contact->street_address ?? '' ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="city"><?php esc_html_e( 'City', 'societypress' ); ?></label></th>
                                <td>
                                    <input type="text" name="city" id="city" class="regular-text"
                                           value="<?php echo esc_attr( $contact->city ?? '' ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="state_province"><?php esc_html_e( 'State/Province', 'societypress' ); ?></label></th>
                                <td>
                                    <input type="text" name="state_province" id="state_province" class="regular-text"
                                           value="<?php echo esc_attr( $contact->state_province ?? '' ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="postal_code"><?php esc_html_e( 'Postal Code', 'societypress' ); ?></label></th>
                                <td>
                                    <input type="text" name="postal_code" id="postal_code"
                                           value="<?php echo esc_attr( $contact->postal_code ?? '' ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="country"><?php esc_html_e( 'Country', 'societypress' ); ?></label></th>
                                <td>
                                    <input type="text" name="country" id="country" class="regular-text"
                                           value="<?php echo esc_attr( $contact->country ?? 'USA' ); ?>">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Options -->
                    <div class="societypress-form-section">
                        <h2><?php esc_html_e( 'Options', 'societypress' ); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Directory', 'societypress' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="directory_visible" value="1"
                                            <?php checked( $member->directory_visible ?? 1, 1 ); ?>>
                                        <?php esc_html_e( 'Show in member directory', 'societypress' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Auto-Renew', 'societypress' ); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="auto_renew" value="1"
                                            <?php checked( $member->auto_renew ?? 0, 1 ); ?>>
                                        <?php esc_html_e( 'Enable automatic renewal', 'societypress' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="communication_preference"><?php esc_html_e( 'Communication Preference', 'societypress' ); ?></label></th>
                                <td>
                                    <select name="communication_preference" id="communication_preference">
                                        <option value="email" <?php selected( $member->communication_preference ?? 'email', 'email' ); ?>>
                                            <?php esc_html_e( 'Email', 'societypress' ); ?>
                                        </option>
                                        <option value="mail" <?php selected( $member->communication_preference ?? '', 'mail' ); ?>>
                                            <?php esc_html_e( 'Postal Mail', 'societypress' ); ?>
                                        </option>
                                        <option value="both" <?php selected( $member->communication_preference ?? '', 'both' ); ?>>
                                            <?php esc_html_e( 'Both', 'societypress' ); ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <p class="submit">
                    <input type="submit" name="societypress_save_member" class="button button-primary button-large"
                           value="<?php esc_attr_e( 'Save Member', 'societypress' ); ?>">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-members' ) ); ?>" class="button button-large">
                        <?php esc_html_e( 'Cancel', 'societypress' ); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render the tiers management page.
     */
    public function render_tiers_page(): void {
        $tiers = societypress()->tiers->get_all();

        ?>
        <div class="wrap societypress-admin">
            <h1><?php esc_html_e( 'Membership Tiers', 'societypress' ); ?></h1>

            <?php $this->render_admin_notices(); ?>

            <table class="widefat striped societypress-tiers-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Name', 'societypress' ); ?></th>
                        <th><?php esc_html_e( 'Slug', 'societypress' ); ?></th>
                        <th><?php esc_html_e( 'Price', 'societypress' ); ?></th>
                        <th><?php esc_html_e( 'Duration', 'societypress' ); ?></th>
                        <th><?php esc_html_e( 'Max Members', 'societypress' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
                        <th><?php esc_html_e( 'Members', 'societypress' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counts = societypress()->tiers->get_member_counts();
                    foreach ( $tiers as $tier ) :
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html( $tier->name ); ?></strong></td>
                            <td><code><?php echo esc_html( $tier->slug ); ?></code></td>
                            <td>$<?php echo esc_html( number_format( $tier->price, 2 ) ); ?></td>
                            <td>
                                <?php
                                if ( 0 === (int) $tier->duration_months ) {
                                    esc_html_e( 'Lifetime', 'societypress' );
                                } else {
                                    printf(
                                        /* translators: %d: number of months */
                                        esc_html( _n( '%d month', '%d months', $tier->duration_months, 'societypress' ) ),
                                        esc_html( $tier->duration_months )
                                    );
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html( $tier->max_members ); ?></td>
                            <td>
                                <?php if ( $tier->is_active ) : ?>
                                    <span class="societypress-status-active"><?php esc_html_e( 'Active', 'societypress' ); ?></span>
                                <?php else : ?>
                                    <span class="societypress-status-inactive"><?php esc_html_e( 'Inactive', 'societypress' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $counts[ $tier->id ] ?? 0 ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="description">
                <?php esc_html_e( 'Tier editing coming in a future update. For now, tiers can be modified directly in the database.', 'societypress' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page(): void {
        // Check if settings were just saved
        if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
            add_settings_error(
                'societypress_settings',
                'settings_saved',
                __( 'Settings saved.', 'societypress' ),
                'success'
            );
        }

        ?>
        <div class="wrap societypress-admin">
            <h1><?php esc_html_e( 'SocietyPress Settings', 'societypress' ); ?></h1>

            <?php settings_errors( 'societypress_settings' ); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'societypress_settings_group' );
                do_settings_sections( 'societypress-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the import page.
     */
    public function render_import_page(): void {
        $this->import->render_page();
    }

    /**
     * Save member from form submission.
     */
    private function save_member(): void {
        // Verify nonce
        if ( ! isset( $_POST['societypress_member_nonce'] ) ||
             ! wp_verify_nonce( $_POST['societypress_member_nonce'], 'societypress_member' ) ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'manage_society_members' ) ) {
            return;
        }

        $member_id = isset( $_POST['member_id'] ) ? absint( $_POST['member_id'] ) : 0;
        $members   = societypress()->members;

        // Prepare member data
        $member_data = array(
            'first_name'               => sanitize_text_field( $_POST['first_name'] ?? '' ),
            'last_name'                => sanitize_text_field( $_POST['last_name'] ?? '' ),
            'membership_tier_id'       => absint( $_POST['membership_tier_id'] ?? 0 ),
            'status'                   => sanitize_text_field( $_POST['status'] ?? 'pending' ),
            'join_date'                => sanitize_text_field( $_POST['join_date'] ?? '' ),
            'expiration_date'          => sanitize_text_field( $_POST['expiration_date'] ?? '' ) ?: null,
            'directory_visible'        => isset( $_POST['directory_visible'] ) ? 1 : 0,
            'auto_renew'               => isset( $_POST['auto_renew'] ) ? 1 : 0,
            'communication_preference' => sanitize_text_field( $_POST['communication_preference'] ?? 'email' ),
        );

        // Prepare contact data
        $contact_data = array(
            'primary_email'   => sanitize_email( $_POST['primary_email'] ?? '' ),
            'cell_phone'      => sanitize_text_field( $_POST['cell_phone'] ?? '' ),
            'street_address'  => sanitize_text_field( $_POST['street_address'] ?? '' ),
            'city'            => sanitize_text_field( $_POST['city'] ?? '' ),
            'state_province'  => sanitize_text_field( $_POST['state_province'] ?? '' ),
            'postal_code'     => sanitize_text_field( $_POST['postal_code'] ?? '' ),
            'country'         => sanitize_text_field( $_POST['country'] ?? 'USA' ),
        );

        if ( $member_id ) {
            // Update existing member
            $result = $members->update( $member_id, $member_data );
            if ( $result ) {
                $members->update_contact( $member_id, $contact_data );
                $this->add_admin_notice( __( 'Member updated successfully.', 'societypress' ), 'success' );
            } else {
                $this->add_admin_notice( __( 'Error updating member.', 'societypress' ), 'error' );
            }
        } else {
            // Create new member
            $new_id = $members->create( $member_data );
            if ( $new_id ) {
                $contact_data['member_id'] = $new_id;
                $members->update_contact( $new_id, $contact_data );
                $this->add_admin_notice( __( 'Member created successfully.', 'societypress' ), 'success' );

                // Redirect to edit page for the new member
                wp_safe_redirect( admin_url( 'admin.php?page=societypress-member-edit&member_id=' . $new_id . '&message=created' ) );
                exit;
            } else {
                $this->add_admin_notice( __( 'Error creating member.', 'societypress' ), 'error' );
            }
        }
    }

    /**
     * Save tier from form submission.
     */
    private function save_tier(): void {
        // Placeholder for future implementation
    }

    /**
     * Add an admin notice to be displayed.
     *
     * @param string $message Notice message.
     * @param string $type    Notice type (success, error, warning, info).
     */
    private function add_admin_notice( string $message, string $type = 'info' ): void {
        set_transient(
            'societypress_admin_notice_' . get_current_user_id(),
            array(
                'message' => $message,
                'type'    => $type,
            ),
            30
        );
    }

    /**
     * Render any pending admin notices.
     */
    private function render_admin_notices(): void {
        $notice = get_transient( 'societypress_admin_notice_' . get_current_user_id() );

        if ( $notice ) {
            delete_transient( 'societypress_admin_notice_' . get_current_user_id() );
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr( $notice['type'] ),
                esc_html( $notice['message'] )
            );
        }

        // Check for URL message parameter
        if ( isset( $_GET['message'] ) ) {
            $count = absint( $_GET['count'] ?? 0 );

            switch ( $_GET['message'] ) {
                case 'created':
                    echo '<div class="notice notice-success is-dismissible"><p>' .
                         esc_html__( 'Member created successfully.', 'societypress' ) .
                         '</p></div>';
                    break;

                case 'deleted':
                    echo '<div class="notice notice-success is-dismissible"><p>' .
                         esc_html__( 'Member deleted successfully.', 'societypress' ) .
                         '</p></div>';
                    break;

                case 'delete_error':
                    echo '<div class="notice notice-error is-dismissible"><p>' .
                         esc_html__( 'Error deleting member.', 'societypress' ) .
                         '</p></div>';
                    break;

                case 'not_found':
                    echo '<div class="notice notice-warning is-dismissible"><p>' .
                         esc_html__( 'Member not found.', 'societypress' ) .
                         '</p></div>';
                    break;

                case 'bulk_deleted':
                    echo '<div class="notice notice-success is-dismissible"><p>' .
                         sprintf(
                             /* translators: %d: number of members deleted */
                             esc_html( _n( '%d member deleted.', '%d members deleted.', $count, 'societypress' ) ),
                             $count
                         ) .
                         '</p></div>';
                    break;

                case 'bulk_activated':
                    echo '<div class="notice notice-success is-dismissible"><p>' .
                         sprintf(
                             /* translators: %d: number of members activated */
                             esc_html( _n( '%d member set to active.', '%d members set to active.', $count, 'societypress' ) ),
                             $count
                         ) .
                         '</p></div>';
                    break;

                case 'bulk_deactivated':
                    echo '<div class="notice notice-success is-dismissible"><p>' .
                         sprintf(
                             /* translators: %d: number of members deactivated */
                             esc_html( _n( '%d member set to expired.', '%d members set to expired.', $count, 'societypress' ) ),
                             $count
                         ) .
                         '</p></div>';
                    break;

                case 'all_deleted':
                    echo '<div class="notice notice-success is-dismissible"><p>' .
                         sprintf(
                             /* translators: %d: number of members deleted */
                             esc_html__( 'All %d members have been deleted.', 'societypress' ),
                             $count
                         ) .
                         '</p></div>';
                    break;
            }
        }
    }
}
