/*----------------------------------------------------------------------------*\
	MODALBOX SHORTCODE
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	function show_modal( $modal, $document ) {
		$modal.addClass( 'mpc-visible' );

		$document.addClass( 'mpc-block-scroll' );
	}

	function close_position( $modal ) {
		var $close = $modal.find( '.mpc-modal__close' );

		if ( ! $modal.is( '.mpc-close--outside' ) ) {
			return false;
		}

		if ( _mpc_vars.breakpoints.custom( '(max-width: 768px)' ) ) {
			$close.prependTo( $modal.find( '.mpc-modal' ) );
		} else {
			$close.prependTo( $modal );
		}
	}

	function init_shortcode( $modal ) {
		var $modal_box      = $modal.find( '.mpc-modal' ),
			$modal_waypoint = $( '.mpc-modal-waypoint[data-id="' + $modal.attr( 'id' ) + '"]' ),
			_delay          = parseInt( $modal.attr( 'data-delay' ) ),
			_frequency      = $modal.attr( 'data-frequency' );

		_delay = isNaN( _delay ) ? 0 : _delay;

		if ( _frequency != undefined ) {
			$.post( _mpc_vars.ajax_url, {
				action:    'mpc_set_modal_cookie',
				id:        $modal.attr( 'id' ),
				frequency: _frequency
			} );
		}

		if ( $modal_waypoint.length ) {
			if ( $modal_waypoint.is( '.mpc-waypoint--init' ) ) {
				$modal_box.trigger( 'mpc.inited' );

				show_modal( $modal, $document );
			} else {
				$modal_waypoint.on( 'mpc.waypoint', function() {
					$modal_box.trigger( 'mpc.inited' );

					show_modal( $modal, $document );
				} );
			}
		} else {
			if ( _delay > 0 ) {
				setTimeout( function() {
					$modal_box.trigger( 'mpc.inited' );

					show_modal( $modal, $document );
				}, _delay * 1000 );
			} else {
				$modal_box.trigger( 'mpc.inited' );
			}
		}

		close_position( $modal );
	}

	var $modals        = $( '.mpc-modal-overlay' ),
		$close_modals  = $( '.mpc-modal__close' ),
		$document      = $( 'html, body' );

	$modals.each( function() {
		var $modal = $( this ),
			$modal_box = $modal.find( '.mpc-modal' );

		$modal_box.one( 'mpc.init', function () {
			init_shortcode( $modal );
		} );
	});

	_mpc_vars.$window.on( 'mpc.resize', function() {
		$.each( $modals, function() {
			close_position( $( this ) );
		});
	} );

	$modals.on( 'click', function( event ) {
		if ( event.target == this ) {
			var $this = $( this );

			if ( $this.is( '.mpc-close-on-click' ) || _mpc_vars.breakpoints.custom( '(max-width: 768px)' ) ) {
				$this.find( '.mpc-modal__close' ).trigger( 'click' );
			}
		}
	} );

	$close_modals.on( 'click', function() {
		var $modal = $( this ).closest( '.mpc-modal-overlay' );

		$modal.removeClass( 'mpc-visible' );

		if ( $modals.filter( '.mpc-visible' ).length == 0 ) {
			$document.removeClass( 'mpc-block-scroll' );
		}
	});
} )( jQuery );
