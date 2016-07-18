<?php
/**
 * Certificate functions
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Certificates
 */



/**
 * Get certificate details
 *
 * Return a link to certificate and certificate threshold
 *
 * @since 2.1.0
 * 
 * @param  int  	$post_id
 * @param  int  	$user_id
 * @return array    certificate details
 */
function learndash_certificate_details( $post_id, $user_id = null ) {
	$user_id = ! empty( $user_id ) ? $user_id : get_current_user_id();

	$certificateLink = '';
	$post = get_post( $post_id );
	$meta = get_post_meta( $post_id, '_sfwd-quiz' );
	$cert_post = '';
	$certificate_threshold = '0.8';

	if ( is_array( $meta ) && ! empty( $meta ) ) {
		$meta = $meta[0];

		if ( is_array( $meta ) && ( ! empty( $meta['sfwd-quiz_certificate'] ) ) ) {
			$certificate_post = $meta['sfwd-quiz_certificate'];
		}

		if ( is_array( $meta ) && ( ! empty( $meta['sfwd-quiz_threshold'] ) ) ) {
			$certificate_threshold = $meta['sfwd-quiz_threshold'];
		}
	}

	if ( ! empty( $certificate_post ) ) {
		$certificateLink = get_permalink( $certificate_post );
	}

	if ( ! empty( $certificateLink ) ) {
		//$certificateLink .= ( strpos( 'a'.$certificateLink,'?' ) ) ? '&' : '?';
		//$certificateLink .= "quiz={$post->ID}&print=" . wp_create_nonce( $post->ID . $user_id );
		$certificateLink = add_query_arg(
			array(
				"quiz"	=>	$post->ID,
				"print"	=>	wp_create_nonce( $post->ID . $user_id )
			),
			$certificateLink
		);
	}

	return array( 'certificateLink' => $certificateLink, 'certificate_threshold' => $certificate_threshold );
}



/**
 * Shortcode to output course certificate link
 *
 * @since 2.1.0
 * 
 * @param  array 	$atts 	shortcode attributes
 * @return string       	output of shortcode
 */
function ld_course_certificate_shortcode( $atts ) {
	$course_id = @$atts['course_id'];

	if ( empty( $course_id ) ) {
		$course_id = learndash_get_course_id();
	}

	$user_id = get_current_user_id();
	$link = learndash_get_course_certificate_link( $course_id, $user_id );

	if ( empty( $link ) ) {
		return '';
	}

	/**
	 * Filter output of shortcode
	 * 
	 * @since 2.1.0
	 *
	 * @param  string  markout of course certificate short code
	 */
	return apply_filters( 'ld_course_certificate', "<div id='learndash_course_certificate'><a href='".$link."' class='btn-blue' target='_blank'>".__( 'PRINT YOUR CERTIFICATE!', 'learndash' ).'</a></div>', $link, $course_id, $user_id );
}

add_shortcode( 'ld_course_certificate', 'ld_course_certificate_shortcode' );



/**
 * Get course certificate link for user
 *
 * @since 2.1.0
 * 
 * @param  int 		 $course_id
 * @param  int 		 $user_id
 * @return string
 */
function learndash_get_course_certificate_link( $course_id, $user_id = null ) {
	$user_id = get_current_user_id();
	if ( empty( $course_id ) || empty( $user_id ) || ! sfwd_lms_has_access( $course_id, $user_id ) ) {
		return '';
	}

	$certificate_id = learndash_get_setting( $course_id, 'certificate' );

	if ( empty( $certificate_id ) ) {
		return '';
	}

	$course_status = learndash_course_status( $course_id, $user_id );

	if ( $course_status != __( 'Completed', 'learndash' ) ) {
		return '';
	}

	//$url = get_permalink( $certificate_id );
	//$url = ( strpos( '?', $url ) === false ) ? $url.'?' : $url.'&';
	//$url = $url.'course_id='.$course_id.'&user_id='.$user_id;

	$url = add_query_arg(
		array(
			'course_id'	=>	$course_id,
			"print"	=>	wp_create_nonce( $course_id . $user_id )
			
			//'user_id'	=>	$user_id
		),
		get_permalink( $certificate_id )
	);
	return $url;
}



/**
 * Get certificate link if certificate exists and quizzes are completed
 *
 * @todo  consider for deprecation, not being used in plugin
 *
 * @since 2.1.0
 * 
 * @param  int 		 $quiz_id
 * @param  int 		 $user_id
 * @return string
 */
function learndash_get_certificate_link( $quiz_id, $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) || empty( $quiz_id ) ) {
		return '';
	}

	$c = learndash_certificate_details( $quiz_id, $user_id );

	if ( empty( $c['certificateLink'] ) ) {
		return '';
	}

	$usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );
	$usermeta = maybe_unserialize( $usermeta );

	if ( ! is_array( $usermeta ) ) { 
		$usermeta = array();
	}

	foreach ( $usermeta as $quizdata ) {
		if ( ! empty( $quizdata['quiz'] ) && $quizdata['quiz'] == $quiz_id ) {
			if ( $c['certificate_threshold'] <= $quizdata['percentage'] / 100 ) {
				return '<a target="_blank" href="'.$c['certificateLink'].'">'.__( 'PRINT YOUR CERTIFICATE!', 'learndash' ).'</a>';
			}
		}
	}

	return '';
}



/**
 * Show text tab by default on certificate edit screen
 * User should not be able to use visual editor tab
 *
 * @since 2.1.0
 * 
 * @param  array $return 	An array of editors. Accepts 'tinymce', 'html', 'test'.
 * @return array $return 	html
 */
function learndash_disable_editor_on_certificate( $return ) {
	global $post;

	if ( is_admin() && ! empty( $post->post_type ) && $post->post_type == 'sfwd-certificates' ) {
		return 'html';
	}

	return $return;
}

add_filter( 'wp_default_editor', 'learndash_disable_editor_on_certificate', 1, 1 );



/**
 * Disable being able to click the visual editor on certificates
 * User should not be able to use visual editor tab
 *
 * @since 2.1.0
 */
function learndash_disable_editor_on_certificate_js() {
	global $post;
	if ( is_admin() && ! empty( $post->post_type) && $post->post_type == 'sfwd-certificates' ) {
		?>
			<style type="text/css">
			a#content-tmce, a#content-tmce:hover, #qt_content_fullscreen, #insert-media-button{
				display:none;
			}
			</style>
			<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery("#content-tmce").attr("onclick", null);
			});
			</script>
		<?php
	}
}

add_filter( 'admin_footer', 'learndash_disable_editor_on_certificate_js', 99 );


add_action('load-post.php', 'learndash_admin_load_certification_post');
add_action('load-post-new.php', 'learndash_admin_load_certification_post');
function learndash_admin_load_certification_post( ) {
	global $post;
	
	if ( ( ( $post instanceof WP_Post ) && ( $post->post_type == 'sfwd-certificates' ) ) || ( ( isset( $_GET['post_type'] ) ) && ( $_GET['post_type'] == 'sfwd-certificates' ) ) ) {
		add_action( 'add_meta_boxes', 	'learndash_certificates_add_meta_box' );
	}	
}

function learndash_certificates_add_meta_box( $post ) {
	add_meta_box(
		'learndash_certificate_options',
		__( 'LearnDash Certificate Options', 'learndash' ), 
		'learndash_certificate_options_metabox',
		'sfwd-certificates',
		'advanced',
		'high'
	);
}

function learndash_certificate_options_metabox( $certificate ) {

	$config_lang = 'eng';
	if ( ! empty( $_GET['lang'] ) ) {
		$config_lang = substr( esc_html( $_GET['lang'] ), 0, 3 );
	}

	require_once LEARNDASH_LMS_PLUGIN_DIR . '/includes/vendor/tcpdf/config/lang/' . $config_lang . '.php';
	require_once LEARNDASH_LMS_PLUGIN_DIR . '/includes/vendor/tcpdf/tcpdf.php';

	$learndash_certificate_options_selected = get_post_meta( $certificate->ID, 'learndash_certificate_options', true);
	if (!is_array($learndash_certificate_options_selected))
		$learndash_certificate_options_selected = array($learndash_certificate_options_selected);
	
	if ( !isset( $learndash_certificate_options_selected['pdf_page_format'] ) )
		$learndash_certificate_options_selected['pdf_page_format'] = 'LETTER';

	if ( !isset( $learndash_certificate_options_selected['pdf_page_orientation]'] ) )
		$learndash_certificate_options_selected['pdf_page_orientation]'] = PDF_PAGE_ORIENTATION;
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'learndash_certificates_nonce' );

	$learndash_certificate_options['pdf_page_format'] = array(
		"LETTER"	=>	__('Letter / USLetter (default)', 'learndash'),
		"A4"		=>	__('A4', 'learndash')
	);
	$learndash_certificate_options['pdf_page_format'] = apply_filters('learndash_certificate_pdf_page_formats', $learndash_certificate_options['pdf_page_format']);

	$learndash_certificate_options['pdf_page_orientation'] = array(
		"L"		=>	__('Landscape (default)', 'learndash'),
		"P"		=>	__('Portrait', 'learndash')
	);
	$learndash_certificate_options['pdf_page_orientation'] = apply_filters('learndash_certificate_page_pdf_page_orientations', $learndash_certificate_options['pdf_page_orientation']);

	if ( ( is_array( $learndash_certificate_options['pdf_page_format'] ) ) && ( !empty( $learndash_certificate_options['pdf_page_format'] ) ) ) {
		?>
		<p><label for="learndash_certificate_options_pdf_page_format"><?php _e('PDF Page Size', 'learndash') ?></label>
			<select id="learndash_certificate_options_pdf_page_format" name="learndash_certificate_options[pdf_page_format]">
			<?php
				foreach( $learndash_certificate_options['pdf_page_format'] as $key => $label ) {
					?><option <?php selected($key, $learndash_certificate_options_selected['pdf_page_format']) ?> value="<?php echo $key ?>"><?php echo $label ?></option><?php
				}
			?>
			</select>
		</p>
		<?php
	}

	if ( ( is_array( $learndash_certificate_options['pdf_page_orientation'] ) ) && ( !empty( $learndash_certificate_options['pdf_page_orientation'] ) ) ) {
		?>
		<p><label for="learndash_certificate_options_pdf_page_orientation"><?php _e('PDF Page Orientation', 'learndash') ?></label>
			<select id="learndash_certificate_options_pdf_page_orientation" name="learndash_certificate_options[pdf_page_orientation]">
			<?php
				foreach( $learndash_certificate_options['pdf_page_orientation'] as $key => $label ) {
					?><option <?php selected($key, $learndash_certificate_options_selected['pdf_page_orientation']) ?> value="<?php echo $key ?>"><?php echo $label ?></option><?php
				}
			?>
			</select>
		</p>
		<?php
	}
}


function learndash_certificates_save_meta_box( $post_id ) {
	// verify if this is an auto save routine.
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( ! isset( $_POST['learndash_certificates_nonce'] ) || ! wp_verify_nonce( $_POST['learndash_certificates_nonce'], plugin_basename( __FILE__ ) ) ) {
		return;
	}

	if ( 'sfwd-certificates' != $_POST['post_type'] ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$config_lang = 'eng';
	if ( ! empty( $_GET['lang'] ) ) {
		$config_lang = substr( esc_html( $_GET['lang'] ), 0, 3 );
	}

	require_once LEARNDASH_LMS_PLUGIN_DIR . '/includes/vendor/tcpdf/config/lang/' . $config_lang . '.php';
	require_once LEARNDASH_LMS_PLUGIN_DIR . '/includes/vendor/tcpdf/tcpdf.php';

	$learndash_certificate_options = array();

	if ( ( isset( $_POST['learndash_certificate_options']['pdf_page_format'] ) ) && (!empty( $_POST['learndash_certificate_options']['pdf_page_format'] ) ) ) {
		$learndash_certificate_options['pdf_page_format'] = esc_attr( $_POST['learndash_certificate_options']['pdf_page_format'] );
	} else {
		$learndash_certificate_options['pdf_page_format'] = 'LETTER';
	}

	if ( ( isset( $_POST['learndash_certificate_options']['pdf_page_orientation'] ) ) && (!empty( $_POST['learndash_certificate_options']['pdf_page_orientation'] ) ) ) {
		$learndash_certificate_options['pdf_page_orientation'] = esc_attr( $_POST['learndash_certificate_options']['pdf_page_orientation'] );
	} else {
		$learndash_certificate_options['pdf_page_orientation'] = PDF_PAGE_ORIENTATION;
	}
	
	update_post_meta( $post_id, 'learndash_certificate_options', $learndash_certificate_options );
}
add_action( 'save_post', 'learndash_certificates_save_meta_box' );
