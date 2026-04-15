<?php
/**
 * Security Policy Page Template (page-security-policy.php)
 *
 * Linked from security.txt's Policy: field. Explains in plain terms how
 * we handle security disclosures — what's in scope, what to send, what
 * we promise in return, what we won't do.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Security Policy</h1>
            <p class="page-hero__subtitle">
                How we handle security vulnerabilities in SocietyPress and
                what security researchers can expect from us.
            </p>
        </div>
    </div>
</section>

<section class="legal-page section">
    <div class="container container--narrow">

        <p class="legal-page__updated">
            <strong>Last updated:</strong> April 15, 2026<br>
            <strong>Machine-readable version:</strong>
            <a href="<?php echo esc_url( home_url( '/.well-known/security.txt' ) ); ?>">/.well-known/security.txt</a>
        </p>

        <h2>Reporting a vulnerability</h2>

        <p>
            Email <a href="mailto:security@getsocietypress.org">security@getsocietypress.org</a>
            with details. Please do <strong>not</strong> file security
            issues through the public bug-reports page &mdash; we need time
            to patch before details are public, and a public issue is a
            head start for anyone malicious.
        </p>

        <p>
            A good report includes:
        </p>

        <ul>
            <li>The SocietyPress version you tested against.</li>
            <li>Steps to reproduce the issue, including any specific configuration required.</li>
            <li>What an attacker could do with this vulnerability.</li>
            <li>Your assessment of severity, if you want to share one (we'll do our own too).</li>
            <li>Whether you'd like to be credited publicly, and if so, how.</li>
        </ul>

        <h2>What we commit to</h2>

        <ul>
            <li><strong>Acknowledge receipt within 3 business days.</strong> If you don't hear from us, assume the email got lost and send a nudge.</li>
            <li><strong>Initial assessment within 7 business days</strong> &mdash; whether we've reproduced it, our severity rating, and a rough timeline.</li>
            <li><strong>Regular progress updates</strong> every 7 days until the issue is resolved.</li>
            <li><strong>Credit in the release notes</strong> if you'd like to be credited. Anonymous reports are also fine &mdash; we respect that preference.</li>
            <li><strong>No legal action against good-faith research.</strong> If you're testing within the scope below and you don't deliberately harm users or data, we're grateful, not litigious.</li>
        </ul>

        <h2>In scope</h2>

        <ul>
            <li>The SocietyPress WordPress plugin (any release since 1.0.0).</li>
            <li>The SocietyPress parent theme.</li>
            <li>The five bundled child themes (Heritage, Coastline, Prairie, Ledger, Parlor).</li>
            <li>The one-click installer (<code>sp-installer.php</code>).</li>
            <li>The getsocietypress.org marketing website itself.</li>
            <li>The demo site at <code>demo.getsocietypress.org</code>.</li>
        </ul>

        <h2>Out of scope</h2>

        <ul>
            <li>Individual society installations we do not operate. If you've found a vulnerability on a specific society's website, please report it directly to that society. If you believe the root cause is in SocietyPress itself, report it to us with a generic reproduction.</li>
            <li>Third-party WordPress plugins installed alongside SocietyPress. Report those to their respective maintainers.</li>
            <li>Vulnerabilities in WordPress core. Report to the WordPress Security Team at <a href="https://wordpress.org/support/wordpress-hackerone/">wordpress.org/support/wordpress-hackerone/</a>.</li>
            <li>Vulnerabilities in hosting provider infrastructure. Report to the host.</li>
            <li>Stripe and PayPal payment processor implementations. Report to them directly.</li>
            <li>Missing security headers that don't meaningfully change the attack surface (e.g., HSTS, CSP nitpicks). We read these reports but they rarely warrant CVEs.</li>
        </ul>

        <h2>What qualifies as a vulnerability</h2>

        <p>The usual suspects &mdash; roughly in order of how much they concern us:</p>

        <ul>
            <li>Remote code execution</li>
            <li>SQL injection</li>
            <li>Authentication bypass or privilege escalation</li>
            <li>Stored XSS</li>
            <li>CSRF on state-changing actions</li>
            <li>Insecure direct object references (IDOR) exposing member data</li>
            <li>Zip-slip, path traversal, or file inclusion</li>
            <li>Server-side request forgery (SSRF)</li>
            <li>Cryptographic weaknesses (encryption at rest uses libsodium's XChaCha20-Poly1305; flaws there are high-priority)</li>
            <li>Information disclosure of PII or credentials</li>
        </ul>

        <h2>What doesn't qualify</h2>

        <ul>
            <li>Self-XSS that requires the user to paste something into the address bar.</li>
            <li>Denial-of-service via obviously resource-intensive actions on your own site. (Yes, you can hammer your own installation. No, that's not a vulnerability.)</li>
            <li>Missing rate-limiting on actions an admin can already do via the REST API.</li>
            <li>Social engineering of project maintainers.</li>
            <li>Reports generated solely by automated scanners without evidence of exploitability.</li>
            <li>Vulnerabilities that require the admin to be running an EOL version of WordPress or PHP.</li>
        </ul>

        <h2>Disclosure timing</h2>

        <p>
            Our preference is <strong>coordinated disclosure</strong>: we
            patch, push an update, notify society administrators, and then
            publish details. For critical issues, expect 7&ndash;14 days
            from patch to public disclosure. For lower-severity issues,
            30 days is typical.
        </p>

        <p>
            If 90 days pass without a fix or a clear plan, you are free to
            disclose publicly. We'd rather that never happen &mdash; if
            we're struggling to reproduce or prioritize, tell us and we'll
            escalate.
        </p>

        <h2>Safe harbor</h2>

        <p>
            Research that complies with this policy is authorized, and we
            will not pursue legal claims against researchers acting in good
            faith. Specifically, you are authorized to:
        </p>

        <ul>
            <li>Run security tests on your own SocietyPress installation.</li>
            <li>Run security tests on the demo site at <code>demo.getsocietypress.org</code> &mdash; but please reset it after (there's a reset button) and avoid actions that could affect other testers.</li>
            <li>Inspect the source code, which is available under the GPL.</li>
        </ul>

        <p>
            You are <strong>not</strong> authorized to:
        </p>

        <ul>
            <li>Access member data on a society's production installation without their explicit permission.</li>
            <li>Run denial-of-service attacks against our infrastructure.</li>
            <li>Perform social engineering against project maintainers or society administrators.</li>
        </ul>

        <h2>Bug bounty</h2>

        <p>
            SocietyPress is a free, volunteer-driven project with no
            revenue. We cannot offer monetary bounties. We can offer
            prominent credit in release notes, a permanent listing on the
            <a href="<?php echo esc_url( home_url( '/sponsors/' ) ); ?>">contributors page</a>,
            and the warm regard of every society whose data you helped
            protect &mdash; which is not nothing.
        </p>

    </div>
</section>

<?php get_footer(); ?>
