jQuery(document).ready(function () {
	jQuery('#yst_ga_refresh').on('click', function () {
		jQuery(this).html(yst_ga_premium.yst_ga_loading + '...');
		jQuery(this).attr('disabled', 'disabled');
		jQuery(this).css('padding-top', '0');

		jQuery.post(
			ajaxurl,
			{
				action  : 'yst_ga_refresh_data',
				security: yst_ga_premium.yst_dashboard_nonce
			},
			function (response) {
				location.reload();
			}
		);
	});
});