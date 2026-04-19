<?php
/**
 * ENS Migration Landing Page Template (page-ens-migration.php)
 *
 * Front door for the 170+ societies currently running on Easynet Solutions
 * (ENS / EasyNetSites) who are considering SocietyPress. The full step-by-step
 * migration guide lives in docs/ENS-MIGRATION-GUIDE.md; this page
 * is the marketing-framed summary that gets them to commit.
 *
 * Sections:
 * 1. Hero — "Moving from ENS to SocietyPress"
 * 2. Why Societies Move — three common reasons
 * 3. What Comes Across — migration matrix summary
 * 4. The Migration Timeline — realistic expectation
 * 5. FAQ block for ENS-specific questions
 * 6. Final CTA — read the full guide / start the migration
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. ENS HERO — direct, specific, audience-aware
     ========================================================================== -->
<section class="ens-hero">
    <div class="container">
        <div class="ens-hero__content">

            <div class="ens-hero__badge">For ENS Societies</div>

            <h1 class="ens-hero__title">
                Moving from ENS to SocietyPress
            </h1>

            <p class="ens-hero__subtitle">
                If your society is on Easynet Solutions (ENS, sometimes EasyNetSites)
                and you're looking for what comes next, you're in the right place.
                SocietyPress was built with you specifically in mind.
            </p>

            <div class="ens-hero__actions">
                <a href="#start" class="btn btn-primary btn-lg">
                    Read the Migration Guide
                </a>
                <a href="https://demo.getsocietypress.org" class="btn btn-outline btn-lg" target="_blank" rel="noopener">
                    Try the Demo
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ==========================================================================
     2. WHY SOCIETIES MOVE
     Three reasons, stated as facts rather than attacks on ENS.
     ========================================================================== -->
<section class="ens-why section">
    <div class="container">

        <div class="section-header">
            <h2>Why Societies Move</h2>
            <p>
                SocietyPress isn't an attack on ENS &mdash; ENS served a lot of
                societies for a lot of years. But the needs of local societies
                have changed, and the tools available to meet them have changed
                too. These are the three reasons we hear most often.
            </p>
        </div>

        <div class="ens-why__grid">

            <div class="ens-why__card">
                <div class="ens-why__card-number">1</div>
                <h3>Cost</h3>
                <p>
                    ENS bills annually at hundreds of dollars a year for every
                    society, large or small. SocietyPress is free forever, and
                    you own your hosting directly &mdash; typically $5&ndash;15
                    a month at a standard WordPress host. For a 200-member
                    society, that's the difference between spending $600 a year
                    on software and spending $120 a year on hosting.
                </p>
            </div>

            <div class="ens-why__card">
                <div class="ens-why__card-number">2</div>
                <h3>Control</h3>
                <p>
                    On ENS, your site lives on ENS's servers, in ENS's format.
                    When you need to change something &mdash; a new field on the
                    member form, a different newsletter layout, a custom event
                    type &mdash; your options are limited to what ENS supports.
                    On SocietyPress, your site is yours. Your data is yours.
                    If you want to change something, you can.
                </p>
            </div>

            <div class="ens-why__card">
                <div class="ens-why__card-number">3</div>
                <h3>Modernity</h3>
                <p>
                    SocietyPress is built on current WordPress. That means
                    mobile-friendly by default, responsive admin screens,
                    modern payment processing (Stripe and PayPal out of the
                    box), PWA support, real search, and all the usability that
                    decades of web development have produced. Your members get
                    a site that feels like 2026, not 2006.
                </p>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     3. WHAT COMES ACROSS — migration matrix summary
     Full detail lives in the migration guide; this is the at-a-glance view.
     ========================================================================== -->
<section class="ens-migrates section">
    <div class="container">

        <div class="section-header">
            <h2>What Comes Across</h2>
            <p>
                SocietyPress supports the standard ENS 73-field CSV export
                directly. Here's the honest breakdown of what transfers
                cleanly, what needs a bit of translation, and what simply
                doesn't exist on the other side.
            </p>
        </div>

        <div class="ens-migrates__grid">

            <div class="ens-migrates__card ens-migrates__card--good">
                <h3>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    Transfers cleanly
                </h3>
                <ul>
                    <li>Full name (prefix, first, preferred, middle, maiden, last, suffix)</li>
                    <li>Membership status and join date</li>
                    <li>Birth date</li>
                    <li>Primary and seasonal addresses</li>
                    <li>Phone, email, and website</li>
                    <li>Email and directory privacy preferences</li>
                    <li>Administrative notes</li>
                    <li>ENS member record ID (preserved as your member number)</li>
                </ul>
            </div>

            <div class="ens-migrates__card ens-migrates__card--meh">
                <h3>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    Needs a small translation
                </h3>
                <ul>
                    <li><strong>Country</strong> &mdash; free-form text normalized to ISO codes</li>
                    <li><strong>Active flag</strong> &mdash; Yes/No converted to active/inactive/lapsed</li>
                    <li><strong>Joint members</strong> &mdash; you choose: keep combined, or split into two linked records</li>
                    <li><strong>Extra phone/email slots</strong> &mdash; moved into the member's notes</li>
                </ul>
            </div>

            <div class="ens-migrates__card ens-migrates__card--bad">
                <h3>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Doesn't come across
                </h3>
                <ul>
                    <li>ENS internal bookkeeping (login counts, file names, audit metadata)</li>
                    <li>Member photos &mdash; filenames are in the CSV but the image files need a separate transfer</li>
                    <li>Member passwords &mdash; members set a new one via "Forgot password" on first login</li>
                    <li>Free-form skills/interests/education &mdash; combined into a single "About" note</li>
                </ul>
            </div>

        </div>

        <div class="ens-migrates__note">
            <strong>Got data in a field the importer skips?</strong>
            Let us know before you migrate. One-time custom mappings for your
            society's specific setup are generally not a problem to add.
        </div>

    </div>
</section>


<!-- ==========================================================================
     4. THE TIMELINE — realistic expectations
     ========================================================================== -->
<section class="ens-timeline section" id="start">
    <div class="container">

        <div class="section-header">
            <h2>A Realistic Timeline</h2>
            <p>
                Most societies complete a migration over a weekend &mdash; plus
                a calm follow-up week of verification. Here's what that looks like.
            </p>
        </div>

        <div class="ens-timeline__steps">

            <div class="ens-timeline__step">
                <div class="ens-timeline__step-time">Day 1 &mdash; Morning</div>
                <h3>Export from ENS</h3>
                <p>
                    Log into ENS admin, run the full member CSV export, and
                    download the events calendar, newsletter PDFs, and library
                    catalog if you have them. Sanity-check the member CSV opens
                    cleanly in Excel or Numbers. About 30 minutes.
                </p>
            </div>

            <div class="ens-timeline__step">
                <div class="ens-timeline__step-time">Day 1 &mdash; Afternoon</div>
                <h3>Install SocietyPress</h3>
                <p>
                    Upload <code>sp-installer.php</code> to your new hosting
                    account, run the installer, and answer the setup wizard's
                    questions (society name, membership tiers, colors). About
                    45 minutes including the coffee break while WordPress
                    downloads.
                </p>
            </div>

            <div class="ens-timeline__step">
                <div class="ens-timeline__step-time">Day 1 &mdash; Evening</div>
                <h3>Import members</h3>
                <p>
                    Go to Members &gt; Import, upload the ENS CSV, review the
                    preview, and click Import. A 200-member society imports in
                    about 30 seconds. A 2,000-member society takes about five
                    minutes. Verify a handful of records look right.
                </p>
            </div>

            <div class="ens-timeline__step">
                <div class="ens-timeline__step-time">Day 2</div>
                <h3>Bring the rest</h3>
                <p>
                    Events, library catalog, newsletter archive, photos, and
                    records collections. Some of this is bulk import, some is
                    drag-and-drop. Don't rush &mdash; members won't see the new
                    site until you're ready.
                </p>
            </div>

            <div class="ens-timeline__step">
                <div class="ens-timeline__step-time">Week 2</div>
                <h3>Cut over</h3>
                <p>
                    Point your domain at the new host. Email your members a
                    heads-up a week ahead of time, and again the day of. Keep
                    your ENS account active for at least 30 days as a safety
                    net in case you need to re-export anything.
                </p>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     5. FAQ — ENS-specific questions
     ========================================================================== -->
<section class="ens-faq section">
    <div class="container container--narrow">

        <div class="section-header">
            <h2>ENS-Specific Questions</h2>
        </div>

        <div class="faq-group">

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Can I keep my current domain name?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Yes. Most societies keep their existing domain and simply
                        point its DNS at the new host. Your domain registration is
                        separate from ENS &mdash; you're not transferring the
                        domain, just changing where it points. DNS changes take
                        up to 48 hours to propagate fully.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    What about member passwords?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Passwords don't (and shouldn't) migrate. They're hashed
                        inside ENS's database and nobody &mdash; not ENS, not us
                        &mdash; can read them. Every member sets a fresh password
                        the first time they sign in, via the standard "Forgot
                        password" link. This is a security feature, not a bug.
                    </p>
                </div>
            </details>

            <details class="faq-item" open>
                <summary class="faq-item__question">
                    Can I run SocietyPress in parallel with ENS while I test?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Absolutely &mdash; this is the recommended approach. Set
                        up SocietyPress on a subdomain or temporary URL, import
                        your data, get everything looking right, then flip your
                        main domain over when you're ready. Keep ENS running for
                        at least 30 days after the cutover as insurance.
                    </p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-item__question">
                    What if I get stuck mid-migration?
                    <svg class="faq-item__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="faq-item__answer">
                    <p>
                        Every import step is reversible &mdash; Members &gt;
                        Import &gt; Recent Imports has an "Undo this import"
                        button that restores the previous state. If you hit
                        something the undo won't fix, reach out through the
                        <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>">community page</a>
                        and we'll help you work through it.
                    </p>
                </div>
            </details>

        </div>

    </div>
</section>


<!-- ==========================================================================
     6. FINAL CTA — two paths forward
     ========================================================================== -->
<section class="ens-cta">
    <div class="container">
        <div class="ens-cta__content">
            <h2>Ready to start?</h2>
            <p>
                The full step-by-step migration guide walks through every piece
                in detail &mdash; what to export, how to install, how to verify,
                what to tell your members. It's written for non-developers.
            </p>
            <div class="ens-cta__actions">
                <a href="<?php echo esc_url( home_url( '/docs/ens-migration-guide/' ) ); ?>" class="btn btn-primary btn-lg">
                    Read the Full Migration Guide
                </a>
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Download SocietyPress
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
