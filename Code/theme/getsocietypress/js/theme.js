/**
 * getsocietypress Theme JavaScript
 *
 * Vanilla JS only — no jQuery, no frameworks. Handles:
 * 1. Mobile hamburger menu toggle
 * 2. Announcement bar dismissal (sessionStorage)
 * 3. Smooth scroll for anchor links
 *
 * @package getsocietypress
 * @version 0.02d
 */

( function() {
    'use strict';

    /* ======================================================================
       1. MOBILE HAMBURGER MENU
       Toggles the mobile nav overlay and animates the hamburger icon.
       Also locks body scroll when the menu is open.
       ====================================================================== */

    var hamburger = document.getElementById( 'hamburger' );
    var mobileNav = document.getElementById( 'mobile-nav' );

    if ( hamburger && mobileNav ) {

        /* Selector for all keyboard-focusable elements inside the mobile nav */
        var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

        function closeMobileNav() {
            hamburger.classList.remove( 'is-open' );
            mobileNav.classList.remove( 'is-open' );
            hamburger.setAttribute( 'aria-expanded', 'false' );
            document.body.style.overflow = '';
            document.removeEventListener( 'keydown', trapMobileNavFocus );
            hamburger.focus();
        }

        /* Focus trap: cycle Tab/Shift+Tab within the open mobile nav; close on Escape */
        function trapMobileNavFocus( e ) {
            if ( e.key === 'Escape' ) {
                closeMobileNav();
                return;
            }

            if ( e.key !== 'Tab' ) {
                return;
            }

            var focusables = mobileNav.querySelectorAll( FOCUSABLE );
            if ( ! focusables.length ) {
                e.preventDefault();
                return;
            }

            var first = focusables[0];
            var last  = focusables[ focusables.length - 1 ];

            if ( e.shiftKey ) {
                /* Shift+Tab: if focus is on the first element, wrap to the last */
                if ( document.activeElement === first ) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                /* Tab: if focus is on the last element, wrap to the first */
                if ( document.activeElement === last ) {
                    e.preventDefault();
                    first.focus();
                }
            }
        }

        hamburger.addEventListener( 'click', function() {
            var isOpen = hamburger.classList.toggle( 'is-open' );
            mobileNav.classList.toggle( 'is-open' );

            /* Update ARIA attribute for accessibility */
            hamburger.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );

            /* Prevent body scrolling while mobile menu is open */
            document.body.style.overflow = isOpen ? 'hidden' : '';

            if ( isOpen ) {
                /* Move focus to the first focusable element inside the nav */
                var firstFocusable = mobileNav.querySelector( FOCUSABLE );
                if ( firstFocusable ) {
                    firstFocusable.focus();
                }
                /* Install focus trap while nav is open */
                document.addEventListener( 'keydown', trapMobileNavFocus );
            } else {
                document.removeEventListener( 'keydown', trapMobileNavFocus );
                hamburger.focus();
            }
        } );

        /* Close the mobile nav when any link inside it is clicked */
        var mobileLinks = mobileNav.querySelectorAll( 'a' );
        for ( var i = 0; i < mobileLinks.length; i++ ) {
            mobileLinks[i].addEventListener( 'click', function() {
                closeMobileNav();
            } );
        }
    }


    /* ======================================================================
       1b. DROP DOWN MENUS
       Category toggles in the nav reveal a panel of child links. Hover and
       focus-within handle the pointer/keyboard reveal in CSS; this wires up
       the click toggle (with aria-expanded for screen readers), closes open
       menus when you click elsewhere, and closes on Escape.
       ====================================================================== */

    var dropdownToggles = document.querySelectorAll( '.nav-dropdown__toggle' );

    function closeDropdowns( except ) {
        var open = document.querySelectorAll( '.nav-dropdown.is-open' );
        for ( var k = 0; k < open.length; k++ ) {
            if ( open[k] === except ) {
                continue;
            }
            open[k].classList.remove( 'is-open' );
            var toggle = open[k].querySelector( '.nav-dropdown__toggle' );
            if ( toggle ) {
                toggle.setAttribute( 'aria-expanded', 'false' );
            }
        }
    }

    for ( var d = 0; d < dropdownToggles.length; d++ ) {
        dropdownToggles[d].addEventListener( 'click', function( e ) {
            e.preventDefault();
            e.stopPropagation();
            var parent   = this.parentNode;
            var willOpen = ! parent.classList.contains( 'is-open' );
            closeDropdowns( parent );
            parent.classList.toggle( 'is-open', willOpen );
            this.setAttribute( 'aria-expanded', willOpen ? 'true' : 'false' );
        } );
    }

    /* Any click outside an open drop down closes it */
    document.addEventListener( 'click', function() {
        closeDropdowns( null );
    } );

    /* Escape closes the open drop down and returns focus to its toggle */
    document.addEventListener( 'keydown', function( e ) {
        if ( e.key !== 'Escape' ) {
            return;
        }
        var open = document.querySelector( '.nav-dropdown.is-open' );
        if ( open ) {
            var toggle = open.querySelector( '.nav-dropdown__toggle' );
            closeDropdowns( null );
            if ( toggle ) {
                toggle.focus();
            }
        }
    } );


    /* ======================================================================
       2. ANNOUNCEMENT BAR DISMISSAL
       When dismissed, stores a flag in sessionStorage so the bar stays
       hidden for the rest of the browser session. On page load, we check
       if the flag exists and hide the bar immediately if so.
       ====================================================================== */

    var announceBar = document.getElementById( 'announce-bar' );
    var dismissBtn  = document.getElementById( 'announce-dismiss' );

    if ( announceBar ) {
        /* Check if the user already dismissed the bar this session */
        if ( sessionStorage.getItem( 'gsp_announce_dismissed' ) === '1' ) {
            announceBar.style.display = 'none';
            document.body.classList.add( 'announce-hidden' );
        }

        /* Handle dismiss click */
        if ( dismissBtn ) {
            dismissBtn.addEventListener( 'click', function() {
                announceBar.style.display = 'none';
                document.body.classList.add( 'announce-hidden' );
                sessionStorage.setItem( 'gsp_announce_dismissed', '1' );
            } );
        }
    }


    /* ======================================================================
       3. SMOOTH SCROLL FOR ANCHOR LINKS
       Any link that starts with "#" gets smooth scroll behavior.
       We use the native scrollIntoView API rather than a library.
       ====================================================================== */

    var anchorLinks = document.querySelectorAll( 'a[href^="#"]' );

    for ( var j = 0; j < anchorLinks.length; j++ ) {
        anchorLinks[j].addEventListener( 'click', function( e ) {
            var targetId = this.getAttribute( 'href' );

            /* Skip empty hashes or "#" alone */
            if ( targetId.length <= 1 ) {
                return;
            }

            var target = document.querySelector( targetId );
            if ( target ) {
                e.preventDefault();
                target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
            }
        } );
    }

} )();
