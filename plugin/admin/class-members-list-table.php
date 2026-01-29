<?php
/**
 * Members List Table
 *
 * Extends WP_List_Table to display members in the admin.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load WP_List_Table if not already loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class SocietyPress_Members_List_Table
 *
 * Custom list table for displaying members.
 */
class SocietyPress_Members_List_Table extends WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            array(
                'singular' => 'member',
                'plural'   => 'members',
                'ajax'     => false,
            )
        );
    }

    /**
     * Get table columns.
     *
     * @return array Column definitions.
     */
    public function get_columns(): array {
        $columns = array(
            'cb'          => '<input type="checkbox">',
        );

        // Only include photo column if photos are enabled
        if ( SocietyPress_Admin::get_setting( 'member_photos_enabled', true ) ) {
            $columns['photo'] = '';
        }

        $columns['name']       = __( 'Name', 'societypress' );
        $columns['email']      = __( 'Email', 'societypress' );
        $columns['tier']       = __( 'Tier', 'societypress' );
        $columns['status']     = __( 'Status', 'societypress' );
        $columns['join_date']  = __( 'Joined', 'societypress' );
        $columns['expiration'] = __( 'Expires', 'societypress' );

        return $columns;
    }

    /**
     * Get sortable columns.
     *
     * @return array Sortable column definitions.
     */
    public function get_sortable_columns(): array {
        return array(
            'name'       => array( 'last_name', false ),
            'tier'       => array( 'membership_tier_id', false ),
            'status'     => array( 'status', false ),
            'join_date'  => array( 'join_date', false ),
            'expiration' => array( 'expiration_date', false ),
        );
    }

    /**
     * Get bulk actions.
     *
     * @return array Bulk action definitions.
     */
    public function get_bulk_actions(): array {
        return array(
            'delete'       => __( 'Delete Selected', 'societypress' ),
            'activate'     => __( 'Set Active', 'societypress' ),
            'deactivate'   => __( 'Set Expired', 'societypress' ),
            'create_users' => __( 'Create User Accounts', 'societypress' ),
            'delete_all'   => __( 'Clear Database', 'societypress' ),
        );
    }

    /**
     * Prepare items for display.
     */
    public function prepare_items(): void {
        // Get members per page from plugin settings
        $per_page = SocietyPress_Admin::get_setting( 'members_per_page', 20 );
        $current_page = $this->get_pagenum();

        // Build query args
        $args = array(
            'limit'  => $per_page,
            'offset' => ( $current_page - 1 ) * $per_page,
        );

        // Handle search
        if ( ! empty( $_REQUEST['s'] ) ) {
            $args['search'] = sanitize_text_field( $_REQUEST['s'] );
        }

        // Handle status filter
        if ( ! empty( $_REQUEST['status'] ) ) {
            $args['status'] = sanitize_text_field( $_REQUEST['status'] );
        }

        // Handle tier filter
        if ( ! empty( $_REQUEST['tier'] ) ) {
            $args['tier_id'] = absint( $_REQUEST['tier'] );
        }

        // Handle sorting
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = sanitize_text_field( $_REQUEST['orderby'] );
            $args['order']   = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : 'ASC';
        }

        // Get members
        $members = societypress()->members;
        $this->items = $members->get_members( $args );

        // Get total count for pagination
        $total_args = $args;
        $total_args['limit'] = 0;
        $total_items = count( $members->get_members( $total_args ) );

        // Set up pagination
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total_items / $per_page ),
            )
        );

        // Set column headers
        $this->_column_headers = array(
            $this->get_columns(),
            array(), // Hidden columns
            $this->get_sortable_columns(),
        );
    }

    /**
     * Render checkbox column.
     *
     * @param object $item Current member.
     * @return string Checkbox HTML.
     */
    public function column_cb( $item ): string {
        return sprintf(
            '<input type="checkbox" name="member[]" value="%d">',
            $item->id
        );
    }

    /**
     * Render name column with row actions.
     *
     * @param object $item Current member.
     * @return string Name HTML with actions.
     */
    public function column_name( $item ): string {
        $edit_url = admin_url( 'admin.php?page=societypress-member-edit&member_id=' . $item->id );
        $delete_url = wp_nonce_url(
            admin_url( 'admin.php?page=societypress-members&action=delete&member=' . $item->id ),
            'delete_member_' . $item->id
        );

        $actions = array(
            'edit'   => sprintf(
                '<a href="%s">%s</a>',
                esc_url( $edit_url ),
                __( 'Edit', 'societypress' )
            ),
            'delete' => sprintf(
                '<a href="%s" class="societypress-delete-member" onclick="return confirm(\'%s\');">%s</a>',
                esc_url( $delete_url ),
                esc_js( __( 'Are you sure you want to delete this member?', 'societypress' ) ),
                __( 'Delete', 'societypress' )
            ),
        );

        // Build name with middle initial if present
        $full_name = $item->first_name;
        if ( ! empty( $item->middle_name ) ) {
            // If middle name is more than one character, show first letter as initial
            $middle_initial = mb_substr( $item->middle_name, 0, 1 );
            $full_name .= ' ' . $middle_initial . '.';
        }
        $full_name .= ' ' . $item->last_name;

        $name = sprintf(
            '<strong><a href="%s">%s</a></strong>',
            esc_url( $edit_url ),
            esc_html( $full_name )
        );

        return $name . $this->row_actions( $actions );
    }

    /**
     * Render photo column.
     *
     * Shows member photo as a small circle, or a placeholder with initials.
     *
     * @param object $item Current member.
     * @return string Photo HTML.
     */
    public function column_photo( $item ): string {
        if ( ! empty( $item->photo_id ) ) {
            $photo_url = wp_get_attachment_image_url( $item->photo_id, 'thumbnail' );
            if ( $photo_url ) {
                return sprintf(
                    '<img src="%s" alt="%s" class="sp-member-photo-small">',
                    esc_url( $photo_url ),
                    esc_attr( $item->first_name . ' ' . $item->last_name )
                );
            }
        }

        // Show placeholder with initials
        $initials = mb_strtoupper( mb_substr( $item->first_name, 0, 1 ) . mb_substr( $item->last_name, 0, 1 ) );
        return sprintf(
            '<span class="sp-member-photo-placeholder" title="%s">%s</span>',
            esc_attr( $item->first_name . ' ' . $item->last_name ),
            esc_html( $initials )
        );
    }

    /**
     * Render email column.
     *
     * @param object $item Current member.
     * @return string Email HTML.
     */
    public function column_email( $item ): string {
        // Get contact info
        $contact = societypress()->members->get_contact( $item->id );

        if ( $contact && ! empty( $contact->primary_email ) ) {
            return sprintf(
                '<a href="mailto:%s">%s</a>',
                esc_attr( $contact->primary_email ),
                esc_html( $contact->primary_email )
            );
        }

        return '<em>' . esc_html__( 'No email', 'societypress' ) . '</em>';
    }

    /**
     * Render tier column.
     *
     * @param object $item Current member.
     * @return string Tier name.
     */
    public function column_tier( $item ): string {
        $tier = societypress()->tiers->get( $item->membership_tier_id );
        return $tier ? esc_html( $tier->name ) : '—';
    }

    /**
     * Render status column.
     *
     * @param object $item Current member.
     * @return string Status badge HTML.
     */
    public function column_status( $item ): string {
        $status_classes = array(
            'active'    => 'societypress-status-active',
            'expired'   => 'societypress-status-expired',
            'pending'   => 'societypress-status-pending',
            'cancelled' => 'societypress-status-cancelled',
            'deceased'  => 'societypress-status-deceased',
        );

        $class = $status_classes[ $item->status ] ?? 'societypress-status-default';

        return sprintf(
            '<span class="%s">%s</span>',
            esc_attr( $class ),
            esc_html( ucfirst( $item->status ) )
        );
    }

    /**
     * Render join date column.
     *
     * Shows year only with "Member since YYYY" format.
     * Full date available on hover tooltip.
     *
     * @param object $item Current member.
     * @return string Formatted year with tooltip.
     */
    public function column_join_date( $item ): string {
        if ( empty( $item->join_date ) ) {
            return '—';
        }

        $year = date( 'Y', strtotime( $item->join_date ) );
        $full_date = date_i18n( get_option( 'date_format' ), strtotime( $item->join_date ) );

        return sprintf(
            '<span title="%s">%s</span>',
            esc_attr( $full_date ),
            esc_html( $year )
        );
    }

    /**
     * Render expiration column.
     *
     * @param object $item Current member.
     * @return string Formatted date or lifetime indicator.
     */
    public function column_expiration( $item ): string {
        if ( empty( $item->expiration_date ) ) {
            return '<em>' . esc_html__( 'Lifetime', 'societypress' ) . '</em>';
        }

        $expiration = strtotime( $item->expiration_date );
        $now        = time();
        $formatted  = date_i18n( get_option( 'date_format' ), $expiration );

        // Add visual indicator if expiring soon or expired
        if ( $expiration < $now ) {
            return '<span class="societypress-expired">' . esc_html( $formatted ) . '</span>';
        } elseif ( $expiration < strtotime( '+30 days' ) ) {
            return '<span class="societypress-expiring-soon">' . esc_html( $formatted ) . '</span>';
        }

        return esc_html( $formatted );
    }

    /**
     * Default column rendering.
     *
     * @param object $item        Current member.
     * @param string $column_name Column name.
     * @return string Column value.
     */
    public function column_default( $item, $column_name ): string {
        return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : '—';
    }

    /**
     * Message when no members found.
     */
    public function no_items(): void {
        esc_html_e( 'No members found.', 'societypress' );
    }

    /**
     * Extra table navigation (filters).
     *
     * @param string $which Top or bottom.
     */
    public function extra_tablenav( $which ): void {
        if ( 'top' !== $which ) {
            return;
        }

        $tiers = societypress()->tiers->get_all();
        $current_status = $_REQUEST['status'] ?? '';
        $current_tier   = $_REQUEST['tier'] ?? '';

        ?>
        <div class="alignleft actions">
            <select name="status">
                <option value=""><?php esc_html_e( 'All Statuses', 'societypress' ); ?></option>
                <?php foreach ( SocietyPress_Members::STATUSES as $status ) : ?>
                    <option value="<?php echo esc_attr( $status ); ?>" <?php selected( $current_status, $status ); ?>>
                        <?php echo esc_html( ucfirst( $status ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="tier">
                <option value=""><?php esc_html_e( 'All Tiers', 'societypress' ); ?></option>
                <?php foreach ( $tiers as $tier ) : ?>
                    <option value="<?php echo esc_attr( $tier->id ); ?>" <?php selected( $current_tier, $tier->id ); ?>>
                        <?php echo esc_html( $tier->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php submit_button( __( 'Filter', 'societypress' ), '', 'filter_action', false ); ?>
        </div>
        <?php
    }
}
