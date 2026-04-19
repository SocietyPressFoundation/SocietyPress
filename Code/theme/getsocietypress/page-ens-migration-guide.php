<?php
/**
 * ENS Migration Guide Page Template (page-ens-migration-guide.php)
 *
 * Renders the canonical ENS migration guide (stored as Markdown in
 * docs/ENS-MIGRATION-GUIDE.md on the server) as a styled HTML
 * page. Lives at /docs/ens-migration-guide/ — linked from the
 * /ens-migration/ landing page's "Read the Full Migration Guide" CTA.
 *
 * The Markdown file stays the canonical source so updates land in
 * one place. This template reads it, renders it through a minimal
 * Markdown-to-HTML converter, and caches the result in a transient.
 *
 * @package getsocietypress
 * @version 0.04d
 */

defined( 'ABSPATH' ) || exit;

/*
 * Lightweight Markdown renderer — handles the specific subset used by
 * the ENS guide. Not a full Markdown parser; intentionally narrow so
 * we're not shipping a 500KB library to render one document.
 *
 * Supported:
 *   - ATX headers (# ## ### ####)
 *   - Horizontal rule (---)
 *   - Ordered lists (1. ...)
 *   - Unordered lists (- ...)
 *   - Blockquote (> ...)
 *   - Fenced code block (``` ... ```)
 *   - Inline: **bold**, *italic*, `code`, [link](url), <url>
 *   - Paragraphs
 */
function gsp_render_simple_markdown( $md, $heading_offset = 0 ) {
    $md = str_replace( "\r\n", "\n", $md );
    $lines = explode( "\n", $md );

    $out = array();
    $in_list = null;      // 'ul' or 'ol' or null
    $in_blockquote = false;
    $in_code = false;
    $code_buffer = array();
    $para_buffer = array();

    $flush_para = function() use ( &$para_buffer, &$out ) {
        if ( empty( $para_buffer ) ) return;
        $text = implode( ' ', $para_buffer );
        $out[] = '<p>' . gsp_md_inline( $text ) . '</p>';
        $para_buffer = array();
    };
    $close_list = function() use ( &$in_list, &$out ) {
        if ( $in_list ) {
            $out[] = '</' . $in_list . '>';
            $in_list = null;
        }
    };
    $close_blockquote = function() use ( &$in_blockquote, &$out ) {
        if ( $in_blockquote ) {
            $out[] = '</blockquote>';
            $in_blockquote = false;
        }
    };

    foreach ( $lines as $raw ) {
        $line = rtrim( $raw );

        // Fenced code block boundaries.
        if ( preg_match( '/^```/', $line ) ) {
            if ( $in_code ) {
                $flush_para();
                $close_list();
                $close_blockquote();
                $out[] = '<pre><code>' . esc_html( implode( "\n", $code_buffer ) ) . '</code></pre>';
                $code_buffer = array();
                $in_code = false;
            } else {
                $flush_para();
                $close_list();
                $close_blockquote();
                $in_code = true;
            }
            continue;
        }
        if ( $in_code ) {
            $code_buffer[] = $raw;
            continue;
        }

        // Blank line closes paragraph / list / blockquote.
        if ( '' === trim( $line ) ) {
            $flush_para();
            $close_list();
            $close_blockquote();
            continue;
        }

        // Horizontal rule.
        if ( preg_match( '/^-{3,}\s*$/', $line ) ) {
            $flush_para();
            $close_list();
            $close_blockquote();
            $out[] = '<hr>';
            continue;
        }

        // Headers. Offset lets the caller shift the hierarchy down —
        // useful when the page already has its own <h1> in the hero
        // and the markdown's own top-level heading should become <h2>.
        if ( preg_match( '/^(#{1,6})\s+(.+)$/', $line, $m ) ) {
            $flush_para();
            $close_list();
            $close_blockquote();
            $level = min( 6, strlen( $m[1] ) + $heading_offset );
            $out[] = '<h' . $level . '>' . gsp_md_inline( $m[2] ) . '</h' . $level . '>';
            continue;
        }

        // Blockquote.
        if ( preg_match( '/^>\s?(.*)$/', $line, $m ) ) {
            $flush_para();
            $close_list();
            if ( ! $in_blockquote ) {
                $out[] = '<blockquote>';
                $in_blockquote = true;
            }
            $out[] = '<p>' . gsp_md_inline( $m[1] ) . '</p>';
            continue;
        }

        // Ordered list.
        if ( preg_match( '/^\d+\.\s+(.+)$/', $line, $m ) ) {
            $flush_para();
            $close_blockquote();
            if ( 'ol' !== $in_list ) {
                $close_list();
                $out[] = '<ol>';
                $in_list = 'ol';
            }
            $out[] = '<li>' . gsp_md_inline( $m[1] ) . '</li>';
            continue;
        }

        // Unordered list.
        if ( preg_match( '/^-\s+(.+)$/', $line, $m ) ) {
            $flush_para();
            $close_blockquote();
            if ( 'ul' !== $in_list ) {
                $close_list();
                $out[] = '<ul>';
                $in_list = 'ul';
            }
            $out[] = '<li>' . gsp_md_inline( $m[1] ) . '</li>';
            continue;
        }

        // Regular paragraph line — accumulate.
        $para_buffer[] = $line;
    }

    $flush_para();
    $close_list();
    $close_blockquote();
    if ( $in_code ) {
        // Unterminated fence — emit as-is.
        $out[] = '<pre><code>' . esc_html( implode( "\n", $code_buffer ) ) . '</code></pre>';
    }

    return implode( "\n", $out );
}

/**
 * Inline markdown: bold, italic, code, links.
 * Applied to already-wrapped block-level content. Keep order:
 * code before italic/bold so backtick content isn't re-parsed.
 */
function gsp_md_inline( $text ) {
    // Escape HTML first so user-markdown can't sneak tags through.
    $text = esc_html( $text );

    // Inline code — `foo`
    $text = preg_replace( '/`([^`]+)`/', '<code>$1</code>', $text );

    // Bold — **foo**
    $text = preg_replace( '/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text );

    // Italic — *foo* (after bold so we don't consume the ** markers)
    $text = preg_replace( '/(?<![*\w])\*([^*\n]+)\*(?![*\w])/', '<em>$1</em>', $text );

    // Links — [text](url). Use esc_url() for href (strips javascript:,
    // data:, and other dangerous schemes) rather than esc_attr() which
    // only entity-encodes and would pass a javascript: URL through.
    $text = preg_replace_callback(
        '/\[([^\]]+)\]\(([^)]+)\)/',
        function( $m ) {
            $url = $m[2];
            // Relative URLs to absolute (keep anchors intact).
            if ( ! preg_match( '/^(https?:|mailto:|#|\/)/', $url ) ) {
                $url = home_url( '/' ) . ltrim( $url, '/' );
            }
            return '<a href="' . esc_url( $url ) . '">' . $m[1] . '</a>';
        },
        $text
    );

    // Bare URLs in angle brackets — <https://...>
    $text = preg_replace_callback(
        '/&lt;(https?:\/\/[^&]+)&gt;/',
        function( $m ) {
            return '<a href="' . esc_url( $m[1] ) . '">' . esc_html( $m[1] ) . '</a>';
        },
        $text
    );

    return $text;
}

get_header();

/*
 * Load the guide. Canonical source: the .md file on the server at
 * ~/domains/getsocietypress.org/public_html/docs/ENS-MIGRATION-GUIDE.md
 * Cached in a transient for 1 hour so we're not hitting the filesystem
 * on every page load.
 */
$gsp_guide_path = ABSPATH . '../docs/ENS-MIGRATION-GUIDE.md';
// Fallback for legacy server path (kept during migration window).
if ( ! file_exists( $gsp_guide_path ) ) {
    $gsp_guide_path = ABSPATH . '../Documentation/ENS-MIGRATION-GUIDE.md';
}
$gsp_guide_html = get_transient( 'gsp_ens_guide_html' );

if ( false === $gsp_guide_html ) {
    if ( is_readable( $gsp_guide_path ) ) {
        $raw = file_get_contents( $gsp_guide_path );
        // Offset 1: the page hero already carries the <h1>, so the
        // markdown's own top-level "# Title" becomes <h2> and the rest
        // shifts down accordingly.
        $gsp_guide_html = gsp_render_simple_markdown( $raw, 1 );
    } else {
        $gsp_guide_html = '<p><em>The migration guide file is temporarily unavailable. Please try again, or email <a href="mailto:migrations@getsocietypress.org">migrations@getsocietypress.org</a> for a copy.</em></p>';
    }
    set_transient( 'gsp_ens_guide_html', $gsp_guide_html, HOUR_IN_SECONDS );
}
?>

<section class="page-hero">
    <div class="container">
        <div class="page-hero__content">
            <nav class="page-breadcrumbs" aria-label="Breadcrumb">
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation</a>
                <span aria-hidden="true">&rsaquo;</span>
                <a href="<?php echo esc_url( home_url( '/ens-migration/' ) ); ?>">Moving from ENS</a>
                <span aria-hidden="true">&rsaquo;</span>
                <span>Full Migration Guide</span>
            </nav>
            <h1 class="page-hero__title">ENS to SocietyPress Migration Guide</h1>
            <p class="page-hero__subtitle">
                A step-by-step walkthrough for society webmasters leaving
                EasyNetSites. No development background required.
            </p>
        </div>
    </div>
</section>

<section class="guide-page section">
    <div class="container container--narrow">

        <article class="guide-page__content">
            <?php
            // Output is already HTML we built; allow standard WP-safe tags.
            echo wp_kses_post( $gsp_guide_html );
            ?>
        </article>

        <div class="guide-page__footer">
            <p>
                <strong>See also:</strong>
                <a href="<?php echo esc_url( home_url( '/ens-migration/' ) ); ?>">ENS migration overview</a> &middot;
                <a href="<?php echo esc_url( home_url( '/docs/' ) ); ?>">Documentation hub</a> &middot;
                <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Migration help</a>
            </p>
        </div>

    </div>
</section>

<?php get_footer(); ?>
