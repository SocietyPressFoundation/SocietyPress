<?php
/**
 * One-Pager: For Board Members (page-for-board-members.php)
 *
 * Aimed at the decision-making audience — board members evaluating whether
 * their society should move to SocietyPress. Framing is governance,
 * fiscal responsibility, and risk; not technical specs.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="onepager">
    <div class="container container--narrow">

        <header class="onepager__header">
            <div class="onepager__role-label">For board members</div>
            <h1 class="onepager__title">A fiscal and governance case for SocietyPress.</h1>
            <p class="onepager__lede">
                Your society's technology costs, vendor dependencies, and member
                data exposure are all governance concerns. SocietyPress is built
                to make each of those better. This is the one-page summary for
                your next board meeting.
            </p>
        </header>

        <section class="onepager__section">
            <h2>The fiscal picture</h2>

            <div class="onepager__compare">

                <div class="onepager__compare-col">
                    <h3>Commercial society platform</h3>
                    <ul>
                        <li>$400&ndash;$900/year per society</li>
                        <li>Plus setup fees ($500&ndash;$2,000 typical)</li>
                        <li>Plus credit-card surcharges on member payments (2&ndash;4%)</li>
                        <li>Price increases annually, most years</li>
                        <li>Data export often requires a paid request</li>
                    </ul>
                    <p class="onepager__compare-total">
                        Typical 5-year cost: <strong>$2,500&ndash;$5,500</strong>
                    </p>
                </div>

                <div class="onepager__compare-col onepager__compare-col--us">
                    <h3>SocietyPress</h3>
                    <ul>
                        <li>Software: free (GPL v2)</li>
                        <li>Setup: free (volunteer time)</li>
                        <li>Hosting: $60&ndash;$180/year at standard shared hosts</li>
                        <li>Payment fees go directly to Stripe/PayPal (your society's own accounts &mdash; no middleman markup)</li>
                        <li>Data export is built in and instant</li>
                    </ul>
                    <p class="onepager__compare-total">
                        Typical 5-year cost: <strong>$300&ndash;$900</strong>
                    </p>
                </div>

            </div>
        </section>

        <section class="onepager__section">
            <h2>Governance advantages</h2>

            <div class="onepager__features">

                <div class="onepager__feature">
                    <h3>No single-vendor risk</h3>
                    <p>The software is open-source and self-hosted. If the project ever stops being maintained, your site keeps running, your data stays yours, and you can hire any WordPress developer to continue supporting it. Commercial platforms create a dependency that becomes existential if the vendor is acquired, raises prices, or sunsets their product.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Member data protection</h3>
                    <p>Sensitive member fields (phone, address, date of birth) are encrypted at rest using modern cryptography. The system has built-in GDPR export and erasure tools. Your members' data lives on your own hosting, under your society's control &mdash; not on a vendor's server.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Fiduciary auditability</h3>
                    <p>Donation ledger, event registration payments, and membership dues all flow through your own Stripe or PayPal account. Your treasurer can reconcile directly against bank statements with no intermediary.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Role-based access</h3>
                    <p>Eight role templates (President, Membership Chair, Treasurer, Librarian, etc.) across ten access areas. Officers see what they need. Nobody accidentally sees the donation ledger who shouldn't.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Election integrity</h3>
                    <p>Run board elections with authenticated, audited online ballots. Every vote is attributed to a specific member account, and the audit trail survives even if a ballot is later questioned.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Meeting minutes &amp; committees</h3>
                    <p>First-class support for committees, officer terms, and governance documents. Track assignments, publish meeting minutes, and keep a historical record that outlives any one board.</p>
                </div>

            </div>
        </section>

        <section class="onepager__section onepager__section--highlighted">
            <h2>The board-level questions, answered</h2>
            <dl class="onepager__faq">
                <dt>What if the project stops being maintained?</dt>
                <dd>The software keeps working. You own the data. You can hire any WordPress developer to keep it running. The GPL license prevents it from ever being made proprietary.</dd>

                <dt>What's our liability exposure?</dt>
                <dd>Same as any self-hosted website, no more. Your hosting provider's terms of service apply. SocietyPress itself is distributed as-is with no warranty, like all open-source software.</dd>

                <dt>Who's behind this?</dt>
                <dd>Charles Stricklin &mdash; a software developer who created, writes, and maintains SocietyPress entirely on his own. Speaking at TSGS Leadership Conference 2026.</dd>

                <dt>How do we evaluate it?</dt>
                <dd>Visit the fully-functional demo at demo.getsocietypress.org. Log in, click around, break things. No account, no form, no demo-request call required.</dd>
            </dl>
        </section>

        <footer class="onepager__footer">
            <p>
                <strong>Evaluation demo:</strong>
                <a href="https://demo.getsocietypress.org">demo.getsocietypress.org</a>
            </p>
            <p>
                <strong>Questions for the board:</strong>
                <a href="mailto:hello@getsocietypress.org">hello@getsocietypress.org</a>
            </p>
        </footer>

    </div>
</section>

<?php get_footer(); ?>
