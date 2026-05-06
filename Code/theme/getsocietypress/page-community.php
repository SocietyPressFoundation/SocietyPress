<?php
/**
 * Community Page Template (page-community.php)
 *
 * Connects societies using SocietyPress with each other and with
 * support resources. This is NOT an open-contribution project —
 * "open source" here means transparency and the freedom to modify,
 * not a call for pull requests.
 *
 * Sections:
 * 1. Hero — "Community"
 * 2. What Open Source Means Here — transparency, not contribution
 * 3. Get Help — where to go when you need assistance
 * 4. Spread the Word + Donate
 * 5. CTA — download
 *
 * @package getsocietypress
 * @version 0.02d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. COMMUNITY HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Community</h1>
            <p class="page-hero__subtitle">
                Societies using SocietyPress, helping each other out.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. WHAT OPEN SOURCE MEANS HERE
     Important distinction: we're not soliciting contributions. We're
     giving you full access to the code so you know what you're running
     and can customize it for your needs.
     ========================================================================== -->
<section class="comm-opensource section">
    <div class="container container--narrow">

        <h2>What "Open Source" Means Here</h2>

        <p>
            SocietyPress is released under the GPL v2 license. That means you get
            full access to every line of code. You can see exactly what the software
            does, how it handles your data, and how it works under the hood.
        </p>

        <p>
            If your society has a developer who wants to tweak something — change a
            layout, add a field, adjust how a feature works — they're free to do that.
            The code is yours to modify.
        </p>

        <p>
            This isn't a call for contributors or pull requests. SocietyPress is
            developed and maintained by one person. Open source, to us, means
            transparency and freedom — not crowdsourced development.
        </p>

    </div>
</section>


<!-- ==========================================================================
     3. GET HELP
     Where to go when you need assistance.
     ========================================================================== -->
<section class="comm-help section">
    <div class="container container--narrow">

        <div class="section-header">
            <h2>Need Help?</h2>
            <p>Here's where to go when you have questions or run into problems.</p>
        </div>

        <div class="comm-help__list">

            <div class="comm-help__item">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Documentation
                </h3>
                <p>Start with the <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">documentation</a> — it covers installation, setup, and all major features.</p>
            </div>

            <div class="comm-help__item">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    FAQ
                </h3>
                <p>Check the <a href="<?php echo esc_url( home_url( '/docs/faq/' ) ); ?>">frequently asked questions</a> for quick answers to common questions.</p>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     4. SPREAD THE WORD + DONATE
     ========================================================================== -->
<section class="comm-involve section">
    <div class="container">

        <div class="comm-involve__grid">

            <div class="comm-involve__card">
                <div class="comm-involve__card-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <h3>Spread the Word</h3>
                <p>
                    Know a genealogical society that's struggling with their website? Tell
                    them about SocietyPress. The more societies that use it, the better
                    it gets for everyone.
                </p>
            </div>

            <div class="comm-involve__card">
                <div class="comm-involve__card-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </div>
                <h3>Donate</h3>
                <p>
                    SocietyPress is free, but hosting, development tools, and time aren't.
                    If this software helps your society, a donation — of any size — helps
                    keep the project going.
                </p>
                <a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>" class="btn btn-secondary btn-sm">
                    Support the Project
                </a>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     5. CTA
     ========================================================================== -->
<section class="comm-cta">
    <div class="container">
        <div class="comm-cta__content">
            <h2>Ready to get started?</h2>
            <p>
                Download SocietyPress and take control of your society's website.
            </p>
            <div class="comm-cta__actions">
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary btn-lg">
                    Download SocietyPress
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
