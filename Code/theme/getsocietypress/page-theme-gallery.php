<?php
/**
 * Theme Gallery Page Template (page-theme-gallery.php)
 *
 * Public catalog of SocietyPress theme presets. Each preset is a small
 * JSON file in the theme's presets/ folder; this template reads the
 * directory, parses each one, and renders a card with a palette swatch
 * preview and a download link. Importer on a SocietyPress install
 * accepts the JSON via SocietyPress → Theme Presets → Import.
 *
 * Sections:
 * 1. Hero — "Pick a look. Make it yours."
 * 2. Preset grid — auto-generated from /presets/*.json
 * 3. How it works
 * 4. Submit-your-preset CTA
 *
 * @package getsocietypress
 * @version 0.01d
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read all preset JSON files from the theme's presets folder.
 */
function gsp_get_theme_presets(): array {
    $dir = get_template_directory() . '/presets/';
    if ( ! is_dir( $dir ) ) return [];

    $files = glob( $dir . '*.json' );
    $presets = [];
    foreach ( $files as $file ) {
        $raw = @file_get_contents( $file );
        if ( ! $raw ) continue;
        $payload = json_decode( $raw, true );
        if ( ! is_array( $payload ) ) continue;
        if ( ( $payload['format'] ?? '' ) !== 'societypress.preset.v1' ) continue;

        $payload['_filename'] = basename( $file );
        $presets[] = $payload;
    }
    // Sort by name
    usort( $presets, fn( $a, $b ) => strcasecmp( $a['name'] ?? '', $b['name'] ?? '' ) );
    return $presets;
}

$presets = gsp_get_theme_presets();

get_header();
?>

<section class="feat-hero tg-hero">
    <div class="container">
        <div class="feat-hero__content">
            <div class="hero__badge"><?php echo esc_html( count( $presets ) ); ?> <?php echo count( $presets ) === 1 ? esc_html__( 'preset', 'getsocietypress' ) : esc_html__( 'presets', 'getsocietypress' ); ?></div>
            <h1 class="feat-hero__title">
                Pick a look.<br>
                <span>Make it yours.</span>
            </h1>
            <p class="feat-hero__subtitle">
                A theme preset is a small JSON file with a society's chosen palette, fonts, spacing, and layout. Import one on your SocietyPress site and it instantly takes on that look — your content, members, and configuration are not touched.
            </p>
            <div class="feat-hero__actions">
                <a href="#presets" class="btn btn-primary btn-lg">Browse the Gallery</a>
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>" class="btn btn-secondary btn-lg">How presets work</a>
            </div>
        </div>
    </div>
</section>

<section class="tg-section" id="presets">
    <div class="container">
        <div class="section-header">
            <h2>Available presets</h2>
            <p>Each preset is curated and reviewed. Click <em>Download</em>, then in your SocietyPress admin go to <strong>SocietyPress → Theme Presets → Import</strong> and upload the file.</p>
        </div>

        <?php if ( empty( $presets ) ) : ?>
            <p class="tg-empty">No presets in the gallery yet. Submit yours to be the first.</p>
        <?php else : ?>
            <div class="tg-grid">
                <?php foreach ( $presets as $preset ) :
                    $tokens = $preset['tokens'] ?? [];
                    $download_url = esc_url( get_template_directory_uri() . '/presets/' . $preset['_filename'] );
                    ?>
                    <article class="tg-card">
                        <div class="tg-swatches">
                            <span class="tg-swatch" style="background:<?php echo esc_attr( $tokens['design_color_primary'] ?? '#0d1f3c' ); ?>;"></span>
                            <span class="tg-swatch" style="background:<?php echo esc_attr( $tokens['design_color_accent'] ?? '#c9973a' ); ?>;"></span>
                            <span class="tg-swatch" style="background:<?php echo esc_attr( $tokens['design_color_header_bg'] ?? '#0d1f3c' ); ?>;"></span>
                            <span class="tg-swatch" style="background:<?php echo esc_attr( $tokens['design_color_footer_bg'] ?? '#0d1f3c' ); ?>;"></span>
                            <span class="tg-swatch" style="background:<?php echo esc_attr( $tokens['design_color_footer_link'] ?? '#c9973a' ); ?>;"></span>
                        </div>
                        <h3 class="tg-name"><?php echo esc_html( $preset['name'] ?? 'Untitled preset' ); ?></h3>
                        <p class="tg-by"><?php printf( esc_html__( 'by %s', 'getsocietypress' ), esc_html( $preset['exported_by'] ?? 'Anonymous' ) ); ?></p>
                        <p class="tg-desc"><?php echo esc_html( $preset['description'] ?? '' ); ?></p>
                        <p class="tg-fonts">
                            <strong>Body:</strong> <?php echo esc_html( gsp_preset_font_label( $tokens['design_font_body'] ?? 'system' ) ); ?>
                            &nbsp;·&nbsp;
                            <strong>Headings:</strong> <?php echo esc_html( gsp_preset_font_label( $tokens['design_font_heading'] ?? 'system' ) ); ?>
                        </p>
                        <a href="<?php echo $download_url; ?>" download class="btn btn-primary tg-download">Download Preset →</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="tg-section tg-section--alt">
    <div class="container">
        <div class="section-header">
            <h2>How it works</h2>
            <p>Three steps from finding a look you like to running it on your site.</p>
        </div>
        <div class="tg-how-grid">
            <div class="tg-how-step">
                <div class="tg-how-num">1</div>
                <h3>Pick a preset</h3>
                <p>Browse the gallery above. Each card shows the palette, fonts, and a description of the look. Click <em>Download Preset</em> to save the JSON file.</p>
            </div>
            <div class="tg-how-step">
                <div class="tg-how-num">2</div>
                <h3>Import it</h3>
                <p>On your SocietyPress site, go to <strong>SocietyPress → Theme Presets → Import</strong>. Upload the JSON file. The site immediately takes on the new look.</p>
            </div>
            <div class="tg-how-step">
                <div class="tg-how-num">3</div>
                <h3>Make it yours</h3>
                <p>Your content, members, logo, and configuration are unchanged — only the design tokens. Tweak any setting under SocietyPress → Settings → Design from there.</p>
            </div>
        </div>
        <p class="tg-safety">
            <strong>What's not in a preset:</strong> No logo. No member data. No content. No org info. No payment credentials. No PHP code or files. A preset is just colors, fonts, and spacing — open it in any text editor before importing if you want to see exactly what you're getting.
        </p>
    </div>
</section>

<section class="tg-cta">
    <div class="container">
        <div class="tg-cta__content">
            <h2>Got a look you'd like to share?</h2>
            <p>Submit your preset for the gallery. Reviewed presets get listed here for other societies to discover.</p>
            <div class="cmp-cta__actions">
                <a href="<?php echo esc_url( home_url( '/feedback/' ) ); ?>" class="btn btn-primary btn-lg">Submit Your Preset</a>
                <a href="<?php echo esc_url( home_url( '/comparison/' ) ); ?>" class="btn btn-secondary btn-lg">See the comparison</a>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();


/**
 * Translate a font slug back into a human-readable label for display.
 */
function gsp_preset_font_label( string $slug ): string {
    $labels = [
        'system'       => 'System',
        'georgia'      => 'Georgia',
        'palatino'     => 'Palatino',
        'garamond'     => 'Garamond',
        'merriweather' => 'Merriweather',
        'lora'         => 'Lora',
        'roboto'       => 'Roboto',
        'open-sans'    => 'Open Sans',
        'source-sans'  => 'Source Sans',
        'nunito'       => 'Nunito',
        'inherit'      => 'Same as body',
    ];
    return $labels[ $slug ] ?? ucfirst( $slug );
}
