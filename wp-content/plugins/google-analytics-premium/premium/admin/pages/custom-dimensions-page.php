<?php
/**
 * @package GoogleAnalytics\Premium
 */

?>
<h3><?php _e( 'Learn more', 'ga-premium' ) ?></h3>
<div id="ga-promote">
	<p class="ga-topdescription">
		<?php

		printf(
			__( 'Visit our knowledge base to learn more about %1$show to setup%3$s and %2$show to use%3$s custom dimensions in Google Analytics.', 'ga-premium' ),
			"<a href='https://www.monsterinsights.com/docs/how-do-i-set-up-custom-dimensions/#utm_medium=helptext&utm_source=gawp-config&utm_campaign=wpgaplugin' target='_blank'>",
			"<a href='https://www.monsterinsights.com/docs/can-find-custom-dimension-reports/#utm_medium=helptext&utm_source=gawp-config&utm_campaign=wpgaplugin' target='_blank'>",
			'</a>'
		);
		?>
		<br /><br />
		<?php
		printf(
			__( 'Read our %1$sblogposts on custom dimensions%2$s to learn more about what they are and how you could use them.', 'ga-premium' ),
			'<a href="https://yoast.com/tag/custom-dimensions/#utm_medium=helptext&utm_source=gawp-config&utm_campaign=wpgaplugin" target="_blank">',
			'</a>'
		);
		?>
	</p>
</div>

<div class="ga-form ga-form-input">
	<h3><?php _e( 'Add/remove custom dimensions', 'ga-premium' ) ?></h3>

	<table id="yoast-ga-form-label-table-settings-custom_dimensions" class="widefat fixed" width="100%">
		<thead>
		<th width="45%"><?php _e( 'Type', 'ga-premium' ); ?></th>
		<th width="45%"><?php _e( 'Custom Dimension ID', 'ga-premium' ); ?></th>
		<th width="10%">&nbsp;</th>
		</thead>
		<tbody>
		<?php $total = 1;
		$active_custom_dimension_ids  = array( 0 );
		foreach ( $this->active_custom_dimensions as $active_custom_dimension ) :
			$active_custom_dimension_ids[] = $active_custom_dimension['id'];
			?>
			<tr id="yst_ga-<?php echo $total; ?>">
				<?php $select_disabled = '';
				if ( ! $this->custom_dimensions[ $active_custom_dimension['type'] ]['enabled'] ) {
					$select_disabled = 'disabled';
				}

				?>
				<td>
					<?php
					if ( $select_disabled ) {
						echo '<input type="hidden" name="custom_dimensions[' . $total . '][type]" value="' . $active_custom_dimension['type'] . '">';
					}
					?>
					<select name="custom_dimensions[<?php echo $total; ?>][type]" <?php echo $select_disabled ?>>
						<?php foreach ( $this->custom_dimensions as $key => $dimension ) :
							$option_disabled = ( ( $dimension['enabled'] ) ? '' : 'disabled' );

							if ( $active_custom_dimension['type'] == $key ) {
								echo '<option value="' . $key . '" SELECTED >' . $dimension['title'] . '</option>';
							}
							else {
								echo '<option value="' . $key . '" ' . $option_disabled . '>' . $dimension['title'] . '</option>';
							}
						endforeach; ?>
					</select>
					<?php
					if ( $select_disabled ) {
						$enable_inactive_plugins_help = 'To use this custom dimension, please activate WordPress SEO or WordPress SEO Premium';
						echo '<span class="ga-premium-inactive-custom-dimension">Inactive<img src="' . plugins_url( 'assets/img/question-mark.png', GAWP_FILE ) . '" class="yoast_help" alt="' . esc_attr( __( $enable_inactive_plugins_help, 'ga-premium', 'ga-premium' ) ) . '" /></span>';
					}
					?>
				</td>
				<td align="left">
					<input type="text" name="custom_dimensions[<?php echo $total; ?>][id]" value="<?php echo $active_custom_dimension['id']; ?>" style="width: 50px;" />
				</td>
				<td>
					<a href="#top#customdimensions" id="yst-ga_remove_<?php echo $total; ?>"><?php _e( 'Delete', 'ga-premium' ); ?></a>
				</td>
			</tr>
			<?php $total ++; endforeach; ?>
		</tbody>
		<tfoot>
		<th colspan="1" id="yst_add_cd_holder">
			<strong><a href="#top#customdimensions" id="yst-ga_add_row">+ <?php _e( 'Add new custom dimension', 'ga-premium' ); ?></a>&nbsp;
			</strong></th>
		<th align="left" colspan="2">
			<?php
			/* translators %1$s shows the total number of used custom dimensions. %2$s shows the total number of custom dimensions available */
			echo '<em>' . sprintf( __( 'You are using %1$s out of %2$s custom dimensions.', 'ga-premium' ), '<span id="yst-ga_limit">' . $this->custom_dimensions_usage . '</span>', $this->custom_dimensions_limit ) . '</em>' ;
			?>
		</th>
		</tfoot>
	</table>

	<input type="hidden" name="string_error_custom_dimensions" id="string_error_custom_dimensions" value="<?php _e( 'The custom dimension ID already exists!', 'ga-premium' ); ?>" />
</div>

<?php
if ( $this->wp_seo_active() === false ) {
	?>
	<h3><?php _e( 'SEO dimensions', 'ga-premium' ) ?></h3>
	<div id="ga-wpseo-inactive">
		<p class="ga-topdescription">
			<?php

			printf(
				__( 'You need to install %1$sWordPress SEO by Yoast%2$s to be able to use the %3$sSEO Score%4$s and %3$sFocus keyword%4$s custom dimensions. If you\'re already running another SEO plugin, WordPress SEO can import its meta data.', 'ga-premium' ),
				'<a href="https://yoast.com/wordpress/plugins/seo/" target="_blank">',
				'</a>',
				'<strong>',
				'</strong>'
			); ?>
		</p>
	</div>
	<?php
}
?>

<script type="text/javascript">
	var total = <?php echo intval( max( $active_custom_dimension_ids ) ); ?>;
	var limit = <?php echo $this->custom_dimensions_limit; ?>;
	var tmp_total = <?php echo $this->custom_dimensions_usage; ?>;
	var translate_delete = '<?php _e( 'Delete', 'ga-premium' ); ?>';
	var options_to_add = '';

	<?php
	$options_to_add = '';
	foreach ( $this->custom_dimensions as $key => $dimension ) :
		$option_disabled = ( ( $dimension['enabled'] ) ? '' : 'disabled' );

		$options_to_add .= '<option value="' . $key  . '" ' . $option_disabled . '>' . $dimension['title'] . '</option>';
	endforeach;
	echo "options_to_add = '{$options_to_add}';";
	?>

	jQuery(document).ready(function () {
		custom_dimensions.init();

		<?php if ( $this->universal_enabled === false ) : ?>
		jQuery ( '#yst_add_cd_holder' ).html ( '<strong><i style="color: red;"><?php printf( __( '%1$sUniversal tracking%2$s is not enabled', 'ga-premium' ), '<a style="color: red;text-decoration: underline; cursor: pointer;" onclick="custom_dimensions.open_universal();">', '</a>' ); ?></i></strong>' );
		<?php endif; ?>
	});

</script>