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
 * 4. Who's Behind It — Charles, the society, TSGS, RootsTech
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
            Genealogical and historical societies have always been run by
            volunteers &mdash; dedicated people doing meaningful work on
            evenings and weekends, usually with tools that were never designed
            for what they're actually doing. Spreadsheets tracking memberships.
            Generic event plugins awkwardly bent to fit monthly meetings. SaaS
            platforms that cost hundreds a month and still don't understand
            what a surname research database is.
        </p>

        <p>
            SocietyPress started because that gap was frustrating to live with.
            Every society's administrator was reinventing the same wheel &mdash;
            roster management, dues tracking, event calendars, newsletter
            archives, cemetery transcription publishing &mdash; and paying for
            the privilege.
        </p>

        <p>
            It didn't need to be that way. Genealogical and historical societies
            share a set of problems distinct enough to deserve their own software,
            specific enough to build once and share with all of them, and
            important enough that the tools to solve them should be free.
        </p>

    </div>
</section>


<!-- ==========================================================================
     3. MISSION
     One sentence, large and unambiguous.
     ========================================================================== -->
<section class="about-mission">
    <div class="container container--narrow">
        <div class="about-mission__content">
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
                    Charles is a software developer and genealogist,
                    Texas, with a BBA in Business Information Systems from Midwestern
                    State University. He serves as Education Committee Chair at the
                    <strong>the society</strong>
                    (the society), where SocietyPress was originally built to solve the
                    society's own website and membership problems.
                </p>
                <p>
                    He speaks about society technology at community events and
                    writes every line of SocietyPress himself &mdash; though the
                    plugin's design choices, feature priorities, and rough edges
                    all come from sustained conversation with society administrators
                    who are actually running the thing.
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
                    <li>
                        <strong>the society</strong>
                        <span>Education Committee Chair &mdash; ongoing</span>
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
                The GPL license is legally binding. SocietyPress cannot be made
                proprietary, cannot be bought and closed off, and cannot be
                turned into a subscription service. Past versions remain free
                forever, and the license guarantees future versions will too.
            </li>
        </ul>

        <p>
            Donations are always welcome and never required. They fund continued
            development, server costs, and conference attendance &mdash; nothing
            more and nothing less.
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
                <a href="<?php echo esc_url( home_url( '/ens-migration/' ) ); ?>">EasyNetSites (ENS)</a>,
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
                Store checkout, records imports, cleaning up the last i18n gaps,
                and whatever else shakes out from societies actually running
                SocietyPress in production and telling us what hurts.
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
