<?php
/**
 * Attach newsletter cover images and PDFs to existing sp_newsletters records.
 *
 * Run via: wp eval-file /path/to/attach-newsletters.php
 *
 * Imports cover JPGs and PDFs from sample-data/newsletters/ into the WP
 * media library, then updates each sp_newsletters row with the attachment IDs.
 */

if ( ! defined( 'ABSPATH' ) ) {
    WP_CLI::error( 'Must be run via wp eval-file.' );
}

global $wpdb;
$prefix  = $wpdb->prefix . 'sp_';
$base    = '/home/charle24/domains/getsocietypress.org/public_html/demo/sample-data/newsletters/';

// Map season/year to filenames — must match generate-newsletters.py output
$file_map = [
    'Winter 2026' => [ 'cover' => 'hvq-2026-winter-cover.jpg', 'pdf' => 'hvq-2026-winter.pdf' ],
    'Fall 2025'   => [ 'cover' => 'hvq-2025-fall-cover.jpg',   'pdf' => 'hvq-2025-fall.pdf' ],
    'Summer 2025' => [ 'cover' => 'hvq-2025-summer-cover.jpg', 'pdf' => 'hvq-2025-summer.pdf' ],
    'Spring 2025' => [ 'cover' => 'hvq-2025-spring-cover.jpg', 'pdf' => 'hvq-2025-spring.pdf' ],
    'Winter 2025' => [ 'cover' => 'hvq-2025-winter-cover.jpg', 'pdf' => 'hvq-2025-winter.pdf' ],
    'Fall 2024'   => [ 'cover' => 'hvq-2024-fall-cover.jpg',   'pdf' => 'hvq-2024-fall.pdf' ],
    'Summer 2024' => [ 'cover' => 'hvq-2024-summer-cover.jpg', 'pdf' => 'hvq-2024-summer.pdf' ],
    'Spring 2024' => [ 'cover' => 'hvq-2024-spring-cover.jpg', 'pdf' => 'hvq-2024-spring.pdf' ],
    'Winter 2024' => [ 'cover' => 'hvq-2024-winter-cover.jpg', 'pdf' => 'hvq-2024-winter.pdf' ],
    'Fall 2023'   => [ 'cover' => 'hvq-2023-fall-cover.jpg',   'pdf' => 'hvq-2023-fall.pdf' ],
    'Summer 2023' => [ 'cover' => 'hvq-2023-summer-cover.jpg', 'pdf' => 'hvq-2023-summer.pdf' ],
    'Spring 2023' => [ 'cover' => 'hvq-2023-spring-cover.jpg', 'pdf' => 'hvq-2023-spring.pdf' ],
];

// Need this for wp_insert_attachment / media_handle_sideload
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$newsletters = $wpdb->get_results( "SELECT * FROM {$prefix}newsletters ORDER BY pub_date DESC" );
if ( empty( $newsletters ) ) {
    WP_CLI::error( 'No newsletters found in database.' );
}

$attached = 0;
foreach ( $newsletters as $nl ) {
    // Extract "Season Year" from title like "Heritage Valley Quarterly — Winter 2026"
    if ( preg_match( '/—\s*(Winter|Spring|Summer|Fall)\s+(\d{4})/', $nl->title, $m ) ) {
        $key = $m[1] . ' ' . $m[2];
    } else {
        WP_CLI::warning( "  Could not parse season/year from: {$nl->title}" );
        continue;
    }

    if ( ! isset( $file_map[ $key ] ) ) {
        WP_CLI::warning( "  No files mapped for: $key" );
        continue;
    }

    $files = $file_map[ $key ];

    // Import cover image
    $cover_path = $base . $files['cover'];
    $cover_id   = 0;
    if ( file_exists( $cover_path ) ) {
        $cover_id = sp_demo_import_file( $cover_path, $nl->title . ' — Cover' );
        if ( is_wp_error( $cover_id ) ) {
            WP_CLI::warning( "  Cover import failed for $key: " . $cover_id->get_error_message() );
            $cover_id = 0;
        }
    }

    // Import PDF
    $pdf_path = $base . $files['pdf'];
    $pdf_id   = 0;
    if ( file_exists( $pdf_path ) ) {
        $pdf_id = sp_demo_import_file( $pdf_path, $nl->title );
        if ( is_wp_error( $pdf_id ) ) {
            WP_CLI::warning( "  PDF import failed for $key: " . $pdf_id->get_error_message() );
            $pdf_id = 0;
        }
    }

    // Update newsletter record
    $update = [];
    if ( $cover_id ) $update['cover_image_id'] = $cover_id;
    if ( $pdf_id )   $update['file_id']         = $pdf_id;

    if ( ! empty( $update ) ) {
        $wpdb->update( "{$prefix}newsletters", $update, [ 'id' => $nl->id ] );
        $attached++;
        WP_CLI::log( "  $key: cover=$cover_id, pdf=$pdf_id" );
    }
}

WP_CLI::success( "Attached files to $attached newsletters." );


/**
 * Import a file into the WordPress media library.
 *
 * @param string $file_path Absolute path to the file on disk.
 * @param string $title     Title for the attachment.
 * @return int|WP_Error     Attachment ID or error.
 */
function sp_demo_import_file( string $file_path, string $title ) {
    $filetype = wp_check_filetype( basename( $file_path ) );

    // Copy to uploads directory
    $upload_dir = wp_upload_dir();
    $dest       = $upload_dir['path'] . '/' . basename( $file_path );

    if ( ! copy( $file_path, $dest ) ) {
        return new WP_Error( 'copy_failed', "Could not copy $file_path to $dest" );
    }

    $attachment = [
        'post_mime_type' => $filetype['type'],
        'post_title'     => $title,
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment( $attachment, $dest );
    if ( is_wp_error( $attach_id ) ) return $attach_id;

    // Generate image metadata/thumbnails for images
    $attach_data = wp_generate_attachment_metadata( $attach_id, $dest );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    return $attach_id;
}
