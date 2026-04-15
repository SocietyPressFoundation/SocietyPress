<?php
/**
 * Contact Page Template (page-contact.php)
 *
 * A single place for "how do I reach someone?" — routes inquiries to the
 * right address. Mailto-based per the project notes (a real contact form
 * with server-side handling comes later via the companion plugin).
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
            <h1 class="page-hero__title">Contact</h1>
            <p class="page-hero__subtitle">
                Pick the right path for your question &mdash; it'll get a
                faster answer than a general inquiry.
            </p>
        </div>
    </div>
</section>

<section class="contact-page section">
    <div class="container container--narrow">

        <div class="contact-grid">

            <div class="contact-card">
                <h3>General questions</h3>
                <p>
                    Anything that doesn't fit a more specific path below.
                    Questions about the project, partnership inquiries, or
                    just a hello.
                </p>
                <a href="mailto:hello@getsocietypress.org" class="contact-card__link">
                    hello@getsocietypress.org
                </a>
            </div>

            <div class="contact-card">
                <h3>Bug reports</h3>
                <p>
                    Found something broken? The
                    <a href="<?php echo esc_url( home_url( '/bug-reports/' ) ); ?>">bug reports page</a>
                    has a pre-filled template that covers everything we
                    need to reproduce the issue.
                </p>
                <a href="<?php echo esc_url( home_url( '/bug-reports/' ) ); ?>" class="contact-card__link">
                    Report a bug &rarr;
                </a>
            </div>

            <div class="contact-card">
                <h3>Feature requests</h3>
                <p>
                    Have an idea for what SocietyPress should do next? The
                    <a href="<?php echo esc_url( home_url( '/feature-requests/' ) ); ?>">feature request page</a>
                    makes sure we understand the problem before we start
                    building the solution.
                </p>
                <a href="<?php echo esc_url( home_url( '/feature-requests/' ) ); ?>" class="contact-card__link">
                    Request a feature &rarr;
                </a>
            </div>

            <div class="contact-card">
                <h3>Security vulnerabilities</h3>
                <p>
                    Please do not file security issues through the public
                    bug form. Our
                    <a href="<?php echo esc_url( home_url( '/.well-known/security.txt' ) ); ?>">security.txt</a>
                    has the disclosure address and expected response time.
                </p>
                <a href="mailto:security@getsocietypress.org" class="contact-card__link">
                    security@getsocietypress.org
                </a>
            </div>

            <div class="contact-card">
                <h3>Accessibility</h3>
                <p>
                    Hit a barrier on this site or in the admin? We want to
                    know, and we'll credit you for the fix if you'd like.
                </p>
                <a href="mailto:accessibility@getsocietypress.org" class="contact-card__link">
                    accessibility@getsocietypress.org
                </a>
            </div>

            <div class="contact-card">
                <h3>Migration help</h3>
                <p>
                    If you're a society planning a move from ENS or another
                    platform and want to talk through your specific situation
                    before you start, we're glad to help.
                </p>
                <a href="mailto:migrations@getsocietypress.org" class="contact-card__link">
                    migrations@getsocietypress.org
                </a>
            </div>

            <div class="contact-card">
                <h3>Press &amp; speaking</h3>
                <p>
                    Writing about SocietyPress or looking for a speaker at
                    a conference or society event? Charles speaks on
                    society technology and the open-source approach.
                </p>
                <a href="mailto:press@getsocietypress.org" class="contact-card__link">
                    press@getsocietypress.org
                </a>
            </div>

            <div class="contact-card">
                <h3>Donations &amp; sponsorship</h3>
                <p>
                    Questions about supporting the project, sponsorship
                    recognition, or corporate contributions. The
                    <a href="<?php echo esc_url( home_url( '/donate/' ) ); ?>">donate page</a>
                    handles one-time and recurring gifts directly.
                </p>
                <a href="mailto:hello@getsocietypress.org" class="contact-card__link">
                    hello@getsocietypress.org
                </a>
            </div>

        </div>

        <div class="contact-page__footer">
            <p>
                Response times are typically under three business days. The
                project is maintained by a very small team &mdash; if it
                takes a little longer, it's not that we didn't see your
                note, it's that we're getting to it.
            </p>
        </div>

    </div>
</section>

<?php get_footer(); ?>
