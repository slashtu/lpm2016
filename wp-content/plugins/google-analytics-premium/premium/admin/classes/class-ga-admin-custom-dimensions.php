<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Admin_Custom_Dimensions
 */
class Yoast_GA_Admin_Custom_Dimensions extends Yoast_GA_Custom_Dimensions {
	/**
	 * @var array Contains all the custom dimensions by type, with parameters 'title', 'active' and 'enabled'.
	 */
	protected $custom_dimensions;

	/**
	 * @var int The amount of custom dimensions currently active and enabled (used in the custom dimensions view).
	 */
	protected $custom_dimensions_usage;

	/**
	 * @var int The maximum amount of custom dimensions that could be active (used in the custom dimensions view).
	 */
	protected $custom_dimensions_limit;

	/**
	 * @var array
	 */
	protected $active_custom_dimensions_types;

	/**
	 * @var boolean GA universal mode enabled
	 */
	protected $universal_enabled;

	/**
	 * @var array The seo dimension types
	 */
	protected $seo_dimension_types = array( 'focus_keyword', 'seo_score' );

	/**
	 *
	 * Overrides instance to set with this class as class
	 *
	 * @param string $class_name
	 *
	 * @return Yoast_GA_Custom_Dimensions
	 */
	public static function get_instance( $class_name = __CLASS__ ) {
		return parent::get_instance( $class_name );
	}

	/**
	 * enqueues the assets
	 */
	public static function init_assets() {
		if ( 'yst_ga_settings' === filter_input( INPUT_GET, 'page' ) ) {
			wp_enqueue_script( 'custom_dimensions', GAWP_URL . 'premium/admin/js/custom_dimensions.min.js' );
			wp_enqueue_style( 'yoast_ga_premium_styles', GAWP_URL . 'premium/admin/css/yoast_ga_premium_styles.min.css' );
		}
	}

	/**
	 * Hook used for rendering the the custom dimensions tab in the admin settings.
	 */
	public static function hook_custom_dimensions_page() {
		self::get_instance()->render_custom_dimensions_page();
	}

	/**
	 * Hook used for preparing a notice when WPSEO is deactivated and SEO dimensions have been set.
	 */
	public static function wpseo_deactivate() {
		if ( self::get_instance()->seo_dimensions_active() ) {
			$error_message = sprintf(
				__( '%1$sWarning!%2$s Deactivating Wordpress SEO will stop your SEO custom dimensions from working in Google Analytics. Please visit your %3$sGoogle Analytics settings%4$s to see which custom dimensions have been disabled.', 'ga-premium' ),
				'<strong>',
				'</strong>',
				'<a href="' . admin_url( 'admin.php' ) . '?page=yst_ga_settings#top#customdimensions">',
				'</a>'
			);

			set_transient( 'wpseo_deactivated_error', $error_message, MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Hook used for outputting an admin notice when transient has been set on deactivation of WPSEO.
	 */
	public static function display_wpseo_deactivated_notices() {
		$wpseo_deactivated_error = get_transient( 'wpseo_deactivated_error' );

		if ( ! empty( $wpseo_deactivated_error ) ) {
			echo '<div class="error"><p>' . $wpseo_deactivated_error . '</p></div>';
			delete_transient( 'wpseo_deactivated_error' );
		}
	}

	/**
	 * Responsible for rendering the custom dimensions tab
	 */
	public function render_custom_dimensions_page() {
		$this->set_rendering_properties();

		global $yoast_ga_admin;

		require_once dirname( GAWP_FILE ) . '/premium/admin/pages/custom-dimensions-page.php';
	}

	/**
	 * The current supported custom dimensions types (Key name is the matching name for the functions). The metric
	 * is a setting for this specific custom dimension. The metric is used to fetch data with this custom dimension.
	 *
	 * @return array
	 */
	public function custom_dimensions() {
		return array(
			'logged_in'     => array(
				'title'   => __( 'Logged in', 'ga-premium' ),
				'help'    => sprintf(
					__( 'The amount of views on posts and pages by logged in users vs non logged in users. %1$s[Learn more]%2$s', 'ga-premium' ),
					'<a href="https://yoast.com/custom-dimensions-sequel/#utm_medium=custom-dimensions&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank">',
					'</a>'
				),
				'label'   => __( 'Number of logged in sessions', 'ga-premium' ),
				'enabled' => true,
				'metric'  => 'sessions',
			),
			'post_type'     => array(
				'title'   => __( 'Post type', 'ga-premium' ),
				'help'    => sprintf(
					__( 'The amount of views on posts and pages grouped by post type. %1$s[Learn more]%2$s', 'ga-premium' ),
					'<a href="https://yoast.com/custom-dimensions-sequel/#utm_medium=custom-dimensions&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank">',
					'</a>'
				),
				'label'   => __( 'Most popular post types', 'ga-premium' ),
				'enabled' => true,
				'metric'  => 'sessions',
			),
			'author'        => array(
				'title'   => __( 'Author', 'ga-premium' ),
				'help'    => sprintf(
					__( 'The amount of views on posts and pages grouped by author. %1$s[Learn more]%2$s', 'ga-premium' ),
					'<a href="https://yoast.com/google-analytics-custom-dimensions/#utm_medium=custom-dimensions&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank">',
					'</a>'
				),
				'label'   => __( 'Most popular authors', 'ga-premium' ),
				'enabled' => true,
				'metric'  => 'sessions',
			),
			'category'      => array(
				'title'   => __( 'Category', 'ga-premium' ),
				'help'    => sprintf(
					__( 'The amount of views on posts and pages grouped by category. %1$s[Learn more]%2$s', 'ga-premium' ),
					'<a href="https://yoast.com/google-analytics-custom-dimensions/#utm_medium=custom-dimensions&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank">',
					'</a>'
				),
				'label'   => __( 'Most popular categories', 'ga-premium' ),
				'enabled' => true,
				'metric'  => 'sessions',
			),
			'published_at'  => array(
				'title'   => __( 'Published at', 'ga-premium' ),
				'help'    => sprintf(
					__( 'The amount of views on posts and pages grouped by their publication time. %1$s[Learn more]%2$s', 'ga-premium' ),
					'<a href="https://yoast.com/custom-dimensions-sequel/#utm_medium=custom-dimensions&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank">',
					'</a>'
				),
				'label'   => __( 'Best publication time', 'ga-premium' ),
				'enabled' => true,
				'metric'  => 'sessions',
			),
			'tags'          => array(
				'title'   => __( 'Tags', 'ga-premium' ),
				'help'    => __( 'The amount of views on posts and pages grouped by tags. <a href="http://en.support.wordpress.com/posts/tags/" target="_blank">[Learn more]</a>', 'ga-premium' ),
				'label'   => __( 'Most popular tags', 'ga-premium' ),
				'enabled' => true,
				'metric'  => 'sessions',
			),

			/*
                @todo: make sure this custom dimension get's added to the tracker object properly before sending out the event.
			'scroll_depth'  => array(
				'title'   => __( 'Scroll Depth', 'google-analytics-premium-for-wordpress' ),
				'help'    => sprintf(
					__( 'The amount of views on posts and pages grouped by scrolldepth. %1$s[Learn more]%2$s', 'google-analytics-premium-for-wordpress' ),
					'<a href="http://yoa.st/gascroll" target="_blank">',
					'</a>'
				),
				'label'   => __( 'Number of logged in sessions', 'google-analytics-premium-for-wordpress' ),
				'enabled' => true,
				'metric'  => 'totalEvents',
			),
			*/

			'seo_score'     => array(
				'title'   => __( 'SEO Score', 'ga-premium' ),
				'help'    => sprintf(
					__( 'The amount of views on posts and pages grouped by SEO score. %1$s[Learn more]%2$s', 'ga-premium' ),
					'<a href="https://yoast.com/wordpress/plugins/seo/#utm_medium=custom-dimensions&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank">',
					'</a>'
				),
				'label'   => __( 'Best SEO Score', 'ga-premium' ),
				'enabled' => $this->wp_seo_active(),
				'metric'  => 'sessions',
			),
			'focus_keyword' => array(
				'title'   => __( 'Focus Keyword', 'ga-premium' ),
				'help'    => sprintf(
					__( 'The amount of views on posts and pages grouped by focus keyword. %1$s[Learn more]%2$s', 'ga-premium' ),
					'<a href="https://yoast.com/focus-keyword/#utm_medium=custom-dimensions&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank">',
					'</a>'
				),
				'label'   => __( 'Most popular focus keywords', 'ga-premium' ),
				'enabled' => $this->wp_seo_active(),
				'metric'  => 'sessions',
			),
		);
	}

	/**
	 * Adds validation to the google analytics option saving logic
	 *
	 * @param true|WP_Error $validation
	 * @param array         $options
	 *
	 * @return true|WP_Error
	 */
	public static function add_validation( $validation, $options ) {

		if ( ! empty( $options['custom_dimensions'] ) ) {

			$custom_dimensions = (array) $options['custom_dimensions'];
			if ( ! self::dimension_ids_are_unique( $custom_dimensions ) ) {
				return new WP_Error(
					'custom-dimensions-duplicated',
					__( 'The custom dimension ID must be unique for each dimension.', 'ga-premium' )
				);
			}
		}

		return $validation;
	}

	/**
	 * Checks if the given dimensions all have a unique ID
	 *
	 * @param array $dimensions Dimensions to check.
	 *
	 * @return bool Whether or not the dimension IDs are unique.
	 */
	public static function dimension_ids_are_unique( $dimensions ) {
		$dimension_ids = wp_list_pluck( $dimensions, 'id' );

		return $dimension_ids === array_unique( $dimension_ids );
	}

	/**
	 * @return bool Checks if there are any active seo dimensions
	 */
	protected function seo_dimensions_active() {
		$active_seo_dimension_types = array_intersect( $this->seo_dimension_types, $this->active_custom_dimensions_types() );

		return ! empty( $active_seo_dimension_types );
	}

	/**
	 * Prepares a couple of properties to be used in the custom dimensions view
	 */
	protected function set_rendering_properties() {
		$this->universal_enabled              = ( $this->get_option( 'enable_universal' ) == 1 );
		$this->custom_dimensions              = $this->custom_dimensions();
		$this->active_custom_dimensions_types = $this->active_custom_dimensions_types();
		$this->custom_dimensions_usage        = count( $this->active_enabled_custom_dimensions() );
		$this->custom_dimensions_limit        = count( $this->enabled_custom_dimensions() );
	}

	/**
	 * Returns an array with custom dimensions that are both active and enabled.
	 *
	 * @return array
	 */
	private function active_enabled_custom_dimensions() {
		$active_enabled_custom_dimensions = array();

		foreach ( $this->enabled_custom_dimensions() as $key => $custom_dimension ) {
			if ( in_array( $key, $this->active_custom_dimensions_types ) ) {
				$active_enabled_custom_dimensions[ $key ] = $custom_dimension;
			}
		}

		return $active_enabled_custom_dimensions;
	}

	/**
	 * Returns an array with all enabled custom dimensions, both active and inactive.
	 *
	 * @return array
	 */
	private function enabled_custom_dimensions() {
		$enabled_custom_dimensions = array();

		foreach ( $this->custom_dimensions as $key => $custom_dimension ) {
			if ( $custom_dimension['enabled'] ) {
				$enabled_custom_dimensions[ $key ] = $custom_dimension;
			}
		}

		return $enabled_custom_dimensions;
	}

	/**
	 * Maps the types of the active custom dimensions to a separate array to be analyzed in $this->custom_dimensions()
	 *
	 * @return array
	 */
	private function active_custom_dimensions_types() {
		$active_custom_dimensions_types = array();

		foreach ( $this->active_custom_dimensions as $active_custom_dimension ) {
			$active_custom_dimensions_types[] = $active_custom_dimension['type'];
		}

		return $active_custom_dimensions_types;
	}

}