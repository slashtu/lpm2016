<?php
/**
 * User functions
 *
 * @since 2.1.0
 *
 * @package LearnDash\Users
 */



/**
 *
 * Outputs HTML for courses which the user is enrolled into
 *
 * @since 2.1.0
 *
 * @param  object $user User object
 */
function learndash_show_enrolled_courses( $user ) {
	$courses = get_pages( 'post_type=sfwd-courses' );
	$enrolled = array();
	$notenrolled = array();
	?>
		<table class='form-table'>
			<tr>
				<th> <h3><?php printf( __( 'Enrolled %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'courses' ) ); ?></h3></th>
				<td>
					<ol>
					<?php
						foreach ( $courses as $course ) {
							if ( sfwd_lms_has_access( $course->ID,  $user->ID ) ) {
								$since = ld_course_access_from( $course->ID,  $user->ID );
								$since = empty( $since ) ? '' : 'Since: '.date( 'm/d/Y H:i:s', $since );

								if ( empty( $since ) ) {
									$since = learndash_user_group_enrolled_to_course_from( $user->ID, $course->ID );
									$since = empty( $since ) ? '' : 'Since: '.date( 'm/d/Y H:i:s', $since ).' (Group Access)';
								}

								echo "<li><a href='".get_permalink( $course->ID )."'>".$course->post_title."</a> ".$since."</li>";
								$enrolled[] = $course;
							} else {
								$notenrolled[] = $course;
							}
						}
					?>
					</ol>
				</td>
			</tr>

			<?php if ( current_user_can( 'enroll_users' ) ) : ?>
					<tr>
						<th> <h3><?php printf( __( 'Enroll a %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h3></th>
						<td>
							<select name='enroll_course'>
								<option value=''><?php printf( __('-- Select a %s --', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></option>
									<?php foreach ( $notenrolled as $course ) : ?>
										<option value="<?php echo $course->ID; ?>"><?php echo $course->post_title; ?></option>
									<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th> <h3><?php printf( __('Unenroll a %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h3></th>
						<td>
								<select name='unenroll_course'>
									<option value=''><?php printf( __('-- Select a %s --', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></option>
									<?php foreach ( $enrolled as $course ) : ?>
										<option value="<?php echo $course->ID; ?>"><?php echo $course->post_title; ?></option>
									<?php endforeach; ?>
								</select>
						</td>
					</tr>
			<?php endif; ?>
		</table>
	<?php
}



/**
 *
 * Saves enrolled courses for a particular user given it's user id.  Returns false on inability to enroll users.
 *
 * @since 2.1.0
 *
 * @param  int $user_id User ID
 * @return false
 */
function learndash_save_enrolled_courses( $user_id ) {
	if ( ! current_user_can( 'enroll_users' ) ) {
		return FALSE;
	}

	$enroll_course = $_POST['enroll_course'];
	$unenroll_course = $_POST['unenroll_course'];

	if ( ! empty( $enroll_course ) ) {
		$meta = ld_update_course_access( $user_id, $enroll_course );
	}

	if ( ! empty( $unenroll_course ) ) {
		$meta = ld_update_course_access( $user_id, $unenroll_course, $remove = true );
	}
}

add_action( 'show_user_profile', 'learndash_show_enrolled_courses' );
add_action( 'edit_user_profile', 'learndash_show_enrolled_courses' );

add_action( 'personal_options_update', 'learndash_save_enrolled_courses' );
add_action( 'edit_user_profile_update', 'learndash_save_enrolled_courses' );



/**
 * Output link to delete course data for user
 *
 * @since 2.1.0
 * 
 * @param  object $user WP_User object
 */
function learndash_delete_user_data_link( $user ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return '';
	}

	?>
		<div id="learndash_delete_user_data">
			<h2><?php printf( __( 'Permanently Delete %s Data', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h2>
			<p><input type="checkbox" id="learndash_delete_user_data" name="learndash_delete_user_data" value="<?php echo $user->ID; ?>"> <label for="learndash_delete_user_data"><?php _e( 'Check and click update profile to permanently delete user\'s LearnDash course data. <strong>This cannot be undone.</strong>', 'learndash' ); ?></label></p>
		</div>
	<?php
}

add_action( 'show_user_profile', 'learndash_delete_user_data_link', 1000, 1 );
add_action( 'edit_user_profile', 'learndash_delete_user_data_link', 1000, 1 );
add_action( 'nss_license_footer','learndash_delete_user_data_link' );



/**
 * Delete user data
 * 
 * @since 2.1.0
 * 
 * @param  int $user_id
 */
function learndash_delete_user_data( $user_id ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	if ( ! empty( $user->ID ) && ! empty( $_POST['learndash_delete_user_data'] ) && $user->ID == $_POST['learndash_delete_user_data'] ) {
		global $wpdb;
		$ref_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT statistic_ref_id FROM '.$wpdb->prefix."wp_pro_quiz_statistic_ref WHERE  user_id = '%d' ", $user->ID ) );

		if ( ! empty( $ref_ids[0] ) ) {
			$wpdb->delete( $wpdb->prefix.'wp_pro_quiz_statistic_ref', array( 'user_id' => $user->ID ) );
			$wpdb->query( 'DELETE FROM '.$wpdb->prefix.'wp_pro_quiz_statistic WHERE statistic_ref_id IN ('.implode( ',', $ref_ids ).')' );
		}

		$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_sfwd-quizzes', 'user_id' => $user->ID ) );
		$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_sfwd-course_progress', 'user_id' => $user->ID ) );
		$wpdb->query( 'DELETE FROM '.$wpdb->usermeta." WHERE meta_key LIKE 'completed_%' AND user_id = '".$user->ID."'" );
		$wpdb->query( 'DELETE FROM '.$wpdb->usermeta." WHERE meta_key LIKE 'course_%_access_from' AND user_id = '".$user->ID."'" );
		$wpdb->query( 'DELETE FROM '.$wpdb->usermeta." WHERE meta_key LIKE 'course_completed_%' AND user_id = '".$user->ID."'" );
		$wpdb->delete( $wpdb->prefix.'wp_pro_quiz_lock', array( 'user_id' => $user->ID ) );
		$wpdb->delete( $wpdb->prefix.'wp_pro_quiz_toplist', array( 'user_id' => $user->ID ) );

		// Move user uploaded Assignements to Trash.
		$user_assignements_query_args = array(
			'post_type'		=>	'sfwd-assignment',
			'post_status'	=>	'publish',
			'nopaging'		=>	true,
			'author' 		=> 	$user->ID
		);
		
		$user_assignements_query = new WP_Query( $user_assignements_query_args );
		//error_log('user_assignements_query<pre>'. print_r($user_assignements_query, true) .'</pre>');
		if ( $user_assignements_query->have_posts() ) {
			
			foreach( $user_assignements_query->posts as $assignment_post ) {
				wp_trash_post( $assignment_post->ID );
			}
		}
		wp_reset_postdata();
	}	
}

add_action( 'personal_options_update', 'learndash_delete_user_data' );
add_action( 'edit_user_profile_update', 'learndash_delete_user_data' );