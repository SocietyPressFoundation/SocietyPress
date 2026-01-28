<?php
/**
 * Hero Slider Custom Post Type
 *
 * WHY: Provides dedicated post type for managing hero slider content
 * independently from blog posts.
 *
 * @package SocietyPress
 * @since 1.11d
 */

/**
 * Register Hero Slide custom post type.
 *
 * WHY: Allows creating individual slides with custom images and links.
 */
function societypress_register_slide_post_type() {
	$labels = array(
		'name'                  => _x( 'Hero Slides', 'Post type general name', 'societypress' ),
		'singular_name'         => _x( 'Hero Slide', 'Post type singular name', 'societypress' ),
		'menu_name'             => _x( 'Hero Slider', 'Admin Menu text', 'societypress' ),
		'name_admin_bar'        => _x( 'Hero Slide', 'Add New on Toolbar', 'societypress' ),
		'add_new'               => __( 'Add New', 'societypress' ),
		'add_new_item'          => __( 'Add New Slide', 'societypress' ),
		'new_item'              => __( 'New Slide', 'societypress' ),
		'edit_item'             => __( 'Edit Slide', 'societypress' ),
		'view_item'             => __( 'View Slide', 'societypress' ),
		'all_items'             => __( 'All Slides', 'societypress' ),
		'search_items'          => __( 'Search Slides', 'societypress' ),
		'not_found'             => __( 'No slides found.', 'societypress' ),
		'not_found_in_trash'    => __( 'No slides found in Trash.', 'societypress' ),
		'featured_image'        => _x( 'Slide Background Image', 'Overrides the "Featured Image" phrase', 'societypress' ),
		'set_featured_image'    => _x( 'Set slide background image', 'Overrides the "Set featured image" phrase', 'societypress' ),
		'remove_featured_image' => _x( 'Remove slide background image', 'Overrides the "Remove featured image" phrase', 'societypress' ),
		'use_featured_image'    => _x( 'Use as slide background image', 'Overrides the "Use as featured image" phrase', 'societypress' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-images-alt2',
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'supports'           => array( 'title', 'editor', 'thumbnail' ),
		'show_in_rest'       => true,
	);

	register_post_type( 'sp_slide', $args );
}
add_action( 'init', 'societypress_register_slide_post_type' );

/**
 * Add meta box for slide link URL.
 *
 * WHY: Allows specifying where the slide should link to when clicked.
 */
function societypress_add_slide_meta_boxes() {
	add_meta_box(
		'societypress_slide_link',
		__( 'Slide Link', 'societypress' ),
		'societypress_slide_link_meta_box_callback',
		'sp_slide',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'societypress_add_slide_meta_boxes' );

/**
 * Render slide link meta box.
 *
 * @param WP_Post $post Current post object.
 */
function societypress_slide_link_meta_box_callback( $post ) {
	// Add nonce for security
	wp_nonce_field( 'societypress_slide_link_nonce', 'societypress_slide_link_nonce' );

	// Get existing value
	$slide_url = get_post_meta( $post->ID, '_sp_slide_url', true );
	$open_new_tab = get_post_meta( $post->ID, '_sp_slide_new_tab', true );
	?>
	<p>
		<label for="sp_slide_url">
			<?php esc_html_e( 'Link URL:', 'societypress' ); ?>
		</label>
		<input
			type="url"
			id="sp_slide_url"
			name="sp_slide_url"
			value="<?php echo esc_attr( $slide_url ); ?>"
			class="widefat"
			placeholder="https://example.com"
		/>
		<small class="description">
			<?php esc_html_e( 'Enter the full URL where this slide should link (optional). Leave blank for no link.', 'societypress' ); ?>
		</small>
	</p>
	<p>
		<label>
			<input
				type="checkbox"
				name="sp_slide_new_tab"
				value="1"
				<?php checked( $open_new_tab, '1' ); ?>
			/>
			<?php esc_html_e( 'Open link in new tab', 'societypress' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Save slide link meta box data.
 *
 * @param int $post_id Post ID.
 */
function societypress_save_slide_link_meta( $post_id ) {
	// Check nonce
	if ( ! isset( $_POST['societypress_slide_link_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['societypress_slide_link_nonce'], 'societypress_slide_link_nonce' ) ) {
		return;
	}

	// Check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save slide URL
	if ( isset( $_POST['sp_slide_url'] ) ) {
		$slide_url = esc_url_raw( $_POST['sp_slide_url'] );
		update_post_meta( $post_id, '_sp_slide_url', $slide_url );
	}

	// Save new tab setting
	$new_tab = isset( $_POST['sp_slide_new_tab'] ) ? '1' : '0';
	update_post_meta( $post_id, '_sp_slide_new_tab', $new_tab );
}
add_action( 'save_post_sp_slide', 'societypress_save_slide_link_meta' );

/**
 * Add custom columns to slides list.
 *
 * WHY: Shows slide URL and image directly in the admin list.
 */
function societypress_slide_columns( $columns ) {
	$new_columns = array(
		'cb'         => $columns['cb'],
		'image'      => __( 'Image', 'societypress' ),
		'title'      => $columns['title'],
		'slide_url'  => __( 'Link URL', 'societypress' ),
		'date'       => $columns['date'],
	);
	return $new_columns;
}
add_filter( 'manage_sp_slide_posts_columns', 'societypress_slide_columns' );

/**
 * Populate custom columns.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function societypress_slide_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'image':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 60, 60 ) );
			} else {
				echo '<span style="color: #999;">—</span>';
			}
			break;

		case 'slide_url':
			$url = get_post_meta( $post_id, '_sp_slide_url', true );
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a>';
			} else {
				echo '<span style="color: #999;">' . esc_html__( 'No link', 'societypress' ) . '</span>';
			}
			break;
	}
}
add_action( 'manage_sp_slide_posts_custom_column', 'societypress_slide_column_content', 10, 2 );

/**
 * Add help text to slides admin screen.
 *
 * WHY: Guides users on how to create effective slides.
 */
function societypress_slide_help_text() {
	$screen = get_current_screen();

	if ( 'sp_slide' !== $screen->post_type ) {
		return;
	}

	$screen->add_help_tab(
		array(
			'id'      => 'societypress_slide_help',
			'title'   => __( 'Hero Slider Help', 'societypress' ),
			'content' => '
				<h3>' . __( 'Creating Hero Slides', 'societypress' ) . '</h3>
				<p>' . __( 'Each hero slide appears on your homepage in the carousel at the top of the page.', 'societypress' ) . '</p>
				<h4>' . __( 'Required:', 'societypress' ) . '</h4>
				<ul>
					<li><strong>' . __( 'Title:', 'societypress' ) . '</strong> ' . __( 'Appears as the main heading on the slide', 'societypress' ) . '</li>
					<li><strong>' . __( 'Featured Image:', 'societypress' ) . '</strong> ' . __( 'Background image for the slide (recommended: 1920 x 800 pixels)', 'societypress' ) . '</li>
				</ul>
				<h4>' . __( 'Optional:', 'societypress' ) . '</h4>
				<ul>
					<li><strong>' . __( 'Content:', 'societypress' ) . '</strong> ' . __( 'Short description text that appears on the slide (keep it brief)', 'societypress' ) . '</li>
					<li><strong>' . __( 'Link URL:', 'societypress' ) . '</strong> ' . __( 'Where the "Read More" button should go when clicked', 'societypress' ) . '</li>
				</ul>
				<h4>' . __( 'Tips:', 'societypress' ) . '</h4>
				<ul>
					<li>' . __( 'Slides appear in order by date (newest first)', 'societypress' ) . '</li>
					<li>' . __( 'Maximum 10 slides will display in the carousel', 'societypress' ) . '</li>
					<li>' . __( 'Only published slides appear on the homepage', 'societypress' ) . '</li>
					<li>' . __( 'Use high-quality images at least 1920px wide', 'societypress' ) . '</li>
				</ul>
			',
		)
	);
}
add_action( 'admin_head', 'societypress_slide_help_text' );
