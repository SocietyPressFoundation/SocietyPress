<?php
/**
 * Plugin Name: GSP Download Log
 * Description: Records who downloads SocietyPress from getsocietypress.org, and serves the files.
 * Version:     1.0.0
 * Author:      SocietyPress Foundation
 * License:     GPL-2.0-or-later
 *
 * ============================================================================
 * WHY THIS EXISTS
 * ============================================================================
 *
 * The release artifacts live in /downloads/, which sits outside WordPress. The
 * site's root .htaccess routes unmatched requests to index.php, but only when
 * the request does NOT correspond to a real file:
 *
 *     RewriteCond %{REQUEST_FILENAME} !-f
 *
 * societypress-latest.zip IS a real file, so that condition fails and Apache
 * serves it straight off disk. WordPress never runs, no hook ever fires, and
 * nothing is recorded anywhere. On 2026-08-15 the question "who downloaded
 * this?" turned out to be unanswerable for exactly that reason: cPanel raw-log
 * archiving was off, no analytics were installed, and GitHub does not expose
 * downloader identity at all. Fifteen downloads existed with zero attribution.
 *
 * This plugin closes that gap for the website half of the problem. A companion
 * rewrite in /downloads/.htaccess sends the two public artifacts here instead
 * of letting Apache serve them, we append a log line, and then we stream the
 * file ourselves.
 *
 * ============================================================================
 * THE PRIME DIRECTIVE: NEVER BREAK THE DOWNLOAD
 * ============================================================================
 *
 * This code sits directly in front of the project's only distribution channel.
 * A logging failure must never become a download failure. Every logging step is
 * wrapped so that any error — unwritable directory, full disk, malformed
 * server variable — is swallowed and the file is served regardless. Losing a
 * log line is a nuisance. Losing a download is the whole point of the site.
 *
 * ============================================================================
 * PRIVACY
 * ============================================================================
 *
 * This records IP address, user agent and referer — the same fields any web
 * server writes to its access log by default, and nothing beyond them. No
 * cookies, no fingerprinting, no third party receives any of it. The log lives
 * outside the web root and cannot be fetched over HTTP.
 *
 * IP addresses are personal data under GDPR. Retention is therefore capped (see
 * GSP_DL_RETENTION_DAYS) and old entries are pruned automatically, so the file
 * does not become an indefinite archive of who fetched what.
 */

// No direct access — this must only ever run inside WordPress.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Where the log lives.
 *
 * WHY OUTSIDE THE WEB ROOT: public_html is served by Apache, so a log file
 * placed anywhere inside it is one guessed URL away from being public. This
 * path is a sibling of public_html, not a child, and cannot be requested over
 * HTTP at any URL.
 */
define( 'GSP_DL_LOG_DIR', dirname( ABSPATH, 2 ) . '/gsp-analytics' );

/** How many days of download records to keep before pruning. */
define( 'GSP_DL_RETENTION_DAYS', 400 );

/**
 * The only files this endpoint will ever serve.
 *
 * WHY AN ALLOWLIST AND NOT A PATH PARAMETER: taking a filename from the query
 * string and joining it to a directory is the classic path traversal hole —
 * ../../wp-config.php and friends. Nothing outside this map is serveable, no
 * matter what arrives in the request, so traversal is structurally impossible
 * rather than merely filtered against.
 */
function gsp_dl_allowed_files(): array {
    return [
        'societypress-latest.zip' => [
            'path' => dirname( ABSPATH ) . '/downloads/societypress-latest.zip',
            'type' => 'application/zip',
        ],
        'sp-installer.php' => [
            'path' => dirname( ABSPATH ) . '/downloads/sp-installer.php',
            // Deliberately NOT text/php — this file is meant to be saved and run
            // on the visitor's own WordPress site, never rendered or executed here.
            'type' => 'application/octet-stream',
        ],
    ];
}

/**
 * Best-effort guess at whether a request came from a machine rather than a person.
 *
 * WHY THIS MATTERS ENOUGH TO GUESS: a raw download count is close to useless
 * for judging whether real societies have found the project. Crawlers, mirrors,
 * security scanners and package bots all increment it. Marking the obvious
 * automation at write time means the log can be read later as "people" versus
 * "machines" without re-deriving it from user agent strings by hand.
 *
 * This is a heuristic and is stored as a flag, never used to block anything.
 * A bot that lies about its user agent is recorded as a person, which is the
 * right way to be wrong here — under-counting bots is better than turning
 * anyone away.
 */
function gsp_dl_looks_like_bot( string $ua ): bool {
    if ( '' === $ua ) {
        return true; // Real browsers always send one.
    }

    $needles = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python',
        'java/', 'go-http', 'libwww', 'httpclient', 'okhttp', 'scrapy',
        'headless', 'phantom', 'monitor', 'scanner', 'fetch', 'archiver',
    ];

    $ua_lower = strtolower( $ua );
    foreach ( $needles as $needle ) {
        if ( str_contains( $ua_lower, $needle ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Work out the client IP, accounting for the host's proxy layer.
 *
 * WHY NOT JUST REMOTE_ADDR: this site sits behind cPanel's proxy, so
 * REMOTE_ADDR is frequently the server's own address and every visitor would
 * look identical. The forwarded headers carry the real client. They are also
 * trivially spoofable by the client, which is fine for analytics — a spoofed
 * address costs an inaccurate log line, and this value is never used for
 * access control, rate limiting or anything security-bearing.
 */
function gsp_dl_client_ip(): string {
    $candidates = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ];

    foreach ( $candidates as $key ) {
        if ( empty( $_SERVER[ $key ] ) ) {
            continue;
        }

        // X-Forwarded-For may be a chain: "client, proxy1, proxy2". The client
        // is the leftmost entry.
        $raw = explode( ',', (string) $_SERVER[ $key ] )[0];
        $ip  = filter_var( trim( $raw ), FILTER_VALIDATE_IP );

        if ( $ip ) {
            return $ip;
        }
    }

    return 'unknown';
}

/**
 * Append one JSON record for this download.
 *
 * WHY JSONL: appending a line is a single atomic-ish write with no need to
 * parse, rewrite or lock the whole file, so concurrent downloads cannot corrupt
 * each other's entries the way rewriting a JSON array would. It also stays
 * greppable from the shell, which matters on a server with no analytics UI.
 *
 * Returns nothing and throws nothing — callers must not care whether it worked.
 */
function gsp_dl_record( string $file ): void {
    try {
        if ( ! is_dir( GSP_DL_LOG_DIR ) && ! @mkdir( GSP_DL_LOG_DIR, 0700, true ) && ! is_dir( GSP_DL_LOG_DIR ) ) {
            return;
        }

        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( (string) $_SERVER['HTTP_USER_AGENT'], 0, 500 ) : '';

        $entry = [
            'at'       => gmdate( 'c' ),
            'file'     => $file,
            'ip'       => gsp_dl_client_ip(),
            'ua'       => $ua,
            'referer'  => isset( $_SERVER['HTTP_REFERER'] ) ? substr( (string) $_SERVER['HTTP_REFERER'], 0, 300 ) : '',
            'bot'      => gsp_dl_looks_like_bot( $ua ),
            'method'   => isset( $_SERVER['REQUEST_METHOD'] ) ? (string) $_SERVER['REQUEST_METHOD'] : '',
        ];

        $line = wp_json_encode( $entry );
        if ( false === $line ) {
            return;
        }

        // LOCK_EX keeps two simultaneous downloads from interleaving mid-line.
        @file_put_contents( GSP_DL_LOG_DIR . '/downloads.jsonl', $line . "\n", FILE_APPEND | LOCK_EX );
    } catch ( \Throwable $e ) {
        // Deliberately silent. See "THE PRIME DIRECTIVE" above: a logging
        // problem must never surface as a broken download.
        return;
    }
}

/**
 * Drop records older than the retention window.
 *
 * WHY ONLY ON A CRON AND NOT PER REQUEST: rewriting the file means reading all
 * of it, and doing that while somebody is waiting on an 8.9 MB download would
 * add latency to the one thing that must stay fast.
 */
function gsp_dl_prune(): void {
    try {
        $path = GSP_DL_LOG_DIR . '/downloads.jsonl';
        if ( ! is_readable( $path ) ) {
            return;
        }

        $cutoff = time() - ( GSP_DL_RETENTION_DAYS * DAY_IN_SECONDS );
        $lines  = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        if ( false === $lines ) {
            return;
        }

        $keep = [];
        foreach ( $lines as $line ) {
            $row = json_decode( $line, true );
            // An unparseable line is kept rather than discarded — losing data to
            // a parser quirk is worse than carrying a stray line.
            if ( ! is_array( $row ) || empty( $row['at'] ) ) {
                $keep[] = $line;
                continue;
            }
            if ( strtotime( (string) $row['at'] ) >= $cutoff ) {
                $keep[] = $line;
            }
        }

        if ( count( $keep ) !== count( $lines ) ) {
            @file_put_contents( $path, implode( "\n", $keep ) . "\n", LOCK_EX );
        }
    } catch ( \Throwable $e ) {
        return;
    }
}

add_action( 'gsp_dl_prune_event', 'gsp_dl_prune' );

add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'gsp_dl_prune_event' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'gsp_dl_prune_event' );
    }
} );

/**
 * Handle a download request: log it, then stream the file.
 *
 * Hooked early on purpose. There is no reason to build a query, load a theme or
 * render anything for a request whose entire job is to emit a zip file — and
 * every extra hook that runs first is another chance for an unrelated plugin
 * error to land in the middle of a binary stream and corrupt it.
 */
add_action( 'plugins_loaded', function () {
    if ( empty( $_GET['gsp_dl'] ) ) {
        return;
    }

    $requested = sanitize_file_name( wp_unslash( (string) $_GET['gsp_dl'] ) );
    $allowed   = gsp_dl_allowed_files();

    if ( ! isset( $allowed[ $requested ] ) ) {
        status_header( 404 );
        exit;
    }

    $file = $allowed[ $requested ];

    if ( ! is_readable( $file['path'] ) ) {
        // The artifact is missing from disk. Fail loudly in the log so this is
        // discoverable, because it means the download page is broken.
        error_log( 'GSP Download Log: missing artifact ' . $file['path'] );
        status_header( 404 );
        exit;
    }

    // Log BEFORE sending anything. Once bytes are on the wire an abort would
    // leave the download recorded as never having happened.
    gsp_dl_record( $requested );

    $size = filesize( $file['path'] );

    // Clear any buffering a theme or plugin may have started. Anything already
    // buffered would be prepended to the file and corrupt it.
    while ( ob_get_level() > 0 ) {
        ob_end_clean();
    }

    nocache_headers();
    header( 'Content-Type: ' . $file['type'] );
    header( 'Content-Disposition: attachment; filename="' . $requested . '"' );
    header( 'Content-Transfer-Encoding: binary' );
    if ( false !== $size ) {
        header( 'Content-Length: ' . $size );
    }
    // The installer fetches this over HTTP; an accidental gzip layer on an
    // already-compressed zip wastes CPU and has broken naive clients before.
    header( 'Content-Encoding: none' );

    readfile( $file['path'] );
    exit;
}, 1 );
