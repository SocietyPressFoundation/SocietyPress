/**
 * SocietyPress Customizer Live Preview
 *
 * WHY: Provides instant visual feedback as users adjust customizer settings
 * without requiring page reload.
 *
 * @package SocietyPress
 * @since 1.08d
 */

(function($) {
	'use strict';

	// Primary Color
	wp.customize('societypress_primary_color', function(value) {
		value.bind(function(newval) {
			$('a, .main-navigation .primary-menu .current-menu-item > a').css('color', newval);
		});
	});

	// Header Background Color
	wp.customize('societypress_header_bg_color', function(value) {
		value.bind(function(newval) {
			$('.site-header').css('background-color', newval);
		});
	});

	// Header Text Color
	wp.customize('societypress_header_text_color', function(value) {
		value.bind(function(newval) {
			$('.main-navigation .primary-menu a, .search-toggle, .account-link').css('color', newval);
		});
	});

	// Footer Background Color
	wp.customize('societypress_footer_bg_color', function(value) {
		value.bind(function(newval) {
			$('.site-footer').css('background-color', newval);
		});
	});

	// Footer Text Color
	wp.customize('societypress_footer_text_color', function(value) {
		value.bind(function(newval) {
			$('.site-footer').css('color', newval);
		});
	});

	// Body Text Color
	wp.customize('societypress_body_text_color', function(value) {
		value.bind(function(newval) {
			$('body').css('color', newval);
		});
	});

	// Link Color
	wp.customize('societypress_link_color', function(value) {
		value.bind(function(newval) {
			$('a').css('color', newval);
		});
	});

	// Page Background Color
	wp.customize('societypress_page_bg_color', function(value) {
		value.bind(function(newval) {
			$('body').css('background-color', newval);
		});
	});

	// Body Font Size
	wp.customize('societypress_body_font_size', function(value) {
		value.bind(function(newval) {
			$('body').css('font-size', newval + 'px');
		});
	});

	// Menu Font Size
	wp.customize('societypress_menu_font_size', function(value) {
		value.bind(function(newval) {
			$('.main-navigation .primary-menu a, .search-toggle, .account-link').css('font-size', newval + 'px');
		});
	});

	// Menu Font Weight
	wp.customize('societypress_menu_font_weight', function(value) {
		value.bind(function(newval) {
			$('.main-navigation .primary-menu a, .search-toggle, .account-link').css('font-weight', newval);
		});
	});

	// Logo Height
	wp.customize('societypress_logo_height', function(value) {
		value.bind(function(newval) {
			$('.custom-logo').css('max-height', newval + 'px');
		});
	});

	// Header Padding
	wp.customize('societypress_header_padding', function(value) {
		value.bind(function(newval) {
			$('.site-branding-navigation').css('padding', newval + 'px 0');
		});
	});

	// Content Width
	wp.customize('societypress_content_width', function(value) {
		value.bind(function(newval) {
			$('.sp-container').css('max-width', newval + 'px');
		});
	});

	// Sidebar Width
	wp.customize('societypress_sidebar_width', function(value) {
		value.bind(function(newval) {
			$('.widget-area').css('flex', '0 0 ' + newval + 'px');
		});
	});

	// Footer Padding
	wp.customize('societypress_footer_padding', function(value) {
		value.bind(function(newval) {
			$('.footer-widgets').css('padding', newval + 'px 0');
		});
	});

	// Footer Columns
	wp.customize('societypress_footer_columns', function(value) {
		value.bind(function(newval) {
			$('.footer-widgets-inner').css('grid-template-columns', 'repeat(' + newval + ', 1fr)');
		});
	});

	// Button Background Color
	wp.customize('societypress_button_bg_color', function(value) {
		value.bind(function(newval) {
			$('.button, .wp-block-button__link, button[type="submit"], input[type="submit"], .read-more, .event-details-link').css('background-color', newval);
		});
	});

	// Button Text Color
	wp.customize('societypress_button_text_color', function(value) {
		value.bind(function(newval) {
			$('.button, .wp-block-button__link, button[type="submit"], input[type="submit"], .read-more, .event-details-link').css('color', newval);
		});
	});

	// Button Border Radius
	wp.customize('societypress_button_radius', function(value) {
		value.bind(function(newval) {
			$('.button, .wp-block-button__link, button[type="submit"], input[type="submit"], .read-more, .event-details-link').css('border-radius', newval + 'px');
		});
	});

	// Slider Height
	wp.customize('societypress_slider_height', function(value) {
		value.bind(function(newval) {
			$('.hero-swiper').css('height', newval + 'px');
		});
	});

	// Slider Text Color
	wp.customize('societypress_slider_text_color', function(value) {
		value.bind(function(newval) {
			$('.hero-text, .hero-excerpt, .hero-excerpt p, .hero-excerpt h1, .hero-excerpt h2, .hero-excerpt h3, .hero-excerpt h4, .hero-excerpt h5, .hero-excerpt h6').css('color', newval);
		});
	});

})(jQuery);
