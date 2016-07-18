<?php
/*----------------------------------------------------------------------------*\
	SNIPPETS
\*----------------------------------------------------------------------------*/

if ( ! class_exists( 'MPC_Snippets' ) ) {
	class MPC_Snippets {
		static $helper_scripts = '';
		static $weight = 0;
		// -1234 --- 1 - group, 2 - section, 3/4 - field |
		// - = from end
		// higher = lower

		/*----------------------------------------------------------------------------*\
			OTHER
		\*----------------------------------------------------------------------------*/
		static $animations_loop_list = array(
			'none'           => 'none',
			'callout.bounce' => 'callout.bounce',
			'callout.shake'  => 'callout.shake',
			'callout.flash'  => 'callout.flash',
			'callout.pulse'  => 'callout.pulse',
			'callout.swing'  => 'callout.swing',
			'callout.tada'   => 'callout.tada',
		);

		static $animations_in_list = array(
			'none'                          => 'none',
			'transition.fadeIn'             => 'transition.fadeIn',

			'transition.flipXIn'            => 'transition.flipXIn',
			'transition.flipYIn'            => 'transition.flipYIn',
			'transition.flipBounceXIn'      => 'transition.flipBounceXIn',
			'transition.flipBounceYIn'      => 'transition.flipBounceYIn',

			'transition.swoopIn'            => 'transition.swoopIn',
			'transition.whirlIn'            => 'transition.whirlIn',
			'transition.shrinkIn'           => 'transition.shrinkIn',
			'transition.expandIn'           => 'transition.expandIn',

			'transition.bounceIn'           => 'transition.bounceIn',
			'transition.bounceUpIn'         => 'transition.bounceUpIn',
			'transition.bounceDownIn'       => 'transition.bounceDownIn',
			'transition.bounceLeftIn'       => 'transition.bounceLeftIn',
			'transition.bounceRightIn'      => 'transition.bounceRightIn',

			'transition.slideUpIn'          => 'transition.slideUpIn',
			'transition.slideDownIn'        => 'transition.slideDownIn',
			'transition.slideLeftIn'        => 'transition.slideLeftIn',
			'transition.slideRightIn'       => 'transition.slideRightIn',
			'transition.slideUpBigIn'       => 'transition.slideUpBigIn',
			'transition.slideDownBigIn'     => 'transition.slideDownBigIn',
			'transition.slideLeftBigIn'     => 'transition.slideLeftBigIn',
			'transition.slideRightBigIn'    => 'transition.slideRightBigIn',

			'transition.perspectiveUpIn'    => 'transition.perspectiveUpIn',
			'transition.perspectiveDownIn'  => 'transition.perspectiveDownIn',
			'transition.perspectiveLeftIn'  => 'transition.perspectiveLeftIn',
			'transition.perspectiveRightIn' => 'transition.perspectiveRightIn',
		);

		/*----------------------------------------------------------------------------*\
			VISUAL COMPOSER
		\*----------------------------------------------------------------------------*/

		static function vc_animation( $prefix = '', $subtitle = '' ) {
			$enter = MPC_Snippets::vc_animation_basic( $prefix, $subtitle );

			$prefix   = $prefix != '' ? $prefix . '_' : $prefix;
			$subtitle = $subtitle != '' && substr( $subtitle, - 2 ) != '}}' ? $subtitle . ' - ' : $subtitle;

			$loop = array(
				/* LOOP ANIMATION */
				array(
					'type'       => 'mpc_divider',
					'title'      => $subtitle . __( 'Loop Animation', 'mpc' ),
					'param_name' => $prefix . 'animation_loop_divider',
					'std'        => '',
					'group'      => __( 'Animations', 'mpc' ),
					'weight'      => -10200,
				),
				array(
					'type'        => 'mpc_animation',
					'heading'     => __( 'Loop Type', 'mpc' ),
					'param_name'  => $prefix . 'animation_loop_type',
					'tooltip'     => __( 'Choose one of the animation types. You can apply the animation to the preview box on the right with the <b>Refresh</b> button.', 'mpc' ),
					'value'       => MPC_Snippets::$animations_loop_list,
					'std'         => 'none',
					'admin_label' => true,
					'group'       => __( 'Animations', 'mpc' ),
					'weight'      => -10205,
				),
				array(
					'type'        => 'mpc_slider',
					'heading'     => __( 'Duration', 'mpc' ),
					'param_name'  => $prefix . 'animation_loop_duration',
					'tooltip'     => __( 'Choose duration of the animation', 'mpc' ),
					'min'         => 100,
					'max'         => 5000,
					'step'        => 50,
					'value'       => 500,
					'unit'        => 'ms',
					'group'       => __( 'Animations', 'mpc' ),
					'dependency'  => array(
						'element'            => $prefix . 'animation_loop_type',
						'value_not_equal_to' => 'none',
					),
					'weight'      => -10210,
				),
				array(
					'type'        => 'mpc_slider',
					'heading'     => __( 'Delay', 'mpc' ),
					'param_name'  => $prefix . 'animation_loop_delay',
					'tooltip'     => __( 'Choose delay for the animation. After the delay the animation will start again.', 'mpc' ),
					'min'         => 100,
					'max'         => 5000,
					'step'        => 50,
					'value'       => 1500,
					'unit'        => 'ms',
					'group'       => __( 'Animations', 'mpc' ),
					'dependency'  => array(
						'element'            => $prefix . 'animation_loop_type',
						'value_not_equal_to' => 'none',
					),
					'weight'      => -10215,
				),
				array(
					'type'        => 'checkbox',
					'heading'     => __( 'Pause on Hover', 'mpc' ),
					'param_name'  => $prefix . 'animation_loop_hover',
					'tooltip'     => __( 'Check to pause loop animation when user hovers over the shortcode.', 'mpc' ),
					'value'       => array( __( 'Enable', 'mpc' ) => 'true' ),
					'std'         => '',
					'group'       => __( 'Animations', 'mpc' ),
					'dependency'  => array(
						'element'            => $prefix . 'animation_loop_type',
						'value_not_equal_to' => 'none',
					),
					'weight'      => -10220,
				),
			);

			return array_merge( $enter, $loop );
		}

		static function vc_animation_basic( $prefix = '', $subtitle = '' ) {
			$prefix   = $prefix != '' ? $prefix . '_' : $prefix;
			$subtitle = $subtitle != '' && substr( $subtitle, - 2 ) != '}}' ? $subtitle . ' - ' : $subtitle;

			return array(
				/* ENTER ANIMATION */
				array(
					'type'       => 'mpc_divider',
					'title'      => $subtitle . __( 'Enter Animation', 'mpc' ),
					'param_name' => $prefix . 'animation_in_divider',
					'std'        => '',
					'group'      => __( 'Animations', 'mpc' ),
					'weight'     => -10100,
				),
				array(
					'type'        => 'mpc_animation',
					'heading'     => __( 'Enter Type', 'mpc' ),
					'param_name'  => $prefix . 'animation_in_type',
					'tooltip'     => __( 'Choose one of the animation types. You can apply the animation to the preview box on the right with the <b>Refresh</b> button.', 'mpc' ),
					'value'       => MPC_Snippets::$animations_in_list,
					'std'         => 'none',
					'admin_label' => true,
					'group'       => __( 'Animations', 'mpc' ),
					'weight'      => -10105,
				),
				array(
					'type'        => 'mpc_slider',
					'heading'     => __( 'Offset', 'mpc' ),
					'param_name'  => $prefix . 'animation_in_offset',
					'tooltip'     => __( 'Choose the point on the viewsport that should trigger the animation. For example:<br><b>100%</b>: animation will start as soon as the top of the shortcodes appear in the viewport;<br><b>50%</b>: animation will start when the top of the shortcode is in the middle of viewport.', 'mpc' ),
					'value'       => 100,
					'unit'        => '%',
					'group'       => __( 'Animations', 'mpc' ),
					'dependency'  => array(
						'element'            => $prefix . 'animation_in_type',
						'value_not_equal_to' => 'none',
					),
					'weight'      => -10110,
				),
				array(
					'type'        => 'mpc_slider',
					'heading'     => __( 'Duration', 'mpc' ),
					'param_name'  => $prefix . 'animation_in_duration',
					'tooltip'     => __( 'Choose duration of the animation', 'mpc' ),
					'min'         => 100,
					'max'         => 5000,
					'step'        => 50,
					'value'       => 300,
					'unit'        => 'ms',
					'group'       => __( 'Animations', 'mpc' ),
					'dependency'  => array(
						'element'            => $prefix . 'animation_in_type',
						'value_not_equal_to' => 'none',
					),
					'weight'      => -10115,
				),
				array(
					'type'        => 'mpc_slider',
					'heading'     => __( 'Delay', 'mpc' ),
					'param_name'  => $prefix . 'animation_in_delay',
					'tooltip'     => __( 'Choose delay for the animation. Useful for creating a cascade effect on set of similar shortcodes (<em>buttons</em>, <em>icons</em>, etc.).', 'mpc' ),
					'min'         => 0,
					'max'         => 5000,
					'step'        => 50,
					'value'       => 0,
					'unit'        => 'ms',
					'group'       => __( 'Animations', 'mpc' ),
					'dependency'  => array(
						'element'            => $prefix . 'animation_in_type',
						'value_not_equal_to' => 'none',
					),
					'weight'      => -10120,
				),
			);
		}

		static function vc_font( $atts = array() ) {
			$atts += array(
				'prefix'     => '',
				'subtitle'   => '',
				'title'      => __( 'Typography', 'mpc' ),
				'dependency' => '',
				'group'      => '',
				'with_align' => true,
				'highlight'  => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . $atts[ 'title' ],
				'param_name'       => $atts[ 'prefix' ] . 'font_divider',
				'std'              => '',
				'edit_field_class' => 'vc_col-sm-12 vc_column' . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$divider[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$divider,
				array(
					'type'        => 'mpc_typography',
					'heading'     => __( 'Typography Preset', 'mpc' ),
					'param_name'  => $atts[ 'prefix' ] . 'font_preset',
					'tooltip'     => __( 'Typography presets are used to easily configure your shortcode font settings. You can choose one of the premade presets or create your own. You can easily overwrite presets: <b>color</b>, <b>size</b>, <b>line height</b>, <b>transform</b> or <b>alignment</b>.', 'mpc' ),
					'value'       => '',
					'description' => __( 'Choose preset or create new one.', 'mpc' ),
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Color', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'font_color',
					'tooltip'          => __( 'Overwrite presets font color.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Size', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'font_size',
					'tooltip'          => __( 'Overwrite presets font size.', 'mpc' ),
					'value'            => '',
					'label'            => 'px',
					'addon'            => array(
						'icon'  => 'dashicons-editor-textcolor',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Line Height', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'font_line_height',
					'tooltip'          => __( 'Overwrite presets font line height.', 'mpc' ),
					'value'            => '',
					'label'            => 'em',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-editor-textcolor',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Transform', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'font_transform',
					'tooltip'          => __( 'Overwrite presets font transform style.', 'mpc' ),
					'value'            => array(
						''                        => '',
						__( 'Capitalize', 'mpc' ) => 'capitalize',
						__( 'Small Caps', 'mpc' ) => 'small-caps',
						__( 'Uppercase', 'mpc' )  => 'uppercase',
						__( 'Lowercase', 'mpc' )  => 'lowercase',
						__( 'None', 'mpc' )       => 'none',
					),
					'std'              => '',
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-clear--both mpc-advanced-field',
				),
			);

			if ( $atts[ 'with_align' ] ) {
				$return[] = array(
					'type'             => 'dropdown',
					'heading'          => __( 'Alignment', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'font_align',
					'tooltip'          => __( 'Overwrite presets font alignment.', 'mpc' ),
					'value'            => array(
						''                     => '',
						__( 'Left', 'mpc' )    => 'left',
						__( 'Right', 'mpc' )   => 'right',
						__( 'Center', 'mpc' )  => 'center',
						__( 'Justify', 'mpc' ) => 'justify',
						__( 'Default', 'mpc' ) => 'inherit',
					),
					'std'              => '',
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field',
				);
			}

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		static function vc_font_simple( $atts = array() ) {
			$atts += array(
				'prefix'     => '',
				'subtitle'   => '',
				'title'      => __( 'Typography', 'mpc' ),
				'dependency' => '',
				'group'      => '',
				'with_align' => true,
				'highlight'  => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . $atts[ 'title' ],
				'subtitle'         => __( 'Typography settings.', 'mpc' ),
				'param_name'       => $atts[ 'prefix' ] . 'font_divider',
				'std'              => '',
				'edit_field_class' => 'vc_col-sm-12 vc_column' . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$divider[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$divider,
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Color', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'font_color',
					'tooltip'          => __( 'Choose font color.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-3 vc_column',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Size', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'font_size',
					'tooltip'          => __( 'Define font size.', 'mpc' ),
					'value'            => '',
					'label'            => 'px',
					'addon'            => array(
						'icon'  => 'dashicons-editor-textcolor',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-3 vc_column mpc-advanced-field',
				),
			);

			if ( $atts[ 'with_align' ] ) {
				$return[] = array(
					'type'             => 'dropdown',
					'heading'          => __( 'Alignment', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'font_align',
					'tooltip'          => __( 'Select font alignment.', 'mpc' ),
					'value'            => array(
						''                     => '',
						__( 'Left', 'mpc' )    => 'left',
						__( 'Right', 'mpc' )   => 'right',
						__( 'Center', 'mpc' )  => 'center',
						__( 'Justify', 'mpc' ) => 'justify',
						__( 'Default', 'mpc' ) => 'inherit',
					),
					'std'              => '',
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field',
				);
			}

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		static function vc_border( $atts = array() ) {
			$atts += array(
				'prefix'      => '',
				'subtitle'    => '',
				'dependency'  => '',
				'group'       => '',
				'with_radius' => true,
				'highlight'   => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . __( 'Border', 'mpc' ),
				'param_name'       => $atts[ 'prefix' ] . 'border_divider',
				'value'            => '',
				'std'              => '',
				'advanced'         => true,
				'edit_field_class' => 'vc_col-sm-12 vc_column' . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$divider[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$divider,
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Width', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'border_all',
					'tooltip'          => __( 'Define border width. If you want to specify different values for each side please enable <b>Advanced Settings</b> on the right.', 'mpc' ),
					'value'            => '',
					'addon'            => array(
						'icon'  => 'dashicons-editor-expand',
						'align' => 'prepend'
					),
					'label'            => 'px',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Top', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'border_top',
					'value'            => '',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-up-alt2',
						'align' => 'prepend'
					),
					'label'            => 'px',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Right', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'border_right',
					'value'            => '',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-right-alt2',
						'align' => 'prepend'
					),
					'label'            => 'px',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Bottom', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'border_bottom',
					'value'            => '',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-down-alt2',
						'align' => 'prepend'
					),
					'label'            => 'px',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Left', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'border_left',
					'value'            => '',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-left-alt2',
						'align' => 'prepend'
					),
					'label'            => 'px',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Style', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'border_style',
					'tooltip'          => __( 'Select border style. Learn more about border styles <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/border-style" target="_blank">here</a>.', 'mpc' ),
					'value'            => array(
						''                    => '',
						__( 'Solid', 'mpc' )  => 'solid',
						__( 'Dotted', 'mpc' ) => 'dotted',
						__( 'Dashed', 'mpc' ) => 'dashed',
						__( 'Double', 'mpc' ) => 'double',
						__( 'Groove', 'mpc' ) => 'groove',
						__( 'Ridge', 'mpc' )  => 'ridge',
					),
					'std'              => '',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Color', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'border_color',
					'tooltip'          => __( 'Choose border color.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-color-picker',
				),
				array(
					'type'       => 'mpc_css',
					'section'    => 'border',
					'param_name' => $atts[ 'prefix' ] . 'border_css',
					'value'      => '',
					'prefix'     => $atts[ 'prefix' ],
				),
			);

			if ( $atts[ 'with_radius' ] ) {
				$return[] = array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Radius', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'border_radius',
					'tooltip'          => __( 'Define border radius. You can use radius to make round corners. There is no need to specify other border settings to just make round corners.', 'mpc' ),
					'value'            => '',
					'addon'            => array(
						'icon'  => 'dashicons-marker',
						'align' => 'prepend'
					),
					'label'            => 'px',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-validate-int mpc-advanced-field',
				);
			}

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		static function vc_inner_border( $atts = array() ) {
			$atts += array(
				'prefix'     => '',
				'subtitle'   => '',
				'dependency' => '',
				'group'      => '',
				'highlight'  => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . __( 'Inner Border', 'mpc' ),
				'param_name'       => $atts[ 'prefix' ] . 'inner_border_divider',
				'std'              => '',
				'edit_field_class' => 'vc_col-sm-12 vc_column' . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$divider[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$divider,
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Width', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'inner_border_width',
					'tooltip'          => __( 'Define inner border width', 'mpc' ),
					'value'            => '',
					'addon'            => array(
						'icon'  => 'dashicons-editor-expand',
						'align' => 'prepend'
					),
					'label'            => 'px',
					'edit_field_class' => 'vc_col-sm-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Color', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'inner_border_color',
					'tooltip'          => __( 'Choose border color.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-3 vc_column mpc-color-picker',
				),
				array(
					'type'       => 'mpc_css',
					'section'    => 'inner_border',
					'param_name' => $atts[ 'prefix' ] . 'inner_border_css',
					'prefix'     => $atts[ 'prefix' ],
				),
			);

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		static function vc_padding( $atts = array() ) {
			$atts += array(
				'prefix'     => '',
				'subtitle'   => '',
				'dependency' => '',
				'group'      => '',
				'highlight'  => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . __( 'Padding', 'mpc' ),
				'param_name'       => $atts[ 'prefix' ] . 'padding_divider',
				'value'            => '',
				'std'              => '',
				'advanced'         => true,
				'edit_field_class' => 'vc_col-sm-12 vc_column mpc-advanced-field' . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$divider[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$divider,
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'All', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'padding_all',
					'tooltip'          => __( 'Define padding size. If you want to specify different values for each side please enable <b>Advanced Settings</b> on the right.', 'mpc' ),
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-editor-expand',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Top', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'padding_top',
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-up-alt2',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Right', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'padding_right',
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-right-alt2',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Bottom', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'padding_bottom',
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-down-alt2',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Left', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'padding_left',
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-left-alt2',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Unit', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'padding_unit',
					'tooltip'          => __( 'Select padding unit.', 'mpc' ),
					'value'            => array(
						'px' => 'px',
						'em' => 'em',
						'%'  => '%',
					),
					'std'              => 'px',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'       => 'mpc_css',
					'section'    => 'padding',
					'param_name' => $atts[ 'prefix' ] . 'padding_css',
					'value'      => '',
					'prefix'     => $atts[ 'prefix' ],
				),
			);

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		static function vc_margin( $atts = array() ) {
			$atts += array(
				'prefix'     => '',
				'subtitle'   => '',
				'dependency' => '',
				'group'      => '',
				'highlight'  => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . __( 'Margin', 'mpc' ),
				'param_name'       => $atts[ 'prefix' ] . 'margin_divider',
				'value'            => '',
				'std'              => '',
				'advanced'         => true,
				'edit_field_class' => 'vc_col-sm-12 vc_column mpc-advanced-field' . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$divider[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$divider,
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'All', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'margin_all',
					'tooltip'          => __( 'Define margin size. If you want to specify different values for each side please enable <b>Advanced Settings</b> on the right.', 'mpc' ),
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-editor-expand',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Top', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'margin_top',
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-up-alt2',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Right', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'margin_right',
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-right-alt2',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Bottom', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'margin_bottom',
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-down-alt2',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Left', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'margin_left',
					'value'            => '',
					'validate'         => 'float',
					'addon'            => array(
						'icon'  => 'dashicons-arrow-left-alt2',
						'align' => 'prepend'
					),
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Unit', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'margin_unit',
					'tooltip'          => __( 'Select margin unit.', 'mpc' ),
					'value'            => array(
						'px' => 'px',
						'em' => 'em',
						'%'  => '%',
					),
					'std'              => 'px',
					'edit_field_class' => 'vc_col-sm-4 vc_col-md-3 vc_column mpc-advanced-field',
				),
				array(
					'type'       => 'mpc_css',
					'section'    => 'margin',
					'param_name' => $atts[ 'prefix' ] . 'margin_css',
					'value'      => '',
					'prefix'     => $atts[ 'prefix' ],
				),
			);

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		static function vc_class( $atts = array() ) {
			$atts += array(
				'prefix'     => '',
				'subtitle'   => '',
				'dependency' => '',
				'group'      => '',
				'highlight'  => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$class = array(
				'type'             => 'textfield',
				'heading'          => $atts[ 'subtitle' ] . __( 'Custom Class', 'mpc' ),
				'param_name'       => $atts[ 'prefix' ] . 'class',
				'admin_label'      => true,
				'tooltip'          => __( 'Add your custom class to the element. Mostly used for custom CSS/JS.', 'mpc' ),
				'value'            => '',
				'edit_field_class' => 'vc_col-sm-12 vc_column mpc-advanced-field mpc-no-wrap',
			);

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$class[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$class,
			);

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		static function vc_background( $atts = array() ) {
			$atts += array(
				'prefix'        => '',
				'title'         => __( 'Background', 'mpc' ),
				'subtitle'      => '',
				'tooltip'       => '',
				'tooltip_title' => '',
				'dependency'    => '',
				'group'         => '',
				'highlight'     => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . $atts[ 'title' ],
				'param_name'       => $atts[ 'prefix' ] . 'background_divider',
				'std'              => '',
				'edit_field_class' => 'vc_col-sm-12 vc_column' . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			if ( $atts[ 'tooltip' ] != '' ) {
				$divider[ 'tooltip' ] = $atts[ 'tooltip' ];
			}

			if ( $atts[ 'tooltip_title' ] != '' ) {
				$divider[ 'tooltip_title' ] = $atts[ 'tooltip_title' ];
			}

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$divider[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$divider,
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Type', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'background_type',
					'tooltip'          => __( 'Select one of three background types:<br><b>Color</b>: simple one color background;<br><b>Gradient</b>: two colors linear or radial gradient;<br><b>Image</b>: single image or pattern.', 'mpc' ),
					'value'            => array(
						__( 'Color', 'mpc' )    => 'color',
						__( 'Gradient', 'mpc' ) => 'gradient',
						__( 'Image', 'mpc' )    => 'image',
					),
					'std'              => 'color',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'attach_image',
					'heading'          => __( 'Image', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'background_image',
					'tooltip'          => __( 'Choose background image.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'background_type',
						'value'   => 'image'
					),
				),
				array(
					'type'             => 'mpc_align',
					'heading'          => __( 'Position', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'background_position',
					'tooltip'          => __( 'Choose image alignment position.', 'mpc' ),
					'value'            => '',
					'std'              => 'middle-center',
					'grid_size'        => 'large',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'background_type',
						'value'   => 'image'
					),
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Size', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'background_image_size',
					'tooltip'          => __( 'Define image size. You can use default WordPress sizes (<em>thumbnail</em>, <em>medium</em>, <em>large</em>, <em>full</em>) or pass exact size by width and height in this format: 100x200.', 'mpc' ),
					'value'            => 'large',
					'addon'            => array(
						'icon'  => 'dashicons dashicons-editor-expand',
						'align' => 'prepend',
					),
					'label'            => '',
					'validate'         => false,
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field mpc-input--large',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'background_type',
						'value'   => array( 'image' )
					),
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Color', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'background_color',
					'tooltip'          => __( 'Choose background color.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-color-picker',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'background_type',
						'value'   => array( 'color', 'image' )
					),
				),
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Repeat', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'background_repeat',
					'tooltip'          => __( 'Select image repeat style. Mainly used by patterns.', 'mpc' ),
					'value'            => array(
						__( 'No Repeat', 'mpc' )         => 'no-repeat',
						__( 'Repeat Horizontal', 'mpc' ) => 'repeat-x',
						__( 'Repeat Vertical', 'mpc' )   => 'repeat-y',
						__( 'Repeat', 'mpc' )            => 'repeat',
					),
					'std'              => 'no-repeat',
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'background_type',
						'value'   => 'image'
					),
				),
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Display Size', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'background_size',
					'tooltip'          => __( 'Select background size. Learn more about background sizes <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/background-size" target="_blank">here</a>.', 'mpc' ),
					'value'            => array(
						__( 'Initial', 'mpc' ) => 'initial',
						__( 'Cover', 'mpc' )   => 'cover',
						__( 'Contain', 'mpc' ) => 'contain',
					),
					'std'              => 'initial',
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'background_repeat',
						'value'   => 'no-repeat'
					),
				),
				array(
					'type'             => 'mpc_gradient',
					'heading'          => __( 'Gradient', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'background_gradient',
					'tooltip'          => __( 'Define gradient style:<br>- choose starting and ending colors;<br>- position of both colors (smooth or sharp color transition);<br>- angle of color transition;<br>- linear or radial gradient type.<br><br>All changes are displayed in the preview box on the right.', 'mpc' ),
					'value'            => '#83bae3||#80e0d4||0;100||180||linear',
					'edit_field_class' => 'vc_col-sm-12 vc_column',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'background_type',
						'value'   => 'gradient'
					),
				),
			);

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		static function vc_icon( $atts = array() ) {
			$atts += array(
				'prefix'       => '',
				'title'        => __( 'Icon', 'mpc' ),
				'subtitle'     => '',
				'dependency'   => '',
				'group'        => '',
				'with_size'    => true,
				'with_color'   => true,
				'highlight'    => false,
				'custom_class' => '',
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . $atts[ 'title' ],
				'param_name'       => $atts[ 'prefix' ] . 'icon_divider',
				'std'              => '',
				'edit_field_class' => 'vc_col-sm-12 vc_column ' . $atts[ 'custom_class' ] . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			if ( $atts[ 'dependency' ] != '' && is_array( $atts[ 'dependency' ] ) ) {
				$divider[ 'dependency' ] = $atts[ 'dependency' ];
			}

			$return = array(
				$divider,
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Type', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'icon_type',
					'tooltip'          => __( 'Select one of three icon types:<br><b>Icon Font</b>: choose icon that you like from provided libraries;<br><b>Character</b>: define character and specify typography settings for it;<br><b>Image</b>: choose image.', 'mpc' ),
					'value'            => array(
						__( 'Icon Font', 'mpc' ) => 'icon',
						__( 'Character', 'mpc' ) => 'character',
						__( 'Image', 'mpc' )     => 'image',
					),
					'std'              => 'icon',
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field',
				),
				array(
					'type'             => 'mpc_icon',
					'heading'          => __( 'Select icon', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'icon',
					'tooltip'          => __( 'Choose icon that you like. You can change the icons library at the top. You can also search the icons by keywords. Remember to use as few different icons libraries across your page as possible.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-3 vc_column',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'icon_type',
						'value'   => array( 'icon' )
					),
				),
				array(
					'type'             => 'mpc_typography',
					'heading'          => __( 'Font Preset', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'icon_preset',
					'tooltip'          => __( 'Typography presets are used to easily configure your shortcode font settings. You can choose one of the premade presets or create your own.', 'mpc' ),
					'value'            => '',
					'description'      => __( 'Choose preset or create new one.', 'mpc' ),
					'edit_field_class' => 'vc_col-sm-12 vc_column mpc-advanced-field',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'icon_type',
						'value'   => array( 'character' )
					),
				),
				array(
					'type'             => 'textfield',
					'heading'          => __( 'Character', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'icon_character',
					'tooltip'          => __( 'Define character as icon.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'icon_type',
						'value'   => array( 'character' )
					),
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Size', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'icon_image_size',
					'tooltip'          => __( 'Define image size. You can use default WordPress sizes (<em>thumbnail</em>, <em>medium</em>, <em>large</em>, <em>full</em>) or pass exact size by width and height in this format: 100x200.', 'mpc' ),
					'value'            => 'thumbnail',
					'addon'            => array(
						'icon'  => 'dashicons dashicons-editor-expand',
						'align' => 'prepend',
					),
					'label'            => '',
					'validate'         => false,
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field mpc-input--large',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'icon_type',
						'value'   => array( 'image' )
					),
				),
				array(
					'type'             => 'attach_image',
					'heading'          => __( 'Icon Image', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'icon_image',
					'tooltip'          => __( 'Choose image as icon.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-6 vc_col-sm-1-5 vc_column',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'icon_type',
						'value'   => array( 'image' )
					),
				),
			);

			if ( $atts[ 'with_color' ] ) {
				$return[] = array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Color', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'icon_color',
					'tooltip'          => __( 'Choose icon color.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-color-picker',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'icon_type',
						'value'   => array( 'icon', 'character' )
					),
				);
			}

			if ( $atts[ 'with_size' ] ) {
				$return[] = array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Size', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'icon_size',
					'tooltip'          => __( 'Define icon size.', 'mpc' ),
					'value'            => '',
					'addon'            => array(
						'icon'  => 'dashicons dashicons-editor-textcolor',
						'align' => 'prepend',
					),
					'label'            => 'px',
					'validate'         => true,
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field',
					'dependency'       => array(
						'element' => $atts[ 'prefix' ] . 'icon_type',
						'value'   => array( 'icon', 'character' )
					),
				);
			}

			if ( $atts[ 'group' ] != '' ) {
				self::prefix_group( $return, $atts[ 'group' ] );
			}

			return $return;
		}

		/**
		 * @var array $atts ['prefix']    => ''
		 * @var array $atts ['subtitle']  => ''
		 * @var array $atts ['rows']      => ''
		 * @var array $atts ['cols']      => ''
		 * @var array $atts ['highlight'] => false
		 *
		 * @return array
		 */
		static function vc_rows_cols( $atts = array() ) {
			$atts += array(
				'prefix'    => '',
				'subtitle'  => '',
				'rows'      => '',
				'cols'      => '',
				'highlight' => false,
			);

			$atts[ 'prefix' ]   = $atts[ 'prefix' ] != '' ? $atts[ 'prefix' ] . '_' : $atts[ 'prefix' ];
			$atts[ 'subtitle' ] = $atts[ 'subtitle' ] != '' && substr( $atts[ 'subtitle' ], - 2 ) != '}}' ? $atts[ 'subtitle' ] . ' - ' : $atts[ 'subtitle' ];

			$divider = array(
				'type'             => 'mpc_divider',
				'title'            => $atts[ 'subtitle' ] . __( 'Display', 'mpc' ),
				'param_name'       => $atts[ 'prefix' ] . 'rows_cols_divider',
				'std'              => '',
				'edit_field_class' => 'vc_col-sm-12 vc_column mpc-advanced-field' . ( $atts[ 'highlight' ] ? ' mpc-vc-highlight' : '' ),
			);

			$return = array( $divider );

			if ( $atts[ 'rows' ] !== false ) {
				$atts[ 'rows' ] = wp_parse_args( $atts[ 'rows' ], array(
					'min'        => 1,
					'max'        => 2,
					'default'    => 1,
					'dependency' => ''
				) );

				$rows_options = array();
				for ( $i = $atts[ 'rows' ][ 'min' ]; $i <= $atts[ 'rows' ][ 'max' ]; $i ++ ) {
					$rows_options[ $i ] = $i;
				}

				$rows_field = array(
					'type'             => 'dropdown',
					'heading'          => __( 'Rows Number', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'rows',
					'admin_label'      => true,
					'tooltip'          => __( 'Select number of displayed rows.', 'mpc' ),
					'value'            => $rows_options,
					'std'              => $atts[ 'rows' ][ 'default' ],
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field',
				);

				if ( $atts[ 'rows' ][ 'dependency' ] != '' ) {
					$rows_field[ 'dependency' ] = $atts[ 'rows' ][ 'dependency' ];
				}

				$return[] = $rows_field;
			}

			if ( $atts[ 'cols' ] !== false ) {
				$atts[ 'cols' ] = wp_parse_args( $atts[ 'cols' ], array(
					'min'        => 1,
					'max'        => 4,
					'default'    => 2,
					'dependency' => ''
				) );

				$cols_options = array();
				for ( $i = $atts[ 'cols' ][ 'min' ]; $i <= $atts[ 'cols' ][ 'max' ]; $i ++ ) {
					$cols_options[ $i ] = $i;
				}

				$cols_field = array(
					'type'             => 'dropdown',
					'heading'          => __( 'Columns Number', 'mpc' ),
					'param_name'       => $atts[ 'prefix' ] . 'cols',
					'admin_label'      => true,
					'tooltip'          => __( 'Select number of displayed columns.', 'mpc' ),
					'value'            => $cols_options,
					'std'              => $atts[ 'cols' ][ 'default' ],
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-advanced-field',
				);

				if ( $atts[ 'cols' ][ 'dependency' ] != '' ) {
					$cols_field[ 'dependency' ] = $atts[ 'cols' ][ 'dependency' ];
				}

				$return[] = $cols_field;
			}

			return $return;
		}

		static function vc_overlay() {
			$base = array(
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Appear Effect', 'mpc' ),
					'param_name'       => 'overlay_overlay_effect',
					'tooltip'          => __( 'Select overlay appear effect style:<br><b>Fade</b>: simple fade in effect;<br><b>Slide In</b>: slide overlay in from selected side.<br><br><b>Please notice you need to enable at least one of below icons to display overlay.</b>', 'mpc' ),
					'value'            => array(
						__( 'Fade', 'mpc' )              => 'fade',
						__( 'Slide In - from Bottom', 'mpc' ) => 'slide-up',
						__( 'Slide In - from Top', 'mpc' )    => 'slide-down',
						__( 'Slide In - from Right', 'mpc' )  => 'slide-left',
						__( 'Slide In - from Left', 'mpc' )   => 'slide-right',
					),
					'group'            => __( 'Overlay', 'mpc' ),
					'std'              => 'fade',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-post-effect mpc-advanced-field',
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Overlay Color', 'mpc' ),
					'tooltip'          => __( 'Choose overlay color.', 'mpc' ),
					'param_name'       => 'overlay_background',
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-color-picker mpc-first-row',
					'group'            => __( 'Overlay', 'mpc' ),
				),
				array(
					'type'             => 'mpc_align',
					'heading'          => __( 'Icon Position', 'mpc' ),
					'param_name'       => 'overlay_icon_align',
					'tooltip'          => __( 'Choose overlay icons alignment.', 'mpc' ),
					'value'            => '',
					'std'              => 'middle-center',
					'grid_size'        => 'large',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field mpc-first-row',
					'group'            => __( 'Overlay', 'mpc' ),
				),
			);

			$lightbox = array(
				array(
					'type'             => 'checkbox',
					'heading'          => __( 'Lightbox', 'mpc' ),
					'param_name'       => 'overlay_enable_lightbox',
					'tooltip'          => __( 'Check to enable overlay lightbox icon. This will let user view the image in a full screen popup.', 'mpc' ),
					'value'            => array( __( 'Enable', 'mpc' ) => 'true' ),
					'std'              => '',
					'group'            => __( 'Overlay', 'mpc' ),
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field mpc-no-wrap',
				),
			);
			$lightbox_mirror = array(
				array(
					'type'             => 'checkbox',
					'heading'          => __( 'Horizontal Mirror', 'mpc' ),
					'param_name'       => 'overlay_icon_mirror',
					'tooltip'          => __( 'Check to horizontally reverse the icon.', 'mpc' ),
					'value'            => array( __( 'Enable', 'mpc' ) => 'true' ),
					'std'              => '',
					'group'            => __( 'Overlay', 'mpc' ),
					'dependency'       => array( 'element' => 'overlay_enable_lightbox', 'value' => 'true' ),
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field',
				),
			);

			$external_url = array(
				array(
					'type'             => 'checkbox',
					'heading'          => __( 'External URL', 'mpc' ),
					'param_name'       => 'overlay_enable_url',
					'tooltip'          => __( 'Check to enable overlay external URL icon. This will let user open the link specified for this element in the <b>External URL</b> field.', 'mpc' ),
					'value'            => array( __( 'Enable', 'mpc' ) => 'true' ),
					'std'              => '',
					'group'            => __( 'Overlay', 'mpc' ),
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field mpc-no-wrap mpc-clear--both',
				),
			);
			$external_url_mirror = array(
				array(
					'type'             => 'checkbox',
					'heading'          => __( 'Horizontal Mirror', 'mpc' ),
					'param_name'       => 'overlay_url_icon_mirror',
					'tooltip'          => __( 'Check to horizontally reverse the icon.', 'mpc' ),
					'value'            => array( __( 'Enable', 'mpc' ) => 'true' ),
					'std'              => '',
					'group'            => __( 'Overlay', 'mpc' ),
					'dependency'       => array( 'element' => 'overlay_enable_url', 'value' => 'true' ),
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field',
				),
			);

			$overlay_margin       = MPC_Snippets::vc_padding( array(
				'prefix' => 'overlay',
				'group'  => __( 'Overlay', 'mpc' )
			) );
			$lightbox_icon         = MPC_Snippets::vc_icon( array(
				'prefix'     => 'overlay',
				'subtitle'   => __( 'Lightbox', 'mpc' ),
				'group'      => __( 'Overlay', 'mpc' ),
				'dependency' => array(
					'element' => 'overlay_enable_lightbox',
					'value'   => 'true'
				)
			) );
			$external_url_icon     = MPC_Snippets::vc_icon( array(
				'prefix'     => 'overlay_url',
				'subtitle'   => __( 'External URL', 'mpc' ),
				'group'      => __( 'Overlay', 'mpc' ),
				'dependency' => array(
					'element' => 'overlay_enable_url',
					'value'   => 'true'
				)
			) );
			$overlay_icon_border  = MPC_Snippets::vc_border( array(
				'prefix'   => 'overlay_icon',
				'subtitle' => __( 'Icon', 'mpc' ),
				'group'    => __( 'Overlay', 'mpc' )
			) );
			$overlay_icon_padding = MPC_Snippets::vc_padding( array(
				'prefix'   => 'overlay_icon',
				'subtitle' => __( 'Icon', 'mpc' ),
				'group'    => __( 'Overlay', 'mpc' )
			) );
			$overlay_icon_margin  = MPC_Snippets::vc_margin( array(
				'prefix'   => 'overlay_icon',
				'subtitle' => __( 'Icon', 'mpc' ),
				'group'    => __( 'Overlay', 'mpc' )
			) );

			$overlay_icon_bg = array(
				array(
					'type'       => 'mpc_divider',
					'title'      => __( 'Icon Background', 'mpc' ),
					'param_name' => 'overlay_icon_bg_section_divider',
					'group'      => __( 'Overlay', 'mpc' ),
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Background Color', 'mpc' ),
					'param_name'       => 'overlay_icon_background',
					'tooltip'          => __( 'Choose icon background color.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-color-picker',
					'group'            => __( 'Overlay', 'mpc' ),
				),
			);

			$hover_lightbox = array(
				array(
					'type'       => 'mpc_divider',
					'title'      => __( 'Hover', 'mpc' ),
					'param_name' => 'overlay_hover_section_divider',
					'group'      => __( 'Overlay', 'mpc' ),
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Icon Color', 'mpc' ),
					'param_name'       => 'overlay_hover_color',
					'tooltip'          => __( 'If you want to change the icon color after hover choose a different one from the color picker below.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-color-picker',
					'group'            => __( 'Overlay', 'mpc' ),
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Background Color', 'mpc' ),
					'param_name'       => 'overlay_hover_icon_background',
					'tooltip'          => __( 'If you want to change the background color after hover choose a different one from the color picker below.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-color-picker',
					'group'            => __( 'Overlay', 'mpc' ),
				),
				array(
					'type'             => 'colorpicker',
					'heading'          => __( 'Border Color', 'mpc' ),
					'param_name'       => 'overlay_hover_border',
					'tooltip'          => __( 'If you want to change the border color after hover choose a different one from the color picker below.', 'mpc' ),
					'value'            => '',
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-color-picker',
					'group'            => __( 'Overlay', 'mpc' ),
				),
			);

			return array_merge(
				$base,
				$lightbox,
				$lightbox_icon,
				$lightbox_mirror,
				$external_url,
				$external_url_icon,
				$external_url_mirror,
				$overlay_icon_bg,
				$overlay_icon_border,
				$overlay_icon_margin,
				$overlay_icon_padding,
				$overlay_margin,
				$hover_lightbox
			);
		}

		/*----------------------------------------------------------------------------*\
			STYLES
		\*----------------------------------------------------------------------------*/

		static function css_font( $styles = '', $prefix = '', $force = false ) {
			$prefix = $prefix != '' ? $prefix . '_' : $prefix;
			$style  = '';

			if ( $styles == '' ) {
				return '';
			}

			if ( ! empty( $styles[ $prefix . 'font_preset' ] ) ) {
				MPC_Snippets::add_typography( $styles[ $prefix . 'font_preset' ] );
			}

			$defaults = array(
				$prefix . 'font_color'       => '',
				$prefix . 'font_size'        => '',
				$prefix . 'font_line_height' => '',
				$prefix . 'font_align'       => '',
				$prefix . 'font_transform'   => '',
			);

			$styles = wp_parse_args( $styles, $defaults );

			$important = $force ? ' !important' : '';

			$style .= $styles[ $prefix . 'font_color' ] != '' ? 'color:' . $styles[ $prefix . 'font_color' ] . $important . ';' : '';
			$style .= $styles[ $prefix . 'font_size' ] != '' ? 'font-size:' . $styles[ $prefix . 'font_size' ] . $important . ';' : '';
			$style .= $styles[ $prefix . 'font_line_height' ] != '' ? 'line-height:' . $styles[ $prefix . 'font_line_height' ] . $important . ';' : '';
			$style .= $styles[ $prefix . 'font_align' ] != '' ? 'text-align:' . $styles[ $prefix . 'font_align' ] . $important . ';' : '';

			if ( $styles[ $prefix . 'font_transform' ] == 'small-caps' ) {
				$style .= 'font-variant:' . $styles[ $prefix . 'font_transform' ] . $important . ';';
			} else {
				$style .= $styles[ $prefix . 'font_transform' ] != '' ? 'text-transform:' . $styles[ $prefix . 'font_transform' ] . $important . ';' : '';
			}

			return $style;
		}

		static function css_background_basic( $styles = '', $prefix = '' ) {
			$prefix = $prefix != '' ? $prefix . '_' : $prefix;
			$style  = '';

			if ( $styles == '' ) {
				return '';
			}

			$style .= $styles[ $prefix . 'background_color' ] != '' ? 'background-color:' . $styles[ $prefix . 'background_color' ] . ';' : '';

			return $style;
		}

		static function css_background( $styles = '', $prefix = '' ) {
			$prefix = $prefix != '' ? $prefix . '_' : $prefix;
			$style  = '';

			if ( $styles[ $prefix . 'background_type' ] == 'color' ) {
				$style .= $styles[ $prefix . 'background_color' ] != '' ? 'background: ' . $styles[ $prefix . 'background_color' ] . ';' : '';
			} else if ( $styles[ $prefix . 'background_type' ] == 'image' ) {
				if ( ! isset( $styles[ $prefix . 'background_image' ] ) ) {
					return '';
				}

				$background_image = self::get_image( $styles[ $prefix . 'background_image' ], $styles[ $prefix . 'background_image_size' ] );

				$style .= $styles[ $prefix . 'background_image' ] != '' ? 'background-image: url(' . esc_url( $background_image ) . ');' : '';
				$style .= $styles[ $prefix . 'background_repeat' ] != '' ? 'background-repeat: ' . $styles[ $prefix . 'background_repeat' ] . ';' : '';
				$style .= $styles[ $prefix . 'background_size' ] != '' ? 'background-size: ' . $styles[ $prefix . 'background_size' ] . ';' : '';
				$style .= $styles[ $prefix . 'background_color' ] != '' ? 'background-color: ' . $styles[ $prefix . 'background_color' ] . ';' : '';

				$style .= $styles[ $prefix . 'background_position' ] != '' ? 'background-position: ' . str_replace( array( 'middle', '-' ), array( 'center', ' ' ), $styles[ $prefix . 'background_position' ] ) . ';' : '';
			} else if ( $styles[ $prefix . 'background_type' ] == 'gradient' ) {
				$gradient_values = $styles[ $prefix . 'background_gradient' ] != '' ? explode( '||', $styles[ $prefix . 'background_gradient' ] ) : null;

				if ( count( $gradient_values ) != 5 ) {
					return '';
				}

				$type        = $gradient_values[ 4 ];
				$angle       = (int) $gradient_values[ 3 ];
				$range       = explode( ';', $gradient_values[ 2 ] );
				$end_color   = $gradient_values[ 1 ];
				$start_color = $gradient_values[ 0 ];

				if ( ! is_array( $range ) ) {
					$range = array( 0, 100 );
				}

				if ( $type == 'radial' ) {
					$angle = null;
				}

				$linear_gradient = $type . '-gradient(' . ( $angle === null ? 'circle' : $angle . 'deg' ) . ',' . esc_attr( $start_color ) . ' ' . esc_attr( $range[ 0 ] ) . '%,' . esc_attr( $end_color ) . ' ' . esc_attr( $range[ 1 ] ) . '%)';

				$style .= 'background-image: ' . $linear_gradient . ';';
			}

			return $style;
		}

		static function css_icon( $styles = '', $prefix = '' ) {
			$prefix = $prefix != '' ? $prefix . '_' : $prefix;
			$style  = '';

			if ( $styles == '' ) {
				return '';
			}

			if ( $styles[ $prefix . 'icon_type' ] !== 'image' ) {
				$icon_styles[ 'font_size' ]  = $styles [ $prefix . 'icon_size' ];
				$icon_styles[ 'font_color' ] = $styles [ $prefix . 'icon_color' ];

				$style .= self::css_font( $icon_styles );
			}

			return $style;
		}

		static function css_border( $styles = '', $prefix = '' ) {
			$prefix = $prefix != '' ? $prefix . '-' : $prefix;
			$style  = '';

			if ( $styles == '' ) {
				return '';
			}

			if ( $styles[ $prefix . 'border_top' ] == '' || $styles[ $prefix . 'border_right' ] == '' || $styles[ $prefix . 'border_bottom' ] == '' || $styles[ $prefix . 'border_left' ] == '' ) {
				$style .= $styles[ $prefix . 'border_top' ] != '' ? 'border-top-width:' . $styles[ $prefix . 'border_top' ] . ';' : '';
				$style .= $styles[ $prefix . 'border_right' ] != '' ? 'border-right-width:' . $styles[ $prefix . 'border_right' ] . ';' : '';
				$style .= $styles[ $prefix . 'border_bottom' ] != '' ? 'border-bottom-width:' . $styles[ $prefix . 'border_bottom' ] . ';' : '';
				$style .= $styles[ $prefix . 'border_left' ] != '' ? 'border-left-width:' . $styles[ $prefix . 'border_left' ] . ';' : '';
			} else {
				$style .= 'border-width:' . $styles[ $prefix . 'border_top' ] . ' ' . $styles[ $prefix . 'border_right' ] . ' ' . $styles[ $prefix . 'border_bottom' ] . ' ' . $styles[ $prefix . 'border_left' ] . ';';
			}

			$style .= $styles[ $prefix . 'border_style' ] != '' ? 'border-style:' . $styles[ $prefix . 'border_style' ] . ';' : '';
			$style .= $styles[ $prefix . 'border_color' ] != '' ? 'border-color:' . $styles[ $prefix . 'border_color' ] . ';' : '';
			$style .= $styles[ $prefix . 'border_radius' ] != '' ? 'border-radius:' . $styles[ $prefix . 'border_radius' ] . 'px;' : '';

			return $style;
		}

		static function css_padding( $styles = '', $prefix = '', $with_unit = true ) {
			$prefix = $prefix != '' ? $prefix . '-' : $prefix;
			$style  = '';

			if ( $styles == '' ) {
				return '';
			}

			if ( ! $with_unit ) {
				$styles[ $prefix . 'padding_unit' ] = '';
			}

			if ( $styles[ $prefix . 'padding_top' ] == '' || $styles[ $prefix . 'padding_right' ] == '' || $styles[ $prefix . 'padding_bottom' ] == '' || $styles[ $prefix . 'padding_left' ] == '' ) {
				$style .= $styles[ $prefix . 'padding_top' ] != '' ? 'padding-top:' . $styles[ $prefix . 'padding_top' ] . $styles[ $prefix . 'padding_unit' ] . ';' : '';
				$style .= $styles[ $prefix . 'padding_right' ] != '' ? 'padding-right:' . $styles[ $prefix . 'padding_right' ] . $styles[ $prefix . 'padding_unit' ] . ';' : '';
				$style .= $styles[ $prefix . 'padding_bottom' ] != '' ? 'padding-bottom:' . $styles[ $prefix . 'padding_bottom' ] . $styles[ $prefix . 'padding_unit' ] . ';' : '';
				$style .= $styles[ $prefix . 'padding_left' ] != '' ? 'padding-left:' . $styles[ $prefix . 'padding_left' ] . $styles[ $prefix . 'padding_unit' ] . ';' : '';
			} else {
				$style .= 'padding:' . $styles[ $prefix . 'padding_top' ] . $styles[ $prefix . 'padding_unit' ] . ' ' . $styles[ $prefix . 'padding_right' ] . $styles[ $prefix . 'padding_unit' ] . ' ' . $styles[ $prefix . 'padding_bottom' ] . $styles[ $prefix . 'padding_unit' ] . ' ' . $styles[ $prefix . 'padding_left' ] . $styles[ $prefix . 'padding_unit' ] . ';';
			}

			return $style;
		}

		static function css_margin( $styles = '', $prefix = '', $with_unit = false ) {
			$prefix = $prefix != '' ? $prefix . '-' : $prefix;
			$style  = '';

			if ( $styles == '' ) {
				return '';
			}

			if ( ! $with_unit ) {
				$styles[ $prefix . 'margin_unit' ] = '';
			}

			if ( $styles[ $prefix . 'margin_top' ] == '' || $styles[ $prefix . 'margin_right' ] == '' || $styles[ $prefix . 'margin_bottom' ] == '' || $styles[ $prefix . 'margin_left' ] == '' ) {
				$style .= $styles[ $prefix . 'margin_top' ] != '' ? 'margin-top:' . $styles[ $prefix . 'margin_top' ] . $styles[ $prefix . 'margin_unit' ] . ';' : '';
				$style .= $styles[ $prefix . 'margin_right' ] != '' ? 'margin-right:' . $styles[ $prefix . 'margin_right' ] . $styles[ $prefix . 'margin_unit' ] . ';' : '';
				$style .= $styles[ $prefix . 'margin_bottom' ] != '' ? 'margin-bottom:' . $styles[ $prefix . 'margin_bottom' ] . $styles[ $prefix . 'margin_unit' ] . ';' : '';
				$style .= $styles[ $prefix . 'margin_left' ] != '' ? 'margin-left:' . $styles[ $prefix . 'margin_left' ] . $styles[ $prefix . 'margin_unit' ] . ';' : '';
			} else {
				$style .= 'margin:' . $styles[ $prefix . 'margin_top' ] . $styles[ $prefix . 'margin_unit' ] . ' ' . $styles[ $prefix . 'margin_right' ] . $styles[ $prefix . 'margin_unit' ] . ' ' . $styles[ $prefix . 'margin_bottom' ] . $styles[ $prefix . 'margin_unit' ] . ' ' . $styles[ $prefix . 'margin_left' ] . $styles[ $prefix . 'margin_unit' ] . ';';
			}

			return $style;
		}

		/*----------------------------------------------------------------------------*\
			HELPERS
		\*----------------------------------------------------------------------------*/
		static function parse_atts_url( $url, &$url_title = '', $output = 'string' ) {
			global $mpc_can_link;

			if( filter_var( $url, FILTER_VALIDATE_URL ) !== FALSE && $mpc_can_link ) {
				$settings = ' href="' . esc_url( $url ) . '" title=""';
			} else if ( $url != '' && $mpc_can_link ) {
				$url  = explode( '|', $url );
				$link = array();
				foreach ( $url as $part ) {
					if ( $part != '' ) {
						$part               = explode( ':', $part, 2 );
						$part[ 1 ]          = urldecode( $part[ 1 ] );
						$link[ $part[ 0 ] ] = $part[ 1 ];
					}
				}

				if ( isset( $link[ 'url' ] ) ) {
					$link = array_merge( array(
						'url'    => '',
						'target' => '',
						'title'  => '',
					), $link );

					if( $output == 'string' ) {
						$settings = ' href="' . esc_url( $link[ 'url' ] ). '"';
						$settings .= ' target="' . esc_attr( $link[ 'target' ] ) . '"';
						$settings .= ' title="' . esc_attr( $link[ 'title' ] ) . '" ';
					} else {
						$settings = $link;
						$settings[ 'href' ] = $settings[ 'url' ];
						unset( $settings[ 'url' ] );
					}

					$url_title = $link[ 'title' ];
				} else {
					$settings = '';
				}
			} else {
				$settings = '';
			}

			return $settings;
		}

		static function parse_atts_icon( $settings, $prefix = '' ) {
			$prefix = $prefix != '' ? $prefix . '_' : $prefix;
			$icon   = array(
				'class'   => '',
				'content' => '',
			);

			if ( $settings[ $prefix . 'icon_type' ] == 'icon' && $settings[ $prefix . 'icon' ] != '' ) {
				MPC_Snippets::add_icon_font( $settings[ $prefix . 'icon' ] );
				$icon[ 'class' ] = ' ' . esc_attr( $settings[ $prefix . 'icon' ] );
			} else if ( $settings[ $prefix . 'icon_type' ] == 'character' && $settings[ $prefix . 'icon_character' ] != '' ) {
				MPC_Snippets::add_typography( $settings[ $prefix . 'icon_preset' ] );
				$icon[ 'content' ] = $settings[ $prefix . 'icon_character' ];
				$icon[ 'class' ]   = $settings[ $prefix . 'icon_preset' ] != '' ? ' mpc-typography--' . esc_attr( $settings[ $prefix . 'icon_preset' ] ) : '';
			} else if ( $settings[ $prefix . 'icon_type' ] == 'image' ) {
				if ( $settings[ $prefix . 'icon_image' ] != '' ) {
					$icon_image = wpb_getImageBySize( array(
						'attach_id'  => $settings[ $prefix . 'icon_image' ],
						'thumb_size' => $settings[ $prefix . 'icon_image_size' ] ?: 'full'
					) );

					if ( $icon_image ) {
						$icon[ 'content' ] = $icon_image[ 'thumbnail' ];
					}
				}
			}

			return $icon;
		}

		static function parse_atts_animation( $settings, $output = 'string' ) {
//			$animation = '';
			$animation = array();

			if ( isset( $settings[ 'animation_in_type' ] ) && !in_array( $settings[ 'animation_in_type' ], array( 'none','' ) ) ) {
				$animation_in = $settings[ 'animation_in_type' ];

				if ( ! empty( $settings[ 'animation_in_duration' ] ) ) {
					$animation_in .= '||' . $settings[ 'animation_in_duration' ];
				} else {
					$animation_in .= '||250';
				}

				if ( ! empty( $settings[ 'animation_in_delay' ] ) ) {
					$animation_in .= '||' . $settings[ 'animation_in_delay' ];
				} else {
					$animation_in .= '||0';
				}

				if ( ! empty( $settings[ 'animation_in_offset' ] ) ) {
					$animation_in .= '||' . $settings[ 'animation_in_offset' ];
				} else {
					$animation_in .= '||100';
				}

//				$animation .= ' data-animation-in="' . esc_attr( $animation_in ) . '"';
				$animation[ 'data-animation-in' ] = esc_attr( $animation_in );
			}

			if ( isset( $settings[ 'animation_loop_type' ] ) && !in_array( $settings[ 'animation_loop_type' ], array( 'none','' ) ) ) {
				$animation_loop = $settings[ 'animation_loop_type' ];

				if ( ! empty( $settings[ 'animation_loop_duration' ] ) ) {
					$animation_loop .= '||' . $settings[ 'animation_loop_duration' ];
				} else {
					$animation_loop .= '||250';
				}

				if ( ! empty( $settings[ 'animation_loop_delay' ] ) ) {
					$animation_loop .= '||' . $settings[ 'animation_loop_delay' ];
				} else {
					$animation_loop .= '||0';
				}

//				$animation .= ' data-animation-loop="' . esc_attr( $animation_loop ) . '"';
				$animation[ 'data-animation-loop' ] = esc_attr( $animation_loop );
			}

			if ( isset( $settings[ 'animation_loop_hover' ] ) && $settings[ 'animation_loop_hover' ] != '' ) {
//				$animation .= ' data-animation-hover="1"';
				$animation[ 'data-animation-hover' ] = '1';
			}

			if( $output == 'string' ) {
				$return = '';

				foreach( $animation as $attr => $value ) {
					$return .= ' ' . $attr . '="' . $value . '"';
				}

				return $return;
			} else {
				return $animation;
			}
		}

		static function parse_atts_carousel( $settings, $prefix = '', $custom = array() ) {
			$prefix = $prefix != '' ? $prefix . '_' : $prefix;
			$atts   = array(
				'infinite'       => false,
				'autoplay'       => false,
				'rows'           => 1,
				'slidesToShow'   => 4,
				'slidesToScroll' => 1,
				'centerMode'     => false,
				'initialSlide'   => 0,
			);

			if ( isset( $settings[ $prefix . 'rows' ] ) && $settings[ $prefix . 'rows' ] != '' ) {
				$atts[ 'rows' ] = (int) $settings[ $prefix . 'rows' ];
			}

			if ( isset( $settings[ $prefix . 'cols' ] ) && $settings[ $prefix . 'cols' ] != '' ) {
				$atts[ 'slidesToShow' ] = (int) $settings[ $prefix . 'cols' ];
			}

			if ( isset( $settings[ $prefix . 'single_scroll' ] ) ) {
				$atts[ 'slidesToScroll' ] = $settings[ $prefix . 'single_scroll' ] == 'true' ? 1 : $atts[ 'slidesToShow' ];
			}

			if ( isset( $settings[ $prefix . 'loop' ] ) && $settings[ $prefix . 'loop' ] == 'true' ) {
				$atts[ 'infinite' ]   = true;
				$atts[ 'centerMode' ] = isset( $settings[ $prefix . 'center_mode' ] ) ? (bool) $settings[ $prefix . 'center_mode' ] : false;
			}

			if ( isset( $settings[ $prefix . 'auto_slide' ] ) && $settings[ $prefix . 'auto_slide' ] == 'true' ) {
				$atts[ 'autoplay' ]      = true;
				$atts[ 'autoplaySpeed' ] = isset( $settings[ $prefix . 'delay' ] ) ? (int) $settings[ $prefix . 'delay' ] : 1000;
			}

			if ( isset( $settings[ $prefix . 'start_at' ] ) && $settings[ $prefix . 'start_at' ] >= 0 ) {
				$atts[ 'initialSlide' ] = (int) $settings[ $prefix . 'start_at' ] - 1;
			}

			if ( isset( $settings[ $prefix . 'layout' ] ) && $settings[ $prefix . 'layout' ] == 'fluid' ) {
				$atts[ 'variableWidth' ] = true;

				$atts[ 'rows' ]           = 1;
				$atts[ 'slidesToShow' ]   = $atts[ 'infinite' ] ? 3 : 1;
				$atts[ 'slidesToScroll' ] = 1;
			}

			if ( sizeof( $custom ) ) {
				$atts = array_merge( $atts, $custom );
			}

			$atts = ! empty( $atts ) ? " data-mpcslick='" . json_encode( $atts ) . "'" . ' data-slick-cols="' . esc_attr( $atts[ 'slidesToShow' ] ) . '"' : '';

			return $atts;
		}

		static function parse_atts_shortcode( $settings, $prefix = '' ) {
			$prefix = $prefix != '' ? $prefix . '_' : $prefix;
			$atts   = array();

			foreach ( $settings as $name => $value ) {
				$replaced_name = str_replace( $prefix, '', $name );

				if ( $replaced_name != $name ) {
					$atts[ $replaced_name ] = $value;
				}
			}

			return $atts;
		}

		/* Lightbox Vendor */
		static function parse_atts_overlay( $atts ) {
			$defaults = array(
				'atts' => '',
				'class' => '',
				'external' => '',
				'lightbox' => '',
			);

			if ( $atts[ 'overlay_enable_lightbox' ] != '' || $atts[ 'overlay_enable_url' ] != '' ) {
				$defaults[ 'atts' ] .= $atts[ 'overlay_icon_align' ] != '' ? ' data-align="' . $atts[ 'overlay_icon_align' ] . '"' : '';
				$defaults[ 'class' ] .= $atts[ 'overlay_overlay_effect' ] != '' ? ' mpc-overlay--' . $atts[ 'overlay_overlay_effect' ] : ' mpc-overlay--fade';
			}

			if ( $atts[ 'overlay_enable_lightbox' ] != '' ) {
				$icon = MPC_Snippets::parse_atts_icon( $atts, 'overlay' );

				$class = 'mpc-item-overlay__icon mpc-type--lightbox';
				$class .= $atts[ 'overlay_icon_mirror' ] != '' ? ' mpc-icon--mirror' : '';
				$class .= $atts[ 'overlay_icon_type' ] != '' ? ' mpc-icon--' . esc_attr( $atts[ 'overlay_icon_type' ] ) : '';
				$class .= $icon[ 'class' ];

				$defaults[ 'lightbox' ] = array(
					'class' => $class,
					'content' => $icon[ 'content' ],
				);
			}

			if ( $atts[ 'overlay_enable_url' ] != '' ) {
				$icon = MPC_Snippets::parse_atts_icon( $atts, 'overlay_url' );

				$class = 'mpc-item-overlay__icon mpc-type--external';
				$class .= $atts[ 'overlay_url_icon_mirror' ] != '' ? ' mpc-icon--mirror' : '';
				$class .= $atts[ 'overlay_url_icon_type' ] != '' ? ' mpc-icon--' . $atts[ 'overlay_url_icon_type' ] : '';
				$class .= $icon[ 'class' ];

				$defaults[ 'external' ] = array(
					'class' => $class,
					'content' => $icon[ 'content' ],
				);
			}

			return $defaults;
		}
		static function render_overlay( $atts, $image_link = '', $link = '' ) {
			global $mpc_can_link;

			$overlay_atts = MPC_Snippets::parse_atts_overlay( $atts );

			$overlay = '';
			$overlay_begin = '<div class="mpc-item-overlay mpc-transition"><div class="mpc-overlay--vertical-wrap"><div class="mpc-overlay--vertical">';
			$overlay_end   = '</div></div></div>';

			if ( $atts[ 'overlay_enable_lightbox' ] != '' ) {
				$lightbox = MPC_Snippets::lightbox_vendor();

				$url_settings = $mpc_can_link && $image_link != '' ? ' href="' . esc_url( $image_link ) . '"' : '';

				$overlay .= '<a' . $url_settings . ' rel="mpc" class="mpc-icon-anchor' . $lightbox . '">';
					$overlay .= '<i class="' . $overlay_atts[ 'lightbox' ][ 'class' ] . '">' . $overlay_atts[ 'lightbox' ][ 'content' ] . '</i>';
				$overlay .= '</a>';
			}
			if ( $atts[ 'overlay_enable_url' ] != '' ) {
				$url_settings = $mpc_can_link ? MPC_Snippets::parse_atts_url( $link ) : '';

				$overlay .= '<a' . $url_settings . ' class="mpc-icon-anchor">';
					$overlay .= '<i class="' . $overlay_atts[ 'external' ][ 'class' ] . '">' . $overlay_atts[ 'external' ][ 'content' ] . '</i>';
				$overlay .= '</a>';
			}

			return array(
				'content' => $overlay != '' ? $overlay_begin . $overlay . $overlay_end : '',
				'class' => $overlay_atts[ 'class' ],
				'atts' => $overlay_atts[ 'atts' ],
			);
		}
		static function lightbox_vendor() {
			global $mpc_ma_options;

			if( $mpc_ma_options[ 'magnific_popup' ] == '1' ) {
				wp_enqueue_style( 'mpc-massive-magnific-popup-css' );
				wp_enqueue_script( 'mpc-massive-magnific-popup-js' );

				$class = ' mpc-magnific-popup';
			} else {
				wp_enqueue_style( 'prettyphoto' );
				wp_enqueue_script( 'prettyphoto' );

				$class = ' mpc-pretty-photo';
			}

			return $class;
		}

		/* Content Loaders */
		static function add_icon_font( $font ) {
			if ( ! empty( $font ) ) {
				global $mpc_icons_fonts;

				$font = explode( ' ', $font );

				$mpc_icons_fonts[ $font[ 0 ] ] = true;
			}
		}

		static function add_typography( $preset ) {
			if ( empty( $preset ) ) {
				return;
			}

			global $mpc_typography_presets;

			if ( ! in_array( $preset, $mpc_typography_presets ) ) {
				$mpc_typography_presets[] = $preset;
			}
		}

		static function prefix_group( &$fields = array(), $group ) {
			for ( $i = 0; $i < count( $fields ); $i ++ ) {
				if ( isset( $fields[ $i ][ 'group' ] ) ) {
					continue;
				}

				$fields[ $i ][ 'group' ] = $group;
			}
		}

		static function params_weight( &$fields = array(), $start_with = -1000 ) {
			for( $i = 0; $i < count( $fields ); $i++ ) {
				if( isset( $fields[ $i ][ 'weight' ] ) ) {
					continue;
				}

				$fields[ $i ][ 'weight' ] = $start_with - $i;
			}
		}

		/* Get Image */
		static function get_image( $image_id, $thumb_size = 'large' ) {
			if ( ! $image_id ) {
				return false;
			}

			global $_wp_additional_image_sizes;
			$thumbnail = '';

			if ( $thumb_size == '' ) {
				$thumb_size = 'full';
			}

			if ( is_string( $thumb_size ) && ( ( ! empty( $_wp_additional_image_sizes[ $thumb_size ] ) && is_array( $_wp_additional_image_sizes[ $thumb_size ] ) ) || in_array( $thumb_size, array( 'thumbnail', 'thumb', 'medium', 'large', 'full' ) ) ) ) {
				$image_src = wp_get_attachment_image_src( $image_id, $thumb_size );
				$thumbnail = $image_src[ 0 ];
			} elseif ( $image_id ) {
				if ( is_string( $thumb_size ) ) {
					preg_match_all( '/\d+/', $thumb_size, $thumb_matches );
					if ( isset( $thumb_matches[ 0 ] ) ) {
						$thumb_size = array();
						if ( count( $thumb_matches[ 0 ] ) > 1 ) {
							$thumb_size[] = $thumb_matches[ 0 ][ 0 ]; // width
							$thumb_size[] = $thumb_matches[ 0 ][ 1 ]; // height
						} elseif ( count( $thumb_matches[ 0 ] ) > 0 && count( $thumb_matches[ 0 ] ) < 2 ) {
							$thumb_size[] = $thumb_matches[ 0 ][ 0 ]; // width
							$thumb_size[] = $thumb_matches[ 0 ][ 0 ]; // height
						} else {
							$thumb_size = false;
						}
					}
				}
				if ( is_array( $thumb_size ) ) {
					// Resize image to custom size
					$resize = wpb_resize( $image_id, null, $thumb_size[ 0 ], $thumb_size[ 1 ], true );
					$thumbnail = $resize[ 'url' ];
				}
			}

			return $thumbnail;
		}

		static function create_link_block( DOMNode &$node, $url, &$can_link, $is_column = false ) {
			if( !$can_link ) {
				return false;
			}

			$title = '';
			$mpc_url_settings = MPC_Snippets::parse_atts_url( $url, $title, 'array' );

			if( !is_array( $mpc_url_settings ) ) {
				return false;
			}

			$wrapper = $node->ownerDocument->createElement( 'div' );
			$renamed = $node->ownerDocument->createElement( 'a' );

			$wrapper->setAttribute( 'class', 'mpc-link-wrapper' );

			foreach( $mpc_url_settings as $attribute => $value ) {
				$renamed->setAttribute( $attribute, $value );
			}

			foreach ( $node->attributes as $attribute ) {
				$renamed->setAttribute( $attribute->name, $attribute->value );
			}

			while ( $node->firstChild ) {
				$renamed->appendChild( $node->firstChild );
			}

			$can_link = false;

			if( !$is_column ) {
				$wrapper->appendChild( $renamed );
				$node->parentNode->replaceChild( $wrapper, $node );
			} else {
				$node->parentNode->replaceChild( $renamed, $node );
			}

			unset( $wrapper, $renamed );

			return true;
		}

		static function get_scripts() {
			$scripts = self::$helper_scripts;
			self::$helper_scripts = '';
			return $scripts;
		}
		static function search_scripts( &$output, $offset = 0 ) {
			$start_at = stripos( $output, '<script', $offset );
			if( $start_at === false ) { return false; }

			$ends_at = stripos( $output, '</script>', $start_at ) + 9; // 9 =strlen( '</script>'
			if( $ends_at === false && $start_at == $ends_at ) { return false; }

			$script = substr( $output, $start_at, $ends_at - $start_at );
			self::$helper_scripts .= $script;
			if( $match = preg_replace( "/<script([\S|.|\s]*)<\/script>/mU", '', $output, 1 ) ) {
				$output = $match;
			}

			$is_end = strrpos( $output, '>', $start_at );
			if( $is_end !== false && $start_at >= $is_end ) {
				return false;
			} else {
				self::search_scripts( $output, $start_at );
				return true;
			}
		}
	}
}
if ( class_exists( 'MPC_Snippets' ) ) {
//	$MPC_Snippets = new MPC_Snippets;
}

if ( class_exists( 'WPBakeryShortCode' ) ) {
	class MPCShortCode_Base extends WPBakeryShortCode {
		public function singleParamHtmlHolder( $param, $value ) {
			$output = '';

			$param_name = isset( $param[ 'param_name' ] ) ? $param[ 'param_name' ] : '';
			$group      = isset( $param[ 'group' ] ) ? '[' . $param[ 'group' ] . '] ' : '';
			$heading    = isset( $param[ 'heading' ] ) ? $param[ 'heading' ] : '';
			$type       = isset( $param[ 'type' ] ) ? $param[ 'type' ] : '';
			$class      = isset( $param[ 'class' ] ) ? $param[ 'class' ] : '';

			if ( isset( $param['holder'] ) && $param['holder'] !== 'hidden' ) {
				$output .= '<' . $param['holder'] . ' class="wpb_vc_param_value ' . $param_name . ' ' . $type . ' ' . $class . '" name="' . $param_name . '">' . $value . '</' . $param['holder'] . '>';
			}

			if ( isset( $param['admin_label'] ) && $param['admin_label'] === true ) {
				$output .= '<span class="vc_admin_label admin_label_' . $param_name . ( empty( $value ) ? ' hidden-label' : '' ) . '"><label>' . $group . $heading . '</label>: ' . $value . '</span>';
			}

			return $output;
		}
	}
}

if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
	class MPCShortCodeContainer_Base extends WPBakeryShortCodesContainer {
		public function singleParamHtmlHolder( $param, $value ) {
			$output = '';

			$param_name = isset( $param[ 'param_name' ] ) ? $param[ 'param_name' ] : '';
			$group      = isset( $param[ 'group' ] ) ? '[' . $param[ 'group' ] . '] ' : '';
			$heading    = isset( $param[ 'heading' ] ) ? $param[ 'heading' ] : '';
			$type       = isset( $param[ 'type' ] ) ? $param[ 'type' ] : '';
			$class      = isset( $param[ 'class' ] ) ? $param[ 'class' ] : '';

			if ( isset( $param['holder'] ) && $param['holder'] !== 'hidden' ) {
				$output .= '<' . $param['holder'] . ' class="wpb_vc_param_value ' . $param_name . ' ' . $type . ' ' . $class . '" name="' . $param_name . '">' . $value . '</' . $param['holder'] . '>';
			}

			if ( isset( $param['admin_label'] ) && $param['admin_label'] === true ) {
				$output .= '<span class="vc_admin_label admin_label_' . $param_name . ( empty( $value ) ? ' hidden-label' : '' ) . '"><label>' . $group . $heading . '</label>: ' . $value . '</span>';
			}

			return $output;
		}
	}
}
