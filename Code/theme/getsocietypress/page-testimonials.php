<?php
/**
 * Testimonials Page Template (page-testimonials.php)
 *
 * Two-mode page: when the testimonials array below is empty, the page
 * honestly says "we're still collecting these" with a clear intake path.
 * When entries exist, it renders them as cards with attribution.
 *
 * Add new testimonials to the $gsp_testimonials array below as they
 * come in. Keep them verbatim with explicit permission; the whole
 * point is authenticity.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

get_header();

/*
 * Testimonials array. Each entry: quote, name, role (optional), society,
 * location (optional), and optional URL for the society's public site.
 * Add verbatim quotes only — never paraphrased. Include date the quote
 * was submitted so we can retire stale ones later.
 */
$gsp_testimonials = array(

    // Empty array at launch — the honest-empty branch below renders.
    // Example structure for when real testimonials arrive:
    //
    // array(
    //     'quote'    => 'The actual words the person said, verbatim.',
    //     'name'     => 'First Last',
    //     'role'     => 'President',                         // optional
    //     'society'  => 'Example Genealogical Society',
    //     'location' => 'Anywhere, TX',                      // optional
    //     'url'      => 'https://examplesociety.org',        // optional
    //     'date'     => '2026-05-01',                        // submission date
    // ),

);
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <h1 class="page-hero__title">Testimonials</h1>
            <p class="page-hero__subtitle">
                What societies running SocietyPress have to say about it
                &mdash; in their own words.
            </p>
        </div>
    </div>
</section>

<?php if ( ! empty( $gsp_testimonials ) ) : ?>

<!-- ==========================================================================
     TESTIMONIALS GRID
     Rendered only when real entries exist.
     ========================================================================== -->
<section class="testimonials section">
    <div class="container">

        <div class="testimonials__grid">

            <?php foreach ( $gsp_testimonials as $t ) : ?>
                <figure class="testimonial-card">

                    <blockquote class="testimonial-card__quote">
                        <?php echo wp_kses_post( $t['quote'] ); ?>
                    </blockquote>

                    <figcaption class="testimonial-card__attribution">
                        <div class="testimonial-card__name">
                            <?php echo esc_html( $t['name'] ); ?>
                        </div>

                        <?php if ( ! empty( $t['role'] ) ) : ?>
                            <div class="testimonial-card__role">
                                <?php echo esc_html( $t['role'] ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="testimonial-card__society">
                            <?php if ( ! empty( $t['url'] ) ) : ?>
                                <a href="<?php echo esc_url( $t['url'] ); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html( $t['society'] ); ?>
                                </a>
                            <?php else : ?>
                                <?php echo esc_html( $t['society'] ); ?>
                            <?php endif; ?>
                            <?php if ( ! empty( $t['location'] ) ) : ?>
                                <span class="testimonial-card__location"> &middot; <?php echo esc_html( $t['location'] ); ?></span>
                            <?php endif; ?>
                        </div>

                    </figcaption>

                </figure>
            <?php endforeach; ?>

        </div>

    </div>
</section>

<?php else : ?>

<!-- ==========================================================================
     EMPTY STATE — honest "we're collecting these" invitation
     ========================================================================== -->
<section class="testimonials-empty section">
    <div class="container container--narrow">

        <div class="testimonials-empty__content">

            <div class="testimonials-empty__icon" aria-hidden="true">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>
            </div>

            <h2>No testimonials yet &mdash; we won't fake them</h2>

            <p>
                SocietyPress is new, and we're seeking early adopters.
                Real quotes from real societies arrive after real
                societies use the software for a while. We'd rather this
                page sit empty for a few months than fill it with
                stock-photo quotes and invented personas.
            </p>

            <p>
                If you're considering being one of the first societies to
                switch, the live demo is the best way to see whether
                SocietyPress fits.
            </p>

            <p class="testimonials-empty__cta">
                <a href="https://demo.getsocietypress.org" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                    Try the demo &rarr;
                </a>
            </p>

            <p>
                Already running SocietyPress? Even mixed or critical
                feedback is welcome &mdash; honest reviews of free
                software beat glowing ones for credibility.
            </p>

        </div>

    </div>
</section>

<?php endif; ?>

<!-- ==========================================================================
     INTAKE — how to submit a testimonial
     Same block whether the page is empty or populated.
     ========================================================================== -->
<section class="testimonials-intake section">
    <div class="container container--narrow">

        <h2>Share your experience</h2>

        <p>
            If your society is using SocietyPress and you'd like to say
            something about it publicly, email a few sentences to
            <a href="mailto:testimonials@getsocietypress.org">testimonials@getsocietypress.org</a>.
            Include:
        </p>

        <ul class="testimonials-intake__list">
            <li>Your name and role (President, webmaster, etc.)</li>
            <li>Your society's name and general location</li>
            <li>A link to your society's site, if it's public</li>
            <li>Whether you're comfortable with us publishing your quote with attribution (we won't publish anonymous ones &mdash; readers distrust them)</li>
            <li>The quote itself &mdash; three or four sentences is plenty</li>
        </ul>

        <p>
            We publish quotes verbatim. We don't polish, paraphrase, or
            combine multiple quotes into a more marketable one. If your
            experience has been mixed, that's valid too &mdash; honest
            reviews of free software beat glowing ones for credibility.
        </p>

        <p class="testimonials-intake__footnote">
            Prefer to talk it through first? Reach out through the
            <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">contact page</a>.
        </p>

    </div>
</section>

<?php get_footer(); ?>
