/**
 * SocietyPress — dependency-free table button for the page editor.
 *
 * WHY: WordPress core ships TinyMCE without the table plugin, so the Content
 * editor on the SocietyPress page screen had no way to build a table short of
 * hand-writing HTML — something a non-technical volunteer should never have to
 * do. This registers a small vanilla TinyMCE plugin (no third-party TinyMCE
 * distribution) that adds a Table menu: insert, add and delete rows and
 * columns, a header-row toggle, and a gallery of ready-made table styles.
 *
 * WHY a style gallery instead of the colour, border and width pickers this
 * plugin used to carry: those wrote inline styles, so every table a volunteer
 * built looked different from every other table on the site, and none of them
 * could be revised later without opening every page. A style is a class on the
 * table. One click, no decisions about hex codes, and consistent everywhere.
 * The looks themselves live in assets/css/sp-editor-table.css.
 */
( function () {
	tinymce.PluginManager.add( 'sp_table', function ( editor ) {

		function t( str ) { return editor.translate( str ); }

		/*
		 * The gallery. `id` is the class suffix (sp-tbl-<id>) and must match a
		 * preset in sp-editor-table.css. Theme leads because it follows the
		 * society's own palette; the neutrals come next because they suit the
		 * commonest job, a list of names and dates; the colourways last.
		 */
		var STYLES = [
			{ id: 'theme',    name: 'Theme colors' },
			{ id: 'grid',     name: 'Grid' },
			{ id: 'stripe',   name: 'Stripes' },
			{ id: 'plain',    name: 'Plain' },
			{ id: 'navy',     name: 'Navy' },
			{ id: 'green',    name: 'Green' },
			{ id: 'burgundy', name: 'Burgundy' },
			{ id: 'gold',     name: 'Gold' },
			{ id: 'plum',     name: 'Plum' }
		];

		var DEFAULT_STYLE = 'theme';

		function styleClass( id ) { return 'sp-tbl-' + id; }

		function buildTable( rows, cols, header, style ) {
			var html = '<table class="sp-content-table ' + styleClass( style ) + '"><tbody>';
			for ( var r = 0; r < rows; r++ ) {
				html += '<tr>';
				var tag = ( header && r === 0 ) ? 'th' : 'td';
				for ( var c = 0; c < cols; c++ ) {
					html += '<' + tag + '>&nbsp;</' + tag + '>';
				}
				html += '</tr>';
			}
			html += '</tbody></table><p>&nbsp;</p>';
			return html;
		}

		function currentCell()  { return editor.dom.getParent( editor.selection.getStart(), 'td,th' ); }
		function currentRow()   { return editor.dom.getParent( editor.selection.getStart(), 'tr' ); }
		function currentTable() { return editor.dom.getParent( editor.selection.getStart(), 'table' ); }

		// Every action below needs the caret inside a table; guard with a
		// friendly nudge rather than silently doing nothing.
		function needTable() {
			if ( currentTable() ) { return true; }
			editor.windowManager.alert( t( 'Click inside a table first.' ) );
			return false;
		}

		function currentStyle( table ) {
			for ( var i = 0; i < STYLES.length; i++ ) {
				if ( editor.dom.hasClass( table, styleClass( STYLES[ i ].id ) ) ) {
					return STYLES[ i ].id;
				}
			}
			return '';
		}

		function applyStyle( table, id ) {
			for ( var i = 0; i < STYLES.length; i++ ) {
				editor.dom.removeClass( table, styleClass( STYLES[ i ].id ) );
			}
			editor.dom.addClass( table, 'sp-content-table' );
			editor.dom.addClass( table, styleClass( id ) );

			/*
			 * Strip the inline colours the retired pickers used to write. A
			 * table carrying background-color on its rows would sit there
			 * ignoring every style the volunteer picked, which reads as the
			 * feature being broken. Only the properties those controls set are
			 * cleared — anything else on the element is left alone.
			 */
			editor.dom.setStyle( table, 'background-color', '' );
			var i2, rows = table.rows;
			for ( i2 = 0; i2 < rows.length; i2++ ) {
				editor.dom.setStyle( rows[ i2 ], 'background-color', '' );
				var cells = rows[ i2 ].cells;
				for ( var c = 0; c < cells.length; c++ ) {
					editor.dom.setStyle( cells[ c ], 'background-color', '' );
					editor.dom.setStyle( cells[ c ], 'border', '' );
				}
			}
		}

		// One miniature: a header band plus four body rows, drawn with the
		// preset's own custom properties so a swatch can never drift from the
		// table it represents.
		function miniHtml() {
			return '<span class="sp-tbl-mini"><i></i><i></i><i></i><i></i><i></i></span>';
		}

		function pickerHtml( selected ) {
			var html = '<div class="sp-tbl-picker">';
			for ( var i = 0; i < STYLES.length; i++ ) {
				var s = STYLES[ i ];
				html += '<button type="button" class="sp-tbl-swatch ' + styleClass( s.id ) + '"' +
					' data-sp-style="' + s.id + '"' +
					' aria-pressed="' + ( s.id === selected ? 'true' : 'false' ) + '">' +
					miniHtml() +
					'<span class="sp-tbl-swatch-name">' + t( s.name ) + '</span>' +
					'</button>';
			}
			return html + '</div>';
		}

		/*
		 * WHY the click handler is wired after the dialog opens rather than
		 * inline in the markup: TinyMCE renders the container HTML into the
		 * dialog itself, so the nodes do not exist until it is on screen, and
		 * inline handlers would be stripped by the editor's own sanitizer.
		 */
		function bindPicker( onPick ) {
			var root = document.querySelector( '.sp-tbl-picker' );
			if ( ! root ) { return; }
			root.addEventListener( 'click', function ( e ) {
				var btn = e.target.closest( '.sp-tbl-swatch' );
				if ( ! btn ) { return; }
				e.preventDefault();
				var all = root.querySelectorAll( '.sp-tbl-swatch' );
				for ( var i = 0; i < all.length; i++ ) {
					all[ i ].setAttribute( 'aria-pressed', all[ i ] === btn ? 'true' : 'false' );
				}
				onPick( btn.getAttribute( 'data-sp-style' ) );
			} );
		}

		function chooseStyle() {
			if ( ! needTable() ) { return; }
			var table = currentTable();
			var start = currentStyle( table ) || DEFAULT_STYLE;

			editor.windowManager.open( {
				title: t( 'Table Style' ),
				body: [ { type: 'container', html: pickerHtml( start ) } ],
				buttons: [
					{ text: t( 'Cancel' ), onclick: function () { closeTop(); } }
				]
			} );

			// Picking a style applies it and closes — a volunteer choosing a
			// look should not then have to confirm the choice they just made.
			bindPicker( function ( id ) {
				applyStyle( table, id );
				closeTop();
			} );
		}

		function closeTop() {
			var win = editor.windowManager.getWindows()[ 0 ];
			if ( win ) { win.close(); }
		}

		function insertTable() {
			editor.windowManager.open( {
				title: t( 'Insert Table' ),
				body: [
					{ type: 'textbox',  name: 'rows',   label: t( 'Rows' ),    value: '2', size: 4 },
					{ type: 'textbox',  name: 'cols',   label: t( 'Columns' ), value: '2', size: 4 },
					{ type: 'checkbox', name: 'header', label: t( 'First row is a header' ), checked: true }
				],
				onsubmit: function ( e ) {
					var rows = Math.max( 1, Math.min( parseInt( e.data.rows, 10 ) || 1, 50 ) );
					var cols = Math.max( 1, Math.min( parseInt( e.data.cols, 10 ) || 1, 20 ) );
					editor.insertContent( buildTable( rows, cols, e.data.header, DEFAULT_STYLE ) );
				}
			} );
		}

		// Rules on or off, independent of which style is applied.
		function toggleBorders() {
			if ( ! needTable() ) { return; }
			var table = currentTable();
			editor.dom.toggleClass( table, 'sp-tbl-noborders' );

			/*
			 * Clear any border the retired Table borders control left inline.
			 * An inline border beats a stylesheet rule, so without this the
			 * toggle would appear to do nothing on an older table — on exactly
			 * the tables whose owner is most likely to be trying it.
			 */
			editor.dom.setStyle( table, 'border', '' );
			for ( var r = 0; r < table.rows.length; r++ ) {
				var cells = table.rows[ r ].cells;
				for ( var c = 0; c < cells.length; c++ ) {
					editor.dom.setStyle( cells[ c ], 'border', '' );
				}
			}
		}

		// Turn the first row into header cells, or back into ordinary ones.
		// WHY it matters beyond looks: a real <th> is what tells a screen reader
		// which column a cell belongs to.
		function toggleHeaderRow() {
			if ( ! needTable() ) { return; }
			var table = currentTable();
			var first = table.rows[ 0 ];
			if ( ! first ) { return; }

			var makeHeader = first.cells.length > 0 && first.cells[ 0 ].tagName.toLowerCase() === 'td';
			var want = makeHeader ? 'th' : 'td';

			for ( var i = first.cells.length - 1; i >= 0; i-- ) {
				var old = first.cells[ i ];
				var cell = editor.dom.create( want, {}, old.innerHTML );
				old.parentNode.replaceChild( cell, old );
			}
		}

		function addRow( after ) {
			var row = currentRow();
			if ( ! row ) { return; }
			var newRow = editor.dom.create( 'tr' );
			for ( var i = 0; i < row.cells.length; i++ ) {
				newRow.appendChild( editor.dom.create( 'td', {}, '&nbsp;' ) );
			}
			if ( after ) { editor.dom.insertAfter( newRow, row ); }
			else { row.parentNode.insertBefore( newRow, row ); }
		}

		function deleteRow() {
			var row = currentRow(), table = currentTable();
			if ( ! row || ! table ) { return; }
			if ( table.rows.length <= 1 ) { editor.dom.remove( table ); return; }
			editor.dom.remove( row );
		}

		function addColumn( after ) {
			var cell = currentCell(), table = currentTable();
			if ( ! cell || ! table ) { return; }
			var idx = cell.cellIndex;
			for ( var i = 0; i < table.rows.length; i++ ) {
				var ref = table.rows[ i ].cells[ idx ];
				if ( ! ref ) { continue; }
				var isHeader = ref.tagName.toLowerCase() === 'th';
				var newCell  = editor.dom.create( isHeader ? 'th' : 'td', {}, '&nbsp;' );
				if ( after ) { editor.dom.insertAfter( newCell, ref ); }
				else { table.rows[ i ].insertBefore( newCell, ref ); }
			}
		}

		function deleteColumn() {
			var cell = currentCell(), table = currentTable();
			if ( ! cell || ! table ) { return; }
			var idx = cell.cellIndex;
			if ( table.rows[ 0 ].cells.length <= 1 ) { editor.dom.remove( table ); return; }
			for ( var i = 0; i < table.rows.length; i++ ) {
				if ( table.rows[ i ].cells[ idx ] ) { editor.dom.remove( table.rows[ i ].cells[ idx ] ); }
			}
		}

		/* --------------------------------------------------------- row height */

		// Compact and Roomy are classes; Normal is their absence, so the
		// commonest choice leaves no markup behind.
		function setDensity( id ) {
			if ( ! needTable() ) { return; }
			var table = currentTable();
			editor.dom.removeClass( table, 'sp-tbl-compact' );
			editor.dom.removeClass( table, 'sp-tbl-roomy' );
			if ( id ) { editor.dom.addClass( table, 'sp-tbl-' + id ); }
			editor.undoManager.add();
		}

		/*
		 * Put row height back to the stylesheet's own spacing.
		 *
		 * WHY this is more than picking Normal: Normal only drops the two
		 * density classes. A table built before this existed, or pasted in from
		 * Word or a spreadsheet, can carry height and padding set inline on the
		 * rows and cells — and inline beats the stylesheet, so Compact and Roomy
		 * would both appear to do nothing on it. This clears those too, which is
		 * what someone reaching for "reset" actually wants.
		 */
		function resetRowHeight() {
			if ( ! needTable() ) { return; }
			var table = currentTable();
			editor.dom.removeClass( table, 'sp-tbl-compact' );
			editor.dom.removeClass( table, 'sp-tbl-roomy' );

			for ( var r = 0; r < table.rows.length; r++ ) {
				var row = table.rows[ r ];
				editor.dom.setStyle( row, 'height', '' );
				editor.dom.setStyle( row, 'line-height', '' );
				row.removeAttribute( 'height' );

				for ( var c = 0; c < row.cells.length; c++ ) {
					var cell = row.cells[ c ];
					editor.dom.setStyle( cell, 'height', '' );
					editor.dom.setStyle( cell, 'padding', '' );
					editor.dom.setStyle( cell, 'line-height', '' );
					cell.removeAttribute( 'height' );
				}
			}

			editor.undoManager.add();
		}

		/* ------------------------------------------------------ column widths */

		/*
		 * Widths live on a <colgroup>, not on every cell.
		 *
		 * WHY: one element per column instead of one per cell means resizing a
		 * ten-row table touches three nodes rather than thirty, the widths
		 * cannot drift out of step row to row, and wp_kses_post keeps <col> and
		 * its width style on save — checked before this was built.
		 */
		function colsOf( table ) {
			var group = table.querySelector( 'colgroup' );
			return group ? group.querySelectorAll( 'col' ) : [];
		}

		function columnCount( table ) {
			return table.rows.length ? table.rows[ 0 ].cells.length : 0;
		}

		// Build (or rebuild) the colgroup. Passing equal spreads the width
		// evenly; otherwise each column keeps the share it currently occupies,
		// so adding the group never moves anything on screen.
		function ensureColgroup( table, equal ) {
			var count = columnCount( table );
			if ( ! count ) { return []; }

			var existing = table.querySelector( 'colgroup' );
			var widths = [];
			var i;

			if ( equal || ! existing ) {
				var row = table.rows[ 0 ];
				var total = row.offsetWidth || 1;
				for ( i = 0; i < count; i++ ) {
					widths.push( equal ? ( 100 / count ) : ( row.cells[ i ].offsetWidth / total ) * 100 );
				}
			}

			if ( existing && ! equal && existing.querySelectorAll( 'col' ).length === count ) {
				return existing.querySelectorAll( 'col' );
			}

			if ( existing ) { editor.dom.remove( existing ); }

			var group = editor.dom.create( 'colgroup' );
			for ( i = 0; i < count; i++ ) {
				var col = editor.dom.create( 'col' );
				col.style.width = widths[ i ].toFixed( 2 ) + '%';
				group.appendChild( col );
			}
			table.insertBefore( group, table.firstChild );
			return group.querySelectorAll( 'col' );
		}

		function equalColumns() {
			if ( ! needTable() ) { return; }
			ensureColgroup( currentTable(), true );
			editor.undoManager.add();
		}

		function resetColumnWidths() {
			if ( ! needTable() ) { return; }
			var group = currentTable().querySelector( 'colgroup' );
			if ( group ) { editor.dom.remove( group ); }
			editor.undoManager.add();
		}

		/*
		 * Drag a column edge to resize, the way a spreadsheet does.
		 *
		 * WHY dragging rather than the "Column width" box this replaced: that
		 * box asked for "25% or 150px" — a volunteer should not have to know the
		 * difference, or guess a number and reopen the dialog to check it.
		 *
		 * Dragging moves the boundary between two neighbours: one gives up
		 * exactly what the other takes, so the table always still adds to 100%
		 * and nothing overflows the page. The last column has no neighbour to
		 * its right, so its outer edge is not a handle.
		 */
		var EDGE = 6;   // px either side of a boundary that counts as the handle
		var MIN  = 5;   // smallest a column may be squeezed to, in percent

		function boundaryAt( e ) {
			var table = editor.dom.getParent( e.target, 'table' );
			if ( ! table || ! editor.dom.hasClass( table, 'sp-content-table' ) ) { return null; }

			var row = table.rows[ 0 ];
			if ( ! row || row.cells.length < 2 ) { return null; }

			// Every boundary except the table's own right edge.
			for ( var i = 0; i < row.cells.length - 1; i++ ) {
				var edge = row.cells[ i ].getBoundingClientRect().right;
				if ( Math.abs( e.clientX - edge ) <= EDGE ) {
					return { table: table, index: i };
				}
			}
			return null;
		}

		function bindColumnResize() {
			var doc = editor.getDoc();
			var drag = null;

			editor.on( 'mousemove', function ( e ) {
				if ( drag ) {
					var deltaPct = ( ( e.clientX - drag.startX ) / drag.tableWidth ) * 100;
					var left  = drag.startLeft + deltaPct;
					var right = drag.startRight - deltaPct;

					// Clamp as a pair so the two always still total what they
					// started with — otherwise repeated drags shrink the table.
					if ( left < MIN )  { right -= ( MIN - left );  left = MIN; }
					if ( right < MIN ) { left  -= ( MIN - right ); right = MIN; }

					drag.cols[ drag.index ].style.width     = left.toFixed( 2 ) + '%';
					drag.cols[ drag.index + 1 ].style.width = right.toFixed( 2 ) + '%';
					e.preventDefault();
					return;
				}

				doc.body.style.cursor = boundaryAt( e ) ? 'col-resize' : '';
			} );

			editor.on( 'mousedown', function ( e ) {
				var hit = boundaryAt( e );
				if ( ! hit ) { return; }

				// Stop TinyMCE starting a text selection under the drag.
				e.preventDefault();

				var cols = ensureColgroup( hit.table, false );
				if ( cols.length < hit.index + 2 ) { return; }

				drag = {
					table: hit.table,
					cols: cols,
					index: hit.index,
					startX: e.clientX,
					tableWidth: hit.table.offsetWidth || 1,
					startLeft: parseFloat( cols[ hit.index ].style.width ) || 0,
					startRight: parseFloat( cols[ hit.index + 1 ].style.width ) || 0
				};
			} );

			editor.on( 'mouseup', function () {
				if ( ! drag ) { return; }
				drag = null;
				doc.body.style.cursor = '';
				editor.undoManager.add();
			} );
		}

		editor.on( 'init', bindColumnResize );

		editor.addButton( 'sp_table', {
			type: 'menubutton',
			text: t( 'Table' ),
			icon: false,
			menu: [
				{ text: t( 'Insert table' ),        onclick: insertTable },
				{ text: t( 'Table style…' ),        onclick: chooseStyle },
				{ text: t( 'Header row on/off' ),   onclick: toggleHeaderRow },
				{ text: t( 'Borders on/off' ),      onclick: toggleBorders },
				{ text: t( 'Row height' ), menu: [
					{ text: t( 'Compact' ), onclick: function () { setDensity( 'compact' ); } },
					{ text: t( 'Normal' ),  onclick: function () { setDensity( '' ); } },
					{ text: t( 'Roomy' ),   onclick: function () { setDensity( 'roomy' ); } },
					{ text: '-' },
					{ text: t( 'Reset row height' ), onclick: resetRowHeight }
				] },
				{ text: t( 'Equal columns' ),       onclick: equalColumns },
				{ text: t( 'Reset column widths' ), onclick: resetColumnWidths },
				{ text: '-' },
				{ text: t( 'Insert row above' ),    onclick: function () { addRow( false ); } },
				{ text: t( 'Insert row below' ),    onclick: function () { addRow( true ); } },
				{ text: t( 'Delete row' ),          onclick: deleteRow },
				{ text: '-' },
				{ text: t( 'Insert column left' ),  onclick: function () { addColumn( false ); } },
				{ text: t( 'Insert column right' ), onclick: function () { addColumn( true ); } },
				{ text: t( 'Delete column' ),       onclick: deleteColumn },
				{ text: '-' },
				{ text: t( 'Delete table' ),        onclick: function () { var tbl = currentTable(); if ( tbl ) { editor.dom.remove( tbl ); } } }
			]
		} );
	} );
}() );
