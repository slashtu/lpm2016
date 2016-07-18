<?php
/**
 * Editor popup part - Button view
 */


// Prevent direct call
if ( !defined( 'WPINC' ) ) die;
if ( !class_exists( 'GW_GoPricing' ) ) die;
 
?>

<tr>
	<th><label><?php _e( 'Type', 'go_pricing_textdomain' ); ?></label></th>
	<td>
		<select name="button[type]">
			<option value="button"<?php echo !empty ( $postdata['button']['type'] ) && $postdata['button']['type']=='button' ? ' selected="selected"' : ''; ?>><?php _e( 'Regular Button', 'go_pricing_textdomain' ); ?></option>
			<option value="submit"<?php echo !empty ( $postdata['button']['type'] ) && $postdata['button']['type']=='submit' ? ' selected="selected"' : ''; ?>><?php _e( 'Form Submit Button (e.g. Paypal)', 'go_pricing_textdomain' ); ?></option>                                                
			<option value="custom"<?php echo !empty ( $postdata['button']['type'] ) && $postdata['button']['type']=='custom' ? ' selected="selected"' : ''; ?>><?php _e( 'Custom Button', 'go_pricing_textdomain' ); ?></option>                                                
		</select>							
	</td>
	<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Type of the button.', 'go_pricing_textdomain' ); ?></p></td>
</tr>
<tr>
	<th><label><?php _e( 'Size', 'go_pricing_textdomain' ); ?></label></th>
	<td>
		<select name="button[size]">
			<option value="small"<?php echo !empty ( $postdata['button']['size'] ) && $postdata['button']['size']=='small' ? ' selected="selected"' : ''; ?>><?php _e( 'Small', 'go_pricing_textdomain' ); ?></option>
			<option value="medium"<?php echo ( !empty ( $postdata['button']['size'] ) && $postdata['button']['size']=='medium' ) ||  empty ( $postdata['button']['size'] ) ? ' selected="selected"' : ''; ?>><?php _e( 'Medium', 'go_pricing_textdomain' ); ?></option>
			<option value="large"<?php echo !empty ( $postdata['button']['size'] ) && $postdata['button']['size']=='large' ? ' selected="selected"' : ''; ?>><?php _e( 'Large', 'go_pricing_textdomain' ); ?></option>
		</select>							
	</td>
	<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Size of the button.', 'go_pricing_textdomain' ); ?></p></td>
</tr>
<tr>
	<th><label><?php _e( 'Font Family', 'go_pricing_textdomain' ); ?></label></th>
	<td>
		<select name="button[font-family]">
			<?php 
			foreach ( (array)$go_pricing['fonts'] as $fonts ) : 
			if ( !empty( $fonts['group_name'] ) )	:
			?>
			<optgroup label="<?php echo esc_attr( $fonts['group_name'] ); ?>"></optgroup>
			<?php 
			foreach ( (array)$fonts['group_data'] as $font_data ) :
			?>
			<option value="<?php echo esc_attr( !empty( $font_data['value'] ) ? $font_data['value'] : '' ); ?>"<?php echo ( !empty( $font_data['value'] ) && isset( $postdata['button']['font-family'] ) && $font_data['value'] == $postdata['button']['font-family'] ? ' selected="selected"' : '' ); ?>><?php echo ( !empty( $font_data['name'] ) ? $font_data['name'] : '' ); ?></option>
			<?php
			endforeach;
			else :
			?>
			<option value="<?php echo esc_attr( !empty( $fonts['value'] ) ? $fonts['value'] : '' ); ?>"<?php echo ( !empty( $fonts['value'] ) && isset( $postdata['button']['font-family'] ) && $fonts['value'] == $postdata['button']['font-family'] ? ' selected="selected"' : '' ); ?>><?php echo ( !empty( $fonts['name'] ) ? $fonts['name'] : '' ); ?></option>
			<?php 
			endif;
			endforeach;
			?>
		</select>
	<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Font family of the button.', 'go_pricing_textdomain' ); ?></p></td>
</tr>
<tr>
	<th><label><?php _e( 'Font Size / Line H.', 'go_pricing_textdomain' ); ?> <span class="gwa-info">(px)</span></label></th>
	<td><input type="text" name="button[font-size]" value="<?php echo !empty( $postdata['button']['font-size'] ) ? esc_attr( $postdata['button']['font-size'] ) : 12; ?>" class="gwa-input-mid"><input type="text" name="button[line-height]" value="<?php echo !empty( $postdata['button']['line-height'] ) ? esc_attr( $postdata['button']['line-height'] ) : 16; ?>" class="gwa-input-mid"><div class="gwa-icon-btn"><a href="#" title="<?php esc_attr_e( 'Bold', 'go_pricing_textdomain' ); ?>" data-action="font-style-bold"<?php echo !empty( $postdata['button']['font-style']['bold'] ) ? ' class="gwa-current"' : ''; ?>><i class="fa fa-bold"></i><input type="hidden" name="button[font-style][bold]" value="<?php echo !empty( $postdata['button']['font-style']['bold'] ) ? esc_attr( $postdata['button']['font-style']['bold'] ) : ''; ?>"></a><a href="#" title="<?php esc_attr_e( 'Italic', 'go_pricing_textdomain' ); ?>" data-action="font-style-italic"<?php echo !empty( $postdata['button']['font-style']['italic'] ) ? ' class="gwa-current"' : ''; ?>><i class="fa fa-italic"></i><input type="hidden" name="button[font-style][italic]" value="<?php echo !empty( $postdata['button']['font-style']['italic'] ) ? esc_attr( $postdata['button']['font-style']['italic'] ) : ''; ?>"></a><a href="#" title="<?php esc_attr_e( 'Strikethrough', 'go_pricing_textdomain' ); ?>" data-action="font-style-strikethrough"<?php echo !empty( $postdata['button']['font-style']['strikethrough'] ) ? ' class="gwa-current"' : ''; ?>><i class="fa fa-strikethrough"></i><input type="hidden" name="button[font-style][strikethrough]" value="<?php echo !empty( $postdata['button']['font-style']['strikethrough'] ) ? esc_attr( $postdata['button']['font-style']['strikethrough'] ) : ''; ?>"></a></div></td>
	<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Font size and line height of the button in pixels.', 'go_pricing_textdomain' ); ?></p></td>
</tr>								
<tr class="gwa-row-fullwidth">
	<th><label><?php _e( 'Text', 'go_pricing_textdomain' ); ?></label></th>
	<td><div class="gwa-textarea-btn"><textarea name="button[content]" rows="5" data-popup="sc-button-icon" data-preview="<?php esc_attr_e( 'Text', 'go_pricing_textdomain' ); ?>"><?php echo isset( $postdata['button']['content'] ) ? esc_textarea( $postdata['button']['content'] ) : '' ; ?></textarea><div class="gwa-textarea-btn-top"><a href="#" data-action="popup" data-popup="sc-button-icon" title="<?php _e( 'Add Shortcode', 'go_pricing_textdomain' ); ?>" class="gwa-fr"><i class="fa fa-code"></i></a></div></div>
	<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Text of the "Regular" and "Paypal" buttons. Leave blank for "Custom" buttons.', 'go_pricing_textdomain' ); ?></p></td>
</tr>
<tr class="gwa-row-fullwidth">
	<th><label><?php _e( 'Link / Code', 'go_pricing_textdomain' ); ?></label></th>
	<td><textarea name="button[code]" rows="5" data-preview="<?php esc_attr_e( 'Link / Code', 'go_pricing_textdomain' ); ?>"><?php echo isset( $postdata['button']['code'] ) ? esc_textarea( $postdata['button']['code'] ) : '' ; ?></textarea></td>
	<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'URL of the "Regular" button or code of the "Paypal" and "Custom" buttons.', 'go_pricing_textdomain' ); ?></p></td>
</tr>																								
<tr data-parent-id="button[type]" data-parent-value="button"<?php echo ( isset( $postdata['button']['type'] ) && $postdata['button']['type'] == 'button' ) || !isset( $postdata['button']['type'] ) ? ' style="display:none;"' : ''; ?>>
	<th><label><?php _e( 'Open In New Window?', 'go_pricing_textdomain' ); ?></label></th>
	<td><p><label><span class="gwa-checkbox<?php echo isset( $postdata['button']['target'] ) ? ' gwa-checked' : ''; ?>" tabindex="0"><span></span><input type="checkbox" name="button[target]" tabindex="-1" value="1" <?php echo isset( $postdata['button']['target'] ) ? ' checked="checked"' : ''; ?>></span><?php _e( 'Yes', 'go_pricing_textdomain' ); ?></label></p></td>
	<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Whether to open the link in a new window.', 'go_pricing_textdomain' ); ?></p></td>									
</tr>
<tr data-parent-id="button[type]" data-parent-value="button"<?php  echo ( isset( $postdata['button']['type'] ) && $postdata['button']['type'] == 'button' ) || !isset( $postdata['button']['type'] ) ? ' style="display:none;"' : ''; ?>>
	<th><label><?php _e( 'Nofollow Link?', 'go_pricing_textdomain' ); ?></label></th>
	<td><p><label><span class="gwa-checkbox<?php echo isset( $postdata['button']['nofollow'] ) ? ' gwa-checked' : ''; ?>" tabindex="0"><span></span><input type="checkbox" name="button[nofollow]" tabindex="-1" value="1" <?php echo isset( $postdata['button']['nofollow'] ) ? ' checked="checked"' : ''; ?>></span><?php _e( 'Yes', 'go_pricing_textdomain' ); ?></label></p></td>                                                                                
	<td class="gwa-abox-info"><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Whether to tell the search engines "Don\'t follow the link".', 'go_pricing_textdomain' ); ?></p></td>
</tr>																														