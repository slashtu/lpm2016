/*----------------------------------------------------------------------------*\
	MAIN
\*----------------------------------------------------------------------------*/

/*----------------------------------------------------------------------------*\
	INIT
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $inits = $( '.mpc-init' ),
		$rows  = $( '.mpc-row.mpc-animation, .mpc-column.mpc-animation, .mpc-toggle-row.mpc-animation' );

	_mpc_vars.$document.ready( function() {
		$inits.trigger( 'mpc.init' );

		$rows.trigger( 'mpc.inited' );
	});

	$inits.one( 'mpc.inited', function( event ) {
		event.stopPropagation();

		var $this = $( this );

		if( ! $this.is( '.mpc-animation' ) ) {
			$this
				.velocity( {
					opacity: 1
				}, {
					duration: 250,
					delay: 100,
					begin: function() {
						$this
							.addClass( 'mpc-inited' )
							.removeClass( 'mpc-init' );
					}
				});
		} else {
			$this
				.addClass( 'mpc-inited' )
				.removeClass( 'mpc-init' );
		}
	});

} )( jQuery );

/*----------------------------------------------------------------------------*\
	ANIMATIONS
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	function init_animation( $item, _animation_in, _animation_loop, _animation_hover ) {
		if ( _animation_in != '' && ( ! _mpc_vars.breakpoints.custom( '(max-width: 768px)' ) || _mpc_vars.animations ) ) {
			var _item = new MPCwaypoint( {
				element: $item[ 0 ],
				handler: function() {
					$item.velocity( _animation_in[ 0 ], {
						duration: parseInt( _animation_in[ 1 ] ),
						delay:    parseInt( _animation_in[ 2 ] ),
						display:  null,
						complete: function() {
							$item.css( 'transform', '' );
						}
					} );

					loop_item( $item, _animation_loop, _animation_hover );

					if ( this.destroy !== undefined ) {
						this.destroy();
					}
				},
				offset: parseInt( _animation_in[ 3 ] ) + '%'
			} );

		} else {
			$item.css( 'opacity', 1 );

			loop_item( $item, _animation_loop, _animation_hover );
		}
	}

	function loop_effect( $item, _effect, _duration, _delay, _hover ) {
		if ( _hover && $item._hover ) {
			setTimeout( function() {
				loop_effect( $item, _effect, _duration, _delay, _hover );
			}, _delay );
		} else {
			$item.velocity( _effect, {
				duration: _duration,
				display:  null,
				complete: function() {
					setTimeout( function() {
						loop_effect( $item, _effect, _duration, _delay, _hover );
					}, _delay );
				}
			} );
		}
	}

	function loop_item( $item, _animation_loop, _animation_hover ) {
		if ( _animation_loop != '' ) {
			if ( parseInt( _animation_loop[ 2 ] ) == 0 ) {
				$item.velocity( _animation_loop[ 0 ], {
					duration: parseInt( _animation_loop[ 1 ] ),
					display:  null
				} );
			} else {
				if ( _animation_hover ) {
					$item.on( 'mouseenter', function() {
						$item._hover = true;
					} ).on( 'mouseleave', function() {
						$item._hover = false;
					} );
				}

				loop_effect( $item, _animation_loop[ 0 ], parseInt( _animation_loop[ 1 ] ), parseInt( _animation_loop[ 2 ] ), _animation_hover );
			}
		}
	}

	$( 'body' ).addClass( 'mpc-loaded' );

	var $animated_items = $( '.mpc-animation' );

	$animated_items.each( function() {
		var $item            = $( this ),
			_animation_in    = $item.attr( 'data-animation-in' ),
			_animation_loop  = $item.attr( 'data-animation-loop' ),
			_animation_hover = $item.attr( 'data-animation-hover' );

		_animation_in    = typeof _animation_in != 'undefined' ? _animation_in.split( '||' ) : '';
		_animation_loop  = typeof _animation_loop != 'undefined' ? _animation_loop.split( '||' ) : '';
		_animation_hover = typeof _animation_hover != 'undefined';

		$item.one( 'mpc.inited', function() {
			init_animation( $item, _animation_in, _animation_loop, _animation_hover );
		} );
	} );
} )( jQuery );

/*----------------------------------------------------------------------------*\
	WAYPOINTS
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $waypoints = $( '.mpc-waypoint' );

	$waypoints.each( function() {
		var $waypoint = $( this ),
			_inview = new MPCwaypoint.Inview( {
				element: $waypoint[ 0 ],
				enter: function() {
					$waypoint
						.addClass( 'mpc-waypoint--init' )
						.trigger( 'mpc.waypoint' );

					setTimeout( function() {
						_inview.destroy();
					}, 10 );
				}
			} );
	} );
} )( jQuery );

/*----------------------------------------------------------------------------*\
	RESIZE
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var _resize_timer;

	_mpc_vars.window_width = _mpc_vars.$window.width();
	_mpc_vars.window_height = _mpc_vars.$window.height();

	_mpc_vars.$window.on( 'resize', function() {
		clearTimeout( _resize_timer );

		_resize_timer = setTimeout( function() {
			_mpc_vars.window_width = _mpc_vars.$window.width();
			_mpc_vars.window_height = _mpc_vars.$window.height();

			_mpc_vars.$window.trigger( 'mpc.resize' );
		}, 250 );
	} );
} )( jQuery );

/*----------------------------------------------------------------------------*\
	VC Row Stretch trigger workaround...
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $rows = $( '[data-vc-full-width="true"]' );

	function mpc_stretch_row_trigger( $row ) {
		if( $row.attr( 'data-vc-full-width-init' ) == 'true' ) {
			$row.trigger( 'mpc.rowResize' );
		} else {
			setTimeout( function() {
				mpc_stretch_row_trigger( $row );
			}, 100 );
		}
	}

	$.each( $rows, function() {
		mpc_stretch_row_trigger( $( this ) );
	} );

} )( jQuery );

/*----------------------------------------------------------------------------*\
 Magnific Popup init
\*----------------------------------------------------------------------------*/
var mpc_init_lightbox;
( function( $ ) {
	mpc_init_lightbox = function( $element, _is_gallery ) {
		var $lightbox = '',
			_vendor = '';

		if( $element.is( '.mpc-pretty-photo' ) || $element.find( '.mpc-pretty-photo' ).length ) {
			_vendor = 'prettyphoto';
		} else if( $element.is( '.mpc-magnific-popup' ) || $element.find( '.mpc-magnific-popup' ).length ) {
			_vendor = 'magnificPopup'
		}

		if( _vendor == '' ) {
			return;
		}

		if( $.fn.prettyPhoto && _vendor == 'prettyphoto' ) {
			$lightbox = $element.is( '.mpc-pretty-photo' ) ? $element : $element.find( '.mpc-pretty-photo' );

			$lightbox.prettyPhoto( {
				animationSpeed: 'normal',
				padding: 15,
				opacity: 0.7,
				showTitle: true,
				allowresize: false,
				hideflash: true,
				modal: false,
				social_tools: '',
				overlay_gallery: false,
				deeplinking: false,
				ie6_fallback: false
			} );
		} else if( $.fn.magnificPopup && _vendor == 'magnificPopup' ) {
			$lightbox = $element.is( '.mpc-magnific-popup' ) ? $element : $element.find( '.mpc-magnific-popup' );

			var _type = /\.(gif|jpg|jpeg|tiff|png)/i.test( $lightbox.attr( 'href' ) ) ? 'image' : 'iframe',
				_atts;

			_atts = {
				type: _type,
				closeOnContentClick: true,
				mainClass:           'mfp-img-mobile',
				image:               {
					verticalFit: true
				},
				callbacks:           {
					beforeOpen: function() {
						_mpc_vars.$window.trigger( 'mpc.lightbox.open' );
					},
					afterClose: function() {
						_mpc_vars.$window.trigger( 'mpc.lightbox.close' );
					}
				},
				iframe: {
					patterns:  {
						youtube: {
							id: function( _url ) {
								var _re = /https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\S*?[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:['"][^<>]*>|<\/a>))[?=&+%\w.-]*/ig;

								var _id =  _re.exec( _url );
								return typeof _id[ 1 ] !== typeof undefined ? _id[ 1 ] : '';
							},
							src: '//www.youtube.com/embed/%id%' // URL that will be set as a source for iframe.
						}
					}
				}
			};


			if( _is_gallery ) {
				_atts.gallery = {
					enabled: true,
					preload: [0,1],
					tCounter: ''
				}
			}

			$lightbox.magnificPopup( _atts );
		} else {
			setTimeout( mpc_init_lightbox( $element, _is_gallery ), 250 );
		}
	};
} )( jQuery );