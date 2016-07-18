<?php

/**
 * Plugin Name: LearnDash Visual Customizer
 * Plugin URI: http://plugins.projectpanorama.com/plugins/learndash-skins
 * Description: A set of custom colors and color pickers for LearnDash
 * Version: 1.3
 * Author: High Orbit
 * Author URI: http://www.fromhighorbit.com
 * Text Domain: learndash-skins
 * License: GPL2
 */

/*
 * Required Files and Constants
 *
 */

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include_once('lds-license.php');
}


if(! defined('LDS_STORE_URL')) { 
	define( 'LDS_STORE_URL', 'http://www.snaporbital.com' );
}

if(! defined('EDD_LEARNDASH_SKINS')) { 
	define( 'EDD_LEARNDASH_SKINS', 'LearnDash Visual Customizer' );
}

if(! defined('LDS_VER')) {
	define('LDS_VER','1.3.1');
}

if(! defined( 'LDS_PATH' ) ) {
	define( 'LDS_PATH' , plugin_dir_path(__FILE__) );
}

/*
 * Initialize the plugin
 *
 */

add_action('admin_menu', 'lds_settings_page',2500);
add_action( 'plugins_loaded', "learndash_lds_i18ize" );
register_activation_hook( __FILE__ , 'lds_build_stylesheet' );

include_once( 'lds-stylesheet-management.php' );



function lds_settings_page() {
				
    add_submenu_page( 'edit.php?post_type=sfwd-courses',__('LearnDash Appearance','lds_skins'), __('LearnDash Appearance','lds_skins'), 'manage_options', 'admin.php?page=learndash-appearance', 'lds_appearance_settings' );

   $hook = add_submenu_page( 'learndash-lms-non-existant',__('LearnDash Appearance','lds_skins'), __('LearnDash Appearance','lds_skins'), 'manage_options', 'learndash-appearance', 'lds_appearance_settings' );	
   
   add_action( 'load-'.$hook , 'lds_options_saved' );
		
} 


/* 
 * Add translation support
 *
 */

function learndash_lds_i18ize() {
	load_plugin_textdomain( 'lds_skins', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}



/* 
 * Add tab to the learndash settings
 *
 */

add_filter("learndash_admin_tabs", "lds_customizer_tabs");
function lds_customizer_tabs($admin_tabs) {
	
	$admin_tabs["apperance"] = array(
		"link"  		=>      'admin.php?page=learndash-appearance',
		"name" 			=>      __("Appearance","lds_skins"),
		"id"    		=>      "admin_page_learndash-appearance",
		"menu_link"     =>      "edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses",
	);

   	return $admin_tabs;

}

add_filter("learndash_admin_tabs_on_page", "learndash_customizer_learndash_admin_tabs_on_page", 3, 3);
function learndash_customizer_learndash_admin_tabs_on_page($admin_tabs_on_page, $admin_tabs, $current_page_id) {
	
	$admin_tabs_on_page["admin_page_learndash-appearance"] = array_merge($admin_tabs_on_page["sfwd-courses_page_sfwd-lms_sfwd_lms_post_type_sfwd-courses"], (array) $admin_tabs_on_page["admin_page_learndash-appearance"]);
	
	foreach ($admin_tabs as $key => $value) {
		if($value["id"] == $current_page_id && $value["menu_link"] == "edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses")
		{
			$admin_tabs_on_page[$current_page_id][] = "apperance";	
			return $admin_tabs_on_page;
		}
	}
		
	return $admin_tabs_on_page;
}


function lds_appearance_settings() { ?>
		
	<?php if(!current_user_can('manage_options')) { 
		// User doesn't have access to this page
		wp_die( __('You do not have sufficient permissions to access this page.') );
	} ?>

	<?php
	
	// Listen for license activation	
	lds_skins_activate_license();
	
    if( isset( $_GET[ 'tab' ] ) ) {
       $active_tab = $_GET[ 'tab' ];
    } else {
        $active_tab = 'lds_visuals';
    }
	
	?>

    <div class="wrap">
        <div id="icon-tools" class="icon32"></div>
		
        	<h2 class="nav-tab-wrapper">
            	<a href="admin.php?page=learndash-appearance&tab=lds_visuals" class="nav-tab <?php if($active_tab == "lds_visuals") { echo 'nav-tab-active'; } ?>"><?php _e('Visual Customizer','learndash-skins'); ?></a>
            	<a href="admin.php?page=learndash-appearance&tab=lds_license" class="nav-tab <?php if($active_tab == "lds_license") { echo 'nav-tab-active'; } ?>"><?php _e('License','learndash-skins'); ?></a>
      	  	</h2>

			<br>
			<br>
			
			<form method="post" action="options.php"> 	
			
				<?php do_settings_sections( 'lds_customizer' ); ?>   
				<?php settings_fields('lds_customizer'); ?>
				<?php $lds_skin = get_option('lds_skin'); ?>
				<?php $license 	= get_option( 'lds_skins_license_key' ); ?>
				<?php $status 	= get_option( 'lds_skins_license_status' );?>
				<?php $lds_animation = get_option( 'lds_animation'); ?>
								
	            <?php if(isset($_GET['lds_activate_response'])): ?>
	                <div class="lds-status-message">
	                    <pre>
	                        <?php lds_check_activation_response(); ?>
	                    </pre>
	                </div>
	            <?php endif; ?>
			
				<div class="lds-tab <?php if($active_tab == 'lds_license') { echo 'lds-tab-active'; } ?>">
					
		            <h2><?php _e('LearnDash Visual Customizer License','learndash-skins'); ?></h2>
					
					<table class="form-table">
                		<tr valign="top">
                    		<th scope="row" valign="top">
                        		<?php _e('License Key','learndash-skins'); ?>
                        	</th>
                        	<td>
                        		<input id="lds_skins_license_key" name="lds_skins_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
                            	<label class="description" for="lds_skins_license_key"><?php _e('Enter your license key','psp_projects'); ?></label>
                        	</td>
                    	</tr>
					</table>
				</div> <!--/.lds-tab-->
				
				<div class="lds-tab <?php if($active_tab == 'lds_visuals') { echo 'lds-tab-active'; } ?>">	
				
	            <h2><?php _e('LearnDash Visual Customizer','learndash-skins'); ?></h2>
				
				<table class="form-table">
                    <tr>
                        <th><label for="lds_skin"><?php _e('LearnDash Theme','learndash-skins'); ?></label></th>
                        <td><select name="lds_skin" id="learndash-skin">
								<?php if((isset($lds_skin)) && (!empty($lds_skin))): ?>
									<option value="<?php echo $lds_skin; ?>"><?php echo $lds_skin; ?></option>
									<option value="---" disabled>---</option>
	                                <option value="default"><?php _e('Default','learndash-skins'); ?></option>
								<?php else: ?>
	                                <option value="default"><?php _e('Default','learndash-skins'); ?></option>
									<option value="---" disabled>---</option>
								<?php endif; ?>
                                <option value="modern"><?php _e('Modern','learndash-skins'); ?></option>
                                <option value="classic"><?php _e('Classic','learndash-skins'); ?></option>
                                <option value="rustic"><?php _e('Rustic','learndash-skins'); ?></option>
                                <option value="playful"><?php _e('Playful','learndash-skins'); ?></option>
								<option value="upscale"><?php _e('Upscale','learndash-skins'); ?></option>
								<?php do_action('lds_skin_options'); ?>
                            </select>
						</td>
                    </tr>
					<tr>
						<th><label for="lds_animation"><?php _e('Animation','learndash-skins'); ?></label>
						</th>
						<td><input type="checkbox" name="lds_animation" value="yes" <?php if($lds_animation == 'yes') { echo 'checked'; } ?>>							<em><?php _e('Fade in course listing and user profile','learndash-skins'); ?></em>

					</td>
				</table>
				
				<div class="lds-preview">
					
					<p><em><?php _e('Preview of this theme\'s default color scheme','learndash-skins'); ?></em></p>
													
					<div id="lds-modern" class="lds-theme-preview">
						
						<p><strong><?php _e('Modern','learndash-skins'); ?></strong> <?php _e('Flat, minimal and clean with rich blues, grays and greens.','learndash-skins'); ?></p>
						
						<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/previews/modern.jpg" alt="A preview of the modern theme">
						
					</div> <!--/.lds-theme-preview-->
					
					<div id="lds-classic" class="lds-theme-preview">
						
						<p><strong><?php _e('Classic','learndash-skins'); ?></strong> <?php _e('Soft grays, strong geometric lines add up to a clean, professional appearance.','learndash-skins'); ?></p>
						
						<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/previews/classic.jpg" alt="A preview of the classic theme">
						
					</div> <!--/.lds-theme-preview-->
					
					<div id="lds-rustic" class="lds-theme-preview">
						
						<p><strong><?php _e('Rustic','learndash-skins'); ?></strong> <?php _e('Rounded corners and earth tones paired with a dash of depth.','learndash-skins'); ?></p>
						
						<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/previews/rustic.jpg" alt="A preview of the rustic theme">
						
					</div> <!--/.lds-theme-preview-->
					
					<div id="lds-playful" class="lds-theme-preview">
						
						<p><strong><?php _e('Playful','learndash-skins'); ?></strong> <?php _e('Fun and entertaining, with bright colors, handwriting fonts and a dash of style.')?></p>
						<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/previews/playful.jpg" alt="A preview of the playful theme">
						
					</div> <!--/.lds-theme-preview-->
					
					<div id="lds-default" class="lds-theme-preview">
						
						<p><strong><?php _e('Default','learndash-skins'); ?></strong> <?php _e('The default LearnDash theme. Customize the colors but keep the original look and feel.'); ?></p>
						
						<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/previews/default.jpg" alt="A preview of the default theme">
						
					</div> <!--/.lds-theme-preview--> 
			
					<div id="lds-upscale" class="lds-theme-preview">
				
						<p><strong><?php _e('Upscale','learndash-skins'); ?></strong> <?php _e('A luxurious mix of rich leather and gold, perfect for any premium course.'); ?></p>
						<img src="<?php echo plugin_dir_url(__FILE__); ?>/assets/img/previews/upscale.jpg" alt="A preview of the upscale theme">
				
					</div> <!--/.lds-theme-preview-->
			
				</div>
				
				<hr class="lds-seperater lds-mb-60">
				
				<h2><?php _e('Icon Style','learndash-skins'); ?></h2>
				
				<fieldset class="lds-group">
					<table class="form-table">
						<tr>
							<th><label for="lds_icon_style">Icon Style</label></th>
							<td>
								<select id="lds_icon_style" name="lds_icon_style">
									<?php if(get_option('lds_icon_style')): ?>
										<option value="<?php echo get_option('lds_icon_style'); ?>"><?php echo get_option('lds_icon_style'); ?></option>
									<?php endif; ?>
									<option value="---" disabled>---</option>
									<option value="default"><?php _e('default','learndash-skins'); ?></option>
									<option value="modern"><?php _e('modern','learndash-skins'); ?></option>
									<option value="minimal"><?php _e('minimal','learndash-skins'); ?></option>
									<option value="chunky"><?php _e('chunky','learndash-skins'); ?></option>
									<option value="playful"><?php _e('playful','learndash-skins'); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>
								
				<hr class="lds-seperater lds-mb-60">
						
				<h2><?php _e('Customize Colors','learndash-skins'); ?></h2>
				
				<p><?php _e('Customize major colors of the selected Learn Dash LMS theme','learndash-skins'); ?></p>
			
				<fieldset class="lds-group">
					<h3 class="lds-ntb"><?php _e('Course, Lesson and Quiz Listings','learndash-skins'); ?></h3>
					
					<p><?php _e('Headings that appear in course and lesson listing tables','learndash-skins'); ?></p>
			
					<table class="form-table">
						<tr>
							<th><label for="lds_heading_bg"><?php _e('Table heading background','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_heading_bg" name="lds_heading_bg" value="<?php echo get_option('lds_heading_bg');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_heading_txt"><?php _e('Table heading text','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_heading_txt" name="lds_heading_txt" value="<?php echo get_option('lds_heading_txt');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_bg"><?php _e('Table row background','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_row_bg" name="lds_row_bg" value="<?php echo get_option('lds_row_bg');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_bg"><?php _e('Alt. table row background','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_row_bg_alt" name="lds_row_bg_alt" value="<?php echo get_option('lds_row_bg_alt');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_bg"><?php _e('Table row text','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_row_txt" name="lds_row_txt" value="<?php echo get_option('lds_row_txt');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_bg"><?php _e('Sub table row background','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_sub_row_bg" name="lds_sub_row_bg" value="<?php echo get_option('lds_sub_row_bg');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_bg"><?php _e('Sub table row alt background','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_sub_row_bg_alt" name="lds_sub_row_bg_alt" value="<?php echo get_option('lds_sub_row_bg_alt');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_bg"><?php _e('Table sub row text','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_sub_row_txt" name="lds_sub_row_txt" value="<?php echo get_option('lds_sub_row_txt');?>" />
							</td>
						</tr>							
					</table>
				
				</fieldset>
			
				<fieldset class="lds-group">
					
					<h3><?php _e('Visual Elements','learndash-skins'); ?></h3>
					
					<p><?php _e('Core visual elements like the progress bar, completed colors, icons, etc...','learndash-skins'); ?></p>
					
						
					<table class="form-table">
						<tr>
							<th><label for="lds_progress"><?php _e('Progress bar','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_progress" name="lds_progress" value="<?php echo get_option('lds_progress');?>" />
							</td>
						</tr>
						<?php /*
						<tr>
							<th><label for="lds_complete"><?php _e('Completed','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_complete" name="lds_complete" value="<?php echo get_option('lds_complete');?>" />
							</td>
						</tr>
						*/ ?>
						<tr>
							<th><label for="lds_checkbox_incomplete"><?php _e('Checkbox incomplete','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_checkbox_incomplete" name="lds_checkbox_incomplete" value="<?php echo get_option('lds_checkbox_incomplete');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_checkbox_complete"><?php _e('Checkbox complete','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_checkbox_complete" name="lds_checkbox_complete" value="<?php echo get_option('lds_checkbox_complete');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_arrow_incomplete"><?php _e('Arrow incomplete','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_arrow_incomplete" name="lds_arrow_incomplete" value="<?php echo get_option('lds_arrow_incomplete');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_arrow_complete"><?php _e('Arrow complete','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_arrow_complete" name="lds_arrow_complete" value="<?php echo get_option('lds_arrow_complete');?>" />
							</td>
						</tr>
					</table>
				
				</fieldset>
			
				<fieldset class="lds-group">
					<h3><?php _e('Buttons','learndash-skins'); ?></h3>
				
					<p><?php _e('Complete, course, apply buttons','learndash-skins'); ?></p>
				
					<table class="form-table">
						<tr>
							<th><label for="lds_button_border_radius"><?php _e('Rounded Corners','learndash-skins'); ?></label></th>
							<td>
								<select name="lds_button_border_radius" id="lds_button_border_radius">
									<?php if(get_option('lds_button_border_radius')): ?>
										<option value="<?php echo get_option('lds_button_border_radius'); ?>"><?php echo get_option('lds_button_border_radius'); ?></option>
									<?php endif; ?>
									<option value="" disabled>---</option>
									<option value="0">0px - Square</option>
									<option value="2">2</option>
									<option value="4">4</option>
									<option value="6">6</option>
									<option value="8">8</option>
									<option value="10">10</option>
									<option value="12">12</option>
									<option value="14">14</option>
									<option value="16">16</option>
									<option value="18">18</option>
									<option value="20">20</option>
								</select>
							</td>
						<tr>
							<th><label for="lds_button_bg"><?php _e('Standard button','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_button_bg" name="lds_button_bg" value="<?php echo get_option('lds_button_bg');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_button_txt"><?php _e('Standard button text','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_button_txt" name="lds_button_txt" value="<?php echo get_option('lds_button_txt');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_complete_button_bg"><?php _e('Complete button background','learndash-skins'); ?></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_complete_button_bg" name="lds_complete_button_bg" value="<?php echo get_option('lds_complete_button_bg');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_complete_button_txt"><?php _e('Complete button text','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker" name="lds_complete_button_txt" value="<?php echo get_option('lds_complete_button_txt');?>" />
							</td>						
						</tr>
					</table>
			
				</fieldset>
			
				<fielset class="lds-group">
					<h3><?php _e('Widgets','learndash-skins'); ?></h3>
					
					<p><?php _e('Course listing, progress widget, etc...','learndash-skins'); ?></p>
					
				
					<table class="form-table">
						<tr>
							<th><label for="lds_widget_bg"><?php _e('Widget background','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_widget_bg" name="lds_widget_bg" value="<?php echo get_option('lds_widget_bg');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_widget_txt"><?php _e('Widget text','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_widget_txt" name="lds_widget_txt" value="<?php echo get_option('lds_widget_txt');?>" />
							</td>
						</tr>
						<th><label for="lds_links"><?php _e('Widget Links','learndash-skins'); ?></label></th>
						<td>
							<input type="text" class="learndash-skin-color-picker lds_links" name="lds_links" value="<?php echo get_option('lds_links');?>" />
						</td>
						<tr>
							<th><label for="lds_widget_header_bg"><?php _e('Header background','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_widget_header_bg" name="lds_widget_header_bg" value="<?php echo get_option('lds_widget_header_bg');?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_widget_header_txt"><?php _e('Header text','learndash-skins'); ?></label></th>
							<td>
								<input type="text" class="learndash-skin-color-picker lds_widget_header_txt" name="lds_widget_header_txt" value="<?php echo get_option('lds_widget_header_txt');?>" />
							</td>
						</tr>
					</table>
				</fielset>
				
				<fieldset class="lds-group">
					<h3><?php _e('Custom CSS','learndash-skins'); ?></h3>
					<p><?php _e('Customize LearnDash even further with CSS (for experts only)','learndash-skins'); ?></p>
					
					<table class="form-table">
						<tr>
							<th><label for="lds_open_css"><?php _e('Custom CSS','learndash-skins'); ?></label></th>
							<td>
								<textarea name="lds_open_css" id="lds_open_css" rows="10" cols="50"><?php echo get_option('lds_open_css'); ?></textarea>
							</td>
						</tr>
					</table>
				</fieldset>
			
				<fieldset class="lds-group">
					
					<h3><?php _e('Advanced Settings','learndash-skins'); ?></h3>
					
					<p><?php _e('Adjust default CSS selectors and enqueue method','learndash-skins'); ?></p>
					
					<?php $lds_enqueue_method = get_option( 'ldvc_add_method' , 'generated' ); ?>
				
					<table class="form-table">
						<tr>
							<th><label for="ldvc_add_method"><?php _e('Enqueue Method', 'learndash-skins' ); ?></label></th>
							<td><select id="ldvc_add_method" name="ldvc_add_method">
									<option value="generated" <?php if($lds_enqueue_method == 'generated' ) { echo 'selected'; } ?>>Generated file</option>
									<option value="dynamic" <?php if($lds_enqueue_method == 'dynamic' ) { echo 'selected'; } ?>>Dynamic file</option>
									<option value="inline" <?php if($lds_enqueue_method == 'inline' ) { echo 'selected'; } ?>>Inline head</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="lds_widget_wrapper"><?php _e('Widget Wrapper','learndash-skins'); ?></label></th>
							<td>
								<input type="text" name="lds_widget_wrapper" value="<?php echo get_option( 'lds_widget_wrapper' );?>" />
							</td>
						</tr>
						<tr>
							<th><label for="lds_widget_title"><?php _e('Widget Title','learndash-skins'); ?></label></th>
							<td>
								<input type="text" name="lds_widget_title" value="<?php echo get_option( 'lds_widget_title' , '.widget-title' );?>" />
							</td>
						</tr>
					</table>
					
					
			</div> <!--/.lds-tab-->
				
                <p><input type="submit" class="button button-primary" value="Save" name="save"></p>

			</form>
				
           	 	<?php if(( false !== $license ) && ($active_tab == "lds_license")) { ?>
					
				<hr>
					
				<form method="post" action="">
					
					<table class="form-table">
           			 <tr valign="top">
                    	 <th scope="row" valign="top">
                        	 <?php _e('Activate License','learndash-skins'); ?>
                    	 </th>
                    	 <td>
                        	 <?php if( $status !== false && $status == 'valid' ) { ?>
                        		 <span style="color:green;" class="lds-activation-notice"><?php _e('Active','learndash-skins'); ?></span>
                         		 <?php wp_nonce_field( 'lds_nonce', 'lds_nonce' ); ?>
                            	<input type="submit" class="button-secondary" name="lds_license_deactivate" value="<?php _e('Deactivate License','learndash-skins'); ?>"/>
							   <?php } else { ?>
                            	   <span style="color:red;" class="lds-activation-notice"><?php _e('Inactive','learndash-skins'); ?></span>
                            	   <?php wp_nonce_field( 'lds_nonce', 'lds_nonce' ); ?>
                            	   <input type="submit" class="button-secondary" name="lds_license_activate" value="<?php _e('Activate License','learndash-skins'); ?>"/>
                        	  <?php } ?>
                    	  </td>
                	  </tr>
					  </table>
					  
		  		</form>
            	<?php } ?>
			
        </div>

    <?php
}

add_filter( 'the_content', 'lds_load_skin_assets', 9999 );
function lds_load_skin_assets($content = null) {
	
	wp_enqueue_style('font-awesome', plugin_dir_url(__FILE__) . 'assets/css/font-awesome.min.css'); 
	
	$enqueue_method = get_option( 'ldvc_add_method' );
		
	if( $enqueue_method == 'dynamic' ) { 
	
		wp_enqueue_style('lds-custom-style', plugin_dir_url(__FILE__).'assets/css/learndash-skins-custom.css.php',array('sfwd_template_css'));
	
	} elseif( $enqueue_method == 'generated' ) { 
	
		wp_enqueue_style('lds-custom-style', plugin_dir_url(__FILE__).'assets/css/learndash-compiled.css',array('sfwd_template_css'));
	
	} elseif( $enqueue_method == 'inline' ) {
		
		wp_dequeue_style( 'sfwd_template_css' );
		
	}
	
	return $content;

}

add_action( 'wp_enqueue_scripts', 'lds_move_template_script' );
function lds_move_template_script() { 
	
	if( get_option( 'ldvc_add_method' ) == 'inline' ) { 
	
		wp_enqueue_style( 'sfwd_template_css' , plugins_url() . '/sfwd-lms/templates/learndash_template_style.css' );
	
	}

}

add_action( 'wp_head' , 'lds_inline_styles' );
function lds_inline_styles() { 

	$enqueue_method = get_option( 'ldvc_add_method' );

	if( $enqueue_method == 'inline' ) { 
	
		?>

		<style type="text/css">
		
			<?php echo lds_generate_styles(); ?>
		
		</style>

		<?php 
	
	}

}

add_action( 'admin_enqueue_scripts', 'lds_enqueue_color_picker' );
function lds_enqueue_color_picker( $hook_suffix ) {
   
    wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'lds-admin', plugin_dir_url(__FILE__) . 'assets/css/lds-admin.css'); 
    wp_enqueue_script( 'lds-custom-js', plugin_dir_url(__FILE__).'assets/js/lds-admin.js', array( 'wp-color-picker' ), false, true );
	
	if(get_option('lds_skin') == 'playful') { 
		wp_enqueue_style('architects-daughter','http://fonts.googleapis.com/css?family=Permanent+Marker');
	}
	
}

add_action('admin_init', 'lds_register_settings');
function lds_register_settings() { 	
		
	$lds_settings = array(
		'lds_skins_license_key',
		'lds_skins_sanitize_license',
		'lds_skins_license_status',
		'lds_animation',
		'lds_skin',
		'lds_icon_style',
		'lds_heading_bg',
		'lds_heading_txt',
		'lds_row_bg',
		'lds_row_bg_alt',
		'lds_sub_row_bg',
		'lds_sub_row_bg_alt',
		'lds_sub_row_txt',
		'lds_row_txt',
		'lds_button_bg',
		'lds_button_border_radius',
		'lds_button_txt',
		'lds_complete_button_bg',
		'lds_complete_button_txt',
		'lds_progress',
		'lds_links',
		'lds_checkbox_incomplete',
		'lds_checkbox_complete',
		'lds_arrow_incomplete',
		'lds_arrow_complete',
		'lds_complete',
		'lds_widget_bg',
		'lds_widget_header_bg',
		'lds_widget_header_txt',
		'lds_widget_txt',
		'lds_open_css',
		'lds_widget_wrapper',
		'lds_widget_title',
		'ldvc_add_method'		
	);
	
	foreach($lds_settings as $setting) { 
		register_setting('lds_customizer', $setting);
	}
	
}


function lds_options_saved() { 

	if( get_option( 'ldvc_add_method' ) == 'generated' ) {
		
		lds_build_stylesheet();
		
	}

}


add_action( 'admin_init', 'edd_lds_plugin_updater' );
function edd_lds_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'lds_skins_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( LDS_STORE_URL, __FILE__, array(
			'version' 	=> LDS_VER, 				// current version number
			'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
			'item_name' => EDD_LEARNDASH_SKINS, 	// name of this plugin
			'author' 	=> 'High Orbit',  // author of this plugin
			'url'           => home_url()
		)
	);

}

function lds_sanitize_license( $new ) {
	$old = get_option( 'lds_skins_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'lds_skins_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

function lds_skins_activate_license() {
								
	// listen for our activate button to be clicked
	if( isset( $_POST['lds_license_activate'] ) ) {		

		// run a quick security check
	 	if( ! check_admin_referer( 'lds_nonce', 'lds_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'lds_skins_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( EDD_LEARNDASH_SKINS ), // the name of our product in EDD
		    'url'   => home_url()
        );

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, LDS_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		// $license_data->license will be either "active" or "inactive"

		update_option( 'lds_skins_license_status', $license_data->license );

	}

}
add_action('admin_init', 'lds_skins_activate_license',1);

function lds_check_activation_response() {

    // retrieve the license from the database
    $license = trim( get_option( 'lds_skins_license_key' ) );


    // data to send in our API request
    $api_params = array(
        'edd_action'=> 'activate_license',
        'license' 	=> $license,
        'item_name' => urlencode( EDD_LEARNDASH_SKINS ), // the name of our product in EDD
        'url'   => home_url()
    );

    // Call the custom API.
    $response = wp_remote_get( add_query_arg( $api_params, LDS_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

	var_dump($response);

}


/***********************************************
* Illustrates how to deactivate a license key.
* This will descrease the site count
***********************************************/

function lds_skins_deactivate_license() {
	
	// listen for our activate button to be clicked
	if( isset( $_POST['lds_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'lds_nonce', 'lds_nonce' ) )
			return; // get out if we didn't click the deactivate button

		// retrieve the license from the database
		$license = trim( get_option( 'lds_skins_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( EDD_LEARNDASH_SKINS ) // the name of our product in EDD
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, LDS_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' )
			delete_option( 'lds_skins_license_status' );

	}
}
add_action('admin_init', 'lds_skins_deactivate_license',1);


/************************************
* this illustrates how to check if
* a license key is still valid
* the updater does this for you,
* so this is only needed if you
* want to do something custom
*************************************/

function lds_skins_check_license() {

	global $wp_version;

	$license = trim( get_option( 'lds_skins_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( EDD_LEARNDASH_SKINS )
	);

	// Call the custom API.
	$response = wp_remote_get( add_query_arg( $api_params, LDS_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		echo 'valid'; exit;
		// this license is still valid
	} else {
		echo 'invalid'; exit;
		// this license is no longer valid
	}
}
	
