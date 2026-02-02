<?php
/**
 * Volunteer Opportunities Frontend
 *
 * Handles the public-facing volunteer opportunities browsing and signup
 * interface for members.
 *
 * WHY: Makes it easy for members to find and sign up for volunteer work.
 *      Committee chairs can post needs, members can browse and volunteer.
 *      Keeps the society running with engaged volunteers.
 *
 * @package SocietyPress
 * @since 0.54d
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Volunteer_Frontend
 *
 * Frontend rendering and AJAX handlers for volunteer opportunities.
 */
class SocietyPress_Volunteer_Frontend {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks(): void {
        // Shortcode for displaying opportunities
        add_shortcode( 'societypress_volunteer_opportunities', array( $this, 'render_opportunities' ) );

        // AJAX handlers
        add_action( 'wp_ajax_societypress_volunteer_signup', array( $this, 'ajax_signup' ) );
        add_action( 'wp_ajax_societypress_volunteer_cancel', array( $this, 'ajax_cancel' ) );

        // Enqueue assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue frontend assets.
     *
     * WHY: Only loads on pages that have the shortcode to minimize bloat.
     */
    public function enqueue_assets(): void {
        global $post;

        // Only load on pages with our shortcode or when AJAX
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'societypress_volunteer_opportunities' ) ) {
            return;
        }

        wp_enqueue_style(
            'societypress-volunteer',
            SOCIETYPRESS_URL . 'assets/css/volunteer.css',
            array(),
            SOCIETYPRESS_VERSION
        );

        wp_enqueue_script(
            'societypress-volunteer',
            SOCIETYPRESS_URL . 'assets/js/volunteer.js',
            array( 'jquery' ),
            SOCIETYPRESS_VERSION,
            true
        );

        wp_localize_script(
            'societypress-volunteer',
            'societypressVolunteer',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'societypress_volunteer' ),
                'strings' => array(
                    'signing_up'    => __( 'Signing up...', 'societypress' ),
                    'cancelling'    => __( 'Cancelling...', 'societypress' ),
                    'error'         => __( 'An error occurred. Please try again.', 'societypress' ),
                    'confirm_cancel' => __( 'Are you sure you want to cancel your signup?', 'societypress' ),
                ),
            )
        );
    }

    /**
     * Render the volunteer opportunities shortcode.
     *
     * Displays a browsable list of open volunteer opportunities with filters.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_opportunities( $atts ): string {
        $atts = shortcode_atts(
            array(
                'committee'   => '',       // Filter by committee slug
                'type'        => '',       // Filter by type (one_time, recurring, ongoing)
                'show_filled' => 'no',     // Show filled opportunities
                'limit'       => 0,        // Max to show (0 = unlimited)
            ),
            $atts,
            'societypress_volunteer_opportunities'
        );

        // Build filters
        $filters = array(
            'status'      => $atts['show_filled'] === 'yes' ? array( 'open', 'filled' ) : 'open',
            'active_only' => true,
            'upcoming'    => true,
        );

        if ( ! empty( $atts['type'] ) ) {
            $filters['type'] = sanitize_text_field( $atts['type'] );
        }

        // Get committee by slug if specified
        if ( ! empty( $atts['committee'] ) ) {
            if ( $atts['committee'] === 'society' ) {
                $filters['committee_id'] = null;
            } else {
                $committee = societypress()->committees->get_by_slug( sanitize_text_field( $atts['committee'] ) );
                if ( $committee ) {
                    $filters['committee_id'] = $committee['id'];
                }
            }
        }

        $opportunities = societypress()->volunteer_opportunities->get_all( $filters );

        // Apply limit
        if ( $atts['limit'] > 0 ) {
            $opportunities = array_slice( $opportunities, 0, absint( $atts['limit'] ) );
        }

        // Get current member if logged in
        $member_id = $this->get_current_member_id();

        ob_start();
        ?>
        <div class="sp-volunteer-opportunities">
            <?php echo $this->render_filters(); ?>

            <?php if ( empty( $opportunities ) ) : ?>
                <div class="sp-volunteer-empty">
                    <p><?php esc_html_e( 'No volunteer opportunities available at this time.', 'societypress' ); ?></p>
                    <p><?php esc_html_e( 'Check back later or contact us to learn how you can help.', 'societypress' ); ?></p>
                </div>
            <?php else : ?>
                <div class="sp-volunteer-list">
                    <?php foreach ( $opportunities as $opp ) : ?>
                        <?php echo $this->render_opportunity_card( $opp, $member_id ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render filter UI for opportunities.
     *
     * WHY: Lets members filter by committee or type to find relevant opportunities.
     *
     * @return string HTML output.
     */
    private function render_filters(): string {
        $committees = societypress()->committees->get_all( true );

        // Only show filters if there are multiple committees
        if ( count( $committees ) < 2 ) {
            return '';
        }

        ob_start();
        ?>
        <div class="sp-volunteer-filters">
            <label for="sp-volunteer-filter-committee">
                <?php esc_html_e( 'Filter by Committee:', 'societypress' ); ?>
            </label>
            <select id="sp-volunteer-filter-committee" class="sp-volunteer-filter">
                <option value=""><?php esc_html_e( 'All Committees', 'societypress' ); ?></option>
                <option value="society"><?php esc_html_e( 'Society-Wide', 'societypress' ); ?></option>
                <?php foreach ( $committees as $committee ) : ?>
                    <option value="<?php echo esc_attr( $committee['slug'] ); ?>">
                        <?php echo esc_html( $committee['name'] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="sp-volunteer-filter-type">
                <?php esc_html_e( 'Type:', 'societypress' ); ?>
            </label>
            <select id="sp-volunteer-filter-type" class="sp-volunteer-filter">
                <option value=""><?php esc_html_e( 'All Types', 'societypress' ); ?></option>
                <option value="one_time"><?php esc_html_e( 'One-Time', 'societypress' ); ?></option>
                <option value="recurring"><?php esc_html_e( 'Recurring', 'societypress' ); ?></option>
                <option value="ongoing"><?php esc_html_e( 'Ongoing', 'societypress' ); ?></option>
            </select>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render a single opportunity card.
     *
     * WHY: Card-based design makes it easy to scan opportunities quickly.
     *      Shows key info at a glance with action button.
     *
     * @param array    $opportunity Opportunity data.
     * @param int|null $member_id   Current member ID or null.
     * @return string HTML output.
     */
    private function render_opportunity_card( array $opportunity, ?int $member_id ): string {
        $opportunities_class = societypress()->volunteer_opportunities;
        $signups_class       = societypress()->volunteer_signups;

        $schedule   = $opportunities_class->format_schedule( $opportunity );
        $type_label = SocietyPress_Volunteer_Opportunities::get_type_label( $opportunity['opportunity_type'] );
        $status     = $opportunity['status'];

        // Calculate capacity info
        $capacity_text   = '';
        $spots_left      = null;
        $signup_count    = $opportunities_class->get_signup_count( $opportunity['id'] );

        if ( $opportunity['capacity'] ) {
            $spots_left    = $opportunities_class->get_remaining_capacity( $opportunity['id'] );
            $capacity_text = sprintf(
                /* translators: %1$d: spots taken, %2$d: total capacity */
                __( '%1$d / %2$d spots filled', 'societypress' ),
                $signup_count,
                $opportunity['capacity']
            );
        } elseif ( $signup_count > 0 ) {
            $capacity_text = sprintf(
                /* translators: %d: number of volunteers */
                _n( '%d volunteer signed up', '%d volunteers signed up', $signup_count, 'societypress' ),
                $signup_count
            );
        }

        // Check member's signup status
        $member_signup_status = null;
        $signup_id            = null;
        if ( $member_id ) {
            $signup = $signups_class->get_signup( $opportunity['id'], $member_id );
            if ( $signup && ! in_array( $signup['status'], array( 'cancelled', 'completed', 'no_show' ), true ) ) {
                $member_signup_status = $signup['status'];
                $signup_id            = $signup['id'];
            }
        }

        ob_start();
        ?>
        <article class="sp-volunteer-card sp-volunteer-status-<?php echo esc_attr( $status ); ?>"
                 data-committee="<?php echo esc_attr( $opportunity['committee_slug'] ?? 'society' ); ?>"
                 data-type="<?php echo esc_attr( $opportunity['opportunity_type'] ); ?>"
                 data-opportunity-id="<?php echo esc_attr( $opportunity['id'] ); ?>">

            <header class="sp-volunteer-card-header">
                <h3 class="sp-volunteer-title"><?php echo esc_html( $opportunity['title'] ); ?></h3>
                <span class="sp-volunteer-type-badge sp-type-<?php echo esc_attr( $opportunity['opportunity_type'] ); ?>">
                    <?php echo esc_html( $type_label ); ?>
                </span>
            </header>

            <div class="sp-volunteer-card-body">
                <?php if ( ! empty( $opportunity['committee_name'] ) ) : ?>
                    <p class="sp-volunteer-committee">
                        <strong><?php esc_html_e( 'Committee:', 'societypress' ); ?></strong>
                        <?php echo esc_html( $opportunity['committee_name'] ); ?>
                    </p>
                <?php endif; ?>

                <?php if ( $schedule ) : ?>
                    <p class="sp-volunteer-schedule">
                        <strong><?php esc_html_e( 'When:', 'societypress' ); ?></strong>
                        <?php echo esc_html( $schedule ); ?>
                    </p>
                <?php endif; ?>

                <?php if ( ! empty( $opportunity['location'] ) ) : ?>
                    <p class="sp-volunteer-location">
                        <strong><?php esc_html_e( 'Where:', 'societypress' ); ?></strong>
                        <?php echo esc_html( $opportunity['location'] ); ?>
                    </p>
                <?php endif; ?>

                <?php if ( ! empty( $opportunity['description'] ) ) : ?>
                    <div class="sp-volunteer-description">
                        <?php echo wp_kses_post( wpautop( $opportunity['description'] ) ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $opportunity['skills_needed'] ) ) : ?>
                    <p class="sp-volunteer-skills">
                        <strong><?php esc_html_e( 'Skills/Requirements:', 'societypress' ); ?></strong>
                        <?php echo esc_html( $opportunity['skills_needed'] ); ?>
                    </p>
                <?php endif; ?>

                <?php if ( $capacity_text ) : ?>
                    <p class="sp-volunteer-capacity <?php echo $spots_left === 0 ? 'sp-capacity-full' : ''; ?>">
                        <?php echo esc_html( $capacity_text ); ?>
                    </p>
                <?php endif; ?>
            </div>

            <footer class="sp-volunteer-card-footer">
                <?php echo $this->render_action_button( $opportunity, $member_id, $member_signup_status, $signup_id ); ?>

                <?php if ( ! empty( $opportunity['contact_first_name'] ) ) : ?>
                    <p class="sp-volunteer-contact">
                        <?php
                        printf(
                            /* translators: %s: contact person name */
                            esc_html__( 'Contact: %s', 'societypress' ),
                            esc_html( $opportunity['contact_first_name'] . ' ' . $opportunity['contact_last_name'] )
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </footer>
        </article>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the action button for an opportunity.
     *
     * WHY: Shows context-appropriate actions based on member status and capacity.
     *
     * @param array    $opportunity         Opportunity data.
     * @param int|null $member_id           Current member ID.
     * @param string|null $member_signup_status Member's signup status or null.
     * @param int|null $signup_id           Member's signup ID if signed up.
     * @return string HTML output.
     */
    private function render_action_button( array $opportunity, ?int $member_id, ?string $member_signup_status, ?int $signup_id ): string {
        // Not logged in
        if ( ! is_user_logged_in() ) {
            return '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="sp-volunteer-btn sp-btn-login">' .
                   esc_html__( 'Log in to volunteer', 'societypress' ) .
                   '</a>';
        }

        // Logged in but not a member
        if ( ! $member_id ) {
            return '<span class="sp-volunteer-notice">' .
                   esc_html__( 'Volunteering is for members only', 'societypress' ) .
                   '</span>';
        }

        // Opportunity closed or cancelled
        if ( ! in_array( $opportunity['status'], array( 'open', 'filled' ), true ) ) {
            return '<span class="sp-volunteer-notice sp-notice-closed">' .
                   esc_html__( 'This opportunity is no longer available', 'societypress' ) .
                   '</span>';
        }

        // Already signed up - show status and cancel option
        if ( $member_signup_status ) {
            $signups = societypress()->volunteer_signups;

            $status_label = SocietyPress_Volunteer_Signups::get_status_label( $member_signup_status );

            ob_start();
            ?>
            <div class="sp-volunteer-signed-up">
                <span class="sp-signup-status sp-status-<?php echo esc_attr( $member_signup_status ); ?>">
                    <?php echo esc_html( $status_label ); ?>
                    <?php if ( $member_signup_status === 'waitlist' ) : ?>
                        <?php
                        $position = $signups->get_waitlist_position( $signup_id );
                        if ( $position ) {
                            printf( ' (#%d)', $position );
                        }
                        ?>
                    <?php endif; ?>
                </span>
                <button type="button"
                        class="sp-volunteer-btn sp-btn-cancel"
                        data-signup-id="<?php echo esc_attr( $signup_id ); ?>">
                    <?php esc_html_e( 'Cancel', 'societypress' ); ?>
                </button>
            </div>
            <?php
            return ob_get_clean();
        }

        // Not signed up - check capacity
        $has_capacity = societypress()->volunteer_opportunities->has_capacity( $opportunity['id'] );

        if ( $has_capacity ) {
            return '<button type="button" class="sp-volunteer-btn sp-btn-signup" data-opportunity-id="' .
                   esc_attr( $opportunity['id'] ) . '">' .
                   esc_html__( 'Sign Up', 'societypress' ) .
                   '</button>';
        } else {
            // Full - offer waitlist
            return '<button type="button" class="sp-volunteer-btn sp-btn-waitlist" data-opportunity-id="' .
                   esc_attr( $opportunity['id'] ) . '">' .
                   esc_html__( 'Join Waitlist', 'societypress' ) .
                   '</button>';
        }
    }

    /**
     * Handle AJAX signup request.
     */
    public function ajax_signup(): void {
        check_ajax_referer( 'societypress_volunteer', 'nonce' );

        $opportunity_id = isset( $_POST['opportunity_id'] ) ? absint( $_POST['opportunity_id'] ) : 0;

        if ( ! $opportunity_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid opportunity.', 'societypress' ) ) );
        }

        $member_id = $this->get_current_member_id();

        if ( ! $member_id ) {
            wp_send_json_error( array( 'message' => __( 'You must be a member to volunteer.', 'societypress' ) ) );
        }

        $result = societypress()->volunteer_signups->signup( $opportunity_id, $member_id );

        if ( $result['success'] ) {
            wp_send_json_success( array(
                'message'   => $result['message'],
                'status'    => $result['status'],
                'signup_id' => $result['signup_id'] ?? null,
            ) );
        } else {
            wp_send_json_error( array( 'message' => $result['message'] ) );
        }
    }

    /**
     * Handle AJAX cancel request.
     */
    public function ajax_cancel(): void {
        check_ajax_referer( 'societypress_volunteer', 'nonce' );

        $signup_id = isset( $_POST['signup_id'] ) ? absint( $_POST['signup_id'] ) : 0;

        if ( ! $signup_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid signup.', 'societypress' ) ) );
        }

        $member_id = $this->get_current_member_id();

        if ( ! $member_id ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'societypress' ) ) );
        }

        // Verify the signup belongs to this member
        $signup = societypress()->volunteer_signups->get( $signup_id );

        if ( ! $signup || (int) $signup['member_id'] !== $member_id ) {
            wp_send_json_error( array( 'message' => __( 'You cannot cancel this signup.', 'societypress' ) ) );
        }

        $result = societypress()->volunteer_signups->cancel( $signup_id, __( 'Cancelled by member', 'societypress' ) );

        if ( $result['success'] ) {
            wp_send_json_success( array( 'message' => $result['message'] ) );
        } else {
            wp_send_json_error( array( 'message' => $result['message'] ) );
        }
    }

    /**
     * Get the current user's member ID.
     *
     * @return int|null Member ID or null if not a member.
     */
    private function get_current_member_id(): ?int {
        if ( ! is_user_logged_in() ) {
            return null;
        }

        $user_id = get_current_user_id();
        $member  = societypress()->members->get_by_user_id( $user_id );

        return $member ? (int) $member->id : null;
    }
}
