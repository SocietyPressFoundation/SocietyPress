/**
 * SocietyPress — plain-worded picture controls for the page editor.
 *
 * WHY this exists when WordPress can already insert images: it could, and the
 * buttons were all present, but a volunteer could not find them and could not
 * make them work. Alignment lived behind a dropdown inside the media dialog or
 * an icon-only strip that only appears once you happen to click the picture,
 * and neither says the words "wrap text". The reporter on this ticket ended up
 * building a two-column table to get a picture beside a paragraph.
 *
 * WHY inserting at the start of the paragraph matters: a float only pushes
 * aside the content that comes AFTER it. Clicking at the end of a paragraph —
 * the natural place to be when you decide you want a picture — and inserting
 * there produces no wrap at all, which reads as "the wrap option is broken".
 * Every insert here goes to the front of the paragraph the caret is in, so the
 * common case works without anyone having to know why.
 *
 * The words on the menu are deliberately "Wrap text on the left", not "Align
 * left": alignment describes what happens to the picture, wrapping describes
 * what the volunteer is actually trying to achieve.
 */
( function () {
	tinymce.PluginManager.add( 'sp_image', function ( editor ) {

		function t( str ) { return editor.translate( str ); }

		var ALIGN_CLASSES = [ 'alignleft', 'alignright', 'aligncenter', 'alignnone' ];

		/**
		 * The picture the volunteer is working on, if any.
		 *
		 * Selecting a captioned picture puts the selection on the caption
		 * wrapper rather than the <img>, so look inside it before giving up.
		 */
		function currentImage() {
			var node = editor.selection.getNode();
			if ( ! node ) { return null; }
			if ( 'IMG' === node.nodeName ) { return node; }
			return editor.dom.select( 'img', node )[ 0 ] || null;
		}

		/*
		 * WordPress hangs the alignment class on the caption wrapper when there
		 * is one, and on the <img> when there is not. Putting it on the <img>
		 * inside a caption would float the picture out of its own caption.
		 */
		function alignTarget( img ) {
			return editor.dom.getParent( img, '.wp-caption' ) || img;
		}

		function needImage() {
			if ( currentImage() ) { return true; }
			editor.windowManager.alert( t( 'Click a picture first, then choose how the text should wrap around it.' ) );
			return false;
		}

		/**
		 * Move a floated picture to the front of its paragraph.
		 *
		 * Without this, a picture sitting at the end of a paragraph has no text
		 * left to flow beside it and the page looks exactly as though nothing
		 * happened.
		 */
		function moveToBlockStart( node ) {
			var parent = node.parentNode;
			if ( ! parent ) { return; }

			var block = editor.dom.getParent( parent, editor.dom.isBlock );
			if ( ! block || block === node || block === editor.getBody() ) { return; }
			if ( block.firstChild === node ) { return; }

			block.insertBefore( node, block.firstChild );
		}

		function setWrap( cls ) {
			if ( ! needImage() ) { return; }

			var el = alignTarget( currentImage() );

			ALIGN_CLASSES.forEach( function ( c ) { editor.dom.removeClass( el, c ); } );
			editor.dom.addClass( el, cls );

			if ( 'alignleft' === cls || 'alignright' === cls ) {
				moveToBlockStart( el );
			}

			editor.nodeChanged();
			editor.undoManager.add();
		}

		function removePicture() {
			if ( ! needImage() ) { return; }

			var el = alignTarget( currentImage() );

			// A captioned picture is wrapped again in .mceTemp purely so the
			// editor can select it as one unit; leaving that behind would leave
			// an empty box on the page.
			var temp = editor.dom.getParent( el, '.mceTemp' );

			editor.dom.remove( temp || el );
			editor.nodeChanged();
			editor.undoManager.add();
		}

		function attr( value ) {
			return String( value == null ? '' : value )
				.replace( /&/g, '&amp;' )
				.replace( /"/g, '&quot;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' );
		}

		/**
		 * Open the media library and drop the chosen picture in, already
		 * wrapping, already in the right place.
		 */
		function insertPicture() {
			if ( ! window.wp || ! window.wp.media ) {
				editor.windowManager.alert( t( 'The media library is still loading. Try again in a moment.' ) );
				return;
			}

			var frame = window.wp.media( {
				title:    t( 'Choose a picture' ),
				library:  { type: 'image' },
				button:   { text: t( 'Use this picture' ) },
				multiple: false
			} );

			frame.on( 'select', function () {
				var a = frame.state().get( 'selection' ).first().toJSON();

				// Medium is the size that actually sits beside a paragraph.
				// Full-size scans from a society's archive are routinely
				// thousands of pixels wide and would swallow the page.
				var size = ( a.sizes && a.sizes.medium ) ? a.sizes.medium
				         : ( a.sizes && a.sizes.full )   ? a.sizes.full
				         : { url: a.url, width: a.width, height: a.height };

				var html = '<img class="alignleft size-medium wp-image-' + parseInt( a.id, 10 ) + '"'
				         + ' src="' + attr( size.url ) + '"'
				         + ' alt="' + attr( a.alt || '' ) + '"'
				         + ( size.width  ? ' width="'  + parseInt( size.width, 10 )  + '"' : '' )
				         + ( size.height ? ' height="' + parseInt( size.height, 10 ) + '"' : '' )
				         + ' />';

				editor.insertContent( html );

				// insertContent drops it at the caret; pull it to the front of
				// the paragraph so the text beside it actually wraps.
				var inserted = editor.dom.select( 'img.wp-image-' + parseInt( a.id, 10 ), editor.getBody() );
				if ( inserted.length ) {
					moveToBlockStart( inserted[ inserted.length - 1 ] );
				}

				editor.nodeChanged();
				editor.undoManager.add();
			} );

			frame.open();
		}

		editor.addButton( 'sp_image', {
			type: 'menubutton',
			text: t( 'Picture' ),
			icon: false,
			menu: [
				{ text: t( 'Insert a picture…' ),        onclick: insertPicture },
				{ text: '-' },
				{ text: t( 'Wrap text on the left' ),    onclick: function () { setWrap( 'alignright' ); } },
				{ text: t( 'Wrap text on the right' ),   onclick: function () { setWrap( 'alignleft' ); } },
				{ text: t( 'Center it, no wrap' ),       onclick: function () { setWrap( 'aligncenter' ); } },
				{ text: t( 'Full width, no wrap' ),      onclick: function () { setWrap( 'alignnone' ); } },
				{ text: '-' },
				{ text: t( 'Remove the picture' ),       onclick: removePicture }
			]
		} );
	} );
}() );
