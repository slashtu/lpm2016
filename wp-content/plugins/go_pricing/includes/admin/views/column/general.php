<?php
/**
 * Column general view
 */


// Prevent direct call
if ( !defined( 'WPINC' ) ) die;
if ( !class_exists( 'GW_GoPricing' ) ) die; 

global $go_pricing; 
 
?>

<!-- Geneal -->
<div class="gwa-col-box gwa-col-box-main gwa-closed">
	<div class="gwa-col-box-header">
		<div class="gwa-col-box-header-icon"><i class="fa fa-stack-exchange"></i></div>
		<div class="gwa-col-box-title"><?php _e( 'Style & Layout', 'go_pricing_textdomain' ); ?></div>
		<div class="gwa-col-box-ctrl"></div>
	</div>
	<div class="gwa-col-box-content">
		<table class="gwa-table">
			<?php 
			$selected_style = isset( $_POST['param']['style'] ) ? $_POST['param']['style'] : ( isset( $table_data['style'] ) ? $table_data['style'] : 'clean' ); 
			if ( !empty( $go_pricing['style_types'] ) ) : 
			foreach ( (array)$go_pricing['style_types'] as $style_type => $style_type_data ) :
			
			if ( $style_type != $selected_style ) continue;
			?>					
			<tr class="gwa-row-fullwidth">
				<th><label><?php _e( 'Column Style', 'go_pricing_textdomain' ); ?></label></th>
				<td>
					<select name="col-data[<?php echo $col_index ?>][col-style-type]" class="gwa-img-selector">
						<?php 
						$style_img_src = '';
						foreach ( $style_type_data as $col_style ) : 
						if ( !empty( $col_style['group_name'] ) && !empty( $col_style['group_data'] ) )	:
						?>
						<optgroup label="<?php echo esc_attr( $col_style['group_name'] ); ?>"></optgroup>
						<?php 
						foreach ( (array)$col_style['group_data'] as $col_style ) :
						if ( !empty( $col_style['value'] ) && !empty( $col_style['data'] ) ) {
							if ( empty( $style_img_src ) ) $style_img_src = $col_style['data'];	
							if ( !empty( $col_data['col-style-type'] ) && $col_style['value'] == $col_data['col-style-type'] ) $style_img_src = $col_style['data'];	
						}
						?>
						<option data-type="<?php echo esc_attr( !empty( $col_style['type'] ) ? $col_style['type'] : '' ); ?>" data-src="<?php echo esc_attr( !empty( $col_style['data'] ) ? $col_style['data'] : '' ); ?>" value="<?php echo esc_attr( !empty( $col_style['value'] ) ? $col_style['value'] : '' ); ?>"<?php echo ( !empty( $col_style['value'] )  && !empty ( $col_data['col-style-type'] ) && $col_style['value'] == $col_data['col-style-type'] ? ' selected="selected"' : '' ); ?>><?php echo ( !empty( $col_style['name'] ) ? $col_style['name'] : '' ); ?></option>
						<?php
						endforeach;
						else :
						?>
						<option data-type="<?php echo esc_attr( !empty( $col_style['type'] ) ? $col_style['type'] : '' ); ?>" data-src="<?php echo esc_attr( !empty( $col_style['data'] ) ? $col_style['data'] : '' ); ?>" value="<?php echo esc_attr( !empty( $col_style['value'] ) ? $col_style['value'] : '' ); ?>"<?php echo ( !empty( $col_style['value'] )  && !empty ( $col_data['col-style-type'] ) && $col_style['value'] == $col_data['col-style-type'] ? ' selected="selected"' : '' ); ?>><?php echo ( !empty( $col_style['name'] ) ? $col_style['name'] : '' ); ?></option>
						<?php 
						endif;
						if ( !empty( $col_style['value'] ) && !empty( $col_style['data'] ) ) {
							if ( empty( $style_img_src ) ) $style_img_src = $col_style['data'];	
							if ( !empty( $col_data['col-style-type'] ) && $col_data['col-style-type'] == $col_style['value'] ) $style_img_src = $col_style['data'];
						}
						endforeach;
						?>
					</select>
					<div class="gwa-img-selector-media">
					<?php if ( !empty( $style_img_src ) ) : ?>
					<img src="<?php echo esc_attr( $style_img_src ); ?>">
					<?php endif; ?>					
					</div>
				</td>
				<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Style of the column.', 'go_pricing_textdomain' ); ?></p></td>
			</tr>
			<?php 
			endforeach;
			endif;
			?>
			
			<?php if ( $selected_style == 'clean') : ?>
			<tr>
				<th><label><?php _e( 'Main Color', 'go_pricing_textdomain' ); ?></label></th>
				<td><label><div class="gwa-colorpicker" tabindex="0"><input type="hidden" name="col-data[<?php echo $col_index ?>][main-color]" value="<?php echo esc_attr( isset( $col_data['main-color'] ) ? $col_data['main-color'] : '' ); ?>"><span class="gwa-cp-picker"><span<?php echo ( !empty( $col_data['main-color'] ) ? ' style="background:' . $col_data['main-color'] . ';"' : '' ); ?>></span></span><span class="gwa-cp-label"><?php echo ( !empty( $col_data['main-color'] ) ? $col_data['main-color'] : '&nbsp;' ); ?></span><div class="gwa-cp-popup"><div class="gwa-cp-popup-inner"></div><div class="gwa-input-btn"><input type="text" tabindex="-1" value="<?php echo esc_attr( !empty( $col_data['main-color'] ) ? $col_data['main-color'] : '' ); ?>"><a href="#" data-action="cp-fav" tabindex="-1" title="<?php _e( 'Add To Favourites', 'go_pricing_textdomain' ); ?>"><i class="fa fa-heart"></i></a></div></div></div></label></td>
				<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Main color of the pricing table.', 'go_pricing_textdomain' ); ?></p></td>
			</tr>
			<?php endif; ?>
		</table>
		<div class="gwa-table-separator"></div>
		<table class="gwa-table">
			<tr>
				<th><label><?php _e( 'Highlight Column?', 'go_pricing_textdomain' ); ?></label></th>
				<td><p><label><span class="gwa-checkbox<?php echo !empty( $col_data['col-highlight'] ) ? ' gwa-checked' : ''; ?>" tabindex="0"><span></span><input type="checkbox" name="col-data[<?php echo $col_index ?>][col-highlight]" tabindex="-1" value="1" <?php echo !empty( $col_data['col-highlight'] ) ? ' checked="checked"' : ''; ?>></span><?php _e( 'Yes', 'go_pricing_textdomain' ); ?></label></p></td>
				<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Whether to enable hover / active state of the column.', 'go_pricing_textdomain' ); ?></p></td>									
			</tr>										
			<tr>
				<th><label><?php _e( 'Disable Hover?', 'go_pricing_textdomain' ); ?></label></th>
				<td><p><label><span class="gwa-checkbox<?php echo !empty( $col_data['col-disable-hover'] ) ? ' gwa-checked' : ''; ?>" tabindex="0"><span></span><input type="checkbox" name="col-data[<?php echo $col_index ?>][col-disable-hover]" tabindex="-1" value="1" <?php echo !empty( $col_data['col-disable-hover'] ) ? ' checked="checked"' : ''; ?>></span><?php _e( 'Yes', 'go_pricing_textdomain' ); ?></label></p></td>
				<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Whether to disable hover / active state of the column.', 'go_pricing_textdomain' ); ?></p></td>									
			</tr>
			<tr>
				<th><label><?php _e( 'Disable Enlarge?', 'go_pricing_textdomain' ); ?></label></th>
				<td><p><label><span class="gwa-checkbox<?php echo !empty( $col_data['col-disable-enlarge'] ) ? ' gwa-checked' : ''; ?>" tabindex="0"><span></span><input type="checkbox" name="col-data[<?php echo $col_index ?>][col-disable-enlarge]" tabindex="-1" value="1" <?php echo !empty( $col_data['col-disable-enlarge'] ) ? ' checked="checked"' : ''; ?>></span><?php _e( 'Yes', 'go_pricing_textdomain' ); ?></label></p></td>
				<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Whether to disable enlarge effect on column hover / highlight.', 'go_pricing_textdomain' ); ?></p></td>									
			</tr>																						
		</table>							
	</div>
</div>
<!-- / Geneal -->

<!-- Decoration -->
<?php
$postdata = array();
$raw_postdata = isset( $table_data['col-data'][$col_index]['decoration'] ) ? $table_data['col-data'][$col_index]['decoration'] : '';
if ( $raw_postdata !='' && is_string( $raw_postdata ) ) $postdata = GW_GoPricing_Helper::parse_data( $raw_postdata );
$setting_shadow = '-';
$setting_sign = '-';
?>
<div class="gwa-col-box">
	<a href="#" class="gwa-col-box-link" title="<?php esc_attr_e( 'Decoration', 'go_pricing_textdomain' ); ?>" tabindex="-1"></a>
	<div class="gwa-assets-nav"><a href="#" class="gwa-asset-icon-edit" data-action="edit-box" data-popup="general_decoration" data-popup-subtitle="<?php esc_attr_e( 'Shadow & Sign Settings', 'go_pricing_textdomain' ); ?>" title="<?php esc_attr_e( 'Edit', 'go_pricing_textdomain' ); ?>" tabindex="-1"><span></span></a></div>
	<input type="hidden" name="col-data[<?php echo $col_index ?>][decoration]" value="<?php echo esc_attr( $raw_postdata ); ?>">
	<div class="gwa-col-box-header">
		<div class="gwa-col-box-header-icon"><i class="fa fa-sun-o"></i></div>
		<div class="gwa-col-box-title"><?php _e( 'Decoration', 'go_pricing_textdomain' ); ?></div>
	</div>
	<div class="gwa-col-box-content">
		<?php 
		if ( isset( $postdata['col-shadow'] ) && !empty( $go_pricing['shadows'] ) ) {
			foreach ( (array)$go_pricing['shadows'] as $col_shadow ) {
				if ( $col_shadow['value'] == $postdata['col-shadow'] ) $setting_shadow = $col_shadow['name'];
			}
		}

		if ( isset( $postdata['col-sign-type'] ) && !empty( $go_pricing['sign_types'] ) ) {
			foreach ( (array)$go_pricing['sign_types'] as $sign_type ) {
				if ( !empty( $sign_type['group_name'] ) && !empty( $sign_type['group_data'] ) )	{
					foreach ( (array)$sign_type['group_data'] as $sign ) {
						if ( $sign['id'] == $postdata['col-sign-type'] ) $setting_sign = $sign['name'];
					}
				} else {
					if ( $sign_type['id'] == $postdata['col-sign-type'] ) $setting_sign = $sign_type['name'];
				}
			}
		}
		?>
		<p><?php _e( 'Shadow Style', 'go_pricing_textdomain' ); ?>: <span><?php echo htmlentities( $setting_shadow ); ?></span></p>		
		<p><?php _e( 'Sign Type', 'go_pricing_textdomain' ); ?>: <span><?php echo htmlentities( $setting_sign ); ?></span></p>		
	</div>
</div>
<!-- / Decoration -->