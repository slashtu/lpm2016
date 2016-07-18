<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Dashboards_Premium
 */
class Yoast_GA_Dashboards_Premium {

	/**
	 * Store this instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Get the instance
	 *
	 * @return Yoast_GA_Dashboards_Premium
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Init the premium dashboards in Google Analytics
	 */
	public function init_premium_dashboards() {
		add_action( 'admin_init', array( $this, 'register_custom_dimensions' ) );

		add_action( 'yst_ga_custom_dimension_add-dashboards-tab', array( $this, 'action_add_custom_dimension_dashboards_tab' ) );

		add_action( 'wp_ajax_yst_ga_refresh_data', array( $this, 'yst_ga_refresh_data' ) );
		add_filter( 'yst-ga-filter-api-end-date', array( $this, 'set_dashboards_end_date' ) );
		add_action( 'yst_ga_dashboard_title', array( $this, 'dashboard_refresh_button' ) );
		add_action( 'admin_notices', array( $this, 'check_for_message_refresh_dashboard' ) );
	}

	/**
	 * Register custom dimensions to dashboards
	 */
	public function register_custom_dimensions() {
		$dimensions = $this->get_active_enabled_custom_dimensions();

		// Register the dimensions
		$register = apply_filters( 'ga_dashboards_dimensions', $dimensions );

		// Register the dimensions in the dashboard
		$dashboards = apply_filters( 'ga_extend_dashboards', $this->get_active_dashboards_custom_dimensions( $dimensions ) );
	}

	/**
	 * Action for adding content to the custom dimensions tab
	 *
	 */
	public function action_add_custom_dimension_dashboards_tab() {
		$dimensions             = $this->get_active_enabled_custom_dimensions();
		$has_enabled_dimensions = ! empty( $dimensions );
		require_once( 'pages/custom-dimensions.php' );
	}


	/**
	 * Ajax call: Fetch new data from Google Analytics
	 */
	public function yst_ga_refresh_data() {
		check_ajax_referer( 'yst_ga_dashboards_ajax_nonce', 'security' );

		Yoast_GA_Dashboards::get_instance()->aggregator->aggregate_data();

		/**
		 * Add a notification
		 */
		set_transient( 'yst-ga-premium-ajax-refresh-message', true, ( MINUTE_IN_SECONDS * 5 ) );

		die( 'done' );
	}

	/**
	 * Set the end date in the dashboards on today. Default: yesterday
	 *
	 * @return string
	 */
	public function set_dashboards_end_date() {
		return date( 'Y-m-d' );
	}

	/**
	 * Show the dashboard refresh button for premium users
	 */
	public function dashboard_refresh_button() {
		echo '<a class="button" id="yst_ga_refresh" style="padding-top: 2px; float: right;" alt="' . __( 'Click to fetch the latest data from Google Analytics.', 'ga-premium' ) . '" title="' . __( 'Click to fetch the latest data from Google Analytics.', 'ga-premium' ) . '"><span class="dashicons dashicons-update"></span></a>';
	}

	/**
	 * Check if we need to show an admin message when we refreshed the dashboar data
	 */
	public function check_for_message_refresh_dashboard() {
		if ( get_transient( 'yst-ga-premium-ajax-refresh-message' ) !== false ) {
			echo '<div class="updated"><p>' . __( 'The Google Analytics dashboards data was updated successfully.', 'ga-premium' ) . '</p></div>';

			delete_transient( 'yst-ga-premium-ajax-refresh-message' );
		}
	}

	/**
	 * Get the custom dimensions for the dashboard
	 *
	 * @return array
	 */
	private function get_active_enabled_custom_dimensions() {
		$dimensions                = array();
		$custom_dimensions         = Yoast_GA_Admin_Custom_Dimensions::get_instance();
		$default_custom_dimensions = $custom_dimensions->custom_dimensions();
		$custom_dimension_option   = $custom_dimensions->get_option( 'custom_dimensions', array() );

		foreach ( $custom_dimension_option as $dimension ) {
			$dimensions[] = array(
				'id'      => $dimension['id'],
				'key'     => $dimension['type'],
				'name'    => $default_custom_dimensions[ $dimension['type'] ]['title'],
				'help'    => $default_custom_dimensions[ $dimension['type'] ]['help'],
				'label'   => $default_custom_dimensions[ $dimension['type'] ]['label'],
				'enabled' => $default_custom_dimensions[ $dimension['type'] ]['enabled'],
				'metric'  => $default_custom_dimensions[ $dimension['type'] ]['metric'],
			);
		}

		return $dimensions;
	}

	/**
	 * Get the $dashboards array to add them in the filter
	 *
	 * @param array $dimensions
	 * @param array $dashboards
	 *
	 * @return array
	 */
	private function get_active_dashboards_custom_dimensions( $dimensions, $dashboards = array() ) {
		foreach ( $dimensions as $dimension_single ) {
			$dashboards[ $dimension_single['key'] ] = array(
				'title'               => $dimension_single['name'],
				'help'                => $dimension_single['help'],
				'data-label'          => $dimension_single['label'],
				'type'                => 'table',
				'columns'             => array(
					__( 'Sessions', 'ga-premium' )
				),
				'tab'                 => 'customdimensions',
				'custom-dimension-id' => $dimension_single['id'],
				'metric'              => $dimension_single['metric'],
			);
		}

		return $dashboards;
	}

}