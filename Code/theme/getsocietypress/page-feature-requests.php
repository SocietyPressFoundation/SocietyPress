<?php
/**
 * Feature Requests Page Template (page-feature-requests.php)
 *
 * Where users suggest new features. Email-based intake with a template
 * designed to surface the problem-behind-the-request (not just the
 * surface feature ask).
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

$gsp_fr_subject = rawurlencode( 'Feature request: [short summary]' );
$gsp_fr_body    = rawurlencode(
    "WHAT YOU'RE TRYING TO ACCOMPLISH:\n(Describe the situation or problem, not the feature. 'I want members to see their renewal date' beats 'Add a renewal date field'.)\n\n" .
    "WHY IT MATTERS TO YOUR SOCIETY:\n(How often does this come up? How many members or volunteers are affected?)\n\n" .
    "WHAT YOU DO TODAY:\n(Workaround you're using, or how other tools solve it.)\n\n" .
    "WHAT YOU'D LIKE TO SEE:\n(Your proposed solution — rough is fine.)\n\n" .
    "YOUR SOCIETY:\n(Society name, rough member count, and website if SocietyPress is live — helps us understand the context.)"
);
$gsp_fr_mailto  = 'mailto:ideas@getsocietypress.org?subject=' . $gsp_fr_subject . '&body=' . $gsp_fr_body;
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Request a Feature</h1>
            <p class="page-hero__subtitle">
                The best feature requests come from the societies actually
                running SocietyPress. Tell us what you need.
            </p>
        </div>
    </div>
</section>

<section class="intake-page section">
    <div class="container container--narrow">

        <div class="intake-page__lede">
            <p>
                SocietyPress's feature set came from listening to society
                administrators describe the specific problems they face. The
                next round of features will come the same way. If there's
                something missing that would make your job easier, we want
                to hear about it.
            </p>
        </div>

        <div class="intake-page__cta">
            <a href="<?php echo esc_url( $gsp_fr_mailto ); ?>" class="btn btn-primary btn-xl">
                Open a pre-filled feature request
            </a>
            <p class="intake-page__cta-note">
                This opens your email app with the template already filled in.
            </p>
        </div>

        <h2>What we prioritize</h2>

        <p>Not every request lands in the plugin, and we try to be honest about why. Broadly, features that fit these patterns rise fastest:</p>

        <ul>
            <li>
                <strong>Multiple societies need it.</strong> If three or
                four societies independently describe the same pain, that's
                a signal.
            </li>
            <li>
                <strong>It's core to society operations.</strong> Membership,
                events, records, library, newsletter, governance &mdash; the
                plugin's native territory. One-off integrations get lower
                priority.
            </li>
            <li>
                <strong>It doesn't require ongoing external dependencies.</strong>
                SocietyPress is designed to run on any standard host with
                zero monthly service fees. Features that need a paid API or
                a hosted service compromise that, so they need a very strong
                case.
            </li>
            <li>
                <strong>The data model supports it.</strong> Some requests
                turn out to be easy; others would require re-modeling a core
                table. We'll tell you honestly which yours is.
            </li>
        </ul>

        <h2>What happens after you send</h2>

        <ol>
            <li>We acknowledge your request within a few days.</li>
            <li>We ask clarifying questions if the problem isn't clear enough.</li>
            <li>If it's a fit, it goes on the <a href="<?php echo esc_url( home_url( '/roadmap/' ) ); ?>">public roadmap</a> with a rough priority.</li>
            <li>If it isn't a fit, we'll tell you why &mdash; and often suggest a workaround.</li>
        </ol>

        <h2>Feature requests that are already on the list</h2>

        <p>
            Before you write a new request, skim the
            <a href="<?php echo esc_url( home_url( '/roadmap/' ) ); ?>">public roadmap</a> &mdash;
            your idea may already be tracked. If so, a quick email saying
            &ldquo;we need this too, and here's why&rdquo; still helps us
            prioritize.
        </p>

    </div>
</section>

<?php get_footer(); ?>
