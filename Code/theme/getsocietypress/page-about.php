<?php
/**
 * About Page Template (page-about.php)
 *
 * The About page's story, values, credibility, and roadmap in one place.
 * Served to the /about/ page when it has this template selected. Content
 * lives in this template (not the WP editor) so it moves with the code and
 * stays consistent across deploys.
 *
 * Sections:
 * 1. Hero — "About SocietyPress"
 * 2. The Origin — why this exists
 * 3. Mission — one sentence, clearly stated
 * 4. Who's Behind It — Charles, speaking engagements, milestones
 * 5. Why Open Source — the commitment
 * 6. Roadmap — where we're headed
 * 7. CTA — get involved
 *
 * @package getsocietypress
 * @version 0.03d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. ABOUT HERO
     ========================================================================== -->
<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">About SocietyPress</h1>
            <p class="page-hero__subtitle">
                Built by a society volunteer for society volunteers.
                Free, open source, and meant to stay that way.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     2. THE ORIGIN
     ========================================================================== -->
<section class="about-origin section">
    <div class="container container--narrow">

        <h2>Why This Exists</h2>

        <p>
            Genealogical and historical societies rely on volunteers &mdash;
            people working evenings and weekends, often with inadequate tools.
            Memberships tracked in spreadsheets. Event plugins awkwardly
            adapted for meetings. SaaS platforms costing hundreds per year
            still overlook surname research databases.
        </p>

        <p>
            SocietyPress began from frustration with this gap. Every
            administrator reinvented roster management, dues tracking, event
            calendars, newsletter archives, cemetery transcription publishing
            &mdash; and paid for it.
        </p>

        <p>
            Too often, important needs go unmet. Genealogical and historical
            societies face unique challenges that require software solutions
            tailored to their needs. SocietyPress is developed once, shared
            collectively, and made freely available so every group can access
            and benefit from the right tools without barriers.
        </p>

    </div>
</section>


<!-- ==========================================================================
     3. MISSION
     One sentence, large and unambiguous.
     ========================================================================== -->
<section class="about-mission" aria-labelledby="about-mission-heading">
    <div class="container container--narrow">
        <div class="about-mission__content">
            <h2 id="about-mission-heading" class="screen-reader-text">Mission</h2>
            <p class="about-mission__statement">
                SocietyPress exists to give every genealogical and historical
                society &mdash; no matter how small, no matter how broke &mdash;
                the same caliber of website and membership tools as the largest
                national organizations, without ever charging for it.
            </p>
        </div>
    </div>
</section>


<!-- ==========================================================================
     4. WHO'S BEHIND IT
     Trust signals: the real person, the real society, the real speaking
     engagements. Specific beats vague every time.
     ========================================================================== -->
<section class="about-who section">
    <div class="container">

        <div class="section-header">
            <h2>Who's Behind It</h2>
        </div>

        <div class="about-who__grid">

            <div class="about-who__bio">
                <h3>Charles Stricklin</h3>
                <p class="about-who__role">
                    Creator, developer, and lead maintainer.
                </p>
                <p>
                    Charles is a software developer and genealogist in Texas
                    with a BBA in Business Information Systems from
                    <a href="https://msstate.edu" target="_blank" rel="noopener">Mississippi State University</a>.
                    Everything SocietyPress is his own work,
                    start to finish &mdash; designed, coded, documented, and
                    maintained by one person who cares that the result is
                    right.
                </p>
                <p>
                    He speaks about society technology at community events
                    and writes every line of SocietyPress himself. The
                    plugin's design choices, feature priorities, and rough
                    edges come from his own experience as a society
                    webmaster, conversations with peers in the
                    genealogical-society community, and feedback from the
                    early-adopter societies as they come online.
                </p>
            </div>

            <div class="about-who__credentials">
                <h4>Speaking &amp; Community</h4>
                <ul class="about-who__list">
                    <li>
                        <strong>TSGS Leadership Conference 2026</strong>
                        <span>Speaker and vendor &mdash; August 29, 2026</span>
                    </li>
                    <li>
                        <strong>RootsTech 2027</strong>
                        <span>Target launch milestone &mdash; Salt Lake City, March 3&ndash;7, 2027</span>
                    </li>
                </ul>
            </div>

        </div>

    </div>
</section>


<!-- ==========================================================================
     5. WHY OPEN SOURCE
     The commitment, in plain terms.
     ========================================================================== -->
<section class="about-open section">
    <div class="container container--narrow">

        <h2>Why Open Source</h2>

        <p>
            SocietyPress is released under the GNU General Public License, version
            2. In practical terms, that means three things that matter:
        </p>

        <ul class="about-open__points">
            <li>
                <strong>No paywall, ever.</strong>
                There is no &ldquo;Pro&rdquo; version, no premium tier, no locked
                features, no trial expiration. Everything SocietyPress does is in
                the free download, and it always will be.
            </li>
            <li>
                <strong>No vendor lock-in.</strong>
                The code is public on GitHub. Your data is yours to export in
                standard formats at any time. If SocietyPress ever stopped being
                developed tomorrow, your site would keep running on whatever host
                you chose, and your data would still be portable.
            </li>
            <li>
                <strong>Legal protection.</strong>
                The GPL license is legally binding. The code stays open,
                past versions remain free forever, and the license guarantees
                future versions will too. Anyone &mdash; including us &mdash;
                is free to fork SocietyPress and keep it going, so the
                project can never be closed off.
            </li>
        </ul>

        <p>
            <a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>">Donations are always welcome and never required.</a>
            They fund continued development, server costs, and conference
            attendance &mdash; nothing more and nothing less.
        </p>

    </div>
</section>


<!-- ==========================================================================
     6. ROADMAP
     Broad strokes. Not a promise of dates — a direction.
     ========================================================================== -->
<section class="about-roadmap section">
    <div class="container container--narrow">

        <h2>Where We're Headed</h2>

        <p>
            The plugin hit feature completeness in early 2026. The work ahead
            is in three broad arcs:
        </p>

        <ol class="about-roadmap__list">
            <li>
                <strong>Documentation.</strong>
                Comprehensive written guides, video walkthroughs, and migration
                guides &mdash; especially for societies coming from
                <a href="<?php echo esc_url( home_url( '/docs/ens-migration/' ) ); ?>">EasyNetSites (ENS)</a>,
                which is the most common platform SocietyPress replaces.
            </li>
            <li>
                <strong>Adoption.</strong>
                Getting SocietyPress into the hands of more societies &mdash;
                starting with the roughly 170 active ENS societies that are looking
                for a successor, and expanding from there. This is why
                <a href="https://rootstech.org">RootsTech 2027</a> matters.
            </li>
            <li>
                <strong>Polish.</strong>
                Store checkout, records imports, cleaning up the last
                i18n gaps, and whatever else shakes out once early-adopter
                societies are running SocietyPress in production and
                telling us what hurts.
            </li>
        </ol>

    </div>
</section>


<!-- ==========================================================================
     7. CTA
     ========================================================================== -->
<section class="about-cta">
    <div class="container">
        <div class="about-cta__content">
            <h2>Join In</h2>
            <p>
                Download SocietyPress, try the demo, read the documentation,
                or reach out directly. We'd love to hear from your society.
            </p>
            <div class="about-cta__actions">
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary btn-lg">
                    Download SocietyPress
                </a>
                <a href="https://demo.getsocietypress.org" class="btn btn-outline btn-lg" target="_blank" rel="noopener">
                    Try the Demo
                </a>
                <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>" class="btn btn-secondary btn-lg">
                    Get in Touch
                </a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
