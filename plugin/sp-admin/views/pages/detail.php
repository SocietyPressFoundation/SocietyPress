<?php
/**
 * SocietyPress Admin - Page Detail/Edit View
 *
 * Provides a simplified page editor for volunteers. We intentionally keep this
 * simple - just title, content (with basic editor), and status. This protects
 * volunteers from the complexity of the full WordPress editor while still
 * letting them make necessary content updates.
 *
 * @package SocietyPress
 * @since 0.59
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_id = $router->get_param( 'id' );
$action  = $router->get_param( 'action', '' );
$is_new  = ( $action === 'new' );
$is_edit = ( $action === 'edit' ) || $is_new;

$page = null;

// Load existing page if we have an ID
if ( $page_id && ! $is_new ) {
    $page = get_post( $page_id );
    if ( ! $page || $page->post_type !== 'page' ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Page not found.</div>';
        return;
    }
}

// Handle form submission
// We process this before rendering so we can redirect on success
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['sp_page_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['sp_page_nonce'], 'sp_save_page' ) ) {

        // Prepare the page data
        // wp_kses_post allows safe HTML (paragraphs, links, images, etc.)
        $page_data = [
            'post_title'   => sanitize_text_field( $_POST['post_title'] ?? '' ),
            'post_content' => wp_kses_post( $_POST['post_content'] ?? '' ),
            'post_status'  => in_array( $_POST['post_status'], [ 'publish', 'draft' ] ) ? $_POST['post_status'] : 'draft',
            'post_type'    => 'page',
        ];

        // Handle parent page assignment
        // This lets volunteers create page hierarchies
        if ( isset( $_POST['post_parent'] ) ) {
            $page_data['post_parent'] = absint( $_POST['post_parent'] );
        }

        if ( $is_new ) {
            // Creating a new page
            $page_data['post_author'] = get_current_user_id();
            $new_page_id = wp_insert_post( $page_data );

            if ( ! is_wp_error( $new_page_id ) ) {
                wp_redirect( $router->url( 'pages', [ 'id' => $new_page_id ] ) . '?success=created' );
                exit;
            }
        } else {
            // Updating existing page
            $page_data['ID'] = $page_id;
            $result = wp_update_post( $page_data );

            if ( ! is_wp_error( $result ) ) {
                wp_redirect( $router->url( 'pages', [ 'id' => $page_id ] ) . '?success=saved' );
                exit;
            }
        }
    }
}

// Handle delete
// Moves to trash instead of permanent deletion (safer for volunteers)
if ( isset( $_GET['delete'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete_page' ) ) {
    wp_trash_post( $page_id );
    wp_redirect( $router->url( 'pages' ) . '?success=deleted' );
    exit;
}

// Get all pages for parent dropdown
// Exclude the current page to prevent circular references
$all_pages = get_posts( [
    'post_type'      => 'page',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'post_status'    => [ 'publish', 'draft' ],
    'exclude'        => $page_id ? [ $page_id ] : [],
] );

// Default values for new page
if ( $is_new ) {
    $page = (object) [
        'post_title'   => '',
        'post_content' => '',
        'post_status'  => 'draft',
        'post_parent'  => 0,
    ];
}
?>

<a href="<?php echo esc_url( $router->url( 'pages' ) ); ?>" class="sp-back-link">← Back to Pages</a>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">
        <?php
        if ( $is_new ) {
            echo 'Add New Page';
        } elseif ( $is_edit ) {
            echo 'Edit Page';
        } else {
            echo esc_html( $page->post_title );
        }
        ?>
    </h1>
    <?php if ( ! $is_new && ! $is_edit ) : ?>
        <div style="display: flex; gap: var(--sp-spacing-sm);">
            <a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank" class="sp-button sp-button--secondary">View Page</a>
            <a href="<?php echo esc_url( $router->url( 'pages', [ 'id' => $page_id, 'action' => 'edit' ] ) ); ?>" class="sp-button sp-button--primary">Edit Page</a>
        </div>
    <?php endif; ?>
</header>

<?php if ( $is_edit ) : ?>
<form method="post" data-sp-form>
    <?php wp_nonce_field( 'sp_save_page', 'sp_page_nonce' ); ?>

    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Page Content</legend>

        <div class="sp-form-group">
            <label for="post_title" class="sp-form-label sp-form-label--required">Page Title</label>
            <input type="text" id="post_title" name="post_title" class="sp-input"
                   value="<?php echo esc_attr( $page->post_title ); ?>" required
                   placeholder="Enter the page title">
            <small style="color: var(--sp-gray-500);">This will appear as the page heading and in navigation menus.</small>
        </div>

        <div class="sp-form-group">
            <label for="post_content" class="sp-form-label">Page Content</label>
            <?php
            // Use WordPress's built-in editor with minimal options
            // This gives volunteers formatting tools without overwhelming them
            wp_editor( $page->post_content, 'post_content', [
                'media_buttons' => true,  // Allow adding images
                'textarea_rows' => 15,
                'teeny'         => false, // Use full (but still simplified) toolbar
                'quicktags'     => true,
                'tinymce'       => [
                    'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink,wp_adv',
                    'toolbar2' => 'formatselect,alignleft,aligncenter,alignright,undo,redo',
                ],
            ] );
            ?>
        </div>
    </fieldset>

    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Page Settings</legend>

        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="post_status" class="sp-form-label">Status</label>
                <select id="post_status" name="post_status" class="sp-select">
                    <option value="draft" <?php selected( $page->post_status, 'draft' ); ?>>Draft</option>
                    <option value="publish" <?php selected( $page->post_status, 'publish' ); ?>>Published</option>
                </select>
                <small style="color: var(--sp-gray-500);">Draft pages are only visible to logged-in users.</small>
            </div>

            <div class="sp-form-group">
                <label for="post_parent" class="sp-form-label">Parent Page</label>
                <select id="post_parent" name="post_parent" class="sp-select">
                    <option value="0">(No parent - top level)</option>
                    <?php foreach ( $all_pages as $p ) : ?>
                        <option value="<?php echo $p->ID; ?>" <?php selected( $page->post_parent, $p->ID ); ?>>
                            <?php echo esc_html( $p->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--sp-gray-500);">Organize pages hierarchically by choosing a parent.</small>
            </div>
        </div>
    </fieldset>

    <div class="sp-form-actions" style="display: flex; gap: var(--sp-spacing-md); margin-top: var(--sp-spacing-xl);">
        <button type="submit" class="sp-button sp-button--primary sp-button--large">
            <?php echo $is_new ? 'Create Page' : 'Save Changes'; ?>
        </button>
        <a href="<?php echo esc_url( $router->url( 'pages' ) ); ?>" class="sp-button sp-button--secondary sp-button--large">Cancel</a>
        <?php if ( ! $is_new ) : ?>
            <a href="<?php echo esc_url( wp_nonce_url( $router->url( 'pages', [ 'id' => $page_id ] ) . '?delete=1', 'delete_page' ) ); ?>"
               class="sp-button sp-button--danger sp-button--large" style="margin-left: auto;"
               data-sp-confirm="Move this page to trash? You can restore it from the WordPress admin if needed.">Delete</a>
        <?php endif; ?>
    </div>
</form>

<?php else : ?>
<!-- View Mode - shows page details without edit form -->
<div class="sp-card">
    <div class="sp-card-header">
        <h2 class="sp-card-title">Page Details</h2>
    </div>

    <div style="padding: var(--sp-spacing-lg);">
        <div class="sp-detail-grid" style="display: grid; grid-template-columns: 150px 1fr; gap: var(--sp-spacing-md);">
            <div style="font-weight: 600; color: var(--sp-gray-600);">Status:</div>
            <div>
                <?php
                $status_colors = [
                    'publish' => 'var(--sp-success)',
                    'draft'   => 'var(--sp-warning)',
                ];
                $status_labels = [
                    'publish' => 'Published',
                    'draft'   => 'Draft',
                ];
                $color = $status_colors[ $page->post_status ] ?? 'var(--sp-gray-500)';
                $label = $status_labels[ $page->post_status ] ?? ucfirst( $page->post_status );
                ?>
                <span style="background: <?php echo $color; ?>; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                    <?php echo esc_html( $label ); ?>
                </span>
            </div>

            <div style="font-weight: 600; color: var(--sp-gray-600);">URL:</div>
            <div>
                <a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank">
                    <?php echo esc_html( get_permalink( $page_id ) ); ?>
                </a>
            </div>

            <div style="font-weight: 600; color: var(--sp-gray-600);">Last Modified:</div>
            <div><?php echo date( 'F j, Y \a\t g:i a', strtotime( $page->post_modified ) ); ?></div>

            <?php if ( $page->post_parent ) :
                $parent = get_post( $page->post_parent );
            ?>
            <div style="font-weight: 600; color: var(--sp-gray-600);">Parent Page:</div>
            <div>
                <a href="<?php echo esc_url( $router->url( 'pages', [ 'id' => $page->post_parent ] ) ); ?>">
                    <?php echo esc_html( $parent->post_title ); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Page Content Preview -->
<div class="sp-card" style="margin-top: var(--sp-spacing-lg);">
    <div class="sp-card-header">
        <h2 class="sp-card-title">Content Preview</h2>
    </div>
    <div style="padding: var(--sp-spacing-lg);">
        <div class="sp-content-preview" style="max-height: 400px; overflow-y: auto; border: 1px solid var(--sp-gray-200); padding: var(--sp-spacing-lg); border-radius: var(--sp-radius-md); background: var(--sp-gray-50);">
            <?php echo apply_filters( 'the_content', $page->post_content ); ?>
        </div>
    </div>
</div>
<?php endif; ?>
