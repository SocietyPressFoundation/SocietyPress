<?php
/**
 * Theme Review Policy Page Template (page-theme-review-policy.php)
 *
 * The public statement of what the Theme Exchange accepts, what it refuses,
 * and what the "Reviewed by SocietyPress" badge does and does not mean.
 * Linked from the plugin's Theme Presets screen and from the Theme Gallery.
 *
 * Kept in step with docs/THEME-REVIEW-POLICY.md in the repository.
 *
 * @package getsocietypress
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Theme Exchange &mdash; review policy</h1>
            <p class="page-hero__subtitle">
                What is accepted, what is refused, and what the
                &ldquo;Reviewed by SocietyPress&rdquo; badge is actually a
                statement about.
            </p>
        </div>
    </div>
</section>

<section class="legal-page section">
    <div class="container container--narrow">

        <h2>What the Exchange is</h2>

        <p>
            Societies make their sites look like their own choosing, and then
            have no way to lend that work to the society three counties over
            that admires it. The Theme Exchange is that lending shelf.
        </p>

        <p>It has three tiers, and they are three different levels of trust.</p>

        <p>
            <strong>Tier 1 &mdash; a saved look.</strong> Colors, fonts,
            spacing, sizes. A file of settings and nothing else. The worst a
            bad one does is make a site ugly, and one click puts it back.
            No review.
        </p>

        <p>
            <strong>Tier 2 &mdash; a bundle.</strong> A saved look plus a
            stylesheet and image files, in a <code>.spchildtheme</code>
            archive. SocietyPress strips anything from the stylesheet that is
            not styling and accepts only image files alongside it. A bundle
            cannot <em>do</em> anything; it can only look like something.
            No review.
        </p>

        <p>
            <strong>Tier 3 &mdash; a child theme.</strong> A full WordPress
            theme. This page is about Tier 3 and only Tier 3.
        </p>

        <p>
            All three tiers are open from the day the Exchange is. Tier 3's
            queue may be slow, and how slow is visible to everybody &mdash;
            but it is not closed, and was not held back until the rest had
            proved itself.
        </p>

        <h2>Why Tier 3 is different</h2>

        <p>
            A WordPress theme contains executable code. It runs on the
            society's own server, with the site's own permissions, every time
            a page loads. A good one gives a society something genuinely its
            own. A bad one can read the member list.
        </p>

        <p>
            That is the whole reason for a review. Nothing about a theme's
            appearance can be dangerous. Everything about its code can be.
        </p>

        <h2>What the badge means, and what it does not</h2>

        <p>
            A theme that passes review carries
            <strong>Reviewed by SocietyPress</strong> where a society can see
            it before installing.
        </p>

        <p>
            It means: a person read the theme's code, start to finish, at the
            version being offered, and found nothing that reaches outside
            making the site look a certain way.
        </p>

        <p>
            It does not mean the theme is well built, that it will suit your
            society, that it will keep working after the next WordPress
            release, or that anybody is obliged to maintain it. It is a
            statement about safety and nothing else.
        </p>

        <p>
            <strong>Who reviews.</strong> Today, we do, personally. There is
            no review board, and this policy says so plainly rather than
            implying a committee that does not exist. If that changes, this
            section changes with it.
        </p>

        <h2>What is accepted</h2>

        <p>A theme is accepted when all of the following are true.</p>

        <ol>
            <li><strong>It is a child theme of the SocietyPress parent theme.</strong> Not a standalone theme, not a child of something else.</li>
            <li><strong>It is licensed GPL-2.0-or-later</strong> &mdash; the free license that lets anybody use, change and pass on the code &mdash; like everything else here, and says so.</li>
            <li><strong>Its code does presentation and nothing else.</strong> Templates, styling, and the small amount of PHP it takes to arrange them.</li>
            <li><strong>Every asset it ships is in the archive.</strong> No fonts, scripts, styles, or images fetched from another site at page load.</li>
            <li><strong>It names a person or a society as its author</strong>, with a working contact address.</li>
            <li><strong>It installs on a clean site and works</strong> without instructions beyond &ldquo;activate it.&rdquo;</li>
        </ol>

        <h2>What is denied</h2>

        <p>Any one of these ends the review. There is no partial pass.</p>

        <ul>
            <li><strong>Anything that sends data anywhere.</strong> No analytics, no phoning home, no telemetry, however anonymous, however well meant.</li>
            <li><strong>Anything that fetches code at runtime</strong> &mdash; remote scripts, remote stylesheets, fonts loaded from a third party, an updater of its own.</li>
            <li><strong>Anything that reads what it has no business reading.</strong> Member records, payment records, user accounts, settings unrelated to appearance.</li>
            <li><strong>Anything that writes outside its own settings.</strong> Creating pages, changing options, adding users, touching another plugin's data.</li>
            <li><strong>Obscured code.</strong> Minified PHP, encoded strings, anything a reviewer cannot simply read. Not because it is necessarily malicious, but because it cannot be reviewed, and an unreviewable theme cannot carry a badge that says it was.</li>
            <li><strong>Advertising.</strong> No links, credits, or branding that a society cannot remove.</li>
            <li><strong>Anything that alters SocietyPress's own behavior</strong> rather than its appearance.</li>
        </ul>

        <h2>What SocietyPress itself sends</h2>

        <p>
            A fair question, given the line above about themes: SocietyPress
            does phone home, and a theme may not.
        </p>

        <p>
            Once a week an install tells us three things &mdash; the society's
            name, its website address, and which version it is running. Nothing
            else, and nothing about any person. All three are already on the
            society's own public homepage. There is no setting to turn it off,
            and the Privacy screen says so in those words.
        </p>

        <p>
            We keep tabs on who is using our software. It is how we know which
            versions are still out there and how far an announcement reaches.
        </p>

        <p>
            A theme is held to a stricter rule than the plugin because the two
            are not in the same position. A society chooses SocietyPress, is
            told plainly what it sends, and can decline by not installing it. A
            theme is a passenger inside a site that has already made that
            decision, and it arrives from somebody the society has never heard
            of.
        </p>

        <h2>What is asked for but not required</h2>

        <p>
            These do not decide acceptance. They are said out loud because
            they are what separates a theme somebody uses from a theme
            somebody tries once.
        </p>

        <ul>
            <li>Works on a phone.</li>
            <li>Readable contrast, and text that survives being enlarged.</li>
            <li>Sensible behavior when a society has no logo, no hero image, or no events.</li>
            <li>A screenshot that shows what it actually looks like.</li>
        </ul>

        <h2>When a reviewed theme fails approval</h2>

        <ol>
            <li><strong>The badge is withdrawn immediately</strong>, before anybody is contacted and before anything is explained. A badge in doubt is a badge removed.</li>
            <li><strong>The theme is delisted</strong> from the Exchange the same day.</li>
            <li><strong>Societies running it are told</strong>, by name, what was found and what to do. Not a changelog line &mdash; a message that reaches the person who installed it.</li>
            <li><strong>What was found is published</strong>, including the fact that it passed review. A review process that hides its misses is worth less than no review at all.</li>
            <li><strong>The author may submit again</strong>, unless what was found looks deliberate.</li>
        </ol>

        <p>
            A society is never left with a theme it cannot get out of:
            switching back to a SocietyPress theme is one click, and no theme
            in the Exchange is permitted to own anything a society would lose
            by leaving it.
        </p>

        <h2>Who may submit</h2>

        <p><strong>Anybody.</strong></p>

        <p>
            Not only societies, not only members, not only people we know.
            SocietyPress is GPL &mdash; a free license that lets anybody use and
            change the code &mdash; which means anyone may already write a child
            theme and hand it straight to a society without asking us. The
            Exchange has no authority over that and never will. What it
            governs is only which themes carry the badge &mdash; so
            restricting who may submit would not keep one bad theme away from
            one society. It would only mean fewer good ones were ever read.
        </p>

        <p>
            So the question is not who is permitted to write themes. The
            license settled that. The question is whose work SocietyPress will
            put its name on, and the answer is: anyone willing to be named
            alongside it.
        </p>

        <p>
            <strong>A submission names a person.</strong> A working contact
            address, a real name or a real society, published with the theme.
            Anonymous submissions are refused without review. This is the
            whole gate, and it is enough of one: a theme asks a society to run
            its code on the server that holds the members' names, addresses,
            payment history, and family records about living people. Somebody
            has to be answerable for that, and it cannot be us.
        </p>

        <p>
            <strong>One open submission per author at a time.</strong> A
            second is accepted when the first is decided. This is not a
            judgment about anyone's work &mdash; it is the only way one
            reviewer and an open door can coexist.
        </p>

        <p>
            <strong>A review may be declined without a reason.</strong> This
            is not a rejection and is not recorded as one. A rejection means a
            theme failed a line in this document, and we say which line. A
            declined review means only that we are not able to take it on. The
            distinction matters because the alternative is promising to read
            anything anyone ever sends, forever, and that is a promise that
            would be broken quietly rather than kept.
        </p>

        <h2>Submitting</h2>

        <p>
            A submission is an issue on the SocietyPress repository. It joins
            a queue that is public &mdash; everybody can see what is waiting,
            how long it has waited, and what was decided.
        </p>

        <p>
            The review is a checklist drawn from this page, and the completed
            checklist is published with the decision. A rejection says which
            line it failed and what would fix it.
        </p>

        <p>
            <a class="btn btn-primary btn-lg" href="https://github.com/SocietyPressFoundation/SocietyPress/issues/new?template=theme-submission.yml">Submit a theme</a>
            <a class="btn btn-secondary btn-lg" href="https://github.com/SocietyPressFoundation/SocietyPress/issues?q=is%3Aissue+label%3Atheme-submission">See the queue</a>
        </p>

        <h3>How long does a review take?</h3>
        <p>
            There is no promise. A theme is reviewed when someone is able to
            review it. The queue shows how long everything in it has been
            waiting, so nobody has to ask, and repeated requests about timing
            will be ignored.
        </p>

        <h3>What happens to a theme whose author disappears?</h3>
        <p>
            It is unlisted at the next WordPress release that breaks it, once
            SocietyPress is notified.
        </p>

        <h3>Is a re-review required on every update?</h3>
        <p>Only when the code changes.</p>

    </div>
</section>

<?php
get_footer();
