/**
 * SocietyPress — dependency-free table button for the page editor.
 *
 * WHY: WordPress core ships TinyMCE without the table plugin, so the Content
 * editor on the SocietyPress page screen had no way to build a table short of
 * hand-writing HTML — something a non-technical volunteer should never have to
 * do. This registers a small vanilla TinyMCE plugin (no third-party TinyMCE
 * distribution) that adds a Table menu: insert, add/delete rows and columns,
 * plus formatting — row background color, column width, and table borders.
 * Inserted tables carry the sp-content-table class so they pick up
 * assets/css/sp-editor-table.css in the editor and on the front end; formatting
 * choices are written as inline styles so they travel with the saved content.
 */
( function () {
	tinymce.PluginManager.add( 'sp_table', function ( editor ) {

		function t( str ) { return editor.translate( str ); }

		function buildTable( rows, cols, header ) {
			var html = '<table class="sp-content-table"><tbody>';
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

		// Every formatting action needs the caret inside a table; guard with a
		// friendly nudge rather than silently doing nothing.
		function needTable() {
			if ( currentTable() ) { return true; }
			editor.windowManager.alert( t( 'Click inside a table first.' ) );
			return false;
		}

		// Normalize a stored color (which the browser reports back as rgb(...))
		// to the #rrggbb form the native color input requires.
		function toHex( c ) {
			if ( ! c ) { return ''; }
			c = ( '' + c ).trim();
			if ( c.charAt( 0 ) === '#' ) {
				return c.length === 4 ? '#' + c[ 1 ] + c[ 1 ] + c[ 2 ] + c[ 2 ] + c[ 3 ] + c[ 3 ] : c;
			}
			var m = c.match( /rgba?\((\d+),\s*(\d+),\s*(\d+)/i );
			if ( ! m ) { return ''; }
			function h( n ) { n = parseInt( n, 10 ).toString( 16 ); return n.length === 1 ? '0' + n : n; }
			return '#' + h( m[ 1 ] ) + h( m[ 2 ] ) + h( m[ 3 ] );
		}

		// Markup for a real color picker: the browser's native color chooser
		// paired with a hex box so an exact brand color can be typed or pasted.
		// The two stay in sync. Returns the field ids so the caller can read them.
		var colorSeq = 0;
		function colorFieldHtml( startValue ) {
			var id  = 'sp-color-' + ( colorSeq++ );
			var val = toHex( startValue ) || '#ffffff';
			var html =
				'<span class="sp-color-field" style="display:inline-flex;align-items:center;gap:8px;">' +
					'<input type="color" id="' + id + '" value="' + val + '" ' +
						'style="width:48px;height:32px;padding:0;border:1px solid #8c8f94;border-radius:3px;background:none;cursor:pointer;">' +
					'<input type="text" id="' + id + '-hex" value="' + val + '" maxlength="7" ' +
						'style="width:90px;height:30px;">' +
				'</span>';
			return { id: id, html: html };
		}

		// Wire the native picker and the hex box together once the dialog DOM
		// exists. windowManager.open() renders synchronously into the admin
		// document, so the elements are already queryable here.
		function bindColorField( id ) {
			var native = document.getElementById( id );
			var hex    = document.getElementById( id + '-hex' );
			if ( ! native || ! hex ) { return; }
			native.addEventListener( 'input', function () { hex.value = native.value; } );
			hex.addEventListener( 'change', function () {
				if ( /^#[0-9a-fA-F]{6}$/.test( hex.value ) ) { native.value = hex.value; }
			} );
		}

		function readColorField( id ) {
			var native = document.getElementById( id );
			return native ? native.value : '';
		}

		// Shared dialog for the table and row background pickers.
		function pickBackground( title, current, apply ) {
			var field = colorFieldHtml( current );
			editor.windowManager.open( {
				title: t( title ),
				body: [ { type: 'container', label: t( 'Color' ), html: field.html } ],
				buttons: [
					{ text: t( 'Clear color' ), onclick: function () { apply( '' ); editor.windowManager.getWindows()[ 0 ].close(); } },
					{ text: t( 'Ok' ), subtype: 'primary', onclick: function () { apply( readColorField( field.id ) ); editor.windowManager.getWindows()[ 0 ].close(); } },
					{ text: t( 'Cancel' ), onclick: function () { editor.windowManager.getWindows()[ 0 ].close(); } }
				]
			} );
			bindColorField( field.id );
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
					editor.insertContent( buildTable( rows, cols, e.data.header ) );
				}
			} );
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

		function rowColor() {
			if ( ! needTable() ) { return; }
			var row = currentRow();
			pickBackground( 'Row Background Color', editor.dom.getStyle( row, 'background-color' ), function ( c ) {
				editor.dom.setStyle( row, 'background-color', c );
			} );
		}

		function columnWidth() {
			if ( ! needTable() ) { return; }
			var cell = currentCell(), table = currentTable();
			var idx = cell.cellIndex;
			editor.windowManager.open( {
				title: t( 'Column Width' ),
				body: [
					{ type: 'textbox', name: 'width', label: t( 'Width (e.g. 25% or 150px)' ), value: editor.dom.getStyle( cell, 'width' ) || '', size: 12 }
				],
				onsubmit: function ( e ) {
					var w = ( e.data.width || '' ).trim();
					for ( var i = 0; i < table.rows.length; i++ ) {
						var cc = table.rows[ i ].cells[ idx ];
						if ( cc ) { editor.dom.setStyle( cc, 'width', w ); }
					}
				}
			} );
		}

		function tableColor() {
			if ( ! needTable() ) { return; }
			var table = currentTable();
			pickBackground( 'Table Background Color', editor.dom.getStyle( table, 'background-color' ), function ( c ) {
				editor.dom.setStyle( table, 'background-color', c );
			} );
		}

		function tableBorders() {
			if ( ! needTable() ) { return; }
			var table = currentTable();
			var field = colorFieldHtml( '#767676' );
			editor.windowManager.open( {
				title: t( 'Table Borders' ),
				body: [
					{ type: 'textbox', name: 'width', label: t( 'Thickness (px)' ), value: '1', size: 4 },
					{ type: 'listbox', name: 'style', label: t( 'Style' ), value: 'solid', values: [
						{ text: t( 'Solid' ),  value: 'solid' },
						{ text: t( 'Dashed' ), value: 'dashed' },
						{ text: t( 'Dotted' ), value: 'dotted' },
						{ text: t( 'None' ),   value: 'none' }
					] },
					{ type: 'container', label: t( 'Color' ), html: field.html }
				],
				onsubmit: function ( e ) {
					var w = parseInt( e.data.width, 10 );
					if ( isNaN( w ) || w < 0 ) { w = 1; }
					var color  = readColorField( field.id ) || '#767676';
					var border = ( e.data.style === 'none' )
						? '0'
						: w + 'px ' + e.data.style + ' ' + color;
					editor.dom.setStyle( table, 'border', border );
					var cells = table.querySelectorAll( 'td,th' );
					for ( var i = 0; i < cells.length; i++ ) {
						editor.dom.setStyle( cells[ i ], 'border', border );
					}
				}
			} );
			bindColorField( field.id );
		}

		editor.addButton( 'sp_table', {
			type: 'menubutton',
			text: t( 'Table' ),
			icon: false,
			menu: [
				{ text: t( 'Insert table' ),        onclick: insertTable },
				{ text: '-' },
				{ text: t( 'Insert row above' ),    onclick: function () { addRow( false ); } },
				{ text: t( 'Insert row below' ),    onclick: function () { addRow( true ); } },
				{ text: t( 'Delete row' ),          onclick: deleteRow },
				{ text: t( 'Row background color' ), onclick: rowColor },
				{ text: '-' },
				{ text: t( 'Insert column left' ),  onclick: function () { addColumn( false ); } },
				{ text: t( 'Insert column right' ), onclick: function () { addColumn( true ); } },
				{ text: t( 'Delete column' ),       onclick: deleteColumn },
				{ text: t( 'Column width' ),        onclick: columnWidth },
				{ text: '-' },
				{ text: t( 'Table background color' ), onclick: tableColor },
				{ text: t( 'Table borders' ),       onclick: tableBorders },
				{ text: t( 'Delete table' ),        onclick: function () { var tbl = currentTable(); if ( tbl ) { editor.dom.remove( tbl ); } } }
			]
		} );
	} );
} )();
