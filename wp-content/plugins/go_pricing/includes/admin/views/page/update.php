<?php 
/**
 * Import & Export Page - Main View
 */


// Prevent direct call
if ( !defined( 'WPINC' ) ) die;
if ( !class_exists( 'GW_GoPricing' ) ) die;	

// Get current user id 
$user_id = get_current_user_id();

// Get general settings
$general_settings = get_option( self::$plugin_prefix . '_table_settings' );

// Get pricing tables data
$pricing_tables = GW_GoPricing_Data::get_tables();

$max_upload_size = wp_max_upload_size();
if ( !$max_upload_size ) $max_upload_size = __( 'Unknown', 'go_pricing_textdomain' );

?>
<!-- Top Bar -->
<div class="gwa-ptopbar">
	<div class="gwa-ptopbar-icon"></div>
	<div class="gwa-ptopbar-title">Go Pricing</div>
	<div class="gwa-ptopbar-content"><label><span class="gwa-label"><?php _e( 'Help', 'go_pricing_textdomain' ); ?></span><select data-action="help" class="gwa-w80"><option value="1"<?php echo isset( $_COOKIE['go_pricing']['settings']['help'][$user_id] ) && $_COOKIE['go_pricing']['settings']['help'][$user_id] == 1 ? ' selected="selected"' : ''; ?>><?php _e( 'Tooltip', 'go_pricing_textdomain' ); ?></option><option value="2"<?php echo isset( $_COOKIE['go_pricing']['settings']['help'][$user_id] ) && $_COOKIE['go_pricing']['settings']['help'][$user_id] == 2 ? ' selected="selected"' : ''; ?>><?php _e( 'Show', 'go_pricing_textdomain' ); ?></option><option value="0"<?php echo isset( $_COOKIE['go_pricing']['settings']['help'][$user_id] ) && $_COOKIE['go_pricing']['settings']['help'][$user_id] == 0 ? ' selected="selected"' : ''; ?>><?php _e( 'None', 'go_pricing_textdomain' ); ?></option></select></label><a href="#" data-action="submit" title="<?php esc_attr_e( 'Next', 'go_pricing_textdomain' ); ?>" class="gwa-btn-style1 gwa-ml20"><?php _e( 'Next', 'go_pricing_textdomain' ); ?></a></div>
</div>
<!-- /Top Bar -->

<!-- Page Content -->
<div class="gwa-pcontent" data-ajax="<?php echo esc_attr( isset( $general_settings['admin']['ajax'] ) ? "true" : "false" ); ?>" data-help="<?php echo esc_attr( isset( $_COOKIE['go_pricing']['settings']['help'][$user_id] ) ? $_COOKIE['go_pricing']['settings']['help'][$user_id] : '' ); ?>">
	<form id="go-pricing-form" name="plugin-update-form" method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" name="_action" value="update">
		<?php wp_nonce_field( $this->nonce, '_nonce' ); ?>
		
		<div class="gwa-abox">
			<div class="gwa-abox-header">
				<div class="gwa-abox-header-icon"><i class="fa fa-info-circle"></i></div>
				<div class="gwa-abox-title"><?php _e( 'Info', 'go_pricing_textdomain' ); ?> <span class="gwa-info">(<?php printf( __( 'Current Version: %s', 'go_pricing_textdomain' ), self::$plugin_version ); ?>)</span></div>		
			</div>
			<div class="gwa-abox-content gwa-abox-content-big">
				<p><strong><?php _e( 'Please ensure that your copy is from safe source. Most of downloaded plugins from warez sites include harmful codes, which can endanger your whole site.', 'go_pricing_textdomain' ); ?></strong></p>
				<div class="go-pricing-spacer"></div>
				<p><a href="http://codecanyon.net/downloads?ref=Granth" target="_blank" title="<?php esc_attr_e( 'Download Now', 'go_pricing_textdomain' ); ?>" class="gwa-btn-style1"><?php _e( 'Download Now', 'go_pricing_textdomain' ); ?></a><a href="http://codecanyon.net/item/go-pricing-wordpress-responsive-pricing-tables/3725820?ref=Granth" target="_blank" title="<?php esc_attr_e( 'Buy Now', 'go_pricing_textdomain' ); ?>" class="gwa-btn-style2 gwa-ml10"><?php _e( 'Buy Now', 'go_pricing_textdomain' ); ?></a><a href="http://go-pricing.com/changelog?utm_source=plugin" target="_blank" title="<?php esc_attr_e( 'Changelog', 'go_pricing_textdomain' ); ?>" class="gwa-btn-style4 gwa-ml10"><?php _e( 'Changelog', 'go_pricing_textdomain' ); ?></a></p>				
				<div class="go-pricing-separator go-pricing-separator-big"></div>
				<p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'Downgrading the plugin (uploading older versions) is not supported.', 'go_pricing_textdomain' ); ?></p>				
			</div>
		</div>
		
		<!-- Admin Box -->
		<div class="gwa-abox">
			<div class="gwa-abox-header">
				<div class="gwa-abox-header-icon"><i class="fa fa-plug"></i></div>
				<div class="gwa-abox-title"><?php _e( 'Update plugin', 'go_pricing_textdomain' ); ?></div>
				<div class="gwa-abox-ctrl"></div>
			</div>
			<div class="gwa-abox-content-wrap">
				<div class="gwa-abox-content">
					<table class="gwa-table">
						<?php 
						$fs_method = get_filesystem_method();
						if ( $fs_method != 'direct' ) : 
						?>
							<div class="gwa-abox-content gwa-abox-content-big">
								<p><strong><?php _e( 'Oops, it seems WordPress has no direct access to the file system, please update the plugin using FTP.', 'go_pricing_textdomain' ); ?></strong></p>
							</div>
						<?php 
						else : 
						?>
						<tr class="gwa-row-fullwidth">
							<th><label><?php _e( 'Upload Plugin zip', 'go_pricing_textdomain' ); ?></label></th>
							<td>
								<div class="gwa-dnd-upload">
									<span class="gwa-dnd-upload-icon-front"></span>
									<span class="gwa-dnd-upload-icon-back"></span>
									<div class="gwa-dnd-upload-label">
										<p><?php _e( 'Drop files here or', 'go_pricing_textdomain' ); ?></p>
										<p><input type="file" name="plugin-data"><a href="#" data-action="dnd-upload" title="<?php esc_attr_e( 'Select Files', 'go_pricing_textdomain' ); ?>" class="gwa-btn-style1"><?php _e( 'Select Files', 'go_pricing_textdomain' ); ?></a></p>
									</div>
								</div>							
							</td>
							<td><p class="gwa-info"><i class="fa fa-info-circle"></i><?php _e( 'For older browsers or with AJAX disabled, please use the "Select Files" button to upload files.', 'go_pricing_textdomain' ); ?> <?php printf( __( 'Maximum upload file size: %s.', 'go_pricing_textdomain' ), size_format( $max_upload_size ) ); ?></p></td>
						</tr>						
						<?php 
						endif;
						?>															
					</table>																																														
				</div>
			 </div>
		</div>
		<!-- /Admin Box -->
		
		<!-- Submit -->
		<div class="gwa-submit"><button type="submit" class="gwa-btn-style1"><?php _e( 'Next', 'go_pricing_textdomain' ); ?></button></div>
		<!-- /Submit -->		

	</form>
</div>
<!-- /Page Content -->