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
        $feedback_error = 'Security check failed. Please try again.';
    } else {
        $name    = sanitize_text_field( $_POST['gsp_name'] ?? '' );
        $email   = sanitize_email( $_POST['gsp_email'] ?? '' );
        $type    = sanitize_text_field( $_POST['gsp_type'] ?? 'general' );
        $message = sanitize_textarea_field( $_POST['gsp_message'] ?? '' );

        // Validate
        if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
            $feedback_error = 'Please fill in all required fields.';
        } elseif ( ! is_email( $email ) ) {
            $feedback_error = 'Please enter a valid email address.';
        } else {
            // Rate limit — 3 submissions per hour per IP
            $ip        = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );
            $cache_key = 'gsp_feedback_' . md5( $ip );
            $count     = (int) get_transient( $cache_key );

            if ( $count >= 3 ) {
                $feedback_error = 'Too many submissions. Please try again later.';
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
                    'Reply-To: ' . str_replace( [ "\r", "\n" ], '', $name ) . ' <' . $email . '>',
                ];

                $sent = wp_mail( $to, $subject, $body, $headers );

                if ( $sent ) {
                    $feedback_sent = true;
                    set_transient( $cache_key, $count + 1, HOUR_IN_SECONDS );
                } else {
                    $feedback_error = 'Something went wrong sending your message. Please try emailing us directly.';
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

                    <div class="feedback-success">
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
                        <div class="feedback-error">
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
                    <a href="https://github.com/charles-stricklin/SocietyPress/issues"
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

<style>
    /* ---- Feedback Page Styles ---- */
    .feedback-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 48px;
        max-width: 960px;
        margin: 0 auto;
    }

    .feedback-intro {
        font-size: var(--font-size-lg);
        color: var(--color-text-secondary);
        margin-bottom: 32px;
    }

    .feedback-form {
        max-width: 100%;
    }

    .feedback-field {
        margin-bottom: 24px;
    }

    .feedback-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--color-text);
        font-size: var(--font-size-base);
    }

    .feedback-field .required {
        color: #d63638;
    }

    .feedback-field input,
    .feedback-field select,
    .feedback-field textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--color-border);
        border-radius: 6px;
        font-size: var(--font-size-base);
        font-family: var(--font-family);
        color: var(--color-text);
        background: #fff;
        transition: border-color 0.2s;
    }

    .feedback-field input:focus,
    .feedback-field select:focus,
    .feedback-field textarea:focus {
        outline: none;
        border-color: var(--color-accent);
        box-shadow: 0 0 0 3px rgba(201, 151, 58, 0.15);
    }

    .feedback-field textarea {
        resize: vertical;
        min-height: 160px;
    }

    .feedback-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 24px;
        font-weight: 500;
    }

    .feedback-success {
        text-align: center;
        padding: 60px 20px;
    }

    .feedback-success h2 {
        color: var(--color-primary);
        margin-bottom: 12px;
    }

    .feedback-success p {
        color: var(--color-text-secondary);
        font-size: var(--font-size-lg);
        margin-bottom: 32px;
        max-width: 480px;
        margin-left: auto;
        margin-right: auto;
    }

    .feedback-sidebar-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
    }

    .feedback-sidebar-card h3 {
        font-size: var(--font-size-lg);
        margin-bottom: 8px;
        color: var(--color-primary);
    }

    .feedback-sidebar-card p {
        color: var(--color-text-secondary);
        font-size: var(--font-size-sm);
        margin-bottom: 16px;
    }

    .feedback-sidebar-card ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .feedback-sidebar-card li {
        padding: 6px 0;
        font-size: var(--font-size-sm);
        color: var(--color-text-secondary);
        border-bottom: 1px solid var(--color-border);
    }

    .feedback-sidebar-card li:last-child {
        border-bottom: none;
    }

    .feedback-sidebar-card li::before {
        content: '\2022';
        color: var(--color-accent);
        font-weight: 700;
        margin-right: 8px;
    }

    @media (max-width: 768px) {
        .feedback-layout {
            grid-template-columns: 1fr;
            gap: 32px;
        }
    }
</style>

<?php get_footer(); ?>
