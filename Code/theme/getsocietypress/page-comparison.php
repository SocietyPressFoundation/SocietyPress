<?php
/**
 * Comparison Page Template (page-comparison.php)
 *
 * Side-by-side comparison of SocietyPress against the platforms most
 * genealogical and historical societies are evaluating: ENS / Blue Crab
 * (the legacy ENS-Classic and the new ENS-Responsive), and Wild Apricot
 * (a generic association-management platform many societies consider).
 *
 * Sections:
 * 1. Hero — "Three platforms. One that's actually yours."
 * 2. Quick matrix — 4-column at-a-glance card grid
 * 3. Honest weaknesses — where SocietyPress is genuinely behind
 * 4. Detailed feature matrix — rows of features, columns of platforms
 * 5. Pricing comparison — total cost of ownership over 5 years
 * 6. Migration callout — link to ENS migration guide
 * 7. Final CTA — Download / read the migration guide
 *
 * @package getsocietypress
 * @version 0.01d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- ==========================================================================
     1. HERO
     ========================================================================== -->
<section class="feat-hero cmp-hero">
    <div class="container">
        <div class="feat-hero__content">

            <div class="hero__badge">Honest comparison</div>

            <h1 class="feat-hero__title">
                Three platforms.<br>
                <span>One that's actually yours.</span>
            </h1>

            <p class="feat-hero__subtitle">
                Most genealogical societies pick between EasyNetSites, Wild Apricot,
                and SocietyPress. Here's the side-by-side — pricing, ownership,
                migration cost, and where each one wins.
            </p>

            <div class="feat-hero__actions">
                <a href="#matrix" class="btn btn-primary btn-lg">See the matrix</a>
                <a href="<?php echo esc_url( home_url( '/ens-migration/' ) ); ?>" class="btn btn-secondary btn-lg">ENS migration guide</a>
            </div>

        </div>
    </div>
</section>


<!-- ==========================================================================
     2. QUICK MATRIX — 4-column at-a-glance card grid
     ========================================================================== -->
<section class="cmp-section" id="matrix">
    <div class="container">
        <div class="section-header">
            <h2>At a glance</h2>
            <p>The differences that change which platform actually fits your society.</p>
        </div>

        <div class="cmp-quick-grid">

            <!-- SocietyPress -->
            <div class="cmp-card cmp-card--featured">
                <div class="cmp-card__badge">SocietyPress</div>
                <ul class="cmp-card__list">
                    <li><strong>Free forever.</strong> GPL-2.0, no per-member pricing.</li>
                    <li><strong>You own everything.</strong> Files, database, code — on your hosting.</li>
                    <li><strong>Built for societies.</strong> 16 modules, 21 page-builder widgets, 5 child themes.</li>
                    <li><strong>One-click installer.</strong> No professional services bill.</li>
                    <li><strong>ENS migration built in.</strong> 86-column CSV importer.</li>
                    <li><strong>Open source.</strong> Code on GitHub. No vendor lock-in.</li>
                </ul>
            </div>

            <!-- ENS-R -->
            <div class="cmp-card">
                <div class="cmp-card__badge cmp-card__badge--alt">ENS-Responsive</div>
                <ul class="cmp-card__list">
                    <li><strong>$249–$700+/year</strong> tiered by membership size.</li>
                    <li><strong>Hosted only.</strong> Data lives on Blue Crab servers.</li>
                    <li><strong>Mature module set.</strong> ~15 modules including Voting, Surname Research, UDM.</li>
                    <li><strong>Migration cost.</strong> $60/hr — $1,800–$2,400 typical for ENS-C → ENS-R.</li>
                    <li><strong>ENS-Classic sunsetting</strong> May 31, 2027.</li>
                    <li><strong>Closed source.</strong> Likely WordPress under the hood, locked down.</li>
                </ul>
            </div>

            <!-- Wild Apricot -->
            <div class="cmp-card">
                <div class="cmp-card__badge cmp-card__badge--alt">Wild Apricot</div>
                <ul class="cmp-card__list">
                    <li><strong>$60–$330+/month</strong> tiered by contact count.</li>
                    <li><strong>Hosted only.</strong> Personify, the parent company, owns the stack.</li>
                    <li><strong>Generic association tools.</strong> Members, events, donations — not genealogy-specific.</li>
                    <li><strong>No surname research.</strong> No First Families. No library catalog.</li>
                    <li><strong>Migration is BYO.</strong> CSV in, CSV out — no genealogy importers.</li>
                    <li><strong>Closed source.</strong> SaaS only.</li>
                </ul>
            </div>

            <!-- Custom WordPress -->
            <div class="cmp-card">
                <div class="cmp-card__badge cmp-card__badge--alt">Custom WordPress</div>
                <ul class="cmp-card__list">
                    <li><strong>WordPress + plugins</strong> assembled à la carte.</li>
                    <li><strong>Per-plugin licenses</strong> add up — MemberPress, The Events Calendar, etc.</li>
                    <li><strong>You integrate.</strong> Five plugins from five vendors that don't talk to each other.</li>
                    <li><strong>No genealogy-specific features.</strong> Build your own surname directory.</li>
                    <li><strong>Maintenance is yours.</strong> Or your developer's, billed hourly.</li>
                    <li><strong>You own everything.</strong> Same upside as SocietyPress, more work.</li>
                </ul>
            </div>

        </div>
    </div>
</section>


<!-- ==========================================================================
     3. HONEST WEAKNESSES
     ========================================================================== -->
<section class="cmp-section cmp-section--alt">
    <div class="container">
        <div class="section-header">
            <h2>Where SocietyPress is genuinely behind</h2>
            <p>Pretending we win every category is sales fiction. Here's the truth.</p>
        </div>

        <div class="cmp-honest-grid">
            <div class="cmp-honest">
                <h3>Track record</h3>
                <p>ENS has been running for 15 years and serves 170+ societies. SocietyPress is younger and growing. If "thousands of societies use it" is what you need to bring back to your board, ENS still wins that conversation.</p>
            </div>
            <div class="cmp-honest">
                <h3>Hosted convenience</h3>
                <p>ENS and Wild Apricot include hosting, backups, and updates. SocietyPress is self-hosted — you'll need a $5–$10/month cPanel host and to click "Update" when prompted. Most of our target audience is fine with that. If you want zero infrastructure responsibility, hosted is genuinely simpler.</p>
            </div>
            <div class="cmp-honest">
                <h3>White-glove migration</h3>
                <p>ENS will (for $60/hour) import your data, build your pages, and hand you a finished site. SocietyPress's installer + ENS CSV importer covers the data migration for free, but page rebuilding and theme customization is on you or a volunteer.</p>
            </div>
            <div class="cmp-honest">
                <h3>Phone support</h3>
                <p>ENS and Wild Apricot answer the phone. SocietyPress is a community project — questions go to GitHub Issues or the user forum. We'll get back to you, but not at 9 a.m. Tuesday on a guaranteed SLA.</p>
            </div>
        </div>
    </div>
</section>


<!-- ==========================================================================
     4. DETAILED FEATURE MATRIX
     ========================================================================== -->
<section class="cmp-section">
    <div class="container">
        <div class="section-header">
            <h2>Feature-by-feature</h2>
            <p>The full breakdown across the categories that matter for societies.</p>
        </div>

        <div class="cmp-matrix-wrap">
            <table class="cmp-matrix">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th class="cmp-matrix__sp">SocietyPress</th>
                        <th>ENS-Responsive</th>
                        <th>Wild Apricot</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Cost & ownership -->
                    <tr class="cmp-matrix__group"><td colspan="4">Cost &amp; ownership</td></tr>
                    <tr><td>Annual platform fee</td><td class="cmp-matrix__sp">$0</td><td>$249–$700+</td><td>$720–$3,960+</td></tr>
                    <tr><td>Source code access</td><td class="cmp-matrix__sp">✓ GitHub</td><td>✗</td><td>✗</td></tr>
                    <tr><td>You own the data</td><td class="cmp-matrix__sp">✓</td><td>~ (export available)</td><td>~ (export available)</td></tr>
                    <tr><td>You can self-host</td><td class="cmp-matrix__sp">✓ Any cPanel host</td><td>✗</td><td>✗</td></tr>

                    <!-- Membership -->
                    <tr class="cmp-matrix__group"><td colspan="4">Membership</td></tr>
                    <tr><td>Tiered membership levels</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Online dues with Stripe + PayPal</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Renewal reminders</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Lifetime / honorary tiers</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Member directory with surname/area filters</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>~ (generic only)</td></tr>

                    <!-- Genealogy-specific -->
                    <tr class="cmp-matrix__group"><td colspan="4">Genealogy-specific</td></tr>
                    <tr><td>Surname Research Database (with Soundex)</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✗</td></tr>
                    <tr><td>Library catalog (book/serial inventory)</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✗</td></tr>
                    <tr><td>Lineage / First Families program</td><td class="cmp-matrix__sp">✓</td><td>✗</td><td>✗</td></tr>
                    <tr><td>Member-submitted ancestor photo wall</td><td class="cmp-matrix__sp">✓</td><td>✗</td><td>✗</td></tr>
                    <tr><td>Genealogical record collections (cemetery, census, etc.)</td><td class="cmp-matrix__sp">✓</td><td>✓ (UDM)</td><td>✗</td></tr>

                    <!-- Events -->
                    <tr class="cmp-matrix__group"><td colspan="4">Events</td></tr>
                    <tr><td>Event calendar</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Online registration with paid tickets</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>iCal feed / Google Calendar sync</td><td class="cmp-matrix__sp">✓</td><td>~</td><td>✓</td></tr>
                    <tr><td>Speaker management</td><td class="cmp-matrix__sp">✓</td><td>~</td><td>~</td></tr>

                    <!-- Communications -->
                    <tr class="cmp-matrix__group"><td colspan="4">Communications</td></tr>
                    <tr><td>Newsletter archive (PDFs)</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Built-in blast email</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Document library with member-only access</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>

                    <!-- Governance -->
                    <tr class="cmp-matrix__group"><td colspan="4">Governance</td></tr>
                    <tr><td>Online voting / ballots</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>~</td></tr>
                    <tr><td>Committees as first-class records</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>~</td></tr>
                    <tr><td>Volunteer hours tracking</td><td class="cmp-matrix__sp">✓</td><td>~</td><td>~</td></tr>
                    <tr><td>Meeting minutes</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>~</td></tr>

                    <!-- Donations -->
                    <tr class="cmp-matrix__group"><td colspan="4">Donations</td></tr>
                    <tr><td>One-time donations (Stripe + PayPal)</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Recurring monthly / annual donations</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>
                    <tr><td>Cover-the-fee toggle</td><td class="cmp-matrix__sp">✓</td><td>✗</td><td>~</td></tr>
                    <tr><td>Anonymous donations</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>

                    <!-- Store -->
                    <tr class="cmp-matrix__group"><td colspan="4">Store</td></tr>
                    <tr><td>Sell publications + merchandise</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>~ (limited)</td></tr>
                    <tr><td>Per-item shipping fees</td><td class="cmp-matrix__sp">✓</td><td>✓</td><td>✓</td></tr>

                    <!-- Migration -->
                    <tr class="cmp-matrix__group"><td colspan="4">Getting in &amp; out</td></tr>
                    <tr><td>One-click installer</td><td class="cmp-matrix__sp">✓</td><td>n/a (hosted)</td><td>n/a (hosted)</td></tr>
                    <tr><td>ENS-format CSV importer (86 fields)</td><td class="cmp-matrix__sp">✓</td><td>$60/hr to import</td><td>BYO mapping</td></tr>
                    <tr><td>Full data export (your data, no friction)</td><td class="cmp-matrix__sp">✓ Full SQL + media</td><td>~</td><td>~</td></tr>
                    <tr><td>Standards-based formats (.genrecord, GEDCOM)</td><td class="cmp-matrix__sp">✓ (planned)</td><td>✗</td><td>✗</td></tr>

                    <!-- Compliance -->
                    <tr class="cmp-matrix__group"><td colspan="4">Compliance &amp; security</td></tr>
                    <tr><td>GDPR data exporters / erasers</td><td class="cmp-matrix__sp">✓</td><td>~</td><td>✓</td></tr>
                    <tr><td>libsodium-encrypted sensitive fields</td><td class="cmp-matrix__sp">✓</td><td>?</td><td>?</td></tr>
                    <tr><td>PWA / mobile-installable</td><td class="cmp-matrix__sp">✓</td><td>✗</td><td>~</td></tr>

                </tbody>
            </table>
        </div>

        <p class="cmp-matrix__legend">
            ✓ = supported &nbsp; · &nbsp; ~ = partial / limited &nbsp; · &nbsp; ✗ = not supported &nbsp; · &nbsp; ? = unverified
        </p>
    </div>
</section>


<!-- ==========================================================================
     5. PRICING — 5-year total cost of ownership for a 200-member society
     ========================================================================== -->
<section class="cmp-section cmp-section--alt">
    <div class="container">
        <div class="section-header">
            <h2>5-year cost for a 200-member society</h2>
            <p>Real-world numbers, not list prices. Hosting, payments, and migration included.</p>
        </div>

        <div class="cmp-pricing-grid">

            <div class="cmp-pricing">
                <div class="cmp-pricing__name">SocietyPress</div>
                <div class="cmp-pricing__total">~$540</div>
                <div class="cmp-pricing__period">5 years total</div>
                <ul class="cmp-pricing__breakdown">
                    <li>Platform: $0</li>
                    <li>Hosting: $9/mo cPanel × 60 = $540</li>
                    <li>Migration: $0 (one-click installer + free CSV import)</li>
                    <li>Stripe / PayPal fees: standard 2.9% + 30¢, no platform markup</li>
                </ul>
            </div>

            <div class="cmp-pricing">
                <div class="cmp-pricing__name">ENS-Responsive</div>
                <div class="cmp-pricing__total">~$4,300</div>
                <div class="cmp-pricing__period">5 years total</div>
                <ul class="cmp-pricing__breakdown">
                    <li>Platform: $499.95/yr × 5 = $2,500</li>
                    <li>Hosting: included</li>
                    <li>Migration to ENS-R: ~$1,800 typical (one-time)</li>
                    <li>Plus payment processing (theirs or yours, varies)</li>
                </ul>
            </div>

            <div class="cmp-pricing">
                <div class="cmp-pricing__name">Wild Apricot</div>
                <div class="cmp-pricing__total">~$8,400</div>
                <div class="cmp-pricing__period">5 years total</div>
                <ul class="cmp-pricing__breakdown">
                    <li>Platform: ~$140/mo × 60 = $8,400 (Community tier, 250 contacts)</li>
                    <li>Hosting: included</li>
                    <li>Migration: BYO (CSV in, your time)</li>
                    <li>Plus payment processing fees (theirs or AffiniPay, varies)</li>
                </ul>
            </div>

        </div>

        <p class="cmp-pricing__note">
            Numbers are estimates from publicly listed pricing as of 2026. Stripe and PayPal processing fees apply on every platform. SocietyPress collects nothing from your transactions — your processor invoices you directly.
        </p>
    </div>
</section>


<!-- ==========================================================================
     6. MIGRATION CALLOUT
     ========================================================================== -->
<section class="cmp-section">
    <div class="container">
        <div class="cmp-migration">
            <h2>Coming from ENS?</h2>
            <p>
                The ENS migration guide walks you through every step — what to export,
                how to import, what carries over, and what doesn't. Free, no signup.
            </p>
            <a href="<?php echo esc_url( home_url( '/ens-migration/' ) ); ?>" class="btn btn-primary btn-lg">
                Read the ENS migration guide →
            </a>
        </div>
    </div>
</section>


<!-- ==========================================================================
     7. FINAL CTA
     ========================================================================== -->
<section class="cmp-cta">
    <div class="container">
        <div class="cmp-cta__content">
            <h2>Pick the platform that's actually yours.</h2>
            <p>Free to download, free to run, free forever. No vendor will ever invoice you for it.</p>
            <div class="cmp-cta__actions">
                <a href="<?php echo esc_url( home_url( '/download/' ) ); ?>" class="btn btn-primary btn-lg">Download SocietyPress</a>
                <a href="<?php echo esc_url( home_url( '/features/' ) ); ?>" class="btn btn-secondary btn-lg">See all features</a>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
