<?php
/**
 * Breadcrumbs functionality.
 *
 * WHY: Provides breadcrumb navigation to help visitors understand site hierarchy.
 *      Shows path like "Home > Events > Workshop Name" so users know where they are
 *      and can easily navigate back up the structure.
 *
 * @package SocietyPress
 * @since 1.23d
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate breadcrumb trail HTML.
 *
 * WHY: Central function that builds the breadcrumb trail based on current page context.
 *      Handles pages, posts, custom post types, archives, search, 404, etc.
 *
 * @param array $args Optional arguments to override defaults.
 * @return string Breadcrumb HTML.
 */
function societypress_get_breadcrumbs( $args = array() ) {
	// Don't show on front page.
	if ( is_front_page() ) {
		return '';
	}

	// Get settings from plugin if available, otherwise use defaults.
	$separator = '>';
	$show_icon = false;
	$home_text = 'Home';

	if ( function_exists( 'SocietyPress_Admin' ) || class_exists( 'SocietyPress_Admin' ) ) {
		$separator = SocietyPress_Admin::get_setting( 'breadcrumb_separator', '>' );
		$show_icon = SocietyPress_Admin::get_setting( 'breadcrumb_home_icon', false );
		$home_text = SocietyPress_Admin::get_setting( 'breadcrumb_home_text', 'Home' );
	}

	// Allow overrides via args.
	$defaults = array(
		'separator' => $separator,
		'show_icon' => $show_icon,
		'home_text' => $home_text,
	);
	$args = wp_parse_args( $args, $defaults );

	// Build the trail.
	$crumbs = array();

	// Home link (always first).
	$home_label = esc_html( $args['home_text'] ?: __( 'Home', 'societypress' ) );
	if ( $args['show_icon'] ) {
		$home_label = '<span class="sp-breadcrumb-home-icon" aria-hidden="true">🏠</span> ' . $home_label;
	}
	$crumbs[] = '<a href="' . esc_url( home_url( '/' ) ) . '" class="sp-breadcrumb-home">' . $home_label . '</a>';

	// Build trail based on context.
	if ( is_404() ) {
		// 404 page.
		$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . esc_html__( 'Page Not Found', 'societypress' ) . '</span>';

	} elseif ( is_search() ) {
		// Search results.
		/* translators: %s: search query */
		$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . sprintf( esc_html__( 'Search: %s', 'societypress' ), get_search_query() ) . '</span>';

	} elseif ( is_archive() ) {
		// Archive pages (category, tag, date, author, custom taxonomy, post type archive).
		if ( is_category() ) {
			$cat = get_queried_object();
			// Show parent categories if any.
			if ( $cat->parent ) {
				$parent_cats = get_ancestors( $cat->term_id, 'category' );
				$parent_cats = array_reverse( $parent_cats );
				foreach ( $parent_cats as $parent_id ) {
					$parent = get_category( $parent_id );
					$crumbs[] = '<a href="' . esc_url( get_category_link( $parent_id ) ) . '">' . esc_html( $parent->name ) . '</a>';
				}
			}
			$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . esc_html( $cat->name ) . '</span>';

		} elseif ( is_tag() ) {
			/* translators: %s: tag name */
			$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . sprintf( esc_html__( 'Tag: %s', 'societypress' ), single_tag_title( '', false ) ) . '</span>';

		} elseif ( is_author() ) {
			/* translators: %s: author name */
			$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . sprintf( esc_html__( 'Author: %s', 'societypress' ), get_the_author() ) . '</span>';

		} elseif ( is_year() ) {
			$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . get_the_date( 'Y' ) . '</span>';

		} elseif ( is_month() ) {
			$crumbs[] = '<a href="' . esc_url( get_year_link( get_the_date( 'Y' ) ) ) . '">' . get_the_date( 'Y' ) . '</a>';
			$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . get_the_date( 'F' ) . '</span>';

		} elseif ( is_day() ) {
			$crumbs[] = '<a href="' . esc_url( get_year_link( get_the_date( 'Y' ) ) ) . '">' . get_the_date( 'Y' ) . '</a>';
			$crumbs[] = '<a href="' . esc_url( get_month_link( get_the_date( 'Y' ), get_the_date( 'm' ) ) ) . '">' . get_the_date( 'F' ) . '</a>';
			$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . get_the_date( 'j' ) . '</span>';

		} elseif ( is_tax() ) {
			// Custom taxonomy archive.
			$tax = get_queried_object();
			$taxonomy = get_taxonomy( $tax->taxonomy );

			// If this taxonomy is associated with a post type, link to its archive.
			if ( ! empty( $taxonomy->object_type ) ) {
				$post_type = $taxonomy->object_type[0];
				$post_type_obj = get_post_type_object( $post_type );
				if ( $post_type_obj && $post_type_obj->has_archive ) {
					$crumbs[] = '<a href="' . esc_url( get_post_type_archive_link( $post_type ) ) . '">' . esc_html( $post_type_obj->labels->name ) . '</a>';
				}
			}

			$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . esc_html( $tax->name ) . '</span>';

		} elseif ( is_post_type_archive() ) {
			// Custom post type archive.
			$post_type_obj = get_queried_object();
			$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . esc_html( $post_type_obj->labels->name ) . '</span>';
		}

	} elseif ( is_singular() ) {
		// Single posts, pages, custom post types.
		$post = get_queried_object();
		$post_type = get_post_type();
		$post_type_obj = get_post_type_object( $post_type );

		// For custom post types with archives, link to the archive.
		if ( $post_type !== 'post' && $post_type !== 'page' && $post_type_obj && $post_type_obj->has_archive ) {
			$crumbs[] = '<a href="' . esc_url( get_post_type_archive_link( $post_type ) ) . '">' . esc_html( $post_type_obj->labels->name ) . '</a>';
		}

		// For regular posts, show category.
		if ( $post_type === 'post' ) {
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				$cat = $categories[0];
				// Show parent categories.
				if ( $cat->parent ) {
					$parent_cats = get_ancestors( $cat->term_id, 'category' );
					$parent_cats = array_reverse( $parent_cats );
					foreach ( $parent_cats as $parent_id ) {
						$parent = get_category( $parent_id );
						$crumbs[] = '<a href="' . esc_url( get_category_link( $parent_id ) ) . '">' . esc_html( $parent->name ) . '</a>';
					}
				}
				$crumbs[] = '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
			}
		}

		// For hierarchical content (pages), show ancestors.
		if ( is_page() && $post->post_parent ) {
			$ancestors = get_ancestors( $post->ID, 'page' );
			$ancestors = array_reverse( $ancestors );
			foreach ( $ancestors as $ancestor_id ) {
				$crumbs[] = '<a href="' . esc_url( get_permalink( $ancestor_id ) ) . '">' . esc_html( get_the_title( $ancestor_id ) ) . '</a>';
			}
		}

		// Current page/post (not linked).
		$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . esc_html( get_the_title() ) . '</span>';

	} elseif ( is_home() ) {
		// Blog page (when a static front page is set).
		$crumbs[] = '<span class="sp-breadcrumb-current" aria-current="page">' . esc_html__( 'Blog', 'societypress' ) . '</span>';
	}

	// Build the HTML.
	if ( count( $crumbs ) <= 1 ) {
		return ''; // Don't show if only home.
	}

	$separator_html = '<span class="sp-breadcrumb-separator" aria-hidden="true"> ' . esc_html( $args['separator'] ) . ' </span>';

	$output = '<nav class="sp-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'societypress' ) . '">';
	$output .= '<div class="sp-container">';
	$output .= implode( $separator_html, $crumbs );
	$output .= '</div>';
	$output .= '</nav>';

	return $output;
}

/**
 * Display breadcrumbs.
 *
 * WHY: Template tag for easy use in theme files.
 *
 * @param array $args Optional arguments.
 */
function societypress_breadcrumbs( $args = array() ) {
	echo societypress_get_breadcrumbs( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Breadcrumbs Widget.
 *
 * WHY: Allows breadcrumbs to be placed in any widget area via drag-and-drop.
 *      More flexible than hardcoding into templates.
 */
class SocietyPress_Breadcrumbs_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'societypress_breadcrumbs',
			__( 'SocietyPress Breadcrumbs', 'societypress' ),
			array(
				'description' => __( 'Displays breadcrumb navigation trail.', 'societypress' ),
				'classname'   => 'widget-sp-breadcrumbs',
			)
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$breadcrumbs = societypress_get_breadcrumbs();

		// Don't output anything if no breadcrumbs.
		if ( empty( $breadcrumbs ) ) {
			return;
		}

		// Widget doesn't use before_widget/after_widget wrappers for cleaner output.
		// The breadcrumb nav has its own container.
		echo $breadcrumbs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		?>
		<p>
			<?php esc_html_e( 'This widget displays breadcrumb navigation. Configure breadcrumb style in SocietyPress > Settings.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}

/**
 * Register the breadcrumbs widget.
 */
function societypress_register_breadcrumbs_widget() {
	register_widget( 'SocietyPress_Breadcrumbs_Widget' );
}
add_action( 'widgets_init', 'societypress_register_breadcrumbs_widget' );
