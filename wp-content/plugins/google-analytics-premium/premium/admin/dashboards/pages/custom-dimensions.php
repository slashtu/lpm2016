<?php
/**
 * @package GoogleAnalytics\Premium
 */

if ( $has_enabled_dimensions === true ) {
?>
	<div class="ga-form ga-form-input">
		<label class="ga-form ga-form-checkbox-label ga-form-label-left"><?php echo __( 'Select a custom dimension', 'ga-premium' ); ?></label>
	</div>
	<select data-rel='toggle_dimensions' id="toggle_custom_dimensions" style="width: 350px"></select>

<?php
	Yoast_GA_Dashboards_Display::get_instance()->display( 'customdimensions' );
}
else {
	echo sprintf(
		__( 'You have not added any custom dimensions yet. %sAdd them here%s to enable this feature.','ga-premium' ),
		'<a href=" ' .admin_url( 'admin.php?page=yst_ga_settings#top#customdimensions' ) . '">',
		'</a>'
	);
}
?>