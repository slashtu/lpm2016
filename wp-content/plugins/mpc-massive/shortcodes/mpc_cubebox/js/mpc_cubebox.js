/*----------------------------------------------------------------------------*\
	CUBEBOX SHORTCODE
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	function calculate( $cubebox ) {
		var $front = $cubebox.find( '.mpc-cubebox__front .mpc-cubebox-side' ),
		    $side = $cubebox.find( '.mpc-cubebox__side .mpc-cubebox-side' );

		if ( $front.outerHeight() > $side.outerHeight() ) {
			$cubebox.height( $front.outerHeight() );
		} else {
			$cubebox.height( $side.outerHeight() );
		}

		$front.css( 'height', '100%' );
		$side.css( 'height', '100%' );
	}

	function responsive( $cubebox ) {
		var $front = $cubebox.find( '.mpc-cubebox__front' ),
		    $side = $cubebox.find( '.mpc-cubebox__side' );

		$front.removeAttr( 'style' );
		$side.removeAttr( 'style' );

		calculate( $cubebox );
	}

	function init_shortcode( $cubebox ) {
		if( !$cubebox.is( '.mpc-init' ) ) return;

		if( $cubebox.find( 'img' ).length > 0 ) {
			$cubebox.imagesLoaded().always( function() {
				calculate( $cubebox );
			} );
		} else {
			calculate( $cubebox );
		}

		$cubebox.trigger( 'mpc.inited' );
	}

	var $cubeboxes = $( '.mpc-cubebox' );

	$cubeboxes.each( function() {
		var $cubebox = $( this );

		$cubebox.one( 'mpc.init', function() {
			init_shortcode( $cubebox );
		} );

		$cubebox.on( 'mouseenter', function() {
			$cubebox.addClass( 'mpc-flipped' );

			if( $cubebox.find( '.mpc-parent--init' ).length ) {
				$cubebox.find( '.mpc-container' ).trigger( 'mpc.parent-init' );
				$cubebox.find( '.mpc-parent--init' ).removeClass( 'mpc-parent--init' );
			}
		}).on( 'mouseleave', function(){
			$cubebox.removeClass( 'mpc-flipped' );
		} );
	} );

	_mpc_vars.$window.on( 'mpc.resize load', function() {
		$.each( $cubeboxes, function() {
			responsive( $( this ) );
		} );
	} );

} )( jQuery );
