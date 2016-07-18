<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Adsense
 */
class Yoast_GA_Adsense {

	/**
	 * Adding the add-adsense-tracking hook to wp-head
	 *
	 * This method will hook into the wp_head to add some additional code.
	 */
	public static function add_tracking_actions() {
		add_action( 'wp_head', array( 'Yoast_GA_Adsense', 'add_adsense_tracking' ) );
	}

	/**
	 * Adding adsense tracking
	 *
	 * This method will add adsense tracking code into the head
	 */
	public static function add_adsense_tracking() {
		$tracking_code = Yoast_GA_Options::instance()->get_tracking_code();

		if ( ! empty( $tracking_code ) && Yoast_GA_Options::instance()->options['track_adsense'] == 1 ) {
			require_once dirname( GAWP_FILE ) . '/premium/frontend/pages/tracking-adsense.php';
		}
	}

}