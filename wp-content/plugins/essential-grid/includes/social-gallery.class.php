<?php

/**
 * Social Gallery WordPress Photo Viewer Plugin Extension
 * @url: http://codecanyon.net/item/social-gallery-wordpress-photo-viewer-plugin/2665332
 * @since: 2.0.1
 **/

if( !defined( 'ABSPATH') ) exit();

class Essential_Grid_Social_Gallery {
	
	/**
	 * Check if Social Gallery is activated and at least version x.x is installed
	 **/
	public static function sg_exists(){
		
		if(!apply_filters( 'socialgallery-installed', false)){
			return false;
		}else{
			return true;
		}
		
	}
	
	
	/**
	 * Check if Social Gallery is active in the Essential Grid Global options
	 **/
	public static function is_active(){
		if(self::sg_exists()){
			$opt = get_option('tp_eg_use_lightbox', 'false');
			if($opt === 'sg') return true;
		}
		
		return false;
	}
	
}
	
?>