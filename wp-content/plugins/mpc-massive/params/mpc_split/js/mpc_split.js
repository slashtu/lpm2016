/*----------------------------------------------------------------------------*\
	MPC_SPLIT PARAM
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $split_fields = $( '.mpc-vc-split-text' ),
		$split_values = $( '.mpc-vc-split' ),
		$popup        = $( '#vc_ui-panel-edit-element' );

	$popup.one( 'mpc.render', function() {
		$split_fields.on( 'blur', function() {
			var $field = $( this ),
				_value = $field.val();

			if ( _value != '' ) {
				$field.siblings( '.mpc-vc-split' ).val( _value.replace( /\n/g, '|||' ) );
			}
		} );

		$split_values.on( 'change mpc.change', function() {
			var $field = $( this ),
				$split = $field.siblings( '.mpc-vc-split-text' ),
				_value = $field.val();

			if ( _value != '' ) {
				$split.val( _value.replace( /\|\|\|/g, '\n' ) );
			} else {
				$split.val( '' );
			}
		} );
	} );
} )( jQuery );
