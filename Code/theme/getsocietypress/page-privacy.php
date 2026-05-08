<?php
/**
 * Privacy Policy Page Template (page-privacy.php)
 *
 * Marketing site's own privacy policy — what getsocietypress.org itself
 * collects from visitors. Distinct from the privacy policy that
 * SocietyPress generates for actual society installations (that lives
 * inside the plugin).
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
            <h1 class="page-hero__title">Privacy Policy</h1>
            <p class="page-hero__subtitle">What getsocietypress.org collects, and what we do with it.</p>
        </div>
    </div>
</section>

<section class="legal-page section">
    <div class="container container--narrow">

        <p class="legal-page__updated">
            <strong>Last updated:</strong> April 15, 2026
        </p>

        <p class="legal-page__lede">
            This page covers what <strong>getsocietypress.org</strong> (this
            marketing website) collects from its visitors. If you're looking
            for the privacy policy on a society's SocietyPress installation,
            that policy is generated and maintained by the society running
            the site &mdash; not by us. Each install is an independent deployment
            of free software; the project maintainers have no access to it.
        </p>

        <h2>What we collect on this site</h2>

        <h3>Automatic server logs</h3>
        <p>
            Like every website, our web server records standard request
            information: your IP address, the page you requested, your browser's
            user-agent string, and the time of the request. These logs are
            rotated regularly and used only to diagnose errors, block abuse,
            and produce anonymous aggregate traffic counts.
        </p>

        <h3>Analytics</h3>
        <p>
            We may use privacy-respecting analytics (such as Plausible or a
            self-hosted equivalent) to understand which pages get read. If
            analytics are active, they collect only aggregate page views,
            referrer URLs, and coarse country-level geography &mdash; never
            individual visitor profiles, never cross-site tracking, and never
            behavioral advertising signals. No cookies are set for tracking.
        </p>

        <h3>Cookies</h3>
        <p>
            This site itself does not set tracking cookies. <a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a> may set
            a session cookie if you log into the site (administrator only);
            that cookie expires when the session ends. The announcement bar
            dismissal state is remembered in your browser's sessionStorage
            &mdash; it clears when you close the tab.
        </p>

        <h3>Fonts and embeds</h3>
        <p>
            We load the Inter font family from Google Fonts, which means
            Google may log a request from your browser for the font file. If
            you prefer not to contact Google at all, an ad blocker or
            privacy-focused browser extension will typically block this
            request and fall back to system fonts.
        </p>

        <h3>Downloads</h3>
        <p>
            When you download the SocietyPress bundle, your request is logged
            the same way any other page request is. The bundle itself does
            not call home, does not contain telemetry, and does not
            &ldquo;phone&rdquo; anywhere once it's running on your own server.
        </p>

        <h2>What we never do</h2>

        <ul>
            <li>We do not sell any visitor data.</li>
            <li>We do not share visitor data with advertising networks.</li>
            <li>We do not use cross-site tracking or fingerprinting.</li>
            <li>We do not set cookies for marketing purposes.</li>
            <li>We do not build profiles of individual visitors.</li>
            <li>We do not run retargeting pixels or tracking scripts.</li>
        </ul>

        <h2>Forms and email</h2>

        <p>
            Contact forms and feedback submissions send the information you
            enter to the project maintainer's email. Form submissions are
            kept only as long as needed to respond, and then discarded. We
            will never add your address to a mailing list without your
            explicit opt-in.
        </p>

        <h2>Your rights</h2>

        <p>
            If you have submitted information through a form and would like
            it deleted, or if you want to know what records we hold for you,
            email <a href="mailto:hello@getsocietypress.org">hello@getsocietypress.org</a>
            and we'll sort it out. GDPR and CCPA rights are honored
            regardless of where you live.
        </p>

        <h2>Changes to this policy</h2>

        <p>
            We'll update this page if our practices change. Material changes
            will be dated at the top. This is a living document, not a
            contract &mdash; but we'll treat it like one.
        </p>

        <h2>Questions</h2>

        <p>
            <a href="mailto:hello@getsocietypress.org">hello@getsocietypress.org</a>
        </p>

    </div>
</section>

<?php get_footer(); ?>
