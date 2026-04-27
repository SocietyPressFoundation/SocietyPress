<?php
/**
 * Module Documentation Page Template (page-module-docs.php)
 *
 * Renders SocietyPress module guides from the canonical Markdown files
 * at sp-docs-source/modules/*.md on the server. Lives at
 * /docs/modules/ — without a `?guide=` query param it renders an index
 * of all available module guides; with a slug it renders that guide.
 *
 * The Markdown is the source of truth. This template reads, renders
 * through gsp_render_simple_markdown() (defined in
 * page-ens-migration-guide.php and shared), caches the result in a
 * transient.
 *
 * @package getsocietypress
 * @version 0.01d
 */

defined( 'ABSPATH' ) || exit;

// Required for the Markdown renderer; the ENS guide template defines it
// but we may not have been loaded after that template. Pull it in.
if ( ! function_exists( 'gsp_render_simple_markdown' ) ) {
    require_once get_template_directory() . '/page-ens-migration-guide.php';
}

$docs_dir = ABSPATH . '../sp-docs-source/modules/';
$requested = isset( $_GET['guide'] ) ? sanitize_file_name( $_GET['guide'] ) : '';
$requested = preg_replace( '/[^a-z0-9_-]/', '', strtolower( $requested ) );

$file_path = $requested ? $docs_dir . $requested . '.md' : '';
$has_file  = $requested && file_exists( $file_path );

get_header();
?>

<section class="page-hero md-hero">
    <div class="container">
        <div class="page-hero__content">
            <?php if ( $has_file ) :
                // Pull the title from the first H1 of the markdown
                $first_line = trim( strtok( file_get_contents( $file_path ), "\n" ) );
                $title = ltrim( $first_line, '# ' );
                ?>
                <p class="md-breadcrumb"><a href="<?php echo esc_url( home_url( '/docs/modules/' ) ); ?>">← All module guides</a></p>
                <h1><?php echo esc_html( $title ); ?></h1>
            <?php else : ?>
                <h1>Module Guides</h1>
                <p>One guide per SocietyPress module. Plain English, written for the volunteer who runs the website.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="md-section">
    <div class="container">
        <?php if ( $has_file ) :
            $cache_key = 'gsp_module_doc_' . md5( $requested . '|' . filemtime( $file_path ) );
            $rendered  = get_transient( $cache_key );
            if ( false === $rendered ) {
                $md = file_get_contents( $file_path );
                // Strip the first H1 since the page hero already shows it
                $md = preg_replace( '/^# .+?\n+/', '', $md, 1 );
                // Heading offset 1 — H1 in markdown becomes H2 in the rendered page
                $rendered = gsp_render_simple_markdown( $md, 1 );
                set_transient( $cache_key, $rendered, HOUR_IN_SECONDS );
            }
            echo '<article class="md-article">' . $rendered . '</article>';

        else :
            // Index view — list every .md file in the modules folder
            $files = is_dir( $docs_dir ) ? glob( $docs_dir . '*.md' ) : [];

            // Build [ slug => [ title, summary ] ]
            $guides = [];
            foreach ( $files as $f ) {
                $slug = basename( $f, '.md' );
                if ( $slug === 'README' ) continue;
                $contents = file_get_contents( $f );
                if ( ! $contents ) continue;
                $lines = explode( "\n", trim( $contents ) );
                $title = ltrim( $lines[0] ?? $slug, '# ' );
                $summary = '';
                foreach ( array_slice( $lines, 1 ) as $line ) {
                    $line = trim( $line );
                    if ( $line && $line[0] !== '#' && $line !== '---' ) {
                        $summary = $line;
                        break;
                    }
                }
                $guides[ $slug ] = [ 'title' => $title, 'summary' => $summary ];
            }
            ksort( $guides );
            ?>

            <?php if ( empty( $guides ) ) : ?>
                <p class="md-empty">No module guides have been deployed yet.</p>
            <?php else : ?>
                <div class="md-grid">
                    <?php foreach ( $guides as $slug => $info ) :
                        $url = esc_url( add_query_arg( 'guide', $slug, home_url( '/docs/modules/' ) ) );
                        ?>
                        <a class="md-card" href="<?php echo $url; ?>">
                            <h3><?php echo esc_html( $info['title'] ); ?></h3>
                            <p><?php echo esc_html( wp_trim_words( $info['summary'], 28 ) ); ?></p>
                            <span class="md-card__more">Read the guide →</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();
