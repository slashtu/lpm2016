

/*----------------------------------------------------------------------------*\
	BUTTON SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $popup = $( '#vc_ui-panel-edit-element' );

	$popup.on( 'mpc.render', function() {
		if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_button' ) {
			return;
		}

		if ( vc.shortcodes.findWhere( { id: vc.active_panel.model.attributes.parent_id } ).get( 'shortcode' ) == 'mpc_button_set' ) {
			$popup.find( '.vc_shortcode-param[data-vc-shortcode-param-name="block"]' ).hide();
		}
	} );
} )( jQuery );





/*----------------------------------------------------------------------------*\
	CAROUSEL POSTS SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
    "use strict";

    var $popup      = $( '#vc_ui-panel-edit-element' ),
        _hide_class = 'vc_dependent-hidden',
        _overlay    = false,
        _readmore   = false;

    function section_dependency( _dependencies, _value ) {
        $.each( _dependencies, function() {
            var $section  = $popup.find( '[data-vc-shortcode-param-name="' + this + '"]' ),
                $siblings = $section.siblings( '.mpc-vc-indent' );

            if( _value === true ) {
                $siblings.addClass( _hide_class );
                $section.addClass( _hide_class );
            } else {
                $siblings.removeClass( _hide_class );
                $section.removeClass( _hide_class );
            }
        } );
    }

    function overlay_tab_toggle() {
        var _params = $popup.find( '[data-vc-shortcode-param-name="overlay_section_divider"]' ).data( 'param_settings' ),
            _group_name = _params.group;

        $.each( $popup.find( '[data-vc-ui-element="panel-tabs-controls"] li' ), function() {
            var $this = $( this );

            if( $this.find( 'button' ).text() == _group_name ) {
                if( _overlay === true ) {
                    $this.addClass( _hide_class );
                } else {
                    $this.removeClass( _hide_class );
                }
            }
        } );
    }

    function readmore_tab_toggle() {
        var _params     = $popup.find( '[data-vc-shortcode-param-name="mpc_button__disable"]' ).data( 'param_settings' ),
            _group_name = _params.group;

        $.each( $popup.find( '[data-vc-ui-element="panel-tabs-controls"] li' ), function() {
            var $this = $( this );

            if( $this.find( 'button' ).text() == _group_name ) {
                if( _readmore === true ) {
                    $this.addClass( _hide_class );
                } else {
                    $this.removeClass( _hide_class );
                }
            }
        } );
    }

    function thumbnail_dependency( _value ) {
        var _dependencies = [ 'items_section_divider' ];
        section_dependency( _dependencies, _value );
    }

    function title_dependency( _overlay_value ) {
        var _layout = $popup.find( '[name="layout"]' ).val(),
            _dependencies = [ 'title_margin_divider' ],
            _overlay_dependencies = [ 'overlay_title_section_divider', 'overlay_title_margin_divider' ];

        if( _layout == 'style_8' && _overlay_value ) {
            section_dependency( _dependencies, false );
        } else {
            section_dependency( _dependencies, true );
        }

        if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_6', 'style_7', 'style_8' ] ) > -1 && !_overlay_value ) {
            section_dependency( _overlay_dependencies, false );
        } else {
            section_dependency( _overlay_dependencies, true );
        }
    }

    function description_dependency( _overlay_value ) {
        var _layout = $popup.find( '[name="layout"]' ).val(),
            _dependencies_base = [ 'description_section_divider' ],
            _dependencies = [ 'description_font_divider', 'description_padding_divider', 'description_margin_divider' ],
            _overlay_dependencies = [ 'overlay_description_section_divider', 'overlay_description_padding_divider', 'overlay_description_margin_divider' ];

        if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_7', 'style_8' ] ) > -1 || _overlay_value ) {
            section_dependency( _dependencies, true );
        } else {
            section_dependency( _dependencies, false );
        }

        if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_7', 'style_8' ] ) > -1 ) {
            section_dependency( _dependencies_base, true );
        } else {
            section_dependency( _dependencies_base, false );
        }

        if( _layout == 'style_6' || _overlay_value ) {
            section_dependency( _overlay_dependencies, true );
        } else {
            section_dependency( _overlay_dependencies, true );
        }
    }

    function check_date_dependency() {
        // Based on layout, thumbnail for style 5, meta data enable
        var _layout    = $popup.find( '[name="layout"]' ).val(),
            _enabled   = $popup.find( '[name="meta_layout-option_date"]' ).is( ':checked' ),
            _thumbnail = $popup.find( '[name="disable_thumbnail"]' ).is( ':checked' ),
            _disable   = true,
            _disable_at_overlay = _layout == 'style_6' && _enabled ? false : true;

        // Disable if date not selected
        if( !_enabled ) {
            date_dependency( _disable, _disable_at_overlay );
            return false;
        }

        // Date is enabled, check if layout needs date settings
        if( $.inArray( _layout, [ 'style_3', 'style_5', 'style_6' ] ) > -1 ) {
            // Check if layout has overlay enabled
            _disable = _thumbnail && _layout == 'style_5';
        }

        date_dependency( _disable, _disable_at_overlay );
    }

    function date_dependency( _value, _overlay_value ) {
        var _layout = $popup.find( '[name="layout"]' ).val(),
            _dependencies = [ 'date_font_divider', 'date_border_divider', 'date_padding_divider', 'date_margin_divider'],
            _overlay_dependencies = [ 'overlay_date_section_divider', 'overlay_date_padding_divider', 'overlay_date_margin_divider' ];

        _overlay_value = _layout == 'style_6' ? _overlay_value : true;

        section_dependency( _dependencies, _value );

        section_dependency( _overlay_dependencies, _overlay_value );
    }

    function meta_dependency( _value ) {
        var _layout = $popup.find( '[name="layout"]' ).val(),
            _dependencies = [ 'meta_font_divider', 'meta_margin_divider'],
            _overlay_dependencies = [ 'overlay_meta_section_divider', 'overlay_meta_margin_divider' ];

        section_dependency( _dependencies, _value );

        if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_6', 'style_7', 'style_8' ] ) > -1 && !_value ) {
            section_dependency( _overlay_dependencies, false );
        } else {
            section_dependency( _overlay_dependencies, true );
        }
    }

    function layout_dependency( _layout, _thumbnail ) {
        /* Trigger Thumbnail dependency */
        if( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5' ] ) > -1 ) {
            thumbnail_dependency( _thumbnail );
        } else {
            thumbnail_dependency( false );
        }

        if( _layout == 'style_9' || ( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5' ] ) > -1 && _thumbnail ) ) {
            _overlay = true;
            overlay_tab_toggle();
        } else {
            _overlay = false;
            overlay_tab_toggle( false );
        }

        /* Read More */
        if( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5', 'style_9' ] ) == -1 ) {
            _readmore = true;
            readmore_tab_toggle();
        } else {
            _readmore = false;
            readmore_tab_toggle();
        }
    }

    $popup.on( 'mpc.render', function() {
        if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_carousel_posts' ) {
            return;
        }

        var $layout      = $popup.find( '[name="layout"]' ),
            $metas       = $popup.find( '[name="meta_layout"]' ),
            $title       = $popup.find( '[name="title_disable"]' ),
            $description = $popup.find( '[name="description_disable"]' ),
            $thumbnail   = $popup.find( '[name="disable_thumbnail"]' );

        $layout.on( 'change', function() {
            layout_dependency( $layout.val(), $thumbnail.is( ':checked' ) );

            $metas.trigger( 'change' );
            $title.trigger( 'change' );
            $description.trigger( 'change' );

            overlay_tab_toggle();
            readmore_tab_toggle();
        } );

        $title.on( 'change', function() {
            title_dependency( $title.is( ':checked' ) );

            overlay_tab_toggle();
        } );

        $description.on( 'change', function() {
            description_dependency( $description.is( ':checked' ) );

            overlay_tab_toggle();
        } );

        $metas.on( 'change', function() {
            var _value = $metas.val() == ''; // true if empty

            meta_dependency( _value );
            check_date_dependency();

            overlay_tab_toggle();
        } );

        $thumbnail.on( 'change', function() {
            if( $.inArray( $layout.val(), [ 'style_2', 'style_3', 'style_5' ] ) > -1 ) {
                var _thumbnail = $thumbnail.is( ':checked');

                _overlay = _thumbnail;

                overlay_tab_toggle();
                thumbnail_dependency( _thumbnail );

                $metas.trigger( 'change' );
            }
        } );

        // Triggers
        setTimeout( function() {
            $layout.trigger( 'change' );
        }, 350 );
    } );
} )( jQuery );












/*----------------------------------------------------------------------------*\
	GRID POSTS SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
    "use strict";

    var $popup      = $( '#vc_ui-panel-edit-element' ),
        _hide_class = 'vc_dependent-hidden',
        _overlay    = false,
        _readmore   = false;

    function section_dependency( _dependencies, _value ) {
        $.each( _dependencies, function() {
            var $section  = $popup.find( '[data-vc-shortcode-param-name="' + this + '"]' ),
                $siblings = $section.siblings( '.mpc-vc-indent' );

            if( _value === true ) {
                $siblings.addClass( _hide_class );
                $section.addClass( _hide_class );
            } else {
                $siblings.removeClass( _hide_class );
                $section.removeClass( _hide_class );
            }
        } );
    }

    function readmore_tab_toggle() {
        var _params     = $popup.find( '[data-vc-shortcode-param-name="mpc_button__disable"]' ).data( 'param_settings' ),
            _group_name = _params.group;

        $.each( $popup.find( '[data-vc-ui-element="panel-tabs-controls"] li' ), function() {
            var $this = $( this );

            if( $this.find( 'button' ).text() == _group_name ) {
                if( _readmore === true ) {
                    $this.addClass( _hide_class );
                } else {
                    $this.removeClass( _hide_class );
                }
            }
        } );
    }

    function overlay_tab_toggle() {
        var _params = $popup.find( '[data-vc-shortcode-param-name="overlay_section_divider"]' ).data( 'param_settings' ),
            _group_name = _params.group;

        $.each( $popup.find( '[data-vc-ui-element="panel-tabs-controls"] li' ), function() {
            var $this = $( this );

            if( $this.find( 'button' ).text() == _group_name ) {
                if( _overlay === true ) {
                    $this.addClass( _hide_class );
                } else {
                    $this.removeClass( _hide_class );
                }
            }
        } );
    }

    function thumbnail_dependency( _value ) {
        var _dependencies = [ 'items_section_divider' ];
        section_dependency( _dependencies, _value );
    }

    function title_dependency( _overlay_value ) {
        var _layout = $popup.find( '[name="layout"]' ).val(),
            _dependencies = [ 'title_margin_divider' ],
            _overlay_dependencies = [ 'overlay_title_section_divider', 'overlay_title_margin_divider' ];

        if( _layout == 'style_8' && _overlay_value ) {
            section_dependency( _dependencies, false );
        } else {
            section_dependency( _dependencies, true );
        }

        if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_6', 'style_7', 'style_8' ] ) > -1 && !_overlay_value ) {
            section_dependency( _overlay_dependencies, false );
        } else {
            section_dependency( _overlay_dependencies, true );
        }
    }

    function description_dependency( _overlay_value ) {
        var _layout = $popup.find( '[name="layout"]' ).val(),
            _dependencies_base = [ 'description_section_divider' ],
            _dependencies = [ 'description_font_divider', 'description_padding_divider', 'description_margin_divider' ],
            _overlay_dependencies = [ 'overlay_description_section_divider', 'overlay_description_padding_divider', 'overlay_description_margin_divider' ];

        if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_7', 'style_8' ] ) > -1 || _overlay_value ) {
            section_dependency( _dependencies, true );
        } else {
            section_dependency( _dependencies, false );
        }

        if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_7', 'style_8' ] ) > -1 ) {
            section_dependency( _dependencies_base, true );
        } else {
            section_dependency( _dependencies_base, false );
        }

        if( _layout == 'style_6' || _overlay_value ) {
            section_dependency( _overlay_dependencies, true );
        } else {
            section_dependency( _overlay_dependencies, true );
        }
    }

    function check_date_dependency() {
        // Based on layout, thumbnail for style 5, meta data enable
        var _layout    = $popup.find( '[name="layout"]' ).val(),
            _enabled   = $popup.find( '[name="meta_layout-option_date"]' ).is( ':checked' ),
            _thumbnail = $popup.find( '[name="disable_thumbnail"]' ).is( ':checked' ),
            _disable   = true,
            _disable_at_overlay = _layout == 'style_6' && _enabled ? false : true;

        // Disable if date not selected
        if( !_enabled ) {
            date_dependency( _disable, _disable_at_overlay );
            return false;
        }

        // Date is enabled, check if layout needs date settings
        if( $.inArray( _layout, [ 'style_3', 'style_5', 'style_6' ] ) > -1 ) {
            // Check if layout has overlay enabled
            _disable = _thumbnail && _layout == 'style_5';
        }

        date_dependency( _disable, _disable_at_overlay );
    }

    function date_dependency( _value, _overlay_value ) {
        var _layout = $popup.find( '[name="layout"]' ).val(),
            _dependencies = [ 'date_font_divider', 'date_border_divider', 'date_padding_divider', 'date_margin_divider'],
            _overlay_dependencies = [ 'overlay_date_section_divider', 'overlay_date_padding_divider', 'overlay_date_margin_divider' ];

        _overlay_value = _layout == 'style_6' ? _overlay_value : true;

        section_dependency( _dependencies, _value );

        section_dependency( _overlay_dependencies, _overlay_value );
    }

    function meta_dependency( _value ) {
        var _layout = $popup.find( '[name="layout"]' ).val(),
            _dependencies = [ 'meta_font_divider', 'meta_margin_divider'],
            _overlay_dependencies = [ 'overlay_meta_section_divider', 'overlay_meta_margin_divider' ];

        section_dependency( _dependencies, _value );

        if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_6', 'style_7', 'style_8' ] ) > -1 && !_value ) {
            section_dependency( _overlay_dependencies, false );
        } else {
            section_dependency( _overlay_dependencies, true );
        }
    }

    function layout_dependency( _layout, _thumbnail ) {
        /* Trigger Thumbnail dependency */
        if( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5' ] ) > -1 ) {
            thumbnail_dependency( _thumbnail );
        } else {
            thumbnail_dependency( false );
        }

        if( _layout == 'style_9' || ( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5' ] ) > -1 && _thumbnail ) ) {
            _overlay = true;
            overlay_tab_toggle();
        } else {
            _overlay = false;
            overlay_tab_toggle( false );
        }

        /* Read More */
        if( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5', 'style_9' ] ) == -1 ) {
            _readmore = true;
            readmore_tab_toggle();
        } else {
            _readmore = false;
            readmore_tab_toggle();
        }
    }

    $popup.on( 'mpc.render', function() {
        if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_grid_posts' ) {
            return;
        }

        var $layout      = $popup.find( '[name="layout"]' ),
            $metas       = $popup.find( '[name="meta_layout"]' ),
            $title       = $popup.find( '[name="title_disable"]' ),
            $description = $popup.find( '[name="description_disable"]' ),
            $thumbnail   = $popup.find( '[name="disable_thumbnail"]' );

        $layout.on( 'change', function() {
            layout_dependency( $layout.val(), $thumbnail.is( ':checked' ) );

            $metas.trigger( 'change' );
            $title.trigger( 'change' );
            $description.trigger( 'change' );

            overlay_tab_toggle();
            readmore_tab_toggle();
        } );

        $title.on( 'change', function() {
            title_dependency( $title.is( ':checked' ) );

            overlay_tab_toggle();
        } );

        $description.on( 'change', function() {
            description_dependency( $description.is( ':checked' ) );

            overlay_tab_toggle();
        } );

        $metas.on( 'change', function() {
            var _value = $metas.val() == ''; // true if empty

            meta_dependency( _value );
            check_date_dependency();

            overlay_tab_toggle();
        } );

        $thumbnail.on( 'change', function() {
            if( $.inArray( $layout.val(), [ 'style_2', 'style_3', 'style_5' ] ) > -1 ) {
                var _thumbnail = $thumbnail.is( ':checked');

                _overlay = _thumbnail;

                overlay_tab_toggle();
                thumbnail_dependency( _thumbnail );

                $metas.trigger( 'change' );
            }
        } );

        // Triggers
        setTimeout( function() {
            $layout.trigger( 'change' );
        }, 350 );
    } );
} )( jQuery );

/*----------------------------------------------------------------------------*\
	HOTSPOT SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	function init_frame( $position_field, _frame, _background_id, _cache ) {
		var _popup_width = $popup.width();

		$position_field.parent().append( _frame );

		var $frame = $position_field.siblings( '.mpc-coords' ),
			$overlay = $frame.find( '.mpc-coords__overlay' ),
			$point = $frame.find( '.mpc-coords__point' ),
			_position = $position_field.val().split( '||' );

		$frame.css( 'max-width', _popup_width );

		if ( _cache ) {
			$frame.attr( 'data-id', _background_id );
			$images_cache.append( $frame.clone() );
		}

		if ( _position.length == 2 ) {
			_position[ 0 ] = isNaN( parseFloat( _position[ 0 ] ) ) ? 50 : parseFloat( _position[ 0 ] );
			_position[ 1 ] = isNaN( parseFloat( _position[ 1 ] ) ) ? 50 : parseFloat( _position[ 1 ] );

			$point.css( {
				left: _position[ 0 ] + '%',
				top: _position[ 1 ] + '%'
			} );
		}

		frame_behavior( $frame, $overlay, $point, $position_field );
	}

	function frame_behavior( $frame, $overlay, $point, $position_field ) {
		var _is_dragging = false,
			_release_timer;

		$overlay.on( 'mousedown', function( event ) {
			_is_dragging = true;

			event.preventDefault();
		} ).on( 'mouseup', function() {
			_is_dragging = false;
		} ).on( 'mouseleave', function() {
			_release_timer = setTimeout( function() {
				$overlay.trigger( 'mouseup' );
			}, 500 );
		} ).on( 'mouseenter', function() {
			clearTimeout( _release_timer );
		} ).on( 'mousemove', function( event ) {
			if ( ! _is_dragging ) {
				return;
			}

			set_position( $frame, $point, $position_field, event );
		} ).on( 'click', function( event ) {
			set_position( $frame, $point, $position_field, event );
		} ).on( 'dragstart', function( event ) {
			event.preventDefault();
		} );
	}

	function set_position( $frame, $point, $position_field, event ) {
		var _offsetX = typeof event.offsetX != 'undefined' ? event.offsetX : event.originalEvent.layerX,
			_offsetY = typeof event.offsetY != 'undefined' ? event.offsetY : event.originalEvent.layerY,
			_position = {
				x: ( _offsetX / $frame.width() * 100 ).toFixed( 3 ),
				y: ( _offsetY / $frame.height() * 100 ).toFixed( 3 )
			};

		$point.css( {
			left: _position.x + '%',
			top: _position.y + '%'
		} );

		$position_field.val( _position.x + '||' + _position.y );
	}

	var $popup = $( '#vc_ui-panel-edit-element' ),
		$images_cache = $( '<div id="mpc_hotspot_images_cache" class="mpc-hotspot-images-cache" />' );

	$images_cache.appendTo( 'body' );

	$popup.on( 'mpc.render', function() {
		if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_hotspot' ) {
			return;
		}

		var $position_field = $( '.wpb_vc_param_value.position' ),
			$load_image = $( '<button class="mpc-vc-button button mpc-default">' + _mpc_lang.mpc_hotspot.set_position + '</button>' ),
			_background_id = '';

		_background_id = vc.shortcodes.findWhere( { id: vc.active_panel.model.attributes.parent_id } ).attributes.params.background_image;
		if ( typeof _background_id == 'undefined' ) {
			_background_id = '';
		}

		if ( _background_id == '' ) {
			$position_field.parent().append( '<p class="mpc-error">' + _mpc_lang.mpc_hotspot.no_background + '</p>' );
			return;
		}

		$position_field.parent().append( $load_image );

		$load_image.one( 'click', function() {
			$load_image.remove();

			if ( $images_cache.find( '.mpc-coords[data-id="' + _background_id + '"]' ).length ) {
				init_frame( $position_field, $images_cache.find( '.mpc-coords[data-id="' + _background_id + '"]' ).clone(), _background_id, false );
			} else {
				$.post( ajaxurl, {
					action: 'mpc_hotspot_get_image',
					image_id: _background_id
				}, function( response ) {
					init_frame( $position_field, response, _background_id, true );
				} );
			}
		} );
	} );
} )( jQuery );


/*----------------------------------------------------------------------------*\
 ICON LIST SHORTCODE - Panel
 \*----------------------------------------------------------------------------*/
(function( $ ) {
	"use strict";

	var $popup = $( '#vc_ui-panel-edit-element' );

	$popup.on( 'mpc.render', function() {
		if( $popup.attr( 'data-vc-shortcode' ) != 'mpc_icon_list' ) {
			return '';
		}

		var $icon_type = $popup.find( '[name="mpc_icon__icon_type"]' ),
			$list_group = $popup.find( '[data-vc-shortcode-param-name="list"]' ),
			$group_toggle = $list_group.find( '.column_toggle' ),
			$group_add = $list_group.find( '.vc_param_group-add_content' ),
			$group_duplicate = $list_group.find( '.column_clone' );

		function icon_dependency( $this ) {
			var _type = $this.val();

			$list_group.find( '[name="list_icon_type"]' ).val( _type ).trigger( 'change' );
		}

		$icon_type.on( 'change', function() {
			icon_dependency( $( this ) );
		} );

		$group_add.on( 'click', function() {
			setTimeout( function(){
				icon_dependency( $icon_type );
			}, 250 );
		} );
		$group_duplicate.on( 'click', function() {
			icon_dependency( $icon_type );
		} );

		// Triggers
		setTimeout( function() {
			icon_dependency( $icon_type );
			$group_toggle.first().trigger( 'click' );
		}, 250 );
	} );
})( jQuery );

/*----------------------------------------------------------------------------*\
	ICON COLUMN SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $popup = $( '#vc_ui-panel-edit-element' );

	$popup.on( 'mpc.render', function() {
		if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_icon_column' ) {
			return;
		}

		if ( vc.shortcodes.findWhere( { id: vc.active_panel.model.attributes.parent_id } ).attributes.shortcode == 'mpc_circle_icons' ) {
			$popup.find( '.vc_shortcode-param[data-vc-shortcode-param-name="layout"], .vc_shortcode-param[data-vc-shortcode-param-name="border_radius"]' ).hide( 0 );

			$popup.find( '.vc_shortcode-param[data-vc-shortcode-param-name="margin_divider"]' ).closest( '.mpc-vc-wrapper' ).hide( 0 );
		}
	} );
} )( jQuery );


/*----------------------------------------------------------------------------*\
	IHOVER ITEM SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	function get_styles( _mpc_shape, _mpc_effect ) {
		if ( _mpc_shape == 'circle' ) {
			switch( _mpc_effect ) {
				case 'effect1':
				case 'effect5':
				case 'effect15':
				case 'effect17':
				case 'effect19':
					return _styles[ 'style1' ];
				case 'effect2':
				case 'effect3':
				case 'effect4':
				case 'effect7':
				case 'effect8':
				case 'effect9':
				case 'effect11':
				case 'effect12':
				case 'effect14':
				case 'effect18':
					return _styles[ 'style2' ];
				case 'effect6':
					return _styles[ 'style3' ];
				case 'effect10':
				case 'effect20':
					return _styles[ 'style4' ];
				case 'effect13':
					return _styles[ 'style5' ];
				case 'effect16':
					return _styles[ 'style6' ];
				default:
					return '';
			}
		} else {
			switch( _mpc_effect ) {
				case 'effect2':
				case 'effect4':
				case 'effect7':
					return _styles[ 'style1' ];
				case 'effect9':
				case 'effect10':
				case 'effect11':
				case 'effect12':
				case 'effect13':
				case 'effect14':
				case 'effect15':
					return _styles[ 'style2' ];
				case 'effect3':
					return _styles[ 'style4' ];
				case 'effect5':
					return _styles[ 'style6' ];
				case 'effect1':
					return _styles[ 'style7' ];
				case 'effect6':
					return _styles[ 'style8' ];
				case 'effect8':
					return _styles[ 'style9' ];
				default:
					return '';
			}
		}
	}

	var _styles = {
		'style1': '.none',
		'style2': '.left_to_right, .right_to_left, .top_to_bottom, .bottom_to_top',
		'style3': '.scale_up, .scale_down, .scale_down_up',
		'style4': '.top_to_bottom, .bottom_to_top',
		'style5': '.from_left_and_right, .top_to_bottom, .bottom_to_top',
		'style6': '.left_to_right, .right_to_left',
		'style7': '.left_and_right, .top_to_bottom, .bottom_to_top',
		'style8': '.from_top_and_bottom, .from_left_and_right, .top_to_bottom, .bottom_to_top',
		'style9': '.scale_up, .scale_down'
	};

	var $popup = $( '#vc_ui-panel-edit-element' );

	$popup.on( 'mpc.render', function() {
		if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_ihover' ) {
			return;
		}

		var $mpc_shape  = $popup.find( '.mpc-ihover-shape select.shape' ),
			$mpc_effect = $popup.find( '.mpc-ihover-effect select.effect' ),
			$mpc_style  = $popup.find( '.mpc-ihover-style select.style' );

		$mpc_shape.on( 'change', function() {
			if ( $mpc_shape.val() == 'circle' ) {
				$mpc_effect.children().prop( 'disabled', false );
			} else {
				$mpc_effect.children( '.effect16, .effect17, .effect18, .effect19, .effect20' ).prop( 'disabled', true );
			}

			if ( $mpc_effect.val() == null ) {
				$mpc_effect.val( $mpc_effect.children( ':not(:disabled)' ).first().attr( 'value' ) );
			}

			$mpc_effect.trigger( 'change' );
		} );
		$mpc_shape.trigger( 'change' );

		$mpc_effect.on( 'change', function() {
			$mpc_style.children().prop( 'disabled', true );

			$mpc_style.find( get_styles( $mpc_shape.val(), $mpc_effect.val() ) ).prop( 'disabled', false );

			if ( $mpc_style.val() == null ) {
				$mpc_style.val( $mpc_style.children( ':not(:disabled)' ).first().attr( 'value' ) );
			}
		} );
		$mpc_effect.trigger( 'change' );
	});

	$popup.on( 'mpc.render', function() {
		if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_ihover_item' ) {
			return;
		}

		var $mpc_style  = $popup.find( '.mpc-ihover-style select.style' ),
			_params = vc.shortcodes.findWhere( { id: vc.active_panel.model.attributes.parent_id } ).attributes.params,
			_shape = _params.shape,
			_effect = _params.effect;

		$mpc_style.children().prop( 'disabled', true );

		$mpc_style.find( get_styles( _shape, _effect ) + ', .default' ).prop( 'disabled', false );

		if ( $mpc_style.val() == null ) {
			$mpc_style.val( $mpc_style.children( ':not(:disabled)' ).first().attr( 'value' ) );
		}
	});
} )( jQuery );

/*----------------------------------------------------------------------------*\
	INTERACTIVE IMAGE SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $popup = $( '#vc_ui-panel-edit-element' );

	$popup.on( 'mpc.render', function() {
		if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_interactive_image' ) {
			return;
		}

		var $divider = $( '.vc_shortcode-param[data-vc-shortcode-param-name="preview_divider"] .edit_form_line' ),
			$load_preview = $( '<button class="mpc-vc-button button mpc-default mpc-preview">' + _mpc_lang.mpc_interactive_image.preview + '</button>' ),
			$preview = $( '<div class="mpc-coords__preview" />' ),
			_hotspots = [];

		$divider.append( $load_preview );
		$load_preview.after( $preview ).after( '<br>' );

		_hotspots = vc.shortcodes.where( { parent_id: vc.active_panel.model.attributes.id } );

		$load_preview.on( 'click', function() {
			var _background_id = $popup.find( '.wpb_vc_param_value.background_image' ).val();

			$preview
				.css( 'max-width', $popup.width() )
				.html( '' );

			if ( _background_id == '' ) {
				$preview.append( '<p class="mpc-error">' + _mpc_lang.mpc_interactive_image.no_background + '</p>' );
			} else if ( _hotspots.length == 0 ) {
				$preview.append( '<p class="mpc-error">' + _mpc_lang.mpc_interactive_image.no_hotspots + '</p>' );
			} else {
				$.post( ajaxurl, {
					action: 'mpc_interactive_image_get_image',
					image_id: _background_id
				}, function( response ) {
					if ( response == 'error' ) {
						$preview.append( '<p class="mpc-error">' + _mpc_lang.mpc_interactive_image.no_background + '</p>' );
						return;
					}

					$preview
						.append( response )
						.addClass( 'mpc-loaded' );

					for ( var _index = 0; _index < _hotspots.length; _index++ ) {
						var $point = $( '<div class="mpc-coords__point" />' ),
							_position = _hotspots[ _index ].attributes.params.position.split( '||' );

						if ( _position.length == 2 ) {
							_position[ 0 ] = isNaN( parseFloat( _position[ 0 ] ) ) ? 50 : parseFloat( _position[ 0 ] );
							_position[ 1 ] = isNaN( parseFloat( _position[ 1 ] ) ) ? 50 : parseFloat( _position[ 1 ] );

							$point.css( {
								left: _position[ 0 ] + '%',
								top: _position[ 1 ] + '%'
							} );

							$preview.append( $point );
						}
					}
				} );
			}
		} );
	} );
} )( jQuery );

/*----------------------------------------------------------------------------*\
 LIGHTBOX SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";
} )( jQuery );








/*----------------------------------------------------------------------------*\
	PRICING COLUMN - Panel
\*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $popup = $( '#vc_ui-panel-edit-element' );

	$popup.on( 'mpc.render', function() {
		if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_pricing_column' ) {
			return;
		}

		var _params         = vc.shortcodes.findWhere( { id: vc.active_panel.model.attributes.parent_id } ).attributes.params,
			_title_disable  = _params.title_disable,
			_price_disable  = _params.price_disable,
            _button_disable = _params.button_disable;

		$popup.find( '[data-vc-shortcode-param-name="title_disable"] input' ).val( _title_disable ).trigger( 'change' );
		$popup.find( '[data-vc-shortcode-param-name="price_disable"] input' ).val( _price_disable ).trigger( 'change' );
		$popup.find( '[data-vc-shortcode-param-name="button_disable"] input' ).val( _button_disable ).trigger( 'change' );
    });
} )( jQuery );







/*----------------------------------------------------------------------------*\
	SINGLE POST SHORTCODE - Panel
\*----------------------------------------------------------------------------*/
(function( $ ) {
	"use strict";

	var $popup      = $( '#vc_ui-panel-edit-element' ),
	    _hide_class = 'vc_dependent-hidden',
	    _overlay    = false,
	    _readmore   = false;

	function section_dependency( _dependencies, _value ) {
		$.each( _dependencies, function() {
			var $section  = $popup.find( '[data-vc-shortcode-param-name="' + this + '"]' ),
			    $siblings = $section.siblings( '.mpc-vc-indent' );

			if( _value === true ) {
				$siblings.addClass( _hide_class );
				$section.addClass( _hide_class );
			} else {
				$siblings.removeClass( _hide_class );
				$section.removeClass( _hide_class );
			}
		} );
	}

	function overlay_tab_toggle() {
		var _params     = $popup.find( '[data-vc-shortcode-param-name="overlay_section_divider"]' ).data( 'param_settings' ),
		    _group_name = _params.group;

		$.each( $popup.find( '[data-vc-ui-element="panel-tabs-controls"] li' ), function() {
			var $this = $( this );

			if( $this.find( 'button' ).text() == _group_name ) {
				if( _overlay === true ) {
					$this.addClass( _hide_class );
				} else {
					$this.removeClass( _hide_class );
				}
			}
		} );
	}

	function readmore_tab_toggle() {
		var _params     = $popup.find( '[data-vc-shortcode-param-name="mpc_button__disable"]' ).data( 'param_settings' ),
		    _group_name = _params.group;

		$.each( $popup.find( '[data-vc-ui-element="panel-tabs-controls"] li' ), function() {
			var $this = $( this );

			if( $this.find( 'button' ).text() == _group_name ) {
				if( _readmore === true ) {
					$this.addClass( _hide_class );
				} else {
					$this.removeClass( _hide_class );
				}
			}
		} );
	}

	function thumbnail_dependency( _value ) {
		var _dependencies = [ 'items_section_divider' ];
		section_dependency( _dependencies, _value );
	}

	function title_dependency( _overlay_value ) {
		var _layout               = $popup.find( '[name="layout"]' ).val(),
		    _dependencies         = [ 'title_margin_divider' ],
		    _overlay_dependencies = [ 'overlay_title_section_divider', 'overlay_title_margin_divider' ];

		if( _layout == 'style_8' && _overlay_value ) {
			section_dependency( _dependencies, false );
		} else {
			section_dependency( _dependencies, true );
		}

		if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_6', 'style_7', 'style_8' ] ) > -1 && !_overlay_value ) {
			section_dependency( _overlay_dependencies, false );
		} else {
			section_dependency( _overlay_dependencies, true );
		}
	}

	function description_dependency( _overlay_value ) {
		var _layout               = $popup.find( '[name="layout"]' ).val(),
		    _dependencies_base    = [ 'description_section_divider' ],
		    _dependencies         = [ 'description_font_divider', 'description_padding_divider', 'description_margin_divider' ],
		    _overlay_dependencies = [ 'overlay_description_section_divider', 'overlay_description_padding_divider', 'overlay_description_margin_divider' ];

		if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_7', 'style_8' ] ) > -1 || _overlay_value ) {
			section_dependency( _dependencies, true );
		} else {
			section_dependency( _dependencies, false );
		}

		if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_7', 'style_8' ] ) > -1 ) {
			section_dependency( _dependencies_base, true );
		} else {
			section_dependency( _dependencies_base, false );
		}

		if( _layout == 'style_6' || _overlay_value ) {
			section_dependency( _overlay_dependencies, true );
		} else {
			section_dependency( _overlay_dependencies, true );
		}
	}

	function check_date_dependency() {
		// Based on layout, thumbnail for style 5, meta data enable
		var _layout             = $popup.find( '[name="layout"]' ).val(),
		    _enabled            = $popup.find( '[name="meta_layout-option_date"]' ).is( ':checked' ),
		    _thumbnail          = $popup.find( '[name="disable_thumbnail"]' ).is( ':checked' ),
		    _disable            = true,
		    _disable_at_overlay = _layout == 'style_6' && _enabled ? false : true;

		// Disable if date not selected
		if( !_enabled ) {
			date_dependency( _disable, _disable_at_overlay );
			return false;
		}

		// Date is enabled, check if layout needs date settings
		if( $.inArray( _layout, [ 'style_3', 'style_5', 'style_6' ] ) > -1 ) {
			// Check if layout has overlay enabled
			_disable = _thumbnail && _layout == 'style_5';
		}

		date_dependency( _disable, _disable_at_overlay );
	}

	function date_dependency( _value, _overlay_value ) {
		var _layout               = $popup.find( '[name="layout"]' ).val(),
		    _dependencies         = [ 'date_font_divider', 'date_border_divider', 'date_padding_divider', 'date_margin_divider' ],
		    _overlay_dependencies = [ 'overlay_date_section_divider', 'overlay_date_padding_divider', 'overlay_date_margin_divider' ];

		_overlay_value = _layout == 'style_6' ? _overlay_value : true;

		section_dependency( _dependencies, _value );

		section_dependency( _overlay_dependencies, _overlay_value );
	}

	function meta_dependency( _value ) {
		var _layout               = $popup.find( '[name="layout"]' ).val(),
		    _dependencies         = [ 'meta_font_divider', 'meta_margin_divider' ],
		    _overlay_dependencies = [ 'overlay_meta_section_divider', 'overlay_meta_margin_divider' ];

		section_dependency( _dependencies, _value );

		if( $.inArray( _layout, [ 'style_1', 'style_4', 'style_6', 'style_7', 'style_8' ] ) > -1 && !_value ) {
			section_dependency( _overlay_dependencies, false );
		} else {
			section_dependency( _overlay_dependencies, true );
		}
	}

	function layout_dependency( _layout, _thumbnail ) {
		/* Trigger Thumbnail dependency */
		if( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5' ] ) > -1 ) {
			thumbnail_dependency( _thumbnail );
		} else {
			thumbnail_dependency( false );
		}

		/* Overlay ( Style 9 + Thumbnail & Overlay disable ) */
		if( _layout == 'style_9' || ( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5' ] ) > -1 && _thumbnail ) ) {
			_overlay = true;
			overlay_tab_toggle();
		} else {
			_overlay = false;
			overlay_tab_toggle();
		}

		/* Read More */
		if( $.inArray( _layout, [ 'style_2', 'style_3', 'style_5', 'style_9' ] ) == -1 ) {
			_readmore = true;
			readmore_tab_toggle();
		} else {
			_readmore = false;
			readmore_tab_toggle();
		}
	}

	$popup.on( 'mpc.render', function() {
		if( $popup.attr( 'data-vc-shortcode' ) != 'mpc_single_post' ) {
			return '';
		}

		var $layout      = $popup.find( '[name="layout"]' ),
		    $metas       = $popup.find( '[name="meta_layout"]' ),
		    $title       = $popup.find( '[name="title_disable"]' ),
		    $description = $popup.find( '[name="description_disable"]' ),
		    $thumbnail   = $popup.find( '[name="disable_thumbnail"]' );

		$layout.on( 'change', function() {
			layout_dependency( $layout.val(), $thumbnail.is( ':checked' ) );

			$metas.trigger( 'change' );
			$title.trigger( 'change' );
			$description.trigger( 'change' );

			overlay_tab_toggle();
			readmore_tab_toggle();
		} );

		$title.on( 'change', function() {
			title_dependency( $title.is( ':checked' ) );

			overlay_tab_toggle();
		} );

		$description.on( 'change', function() {
			description_dependency( $description.is( ':checked' ) );

			overlay_tab_toggle();
		} );

		$metas.on( 'change', function() {
			var _value = $metas.val() == ''; // true if empty

			meta_dependency( _value );
			check_date_dependency();

			overlay_tab_toggle();
		} );

		$thumbnail.on( 'change', function() {
			if( $.inArray( $layout.val(), [ 'style_2', 'style_3', 'style_5' ] ) > -1 ) {
				var _thumbnail = $thumbnail.is( ':checked' );

				_overlay = _thumbnail;

				overlay_tab_toggle();
				thumbnail_dependency( _thumbnail );

				$metas.trigger( 'change' );
			}
		} );

		// Triggers
		setTimeout( function() {
			$layout.trigger( 'change' );
		}, 350 );
	} );
})( jQuery );








/*----------------------------------------------------------------------------*\
 ADD TO CART SHORTCODE - Panel
 \*----------------------------------------------------------------------------*/
( function( $ ) {
	"use strict";

	var $popup = $( '#vc_ui-panel-edit-element' );

	$popup.on( 'mpc.render', function() {
		if ( $popup.attr( 'data-vc-shortcode' ) != 'mpc_wc_add_to_cart' ) {
			return;
		}

		if ( vc.shortcodes.findWhere( { id: vc.active_panel.model.attributes.parent_id } ).get( 'shortcode' ) == 'mpc_button_set' ) {
			$popup.find( '.vc_shortcode-param[data-vc-shortcode-param-name="block"]' ).hide();
		}
	} );
} )( jQuery );
