/*----------------------------------------------------------------------------*\
	GRID POSTS SHORTCODE
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	function delay_init( $grid ) {
		if ( $.fn.isotope && $.fn.imagesLoaded ) {
			init_shortcode( $grid );
		} else {
			setTimeout( function() {
				delay_init( $grid );
			}, 50 );
		}
	}

	function init_shortcode( $grid_posts ) {
		var $row = $grid_posts.parents( '.mpc-row' );

		$grid_posts.imagesLoaded().done( function() {
			$grid_posts.on( 'layoutComplete', function() {
				mpc_init_lightbox( $grid_posts, true );
				MPCwaypoint.refreshAll();
			} );

			$grid_posts.isotope( {
				itemSelector: '.mpc-post',
				layoutMode: 'masonry'
			} );
		} );

		$grid_posts.on( 'mpc.loaded', function() {
			mpc_init_lightbox( $grid_posts, true );
		});

		$row.on( 'mpc.rowResize', function() {
			if( $grid_posts.data( 'isotope' ) ) {
				$grid_posts.isotope( 'layout' );
			}
		} );

		$grid_posts.trigger( 'mpc.inited' );
	}

	if ( typeof window.InlineShortcodeView != 'undefined' ) {
		window.InlineShortcodeView_mpc_grid_posts = window.InlineShortcodeView.extend( {
			rendered: function() {
				var $grid_posts = this.$el.find( '.mpc-grid-posts' ),
					$pagination = $grid_posts.siblings( '.mpc-pagination' );

				$grid_posts.addClass( 'mpc-waypoint--init' );

				_mpc_vars.$body.trigger( 'mpc.icon-loaded', [ $grid_posts, $pagination ] );
				_mpc_vars.$body.trigger( 'mpc.font-loaded', [ $grid_posts, $pagination ] );
				_mpc_vars.$body.trigger( 'mpc.pagination-loaded', [ $pagination ] );
				_mpc_vars.$body.trigger( 'mpc.inited', [ $grid_posts, $pagination ] );

				setTimeout( function() {
					delay_init( $grid_posts );
				}, 500 );

				window.InlineShortcodeView_mpc_grid_posts.__super__.rendered.call( this );
			},
			beforeUpdate: function() {
				this.$el.find( '.mpc-grid-posts' ).isotope( 'destroy' );

				window.InlineShortcodeView_mpc_grid_posts.__super__.beforeUpdate.call( this );
			}
		} );
	}

	var $grids_posts = $( '.mpc-grid-posts' );

	$grids_posts.each( function() {
		var $grid_posts = $( this );

		$grid_posts.one( 'mpc.init', function () {
			delay_init( $grid_posts );
		} );
	});
} )( jQuery );
