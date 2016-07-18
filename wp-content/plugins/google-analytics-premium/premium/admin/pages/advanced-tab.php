<?php
/**
 * @package GoogleAnalytics\Premium
 */

/** translators: %1$s / %2$s: links to an article about setting up the code to track your AdSense sites in Analytics  on google.com */
$integration_required = sprintf( __( 'This requires integration of your Analytics and AdSense account. For how to do this, see %1$sthis help page%2$s.', 'ga-premium' ), '<a href="https://support.google.com/adsense/answer/94743?ref_topic=23415&hl=' . get_locale() . '" target="_blank">', '</a>' );

// Showing checkbox for enabling/disabling adsense tracking
echo Yoast_GA_Admin_Form::input(
	'checkbox',
	__( 'Google Adsense tracking', 'ga-premium' ),
	'track_adsense',
	null,
	$integration_required
);

?>