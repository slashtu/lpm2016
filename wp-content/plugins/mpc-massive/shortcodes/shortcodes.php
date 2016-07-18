<?php
/*----------------------------------------------------------------------------*\
	SHORTCODES
\*----------------------------------------------------------------------------*/

$mpc_shortcodes = array(
	// Prevent errors in nested shortcodes
	'mpc_tooltip',
	'mpc_ribbon',

	// Basic Shortcodes
	'mpc_animated_text',
	'mpc_button_set',
	'mpc_chart',
	'mpc_countdown',
	'mpc_divider',
	'mpc_dropcap',
	'mpc_flipbox',
	'mpc_grid_anything',
	'mpc_grid_images',
	'mpc_icon',
	'mpc_mailchimp',
	'mpc_map',
	'mpc_marker',
	'mpc_navigation',
	'mpc_progress',
	'mpc_qrcode',
	'mpc_testimonial',

	// WooCommerce
	'mpc_wc_add_to_cart',

	// Shortcodes dependent on basic shortcodes
	'mpc_accordion',
	'mpc_alert',
	'mpc_button',
	'mpc_callout',
	'mpc_carousel_anything',
	'mpc_carousel_image',
	'mpc_carousel_slider',
	'mpc_carousel_testimonial',
	'mpc_circle_icons',
	'mpc_connected_icons',
	'mpc_counter',
	'mpc_cubebox',
	'mpc_hotspot',
	'mpc_icon_column',
	'mpc_icon_list',
	'mpc_interactive_image', // Ribbon + Hotspot
	'mpc_ihover',
	'mpc_ihover_item',
    'mpc_image',
	'mpc_lightbox',
	'mpc_modal',
	'mpc_pagination',
	'mpc_pricing_box',
	'mpc_pricing_column',
	'mpc_pricing_legend',
	'mpc_quote',
	'mpc_single_post',
	'mpc_tabs',

	// Shortcodes dependent on advanced shortcodes
	'mpc_carousel_posts',
	'mpc_grid_posts',
);

add_filter( 'pre_update_option_mpc_massive', 'mpc_recheck_mpc_massive', 10, 2 );
function mpc_recheck_mpc_massive( $new_value, $old_value ) {
	$enable_dependencies = array(
		'mpc_accordion'            => array( 'mpc_icon' ),
		'mpc_alert'                => array( 'mpc_tooltip' ),
		'mpc_button_set'           => array( 'mpc_button', 'mpc_ligthbox' ),
		'mpc_callout'              => array( 'mpc_button', 'mpc_divider', 'mpc_ribbon' ),
		'mpc_carousel_anything'    => array( 'mpc_navigation' ),
		'mpc_carousel_image'       => array( 'mpc_navigation' ),
		'mpc_carousel_posts'       => array( 'mpc_navigation', 'mpc_single_post' ),
		'mpc_carousel_slider'      => array( 'mpc_navigation' ),
		'mpc_carousel_testimonial' => array( 'mpc_navigation', 'mpc_testimonial' ),
		'mpc_circle_icons'         => array( 'mpc_icon_column' ),
		'mpc_cubebox'              => array( 'mpc_ribbon' ),
		'mpc_connected_icons'      => array( 'mpc_icon_column' ),
		'mpc_counter'              => array( 'mpc_icon', 'mpc_divider' ),
		'mpc_grid_posts'           => array( 'mpc_pagination', 'mpc_single_post' ),
		'mpc_hotspot'              => array( 'mpc_tooltip' ),
		'mpc_icon_column'          => array( 'mpc_icon', 'mpc_divider' ),
		'mpc_icon_list'            => array( 'mpc_icon' ),
		'mpc_ihover'               => array( 'mpc_ihover_item' ),
		'mpc_image'                => array( 'mpc_ribbon' ),
		'mpc_interactive_image'    => array( 'mpc_hotspot' ),
		'mpc_lightbox'             => array( 'mpc_tooltip' ),
		'mpc_map'                  => array( 'mpc_marker' ),
		'mpc_modal'                => array( 'mpc_icon' ),
		'mpc_pricing_box'          => array( 'mpc_button', 'mpc_navigation', 'mpc_pricing_column', 'mpc_pricing_legend' ),
		'mpc_quote'                => array( 'mpc_ribbon' ),
		'mpc_single_post'          => array( 'mpc_button' ),
		'mpc_tabs'                 => array( 'mpc_button' ),
		'mpc_wc_add_to_cart'       => array( 'mpc_tooltip' ),
	);

	$multi_dependencies = array(
		'mpc_navigation' => array( 'mpc_carousel_anything', 'mpc_carousel_image', 'mpc_carousel_posts', 'mpc_carousel_slider', 'mpc_carousel_testimonial', 'mpc_pricing_box'
		),
		'mpc_pagination' => array( 'mpc_grid_posts' ),
		'mpc_ribbon'     => array( 'mpc_cubebox', 'mpc_image', 'mpc_quote' ),
		'mpc_tooltip'    => array( 'mpc_alert', 'mpc_hotspot', 'mpc_lightbox', 'mpc_wc_add_to_cart' ),
	);

	foreach ( $new_value[ 'shortcodes-list' ] as $shortcode => $state ) {
		if ( $state === '1' ) {
			if ( isset( $enable_dependencies[ $shortcode ] ) ) {
				mpc_recheck_dependencies( $shortcode, $enable_dependencies, $new_value[ 'shortcodes-list' ] );
			}
		}
	}

	foreach ( $multi_dependencies as $subshortcode => $list ) {
		foreach ( $list as $shortcode ) {
			if ( $new_value[ 'shortcodes-list' ][ $shortcode ] === '1' ) {
				$new_value[ 'shortcodes-list' ][ $subshortcode ] = '1';
			}
		}
	}

	return $new_value;
}

function mpc_recheck_dependencies( $shortcode, $dependencies, &$new_value ) {
	$new_value[ 'shortcodes-list' ][ $shortcode ] = '1';

	if ( isset( $dependencies[ $shortcode ] ) ) {
		foreach ( $dependencies[ $shortcode ] as $dependent_shortcode ) {
			mpc_recheck_dependencies( $dependent_shortcode, $dependencies, $new_value );
		}
	}
}

require_once( MPC_MASSIVE_DIR . '/shortcodes/mpc_snippets.php' );

global $mpc_ma_options;
if ( isset( $mpc_ma_options[ 'enabled_shortcodes' ][ 'all' ] ) && $mpc_ma_options[ 'enabled_shortcodes' ][ 'all' ] != '1' ) {
	foreach ( $mpc_ma_options[ 'enabled_shortcodes' ] as $name => $value ) {
		if ( $value === '0' ) {
			$index = array_search( $name, $mpc_shortcodes );

			if ( $index !== false ) {
				unset( $mpc_shortcodes[ $index ] );
			}
		}
	}
}

foreach( $mpc_shortcodes as $shortcode ) {
	require_once( MPC_MASSIVE_DIR . '/shortcodes/' . $shortcode . '/' . $shortcode . '.php' );
}

// Changed because not every theme has vc_row/vc_column class. We have to always add mpc-row and mpc-column classes
// to VC Row/Column elements to be able to use them in JS/CSS
require_once( MPC_MASSIVE_DIR . '/shortcodes/mpc_row/mpc_row.php' );
require_once( MPC_MASSIVE_DIR . '/shortcodes/mpc_column/mpc_column.php' );