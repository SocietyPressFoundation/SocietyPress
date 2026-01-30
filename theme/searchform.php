<?php
/**
 * Custom search form template
 *
 * WHY: Provides accessible, styled search form matching theme design.
 *
 * @package SocietyPress
 * @since 1.01d
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="search-field-<?php echo esc_attr( uniqid( 'search-' ) ); ?>">
		<span class="screen-reader-text"><?php echo esc_html_x( 'Search for:', 'label', 'societypress' ); ?></span>
	</label>
	<div class="search-form-wrapper">
		<input
			type="search"
			id="search-field-<?php echo esc_attr( uniqid( 'search-' ) ); ?>"
			class="search-field"
			placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'societypress' ); ?>"
			value="<?php echo get_search_query(); ?>"
			name="s"
		/>
		<button type="submit" class="search-submit">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
				<path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
			</svg>
			<span class="screen-reader-text"><?php echo esc_html_x( 'Search', 'submit button', 'societypress' ); ?></span>
		</button>
	</div>
</form>
