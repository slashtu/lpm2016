/*----------------------------------------------------------------------------*\
	COUNTER SHORTCODE
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	function fast_init( $this ) {
		$this.text( $this.attr( 'data-to' ) );
	}

	function delay_init( $this ) {
		if ( $this.countTo ) {
			$this.countTo( { refreshInterval: 50 } )
		} else {
			setTimeout( function() {
				delay_init( $this );
			}, 50 );
		}
	}

	function init_shortcode( $counter ) {
		$counter.trigger( 'mpc.inited' );
	}

	if ( typeof window.InlineShortcodeView != 'undefined' ) {
		window.InlineShortcodeView_mpc_counter = window.InlineShortcodeView.extend( {
			rendered: function() {
				var $counter = this.$el.find( '.mpc-counter' );

				$counter.addClass( 'mpc-waypoint--init' );

				_mpc_vars.$body.trigger( 'mpc.icon-loaded', [ $counter ] );
				_mpc_vars.$body.trigger( 'mpc.font-loaded', [ $counter ] );
				_mpc_vars.$body.trigger( 'mpc.inited', [ $counter ] );

				delay_init( $counter.find( '.mpc-counter__counter' ) );

				window.InlineShortcodeView_mpc_counter.__super__.rendered.call( this );
			}
		} );
	}

	var $counters = $( '.mpc-counter' );

	$counters.each( function() {
		var $counter = $( this ),
		    $parent = $counter.parents( '.mpc-container' );

		if( $parent.length ) {
			$parent.one( 'mpc.parent-init', function() {
				delay_init( $counter.find( '.mpc-counter__counter' ) );
			} );
		} else {
			$counter.one( 'mpc.waypoint', function() {
				if( !$counter.is( '.mpc-init--fast' ) ) {
					delay_init( $counter.find( '.mpc-counter__counter' ) );
				}
			});
		}

		$counter.one( 'mpc.init', function () {
			if( $counter.is( '.mpc-init--fast' ) ) {
				fast_init( $counter.find( '.mpc-counter__counter' ) );
			}

			init_shortcode( $counter );
		} );

		$counter.one( 'mpc.init-fast', function() {
			fast_init( $counter.find( '.mpc-counter__counter' ) );
		} );
	} );
} )( jQuery );
