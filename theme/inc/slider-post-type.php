<?php
/**
 * Hero Slider Custom Post Type
 *
 * WHY: Provides dedicated post type for managing hero slider content
 * independently from blog posts. Now enhanced with Slide Groups for
 * displaying different sliders on different pages via the Hero Slider widget.
 *
 * @package SocietyPress
 * @since 1.11d
 * @updated 1.37d - Added Slide Groups taxonomy, video support, text color picker
 */

/**
 * Register Hero Slide custom post type.
 *
 * WHY: Allows creating individual slides with custom images, videos, and links.
 * The page-attributes support enables drag-and-drop ordering in admin.
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
		// WHY: Always hidden from the auto-generated menu. We add it manually via
		// admin_menu hook below so we can control *when* it registers — after the
		// SocietyPress plugin has created its parent menu. The old approach
		// (show_in_menu => 'societypress') ran before the parent existed and
		// WordPress silently fell back to a top-level menu.
		'show_in_menu'       => false,
		'menu_icon'          => 'dashicons-images-alt2',
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		// WHY: page-attributes adds menu_order support for drag-and-drop ordering
		'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'       => true,
	);

	register_post_type( 'sp_slide', $args );
}
add_action( 'init', 'societypress_register_slide_post_type' );

/**
 * Add Hero Slider to the admin sidebar menu.
 *
 * WHY: We register this on admin_menu at priority 20 (after the SocietyPress
 *      plugin's add_menus runs at priority 10) so the parent menu exists when
 *      we add our submenu. If the plugin isn't active, falls back to a
 *      standalone top-level menu so slides remain accessible.
 */
function societypress_add_slide_admin_menu() {
	if ( class_exists( 'SocietyPress' ) ) {
		// Nest under the SocietyPress hub menu
		add_submenu_page(
			'societypress',
			__( 'Hero Slider', 'societypress' ),
			__( 'Hero Slider', 'societypress' ),
			'edit_posts',
			'edit.php?post_type=sp_slide'
		);
	} else {
		// Plugin not active — give it its own top-level menu
		add_menu_page(
			__( 'Hero Slider', 'societypress' ),
			__( 'Hero Slider', 'societypress' ),
			'edit_posts',
			'edit.php?post_type=sp_slide',
			'',
			'dashicons-images-alt2',
			20
		);
	}
}
add_action( 'admin_menu', 'societypress_add_slide_admin_menu', 20 );

/**
 * Register Slide Group taxonomy.
 *
 * WHY: Allows grouping slides so different sliders can be shown on different
 * pages. For example, "Homepage" slides vs "Events" slides. Non-hierarchical
 * like tags for simpler user experience.
 */
function societypress_register_slide_group_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Slide Groups', 'taxonomy general name', 'societypress' ),
		'singular_name'              => _x( 'Slide Group', 'taxonomy singular name', 'societypress' ),
		'search_items'               => __( 'Search Slide Groups', 'societypress' ),
		'popular_items'              => __( 'Popular Slide Groups', 'societypress' ),
		'all_items'                  => __( 'All Slide Groups', 'societypress' ),
		'edit_item'                  => __( 'Edit Slide Group', 'societypress' ),
		'update_item'                => __( 'Update Slide Group', 'societypress' ),
		'add_new_item'               => __( 'Add New Slide Group', 'societypress' ),
		'new_item_name'              => __( 'New Slide Group Name', 'societypress' ),
		'separate_items_with_commas' => __( 'Separate groups with commas', 'societypress' ),
		'add_or_remove_items'        => __( 'Add or remove slide groups', 'societypress' ),
		'choose_from_most_used'      => __( 'Choose from most used groups', 'societypress' ),
		'not_found'                  => __( 'No slide groups found.', 'societypress' ),
		'menu_name'                  => __( 'Slide Groups', 'societypress' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => false, // WHY: Non-hierarchical = simpler tag-like interface
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false,
	);

	register_taxonomy( 'sp_slide_group', 'sp_slide', $args );
}
add_action( 'init', 'societypress_register_slide_group_taxonomy' );

/**
 * Create default "Homepage" slide group on theme activation.
 *
 * WHY: Pre-creates the most common group so users have something to start with.
 * Most sites will want a homepage slider at minimum.
 */
function societypress_create_default_slide_group() {
	// Only create if it doesn't already exist
	if ( ! term_exists( 'Homepage', 'sp_slide_group' ) ) {
		wp_insert_term(
			'Homepage',
			'sp_slide_group',
			array(
				'description' => __( 'Slides displayed on the homepage hero area.', 'societypress' ),
				'slug'        => 'homepage',
			)
		);
	}
}
add_action( 'after_switch_theme', 'societypress_create_default_slide_group' );

/**
 * Add meta boxes for slide settings.
 *
 * WHY: Provides UI for link URL, video background, and text color settings.
 */
function societypress_add_slide_meta_boxes() {
	// Link settings meta box
	add_meta_box(
		'societypress_slide_link',
		__( 'Slide Link', 'societypress' ),
		'societypress_slide_link_meta_box_callback',
		'sp_slide',
		'side',
		'high'
	);

	// Video and display settings meta box
	add_meta_box(
		'societypress_slide_display',
		__( 'Display Settings', 'societypress' ),
		'societypress_slide_display_meta_box_callback',
		'sp_slide',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'societypress_add_slide_meta_boxes' );

/**
 * Render slide link meta box.
 *
 * WHY: Allows specifying where the slide should link to when clicked.
 *
 * @param WP_Post $post Current post object.
 */
function societypress_slide_link_meta_box_callback( $post ) {
	// Add nonce for security
	wp_nonce_field( 'societypress_slide_meta_nonce', 'societypress_slide_meta_nonce' );

	// Get existing values
	$slide_url    = get_post_meta( $post->ID, '_sp_slide_url', true );
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
 * Render slide display settings meta box.
 *
 * WHY: Provides controls for video background (overrides featured image)
 * and per-slide text color customization.
 *
 * @param WP_Post $post Current post object.
 */
function societypress_slide_display_meta_box_callback( $post ) {
	// Get existing values
	$video_id   = get_post_meta( $post->ID, '_sp_slide_video', true );
	$text_color = get_post_meta( $post->ID, '_sp_slide_text_color', true );

	// Default text color if not set
	if ( empty( $text_color ) ) {
		$text_color = '#ffffff';
	}

	// Get video URL if we have an ID
	$video_url = $video_id ? wp_get_attachment_url( $video_id ) : '';
	?>

	<!-- Video Background -->
	<p>
		<label for="sp_slide_video">
			<strong><?php esc_html_e( 'Video Background (MP4):', 'societypress' ); ?></strong>
		</label>
	</p>
	<p>
		<input
			type="hidden"
			id="sp_slide_video"
			name="sp_slide_video"
			value="<?php echo esc_attr( $video_id ); ?>"
		/>
		<button type="button" class="button sp-upload-video-button">
			<?php echo $video_id ? esc_html__( 'Change Video', 'societypress' ) : esc_html__( 'Select Video', 'societypress' ); ?>
		</button>
		<button type="button" class="button sp-remove-video-button" <?php echo $video_id ? '' : 'style="display:none;"'; ?>>
			<?php esc_html_e( 'Remove Video', 'societypress' ); ?>
		</button>
	</p>
	<p class="sp-video-preview" <?php echo $video_id ? '' : 'style="display:none;"'; ?>>
		<video width="100%" controls muted style="max-height: 150px;">
			<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
		</video>
	</p>
	<p class="description">
		<?php esc_html_e( 'Optional. Upload an MP4 video to use as the slide background. Video takes priority over the featured image.', 'societypress' ); ?>
	</p>

	<hr style="margin: 20px 0;" />

	<!-- Text Color -->
	<p>
		<label for="sp_slide_text_color">
			<strong><?php esc_html_e( 'Overlay Text Color:', 'societypress' ); ?></strong>
		</label>
	</p>
	<p>
		<input
			type="text"
			id="sp_slide_text_color"
			name="sp_slide_text_color"
			value="<?php echo esc_attr( $text_color ); ?>"
			class="sp-color-picker"
			data-default-color="#ffffff"
		/>
	</p>
	<p class="description">
		<?php esc_html_e( 'Choose the color for text that appears over this slide. Default is white (#ffffff).', 'societypress' ); ?>
	</p>
	<?php
}

/**
 * Enqueue admin scripts and styles for slide meta boxes.
 *
 * WHY: Provides the media uploader for video selection and color picker.
 *
 * @param string $hook Current admin page.
 */
function societypress_slide_admin_scripts( $hook ) {
	global $post_type;

	// Only load on sp_slide edit screens
	if ( 'sp_slide' !== $post_type ) {
		return;
	}

	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	// Media uploader
	wp_enqueue_media();

	// Color picker
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	// Custom admin script
	wp_add_inline_script(
		'wp-color-picker',
		"
		jQuery(document).ready(function($) {
			// Initialize color picker
			$('.sp-color-picker').wpColorPicker();

			// Video upload button
			$('.sp-upload-video-button').on('click', function(e) {
				e.preventDefault();

				var button = $(this);
				var frame = wp.media({
					title: '" . esc_js( __( 'Select or Upload Video', 'societypress' ) ) . "',
					button: { text: '" . esc_js( __( 'Use this video', 'societypress' ) ) . "' },
					library: { type: 'video' },
					multiple: false
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					$('#sp_slide_video').val(attachment.id);
					$('.sp-video-preview video source').attr('src', attachment.url);
					$('.sp-video-preview video')[0].load();
					$('.sp-video-preview').show();
					$('.sp-remove-video-button').show();
					button.text('" . esc_js( __( 'Change Video', 'societypress' ) ) . "');
				});

				frame.open();
			});

			// Remove video button
			$('.sp-remove-video-button').on('click', function(e) {
				e.preventDefault();
				$('#sp_slide_video').val('');
				$('.sp-video-preview').hide();
				$(this).hide();
				$('.sp-upload-video-button').text('" . esc_js( __( 'Select Video', 'societypress' ) ) . "');
			});
		});
		"
	);
}
add_action( 'admin_enqueue_scripts', 'societypress_slide_admin_scripts' );

/**
 * Save slide meta box data.
 *
 * WHY: Saves link URL, new tab setting, video ID, and text color.
 *
 * @param int $post_id Post ID.
 */
function societypress_save_slide_meta( $post_id ) {
	// Check nonce
	if ( ! isset( $_POST['societypress_slide_meta_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['societypress_slide_meta_nonce'], 'societypress_slide_meta_nonce' ) ) {
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

	// Save video ID
	if ( isset( $_POST['sp_slide_video'] ) ) {
		$video_id = absint( $_POST['sp_slide_video'] );
		if ( $video_id > 0 ) {
			update_post_meta( $post_id, '_sp_slide_video', $video_id );
		} else {
			delete_post_meta( $post_id, '_sp_slide_video' );
		}
	}

	// Save text color
	if ( isset( $_POST['sp_slide_text_color'] ) ) {
		$text_color = sanitize_hex_color( $_POST['sp_slide_text_color'] );
		if ( $text_color ) {
			update_post_meta( $post_id, '_sp_slide_text_color', $text_color );
		} else {
			// Default to white if invalid
			update_post_meta( $post_id, '_sp_slide_text_color', '#ffffff' );
		}
	}
}
add_action( 'save_post_sp_slide', 'societypress_save_slide_meta' );

/**
 * Add custom columns to slides list.
 *
 * WHY: Shows slide image, video, URL, text color, and group directly in the admin list.
 * Includes menu_order column for seeing/adjusting slide order.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function societypress_slide_columns( $columns ) {
	$new_columns = array(
		'cb'          => $columns['cb'],
		'image'       => __( 'Image', 'societypress' ),
		'title'       => $columns['title'],
		'slide_video' => __( 'Video', 'societypress' ),
		'slide_url'   => __( 'Link URL', 'societypress' ),
		'text_color'  => __( 'Text Color', 'societypress' ),
		'menu_order'  => __( 'Order', 'societypress' ),
		'date'        => $columns['date'],
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

		case 'slide_video':
			$video_id = get_post_meta( $post_id, '_sp_slide_video', true );
			if ( $video_id ) {
				echo '<span class="dashicons dashicons-video-alt3" style="color: #0073aa;" title="' . esc_attr__( 'Has video background', 'societypress' ) . '"></span>';
			} else {
				echo '<span style="color: #999;">—</span>';
			}
			break;

		case 'slide_url':
			$url = get_post_meta( $post_id, '_sp_slide_url', true );
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '" target="_blank" style="word-break: break-all;">' . esc_html( $url ) . '</a>';
			} else {
				echo '<span style="color: #999;">' . esc_html__( 'No link', 'societypress' ) . '</span>';
			}
			break;

		case 'text_color':
			$color = get_post_meta( $post_id, '_sp_slide_text_color', true );
			if ( ! $color ) {
				$color = '#ffffff';
			}
			echo '<span style="display: inline-block; width: 20px; height: 20px; background-color: ' . esc_attr( $color ) . '; border: 1px solid #ccc; border-radius: 3px; vertical-align: middle;"></span> ';
			echo '<code style="font-size: 11px;">' . esc_html( $color ) . '</code>';
			break;

		case 'menu_order':
			$post = get_post( $post_id );
			echo '<span style="color: #666;">' . esc_html( $post->menu_order ) . '</span>';
			break;
	}
}
add_action( 'manage_sp_slide_posts_custom_column', 'societypress_slide_column_content', 10, 2 );

/**
 * Make Order column sortable.
 *
 * @param array $columns Sortable columns.
 * @return array Modified sortable columns.
 */
function societypress_slide_sortable_columns( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
}
add_filter( 'manage_edit-sp_slide_sortable_columns', 'societypress_slide_sortable_columns' );

/**
 * Set default sort order to menu_order ascending.
 *
 * WHY: Shows slides in their display order by default, making it easier
 * to see and manage slide ordering.
 *
 * @param WP_Query $query The query object.
 */
function societypress_slide_default_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'edit-sp_slide' !== $screen->id ) {
		return;
	}

	// Only set default if no orderby is specified
	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'societypress_slide_default_order' );

/**
 * Add help text to slides admin screen.
 *
 * WHY: Guides users on how to create effective slides and use the new
 * Slide Groups feature.
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
				<p>' . __( 'Hero slides appear in sliders throughout your site using the Hero Slider widget.', 'societypress' ) . '</p>
				<h4>' . __( 'Required:', 'societypress' ) . '</h4>
				<ul>
					<li><strong>' . __( 'Title:', 'societypress' ) . '</strong> ' . __( 'Internal reference (not displayed on slide)', 'societypress' ) . '</li>
					<li><strong>' . __( 'Featured Image:', 'societypress' ) . '</strong> ' . __( 'Background image for the slide (recommended: 1920 x 800 pixels)', 'societypress' ) . '</li>
				</ul>
				<h4>' . __( 'Optional:', 'societypress' ) . '</h4>
				<ul>
					<li><strong>' . __( 'Content:', 'societypress' ) . '</strong> ' . __( 'Text that appears overlaid on the slide (keep it brief)', 'societypress' ) . '</li>
					<li><strong>' . __( 'Video Background:', 'societypress' ) . '</strong> ' . __( 'MP4 video that plays instead of the image', 'societypress' ) . '</li>
					<li><strong>' . __( 'Text Color:', 'societypress' ) . '</strong> ' . __( 'Color of the overlay text (default: white)', 'societypress' ) . '</li>
					<li><strong>' . __( 'Link URL:', 'societypress' ) . '</strong> ' . __( 'Where clicking the slide takes visitors', 'societypress' ) . '</li>
					<li><strong>' . __( 'Slide Group:', 'societypress' ) . '</strong> ' . __( 'Assign to a group like "Homepage" to use with the Hero Slider widget', 'societypress' ) . '</li>
				</ul>
				<h4>' . __( 'Using Slide Groups:', 'societypress' ) . '</h4>
				<p>' . __( 'Slide Groups let you show different slides on different pages:', 'societypress' ) . '</p>
				<ol>
					<li>' . __( 'Create slides and assign them to a group (e.g., "Homepage")', 'societypress' ) . '</li>
					<li>' . __( 'Go to Appearance → Widgets', 'societypress' ) . '</li>
					<li>' . __( 'Add the "Hero Slider" widget to the "Hero Area"', 'societypress' ) . '</li>
					<li>' . __( 'Select which slide group to display', 'societypress' ) . '</li>
				</ol>
				<h4>' . __( 'Tips:', 'societypress' ) . '</h4>
				<ul>
					<li>' . __( 'Drag slides in the list to reorder them (uses the Order column)', 'societypress' ) . '</li>
					<li>' . __( 'Only published slides appear in sliders', 'societypress' ) . '</li>
					<li>' . __( 'Use high-quality images at least 1920px wide', 'societypress' ) . '</li>
					<li>' . __( 'Keep video files under 10MB for best loading performance', 'societypress' ) . '</li>
				</ul>
			',
		)
	);
}
add_action( 'admin_head', 'societypress_slide_help_text' );
