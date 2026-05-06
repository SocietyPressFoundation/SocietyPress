<?php
/**
 * Front Page Template
 *
 * WHY: The front page is the first thing anyone sees. It needs to look
 * polished without Harold having to configure anything. This template
 * renders a full-viewport cinematic hero section (video or image background
 * with overlay, headline, subtitle, and CTA button) above whatever page
 * content exists (including page builder widgets).
 *
 * The hero pulls from SocietyPress settings (homepage_hero_* keys). If no
 * hero is configured, the page renders normally — no empty hero section.
 *
 * Video backgrounds autoplay muted and loop (browser-required for autoplay).
 * On mobile, video falls back to the poster image for bandwidth savings.
 *
 * @package SocietyPress
 */

get_header();

$sp         = get_option( 'societypress_settings', [] );
$hero_type  = $sp['homepage_hero_type']      ?? 'none';
$hero_media = $sp['homepage_hero_media']     ?? '';
$hero_poster = $sp['homepage_hero_poster']   ?? '';
$hero_headline = $sp['homepage_hero_headline'] ?? '';
$hero_subtitle = $sp['homepage_hero_subtitle'] ?? '';
$hero_cta_text = $sp['homepage_hero_cta_text'] ?? '';
$hero_cta_url  = $sp['homepage_hero_cta_url']  ?? '';
$hero_overlay  = (int) ( $sp['homepage_hero_overlay'] ?? 40 );
$hero_height   = $sp['homepage_hero_height']   ?? 'fullscreen';

// Use organization name as default headline if none set
if ( empty( $hero_headline ) ) {
    $hero_headline = $sp['organization_name'] ?? get_bloginfo( 'name' );
}

// Show the hero section if the type isn't 'none'
$show_hero = ( $hero_type !== 'none' );
?>

<?php if ( $show_hero ) : ?>
<!-- ================================================================
     HERO SECTION
     WHY: Full-viewport hero with video or image background creates the
     "wow" first impression. The overlay ensures text is always readable
     regardless of the background media. All values are configurable from
     the Design settings page — Harold never touches code.

     Dynamic style values (background-image URL, overlay opacity) come
     from settings, so they're emitted as CSS custom properties on the
     section root. The actual CSS that consumes them lives in style.css.
     ================================================================ -->
<section class="sp-front-hero <?php echo esc_attr( 'sp-hero-' . $hero_height ); ?>"
         style="<?php
             if ( $hero_media ) {
                 echo '--sp-hero-bg: url(' . esc_url( $hero_media ) . ');';
             }
             echo '--sp-hero-overlay-opacity: ' . esc_attr( $hero_overlay / 100 ) . ';';
         ?>">

    <?php if ( $hero_type === 'video' && $hero_media ) : ?>
        <!-- WHY autoplay muted loop playsinline: All four attributes are required
             for browsers to allow video autoplay without user interaction. Removing
             'muted' will cause autoplay to silently fail in Chrome/Safari. The
             poster image shows while the video loads (and on mobile where video
             may not autoplay). -->
        <video class="sp-front-hero-video"
               autoplay muted loop playsinline aria-hidden="true"
               <?php if ( $hero_poster ) : ?>poster="<?php echo esc_url( $hero_poster ); ?>"<?php endif; ?>>
            <source src="<?php echo esc_url( $hero_media ); ?>"
                    type="video/<?php echo esc_attr( pathinfo( parse_url( $hero_media, PHP_URL_PATH ), PATHINFO_EXTENSION ) ?: 'mp4' ); ?>">
        </video>
    <?php elseif ( $hero_media ) : ?>
        <div class="sp-front-hero-image"></div>
    <?php else : ?>
        <!-- No media configured — use a gradient background as a dignified fallback -->
        <div class="sp-front-hero-image sp-front-hero-gradient"></div>
    <?php endif; ?>

    <!-- Overlay — opacity controlled by the homepage_hero_overlay setting -->
    <div class="sp-front-hero-overlay"></div>

    <div class="sp-front-hero-content">
        <?php if ( $hero_headline ) : ?>
            <h1 class="sp-front-hero-headline"><?php echo esc_html( $hero_headline ); ?></h1>
        <?php endif; ?>

        <?php if ( $hero_subtitle ) : ?>
            <p class="sp-front-hero-subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
        <?php endif; ?>

        <?php if ( $hero_cta_text && $hero_cta_url ) : ?>
            <a href="<?php echo esc_url( $hero_cta_url ); ?>" class="sp-front-hero-cta">
                <?php echo esc_html( $hero_cta_text ); ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Scroll indicator — subtle cue that there's more below -->
    <div class="sp-front-hero-scroll" aria-hidden="true">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </div>
</section>
<?php endif; ?>

<main id="main-content" class="site-content <?php echo $show_hero ? 'sp-front-has-hero' : ''; ?>">

    <?php while ( have_posts() ) : the_post(); ?>

        <?php
        // WHY: If the page uses the page builder template, render widgets via
        // the plugin's sp_render_builder_widgets() function. Otherwise, render
        // standard page content. We skip the page title because the hero
        // section already establishes the page identity.
        $template = get_page_template_slug();
        if ( $template === 'sp-builder' && function_exists( 'sp_render_builder_widgets' ) ) :
            // Enqueue builder frontend styles and scripts
            if ( function_exists( 'sp_builder_frontend_styles' ) ) {
                add_action( 'wp_head', 'sp_builder_frontend_styles', 99 );
            }
            if ( function_exists( 'sp_builder_frontend_scripts' ) ) {
                add_action( 'wp_footer', 'sp_builder_frontend_scripts' );
            }
        ?>
            <div class="content-area-full">
                <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry-content">
                        <?php sp_render_builder_widgets( get_the_ID() ); ?>
                    </div>
                </article>
            </div>
        <?php else : ?>
            <div class="content-area-full">
                <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>
            </div>
        <?php endif; ?>

    <?php endwhile; ?>

</main>

<?php get_footer(); ?>
