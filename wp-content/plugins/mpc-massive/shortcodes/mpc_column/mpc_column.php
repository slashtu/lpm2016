<?php
/*----------------------------------------------------------------------------*\
	COLUMN SHORTCODE
\*----------------------------------------------------------------------------*/

if ( ! class_exists( 'MPC_Column' ) ) {
	class MPC_Column {
		public $shortcode      = 'vc_column';
		public $panel_section  = array();

		public $css_id    = '';
		public $classes   = '';
		public $sh_atts   = array();
		private $atts     = array();
		private $defaults = array();
		private $html     = '';

		function __construct() {
			global $mpc_ma_options;

			$this->html = new DOMDocument( '1.1' );

			if ( isset( $mpc_ma_options[ 'vc_row_addons' ] ) && $mpc_ma_options[ 'vc_row_addons' ] == '1' ) {
				add_filter( 'vc_shortcode_output', array( $this, 'column_output' ), 9000, 3 );

				add_action( 'admin_init', array( $this, 'shortcode_map' ), 1000 );

				$this->getDefaults();
			} else {
				add_filter( 'vc_shortcode_output', array( $this, 'append_class' ), 9000, 3 );
			}
		}

		/* Enqueue all styles/scripts required by shortcode */
		function enqueue_shortcode_scripts() {
			wp_enqueue_style( 'mpc_column-css', MPC_MASSIVE_URL . '/shortcodes/mpc_column/css/mpc_column.css', array(), MPC_MASSIVE_VERSION );
			wp_enqueue_script( 'mpc_column-js', MPC_MASSIVE_URL . '/shortcodes/mpc_column/js/mpc_column' . MPC_MASSIVE_MIN . '.js', array( 'jquery' ), MPC_MASSIVE_VERSION );
		}

		/* Return shortcode markup for display */
		function column_output( $output, $shortcode, $atts ) {
			$tag = $shortcode->settings( 'base' );

			if( !in_array( $tag, array( 'vc_column', 'vc_column_inner' ) ) ) { // vc_row | vc_row_inner
				return $output;
			}

			if ( ! function_exists( 'libxml_use_internal_errors' ) ) {
				return $output;
			}

			global $mpc_ma_options;
			if ( $mpc_ma_options[ 'single_js_css' ] !== '1' ) {
				$this->enqueue_shortcode_scripts();
			}

			// Prepare and prechecks
			$this->atts = shortcode_atts( $this->defaults, $atts );
			$animation = MPC_Snippets::parse_atts_animation( $this->atts, 'array' );

			if( $this->atts[ 'url' ] == ''
				&& $this->atts[ 'enable_sticky' ] == ''
			    && $this->atts[ 'alignment' ] == ''
			    && count( $animation ) > 0 ) {

				return $this->append_class( $output, $shortcode );
			}

			// Basic Shortcode Atts
			$this->css_id = $this->shortcode_styles( $this->atts );
			$this->sh_atts[ 'data-column-id' ] = $this->css_id;
			$this->column_class( $animation );

			if( $this->atts[ 'enable_sticky' ] != '' ) {
				$this->sh_atts[ 'data-offset' ] = esc_attr( $this->atts[ 'sticky_offset' ] );
			}

			foreach ( $animation as $attr => $value ) {
				$this->sh_atts[ $attr ] = $value;
			}

			// Convert HTML markup inside scripts
			MPC_Snippets::search_scripts( $output );

			// Link Block
			global $mpc_can_link;
			$mpc_can_link = $tag === 'vc_row_inner' ? $mpc_can_link : true;
			if( $mpc_can_link && $this->atts[ 'url' ] != '' && stripos( $output, '<a' ) !== false ) {
				if( current_user_can( 'edit_posts') ) {
					$output_prepend = '<div class="mpc-notice mpc-cannot-link">' . __( '<strong>Massive Addons</strong>: Link Block could not be added to this column because it contains a link already. Check our documentation for more information: <a href="http://hub.mpcreation.net/knowledgebase/link-column/">Link Block.</a> <br/>Psss.. This information is not visible for your visitors.', 'mpc' ) . '</div>';
				}
				$mpc_can_link = false;
			}

			// DOM manipulating
			libxml_use_internal_errors( true ); // Prevent entity errors
			$this->html->loadHTML( mb_convert_encoding( $output, 'HTML-ENTITIES', 'UTF-8' ) );
			$column = $this->html->getElementsByTagName( 'div' )->item( 0 );

			// Append classes
			if( $column->getAttribute( 'class' ) . $this->classes ) {
				$column->setAttribute( 'class', $column->getAttribute( 'class' ) . $this->classes );
			}

			// Append attributes
			if( count( $this->sh_atts ) > 0 ) {
				foreach ( $this->sh_atts as $key => $attribute ) {
					$column->setAttribute( $key, $attribute );
				}
			}

			// Link Block
			global $mpc_can_link;
			$mpc_can_link_next = $mpc_can_link;
			MPC_Snippets::create_link_block( $column, $this->atts[ 'url' ], $mpc_can_link, true );
			$mpc_can_link = $mpc_can_link_next;

			// Clear content
			$output = $this->html->saveHTML();
			$output = preg_match( "/<body>([\S|.|\s]*)<\/body>/mU", $output, $matches );
			$output = $matches[ 1 ];

			if( isset( $output_append ) ) {
				$output = $output_prepend . $output;
			}

			// Clear
			unset( $html, $column, $mpc_can_link_next );
			$this->reset();

			// Output
			return $output;
		}

		function reset() {
			$this->css_id  = '';
			$this->classes = '';
			$this->sh_atts = '';
			$this->atts    = $this->defaults;

			libxml_clear_errors();
		}

		function append_class( $output, $shortcode, $atts = '' ) { // ToDo: Maybe a faster way
			if( !in_array( $shortcode->settings( 'base' ), array( 'vc_column', 'vc_column_inner' ) ) ) { // vc_row | vc_row_inner
				return $output;
			}

			if( !function_exists( 'libxml_use_internal_errors' ) ) {
				return $output;
			}

			// Set up DOMDocument
			libxml_use_internal_errors( true ); // Prevent entity errors

			$this->html->loadHTML( mb_convert_encoding( $output, 'HTML-ENTITIES', 'UTF-8' ) );
			$column = $this->html->getElementsByTagName( 'div' )->item( 0 );

			// Append classes
			$column->setAttribute( 'class', $column->getAttribute( 'class' ) . ' mpc-column' );

			// Clear content
			$output = $this->html->saveHTML();
			$output = preg_match( "/<body>([\S|.|\s]*)<\/body>/mU", $output, $matches );
			$output = $matches[ 1 ];

			// Clear
			$this->reset();

			// Output
			return $output;
		}

		function column_class( $animation = array() ) {
			// classes logic
			$mpc_classes = ' mpc-column';
			$mpc_classes .= $this->atts[ 'enable_sticky' ] != '' ? ' mpc-column--sticky' : '';
			$mpc_classes .= count( $animation ) > 0 ? ' mpc-animation' : '';

			$this->classes = $mpc_classes;
		}

		function getDefaults() {
			$this->defaults = array(
				'content_preset'          => '',
				'url'                     => '',
				'enable_sticky'           => '',
				'sticky_offset'           => '',
				'alignment'               => '',

				'animation_in_type'       => 'none',
				'animation_in_duration'   => '300',
				'animation_in_delay'      => '0',
				'animation_in_offset'     => '100',

				'animation_loop_type'     => 'none',
				'animation_loop_duration' => '1000',
				'animation_loop_delay'    => '1000',
				'animation_loop_hover'    => '',
			);
		}

			/* Generate shortcode styles */
		function shortcode_styles( $styles ) {
			global $mpc_massive_styles;
			$css_id = uniqid( 'mpc_column-' . rand( 1, 100 ) );
			$style  = '';

			if ( $styles[ 'alignment' ] != '' ) {
				$style .= '.mpc-column[data-column-id="' . $css_id . '"] {';
					$style .= 'text-align: ' . $styles[ 'alignment' ] . ';';
				$style .= '}';
			}

			$mpc_massive_styles .= $style;

			return $css_id;
		}

		/* Map all shortcode options to Visual Composer popup */
		function shortcode_map() {
			if ( ! function_exists( 'vc_add_params' ) ) {
				return;
			}

			/* Column */
			$link_params = array(
				array(
					'type'        => 'vc_link',
					'heading'     => __( 'Link', 'mpc' ),
					'param_name'  => 'url',
					'value'       => '',
					'description' => __( 'Specify URL.', 'mpc' ),
					'weight'      => -1005,
					'group'       => __( 'Extras', 'mpc' ),
				),
				array(
					'type'             => 'checkbox',
					'heading'          => __( 'Enable Sticky Column', 'mpc' ),
					'param_name'       => 'enable_sticky',
					'value'            => array( __( 'Yes', 'mpc' ) => 'true' ),
					'std'              => '',
					'description'      => __( 'Enable Sticky Column.', 'mpc' ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
					'weight'           => -1010,
					'group'            => __( 'Extras', 'mpc' ),
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Top Offset', 'mpc' ),
					'param_name'       => 'sticky_offset',
					'value'            => 0,
					'addon'            => array(
						'icon'  => 'dashicons dashicons-arrow-down-alt',
						'align' => 'prepend',
					),
					'label'            => 'px',
					'validate'         => true,
					'edit_field_class' => 'vc_col-sm-6 vc_column mpc-clear--both',
					'dependency'       => array(
						'element' => 'enable_sticky',
						'value'   => 'true'
					),
					'weight'           => -1015,
					'group'            => __( 'Extras', 'mpc' ),
				),
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Content Alignment', 'mpc' ),
					'param_name'       => 'alignment',
					'value'            => array(
						__( 'Default', 'mpc' ) => '',
						__( 'Left', 'mpc' )    => 'left',
						__( 'Center', 'mpc' )  => 'center',
						__( 'Right', 'mpc' )   => 'right',
						__( 'Justify', 'mpc' ) => 'justify',
					),
					'std'              => '',
					'description'      => __( 'Specify custom alignment for column content.', 'mpc' ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
					'weight'           => -1020,
					'group'            => __( 'Extras', 'mpc' ),
				),
			);

			$animation = MPC_Snippets::vc_animation();

			$params = array_merge( $link_params, $animation );
			MPC_Snippets::params_weight( $params );

			$atts = vc_get_shortcode( 'vc_column' );
			$atts[ 'params' ] = array_merge( $atts[ 'params' ] , $params );
			unset( $atts[ 'base' ] );
			vc_map_update( 'vc_column', $atts );

			$atts = vc_get_shortcode( 'vc_column_inner' );
			$atts[ 'params' ] = array_merge( $atts[ 'params' ] , $params );
			unset( $atts[ 'base' ] );
			vc_map_update( 'vc_column_inner', $atts );
		}
	}
}

if ( class_exists( 'MPC_Column' ) ) {
	global $MPC_Column;
	$MPC_Column = new MPC_Column();
}