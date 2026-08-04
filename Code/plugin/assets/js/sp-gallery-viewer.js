/*
 * SocietyPress — Photo Viewer
 *
 * Replaces the pure-CSS :target lightbox with a real viewer: swipe, pinch
 * and wheel zoom, a thumbnail filmstrip, fullscreen, deep-linkable photos,
 * view counts and sharing.
 *
 * WHY progressive enhancement rather than a rewrite: the :target lightbox
 * still ships inside a <noscript> block as the no-JS fallback. When this
 * file runs it intercepts thumbnail clicks before the anchor fires, so the
 * two never fight. Turn JavaScript off and the old behaviour is still
 * there.
 *
 * WHY one viewer node for the whole page: a page can hold several gallery
 * widgets. Building a viewer per photo (what the old markup did) meant N
 * full-size <img> tags in the document, and browsers download those even
 * when the container is display:none. One reused node means exactly one
 * full-size image in flight at a time.
 *
 * No dependencies. All user-facing text arrives pre-translated from PHP in
 * the config blob — never hardcode a string in here.
 */
( function () {
	'use strict';

	var config = null;
	var galleries = {};   // galleryId -> array of photo objects
	var viewer = null;    // the single reused viewer node
	var els = {};         // cached child references

	var photos = [];      // photo list for the gallery currently open
	var galleryId = '';
	var index = 0;

	var scale = 1;
	var panX = 0;
	var panY = 0;

	var MIN_SCALE = 1;
	var MAX_SCALE = 5;
	var SWIPE_DISTANCE = 60;    // px of horizontal travel that counts as a swipe
	var DISMISS_DISTANCE = 110; // px of vertical travel that closes the viewer

	var lastTrigger = null;
	var pushedHistory = false;
	var suppressPopstate = false;
	var viewedPhotos = {};      // photo id -> true, so a view counts once per page
	var pointers = {};          // pointerId -> { x, y }
	var pointerCount = 0;
	var gesture = null;
	var toastTimer = null;
	var lastTap = 0;

	/* ------------------------------------------------------------- helpers */

	function readJSON( el ) {
		if ( ! el ) {
			return null;
		}
		try {
			return JSON.parse( el.textContent );
		} catch ( e ) {
			return null;
		}
	}

	function svg( paths ) {
		return '<svg class="sp-pv-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' + paths + '</svg>';
	}

	var ICONS = {
		close:      svg( '<path d="M6 6l12 12M18 6L6 18"/>' ),
		prev:       svg( '<path d="M15 5l-7 7 7 7"/>' ),
		next:       svg( '<path d="M9 5l7 7-7 7"/>' ),
		zoomIn:     svg( '<circle cx="11" cy="11" r="7"/><path d="M11 8v6M8 11h6M16.5 16.5L21 21"/>' ),
		zoomOut:    svg( '<circle cx="11" cy="11" r="7"/><path d="M8 11h6M16.5 16.5L21 21"/>' ),
		expand:     svg( '<path d="M4 9V4h5M20 15v5h-5M20 9V4h-5M4 15v5h5"/>' ),
		eye:        svg( '<path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="2.6"/>' ),
		facebook:   svg( '<path d="M14 9h3V6h-3a3.5 3.5 0 0 0-3.5 3.5V12H8v3h2.5v6h3v-6H16l.5-3h-3v-2a1 1 0 0 1 1-1z"/>' ),
		x:          svg( '<path d="M4 4l16 16M20 4L4 20"/>' ),
		pinterest:  svg( '<circle cx="12" cy="12" r="9"/><path d="M10 20l2.2-7M9.6 11.2c0-1.9 1.5-3.4 3.4-3.4s3 1.3 3 3.1c0 2.2-1.2 3.8-2.8 3.8-.9 0-1.6-.7-1.4-1.6"/>' ),
		share:      svg( '<circle cx="18" cy="5" r="2.4"/><circle cx="6" cy="12" r="2.4"/><circle cx="18" cy="19" r="2.4"/><path d="M8.2 10.9l7.6-4.4M8.2 13.1l7.6 4.4"/>' ),
		link:       svg( '<path d="M10 13a4 4 0 0 0 5.7 0l2.6-2.6a4 4 0 0 0-5.7-5.7L11.4 6"/><path d="M14 11a4 4 0 0 0-5.7 0L5.7 13.6a4 4 0 0 0 5.7 5.7L12.6 18"/>' )
	};

	function t( key ) {
		return ( config && config.i18n && config.i18n[ key ] ) || '';
	}

	/*
	 * Fill a "%s photos" style string. WHY not a template literal: the
	 * singular/plural choice already happened in PHP via _n(), so all this
	 * has to do is substitute the number back in without assuming where in
	 * the sentence the placeholder sits — word order differs by language.
	 */
	function format( str, value ) {
		return String( str ).replace( '%s', value );
	}

	function post( action, data, done ) {
		var body = new FormData();
		body.append( 'action', action );
		body.append( 'nonce', config.nonce );
		Object.keys( data ).forEach( function ( key ) {
			body.append( key, data[ key ] );
		} );

		fetch( config.ajaxUrl, {
			method: 'POST',
			body: body,
			credentials: 'same-origin'
		} )
			.then( function ( res ) {
				return res.json();
			} )
			.then( function ( json ) {
				if ( done ) {
					done( json && json.success ? json.data : null );
				}
			} )
			.catch( function () {
				if ( done ) {
					done( null );
				}
			} );
	}

	/* --------------------------------------------------------- viewer build */

	function build() {
		if ( viewer ) {
			return;
		}

		viewer = document.createElement( 'div' );
		viewer.className = 'sp-pv';
		viewer.hidden = true;
		viewer.innerHTML =
			'<button type="button" class="sp-pv-backdrop" data-pv-close tabindex="-1" aria-hidden="true"></button>' +
			'<div class="sp-pv-shell" role="dialog" aria-modal="true" aria-label="' + escapeAttr( t( 'viewer' ) ) + '">' +
				'<header class="sp-pv-bar">' +
					'<p class="sp-pv-counter" data-pv-counter aria-live="polite"></p>' +
					'<div class="sp-pv-bar-actions">' +
						'<button type="button" class="sp-pv-btn" data-pv-zoom-out aria-label="' + escapeAttr( t( 'zoomOut' ) ) + '">' + ICONS.zoomOut + '</button>' +
						'<button type="button" class="sp-pv-btn" data-pv-zoom-in aria-label="' + escapeAttr( t( 'zoomIn' ) ) + '">' + ICONS.zoomIn + '</button>' +
						'<button type="button" class="sp-pv-btn" data-pv-fullscreen aria-label="' + escapeAttr( t( 'fullscreen' ) ) + '">' + ICONS.expand + '</button>' +
						'<button type="button" class="sp-pv-btn" data-pv-close aria-label="' + escapeAttr( t( 'close' ) ) + '">' + ICONS.close + '</button>' +
					'</div>' +
				'</header>' +
				'<div class="sp-pv-stage" data-pv-stage>' +
					'<img class="sp-pv-image" data-pv-image alt="">' +
					'<div class="sp-pv-spinner" data-pv-spinner hidden></div>' +
					'<button type="button" class="sp-pv-nav sp-pv-prev" data-pv-prev aria-label="' + escapeAttr( t( 'previous' ) ) + '">' + ICONS.prev + '</button>' +
					'<button type="button" class="sp-pv-nav sp-pv-next" data-pv-next aria-label="' + escapeAttr( t( 'next' ) ) + '">' + ICONS.next + '</button>' +
				'</div>' +
				'<footer class="sp-pv-foot">' +
					'<p class="sp-pv-caption" data-pv-caption></p>' +
					'<p class="sp-pv-credit" data-pv-credit></p>' +
					'<div class="sp-pv-meta">' +
						'<span class="sp-pv-stat" data-pv-views-wrap>' + ICONS.eye + '<span data-pv-view-count></span></span>' +
						'<span class="sp-pv-share">' +
							'<button type="button" class="sp-pv-btn" data-pv-share="facebook" aria-label="' + escapeAttr( t( 'shareFacebook' ) ) + '">' + ICONS.facebook + '</button>' +
							'<button type="button" class="sp-pv-btn" data-pv-share="x" aria-label="' + escapeAttr( t( 'shareX' ) ) + '">' + ICONS.x + '</button>' +
							'<button type="button" class="sp-pv-btn" data-pv-share="pinterest" aria-label="' + escapeAttr( t( 'sharePinterest' ) ) + '">' + ICONS.pinterest + '</button>' +
							'<button type="button" class="sp-pv-btn sp-pv-share-native" data-pv-share="native" hidden aria-label="' + escapeAttr( t( 'share' ) ) + '">' + ICONS.share + '</button>' +
							'<button type="button" class="sp-pv-btn sp-pv-copy" data-pv-share="copy" hidden aria-label="' + escapeAttr( t( 'copyLink' ) ) + '">' + ICONS.link + '</button>' +
							'<span class="sp-pv-toast" data-pv-toast hidden role="status"></span>' +
						'</span>' +
					'</div>' +
					'<div class="sp-pv-film" data-pv-film></div>' +
				'</footer>' +
			'</div>';

		document.body.appendChild( viewer );

		els = {
			shell:        viewer.querySelector( '.sp-pv-shell' ),
			stage:        viewer.querySelector( '[data-pv-stage]' ),
			image:        viewer.querySelector( '[data-pv-image]' ),
			spinner:      viewer.querySelector( '[data-pv-spinner]' ),
			counter:      viewer.querySelector( '[data-pv-counter]' ),
			caption:      viewer.querySelector( '[data-pv-caption]' ),
			credit:       viewer.querySelector( '[data-pv-credit]' ),
			prev:         viewer.querySelector( '[data-pv-prev]' ),
			next:         viewer.querySelector( '[data-pv-next]' ),
			film:         viewer.querySelector( '[data-pv-film]' ),
			viewsWrap:    viewer.querySelector( '[data-pv-views-wrap]' ),
			viewCount:    viewer.querySelector( '[data-pv-view-count]' ),
			shareNative:  viewer.querySelector( '[data-pv-share="native"]' ),
			shareCopy:    viewer.querySelector( '[data-pv-share="copy"]' ),
			toast:        viewer.querySelector( '[data-pv-toast]' )
		};

		// Only one of these two can work on a given browser, so show whichever
		// the platform actually supports rather than a button that does nothing.
		if ( navigator.share ) {
			els.shareNative.hidden = false;
		} else if ( navigator.clipboard ) {
			els.shareCopy.hidden = false;
		}

		if ( ! config.showViews ) {
			els.viewsWrap.hidden = true;
		}

		bindViewer();
	}

	function escapeHTML( str ) {
		var div = document.createElement( 'div' );
		div.textContent = str == null ? '' : String( str );
		return div.innerHTML;
	}

	function escapeAttr( str ) {
		return escapeHTML( str ).replace( /"/g, '&quot;' );
	}

	/* ---------------------------------------------------------- open/close */

	function open( id, startIndex, trigger ) {
		if ( ! galleries[ id ] || ! galleries[ id ].length ) {
			return;
		}

		build();

		galleryId = id;
		photos = galleries[ id ];
		lastTrigger = trigger || null;

		viewer.hidden = false;
		// Locking the root rather than <body> avoids the iOS quirk where a
		// body-level lock lets the page scroll behind the overlay anyway.
		document.documentElement.style.overflow = 'hidden';

		buildFilmstrip();
		show( Math.max( 0, Math.min( startIndex, photos.length - 1 ) ), false );

		if ( ! pushedHistory ) {
			try {
				history.pushState( { spPv: true }, '', '#' + targetId() );
				pushedHistory = true;
			} catch ( e ) {
				// Some embedded browsers reject pushState on a different
				// origin; the viewer works fine without the history entry.
			}
		}

		// Focus the dialog itself so a screen reader announces the label
		// before the user starts tabbing through the controls.
		els.shell.setAttribute( 'tabindex', '-1' );
		els.shell.focus();
	}

	function close() {
		if ( ! viewer || viewer.hidden ) {
			return;
		}

		viewer.hidden = true;
		document.documentElement.style.overflow = '';
		resetZoom( false );

		if ( document.fullscreenElement ) {
			document.exitFullscreen().catch( function () {} );
		}

		// Drop the src so the browser can release a large decoded bitmap
		// instead of holding it for the rest of the page's life.
		els.image.removeAttribute( 'src' );

		if ( pushedHistory && ! suppressPopstate ) {
			pushedHistory = false;
			suppressPopstate = true;
			history.back();
		} else {
			pushedHistory = false;
		}

		if ( lastTrigger && typeof lastTrigger.focus === 'function' ) {
			lastTrigger.focus();
		}
		lastTrigger = null;
	}

	function targetId() {
		var photo = photos[ index ];
		return photo ? 'sp-photo-' + galleryId + '-' + photo.id : '';
	}

	/* ------------------------------------------------------------ rendering */

	function show( next, animate ) {
		index = ( next + photos.length ) % photos.length;
		var photo = photos[ index ];

		resetZoom( false );

		els.spinner.hidden = false;
		els.image.classList.toggle( 'is-animated', !! animate );
		els.image.alt = photo.caption || '';
		els.image.src = photo.full;

		if ( els.image.complete ) {
			els.spinner.hidden = true;
		}

		els.caption.textContent = photo.caption || '';
		els.credit.textContent = photo.credit || '';
		els.counter.textContent = format(
			format( t( 'counter' ), index + 1 ).replace( '%d', photos.length ),
			photos.length
		);

		// A single-photo album has nothing to page through.
		var many = photos.length > 1;
		els.prev.hidden = ! many;
		els.next.hidden = ! many;

		renderStats( photo );
		markFilmstrip();
		preloadNeighbours();
		countView( photo );

		if ( pushedHistory ) {
			try {
				history.replaceState( { spPv: true }, '', '#' + targetId() );
			} catch ( e ) {}
		}
	}

	function renderStats( photo ) {
		els.viewCount.textContent = format( t( 'views' ), photo.views || 0 );
	}

	function buildFilmstrip() {
		els.film.textContent = '';

		if ( photos.length < 2 ) {
			return;
		}

		photos.forEach( function ( photo, i ) {
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'sp-pv-film-btn';
			btn.dataset.pvGo = String( i );
			btn.setAttribute( 'aria-label', format( t( 'goToPhoto' ), i + 1 ) );

			var img = document.createElement( 'img' );
			img.src = photo.thumb;
			img.alt = '';
			img.loading = 'lazy';
			img.decoding = 'async';

			btn.appendChild( img );
			els.film.appendChild( btn );
		} );
	}

	function markFilmstrip() {
		var buttons = els.film.children;
		for ( var i = 0; i < buttons.length; i++ ) {
			var current = i === index;
			buttons[ i ].setAttribute( 'aria-current', current ? 'true' : 'false' );
			if ( current ) {
				buttons[ i ].scrollIntoView( { block: 'nearest', inline: 'center' } );
			}
		}
	}

	/*
	 * Warm the next and previous full-size images so paging feels instant.
	 * WHY only ±1: societies upload albums of several hundred scans, and
	 * eagerly fetching more than the immediate neighbours would put a lot of
	 * megabytes on a rural connection for photos nobody may reach.
	 */
	function preloadNeighbours() {
		if ( photos.length < 2 ) {
			return;
		}
		[ index + 1, index - 1 ].forEach( function ( i ) {
			var photo = photos[ ( i + photos.length ) % photos.length ];
			if ( photo && ! photo._warmed ) {
				photo._warmed = true;
				var img = new Image();
				img.src = photo.full;
			}
		} );
	}

	/* ---------------------------------------------------------- zoom & pan */

	function applyTransform( animate ) {
		els.image.classList.toggle( 'is-animated', !! animate );
		els.image.style.transform =
			'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
		els.stage.dataset.zoomed = scale > 1 ? 'true' : 'false';
	}

	function resetZoom( animate ) {
		scale = 1;
		panX = 0;
		panY = 0;
		applyTransform( animate );
	}

	function setScale( next, animate ) {
		scale = Math.max( MIN_SCALE, Math.min( MAX_SCALE, next ) );
		if ( scale === 1 ) {
			panX = 0;
			panY = 0;
		} else {
			clampPan();
		}
		applyTransform( animate );
	}

	/*
	 * Keep the image overlapping the stage. Without this, a hard pan flings
	 * the photograph off screen and leaves the user staring at black with no
	 * obvious way back.
	 */
	function clampPan() {
		var rect = els.image.getBoundingClientRect();
		var stage = els.stage.getBoundingClientRect();

		// getBoundingClientRect already includes the scale, so undo the
		// current translation to recover the centred footprint.
		var width = rect.width;
		var height = rect.height;

		var maxX = Math.max( 0, ( width - stage.width ) / 2 );
		var maxY = Math.max( 0, ( height - stage.height ) / 2 );

		panX = Math.max( -maxX, Math.min( maxX, panX ) );
		panY = Math.max( -maxY, Math.min( maxY, panY ) );
	}

	function toggleZoom() {
		setScale( scale > 1 ? 1 : 2.5, true );
	}

	/* ------------------------------------------------------------- gestures */

	function activePointers() {
		return Object.keys( pointers );
	}

	function distance( a, b ) {
		return Math.hypot( a.x - b.x, a.y - b.y );
	}

	function onPointerDown( e ) {
		if ( e.target.closest( '.sp-pv-nav' ) ) {
			return;
		}

		pointers[ e.pointerId ] = { x: e.clientX, y: e.clientY };
		pointerCount = activePointers().length;

		if ( pointerCount === 1 ) {
			gesture = {
				startX: e.clientX,
				startY: e.clientY,
				panX: panX,
				panY: panY,
				moved: false
			};

			// Double-tap to zoom. 300ms is the window every mobile platform
			// uses for a double tap, so it matches muscle memory.
			var now = Date.now();
			if ( now - lastTap < 300 ) {
				toggleZoom();
				lastTap = 0;
				gesture = null;
				return;
			}
			lastTap = now;
		} else if ( pointerCount === 2 ) {
			var keys = activePointers();
			gesture = {
				pinch: true,
				startDistance: distance( pointers[ keys[ 0 ] ], pointers[ keys[ 1 ] ] ),
				startScale: scale
			};
		}

		try {
			els.stage.setPointerCapture( e.pointerId );
		} catch ( err ) {}
	}

	function onPointerMove( e ) {
		if ( ! pointers[ e.pointerId ] || ! gesture ) {
			return;
		}

		pointers[ e.pointerId ] = { x: e.clientX, y: e.clientY };

		if ( gesture.pinch ) {
			var keys = activePointers();
			if ( keys.length < 2 ) {
				return;
			}
			var current = distance( pointers[ keys[ 0 ] ], pointers[ keys[ 1 ] ] );
			if ( gesture.startDistance > 0 ) {
				setScale( gesture.startScale * ( current / gesture.startDistance ), false );
			}
			return;
		}

		var dx = e.clientX - gesture.startX;
		var dy = e.clientY - gesture.startY;

		if ( Math.abs( dx ) > 4 || Math.abs( dy ) > 4 ) {
			gesture.moved = true;
		}

		if ( scale > 1 ) {
			// Zoomed in: the drag pans the photograph.
			els.stage.dataset.panning = 'true';
			panX = gesture.panX + dx;
			panY = gesture.panY + dy;
			clampPan();
			applyTransform( false );
		} else {
			// Zoomed out: the drag previews a page turn or a dismiss.
			els.image.classList.remove( 'is-animated' );
			els.image.style.transform =
				'translate(' + dx + 'px,' + ( dy * 0.4 ) + 'px) scale(1)';
		}
	}

	function onPointerUp( e ) {
		if ( ! pointers[ e.pointerId ] ) {
			// A release we never saw the press for — the button came up outside
			// the window, or pointercancel arrived after pointerup and the id
			// was already dropped. Returning bare used to strand the drag
			// preview transform from onPointerMove, leaving the photograph
			// parked half off the stage until the next navigation. Settle it.
			if ( scale <= 1 && els.image.style.transform ) {
				resetZoom( true );
			}
			return;
		}

		var start = gesture;
		delete pointers[ e.pointerId ];
		pointerCount = activePointers().length;
		els.stage.dataset.panning = 'false';

		if ( ! start || start.pinch ) {
			// The remaining finger shouldn't inherit the pinch as a pan.
			gesture = null;
			if ( pointerCount === 0 && scale <= 1 ) {
				resetZoom( true );
			}
			return;
		}

		if ( scale > 1 ) {
			gesture = null;
			return;
		}

		var dx = e.clientX - start.startX;
		var dy = e.clientY - start.startY;
		gesture = null;

		if ( Math.abs( dy ) > DISMISS_DISTANCE && Math.abs( dy ) > Math.abs( dx ) ) {
			resetZoom( false );
			close();
			return;
		}

		if ( Math.abs( dx ) > SWIPE_DISTANCE && photos.length > 1 ) {
			show( dx < 0 ? index + 1 : index - 1, true );
			return;
		}

		// Not far enough to count — settle back into place.
		resetZoom( true );
	}

	/* ------------------------------------------------------------ sharing */

	function photoURL() {
		return location.origin + location.pathname + location.search + '#' + targetId();
	}

	function showToast( message ) {
		els.toast.textContent = message;
		els.toast.hidden = false;
		clearTimeout( toastTimer );
		toastTimer = setTimeout( function () {
			els.toast.hidden = true;
		}, 2200 );
	}

	function share( network ) {
		var photo = photos[ index ];
		var url = photoURL();
		var text = photo.caption || document.title;
		var endpoint = '';

		if ( network === 'native' ) {
			navigator.share( { title: document.title, text: text, url: url } )
				.catch( function () {} );
			return;
		}

		if ( network === 'copy' ) {
			navigator.clipboard.writeText( url ).then( function () {
				showToast( t( 'linkCopied' ) );
			} ).catch( function () {} );
			return;
		}

		if ( network === 'facebook' ) {
			endpoint = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent( url );
		} else if ( network === 'x' ) {
			endpoint = 'https://x.com/intent/post?url=' + encodeURIComponent( url ) +
				'&text=' + encodeURIComponent( text );
		} else if ( network === 'pinterest' ) {
			endpoint = 'https://www.pinterest.com/pin/create/button/?url=' + encodeURIComponent( url ) +
				'&media=' + encodeURIComponent( photo.full ) +
				'&description=' + encodeURIComponent( text );
		}

		if ( endpoint ) {
			window.open( endpoint, 'sp-pv-share', 'width=620,height=560,noopener,noreferrer' );
		}
	}

	/* -------------------------------------------------------------- views */

	function countView( photo ) {
		if ( viewedPhotos[ photo.id ] ) {
			return;
		}
		viewedPhotos[ photo.id ] = true;

		post( 'sp_photo_view', { item_id: photo.id }, function ( data ) {
			if ( data && typeof data.views === 'number' ) {
				photo.views = data.views;
				if ( photos[ index ] === photo ) {
					renderStats( photo );
				}
			}
		} );
	}

	/* ------------------------------------------------------------ bindings */

	function bindViewer() {
		viewer.addEventListener( 'click', function ( e ) {
			var target = e.target;

			if ( target.closest( '[data-pv-close]' ) ) {
				close();
				return;
			}
			if ( target.closest( '[data-pv-prev]' ) ) {
				show( index - 1, true );
				return;
			}
			if ( target.closest( '[data-pv-next]' ) ) {
				show( index + 1, true );
				return;
			}
			if ( target.closest( '[data-pv-zoom-in]' ) ) {
				setScale( scale + 0.5, true );
				return;
			}
			if ( target.closest( '[data-pv-zoom-out]' ) ) {
				setScale( scale - 0.5, true );
				return;
			}
			if ( target.closest( '[data-pv-fullscreen]' ) ) {
				toggleFullscreen();
				return;
			}

			var shareBtn = target.closest( '[data-pv-share]' );
			if ( shareBtn ) {
				share( shareBtn.dataset.pvShare );
				return;
			}

			var go = target.closest( '[data-pv-go]' );
			if ( go ) {
				show( parseInt( go.dataset.pvGo, 10 ), true );
			}
		} );

		els.image.addEventListener( 'load', function () {
			els.spinner.hidden = true;
		} );
		els.image.addEventListener( 'error', function () {
			els.spinner.hidden = true;
		} );

		// Native dblclick covers mouse users; the pointer handler covers taps.
		els.stage.addEventListener( 'dblclick', function ( e ) {
			if ( ! e.target.closest( '.sp-pv-nav' ) ) {
				toggleZoom();
			}
		} );

		els.stage.addEventListener( 'wheel', function ( e ) {
			// The overlay already locks page scroll, so the wheel is free to
			// mean zoom here the way it does in any photo application.
			e.preventDefault();
			setScale( scale + ( e.deltaY < 0 ? 0.25 : -0.25 ), false );
		}, { passive: false } );

		els.stage.addEventListener( 'pointerdown', onPointerDown );
		els.stage.addEventListener( 'pointermove', onPointerMove );
		els.stage.addEventListener( 'pointerup', onPointerUp );
		els.stage.addEventListener( 'pointercancel', onPointerUp );

		// Dragging the photograph onto the desktop is a download by another
		// name, so suppress the native drag.
		els.image.addEventListener( 'dragstart', function ( e ) {
			e.preventDefault();
		} );
	}

	function toggleFullscreen() {
		if ( document.fullscreenElement ) {
			document.exitFullscreen().catch( function () {} );
		} else if ( viewer.requestFullscreen ) {
			viewer.requestFullscreen().catch( function () {} );
		}
	}

	function onKeydown( e ) {
		if ( ! viewer || viewer.hidden ) {
			return;
		}

		// Typing in a field must not page the gallery out from under you.
		var typing = e.target && (
			e.target.tagName === 'TEXTAREA' ||
			e.target.tagName === 'INPUT'
		);

		if ( e.key === 'Escape' ) {
			e.preventDefault();
			close();
			return;
		}

		if ( e.key === 'Tab' ) {
			trapFocus( e );
			return;
		}

		if ( typing ) {
			return;
		}

		switch ( e.key ) {
			case 'ArrowLeft':
				e.preventDefault();
				show( index - 1, true );
				break;
			case 'ArrowRight':
				e.preventDefault();
				show( index + 1, true );
				break;
			case 'Home':
				e.preventDefault();
				show( 0, true );
				break;
			case 'End':
				e.preventDefault();
				show( photos.length - 1, true );
				break;
			case '+':
			case '=':
				e.preventDefault();
				setScale( scale + 0.5, true );
				break;
			case '-':
				e.preventDefault();
				setScale( scale - 0.5, true );
				break;
			case '0':
				e.preventDefault();
				resetZoom( true );
				break;
			case 'f':
			case 'F':
				e.preventDefault();
				toggleFullscreen();
				break;
		}
	}

	function trapFocus( e ) {
		var scope = els.shell;
		var focusable = scope.querySelectorAll(
			'a[href], button:not([disabled]), textarea, input, [tabindex]:not([tabindex="-1"])'
		);

		var visible = Array.prototype.filter.call( focusable, function ( el ) {
			return el.offsetParent !== null || el === document.activeElement;
		} );

		if ( ! visible.length ) {
			return;
		}

		var first = visible[ 0 ];
		var last = visible[ visible.length - 1 ];

		if ( e.shiftKey && document.activeElement === first ) {
			e.preventDefault();
			last.focus();
		} else if ( ! e.shiftKey && document.activeElement === last ) {
			e.preventDefault();
			first.focus();
		}
	}

	/* ---------------------------------------------------------------- init */

	function init() {
		config = readJSON( document.getElementById( 'sp-pv-config' ) );
		if ( ! config ) {
			return;
		}

		var found = false;
		document.querySelectorAll( '.sp-pv-data' ).forEach( function ( el ) {
			var data = readJSON( el );
			if ( data && data.photos && data.photos.length ) {
				galleries[ data.gallery ] = data.photos;
				found = true;
			}
		} );

		if ( ! found ) {
			return;
		}

		// Thumbnails are anchors to the no-JS :target boxes. Intercepting the
		// click here is what hands control to the viewer.
		document.addEventListener( 'click', function ( e ) {
			var thumb = e.target.closest( '.sp-gallery-thumb-link' );
			if ( ! thumb ) {
				return;
			}

			var id = thumb.dataset.pvGallery;
			var i = parseInt( thumb.dataset.pvIndex, 10 );
			if ( ! id || ! galleries[ id ] || isNaN( i ) ) {
				return;
			}

			e.preventDefault();
			open( id, i, thumb );
		} );

		document.addEventListener( 'keydown', onKeydown );

		window.addEventListener( 'popstate', function () {
			if ( ! viewer || viewer.hidden ) {
				suppressPopstate = false;
				return;
			}
			// Back closed the viewer: tear down without calling history.back()
			// again, or the page would navigate away entirely.
			suppressPopstate = true;
			close();
			suppressPopstate = false;
		} );

		openFromHash();
	}

	/*
	 * A shared link lands with #sp-photo-<gallery>-<item>. Opening that photo
	 * directly is the whole point of the share buttons.
	 */
	function openFromHash() {
		var match = /^#sp-photo-([A-Za-z0-9_-]+)-(\d+)$/.exec( location.hash );
		if ( ! match ) {
			return;
		}

		var id = match[ 1 ];
		var itemId = parseInt( match[ 2 ], 10 );
		if ( ! galleries[ id ] ) {
			return;
		}

		for ( var i = 0; i < galleries[ id ].length; i++ ) {
			if ( galleries[ id ][ i ].id === itemId ) {
				// The hash is already in the address bar, so don't push a
				// second entry for it — Back should leave the page.
				pushedHistory = true;
				open( id, i, null );
				return;
			}
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
