/*----------------------------------------------------------------------------*\
	FLIPBOX SHORTCODE
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	function calculate( $flipbox ) {
		var $front = $flipbox.find( '.mpc-flipbox__front' ),
			$back = $flipbox.find( '.mpc-flipbox__back' );

		if ( $front.outerHeight() > $back.outerHeight() ) {
			$flipbox.height( $front.outerHeight() );
		} else {
			$flipbox.height( $back.outerHeight() );
		}

		$front.css( 'height', '100%' );
		$back.css( 'height', '100%' );

		$flipbox.trigger( 'mpc.inited' );
	}

	function responsive( $flipbox ) {
		var $front = $flipbox.find( '.mpc-flipbox__front' ),
		    $side = $flipbox.find( '.mpc-flipbox__back' );

		$front.removeAttr( 'style' );
		$side.removeAttr( 'style' );

		calculate( $flipbox );
	}

	function init_shortcode( $flipbox ) {
		if( ! $flipbox.is( '.mpc-init' ) ) return;

		if ( $flipbox.find( 'img' ).length > 0 ) {
			$flipbox.imagesLoaded().always( function() {
				calculate( $flipbox );
			} );
		} else {
			calculate( $flipbox );
		}
	}

	var $flipboxes = $( '.mpc-flipbox' );

	$flipboxes.each( function() {
		var $flipbox = $( this );

		$flipbox.one( 'mpc.init', function() {
			init_shortcode( $flipbox );
		} );

		$flipbox.on( 'mouseenter', function() {
			if( $flipbox.find( '.mpc-parent--init' ).length ) {
				$flipbox.find( '.mpc-container' ).trigger( 'mpc.parent-init' );
				$flipbox.find( '.mpc-parent--init' ).removeClass( 'mpc-parent--init' );
			}
		} );
	});

	_mpc_vars.$window.on( 'mpc.resize load', function() {
		$.each( $flipboxes, function() {
			responsive( $( this ) );
		} );
	} );
} )( jQuery );
