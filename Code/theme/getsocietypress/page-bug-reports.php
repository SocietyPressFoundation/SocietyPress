<?php
/**
 * Bug Reports Page Template (page-bug-reports.php)
 *
 * How to report a bug. GitHub Issues is the long-term home, but while the
 * repo is private we route reports through email with a clear reproduction
 * template that makes triage fast.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Pre-filled email subject and body. Percent-encoding handled by rawurlencode.
 * The body template is what the reporter starts with — they can edit anything.
 */
$gsp_bug_subject = rawurlencode( 'Bug: [short summary here]' );
$gsp_bug_body    = rawurlencode(
    "WHAT HAPPENED:\n(Tell us what went wrong in one or two sentences.)\n\n" .
    "WHAT YOU EXPECTED:\n(What should have happened instead?)\n\n" .
    "STEPS TO REPRODUCE:\n1. \n2. \n3. \n\n" .
    "SOCIETYPRESS VERSION:\n(Found at Settings > About. Example: 1.0.53)\n\n" .
    "WORDPRESS VERSION:\n(Found at Dashboard > Updates. Example: 6.7.1)\n\n" .
    "BROWSER AND OS:\n(Example: Firefox 128 on Windows 11)\n\n" .
    "ANYTHING ELSE:\n(Attach screenshots, error messages, and anything else that helps. For PHP errors, include the text from wp-content/debug.log if you have WP_DEBUG enabled.)"
);
$gsp_bug_mailto  = 'mailto:bugs@getsocietypress.org?subject=' . $gsp_bug_subject . '&body=' . $gsp_bug_body;
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Report a Bug</h1>
            <p class="page-hero__subtitle">
                Help us fix what's broken. Every good bug report makes
                SocietyPress better for every society.
            </p>
        </div>
    </div>
</section>

<section class="intake-page section">
    <div class="container container--narrow">

        <div class="intake-page__lede">
            <p>
                We read every bug report and respond to every one we can
                reproduce. Clear, specific reports get fixed fastest &mdash;
                the template below gives us the details we need to find the
                problem quickly.
            </p>
        </div>

        <div class="intake-page__cta">
            <a href="<?php echo esc_url( $gsp_bug_mailto ); ?>" class="btn btn-primary btn-xl">
                Open a pre-filled bug report
            </a>
            <p class="intake-page__cta-note">
                This opens your email app with the template already filled in.
                Just replace the bracketed sections and send.
            </p>
        </div>

        <h2>Before you file</h2>

        <p>A 60-second check that often resolves the problem:</p>

        <ul>
            <li>
                <strong>Update first.</strong> Make sure you're on the latest
                SocietyPress release (<a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">download page</a>)
                and the latest <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a>. Many bugs are already fixed in a
                newer version.
            </li>
            <li>
                <strong>Plugin conflict check.</strong> If you have other
                plugins active, temporarily deactivate them and retry. A lot
                of &ldquo;SocietyPress bugs&rdquo; turn out to be another
                plugin doing something unexpected.
            </li>
            <li>
                <strong>Theme check.</strong> Switch to the SocietyPress
                parent theme. If the bug disappears, it's a child-theme
                issue &mdash; still worth reporting, just different triage.
            </li>
            <li>
                <strong>Browser check.</strong> Try a second browser.
                Frontend issues sometimes trace back to a browser extension
                rather than the plugin.
            </li>
        </ul>

        <h2>What makes a great bug report</h2>

        <ol>
            <li>
                <strong>What you expected.</strong> One sentence.
            </li>
            <li>
                <strong>What actually happened.</strong> One sentence.
            </li>
            <li>
                <strong>How to reproduce it.</strong> Numbered steps, starting
                from a fresh page load.
            </li>
            <li>
                <strong>Your versions.</strong> SocietyPress version,
                <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> version, PHP version if you know it, browser and
                OS.
            </li>
            <li>
                <strong>Any error messages</strong> &mdash; copied as text if
                possible, screenshotted if not.
            </li>
        </ol>

        <h2>For security issues</h2>

        <p>
            Please do <strong>not</strong> file security vulnerabilities
            through this form. They go to a private address so we can patch
            before the issue is public. See our
            <a href="<?php echo esc_url( home_url( '/.well-known/security.txt' ) ); ?>">security.txt</a>
            for the responsible-disclosure path.
        </p>

        <div class="intake-page__footer">
            <p>
                Prefer a different contact path? See the
                <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">contact page</a>
                for all the ways to reach us.
            </p>
        </div>

    </div>
</section>

<?php get_footer(); ?>
