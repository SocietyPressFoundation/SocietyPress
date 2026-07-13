/**
 * sp-searchable-select.js — type-to-search for long member dropdowns.
 *
 * WHY: Governance pickers (committee chair, volunteer role, volunteer hours,
 *      opportunity contact) each list every active member. On a real society
 *      roster that is hundreds of names — an unscannable scroll. This upgrades
 *      any <select class="sp-searchable"> into an accessible combobox you filter
 *      by typing. The native <select> stays in the DOM as the submitted control,
 *      so no server code changes and the form still works with JavaScript off.
 *
 * Pattern: WAI-ARIA 1.2 combobox with a listbox popup and aria-activedescendant.
 */
( function () {
	'use strict';

	var L10N = window.spSearchableSelect || {};
	var TYPE_TO_SEARCH = L10N.typeToSearch || 'Type to search…';
	var NO_MATCH = L10N.noMatch || 'No matches';

	var uid = 0;

	function enhance( select ) {
		if ( select.dataset.spSs ) {
			return;
		}
		select.dataset.spSs = '1';
		uid++;

		var idBase = 'sp-ss-' + uid;

		// The wrapper takes the select's place; the select moves inside it.
		var wrap = document.createElement( 'div' );
		wrap.className = 'sp-ss';
		select.parentNode.insertBefore( wrap, select );
		wrap.appendChild( select );

		// Keep the native control submitting, but hide it from sight and from
		// assistive tech so the combobox is the single point of interaction.
		select.classList.add( 'sp-ss-native' );
		select.setAttribute( 'tabindex', '-1' );
		select.setAttribute( 'aria-hidden', 'true' );

		var label = select.id
			? document.querySelector( 'label[for="' + select.id + '"]' )
			: null;

		var input = document.createElement( 'input' );
		input.type = 'text';
		input.className = 'sp-ss-input';
		input.id = idBase + '-input';
		input.setAttribute( 'role', 'combobox' );
		input.setAttribute( 'aria-expanded', 'false' );
		input.setAttribute( 'aria-autocomplete', 'list' );
		input.setAttribute( 'aria-controls', idBase + '-list' );
		input.setAttribute( 'autocomplete', 'off' );
		input.placeholder = select.getAttribute( 'data-placeholder' ) || TYPE_TO_SEARCH;
		if ( select.required ) {
			input.setAttribute( 'aria-required', 'true' );
		}
		// Move the field's label onto the combobox the user actually operates.
		if ( label ) {
			label.setAttribute( 'for', input.id );
		}

		var list = document.createElement( 'ul' );
		list.className = 'sp-ss-list';
		list.id = idBase + '-list';
		list.setAttribute( 'role', 'listbox' );
		list.hidden = true;

		wrap.appendChild( input );
		wrap.appendChild( list );

		// Snapshot options once (value + visible label) for filtering.
		var model = [];
		var i;
		for ( i = 0; i < select.options.length; i++ ) {
			model.push( {
				value: select.options[ i ].value,
				label: select.options[ i ].text
			} );
		}

		var items = [];      // currently-rendered { li, opt }
		var activeIdx = -1;  // index into items

		function selectedLabel() {
			var o = select.options[ select.selectedIndex ];
			// An empty-value option is a placeholder — treat it as "unchosen".
			return ( o && o.value !== '' ) ? o.text : '';
		}

		function syncInputToSelection() {
			input.value = selectedLabel();
		}

		function open() {
			if ( ! list.hidden ) {
				return;
			}
			list.hidden = false;
			input.setAttribute( 'aria-expanded', 'true' );
		}

		function close() {
			if ( list.hidden ) {
				return;
			}
			list.hidden = true;
			input.setAttribute( 'aria-expanded', 'false' );
			setActive( -1 );
		}

		function setActive( idx ) {
			if ( items[ activeIdx ] ) {
				items[ activeIdx ].li.classList.remove( 'is-active' );
				items[ activeIdx ].li.setAttribute( 'aria-selected', 'false' );
			}
			activeIdx = idx;
			if ( items[ activeIdx ] ) {
				var li = items[ activeIdx ].li;
				li.classList.add( 'is-active' );
				li.setAttribute( 'aria-selected', 'true' );
				input.setAttribute( 'aria-activedescendant', li.id );
				// Keep the highlighted row within the scrolling viewport.
				var top = li.offsetTop;
				var bottom = top + li.offsetHeight;
				if ( top < list.scrollTop ) {
					list.scrollTop = top;
				} else if ( bottom > list.scrollTop + list.clientHeight ) {
					list.scrollTop = bottom - list.clientHeight;
				}
			} else {
				input.removeAttribute( 'aria-activedescendant' );
			}
		}

		function commit( opt ) {
			select.value = opt.value;
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			input.value = opt.value !== '' ? opt.label : '';
			close();
			input.focus();
		}

		function render( filter ) {
			var f = ( filter || '' ).trim().toLowerCase();
			list.innerHTML = '';
			items = [];
			var n = 0;
			model.forEach( function ( opt ) {
				if ( f && opt.label.toLowerCase().indexOf( f ) === -1 ) {
					return;
				}
				var li = document.createElement( 'li' );
				li.className = 'sp-ss-opt';
				li.id = idBase + '-opt-' + n;
				li.setAttribute( 'role', 'option' );
				li.setAttribute( 'aria-selected', 'false' );
				li.textContent = opt.label;
				if ( opt.value === select.value ) {
					li.classList.add( 'is-current' );
				}
				// mousedown (not click) so the option is chosen before the
				// input's blur can fire and tear the list down.
				li.addEventListener( 'mousedown', function ( e ) {
					e.preventDefault();
					commit( opt );
				} );
				list.appendChild( li );
				items.push( { li: li, opt: opt } );
				n++;
			} );
			if ( ! items.length ) {
				var empty = document.createElement( 'li' );
				empty.className = 'sp-ss-empty';
				empty.textContent = NO_MATCH;
				list.appendChild( empty );
			}
			setActive( items.length ? 0 : -1 );
		}

		input.addEventListener( 'focus', function () {
			render( '' );
			open();
			input.select();
		} );

		input.addEventListener( 'input', function () {
			render( input.value );
			open();
		} );

		input.addEventListener( 'keydown', function ( e ) {
			switch ( e.key ) {
				case 'ArrowDown':
					e.preventDefault();
					if ( list.hidden ) {
						render( input.value );
						open();
					}
					setActive( Math.min( activeIdx + 1, items.length - 1 ) );
					break;
				case 'ArrowUp':
					e.preventDefault();
					if ( list.hidden ) {
						render( input.value );
						open();
					}
					setActive( Math.max( activeIdx - 1, 0 ) );
					break;
				case 'Enter':
					if ( ! list.hidden && items[ activeIdx ] ) {
						e.preventDefault();
						commit( items[ activeIdx ].opt );
					}
					break;
				case 'Escape':
					if ( ! list.hidden ) {
						e.preventDefault();
						close();
						syncInputToSelection();
					}
					break;
				case 'Tab':
					close();
					syncInputToSelection();
					break;
			}
		} );

		input.addEventListener( 'blur', function () {
			// Small delay so a pending option mousedown can commit first.
			setTimeout( function () {
				close();
				syncInputToSelection();
			}, 120 );
		} );

		syncInputToSelection();
	}

	function init() {
		var selects = document.querySelectorAll( 'select.sp-searchable' );
		Array.prototype.forEach.call( selects, enhance );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
