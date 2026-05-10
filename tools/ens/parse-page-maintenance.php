<?php
/**
 * ENS Page Maintenance Parser
 *
 * Reads a saved copy of an ENS "Menu / Page Maintenance"
 * (mo_menuPageMaint.php) HTML page and emits a normalized JSON list of
 * menu rows. This is the upstream half of the ENS migration importer:
 * once we have structured rows, the WordPress side knows how to create
 * pages and nav menu items from them.
 *
 * Usage:
 *     php parse-page-maintenance.php path/to/saved.html
 *     php parse-page-maintenance.php fixtures/sample-page-maintenance.html
 *
 * Output: JSON array of objects to stdout, each object:
 *     {
 *       "row_index":   1,                       // 1-based table row order
 *       "label":       "Galleries",             // visible nav label
 *       "url":         "gallery.php?sid=3",     // raw ENS URL
 *       "parent_label":"Galleries",             // parent label text or "None"
 *       "parent_id":   "10",                    // parent ENS row id, or "0"
 *       "sort_order":  170,                     // integer sort
 *       "display":     true,                    // visible in nav
 *       "secure":      false,                   // login required
 *       "kind":        "gallery",               // classified destination type
 *       "kind_arg":    "3"                      // module-specific id (sid, pt, etc.)
 *     }
 *
 * Why a CLI tracer instead of a plugin admin screen: this is the
 * smallest end-to-end thing that proves we can extract clean rows from
 * an ENS page. Once parsing is solid, the same logic is wrapped in an
 * SP admin importer that creates wp_posts + wp_nav_menu_items.
 *
 * Defensive on purpose: ENS markup wasn't observed directly, so the
 * parser tries multiple strategies (form-input values, then fallbacks
 * to cell text content) before giving up on a row.
 */

declare( strict_types = 1 );

if ( $argc < 2 ) {
    fwrite( STDERR, "Usage: php parse-page-maintenance.php <html-file>\n" );
    exit( 64 ); // EX_USAGE
}

$path = $argv[1];
if ( ! is_readable( $path ) ) {
    fwrite( STDERR, "Cannot read: {$path}\n" );
    exit( 66 ); // EX_NOINPUT
}

$html = file_get_contents( $path );
if ( $html === false || $html === '' ) {
    fwrite( STDERR, "Empty file: {$path}\n" );
    exit( 65 ); // EX_DATAERR
}

$rows = ens_parse_page_maintenance( $html );

echo json_encode( $rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ), "\n";


/**
 * Parse the Page Maintenance HTML into a flat list of row records.
 *
 * @return array<int,array<string,mixed>>
 */
function ens_parse_page_maintenance( string $html ): array {
    $doc = new DOMDocument();
    libxml_use_internal_errors( true );
    // ENS HTML is likely loose; suppress warnings, accept what we get.
    $doc->loadHTML( $html, LIBXML_NOWARNING | LIBXML_NOERROR );
    libxml_clear_errors();

    $xpath = new DOMXPath( $doc );

    // Strategy: every <tr> that contains an order field is a data row.
    // The order field is either an <input name="order_*"> (form mode) or
    // a numeric-only <td>; we match the form-mode case first because it
    // is the unambiguous one.
    $rows  = [];
    $trs   = $xpath->query( '//tr[.//input[starts-with(@name, "order_") or starts-with(@name, "sort_")]]' );

    if ( $trs && $trs->length > 0 ) {
        foreach ( $trs as $i => $tr ) {
            /** @var DOMElement $tr */
            $row = ens_parse_row_form_mode( $tr, $xpath );
            if ( $row !== null ) {
                $row['row_index'] = $i + 1;
                $rows[]           = $row;
            }
        }
    }

    return $rows;
}


/**
 * Extract a single row when the table is in editable form mode
 * (each cell holds <input>/<select> elements).
 */
function ens_parse_row_form_mode( DOMElement $tr, DOMXPath $xpath ): ?array {
    $label  = ens_first_input_value( $xpath, $tr, 'label_' );
    $order  = ens_first_input_value( $xpath, $tr, 'order_' );
    if ( $label === null || $order === null ) {
        $order = $order ?? ens_first_input_value( $xpath, $tr, 'sort_' );
        $label = $label ?? ens_first_input_value( $xpath, $tr, 'menu_' );
    }
    if ( $label === null && $order === null ) {
        return null;
    }

    // URL: prefer the first <a href> inside the row; fall back to a
    // hidden/visible URL input if present.
    $url     = '';
    $anchors = $xpath->query( './/a[@href]', $tr );
    if ( $anchors && $anchors->length > 0 ) {
        $url = trim( $anchors->item( 0 )->getAttribute( 'href' ) );
    }
    if ( $url === '' ) {
        $url = ens_first_input_value( $xpath, $tr, 'url_' ) ?? '';
    }

    // Parent: the selected <option> inside the parent <select>.
    $parent_label = 'None';
    $parent_id    = '0';
    $sel_opt      = $xpath->query( './/select[starts-with(@name, "parent_")]/option[@selected]', $tr );
    if ( $sel_opt && $sel_opt->length > 0 ) {
        /** @var DOMElement $opt */
        $opt          = $sel_opt->item( 0 );
        $parent_label = trim( $opt->textContent );
        $parent_id    = trim( $opt->getAttribute( 'value' ) );
    }

    $display = ens_checkbox_checked( $xpath, $tr, 'display_' );
    $secure  = ens_checkbox_checked( $xpath, $tr, 'secure_' );

    [ $kind, $kind_arg ] = ens_classify_url( $url );

    return [
        'label'        => $label !== null ? html_entity_decode( $label, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : '',
        'url'          => $url,
        'parent_label' => html_entity_decode( $parent_label, ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
        'parent_id'    => $parent_id,
        'sort_order'   => (int) ( $order ?? 0 ),
        'display'      => $display,
        'secure'       => $secure,
        'kind'         => $kind,
        'kind_arg'     => $kind_arg,
    ];
}


/**
 * Find the first <input> in $tr whose @name starts with $prefix and
 * return its trimmed value, or null if not found.
 */
function ens_first_input_value( DOMXPath $xpath, DOMElement $tr, string $prefix ): ?string {
    $nodes = $xpath->query( './/input[starts-with(@name, "' . $prefix . '")]', $tr );
    if ( ! $nodes || $nodes->length === 0 ) {
        return null;
    }
    /** @var DOMElement $node */
    $node = $nodes->item( 0 );
    return trim( $node->getAttribute( 'value' ) );
}


/**
 * Return true if the first checkbox in $tr with @name starting with
 * $prefix is checked.
 */
function ens_checkbox_checked( DOMXPath $xpath, DOMElement $tr, string $prefix ): bool {
    $nodes = $xpath->query( './/input[@type="checkbox" and starts-with(@name, "' . $prefix . '")]', $tr );
    if ( ! $nodes || $nodes->length === 0 ) {
        return false;
    }
    /** @var DOMElement $node */
    $node = $nodes->item( 0 );
    return $node->hasAttribute( 'checked' );
}


/**
 * Classify an ENS URL into a destination type the SP importer can map.
 *
 * Returns [kind, arg] where:
 *   kind     = "content_page" | "gallery" | "events" | "library_catalog"
 *            | "surname" | "research_help" | "donation" | "links"
 *            | "store" | "members_home" | "profile" | "membership_list"
 *            | "member_file" | "logoff" | "home" | "about_static"
 *            | "join_online" | "file_upload" | "external" | "unknown"
 *   arg      = the relevant id (pt=, sid=, dc=, nm=) or "" if none
 *
 * @return array{0:string,1:string}
 */
function ens_classify_url( string $url ): array {
    if ( $url === '' ) {
        return [ 'unknown', '' ];
    }

    // Direct file uploads (Collection Policy etc.) come through as
    // /upload/menu/... — treat those as media-library targets.
    if ( strpos( $url, '/upload/' ) === 0 ) {
        return [ 'file_upload', $url ];
    }

    // Absolute external URLs.
    if ( preg_match( '#^https?://#i', $url ) ) {
        return [ 'external', $url ];
    }

    // Parse the script name + query.
    $script = strtolower( strtok( $url, '?' ) );
    $query  = '';
    $qpos   = strpos( $url, '?' );
    if ( $qpos !== false ) {
        $query = substr( $url, $qpos + 1 );
    }
    parse_str( $query, $qs );

    switch ( $script ) {
        case 'index.php':
            return [ 'home', '' ];
        case 'about.php':
            return [ 'about_static', '' ];
        case 'cpage.php':
            return [ 'content_page', (string) ( $qs['pt'] ?? '' ) ];
        case 'gallery.php':
            return [ 'gallery', (string) ( $qs['sid'] ?? '' ) ];
        case 'eventlistings.php':
            return [ 'events', (string) ( $qs['nm'] ?? '' ) ];
        case 'libraryrecords.php':
            return [ 'library_catalog', '' ];
        case 'surname.php':
            return [ 'surname', '' ];
        case 'donation.php':
            return [ 'donation', (string) ( $qs['dc'] ?? '' ) ];
        case 'links.php':
            return [ 'links', (string) ( $qs['sid'] ?? '' ) ];
        case 'store.php':
            return [ 'store', (string) ( $qs['sid'] ?? '' ) ];
        case 'members.php':
            return [ 'members_home', '' ];
        case 'profile.php':
            return [ 'profile', '' ];
        case 'membershiplist.php':
            return [ 'membership_list', '' ];
        case 'filedownload.php':
            return [ 'member_file', (string) ( $qs['sid'] ?? '' ) ];
        case 'logoff.php':
            return [ 'logoff', '' ];
        case 'onlinejoin.php':
            return [ 'join_online', '' ];
    }

    return [ 'unknown', $url ];
}
