/**
 * Hero Slider Block Editor Script
 *
 * WHY: Registers the Hero Slider block for the WordPress block editor.
 * The slide group options are passed via wp_localize_script from PHP.
 *
 * @package SocietyPress
 * @since 1.37d
 */

(function(blocks, element, blockEditor, components, i18n, serverSideRender) {
	var el = element.createElement;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var RangeControl = components.RangeControl;
	var ToggleControl = components.ToggleControl;
	var Placeholder = components.Placeholder;
	var ServerSideRender = serverSideRender;
	var __ = i18n.__;

	// Get slide group options from localized data (passed from PHP)
	var slideGroupOptions = (window.societypressHeroSlider && window.societypressHeroSlider.groupOptions) || [
		{ label: '— Select a Slide Group —', value: '' }
	];

	blocks.registerBlockType('societypress/hero-slider', {
		title: __('Hero Slider', 'societypress'),
		description: __('Display a hero slider from a specific slide group.', 'societypress'),
		icon: 'images-alt2',
		category: 'societypress',
		keywords: ['slider', 'hero', 'carousel', 'slides', 'banner'],
		supports: {
			html: false,
			anchor: true
		},
		attributes: {
			slideGroup: { type: 'string', default: '' },
			height: { type: 'number', default: 600 },
			autoplay: { type: 'boolean', default: true },
			autoplayDelay: { type: 'number', default: 5000 },
			showNavigation: { type: 'boolean', default: true },
			showPagination: { type: 'boolean', default: true }
		},

		edit: function(props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				element.Fragment,
				{},
				// Inspector controls (sidebar)
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __('Slider Settings', 'societypress'), initialOpen: true },
						el(SelectControl, {
							label: __('Slide Group', 'societypress'),
							help: __('Choose which slide group to display. Create groups under Hero Slider → Slide Groups.', 'societypress'),
							value: attributes.slideGroup,
							options: slideGroupOptions,
							onChange: function(value) { setAttributes({ slideGroup: value }); }
						}),
						el(RangeControl, {
							label: __('Slider Height (px)', 'societypress'),
							value: attributes.height,
							onChange: function(value) { setAttributes({ height: value }); },
							min: 300,
							max: 1000,
							step: 50
						})
					),
					el(
						PanelBody,
						{ title: __('Autoplay', 'societypress'), initialOpen: false },
						el(ToggleControl, {
							label: __('Enable Autoplay', 'societypress'),
							checked: attributes.autoplay,
							onChange: function(value) { setAttributes({ autoplay: value }); }
						}),
						attributes.autoplay && el(RangeControl, {
							label: __('Delay (seconds)', 'societypress'),
							value: attributes.autoplayDelay / 1000,
							onChange: function(value) { setAttributes({ autoplayDelay: value * 1000 }); },
							min: 2,
							max: 15,
							step: 1
						})
					),
					el(
						PanelBody,
						{ title: __('Controls', 'societypress'), initialOpen: false },
						el(ToggleControl, {
							label: __('Show Navigation Arrows', 'societypress'),
							checked: attributes.showNavigation,
							onChange: function(value) { setAttributes({ showNavigation: value }); }
						}),
						el(ToggleControl, {
							label: __('Show Pagination Dots', 'societypress'),
							checked: attributes.showPagination,
							onChange: function(value) { setAttributes({ showPagination: value }); }
						})
					)
				),
				// Preview - use ServerSideRender for live preview
				el(ServerSideRender, {
					block: 'societypress/hero-slider',
					attributes: attributes
				})
			);
		},

		save: function() {
			// Server-side rendered, return null
			return null;
		}
	});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.serverSideRender
);
