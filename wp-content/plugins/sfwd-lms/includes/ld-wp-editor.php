<?php
/**
 * Customizations to wp editor for LearnDash
 *
 * All functions currently are customizations for custom certificate implementations
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\TinyMCE
 */


/**
 * Add hooks for TinyMCE customization if we are on edit screen
 *
 * @todo  should fire on admin_init
 * 
 * @since 2.1.0
 */
function learndash_mce_init() {
	global $post;

	if ( empty( $post->ID ) && isset( $_GET['post'] ) ) {
		$post = get_post( $_GET['post'] );
	}

	if ( is_admin() && ! empty( $post->ID ) && @$post->post_type == 'sfwd-certificates' ) {
		add_filter( 'tiny_mce_before_init', 'wpLD_tiny_mce_before_init' );
		add_filter( 'mce_css', 'filter_mce_css' );
	}

}

add_action( 'init', 'learndash_mce_init' );



/**
 * Load editor styles for LearnDash
 * 
 * @since 2.1.0
 * 
 * @param  string 	$mce_css 
 * @return string 	$mce_css 	path to sfwd_editor.css
 */
function filter_mce_css( $mce_css ) {
		$mce_css = plugins_url( 'assets/css/sfwd_editor.css', __FILE__ );
		return $mce_css;
}



/**
 * Make the background of the vidual editor the image of the certificate
 *
 * @todo  confirm intent of function and if it's still needed
 *        not currently functional
 * 
 * @since 2.1.0
 * 
 * @param  array $initArray tinymce settings
 * @return array $initArray tinymce settings
 */
function wpLD_tiny_mce_before_init( $initArray ) {
	if ( isset( $_GET['post'] ) ) {	
		$post_id = $_GET['post']; 
	} else { $post_id = 
		get_the_id(); 
	}

	$img_path = learndash_get_thumb_url( $post_id );
	$initArray['setup'] = <<<JS
[function(ed) {
    ed.onInit.add(function(ed, e) {
		var w = jQuery("#content_ifr").width();
		var editorId = ed.getParam("fullscreen_editor_id") || ed.id;
		jQuery("#content_ifr").contents().find("#tinymce").css
		({"background-image":"url($img_path)"
		});
		
		if(editorId == 'wp_mce_fullscreen'){
		jQuery("#wp_mce_fullscreen_ifr").contents().find("#tinymce").css
		({"background-image":"url($img_path)"
		});
		}
    });

}][0]
JS;

	return $initArray;
}



/**
 * Get featured image of post
 *
 * @todo  WP Functions exist to accomplish this better
 * 
 * @since 2.1.0
 * 
 * @param  int 		$post_id
 * @return string 	full path of image
 */
function learndash_get_thumb_url( $post_id ) {
	$thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );

	if ( $thumbnail_id ){
		$img_path = get_post_meta( $thumbnail_id, '_wp_attached_file', true );
		$upload_url = wp_upload_dir();
		$img_full_path = $upload_url['baseurl'].'/'.$img_path;
		return $img_full_path;
	}
	
}

?>