<?php
/**
 * @package GoogleAnalytics\Premium
 */

if ( ! class_exists( 'Yoast_GA_Premium_Autoload' ) ) {

	/**
	 * Class Yoast_GA_Premium_Autoload
	 */
	class Yoast_GA_Premium_Autoload {

		/**
		 * @var null
		 */
		private static $classes = null;

		/**
		 * Setting the autoload
		 *
		 * If given $class is in array self::$class it will be included, if else there will be done nothing
		 *
		 * @param string $class
		 */
		public static function autoload( $class ) {

			$include_path = dirname( GAWP_FILE );

			if ( self::$classes === null ) {

				self::$classes = array(
					'yoast_ga_adsense'                    => 'premium/class-ga-adsense',
					'yoast_ga_premium'                    => 'premium/class-ga-premium',

					// Custom Dimensions
					'yoast_ga_custom_definitions'         => 'premium/class-ga-custom-definitions',
					'yoast_ga_custom_dimensions'          => 'premium/class-ga-custom-dimensions',
					'yoast_ga_admin_custom_dimensions'    => 'premium/admin/classes/class-ga-admin-custom-dimensions',
					'yoast_ga_frontend_custom_dimensions' => 'premium/frontend/classes/class-ga-frontend-custom-dimensions',

					// Dashboards for GA Premium
					'yoast_ga_dashboards_premium'         => 'premium/admin/dashboards/class-admin-dashboards-premium',

					// License manager
					'mi_product_ga_premium'            => 'premium/admin/classes/class-product-ga-premium',

					// Presenters
					'yoast_ga_presenter'                  => 'premium/admin/classes/class-ga-presenter',
					'yoast_ga_advanced_presenter'         => 'premium/admin/classes/class-ga-advanced-presenter',
				);
			}

			$class_name = strtolower( $class );
			if ( ! class_exists( $class ) && isset( self::$classes[ $class_name ] ) ) {
				require_once( $include_path . '/' . self::$classes[ $class_name ] . '.php' );
			}
		}
	}

	// register class autoloader
	spl_autoload_register( array( 'Yoast_GA_Premium_Autoload', 'autoload' ) );

}
