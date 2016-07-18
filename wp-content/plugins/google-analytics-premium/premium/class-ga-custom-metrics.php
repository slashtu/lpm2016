<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Custom_Metrics
 */
abstract class Yoast_GA_Custom_Metrics extends Yoast_GA_Custom_Definitions {

	/**
	 * @var array Contains the active custom dimensions as they were saved to the database.
	 */
	protected $active_custom_metrics = array();

	/**
	 * Overrides instance to set with this class as class
	 *
	 * @param string $class_name
	 *
	 * @return Yoast_GA_Custom_Definitions
	 */
	public static function get_instance( $class_name = __CLASS__ ) {
		return parent::get_instance( $class_name );
	}

	/**
	 * Sets the active custom dimensions so they can be used in the child classes.
	 */
	protected function __construct() {
		$this->set_active_custom_metrics();
	}

	/**
	 * Fetches the active custom dimensions and assigns them to the active_custom_dimensions property
	 */
	protected function set_active_custom_metrics() {
		$this->active_custom_metrics = $this->get_option( 'custom_metrics', array() );
	}
}