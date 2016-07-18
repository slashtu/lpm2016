<?php
/*
Plugin Name: Massive Addons - Uber Visual Composer Extension
Plugin URI: http://themeforest.net/user/mpc/
Description: Uber Visual Composer Extension for Visual Composer plugin.
Author: MassivePixelCreation
Version: 1.3.6
Author URI: http://themeforest.net/user/mpc/
Text Domain: mpc
Domain Path: /languages/
*/

/* Constants */
define( 'MPC_MASSIVE_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
define( 'MPC_MASSIVE_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'MPC_MASSIVE_VERSION', '1.3.6' );
define( 'MPC_MASSIVE_MIN', defined( 'MPC_DEBUG' ) && MPC_DEBUG == true ? '' : '.min' );

/* Globals */
global $mpc_massive_styles;
global $mpc_js_localization;
global $mpc_icons_fonts;
global $mpc_typography_presets;
global $mpc_navigation_presets;
global $mpc_pagination_presets;
global $mpc_frontend;
global $mpc_ma_options;
global $MPC_Shortcode;
global $mpc_can_link;

$mpc_massive_styles     = '';
$mpc_js_localization    = array();
$mpc_icons_fonts        = array();
$mpc_typography_presets = array();
$mpc_navigation_presets = array();
$mpc_pagination_presets = array();
$mpc_frontend           = ( isset( $_GET[ 'vc_editable' ] ) && $_GET[ 'vc_editable' ] == 'true' ) || ( isset( $_POST[ 'action' ] ) && isset( $_POST[ 'vc_inline' ] ) && $_POST[ 'action' ] == 'vc_load_shortcode' && $_POST[ 'vc_inline' ] == 'true' );
$mpc_ma_options         = get_option( 'mpc_ma_options' );
$MPC_Shortcode          = array();
$mpc_can_link           = true;

/*----------------------------------------------------------------------------*\
	SETUP
\*----------------------------------------------------------------------------*/
/* Setup Wizard */
register_activation_hook( __FILE__, 'mpc_setup_wizard' );
function mpc_setup_wizard() {
	set_transient( 'mpc_setup_wizard', true );
}

/* Visual Composer check */
add_action( 'plugins_loaded', 'mpc_check_vc' );
function mpc_check_vc() {
	if ( defined( 'WPB_VC_VERSION' ) ) {
		if ( version_compare( WPB_VC_VERSION, '4.7', '<' ) ) {
			add_action( 'admin_notices', 'mpc_vc_outdated' );
			return;
		}

		define( 'MPC_MASSIVE_FALLBACK', ! function_exists( 'vc_lean_map' ) );

		$mpc_ma_version = get_option( 'mpc_ma_version' );
		if ( ! $mpc_ma_version || version_compare( MPC_MASSIVE_VERSION, $mpc_ma_version, '>' ) ) {
			update_option( 'mpc_ma_version', MPC_MASSIVE_VERSION );

			set_transient( 'mpc_setup_wizard', true );
		}

		/* Add panel settings */
		require_once( MPC_MASSIVE_DIR . '/panel/panel.php' );

		/* Add welcome screen */
		if ( get_transient( 'mpc_setup_wizard' ) ) {
			set_transient( 'mpc_setup_wizard', false );

			wp_redirect( admin_url( 'admin.php?page=ma-setup' ) );
		}
		if ( ! empty( $_GET['page'] ) && $_GET[ 'page' ] == 'ma-setup' ) {
			require_once( MPC_MASSIVE_DIR . '/panel/extra/welcome-screen.php' );
		}

		/* Add system info */
		require_once( MPC_MASSIVE_DIR . '/panel/extra/system-info.php' );

		/* Add params */
		require_once( MPC_MASSIVE_DIR . '/params/params.php' );

		/* Add shortcodes */
		require_once( MPC_MASSIVE_DIR . '/shortcodes/shortcodes.php' );
	} else {
		add_action( 'admin_notices', 'mpc_vc_disabled' );
	}
}

function mpc_vc_disabled() {
	echo '<div class="notice notice-error"><p>' . __( '<strong>Massive Addons</strong>: Please install and activate Visual Composer to use this plugin', 'mpc' ) . '</p></div>';
}

function mpc_vc_outdated() {
	echo '<div class="notice notice-warning"><p>' . __( '<strong>Massive Addons</strong>: Please update Visual Composer to the newest version to use this plugin', 'mpc' ) . '</p></div>';
}

/* Localization */
add_action( 'plugins_loaded', 'mpc_ma_localization' );
function mpc_ma_localization() {
	load_plugin_textdomain( 'mpc', FALSE, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}

/*----------------------------------------------------------------------------*\
	LOAD ASSETS
\*----------------------------------------------------------------------------*/
add_filter( 'admin_body_class', 'add_admin_body_classes', 1000 );
function add_admin_body_classes( $classes ) {
	global $pagenow, $mpc_ma_options;

	if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {
		if ( isset( $mpc_ma_options[ 'easy_mode' ] ) && $mpc_ma_options[ 'easy_mode' ] === '1' ) {
			return "$classes mpc-easy-mode mpc-easy-mode-enabled";
		}
	}

	return $classes;
}

/* Register VC Custom Views */
add_action( 'vc_backend_editor_render', 'mpc_vc_custom_views_enqueue' );
function mpc_vc_custom_views_enqueue() {
	wp_enqueue_script( 'mpc-vc-custom-views-script', MPC_MASSIVE_URL . '/assets/js/mpc-vc-custom-views.js', array( 'vc-backend-min-js' ), MPC_MASSIVE_VERSION, true );
}

/* Back styles/scripts enqueue */
add_action( 'load-post.php', 'mpc_backend_enqueue' );
add_action( 'load-post-new.php', 'mpc_backend_enqueue' );
add_action( 'load-toplevel_page_MassiveAddons', 'mpc_backend_enqueue' );
add_action( 'vc_frontend_editor_enqueue_js_css', 'mpc_backend_enqueue' );
function mpc_backend_enqueue() {
	wp_enqueue_style( 'mpc-massive-admin-style', MPC_MASSIVE_URL . '/assets/css/mpc-styles-admin.css' );

	wp_enqueue_script( 'mpc-massive-vendor-script', MPC_MASSIVE_URL . '/assets/js/mpc-vendor.min.js', '', MPC_MASSIVE_VERSION, true );

	wp_enqueue_script( 'mpc-massive-admin-script', MPC_MASSIVE_URL . '/assets/js/mpc-scripts-admin.js', array( 'jquery-ui-slider', 'jquery-ui-dialog', 'mpc-massive-vendor-script' ), MPC_MASSIVE_VERSION, true );

	wp_enqueue_script( 'mpc-admin-script', MPC_MASSIVE_URL . '/assets/js/mpc-admin.min.js', array( 'wp-color-picker' ), MPC_MASSIVE_VERSION, true );
	wp_enqueue_script( 'jquery-ui-slider' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}

add_action( 'vc_frontend_editor_enqueue_js_css', 'mpc_backend_editor_enqueue' );
function mpc_backend_editor_enqueue() {
	global $mpc_ma_options;

	/* Carousels */
	wp_enqueue_style( 'mpc-massive-slick-css', MPC_MASSIVE_URL . '/assets/css/libs/slick.min.css' );
	wp_enqueue_script( 'mpc-massive-slick-js', MPC_MASSIVE_URL . '/assets/js/libs/slick.min.js', array( 'jquery' ), '', true );

	/* Countdown */
	wp_enqueue_style( 'mpc-massive-countdown-css', MPC_MASSIVE_URL . '/assets/css/libs/jquery.countdown.min.css' );
	wp_enqueue_script( 'mpc-massive-countdown-base-js', MPC_MASSIVE_URL . '/assets/js/libs/jquery.countdown.base.min.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'mpc-massive-countdown-js', MPC_MASSIVE_URL . '/assets/js/libs/jquery.countdown.min.js', array( 'jquery', 'mpc-massive-countdown-base-js' ), '', true );

	/* Counter */
	wp_enqueue_script( 'mpc-massive-countto-js', MPC_MASSIVE_URL . '/assets/js/libs/countTo.min.js', array( 'jquery' ), '', true );

	/* Isotope */
	wp_enqueue_script( 'mpc-massive-isotope-js', MPC_MASSIVE_URL . '/assets/js/libs/isotope.min.js', array( 'jquery' ), '', true );

	/* QR Code */
	wp_enqueue_script( 'mpc-massive-qr-js', MPC_MASSIVE_URL . '/assets/js/libs/qrcode.min.js', array(), '', true );

	/* Magnific Popup */
	if( $mpc_ma_options[ 'magnific_popup' ] == '1' ) {
		wp_enqueue_style( 'mpc-massive-magnific-popup-css', MPC_MASSIVE_URL . '/assets/css/libs/magnific-popup.min.css' );
		wp_enqueue_script( 'mpc-massive-magnific-popup-js', MPC_MASSIVE_URL . '/assets/js/libs/magnific-popup.min.js', array( 'jquery' ), '', true );
	}

	wp_enqueue_style( 'mpc-massive-style', MPC_MASSIVE_URL . '/assets/css/mpc-styles.css' );
	wp_enqueue_script( 'mpc-massive-vendor-script', MPC_MASSIVE_URL . '/assets/js/mpc-vendor.min.js', '', MPC_MASSIVE_VERSION, true );
	wp_enqueue_script( 'mpc-massive-script', MPC_MASSIVE_URL . '/assets/js/mpc-scripts' . MPC_MASSIVE_MIN . '.js', array( 'mpc-massive-vendor-script' ), MPC_MASSIVE_VERSION, true );

	wp_localize_script( 'mpc-massive-vendor-script', '_mpc_ajax', admin_url( 'admin-ajax.php' ) );
	wp_localize_script( 'mpc-massive-vendor-script', '_mpc_animations', $mpc_ma_options[ 'animations_on_mobile' ] );
}

add_action( 'vc_inline_editor_page_view', 'mpc_frontend_editor_enqueue' );
function mpc_frontend_editor_enqueue() {
	wp_enqueue_style( 'mpc-massive-admin-style', MPC_MASSIVE_URL . '/assets/css/mpc-styles-admin.css' );

	wp_enqueue_script( 'mpc-massive-frontend-script', MPC_MASSIVE_URL . '/assets/js/mpc-frontend-editor.js', array( 'jquery-ui-slider', 'jquery-ui-dialog' ), MPC_MASSIVE_VERSION, true );

	$mpc_frontend = array(
		'path'    => MPC_MASSIVE_URL,
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	);

	wp_localize_script( 'mpc-massive-frontend-script', '_mpc_frontend', $mpc_frontend );
}

/* Front styles/scripts enqueue */
add_action( 'wp_enqueue_scripts', 'mpc_frontend_enqueue', 100 );
function mpc_frontend_enqueue() {
	global $mpc_ma_options;

	/* Magnific Popup */
	if( $mpc_ma_options[ 'magnific_popup' ] == '1' ) {
		wp_register_style( 'mpc-massive-magnific-popup-css', MPC_MASSIVE_URL . '/assets/css/libs/magnific-popup.min.css' );
		wp_register_script( 'mpc-massive-magnific-popup-js', MPC_MASSIVE_URL . '/assets/js/libs/magnific-popup.min.js', array( 'jquery' ), '', true );
	}

	if ( $mpc_ma_options[ 'single_js_css' ] == '1' ) {
		wp_enqueue_style( 'mpc-massive-style', MPC_MASSIVE_URL . '/assets/css/mpc-styles.css' );
		wp_enqueue_script( 'mpc-massive-vendor-script', MPC_MASSIVE_URL . '/assets/js/mpc-vendor.min.js', '', MPC_MASSIVE_VERSION, true );
		wp_enqueue_script( 'mpc-massive-script', MPC_MASSIVE_URL . '/assets/js/mpc-scripts' . MPC_MASSIVE_MIN . '.js', array( 'mpc-massive-vendor-script' ), MPC_MASSIVE_VERSION, true );
	} else {
		wp_enqueue_style( 'mpc-massive-main-style', MPC_MASSIVE_URL . '/assets/css/mpc-main' . MPC_MASSIVE_MIN . '.css' );
		wp_enqueue_script( 'mpc-massive-vendor-script', MPC_MASSIVE_URL . '/assets/js/mpc-vendor.min.js', '', MPC_MASSIVE_VERSION, true );
		wp_enqueue_script( 'mpc-massive-main-script', MPC_MASSIVE_URL . '/assets/js/mpc-main' . MPC_MASSIVE_MIN . '.js', array( 'mpc-massive-vendor-script' ), MPC_MASSIVE_VERSION, true );
	}

	wp_localize_script( 'mpc-massive-vendor-script', '_mpc_ajax', admin_url( 'admin-ajax.php' ) );
	wp_localize_script( 'mpc-massive-vendor-script', '_mpc_animations', $mpc_ma_options[ 'animations_on_mobile' ] );
}

/*----------------------------------------------------------------------------*\
	NAVIGATION PRESETS
\*----------------------------------------------------------------------------*/
add_action( 'wp_footer', 'mpc_print_navigation_presets' );
function mpc_print_navigation_presets() {
	global $mpc_navigation_presets, $MPC_Navigation;

	if ( ! $mpc_navigation_presets ) {
		return;
	}

	$defaults = $MPC_Navigation->defaults;

	foreach ( $mpc_navigation_presets as $name => $values ) {
		$values = shortcode_atts( $defaults, $values );

		$style = $MPC_Navigation->shortcode_styles( $values, $name );

		echo '<style id="mpc-nav-preset--' . $name . '">' . $style . '</style>';
	}
}

add_action( 'wp_ajax_mpc_get_navigation', 'mpc_get_navigation_callback' );
function mpc_get_navigation_callback() {
	if ( isset( $_POST[ 'navigation' ] ) ) {
		global $mpc_navigation_presets;

		$navigation_presets = get_option( 'mpc_presets_mpc_navigation' );
		$navigation_presets = json_decode( $navigation_presets, true );

		if ( isset( $navigation_presets[ $_POST[ 'navigation' ] ] ) ) {
			$mpc_navigation_presets[ $_POST[ 'navigation' ] ] = $navigation_presets[ $_POST[ 'navigation' ] ];

			mpc_print_navigation_presets();
		}
	} else {
		die( 'error' );
	}

	die();
}

/*----------------------------------------------------------------------------*\
	PAGINATION PRESETS
\*----------------------------------------------------------------------------*/
add_action( 'wp_footer', 'mpc_print_pagination_presets' );
function mpc_print_pagination_presets() {
	global $mpc_pagination_presets, $MPC_Pagination;

	if( ! $mpc_pagination_presets ) {
		return;
	}

	$defaults = $MPC_Pagination->defaults;

	foreach ( $mpc_pagination_presets as $name => $values ) {
		$values = shortcode_atts( $defaults, $values );

		$style = $MPC_Pagination->shortcode_styles( $values, $name );

		echo '<style id="mpc-pagination-preset--' . $name . '">' . $style . '</style>';
	}
}

add_action( 'wp_ajax_mpc_get_pagination', 'mpc_get_pagination_callback' );
function mpc_get_pagination_callback() {
	if ( isset( $_POST[ 'pagination' ] ) ) {
		global $mpc_pagination_presets;

		$pagination_presets = get_option( 'mpc_presets_mpc_pagination' );
		$pagination_presets = json_decode( $pagination_presets, true );

		if ( isset( $pagination_presets[ $_POST[ 'pagination' ] ] ) ) {
			$mpc_pagination_presets[ $_POST[ 'pagination' ] ] = $pagination_presets[ $_POST[ 'pagination' ] ];

			mpc_print_pagination_presets();
		}
	} else {
		die( 'error' );
	}

	die();
}

/*----------------------------------------------------------------------------*\
	FONTS PRESETS
\*----------------------------------------------------------------------------*/
add_action( 'wp_footer', 'mpc_print_typography_presets' );
function mpc_print_typography_presets() {
	global $mpc_typography_presets;

	$typography_presets = get_option( 'mpc_presets_typography' );
	$typography_presets = json_decode( $typography_presets, true );
	$used_fonts = array();

	foreach( $mpc_typography_presets as $name ) {
		if( is_array( $typography_presets ) && ! array_key_exists( $name, $typography_presets ) ) {
			continue;
		}

		$atts = array_merge( array(
			'color' => '',
			'font-family' => '',
			'font-size' => '',
			'font-style' => '',
			'font-weight' => '',
			'line-height' => '',
			'text-align' => '',
			'text-transform' => '',
		), $typography_presets[ $name ] );

		if ( strpos( $atts[ 'font-family' ], ',' ) !== false ) {
			$font_family = $atts[ 'font-family' ];
		} else {
			$font_family = '"' . $atts[ 'font-family' ] . '"';
		}

		$style = '.mpc-typography--' . $name . '{';
			$style .= $atts[ 'color' ] != '' ? 'color:' . $atts[ 'color' ] . ';' : '';
			$style .= $atts[ 'font-family' ] != '' ? 'font-family:' . $font_family . ' !important;' : '';
			$style .= $atts[ 'font-size' ] != '' ? 'font-size:' . $atts[ 'font-size' ] . 'px;' : '';
			$style .= $atts[ 'font-style' ] != '' ? 'font-style:' . $atts[ 'font-style' ] . ' !important;' : '';
			$style .= $atts[ 'font-weight' ] != '' ? 'font-weight:' . $atts[ 'font-weight' ] . ' !important;' : '';
			$style .= $atts[ 'line-height' ] != '' ? 'line-height:' . $atts[ 'line-height' ] . ';' : '';
			$style .= $atts[ 'text-align' ] != '' ? 'text-align:' . $atts[ 'text-align' ] . ';' : '';
			$style .= $atts[ 'text-transform' ] != '' ? 'text-transform:' . $atts[ 'text-transform' ] . ';' : '';
		$style .= '}';

		if ( $atts[ 'font-family' ] != '' ) {
			if ( ! isset( $used_fonts[ $atts[ 'font-family' ] ] ) ) {
				$used_fonts[ $atts[ 'font-family' ] ] = array(
					'subsets' => array(),
					'variants' => array(),
				);
			}

			if ( $atts[ 'subset' ] != '' && array_search( $atts[ 'subset' ], $used_fonts[ $atts[ 'font-family' ] ][ 'subsets' ] ) === false ) {
				$used_fonts[ $atts[ 'font-family' ] ][ 'subsets' ][] = $atts[ 'subset' ];
			}
			if ( $atts[ 'style' ] != '' && array_search( $atts[ 'style' ], $used_fonts[ $atts[ 'font-family' ] ][ 'variants' ] ) === false ) {
				$used_fonts[ $atts[ 'font-family' ] ][ 'variants' ][] = $atts[ 'style' ];
			}
		}

		echo '<style id="mpc-typography--' . $name . '">' . $style . '</style>';
	}

	mpc_print_typography_presets_links( $used_fonts );
}

function mpc_print_typography_presets_links( $used_fonts ) {
	$protocol = is_ssl() ? 'https' : 'http';
	$fonts    = array();
	$subsets  = array();

	$link = $protocol;
	$link .= '://fonts.googleapis.com/css?family=';

	foreach( $used_fonts as $name => $values ) {
		$font = str_replace( ' ', '+', $name );

		if ( ! empty( $values[ 'variants' ] ) ) {
			$font .= ':' . implode( ',', $values[ 'variants' ] );
		}
		if ( ! empty( $values[ 'subsets' ] ) ) {
			$subsets = array_merge( $subsets, $values[ 'subsets' ] );
		}

		$fonts[] = $font;
	}

	$link .= implode( '|', $fonts );

	if ( ! empty( $subsets ) ) {
		$subsets = array_unique( $subsets );
		$link .= '&' . implode( ',', $subsets );
	}

	if ( count( $used_fonts ) > 0 ) {
		wp_enqueue_style( 'mpc-typography-presets', $link );
	}

	if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'mpc_get_typography' ) {
		echo '<link rel="stylesheet" href="' . $link . '" type="text/css" media="all">';
	}
}

add_action( 'wp_ajax_mpc_get_typography', 'mpc_get_typography_callback' );
function mpc_get_typography_callback() {
	if ( isset( $_POST[ 'typography' ] ) ) {
		global $mpc_typography_presets;

		$mpc_typography_presets[] = $_POST[ 'typography' ];

		mpc_print_typography_presets();
	} else {
		die( 'error' );
	}

	die();
}

/*----------------------------------------------------------------------------*\
	ICON FONTS ENQUEUE
\*----------------------------------------------------------------------------*/
add_action( 'wp_footer', 'mpc_load_icons_fonts' );
function mpc_load_icons_fonts() {
	global $mpc_icons_fonts;

	foreach ( $mpc_icons_fonts as $font => $state ) {
		if ( $state ) {
			if ( $font == 'dashicons' ) {
				wp_enqueue_style( 'dashicons' );
			} else {
				wp_enqueue_style( 'mpc_icons_font-' . $font, MPC_MASSIVE_URL . '/assets/fonts/' . $font . '/' . $font . '.css', array(), MPC_MASSIVE_VERSION );
			}
		}
	}
}

/*----------------------------------------------------------------------------*\
	HELPERS
\*----------------------------------------------------------------------------*/

/* Sort style presets by names */
function mpc_sort_presets( $one, $two ) {
	return strnatcmp( isset( $one[ '__name' ] ) ? $one[ '__name' ] : '', isset( $two[ '__name' ] ) ? $two[ '__name' ] : '' );
}

/* Order style presets by groups */
function mpc_after_sort_presets( $presets ) {
	if ( isset( $presets[ 'default' ] ) ) {
		$default = array( 'default' => $presets[ 'default' ] );
		unset( $presets[ 'default' ] );
	}

	$user_presets    = array_intersect_key( $presets, array_flip( preg_grep( '/^preset_/', array_keys( $presets ) ) ) );
	$premade_presets = array_diff_key( $presets, $user_presets );

	if ( isset( $default ) ) {
		return $default + $user_presets + $premade_presets;
	} else {
		return $user_presets + $premade_presets;
	}
}

/* Print localization strings for JS */
add_action( 'admin_print_scripts-post.php', 'mpc_js_localization' );
add_action( 'admin_print_scripts-post-new.php', 'mpc_js_localization' );
add_action( 'admin_print_scripts-toplevel_page_MassiveAddons', 'mpc_js_localization' );
function mpc_js_localization() {
	global $mpc_js_localization;

	$mpc_js_localization[ 'more' ] = __( 'More', 'mpc' );
	$mpc_js_localization[ 'easy_mode' ] = __( 'Easy Mode', 'mpc' );

	echo '<script>var _mpc_lang = ' . json_encode( $mpc_js_localization ) . ';</script>';
}

/* Get panel option */
if ( ! function_exists( 'mpc_get_option' ) ) {
	function mpc_get_option( $name = '', $default = false ) {
		global $mpc_massive;

		if ( ! isset( $mpc_massive ) )
			return $default;

		if ( $name === '' )
			return $default;

		if ( isset( $mpc_massive[ $name ] ) )
			return $mpc_massive[ $name ];
		else
			return $default;
	}
}

/* Print shortcode styles */
add_action( 'wp_footer', 'mpc_footer_shortcode_css' );
function mpc_footer_shortcode_css() {
	global $mpc_massive_styles;

	if ( $mpc_massive_styles != '' ) {
		echo '<style data-id="mpc-massive-styles">' . $mpc_massive_styles . '</style>';
	}
}
