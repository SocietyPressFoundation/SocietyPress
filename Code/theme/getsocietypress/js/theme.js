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
        hamburger.addEventListener( 'click', function() {
            var isOpen = hamburger.classList.toggle( 'is-open' );
            mobileNav.classList.toggle( 'is-open' );

            /* Update ARIA attribute for accessibility */
            hamburger.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );

            /* Prevent body scrolling while mobile menu is open */
            document.body.style.overflow = isOpen ? 'hidden' : '';
        } );

        /* Close the mobile nav when any link inside it is clicked */
        var mobileLinks = mobileNav.querySelectorAll( 'a' );
        for ( var i = 0; i < mobileLinks.length; i++ ) {
            mobileLinks[i].addEventListener( 'click', function() {
                hamburger.classList.remove( 'is-open' );
                mobileNav.classList.remove( 'is-open' );
                hamburger.setAttribute( 'aria-expanded', 'false' );
                document.body.style.overflow = '';
            } );
        }
    }


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
