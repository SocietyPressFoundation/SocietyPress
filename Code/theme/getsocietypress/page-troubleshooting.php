<?php
/**
 * Troubleshooting Guide Page Template (page-troubleshooting.php)
 *
 * A stable, evergreen reference for the issues society administrators
 * actually hit in production. Organized by symptom — readers come here
 * with a specific problem, not a desire to browse. Each issue has:
 * what you're seeing, what's usually causing it, and the fix.
 *
 * Lives at /docs/troubleshooting/ as a child of /docs/.
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
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation</a>
                <span aria-hidden="true">&rsaquo;</span>
                <span>Troubleshooting</span>
            </nav>
            <h1 class="page-hero__title">When things go wrong</h1>
            <p class="page-hero__subtitle">
                The ten issues society administrators hit most, with
                honest diagnoses and the fixes that actually work.
            </p>
        </div>
    </div>
</section>

<section class="guide-page section">
    <div class="container container--narrow">

        <article class="guide-page__content">

            <h2>A 60-second flowchart before you dive in</h2>
            <p>
                Most problems resolve in under a minute if you do four
                things first, in this order. Do these before you panic,
                search the forum, or email support.
            </p>
            <ol>
                <li><strong>Check your SocietyPress version is current.</strong> <code>Settings &rarr; About</code> shows your version; <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>">the download page</a> shows the latest. A lot of &ldquo;bugs&rdquo; are already fixed in a release you haven't installed yet.</li>
                <li><strong>Check your WordPress version is current.</strong> <code>Dashboard &rarr; Updates</code>. Same reasoning.</li>
                <li><strong>Deactivate other plugins one at a time</strong> and retry. A surprising number of issues trace to a third-party plugin, not to SocietyPress.</li>
                <li><strong>Switch to the parent SocietyPress theme</strong> temporarily. If the problem disappears, it's a child-theme customization issue.</li>
            </ol>
            <p>
                If none of those helps, one of the issues below probably matches.
            </p>

            <hr>

            <h2>1. &ldquo;The installer stops with a timeout error&rdquo;</h2>

            <p><strong>What you're seeing:</strong> <code>sp-installer.php</code> hangs at &ldquo;Downloading WordPress&hellip;&rdquo; or &ldquo;Extracting SocietyPress&hellip;&rdquo; and eventually shows a 504 or blank page.</p>

            <p><strong>Why:</strong> Your host's PHP <code>max_execution_time</code> is set to 30 seconds. The installer needs to download WordPress core (~20 MB) and the SocietyPress bundle (~8 MB) and extract both &mdash; on a slow connection that overshoots 30 seconds.</p>

            <p><strong>Fix:</strong> Ask your host's support to raise <code>max_execution_time</code> to 300 seconds. Or use the manual install path: download the .zip from the download page, unzip locally, upload plugin and theme folders through the WordPress admin.</p>

            <hr>

            <h2>2. &ldquo;White screen of death after activating SocietyPress&rdquo;</h2>

            <p><strong>What you're seeing:</strong> The plugin activates successfully, then the admin (and sometimes the public site) shows a blank white page. No error message.</p>

            <p><strong>Why:</strong> Almost always a PHP memory limit. SocietyPress is a big plugin; 128 MB of PHP memory is the documented minimum but some shared hosts default to 64 MB.</p>

            <p><strong>Fix:</strong> Add this line to <code>wp-config.php</code> just before the &ldquo;That's all, stop editing!&rdquo; comment:</p>
            <pre><code>define( 'WP_MEMORY_LIMIT', '256M' );</code></pre>
            <p>If the white screen persists, turn on WordPress's debug log and check it:</p>
            <pre><code>define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );</code></pre>
            <p>The log lives at <code>wp-content/debug.log</code>. The first error line tells you what's actually failing.</p>

            <hr>

            <h2>3. &ldquo;CSV import says &lsquo;file couldn't be read&rsquo;&rdquo;</h2>

            <p><strong>What you're seeing:</strong> You upload your member CSV to Members &rarr; Import and get an error before the preview screen appears.</p>

            <p><strong>Why:</strong> CSV files from Excel on older versions of Windows are saved in UTF-8-with-BOM or Windows-1252 encoding. SocietyPress expects plain UTF-8.</p>

            <p><strong>Fix:</strong> Open the CSV in Excel, choose <strong>File &rarr; Save As</strong>, and pick <strong>CSV UTF-8 (Comma delimited)</strong> as the format. On macOS Numbers, the default export is already UTF-8 and this doesn't happen. On LibreOffice, check &ldquo;Keep current format&rdquo; and make sure UTF-8 is selected.</p>

            <hr>

            <h2>4. &ldquo;Members imported but the tiers are wrong&rdquo;</h2>

            <p><strong>What you're seeing:</strong> Members came across from the CSV, but every member is on the default tier instead of their actual membership level.</p>

            <p><strong>Why:</strong> The tier names in your CSV don't match the tier names you set up in the SocietyPress setup wizard. The importer matches tier by <em>name</em>, not by some internal ID.</p>

            <p><strong>Fix:</strong> Two options:</p>
            <ol>
                <li><strong>Rename tiers in SocietyPress</strong> to match your CSV exactly. Go to <code>Settings &rarr; Membership</code> and adjust the tier names.</li>
                <li><strong>Edit the CSV</strong> to match your SocietyPress tier names, then re-import. The importer will update existing members rather than duplicate them &mdash; matched by email.</li>
            </ol>

            <hr>

            <h2>5. &ldquo;Renewal reminder emails aren't going out&rdquo;</h2>

            <p><strong>What you're seeing:</strong> Members are reaching their renewal date but not getting the automated reminder email. You've checked spam folders &mdash; nothing there either.</p>

            <p><strong>Why:</strong> Two common causes. First, WordPress's <code>wp_cron</code> system runs when someone visits the site &mdash; if your society site gets low traffic, cron tasks can stack up and not fire. Second, many shared hosts silently drop emails sent via PHP's built-in <code>mail()</code> function because of spam-prevention rules.</p>

            <p><strong>Fix:</strong></p>
            <ol>
                <li>Install a transactional email plugin like <strong>WP Mail SMTP</strong> or <strong>FluentSMTP</strong>. Point it at a free-tier SMTP provider (Amazon SES, Postmark, Mailgun, SendGrid). This solves deliverability for good.</li>
                <li>Set up a real cron trigger instead of WordPress's built-in pseudo-cron. Your cPanel has a &ldquo;Cron Jobs&rdquo; tool; add one that hits <code>wp-cron.php</code> every 15 minutes:
                    <pre><code>wget -q -O - https://yoursite.org/wp-cron.php?doing_wp_cron &gt;/dev/null 2&gt;&amp;1</code></pre>
                </li>
            </ol>

            <hr>

            <h2>6. &ldquo;Members can't log in after migration&rdquo;</h2>

            <p><strong>What you're seeing:</strong> Everything looks right in the member list, but members can't log in with their old passwords.</p>

            <p><strong>Why:</strong> Passwords don't migrate &mdash; and shouldn't. ENS (and every other platform) stores passwords hashed. No one, not ENS, not us, not you, can recover the plaintext. This is by design: a migration that brought plaintext passwords across would be a security incident.</p>

            <p><strong>Fix:</strong> Every member sets a new password on first login via the standard &ldquo;Forgot password&rdquo; link. Send a notification email a few days before cutover explaining this clearly:</p>
            <blockquote>
                <p>
                    <em>When our new site goes live on [date], please log in using the &ldquo;Forgot password&rdquo; link on the login page. Your membership and account history are coming with us &mdash; nothing is being lost. The password reset is a one-time step for security.</em>
                </p>
            </blockquote>

            <hr>

            <h2>7. &ldquo;The public site looks broken &mdash; no styling&rdquo;</h2>

            <p><strong>What you're seeing:</strong> Raw HTML with no colors, no layout, like the browser loaded the page without any CSS.</p>

            <p><strong>Why:</strong> Usually a theme issue. Either the SocietyPress theme (or a child theme) isn't activated, or a child theme is pointing at a missing parent.</p>

            <p><strong>Fix:</strong> Go to <code>Appearance &rarr; Themes</code>. The active theme should be the SocietyPress parent theme or one of its child themes (Heritage, Coastline, Prairie, Ledger, Parlor). If a default WordPress theme (Twenty Twenty-Five, etc.) is active, switch to SocietyPress. If a child theme is active but broken, switch to the SocietyPress parent first to confirm the base works, then re-activate your child theme.</p>

            <hr>

            <h2>8. &ldquo;Payments aren't being recorded&rdquo;</h2>

            <p><strong>What you're seeing:</strong> A member pays for a renewal or event registration successfully through Stripe or PayPal, but the SocietyPress dashboard doesn't show the transaction.</p>

            <p><strong>Why:</strong> Stripe and PayPal use webhooks to notify SocietyPress that a payment succeeded. If the webhook URL isn't configured in your processor's dashboard, or if your host's firewall is blocking the incoming webhook request, payments process but don't get recorded on our end.</p>

            <p><strong>Fix:</strong></p>
            <ol>
                <li>In your Stripe dashboard, go to <strong>Developers &rarr; Webhooks</strong>. Confirm there's an endpoint pointed at <code>yoursite.org/wp-json/societypress/v1/webhooks/stripe</code>.</li>
                <li>In PayPal, go to <strong>My Apps &amp; Credentials</strong>, open your app, scroll to Webhooks. Endpoint should be <code>yoursite.org/wp-json/societypress/v1/webhooks/paypal</code>.</li>
                <li>Both dashboards show the last 30 days of delivery attempts. Check for 4xx or 5xx response codes &mdash; those indicate your site is receiving the webhook but returning an error.</li>
                <li>If deliveries aren't reaching your site at all, ask your host whether their firewall is blocking Stripe's or PayPal's IP ranges.</li>
            </ol>

            <hr>

            <h2>9. &ldquo;The page builder won't save my changes&rdquo;</h2>

            <p><strong>What you're seeing:</strong> You edit a page in the page builder, click Save, get a success message, but reloading the page shows the old content.</p>

            <p><strong>Why:</strong> Caching. Either your hosting provider's CDN is serving the old page, a WordPress caching plugin is holding onto the old version, or your browser is showing a cached copy.</p>

            <p><strong>Fix:</strong> In order of likelihood:</p>
            <ol>
                <li>Open the page in an incognito/private window. If your changes appear, it's browser-side caching &mdash; hard-refresh (<code>Cmd+Shift+R</code> on Mac, <code>Ctrl+Shift+R</code> on Windows).</li>
                <li>If you have a caching plugin (W3 Total Cache, WP Super Cache, LiteSpeed Cache), clear its cache from the plugin's admin panel.</li>
                <li>If your host provides server-side caching (Cloudways, Kinsta, WP Engine), clear it from the hosting control panel.</li>
            </ol>

            <hr>

            <h2>10. &ldquo;I deleted a page/event/member by accident&rdquo;</h2>

            <p><strong>What you're seeing:</strong> You clicked Delete on something important. Panic.</p>

            <p><strong>Why:</strong> Happens to everyone. Breathe.</p>

            <p><strong>Fix:</strong> WordPress and SocietyPress both use a Trash model for most deletions &mdash; things aren't actually deleted for 30 days. To recover:</p>
            <ul>
                <li><strong>Pages:</strong> <code>Pages &rarr; Trash</code>. Click Restore.</li>
                <li><strong>Members:</strong> <code>Members &rarr; Trash</code> (same pattern).</li>
                <li><strong>Events:</strong> Same.</li>
                <li><strong>Library items, newsletters, records:</strong> Same.</li>
            </ul>
            <p>If you did a &ldquo;Delete Permanently&rdquo; by mistake, restoring from your host's daily backup is your last resort. This is why the full-site export at <code>Settings &rarr; Export &amp; Backup</code> is worth running monthly &mdash; a known-good backup from two weeks ago beats scrambling through your host's backup interface at midnight.</p>

            <hr>

            <h2>Still stuck?</h2>

            <p>
                Three ways to get more help, in order of how fast you'll
                hear back:
            </p>
            <ol>
                <li><a href="<?php echo esc_url( home_url( '/forums/' ) ); ?>">The community forum</a> &mdash; other society admins have almost certainly seen your issue.</li>
                <li><a href="<?php echo esc_url( home_url( '/bug-reports/' ) ); ?>">Report a bug</a> &mdash; if you're sure it's a SocietyPress issue and not a configuration problem.</li>
                <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Email support</a> &mdash; slowest, but always answered.</li>
            </ol>

        </article>

        <div class="guide-page__footer">
            <p>
                <strong>See also:</strong>
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation hub</a> &middot;
                <a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">FAQ</a> &middot;
                <a href="<?php echo esc_url( home_url( '/requirements/' ) ); ?>">System requirements</a>
            </p>
        </div>

    </div>
</section>

<?php get_footer(); ?>
