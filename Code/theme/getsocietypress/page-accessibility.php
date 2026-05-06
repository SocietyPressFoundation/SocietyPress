<?php
/**
 * Accessibility Statement Page Template (page-accessibility.php)
 *
 * Honest accessibility statement: target, conformance claims, known gaps,
 * how to report issues. Kept up-to-date by editing this template — not
 * the WP editor — so it lives alongside the code and version bumps.
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
            <h1 class="page-hero__title">Accessibility</h1>
            <p class="page-hero__subtitle">
                Our commitments, our known gaps, and how to report an issue.
            </p>
        </div>
    </div>
</section>

<section class="legal-page section">
    <div class="container container--narrow">

        <p class="legal-page__updated">
            <strong>Last updated:</strong> May 5, 2026
        </p>

        <h2>Our target</h2>

        <p>
            SocietyPress aims for conformance with <strong>WCAG 2.1 Level AA</strong>
            across both the plugin's admin interface and the public-facing
            pages it renders. We're committed to that standard for three
            reasons:
        </p>

        <ul>
            <li>
                <strong>Society members span every age and ability.</strong>
                Genealogical research is a passion that attracts people from
                every walk of life. Your members deserve a website they can
                actually use.
            </li>
            <li>
                <strong>Volunteer administrators deserve the same.</strong>
                The people running your society's website are often retirees,
                people with variable vision, and people using older hardware
                or screen magnification. The admin interface has to work for
                them too.
            </li>
            <li>
                <strong>Accessible software is better software.</strong>
                The same design decisions that help people with disabilities
                &mdash; clear headings, strong color contrast, meaningful
                labels &mdash; also make the software easier for everyone.
            </li>
        </ul>

        <h2>What we've done</h2>

        <ul>
            <li>Semantic HTML throughout &mdash; actual headings, actual lists, actual landmarks.</li>
            <li>Keyboard navigation for every interactive element on the public pages and every admin screen.</li>
            <li>Focus states that are visible, not suppressed.</li>
            <li>Color contrast checked against WCAG AA for body text and UI controls.</li>
            <li>Form labels associated with inputs; no placeholder-only fields.</li>
            <li>Announcement bars and modals use appropriate ARIA roles and are dismissible.</li>
            <li>The plugin's design panel lets every society customize colors &mdash; so if the default palette doesn't work for your audience, you can adjust it without touching code.</li>
            <li>Vanilla JavaScript only &mdash; no frameworks that bury accessibility behind abstractions.</li>
        </ul>

        <h2>Where we're not there yet</h2>

        <p>
            We believe in naming our known gaps:
        </p>

        <ul>
            <li>
                <strong>Screen reader testing is not exhaustive.</strong> We
                test with VoiceOver on macOS regularly, but NVDA and JAWS
                coverage is lighter. Reports from screen-reader users are
                especially welcome.
            </li>
            <li>
                <strong>The page builder drag-and-drop is mouse-primary.</strong>
                Keyboard alternatives exist for every widget action (you can
                reorder widgets via the &ldquo;Move up / Move down&rdquo;
                buttons), but the drag interaction itself assumes pointer
                input.
            </li>
            <li>
                <strong>Some admin data tables are dense.</strong> WP_List_Table
                bulk-action checkboxes and sort arrows are WordPress core
                defaults, and their accessibility is only as good as core's.
                We avoid building on top of them where we can.
            </li>
            <li>
                <strong>PDF newsletter previews</strong> depend on the uploaded
                PDF being accessible. We can't fix source documents, but we
                do display member-readable text alternatives where they
                exist.
            </li>
        </ul>

        <h2>How to report an issue</h2>

        <p>
            If you hit an accessibility barrier on this website or inside a
            SocietyPress admin interface, please tell us. A short email is
            more valuable than a polished bug report &mdash; we'd rather know
            than not.
        </p>

        <p>
            Email: <a href="mailto:accessibility@getsocietypress.org">accessibility@getsocietypress.org</a>
        </p>

        <p>
            Include anything you can: the page URL, what you were trying to
            do, what happened, and (if you're comfortable sharing) the
            assistive technology you're using. We aim to reply within two
            business days and will credit you if you'd like to be credited.
        </p>

        <h2>Compliance</h2>

        <p>
            This statement is provided as a voluntary accessibility
            commitment. SocietyPress is free software distributed under the
            GPL v2 with no warranty, and this statement does not create any
            contractual obligation. That said, we take this seriously, and
            if a society running SocietyPress faces accessibility compliance
            requirements (ADA, Section 508, EN 301 549), we'll do what we
            can to help.
        </p>

    </div>
</section>

<?php get_footer(); ?>
