/**
 * BeanBox – menu mobilne (przycisk „hamburger”).
 */
( function () {
	'use strict';
	const toggle = document.querySelector( '.nav-toggle' );
	const nav = document.getElementById( 'menu-glowne' );
	if ( ! toggle || ! nav ) {
		return;
	}
	toggle.addEventListener( 'click', function () {
		const open = nav.classList.toggle( 'is-open' );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && nav.classList.contains( 'is-open' ) ) {
			nav.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.focus();
		}
	} );
}() );
