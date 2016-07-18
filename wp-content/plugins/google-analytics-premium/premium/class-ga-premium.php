<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Premium
 */
class Yoast_GA_Premium {

	/**
	 * Store the GA options
	 *
	 * @var array
	 */
	public static $ga_options = array();

	/**
	 * Initialize the premium functionality.
	 *
	 * This method will call specific method for initialising the admin or frontend functionality.
	 *
	 * It will even add the hook for default options.
	 */
	public static function init() {
		self::$ga_options = Yoast_GA_Options::instance()->get_options();

		if ( is_admin() ) {
			self::init_admin();
		}
		else {
			self::init_frontend();
		}

		add_action( 'plugins_loaded', array( 'Yoast_GA_Premium', 'load_textdomain' ) );
		add_filter( 'yst_ga_default-ga-values', array( 'Yoast_GA_Premium', 'add_default_options' ), 10, 2 );
	}

	/**
	 * Initialize the admin
	 *
	 * Adding action for the advanced tab and include the custom definition classes
	 */
	protected static function init_admin() {
		$advanced_presenter = new Yoast_GA_Advanced_Presenter();

		add_action( 'yst_ga_advanced-tab', array( $advanced_presenter, 'print_view' ) );

		add_action( 'init', array( 'Yoast_GA_Admin_Custom_Dimensions', 'init_assets' ) );
		add_action( 'yst_ga_custom_dimensions_tab-content', array( 'Yoast_GA_Admin_Custom_Dimensions', 'hook_custom_dimensions_page' ) );

		add_action( 'init', array( 'Yoast_GA_Premium', 'init_premium_dashboards' ) );
		register_deactivation_hook( 'wordpress-seo/wp-seo.php', array( 'Yoast_GA_Admin_Custom_Dimensions', 'wpseo_deactivate' ) );
		register_deactivation_hook( 'wordpress-seo-premium/wp-seo-premium.php', array( 'Yoast_GA_Admin_Custom_dimensions', 'wpseo_deactivate' ) );
		add_action( 'admin_notices', array( 'Yoast_GA_Admin_Custom_Dimensions', 'display_wpseo_deactivated_notices' ) );
		add_filter( 'yst_ga_admin_validate_settings', array( 'Yoast_GA_Admin_Custom_Dimensions', 'add_validation' ), 10, 2 );

		add_filter( 'yst-ga-filter-ga-config', array( 'Yoast_GA_Premium', 'set_ga_credentials' ) );

		add_action( 'admin_print_scripts', array( 'Yoast_GA_Premium', 'enqueue_premium_dashboard_script' ) );

		new MI_Product_GA_Premium();

	}

	/**
	 * Initialize the frontend
	 *
	 * Adding hook for getting
	 */
	protected static function init_frontend() {
		add_action( 'yst_tracking', array( 'Yoast_GA_Adsense', 'add_tracking_actions' ) );

		if ( self::$ga_options['enable_universal'] == 1 ) {
			add_filter( 'yoast-ga-push-array-universal', array( 'Yoast_GA_Frontend_Custom_Dimensions', 'hook_custom_dimensions' ) );
		}
	}

	/**
	 * Initiate the premium dashboards
	 */
	public static function init_premium_dashboards() {
		Yoast_GA_Dashboards_Premium::get_instance()->init_premium_dashboards();
	}

	/**
	 * Adding the vars to the options
	 *
	 * These options come from class-options, which is calling this action-hook. Specific premium options can be
	 * added within this method
	 *
	 * @param array  $options
	 * @param string $prefix
	 *
	 * @return array
	 */
	public static function add_default_options( $options, $prefix ) {
		$options[ $prefix ]['track_adsense']      = null;
		$options[ $prefix ]['custom_dimensions']  = array();
		$options[ $prefix ]['custom_metrics']     = array();

		return $options;
	}

	/**
	 * Overwrite the GA credentials by filter hook
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public static function set_ga_credentials( $config ) {
		return array(
			'application_name' => 'Google Analytics Yoast Premium',
			'client_id'        => '782108975374-j6frtnafilpgoi8d1rmek56o43n1772k.apps.googleusercontent.com',
			'client_secret'    => 'efSFIjavMFyJXMDeYvRQ6d60',
			'redirect_uri'     => 'urn:ietf:wg:oauth:2.0:oob',
			'scopes'           => array( 'https://www.googleapis.com/auth/analytics.readonly' ),
		);
	}

	/**
	 * Enqueue premium scripts
	 */
	public static function enqueue_premium_dashboard_script() {
		if ( filter_input( INPUT_GET, 'page' ) === 'yst_ga_dashboard' && current_user_can( 'manage_options' ) ) {

			wp_enqueue_script( 'yoast_ga_premium_dashboard', Yoast_GA_Admin_Assets::get_asset_path( 'premium/admin/js/ga_dashboards.min.js' ) );

			wp_localize_script( 'yoast_ga_premium_dashboard', 'yst_ga_premium', array(
				'yst_ga_loading'      => __( 'Loading', 'ga-premium' ),
				'yst_dashboard_nonce' => wp_create_nonce( 'yst_ga_dashboards_ajax_nonce' ),
			) );
		}
	}

	/**
	 * Ajax call: Fetch new data from Google Analytics
	 */
	public static function yst_ga_refresh_data() {
		check_ajax_referer( 'yst_ga_dashboards_ajax_nonce', 'security' );

		Yoast_GA_Dashboards::get_instance()->aggregator->aggregate_data();

		die( 'done' );
	}

	/**
	 * Show the dashboard refresh button for premium users
	 */
	public static function dashboard_refresh_button() {
		echo '<a class="button" id="yst_ga_refresh" style="padding-top: 2px; float: right;"><span class="dashicons dashicons-update"></span></a>';
	}

	/**
	 * Load premium textdomain
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'ga-premium', false, dirname( plugin_basename( GAWP_FILE ) ) . '/premium/languages/' );
	}
}