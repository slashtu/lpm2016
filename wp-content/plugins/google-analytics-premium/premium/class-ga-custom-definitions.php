<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Custom_Definitions
 */
class Yoast_GA_Custom_Definitions {

	/**
	 * Property for holding instance of itself
	 *
	 * @var __CLASS__
	 */
	protected static $instance;

	/**
	 * Fetches the Singleton object
	 *
	 * @param string $class_name
	 *
	 * @return Yoast_GA_Admin_Custom_Dimensions
	 */
	public static function get_instance( $class_name = __CLASS__ ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new $class_name();
		}

		return self::$instance;
	}

	/**
	 * Fetches the options
	 */
	protected function get_options() {
		return Yoast_GA_Options::instance()->get_options();
	}

	/**
	 * Fetches an option from the options
	 *
	 * @param string $key     Used for the option to be fetched
	 * @param Mixed  $default Default return value
	 *
	 * @return Mixed
	 */
	public function get_option( $key, $default = null ) {
		$options = $this->get_options();

		return ( array_key_exists( $key, $options ) ? $options[ $key ] : $default );
	}

	/**
	 * Wrapper to enable stubbing in tests.
	 */
	protected function wp_seo_active() {
		return Yoast_GA_Utils::wp_seo_active();
	}
}