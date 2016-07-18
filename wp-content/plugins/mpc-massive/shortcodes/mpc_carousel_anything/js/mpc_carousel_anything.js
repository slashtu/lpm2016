/*----------------------------------------------------------------------------*\
 CAROUSEL ANYTHING SHORTCODE
 \*----------------------------------------------------------------------------*/
(function( $ ) {
	"use strict";

	function wrap_shortcode( $carousel ) {
		$carousel.children().each( function() {
			var $this = $( this );

			$this
				.addClass( 'mpc-init--fast' )
				.wrap( '<div class="mpc-carousel__item-wrapper" />' );

			setTimeout( function() {
				$this.trigger( 'mpc.init-fast' );
			}, 20 );
		} );
	}

	function unwrap_shortcode( $carousel ) {
		$carousel.find( '.vc_element' ).each( function() {
			$( this ).unwrap().unwrap();
		} );
	}

	function get_initial( $carousel ) {
		return Math.random() * $carousel.children().length;
	}

	function delay_init( $carousel ) {
		if( $.fn.mpcslick && !$carousel.is( '.slick-initialized' ) ) {
			var _initial    = $carousel.data( 'slick-random' ) == 'true' ? get_initial( $carousel ) : $carousel.data( 'slick-initial' );

			$carousel.mpcslick( {
				prevArrow: '[data-slider="' + $carousel.attr( 'id' ) + '"] .mpcslick-prev',
				nextArrow: '[data-slider="' + $carousel.attr( 'id' ) + '"] .mpcslick-next',
				adaptiveHeight: true,
				initialSlide: _initial,
				responsive: _mpc_vars.carousel_breakpoints( $carousel ),
				rtl: _mpc_vars.rtl.global()
			} );

			init_shortcode( $carousel );
		} else {
			setTimeout( function() {
				delay_init( $carousel );
			}, 50 );
		}
	}

	function init_shortcode( $carousel ) {
		$carousel.trigger( 'mpc.inited' );
	}

	if( typeof window.InlineShortcodeView != 'undefined' ) {
		window.InlineShortcodeView_mpc_carousel_anything = window.InlineShortcodeViewContainer.extend( {
			events: {
				'click > .vc_controls .vc_element .vc_control-btn-delete': 'destroy',
				'click > .vc_controls .vc_element .vc_control-btn-edit': 'edit',
				'click > .vc_controls .vc_element .vc_control-btn-clone': 'clone',
				'click > .vc_controls .vc_element .vc_control-btn-prepend': 'prependElement',
				'click > .vc_controls .vc_control-btn-append': 'appendElement',
				'click > .vc_empty-element': 'appendElement',
				'click > .mpc-carousel__wrapper .mpcslick-prev .mpc-nav__icon': 'prevSlide',
				'click > .mpc-carousel__wrapper .mpcslick-next .mpc-nav__icon': 'nextSlide'
			},
			initialize: function( params ) {
				_.bindAll( this, 'mpcUpdate' );
				window.InlineShortcodeViewContainer.__super__.initialize.call( this, params );
				this.parent_view = vc.shortcodes.get( this.model.get( 'parent_id' ) ).view;

				this.listenTo( this.model, 'update', this.mpcUpdate );
			},
			rendered: function() {
				var $carousel   = this.$el.find( '.mpc-carousel-anything' ),
				    $navigation = $carousel.siblings( '.mpc-navigation' );

				$carousel.addClass( 'mpc-waypoint--init' );

				_mpc_vars.$body.trigger( 'mpc.icon-loaded', [ $navigation ] );
				_mpc_vars.$body.trigger( 'mpc.font-loaded', [ $navigation ] );
				_mpc_vars.$body.trigger( 'mpc.navigation-loaded', [ $navigation ] );
				_mpc_vars.$body.trigger( 'mpc.inited', [ $carousel, $navigation ] );

				setTimeout( function() {
					wrap_shortcode( $carousel );
					delay_init( $carousel );
				}, 250 );

				window.InlineShortcodeView_mpc_carousel_anything.__super__.rendered.call( this );
			},
			beforeUpdate: function() {
				var $carousel = this.$el.find( '.mpc-carousel-anything' );

				$carousel.mpcslick( 'unslick' );
			},
			prevSlide: function() {
				this.$el.find( '.mpc-carousel-anything' ).mpcslick( 'slickPrev' );
			},
			nextSlide: function() {
				this.$el.find( '.mpc-carousel-anything' ).mpcslick( 'slickNext' );
			}
		} );
	}

	var $carousels_anything = $( '.mpc-carousel-anything' );

	$carousels_anything.each( function() {
		var $carousel_anything = $( this );

		wrap_shortcode( $carousel_anything );

		$carousel_anything.one( 'mpc.init', function() {
			delay_init( $carousel_anything );
		} );
	} );
})( jQuery );
