<?php
/**
 * Template Name: Feedback
 *
 * WHY: A public feedback page where users can report bugs, request features,
 *      or ask questions about SocietyPress. The form emails the submission
 *      directly — no database, no plugin, no complexity. For technical users,
 *      there's a prominent link to GitHub Issues.
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

// ---- Handle form submission ----
$feedback_sent    = false;
$feedback_error   = '';

if ( isset( $_POST['gsp_feedback_submit'] ) ) {
    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['gsp_feedback_nonce'] ?? '', 'gsp_feedback' ) ) {
        $feedback_error = __( 'Security check failed. Please try again.', 'getsocietypress' );
    } else {
        $name    = sanitize_text_field( $_POST['gsp_name'] ?? '' );
        $email   = sanitize_email( $_POST['gsp_email'] ?? '' );
        $type    = sanitize_text_field( $_POST['gsp_type'] ?? 'general' );
        $message = sanitize_textarea_field( $_POST['gsp_message'] ?? '' );

        // Validate
        if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
            $feedback_error = __( 'Please fill in all required fields.', 'getsocietypress' );
        } elseif ( ! is_email( $email ) ) {
            $feedback_error = __( 'Please enter a valid email address.', 'getsocietypress' );
        } else {
            // Rate limit — 3 submissions per hour per IP
            $ip        = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
            $cache_key = 'gsp_feedback_' . md5( $ip );
            $count     = (int) get_transient( $cache_key );

            if ( $count >= 3 ) {
                $feedback_error = __( 'Too many submissions. Please try again later.', 'getsocietypress' );
            } else {
                // Build email
                $type_labels = [
                    'bug'     => 'Bug Report',
                    'feature' => 'Feature Request',
                    'question' => 'Question',
                    'general' => 'General Feedback',
                ];
                $type_label = $type_labels[ $type ] ?? 'General Feedback';

                $to      = get_option( 'admin_email' );
                $subject = '[SocietyPress Feedback] ' . $type_label . ' from ' . $name;
                $body    = "Type: {$type_label}\n";
                $body   .= "Name: {$name}\n";
                $body   .= "Email: {$email}\n";
                $body   .= "---\n\n";
                $body   .= $message;

                $headers = [
                    'Content-Type: text/plain; charset=UTF-8',
                    // Strip CRLF (header injection) and < > (can't break out of Name <email>).
                    'Reply-To: ' . str_replace( [ "\r", "\n", '<', '>' ], '', $name ) . ' <' . $email . '>',
                ];

                $sent = wp_mail( $to, $subject, $body, $headers );

                if ( $sent ) {
                    $feedback_sent = true;
                    set_transient( $cache_key, $count + 1, HOUR_IN_SECONDS );
                } else {
                    $feedback_error = __( 'Something went wrong sending your message. Please try emailing us directly.', 'getsocietypress' );
                }
            }
        }
    }
}

get_header();
?>

<div class="page-header">
    <div class="container">
        <h1>Feedback</h1>
    </div>
</div>

<div class="page-content">
    <div class="container">

        <div class="feedback-layout">

            <!-- Main form column -->
            <div class="feedback-main">

                <?php if ( $feedback_sent ) : ?>

                    <div class="feedback-success" role="status">
                        <h2>Thank you!</h2>
                        <p>
                            Your feedback has been sent. We read every submission.
                            If you included a question, we'll reply to the email address you provided.
                        </p>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
                            Back to Home
                        </a>
                    </div>

                <?php else : ?>

                    <p class="feedback-intro">
                        Found a bug? Have a feature idea? Just want to tell us what you think?
                        We'd love to hear from you. Every submission is read by a real person.
                    </p>

                    <?php if ( $feedback_error ) : ?>
                        <div class="feedback-error" role="alert">
                            <?php echo esc_html( $feedback_error ); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="feedback-form">
                        <?php wp_nonce_field( 'gsp_feedback', 'gsp_feedback_nonce' ); ?>

                        <div class="feedback-field">
                            <label for="gsp_name">Your Name <span class="required">*</span></label>
                            <input type="text" id="gsp_name" name="gsp_name"
                                   value="<?php echo esc_attr( $_POST['gsp_name'] ?? '' ); ?>"
                                   required>
                        </div>

                        <div class="feedback-field">
                            <label for="gsp_email">Email Address <span class="required">*</span></label>
                            <input type="email" id="gsp_email" name="gsp_email"
                                   value="<?php echo esc_attr( $_POST['gsp_email'] ?? '' ); ?>"
                                   required>
                        </div>

                        <div class="feedback-field">
                            <label for="gsp_type">What kind of feedback?</label>
                            <select id="gsp_type" name="gsp_type">
                                <option value="general" <?php selected( $_POST['gsp_type'] ?? '', 'general' ); ?>>General Feedback</option>
                                <option value="bug" <?php selected( $_POST['gsp_type'] ?? '', 'bug' ); ?>>Bug Report</option>
                                <option value="feature" <?php selected( $_POST['gsp_type'] ?? '', 'feature' ); ?>>Feature Request</option>
                                <option value="question" <?php selected( $_POST['gsp_type'] ?? '', 'question' ); ?>>Question</option>
                            </select>
                        </div>

                        <div class="feedback-field">
                            <label for="gsp_message">Your Message <span class="required">*</span></label>
                            <textarea id="gsp_message" name="gsp_message" rows="8"
                                      required><?php echo esc_textarea( $_POST['gsp_message'] ?? '' ); ?></textarea>
                        </div>

                        <button type="submit" name="gsp_feedback_submit" class="btn btn-primary btn-lg">
                            Send Feedback
                        </button>
                    </form>

                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <div class="feedback-sidebar">

                <div class="feedback-sidebar-card">
                    <h3>GitHub Issues</h3>
                    <p>
                        If you're technical, you can also file bugs and feature
                        requests directly on GitHub.
                    </p>
                    <a href="https://github.com/SocietyPressFoundation/SocietyPress/issues"
                       class="btn btn-outline" target="_blank" rel="noopener">
                        Open an Issue
                    </a>
                </div>

                <div class="feedback-sidebar-card">
                    <h3>What Helps Us Most</h3>
                    <ul>
                        <li>What you were trying to do</li>
                        <li>What actually happened</li>
                        <li>Your browser and device</li>
                        <li>Screenshots, if you have them</li>
                    </ul>
                </div>

            </div>

        </div>

    </div>
</div>

<?php get_footer(); ?>
