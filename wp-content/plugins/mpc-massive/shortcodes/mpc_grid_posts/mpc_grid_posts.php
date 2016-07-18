<?php
/*----------------------------------------------------------------------------*\
	GRID POSTS SHORTCODE
\*----------------------------------------------------------------------------*/

if ( ! class_exists( 'MPC_Grid_Posts' ) ) {
	class MPC_Grid_Posts {
		public $shortcode  = 'mpc_grid_posts';
		public $post_types = array();
		public $parts = array();

		function __construct() {
			add_shortcode( $this->shortcode, array( $this, 'shortcode_template' ) );

			if ( function_exists( 'vc_lean_map' ) ) {
				vc_lean_map( 'mpc_grid_posts', array( $this, 'shortcode_map' ) );
			} else {
				add_action( 'init', array( $this, 'shortcode_map_fallback' ) );
			}

			/* Read More button */
			add_filter( 'excerpt_more', array( $this, 'remove_excerpt_more' ), 999 );

			/* Autocomplete */
			add_filter( 'vc_autocomplete_mpc_grid_posts_ids_callback', 'vc_include_field_search', 10, 1 );
			add_filter( 'vc_autocomplete_mpc_grid_posts_ids_render', 'vc_include_field_render', 10, 1 );
			add_filter( 'vc_autocomplete_mpc_grid_posts_taxonomies_callback', 'vc_autocomplete_taxonomies_field_search', 10, 1 );
			add_filter( 'vc_autocomplete_mpc_grid_posts_taxonomies_render', 'vc_autocomplete_taxonomies_field_render', 10, 1 );

			$parts = array(
				'section_begin' => '',
				'section_end'   => '',
				'overlay_begin' => '',
				'overlay_end'   => '',
				'meta'          => '',
				'readmore'      => '',
				'title'         => '',
				'date'          => '',
				'author'        => '',
				'taxonomies'    => '',
				'comments'      => '',
				'description'   => '',
				'thumbnail'     => '',
			);

			$this->parts = $parts;
		}

		function shortcode_map_fallback() {
			vc_map( $this->shortcode_map() );
		}

		/* Enqueue all styles/scripts required by shortcode */
		function enqueue_shortcode_scripts() {
			wp_enqueue_style( 'mpc_grid_posts-css', MPC_MASSIVE_URL . '/shortcodes/mpc_grid_posts/css/mpc_grid_posts.css', array(), MPC_MASSIVE_VERSION );
			wp_enqueue_script( 'mpc_grid_posts-js', MPC_MASSIVE_URL . '/shortcodes/mpc_grid_posts/js/mpc_grid_posts' . MPC_MASSIVE_MIN . '.js', array( 'jquery' ), MPC_MASSIVE_VERSION );
		}

		/* Remove Excerpt More */
		function remove_excerpt_more() {
			return '';
		}

		/* Retrieve posts data */
		function get_posts_details() {
			$post_types = get_post_types( array() );

			if ( is_array( $post_types ) && ! empty( $post_types ) ) {
				foreach ( $post_types as $post_type ) {
					if ( $post_type !== 'revision' && $post_type !== 'nav_menu_item' && $post_type !== 'attachment' ) {
						$label = ucfirst( $post_type );
						$this->post_types[] = array( $post_type, __( $label, 'mpc' ) );
					}
				}
			}

			$this->post_types[] = array( 'ids', __( 'Custom List', 'mpc' ) );
		}

		/* Build query */
		function build_query( $atts ) {
			$args = array(
				'post_status' => 'publish',
				'ignore_sticky_posts' => true,
				'paged' => (int) $atts[ 'paged' ],
				'post_type' => $atts[ 'post_type' ] == 'ids' ? 'any' : $atts[ 'post_type' ],
				'orderby' => $atts[ 'orderby' ],
				'order' => $atts[ 'order' ],
			);

			/* Other args */
			if( $atts[ 'post_type' ] != 'ids' ) {
				$args[ 'posts_per_page' ] = (int) $atts[ 'items_number' ];

				if( $atts[ 'taxonomies' ] != '' ) {
					$tax_types = get_taxonomies( array( 'public' => true ) );

					$terms = get_terms( array_keys( $tax_types ), array(
						'hide_empty' => false,
						'include' => $atts[ 'taxonomies' ],
					) );

					$args['tax_query'] = array();

					$tax_queries = array();
					foreach ( $terms as $t ) {
						if ( ! isset( $tax_queries[ $t->taxonomy ] ) ) {
							$tax_queries[ $t->taxonomy ] = array(
								'taxonomy' => $t->taxonomy,
								'field' => 'id',
								'terms' => array( $t->term_id ),
								'relation' => 'IN'
							);
						} else {
							$tax_queries[ $t->taxonomy ]['terms'][] = $t->term_id;
						}
					}

					$args['tax_query'] = array_values( $tax_queries );
					$args['tax_query']['relation'] = 'OR';
				}
			}

			if( $atts[ 'post_type' ] == 'ids' ) {
				if( $atts[ 'ids' ] != '' ) {
					$args[ 'post__in' ] = explode( ', ', $atts[ 'ids' ] );
				}
			}

			return $args;
		}

		/* Get Posts */
		function get_posts( $atts ) {
			$args = $this->build_query( $atts );

			$query = new WP_Query( $args );

			return $query;
		}

		/* Generate template */
		function setup_template() {
			add_filter( 'ma/single_post/permalink',         array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/title',             array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/lightbox_src',      array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/get/author',        array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/get/comments',      array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/get/content',       array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/get/date',          array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/get/taxonomies',    array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/get/thumbnail',     array( $this, 'template_tags' ), 1000, 2 );
			add_filter( 'ma/single_post/get/title',         array( $this, 'template_tags' ), 1000, 2 );

			add_filter( 'ma/single_post/build/thumbnail',   array( $this, 'template_tags' ), 1000, 2 );
		}
		function unset_template() {
			remove_filter( 'ma/single_post/permalink',       array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/title',           array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/lightbox_src',    array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/get/author',      array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/get/comments',    array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/get/content',     array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/get/date',        array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/get/taxonomies',  array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/get/thumbnail',   array( $this, 'template_tags' ), 1000 );
			remove_filter( 'ma/single_post/get/title',       array( $this, 'template_tags' ), 1000 );

			remove_filter( 'ma/single_post/build/thumbnail', array( $this, 'template_tags' ), 1000 );
		}
		function template_tags( $content, $tag ) {
			return $tag != '' ? '{{' . $tag . '}}' : $content;
		}

		/* Return Pagination content */
		public function get_paginated_content( $atts ) {
			$data_query = $this->get_posts( $atts );
			if( !$data_query->have_posts() ) return '';

			global $MPC_Single_Post;

			/* Prepare */
			$items = array(
				'items' => array(),
				'settings' => array(
					'current' => $data_query->query[ 'paged' ],
					'pages' => $data_query->max_num_pages
				),
			);

			/* Data Build */
			while( $data_query->have_posts() )  {
				$data_query->the_post();

				$item = array();

				$item[ 'title' ]           = get_the_title();
				$item[ 'permalink' ]       = apply_filters( 'ma/single_post/permalink', get_the_permalink(), 'permalink' );
				$item[ 'lightbox_src' ]    = $MPC_Single_Post->get_lightbox_src();
				$item[ 'get_date' ]        = $MPC_Single_Post->get_date( $atts[ 'layout' ] );
				$item[ 'get_title' ]       = $MPC_Single_Post->get_title();
				$item[ 'get_author' ]      = $MPC_Single_Post->get_author();
				$item[ 'get_content' ]     = $MPC_Single_Post->get_content();
				$item[ 'get_comments' ]    = $MPC_Single_Post->get_comments();
				$item[ 'get_thumbnail' ]   = $MPC_Single_Post->get_thumbnail();
				$item[ 'build_thumbnail' ] = $MPC_Single_Post->build_thumbnail( $atts[ 'layout' ], '' );
				$item[ 'get_taxonomies' ]  = $MPC_Single_Post->get_taxonomies( $atts[ 'layout' ] );

				$items[ 'items' ][] = $item;
			}

			wp_reset_postdata();

			return $items;
		}

		/* Return shortcode markup for display */
		function shortcode_template( $atts, $content = null ) {
			/* Enqueues */
			wp_enqueue_script( 'mpc-massive-isotope-js', MPC_MASSIVE_URL . '/assets/js/libs/isotope.min.js', array( 'jquery' ), '', true );

			global $MPC_Pagination, $MPC_Single_Post, $MPC_Shortcode, $mpc_ma_options;
			if ( $mpc_ma_options[ 'single_js_css' ] !== '1' ) {
				$this->enqueue_shortcode_scripts();
			}

			$grid_atts = shortcode_atts( array(
				'class'                     => '',
				'preset'                    => '',
				'layout'                    => 'style_1',
				'cols'                      => '2',
				'gap'                       => '0',

				'post_type'                 => 'post',
				'taxonomies'                => '',
				'ids'                       => '',
				'order'                     => 'ASC',
				'orderby'                   => 'date',
				'items_number'              => '6',
				'paged'                     => '1',

				'odd_background_type'       => 'color',
				'odd_background_color'      => '',
				'odd_background_image'      => '',
				'odd_background_image_size' => 'large',
				'odd_background_repeat'     => 'no-repeat',
				'odd_background_size'       => 'initial',
				'odd_background_position'   => 'middle-center',
				'odd_background_gradient'   => '#83bae3||#80e0d4||0;100||180||linear',

				'mpc_pagination__preset'    => '',
			), $atts );

			/* Build Query */
			$data_query = $this->get_posts( $grid_atts );
			if( !$data_query->have_posts() ) return '';

			/* Prepare */
			$css_id = $this->shortcode_styles( $grid_atts );
			$animation    = MPC_Snippets::parse_atts_animation( $grid_atts );

			/* Query Atts for Pagination */
			$query_atts = array(
				'layout'       => $grid_atts[ 'layout' ],
				'post_type'    => $grid_atts[ 'post_type' ],
				'taxonomies'   => $grid_atts[ 'taxonomies' ],
				'ids'          => $grid_atts[ 'ids' ],
				'order'        => $grid_atts[ 'order' ],
				'orderby'      => $grid_atts[ 'orderby' ],
				'items_number' => $grid_atts[ 'items_number' ],
				'target'       => $css_id,
				'callback'     => 'MPC_Grid_Posts',
			);
			$sh_atts = $grid_atts[ 'cols' ] != '' ? ' data-grid-cols="' . (int) esc_attr( $grid_atts[ 'cols' ] ) . '"' : '';
			$sh_atts .= $grid_atts[ 'layout' ] != '' ? " data-query='" . json_encode( $query_atts ) . "'" : '';

			/* Get Posts */
			$css_settings = array(
				'id' => $css_id,
				'selector' => '.mpc-grid-posts[id="' . $css_id . '"] .mpc-post'
			);
			$MPC_Shortcode[ 'pagination' ] = $data_query;
			$MPC_Single_Post->query = &$data_query;

			/* Generate markup & template */
			$content = $MPC_Single_Post->shortcode_template( $atts, null, null, $css_settings );
			$this->setup_template();
			$template = $MPC_Single_Post->item_template( $atts );
			$this->unset_template();

			/* Shortcode classes | Animation | Layout */
			$classes = ' mpc-init';
			$classes .= $animation != '' ? ' mpc-animation' : '';
			$classes .= $MPC_Single_Post->classes;
			$classes .= ' ' . esc_attr( $grid_atts[ 'class' ] );

			/* Shortcode Output */
			$return = '<div id="' . $css_id . '" class="mpc-grid-posts' . $classes . '" ' . $animation . $sh_atts . '>';
				$return .= $template . $content;
			$return .= '</div>';

			/* Prepare Pagination */
			$return .= $MPC_Pagination->shortcode_template( $grid_atts[ 'mpc_pagination__preset' ], null, null, $css_id );

			/* Restore original Post Data */
			wp_reset_postdata();

			return $return;
		}

		/* Generate shortcode styles */
		function shortcode_styles( $styles ) {
			global $mpc_massive_styles;
			$css_id = uniqid( 'mpc_grid_posts-' . rand( 1, 100 ) );
			$style  = '';

			// Gap
			if( $styles[ 'gap' ] = (int) $styles[ 'gap' ] ) {
				$style .= '.mpc-grid-posts[id="' . $css_id . '"] {';
					$style .= ' margin: 0 -' . ( $styles[ 'gap' ] * .5 ) . 'px -' . $styles[ 'gap' ] . 'px !important;';
				$style .= '}';

				$style .= '.mpc-grid-posts[id="' . $css_id . '"] .mpc-post__wrapper {';
					$style .=  'margin: 0 ' . ( $styles[ 'gap' ] * .5 ) . 'px; ';
				$style .= '}';

				$style .= '.mpc-grid-posts[id="' . $css_id . '"] .mpc-post {';
					$style .=  'margin-bottom: ' . $styles[ 'gap' ] . 'px;';
				$style .= '}';
			}

			// Regular
			if ( $temp_style = MPC_Snippets::css_background( $styles, 'odd' ) ) {
				$style .= '.mpc-grid-posts[id="' . $css_id . '"] .mpc-post:nth-child(2n+1) .mpc-post__content { ';
					$style .= $temp_style;
				$style .= '}';
			}

			$mpc_massive_styles .= $style;

			return $css_id;
		}

		/* Map all shortcode options to Visual Composer popup */
		function shortcode_map() {
			if ( ! function_exists( 'vc_map' ) ) {
				return '';
			}

			$this->get_posts_details();

			$base = array(
				array(
					'type'        => 'mpc_preset',
					'heading'     => __( 'Main Preset', 'mpc' ),
					'param_name'  => 'preset',
					'tooltip'     => __( 'Presets are used to easily configure your shortcode. You can choose one of the premade presets or create your own. You can preview the available presets with <b>Preview & Load Preset</b> button.<br><br><em>After choosing preset from dropdown you have to <b>Reload</b> it to take effect.</em>', 'mpc' ),
					'value'       => '',
					'shortcode'   => $this->shortcode,
					'wide_modal'  => true,
					'description' => __( 'Choose preset or create new one.', 'mpc' ),
				),
			);

			$base_ext = array(
				array(
					'type'             => 'mpc_slider',
					'heading'          => __( 'Gap', 'mpc' ),
					'param_name'       => 'gap',
					'tooltip'          => __( 'Choose gap between grid items.', 'mpc' ),
					'min'              => 0,
					'max'              => 100,
					'step'             => 1,
					'value'            => 0,
					'unit'             => 'px',
					'edit_field_class' => 'vc_col-sm-12 vc_column mpc-advanced-field',
				),
			);

			$source = array(
				array(
					'type'       => 'mpc_divider',
					'title'      => __( 'Source', 'mpc' ),
					'param_name' => 'source_section_divider',
					'group'      => __( 'Source', 'mpc' ),
				),
				array(
					'type'             => 'dropdown',
					'heading'          => __( 'Data source', 'mpc' ),
					'param_name'       => 'post_type',
					'tooltip'          => __( 'Select post types for grid. <b>Custom List</b> lets you select the exact list of posts you want.', 'mpc' ),
					'value'            => $this->post_types,
					'std'              => 'post',
					'group'            => __( 'Source', 'mpc' ),
					'edit_field_class' => 'vc_col-sm-6 vc_column',
				),
				array(
					'type'             => 'autocomplete',
					'heading'          => __( 'Posts', 'mpc' ),
					'param_name'       => 'ids',
					'tooltip'          => __( 'Define list of posts displayed by this grid.', 'mpc' ),
					'settings'         => array(
						'multiple'      => true,
						'sortable'      => true,
						'unique_values' => true,
					),
					'std'              => '',
					'edit_field_class' => 'vc_col-sm-6 vc_column',
					'dependency'       => array( 'element' => 'post_type', 'value' => array( 'ids' ), ),
					'group'            => __( 'Source', 'mpc' ),
				),
				array(
					'type'               => 'autocomplete',
					'heading'            => __( 'Taxonomies or Tags', 'mpc' ),
					'param_name'         => 'taxonomies',
					'tooltip'            => __( 'Define posts tags, categories or custom taxonomies. It will filter the posts to display only the ones with specified tag/category.', 'mpc' ),
					'settings'           => array(
						'multiple'       => true,
						'min_length'     => 1,
						'groups'         => true,
						'unique_values'  => true,
						'display_inline' => true,
						'delay'          => 500,
						'auto_focus'     => true,
					),
					'std'                => '',
					'param_holder_class' => 'vc_not-for-custom',
					'edit_field_class'   => 'vc_col-sm-6 vc_column',
					'dependency'         => array( 'element' => 'post_type', 'value_not_equal_to' => array( 'ids' ), ),
					'group'              => __( 'Source', 'mpc' ),
				),
				array(
					'type'               => 'dropdown',
					'heading'            => __( 'Sort by', 'mpc' ),
					'param_name'         => 'orderby',
					'tooltip'            => __( 'Select posts sorting parameter.', 'mpc' ),
					'value'              => array(
						__( 'Date', 'mpc' )               => 'date',
						__( 'Order by post ID', 'mpc' )   => 'ID',
						__( 'Author', 'mpc' )             => 'author',
						__( 'Title', 'mpc' )              => 'title',
						__( 'Last modified date', 'mpc' ) => 'modified',
						__( 'Number of comments', 'mpc' ) => 'comment_count',
						__( 'Random order', 'mpc' )       => 'rand',
					),
					'std'                => 'date',
					'group'              => __( 'Source', 'mpc' ),
					'edit_field_class'   => 'vc_col-sm-4 vc_column mpc-advanced-field',
					'dependency'         => array(
						'element'            => 'post_type',
						'value_not_equal_to' => array( 'ids' ),
					),
				),
				array(
					'type'               => 'dropdown',
					'heading'            => __( 'Order', 'mpc' ),
					'param_name'         => 'order',
					'tooltip'            => __( 'Select posts sorting order.', 'mpc' ),
					'group'              => __( 'Source', 'mpc' ),
					'value'              => array(
						__( 'Descending', 'mpc' ) => 'DESC',
						__( 'Ascending', 'mpc' )  => 'ASC',
					),
					'std'                => 'ASC',
					'edit_field_class'   => 'vc_col-sm-4 vc_column mpc-advanced-field',
					'dependency'         => array(
						'element'            => 'post_type',
						'value_not_equal_to' => array( 'ids' ),
					),
				),
				array(
					'type'             => 'mpc_text',
					'heading'          => __( 'Items Per Page', 'mpc' ),
					'param_name'       => 'items_number',
					'tooltip'          => __( 'Define maximum number of displayed posts per page. If the number of posts meeting the above parameters is smaller it will only show those posts.', 'mpc' ),
					'value'            => '6',
					'addon'            => array(
						'icon'  => 'dashicons dashicons-slides',
						'align' => 'prepend',
					),
					'edit_field_class' => 'vc_col-sm-4 vc_column mpc-advanced-field',
					'label'            => '',
					'validate'         => true,
					'group'            => __( 'Source', 'mpc' ),
				)
			);


			/* General */
			$item_odd_background  = MPC_Snippets::vc_background( array( 'prefix' => 'odd', 'subtitle' => __( 'Odd', 'mpc' ), 'group' => __( 'Item', 'mpc' ) ) );
			$rows_cols = MPC_Snippets::vc_rows_cols( array( 'cols' => array( 'min' => 1, 'max' => 4, 'default' => 2 ), 'rows' => false ) );
			$animation = MPC_Snippets::vc_animation_basic();
			$class     = MPC_Snippets::vc_class();

			/* Pagination */
			$integrate_pagination = vc_map_integrate_shortcode( 'mpc_pagination', 'mpc_pagination__', __( 'Pagination', 'mpc' ) );

			/* Integrate Item */
			$item_exclude   = array( 'exclude_regex' => '/animation_(.*)|item_id/', );
			$integrate_item = vc_map_integrate_shortcode( 'mpc_single_post', '', '', $item_exclude );

			$params = array_merge(
				$base,
				$rows_cols,
				$base_ext,
				$source,

				$integrate_item,
				$item_odd_background,

				$integrate_pagination,
				$animation,
				$class
			);

			return array(
				'name'        => __( 'Grid Posts', 'mpc' ),
				'description' => __( 'Grid with posts', 'mpc' ),
				'base'        => 'mpc_grid_posts',
//				'icon'        => MPC_MASSIVE_URL . '/assets/images/icons/mpc-grid-posts.png',
				'icon'        => 'mpc-shicon-grid-posts',
				'category'    => __( 'MPC', 'mpc' ),
				'params'      => $params,
			);
		}
	}
}

if ( class_exists( 'MPC_Grid_Posts' ) ) {
	global $MPC_Grid_Posts;
	$MPC_Grid_Posts = new MPC_Grid_Posts;
}

if ( class_exists( 'MPCShortCode_Base' ) ) {
	class WPBakeryShortCode_mpc_grid_posts extends MPCShortCode_Base {}
}
