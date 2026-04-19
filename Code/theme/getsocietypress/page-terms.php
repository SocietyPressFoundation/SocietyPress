<?php
/**
 * Terms of Use Page Template (page-terms.php)
 *
 * Terms for using the getsocietypress.org marketing website and for
 * downloading the SocietyPress software. Software itself is governed by
 * the GPL v2 — these terms cover the website and the download experience.
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
            <h1 class="page-hero__title">Terms of Use</h1>
            <p class="page-hero__subtitle">The short version: it's free, it's yours, and we're not liable if it breaks.</p>
        </div>
    </div>
</section>

<section class="legal-page section">
    <div class="container container--narrow">

        <p class="legal-page__updated">
            <strong>Last updated:</strong> April 15, 2026
        </p>

        <h2>The software</h2>

        <p>
            The SocietyPress plugin and themes are released under the
            <strong>GNU General Public License, version 2 or later (GPL-2.0-or-later)</strong>.
            The full text of the license is included in every download. In
            plain terms, you can:
        </p>

        <ul>
            <li>Use SocietyPress for any purpose, commercial or otherwise.</li>
            <li>Modify the source code however you like.</li>
            <li>Redistribute the software, with or without changes.</li>
            <li>Share your modifications with others.</li>
        </ul>

        <p>
            The one condition the GPL asks of you: if you distribute a
            modified version, your modifications must carry the same license
            so they stay free. That's what keeps the software a community
            resource.
        </p>

        <h2>The website</h2>

        <p>
            The content on getsocietypress.org &mdash; documentation, guides,
            blog posts, marketing copy &mdash; is provided for your reference.
            You're welcome to quote it, link to it, and share it. If you want
            to republish significant portions in print or on another
            website, drop us a line first: we'll almost certainly say yes,
            and we appreciate the courtesy of being asked.
        </p>

        <h2>No warranty</h2>

        <p>
            SocietyPress is free software, provided as-is. It comes with
            absolutely no warranty &mdash; not for correctness, not for
            fitness for any particular purpose, not for uninterrupted
            operation, not for anything. The GPL spells this out in greater
            detail, but the short version is: this is community software,
            built by volunteers, and you run it at your own risk.
        </p>

        <p>
            We test SocietyPress thoroughly and try hard to ship reliable
            code. But software has bugs, society data is important, and you
            are the one running the server. <strong>Back up your database
            before every upgrade.</strong> The plugin includes one-click
            exports for exactly this reason.
        </p>

        <h2>No liability</h2>

        <p>
            By using this website or downloading SocietyPress, you agree
            that the project maintainers and contributors are not liable
            for any loss, damage, data corruption, downtime, lost revenue,
            or other claim arising from use of the software or the website.
            This is standard open-source practice and is not optional.
        </p>

        <h2>Third-party services</h2>

        <p>
            SocietyPress optionally integrates with third-party services
            (Stripe, PayPal, Open Library, Google Fonts). Your use of those
            services is governed by their own terms, not ours. We do not
            warrant their availability or the quality of their service.
        </p>

        <h2>Domain and trademarks</h2>

        <p>
            &ldquo;SocietyPress&rdquo; and the SocietyPress logo mark are
            non-registered project marks. You're welcome to refer to the
            software by name (&ldquo;We use SocietyPress&rdquo; is fine), to
            screenshot the admin interface for training or reviews, and to
            mention the project in your society's materials. Please don't
            imply endorsement by the project of your own products or services.
        </p>

        <h2>Changes</h2>

        <p>
            These terms may be updated. Material changes will be dated. If
            you have an active relationship with the project (you've donated,
            you've contributed code, you're featured on the showcase) we'll
            reach out directly about anything that might affect you.
        </p>

        <h2>Questions</h2>

        <p>
            <a href="mailto:hello@getsocietypress.org">hello@getsocietypress.org</a>
        </p>

    </div>
</section>

<?php get_footer(); ?>
