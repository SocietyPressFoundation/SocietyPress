<?php
/**
 * SocietyPress Admin - Newsletters List View
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get newsletter directory path
$upload_dir = wp_upload_dir();
$newsletters_dir = $upload_dir['basedir'] . '/newsletters';
$newsletters_url = $upload_dir['baseurl'] . '/newsletters';

// Create directory if it doesn't exist
if ( ! file_exists( $newsletters_dir ) ) {
    wp_mkdir_p( $newsletters_dir );
}

// Handle upload
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_FILES['newsletter_file'] ) && isset( $_POST['sp_newsletter_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['sp_newsletter_nonce'], 'sp_upload_newsletter' ) ) {
        $file = $_FILES['newsletter_file'];
        if ( $file['error'] === UPLOAD_ERR_OK && pathinfo( $file['name'], PATHINFO_EXTENSION ) === 'pdf' ) {
            $filename = sanitize_file_name( $_POST['filename'] ?? $file['name'] );
            if ( ! str_ends_with( strtolower( $filename ), '.pdf' ) ) {
                $filename .= '.pdf';
            }
            move_uploaded_file( $file['tmp_name'], $newsletters_dir . '/' . $filename );
            wp_redirect( $router->url( 'newsletters' ) . '?success=uploaded' );
            exit;
        }
    }
}

// Handle delete
if ( isset( $_GET['delete'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete_newsletter' ) ) {
    $file_to_delete = $newsletters_dir . '/' . basename( $_GET['delete'] );
    if ( file_exists( $file_to_delete ) ) {
        unlink( $file_to_delete );
    }
    wp_redirect( $router->url( 'newsletters' ) . '?success=deleted' );
    exit;
}

// Get all PDFs grouped by year
$newsletters = [];
if ( is_dir( $newsletters_dir ) ) {
    $files = glob( $newsletters_dir . '/*.pdf' );
    foreach ( $files as $file ) {
        $filename = basename( $file );
        $year = 'Other';
        if ( preg_match( '/^(\d{4})/', $filename, $matches ) ) {
            $year = $matches[1];
        }
        $newsletters[ $year ][] = [
            'filename' => $filename,
            'url'      => $newsletters_url . '/' . $filename,
            'size'     => filesize( $file ),
            'modified' => filemtime( $file ),
        ];
    }
    krsort( $newsletters ); // Sort years descending
}
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Newsletters</h1>
</header>

<!-- Upload Form -->
<div class="sp-card" style="margin-bottom: var(--sp-spacing-xl);">
    <div class="sp-card-header">
        <h2 class="sp-card-title">Upload Newsletter</h2>
    </div>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field( 'sp_upload_newsletter', 'sp_newsletter_nonce' ); ?>
        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="filename" class="sp-form-label">Filename</label>
                <input type="text" id="filename" name="filename" class="sp-input" placeholder="2026_01_January_Newsletter.pdf">
                <small style="color: var(--sp-gray-500);">Format: YYYY_MM_Month_Newsletter.pdf</small>
            </div>
            <div class="sp-form-group">
                <label for="newsletter_file" class="sp-form-label">PDF File</label>
                <input type="file" id="newsletter_file" name="newsletter_file" accept=".pdf" class="sp-input">
            </div>
            <div class="sp-form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="sp-button sp-button--primary">Upload</button>
            </div>
        </div>
    </form>
</div>

<!-- Newsletter List -->
<?php if ( empty( $newsletters ) ) : ?>
    <div class="sp-card">
        <p style="text-align: center; padding: var(--sp-spacing-xl); color: var(--sp-gray-500);">
            No newsletters uploaded yet.
        </p>
    </div>
<?php else : ?>
    <?php foreach ( $newsletters as $year => $files ) : ?>
        <div class="sp-card" style="margin-bottom: var(--sp-spacing-lg);">
            <div class="sp-card-header">
                <h2 class="sp-card-title"><?php echo esc_html( $year ); ?></h2>
            </div>
            <table class="sp-table">
                <thead>
                    <tr><th>Newsletter</th><th>Size</th><th>Uploaded</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ( $files as $file ) : ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url( $file['url'] ); ?>" target="_blank">
                                    <?php echo esc_html( $file['filename'] ); ?>
                                </a>
                            </td>
                            <td><?php echo size_format( $file['size'] ); ?></td>
                            <td><?php echo date( 'M j, Y', $file['modified'] ); ?></td>
                            <td class="sp-table-actions">
                                <a href="<?php echo esc_url( $file['url'] ); ?>" target="_blank" class="sp-table-action">View</a>
                                <a href="<?php echo esc_url( wp_nonce_url( $router->url( 'newsletters' ) . '?delete=' . urlencode( $file['filename'] ), 'delete_newsletter' ) ); ?>" 
                                   class="sp-table-action" style="color: var(--sp-danger);"
                                   data-sp-confirm="Delete this newsletter?">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
