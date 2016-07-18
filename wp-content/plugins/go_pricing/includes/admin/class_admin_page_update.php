<?php
/**
 * Plugin Update page controller class
 */
 
 
// Prevent direct call
if ( !defined( 'WPINC' ) ) die;
if ( !class_exists( 'GW_GoPricing' ) ) die;	


// Class
class GW_GoPricing_AdminPage_Update extends GW_GoPricing_AdminPage {
	
	/**
	 * Register ajax actions
	 *
	 * @return void
	 */	
	
	public function register_ajax_actions( $ajax_action_callback ) { 
	
		GW_GoPricing_Admin::register_ajax_action( 'update', $ajax_action_callback );
		
	}
	
	
	/**
	 * Action
	 *
	 * @return void
	 */
	 	
	public function action() {
		
		// Create custom nonce
		$this->create_nonce( 'update' );
		
		// Load views if action is empty		
		if ( empty( $this->action ) ) {
			
			$this->content( $this->view() );
			
		}
		
		// Load views if action is not empty (handle postdata)
		if ( !empty( $this->action ) && check_admin_referer( $this->nonce, '_nonce' ) ) {
			
			add_filter( 'upload_mimes', array( $this, 'restrict_upload_mimes') );
			add_filter( 'upload_dir', array( $this, 'set_upload_dir' ) );
			
			$result = $this->validate_import_data( $_FILES );

			if ( $this->is_ajax === false ) {
				if ( $result === false ) {
					wp_redirect( $this->referrer );
				} else {
					wp_redirect( admin_url( 'admin.php?page=go-pricing' ) );
				}
				exit;
			} else {
				echo $this->view();
				GW_GoPricing_AdminNotices::show();
			}

		}
		
	}
	
	
	/**
	 * Load views
	 *
	 * @return void
	 */	
	
	public function view( $view = '' ) {

		ob_start();
		include_once( 'views/page/update.php' );
		return ob_get_clean();
		
	}

	
	/**
	 * Validate & return import data
	 *
	 * @return string | bool
	 */		
	
	public function validate_import_data( $import_data ) {
								
		$hash = md5( microtime() );
		
		if ( empty( $import_data ) || empty( $import_data['plugin-data'] ) || empty( $import_data['plugin-data']['name'] ) || empty( $import_data['plugin-data']['tmp_name'] ) || empty( $import_data['plugin-data']['size'] ) ) {
		
			GW_GoPricing_AdminNotices::add( 'update', 'error', __( 'Please select a file to upload!', 'go_pricing_textdomain' ) );	
			return false;
			
		}
		
		if ( !empty( $import_data['plugin-data']['error'] ) || ( $file_content = @file_get_contents( $_FILES['plugin-data']['tmp_name'] ) ) === false ) {
		
			GW_GoPricing_AdminNotices::add( 'update', 'error', __( 'Oops, something went wrong!', 'go_pricing_textdomain' ) );	
			return false;
			
		}
		
		$file = wp_upload_bits( $hash . '_' . $_FILES['plugin-data']['name'], '', $file_content );
		
		if ( empty( $file ) || empty( $file['file'] ) || !empty( $file['error'] ) ) {
		
			GW_GoPricing_AdminNotices::add( 'update', 'error', !empty( $file['error'] ) ? $file['error'] : __( 'Oops, something went wrong2!', 'go_pricing_textdomain' ) );	
			return false;
			
		}
		
		if ( ( $fs = WP_Filesystem() ) === false ) {
			
			GW_GoPricing_AdminNotices::add( 'update', 'error', __( 'Oops, filesystem error!', 'go_pricing_textdomain' ) );
			unlink( $file['file'] );			
			return false;
			
		}

		global $wp_filesystem;
		
		$upload_dir = wp_upload_dir();
		$temp_dir_path = $upload_dir['path'] . '/' . $hash;
		$unzip_file = unzip_file( $file['file'], $temp_dir_path );		   
		
		if ( ! $unzip_file || !is_dir( $temp_dir_path ) ) {
			unlink( $file['file'] );
			GW_GoPricing_AdminNotices::add( 'update', 'error', __( 'Oops, something went wrong!', 'go_pricing_textdomain' ) );
			return false;	

		}
			
		$upgrader = new GW_GoPricing_Plugin_Upgrader( new GW_GoPricing_Plugin_Installer_Skin() );
		
		if ( ( $plugin_file = $upgrader->validate_upload( $temp_dir_path, self::$plugin_version ) ) === false ) {
			
			GW_GoPricing_AdminNotices::add( 'update', 'error', __( 'Invalid plugin file or version!', 'go_pricing_textdomain' ) );
			unlink( $file['file'] );
			$wp_filesystem->rmdir( $temp_dir_path, true );
			return false;		
			
		}
		
		// Delete temp folder
		$wp_filesystem->rmdir( $temp_dir_path, true );
		
		// Save file data to DB
		$uploads = get_option( self::$plugin_prefix . '_uploads', array() );
		$uploads[] = array(
			'file' => $file['file'],
			'expiration' => gmdate( 'Y-m-d H:i:s', time() + 5 * 60 )
		);
		
		update_option( self::$plugin_prefix . '_uploads', $uploads );		
		
		$is_active_for_network = is_plugin_active_for_network( $this->plugin_base );
		
		if ( $result = $upgrader->update_plugin( $file['file'], $this->plugin_base ) === false ) {

			GW_GoPricing_AdminNotices::add( 'update', 'error', __( 'Plugin update failed!', 'go_pricing_textdomain' ) );
			return false;
		}
		
		GW_GoPricing_AdminNotices::add( 'update', 'success', __( 'Plugin updated successfully!', 'go_pricing_textdomain' ) );

		activate_plugin( $this->plugin_base, NULL, $is_active_for_network, true );

		return $info;
		
	}
	
	
	/**
	 * Restrict allowed mimes
	 *
	 * @return array
	 */		
	
	public function restrict_upload_mimes( $mimes ) {
		
		$allowed_mimes = array( 'zip' => 'application/zip' );
		
		return $allowed_mimes;
		
	}
	
	
	/**
	 * Set custom upload path
	 *
	 * @return array
	 */		
	
	public function set_upload_dir( $param ) {
		
		$param['subdir'] = '/go_pricing_data';
		$param['path'] = $param['basedir'] . $param['subdir'];
				
		return $param;
		
	}		
	
}
 

?>