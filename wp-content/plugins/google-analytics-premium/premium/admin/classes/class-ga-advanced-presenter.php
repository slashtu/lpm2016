<?php
/**
 * @package GoogleAnalytics\Premium
 */

/**
 * Class Yoast_GA_Advanced_Presenter
 *
 * Display views for premium options in the advanced tab
 */
class Yoast_GA_Advanced_Presenter extends Yoast_GA_Presenter {

	/**
	 * Present the HTML that should be presented to the user.
	 *
	 * @return string
	 */
	protected function render_view() {
		return $this->render_adsense_tracking();
	}

	/**
	 * Get the render adsense option for the dashboard.
	 *
	 * @return string
	 */
	protected function render_adsense_tracking() {
		/** translators: %1$s / %2$s: links to an article about setting up the code to track your AdSense sites in Google Analytics on google.com */
		$adsense_help = sprintf( __( 'This requires integration of your Analytics and AdSense account, for help, %1$slook here%2$s.', 'ga-premium' ), '<a href="https://support.google.com/adsense/answer/94743?&ref_topic=23415?hl=' . get_locale() . '" target="_blank">', '</a>' );

		return Yoast_GA_Admin_Form::input( 'checkbox', __( 'Google Adsense tracking', 'ga-premium' ), 'track_adsense', null, $adsense_help );
	}


}

