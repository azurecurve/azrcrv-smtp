/*
 * Tabs
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		var tabLinks = document.querySelectorAll( '#tabs ul li a' );

		tabLinks.forEach( function ( link ) {

			// Tab switching on click or Enter key.
			link.addEventListener( 'keyup', handleTabActivation );
			link.addEventListener( 'click', handleTabActivation );

			// Hover state.
			link.addEventListener( 'mouseenter', function () {
				this.classList.add( 'azrcrv-ui-state-hover' );
			} );
			link.addEventListener( 'mouseleave', function () {
				this.classList.remove( 'azrcrv-ui-state-hover' );
			} );
		} );

		function handleTabActivation( e ) {
			if ( e.type === 'keyup' && e.key !== 'Enter' ) {
				return;
			}

			e.preventDefault();

			var targetId = this.getAttribute( 'href' );
			var targetPanel = document.querySelector( targetId );

			if ( ! targetPanel ) {
				return;
			}

			// Deactivate all tab list items.
			document.querySelectorAll( '#tabs ul li' ).forEach( function ( li ) {
				li.classList.remove( 'azrcrv-ui-state-active' );
				li.setAttribute( 'aria-selected', 'false' );
				li.setAttribute( 'aria-expanded', 'false' );
			} );

			// Activate the clicked tab list item.
			var parentLi = this.closest( 'li' );
			parentLi.classList.add( 'azrcrv-ui-state-active' );
			parentLi.setAttribute( 'aria-selected', 'true' );
			parentLi.setAttribute( 'aria-expanded', 'true' );

			// Hide all sibling panels.
			var allPanels = this.closest( 'ul' ).parentElement.querySelectorAll( ':scope > div' );
			allPanels.forEach( function ( panel ) {
				panel.classList.add( 'azrcrv-ui-tabs-hidden' );
				panel.setAttribute( 'aria-hidden', 'true' );
			} );

			// Show the target panel.
			targetPanel.classList.remove( 'azrcrv-ui-tabs-hidden' );
			targetPanel.setAttribute( 'aria-hidden', 'false' );
		}

	} );
}() );
