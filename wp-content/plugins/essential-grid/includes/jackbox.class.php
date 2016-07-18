<?php

/**
 * JackBox WordPress Plugin Extension
 * @url: http://codecanyon.net/item/jackbox-responsive-lightbox-wordpress-plugin/3357551
 * @since: 2.0
 **/

if( !defined( 'ABSPATH') ) exit();

class Essential_Grid_Jackbox {
	
	/**
	 * Check if JackBox is activated and at least version 2.2 is installed
	 **/
	public static function jb_exists(){
	
		//if(is_plugin_active("wp-jackbox/wp-jackbox.php")){
		if(function_exists('jackbox_admin_link')){
			if(is_admin()){
				$data = get_plugin_data(WP_PLUGIN_DIR."/wp-jackbox/wp-jackbox.php", false);
				update_option('tp_eg_jackbox_version', $data['Version']);
				$version =  $data['Version'];
			}else{
				$version =  get_option('tp_eg_jackbox_version', '0');
			}
			
			return true;
		}else{
			return false;
		}
	}
	
	
	/**
	 * Enable JackBox by adding Essential Grid to the Options of JackBox. JB will handle the rest
	 **/
	public static function enable_jackbox(){
		if(!self::jb_exists()) return false;
		
		$jackbox_options = get_option('jackbox_settings');
		
		if($jackbox_options){
			$jackbox_options['essential'] = 'yes';
		}
		
		update_option("jackbox_settings", $jackbox_options);
	}
	
	
	/**
	 * Disable JackBox by removing Essential Grid from the Options of JackBox. JB will handle the rest
	 **/
	public static function disable_jackbox(){
		if(!self::jb_exists()) return false;
		
		$jackbox_options = get_option('jackbox_settings');
		
		if($jackbox_options){
			$jackbox_options['essential'] = 'no';
		}
		
		update_option("jackbox_settings", $jackbox_options);
	}
	
	
	/**
	 * Check if JackBox is active in the Essential Grid Global options
	 **/
	public static function is_active(){
		if(self::jb_exists()){
			$opt = get_option('tp_eg_use_lightbox', 'false');
			if($opt === 'jackbox') return true;
		}
		
		return false;
	}
	
}
	
?>