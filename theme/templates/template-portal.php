<?php
/**
 * Template Name: Member Portal
 * Template Post Type: page
 *
 * WHY: Member self-service portal. Works with SocietyPress plugin.
 * Allows members to update their profile, preferences, and contact info.
 *
 * @package SocietyPress
 * @since 1.28d
 */

get_header();

// Check if SocietyPress plugin is active
$plugin_active = function_exists( 'societypress' );

// Check if user is logged in
$is_logged_in = is_user_logged_in();
?>

<main id="primary" class="site-main">
	<div class="sp-container">
		<div class="content-area">

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>

				<div class="entry-content">
					<?php
					// Display any content added in the page editor
					while ( have_posts() ) :
						the_post();
						the_content();
					endwhile;

					if ( ! $plugin_active ) {
						?>
						<div class="sp-alert sp-alert--error">
							<p><?php esc_html_e( 'The SocietyPress plugin is required for the member portal.', 'societypress' ); ?></p>
						</div>
						<?php
					} elseif ( ! $is_logged_in ) {
						// Show login form for non-logged-in users
						?>
						<div class="sp-login-prompt">
							<p><?php esc_html_e( 'Please log in to access your member profile.', 'societypress' ); ?></p>
							<?php wp_login_form( array( 'redirect' => get_permalink() ) ); ?>
							<p class="sp-login-links">
								<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>">
									<?php esc_html_e( 'Forgot your password?', 'societypress' ); ?>
								</a>
							</p>
						</div>
						<?php
					} else {
						// Render the portal via shortcode (plugin handles all logic)
						echo do_shortcode( '[societypress_portal]' );
					}
					?>
				</div>

			</article>

		</div><!-- .content-area -->

		<?php get_sidebar(); ?>

	</div><!-- .sp-container -->
</main><!-- #primary -->

<?php
get_footer();
