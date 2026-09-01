/**
 * SocietyPress — the Highlight button for the rich text toolbar.
 *
 * WHY this is a class on a <mark> and not the colour picker TinyMCE ships with:
 * the child theme IS the society's identity, so a page has to survive being
 * looked at under a different theme. A highlight written as an inline hex value
 * does not — it keeps rendering last year's yellow on a palette built around
 * something else, and the only cure is opening every page and clearing it by
 * hand. That is the same trap the table colour pickers were, and the reason the
 * font menu applies classes instead of typefaces.
 *
 * WHY one highlight instead of a row of colours: a volunteer marking a sentence
 * is saying "look at this", not choosing a colour. One decision, reversible,
 * and a hundred pages that used it all change together if the look is ever
 * revised.
 *
 * WHY the formatter rather than wrapping the selection by hand: partial
 * selections, already-highlighted runs, and selections spanning paragraphs all
 * have to work, and hand-rolled DOM surgery gets at least one of the three
 * wrong. TinyMCE's formatter handles the toggle-off case for free.
 */
( function () {
	tinymce.PluginManager.add( 'sp_text', function ( editor ) {

		function t( str ) { return editor.translate( str ); }

		var FORMAT = 'sp_highlight';

		/*
		 * Registered on 'init' because editor.formatter does not exist until
		 * TinyMCE has built the editor; registering at plugin construction time
		 * throws before the toolbar is ever drawn.
		 */
		editor.on( 'init', function () {
			editor.formatter.register( FORMAT, {
				inline:  'mark',
				classes: 'sp-highlight',
				remove:  'all'
			} );
		} );

		editor.addButton( FORMAT, {
			text:    t( 'Highlight' ),
			icon:    false,
			tooltip: t( 'Highlight the selected text' ),

			onclick: function () {
				editor.formatter.toggle( FORMAT );
				editor.nodeChanged();
			},

			/*
			 * Keeping the button pressed while the caret sits in highlighted
			 * text is what tells a volunteer the second click will remove it.
			 * Without it the button looks like it only ever adds.
			 */
			onPostRender: function () {
				var button = this;

				editor.on( 'init', function () {
					editor.formatter.formatChanged( FORMAT, function ( state ) {
						button.active( state );
					} );
				} );
			}
		} );
	} );
}() );
