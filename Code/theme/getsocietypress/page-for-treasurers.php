<?php
/**
 * One-Pager: For Treasurers (page-for-treasurers.php)
 *
 * Aimed at the society treasurer — the person who actually reconciles
 * the books. Focused on money flow, reporting, audit trail, and why
 * the SocietyPress approach (your own Stripe/PayPal accounts) is
 * materially better than middleman SaaS platforms for their job.
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
            <div class="onepager__role-label">For society treasurers</div>
            <h1 class="onepager__title">Money flows directly. Books reconcile cleanly.</h1>
            <p class="onepager__lede">
                SocietyPress connects to your society's own Stripe and PayPal
                accounts &mdash; not ours. There is no middleman taking a cut,
                no third-party statement to reconcile against, and no delay
                between collection and deposit beyond what the processors
                themselves require.
            </p>
        </header>

        <section class="onepager__section">
            <h2>How the money flows</h2>
            <ol class="onepager__steps">
                <li>Your society sets up its own Stripe and/or PayPal account (free to open, standard rates apply).</li>
                <li>Paste your API keys into SocietyPress's settings.</li>
                <li>Members pay dues, event registrations, and donations through your society's site.</li>
                <li>Money lands in your society's own merchant account within the processor's standard settlement window (typically 2 business days for Stripe, 1 for PayPal).</li>
                <li>Processor deposits to your society's bank account on whatever schedule you've set with them.</li>
            </ol>
            <p>
                <strong>What doesn't happen:</strong> no SaaS platform sits in
                the middle, collects the money first, takes a cut, and pays you
                out later. Every dollar a member pays goes directly to your
                merchant account.
            </p>
        </section>

        <section class="onepager__section">
            <h2>What you can track</h2>

            <div class="onepager__features">

                <div class="onepager__feature">
                    <h3>Dues &amp; renewals</h3>
                    <p>Every membership transaction with date, amount, member name, tier, and payment method. Filterable by date range, status, and tier.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Event registrations</h3>
                    <p>Paid event tickets, attendee count, refunds. Reconcilable line-item against your Stripe/PayPal dashboard by transaction ID.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Donations</h3>
                    <p>Dedicated donation ledger with campaign attribution, recurring-gift tracking, and donor acknowledgement reports for IRS letters.</p>
                </div>

                <div class="onepager__feature">
                    <h3>Store orders</h3>
                    <p>Society merchandise sales (polo shirts, lapel pins, printed publications) with inventory and fulfillment tracking.</p>
                </div>

            </div>
        </section>

        <section class="onepager__section">
            <h2>Exports for reconciliation</h2>
            <p>
                Everything exports to CSV, formatted so your accounting
                software (QuickBooks, Wave, etc.) can import it cleanly:
            </p>
            <ul class="onepager__list">
                <li>Dues transactions &mdash; member, date, amount, tier, payment method, Stripe/PayPal transaction ID</li>
                <li>Event registration revenue &mdash; event, attendee, amount, refund status</li>
                <li>Donation ledger &mdash; donor, date, amount, campaign, recurring vs one-time, gift-aid/receipt status</li>
                <li>Store orders &mdash; order number, items, total, shipping, fulfillment status</li>
            </ul>
            <p>
                Every CSV row carries the processor's transaction ID so a
                line in your books maps to a line in your Stripe or PayPal
                dashboard.
            </p>
        </section>

        <section class="onepager__section onepager__section--highlighted">
            <h2>Treasurer-specific role template</h2>
            <p>
                SocietyPress ships with a pre-built <strong>Treasurer</strong>
                role template that gives you exactly the access you need &mdash;
                financial reports, donation ledger, transaction history, member
                payment status &mdash; and nothing you don't. Other officers
                can manage their own areas without seeing financial detail.
            </p>
        </section>

        <section class="onepager__section">
            <h2>What changes at tax / audit time</h2>
            <ul class="onepager__list">
                <li><strong>Donor receipts:</strong> annual donor letters generate from the donation ledger with a click.</li>
                <li><strong>990 prep:</strong> export revenue by category (dues / events / donations / store) for lines 1&ndash;12 of Form 990-EZ in one CSV.</li>
                <li><strong>Audit trail:</strong> every transaction preserves the processor's ID and timestamp. If your auditor asks &ldquo;what is this $75?&rdquo;, you can trace it to a specific member and a specific Stripe charge in 10 seconds.</li>
                <li><strong>PCI scope:</strong> card numbers never touch your site. Stripe and PayPal handle the card data; SocietyPress only stores the transaction metadata. Your PCI responsibility is essentially zero.</li>
            </ul>
        </section>

        <footer class="onepager__footer">
            <p>
                <strong>See the treasurer views on the demo:</strong>
                <a href="https://demo.getsocietypress.org">demo.getsocietypress.org</a>
                (log in with the treasurer demo account to see only the
                financial surfaces)
            </p>
            <p>
                <strong>Finance-specific questions:</strong>
                <a href="mailto:hello@getsocietypress.org">hello@getsocietypress.org</a>
            </p>
        </footer>

    </div>
</section>

<?php get_footer(); ?>
