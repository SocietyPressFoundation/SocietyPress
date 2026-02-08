<?php
/**
 * SocietyPress Admin - Pages List View
 *
 * This view provides a simplified interface for managing WordPress pages.
 * Volunteers can see all pages and access a simple editor without needing
 * to navigate the full WordPress admin interface.
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get all pages, including drafts
// We're using get_posts instead of get_pages for more control over ordering
$pages = get_posts( [
    'post_type'      => 'page',
    'posts_per_page' => -1, // All pages
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
    'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
] );

// Organize pages by parent for a hierarchical display
// This helps volunteers understand the page structure
$organized_pages = [];
$child_pages = [];

foreach ( $pages as $page ) {
    if ( $page->post_parent === 0 ) {
        $organized_pages[] = $page;
    } else {
        if ( ! isset( $child_pages[ $page->post_parent ] ) ) {
            $child_pages[ $page->post_parent ] = [];
        }
        $child_pages[ $page->post_parent ][] = $page;
    }
}

/**
 * Helper function to display a page row in the table
 * Includes recursion for child pages with indentation
 *
 * @param object $page       The page object from WP_Query
 * @param object $router     The SP_Admin_Router instance for URL generation
 * @param array  $child_pages Array of child pages indexed by parent ID
 * @param int    $depth      Current nesting depth for indentation
 */
function sp_render_page_row( $page, $router, $child_pages, $depth = 0 ) {
    // Status badge colors for visual clarity
    $status_colors = [
        'publish' => 'var(--sp-success)',
        'draft'   => 'var(--sp-warning)',
        'pending' => 'var(--sp-info)',
        'private' => 'var(--sp-gray-500)',
    ];

    // Human-readable status labels
    $status_labels = [
        'publish' => 'Published',
        'draft'   => 'Draft',
        'pending' => 'Pending Review',
        'private' => 'Private',
    ];

    $indent = str_repeat( '— ', $depth ); // Visual hierarchy indicator
    $status_color = $status_colors[ $page->post_status ] ?? 'var(--sp-gray-500)';
    $status_label = $status_labels[ $page->post_status ] ?? ucfirst( $page->post_status );
    ?>
    <tr>
        <td>
            <a href="<?php echo esc_url( $router->url( 'pages', [ 'id' => $page->ID ] ) ); ?>">
                <?php echo $indent; ?><strong><?php echo esc_html( $page->post_title ); ?></strong>
            </a>
        </td>
        <td>
            <span class="sp-status-badge" style="background: <?php echo $status_color; ?>; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                <?php echo esc_html( $status_label ); ?>
            </span>
        </td>
        <td><?php echo date( 'M j, Y', strtotime( $page->post_modified ) ); ?></td>
        <td class="sp-table-actions">
            <a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" target="_blank" class="sp-table-action">View</a>
            <a href="<?php echo esc_url( $router->url( 'pages', [ 'id' => $page->ID, 'action' => 'edit' ] ) ); ?>" class="sp-table-action">Edit</a>
        </td>
    </tr>
    <?php
    // Recursively render child pages with increased indentation
    if ( isset( $child_pages[ $page->ID ] ) ) {
        foreach ( $child_pages[ $page->ID ] as $child ) {
            sp_render_page_row( $child, $router, $child_pages, $depth + 1 );
        }
    }
}
?>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">Pages</h1>
    <a href="<?php echo esc_url( $router->url( 'pages', [ 'action' => 'new' ] ) ); ?>" class="sp-button sp-button--primary">
        + Add New Page
    </a>
</header>

<!-- Helpful tip for volunteers -->
<div class="sp-admin-notice sp-admin-notice--info" style="margin-bottom: var(--sp-spacing-lg);">
    <strong>Tip:</strong> Pages are organized hierarchically. Child pages appear indented under their parent.
    Click on any page title to view and edit its content.
</div>

<div class="sp-table-wrapper">
    <table class="sp-table">
        <thead>
            <tr>
                <th>Page Title</th>
                <th>Status</th>
                <th>Last Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $organized_pages ) ) : ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: var(--sp-spacing-xl); color: var(--sp-gray-500);">
                        No pages found. <a href="<?php echo esc_url( $router->url( 'pages', [ 'action' => 'new' ] ) ); ?>">Create your first page</a>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $organized_pages as $page ) : ?>
                    <?php sp_render_page_row( $page, $router, $child_pages, 0 ); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
