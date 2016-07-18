<?php
/**
 * Course Functions
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Course
 */



/**
 * Get course ID for resource.
 * 
 * Determine type of ID is being passed in.  Should be the ID of
 * anything that belongs to a course (Lesson, Topic, Quiz, etc)
 *
 * @since 2.1.0
 * 
 * @param  obj|int 	$id 	id of resource
 * @return string    		id of course
 */
function learndash_get_course_id( $id = null ) {
	global $post;

	if ( is_object( $id ) && $id->ID ) {
		$p = $id;
		$id = $p->ID;
	} else if ( is_numeric( $id ) ) {
		$p = get_post( $id );
	}

	if ( empty( $id ) ) {
		if ( ! is_single() || is_home() ) {
			return false;
		}

		$id = $post->ID;
		$p = $post;
	}

	if ( empty( $p->ID ) ) {
		return 0;
	}

	if ( $p->post_type == 'sfwd-courses' ) {
		return $p->ID;
	}

	return get_post_meta( $id, 'course_id', true );
}



/**
 * Get course ID for resource (legacy users)
 * 
 * Determine type of ID is being passed in.  Should be the ID of
 * anything that belongs to a course (Lesson, Topic, Quiz, etc)
 * 
 * @since 2.1.0
 * 
 * @param  obj|int 	$id 	id of resource
 * @return string    		id of course
 */
function learndash_get_legacy_course_id( $id = null ){
	global $post;

	if ( empty( $id ) ) {
		if ( ! is_single() || is_home() ) {
			return false;
		}

		$id = $post->ID;
	}

	$terms = wp_get_post_terms( $id, 'courses' );

	if ( empty( $terms) || empty( $terms[0] ) || empty( $terms[0]->slug) ) {
		return 0;
	}

	$courseslug = $terms[0]->slug;

	global $wpdb;

	$term_taxonomy_id = $wpdb->get_var(
		$wpdb->prepare(
			"
		 SELECT `term_taxonomy_id` FROM $wpdb->term_taxonomy tt, $wpdb->terms t 
		 WHERE slug = %s 
		 AND t.term_id = tt.term_id
		 AND tt.taxonomy = 'courses'
		",
			$courseslug
		)
	);

	$course_id = $wpdb->get_var(
		$wpdb->prepare(
			"
		 SELECT `ID` FROM $wpdb->term_relationships, $wpdb->posts 
		 WHERE `ID` = `object_id`
		 AND `term_taxonomy_id` = %d
		 AND `post_type` = 'sfwd-courses'
		 AND `post_status` = 'publish' 
		",
			$term_taxonomy_id
		)
	);

	return $course_id;
}



/**
 * Get lesson id of resource
 *
 * @since 2.1.0
 * 
 * @param  int 		$id  post id of resource
 * @return string     	 lesson id
 */
function learndash_get_lesson_id( $id = null ) {
	global $post;

	if ( empty( $id ) ) {
		if ( ! is_single() || is_home() ) {
			return false;
		}

		$id = $post->ID;
	}

	return get_post_meta( $id, 'lesson_id', true );
}


/**
 * Get array of courses that user has access to
 *
 * @since 2.1.0
 * 
 * @param  int 		$user_id
 * @return array    array of courses that user has access to
 */
function ld_get_mycourses( $user_id = null ) {
	$filter = array(
		'post_type' => 'sfwd-courses', 
		'posts_per_page' => - 1, 
		'post_status' => 'publish'
	);
	
	$loop = new WP_Query( $filter );
	$mycourses = array();

	while ( $loop->have_posts() ) {
		$loop->the_post();

		if ( sfwd_lms_has_access( get_the_ID(), $user_id ) ) {
			$mycourses[] = get_the_ID();
		}
	}

	wp_reset_query();
	return $mycourses;
}


/**
 * Does user have access to course (houses filter)
 * 
 * @since 2.1.0
 * 
 * @param  int 	$post_id 	id of resource
 * @param  int 	$user_id
 * @return bool       
 */
function sfwd_lms_has_access( $post_id, $user_id = null ) {

	 /**
	 * Filter if user has access to course
	 *
	 * Calls sfwd_lms_has_access_fn() to determine if user has access to course
	 * 
	 * @since 2.1.0
	 * 
	 * @param  bool
	 */
	return apply_filters( 'sfwd_lms_has_access', sfwd_lms_has_access_fn( $post_id, $user_id ), $post_id, $user_id );
}



/**
 * Does user have access to course
 * 
 * Check's if user has access to course when they try to access a resource that
 * belong to that course (Lesson, Topic, Quiz, etc.)
 *
 * @since 2.1.0
 * 
 * @param  int 	$post_id 	id of resource
 * @param  int 	$user_id
 * @return bool  
 */
function sfwd_lms_has_access_fn( $post_id, $user_id = null ) {

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}

	$course_id = learndash_get_course_id( $post_id );

	if ( empty( $course_id ) ) {
		return true;
	}

	if ( ! empty( $post_id ) && learndash_is_sample( $post_id ) ) {
		return true;
	}

	$meta = get_post_meta( $course_id, '_sfwd-courses', true );

	if ( @$meta['sfwd-courses_course_price_type'] == 'open' || @$meta['sfwd-courses_course_price_type'] == 'paynow' && empty( $meta['sfwd-courses_course_join'] ) && empty( $meta['sfwd-courses_course_price'] ) ) {
		return true;
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	if ( ! empty( $meta['sfwd-courses_course_access_list'] ) ) {
		$course_access_list = explode( ',', $meta['sfwd-courses_course_access_list'] );
	} else {
		$course_access_list = array();
	}

	if ( in_array( $user_id, $course_access_list )  || learndash_user_group_enrolled_to_course( $user_id, $course_id ) ) {
		$expired = ld_course_access_expired( $course_id, $user_id );
		return ! $expired; //True if not expired.
	} else {
		return false;
	}
}



/**
 * Redirect user to course
 *
 * @since 2.1.0
 * 
 * @param  int 	$post_id  id of resource that belongs to a course
 */
function sfwd_lms_access_redirect( $post_id ) {
	$access = sfwd_lms_has_access( $post_id );
	if ( $access === true ) {
		return true;
	}

	$link = get_permalink( learndash_get_course_id( $post_id ) );
	$link = apply_filters( 'learndash_access_redirect' , $link, $post_id );
	wp_redirect( $link );
	exit();
}



/**
 * Is users access to course expired
 *
 * @since 2.1.0
 * 
 * @param  int 	$course_id
 * @param  int 	$user_id  
 * @return bool           
 */
function ld_course_access_expired( $course_id, $user_id ) {
	$course_access_upto = ld_course_access_expires_on( $course_id, $user_id );

	if ( empty( $course_access_upto ) ) {
		return false;
	} else {

		if ( time() >= $course_access_upto ) {
			update_user_meta( $user_id, 'learndash_course_expired_'.$course_id, 1 );
			ld_update_course_access( $user_id, $course_id, $remove = true );
			$delete_course_progress = learndash_get_setting( $course_id, 'expire_access_delete_progress' );
			if ( ! empty( $delete_course_progress) ) {
				learndash_delete_course_progress( $course_id, $user_id );
			}
			return true;
		} else {
			return false;
		}

	}	 
}



/**
 * Generate alert in wp_head that users access to course is expired
 *
 * @since 2.1.0
 */
function ld_course_access_expired_alert() {
	global $post;

	if ( ! is_singular() || empty( $post->ID ) || $post->post_type != 'sfwd-courses' ) {
		return;
	}

	$user_id = get_current_user_id();

	if ( empty( $user_id ) ) {
		return;
	}

	$expired = get_user_meta( $user_id, 'learndash_course_expired_'.$post->ID, true );

	if ( empty( $expired) ) {
		return;
	}

	$has_access = sfwd_lms_has_access( $post->ID, $user_id );

	if ( $has_access ) {
		delete_user_meta( $user_id, 'learndash_course_expired_'.$post->ID );
		return;
	} else	{
		?>
		<script>
			setTimeout(function() {
				alert("<?php _e( 'Your access to this course has expired.', 'learndash' ); ?>")
			}, 2000);
		</script>
		<?php
	}
}

add_action( 'wp_head', 'ld_course_access_expired_alert', 1 );



/**
 * Get amount of time until users course access expires for user
 *
 * @since 2.1.0
 * 
 * @param  int 	$course_id
 * @param  int 	$user_id  
 * @return int  
 */
function ld_course_access_expires_on( $course_id, $user_id ) {
	$couses_access_from = ld_course_access_from( $course_id, $user_id );

	if ( empty( $couses_access_from ) ) {
		$couses_access_from = learndash_user_group_enrolled_to_course_from( $user_id, $course_id );
	}

	$expire_access = learndash_get_setting( $course_id, 'expire_access' );

	if ( empty( $expire_access ) ) {
		return 0;
	}

	$expire_access_days = learndash_get_setting( $course_id, 'expire_access_days' );
	$course_access_upto = $couses_access_from + $expire_access_days * 24 * 60 * 60;
	return $course_access_upto;
}



/**
 * Get amount of time when lesson becomes available to user
 *
 * @since 2.1.0
 * 
 * @param  int $course_id
 * @param  int $user_id  
 * @return string
 */
function ld_course_access_from( $course_id, $user_id ) {
	return get_user_meta( $user_id, 'course_'.$course_id.'_access_from', true );
}



/**
 * Update list of courses users has access to
 *
 * @since 2.1.0
 * 
 * @param  int 		$user_id   
 * @param  int 	 	$course_id 
 * @param  bool 	$remove    
 * @return array   list of courses users has access to
 */
function ld_update_course_access( $user_id, $course_id, $remove = false ) {
	if ( empty( $user_id ) || empty( $course_id ) ) {
		return;
	}

	$meta = get_post_meta( $course_id, '_sfwd-courses', true );
	$access_list = $meta['sfwd-courses_course_access_list'];

	if ( empty( $remove ) ) {

		if ( empty( $access_list ) ) {
			$access_list = $user_id;
		} else {
			$access_list_arr = explode( ',', $access_list );
			$access_list_arr = array_map( 'intVal', $access_list_arr );
			$access_list_arr[] = $user_id;
			$access_list_arr = array_unique( $access_list_arr );
			$access_list = implode( ',', $access_list_arr );
		}

		$current_access = get_user_meta( $user_id, "course_".$course_id."_access_from", true );

		if ( empty( $current_access ) ) {
			update_user_meta( $user_id, "course_".$course_id."_access_from", time() );
		}

	} else if ( ! empty( $access_list ) ) {

		$access_list = explode( ',', $access_list );
		$new_access_list = array();
		foreach ( $access_list as $c ) {
			if ( trim( $c ) != $user_id ) {
				$new_access_list[] = trim( $c );
			}
		}
		$access_list = implode( ',', $new_access_list );
		delete_user_meta( $user_id, 'course_'.$course_id.'_access_from' );

	}

	$meta['sfwd-courses_course_access_list'] = $access_list;
	update_post_meta( $course_id, '_sfwd-courses', $meta );

	/**
	 * Run actions after a users list of courses is updated
	 * 
	 * @since 2.1.0
	 * 
	 * @param  int  	$user_id 		
	 * @param  int  	$course_id
	 * @param  array  	$access_list
	 * @param  bool  	$remove
	 */
	do_action( 'learndash_update_course_access', $user_id, $course_id, $access_list, $remove );

	return $meta;	
}



/**
 * Get timestamp of when user has access to lesson
 *
 * @since 2.1.0
 * 
 * @param  int 	$lesson_id
 * @param  int 	$user_id  
 * @return int  timestamp
 */
function ld_lesson_access_from( $lesson_id, $user_id ) {
	$course_id = learndash_get_course_id( $lesson_id );
	$couses_access_from = ld_course_access_from( $course_id, $user_id );

	if ( empty( $couses_access_from ) ) {
		$couses_access_from = learndash_user_group_enrolled_to_course_from( $user_id, $course_id );
	}

	$visible_after = learndash_get_setting( $lesson_id, 'visible_after' );

	if ( $visible_after > 0 ) {
		$lesson_access_from = $couses_access_from + $visible_after * 24 * 60 * 60;

		if ( time() >= $lesson_access_from ) {
			$return = null;
		} else {
			$return = $lesson_access_from;
		}		
	} else {
		$visible_after_specific_date = learndash_get_setting( $lesson_id, 'visible_after_specific_date' );
		$specific_date = strtotime( $visible_after_specific_date );

		if ( time() > $specific_date ) {
			$return = null;
		} else {
			$return = $specific_date;
		}
	}

	return apply_filters( 'ld_lesson_access_from', $return, $lesson_id, $user_id );
}



/**
 * Display when lesson will be available
 *
 * @since 2.1.0
 * 
 * @param  string $content content of lesson
 * @param  object $post    WP_Post object
 * @return string          when lesson will be available
 */
function lesson_visible_after( $content, $post ) {	
	if ( empty( $post->post_type ) ) {
		return $content; 
	}

	if ( $post->post_type == 'sfwd-lessons' ) {
		$lesson_id = $post->ID; 
	} else {
		if ( $post->post_type == 'sfwd-topic' || $post->post_type == 'sfwd-quiz' ) {
			$lesson_id = learndash_get_setting( $post, 'lesson' );	
			if ( empty( $lesson_id ) ) {
				return $content; 
			}
		} else {
			return $content; 
		}
	}

	$lesson_access_from = ld_lesson_access_from( $lesson_id, get_current_user_id() );

	if ( empty( $lesson_access_from) ) {
		return $content; 
	} else {
		$content = sprintf( __( ' Available on: %s ', 'learndash' ), date_i18n( 'd-M-Y', $lesson_access_from ) ).'<br><br>';
		$course_id = learndash_get_course_id( $lesson_id );
		$course_link = get_permalink( $course_id );
		$content .= "<a href='".$course_link."'>". sprintf( __( 'Return to %s Overview', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ) . '</a>';

		 /**
		 * Filter content of lesson available text
		 * 
		 * @since 2.1.0
		 * 
		 * @param  string  $content
		 */
		return "<div class='notavailable_message'>".apply_filters( 'leardash_lesson_available_from_text', $content, $post, $lesson_access_from ).'</div>';
	}

	return $content;
}

add_filter( 'learndash_content', 'lesson_visible_after', 1, 2 );



/**
 * Is users course prerequisites completed for a given course
 *
 * @since 2.1.0
 * 
 * @param  int  	$id  course id
 * @return boolean 
 */
function is_course_prerequities_completed( $id ){
	global $wp;
	$current_user = wp_get_current_user();
	$course_pre = learndash_get_course_prerequisite( $id );

	if ( ! empty( $course_pre ) ){
		//Now check if the prerequities course is completed by user or not
		$course_status = learndash_course_status( $course_pre, null );

		if ( $course_status == __( 'Completed','learndash' ) ) { 
			return true;
		} else { 
			return false;
		}
	} else {
		return true;
	}
}



/**
 * Get list of course prerequisites for a given course
 *
 * @since 2.1.0
 * 
 * @param  int 	 $id  course id
 * @return array      list of courses
 */
function learndash_get_course_prerequisite( $id ) {
	$id = learndash_get_course_id( $id );
	$post_options = get_post_meta( $id, '_sfwd-courses', true );
	$course_pre = isset( $post_options['sfwd-courses_course_prerequisite'] ) ? $post_options['sfwd-courses_course_prerequisite'] : 0;
	return $course_pre;
}



/**
 * Handles actions to be made when user joins a course
 *
 * Redirects user to login url, adds course access to user
 * 
 * @since 2.1.0
 */
function learndash_process_course_join(){
	if ( ! isset( $_POST['course_join'] ) || ! isset( $_POST['course_id'] ) ) {
		return;
	}

	$user_id = get_current_user_id();

	if ( empty( $user_id ) ) {
		$login_url = wp_login_url();

		 /**
		 * Filter URL of where user should be redirected to
		 * 
		 * @since 2.1.0
		 * 
		 * @param  login_url  $login_url
		 */
		$login_url = apply_filters( 'learndash_course_join_redirect', $login_url, $_POST['course_id'] );
		wp_redirect( $login_url );
		exit;
	}

	$course_id = $_POST['course_id'];
	$meta = get_post_meta( $course_id, '_sfwd-courses', true );

	if ( @$meta['sfwd-courses_course_price_type'] == 'free' || @$meta['sfwd-courses_course_price_type'] == 'paynow' && empty( $meta['sfwd-courses_course_price'] ) && ! empty( $meta['sfwd-courses_course_join'] ) || sfwd_lms_has_access( $course_id, $user_id ) ) {
		ld_update_course_access( $user_id, $course_id );
	}
}

add_action( 'wp', 'learndash_process_course_join' );



/**
 * Shortcode to output course content
 *
 * @since 2.1.0
 * 
 * @param  array 	$atts 	shortcode attributes
 * @return string       	output of shortcode
 */
function learndash_course_content_shortcode( $atts ) {
	if ( empty( $atts['course_id'] ) ) {
		return '';
	}

	$course_id = $atts['course_id'];

	$course = $post = get_post( $course_id );

	if ( ! is_singular() || $post->post_type != 'sfwd-courses' ) {
		return '';
	}

	$current_user = wp_get_current_user();

	$user_id = $current_user->ID;
	$logged_in = ! empty( $user_id );
	$lesson_progression_enabled = false;

	$course_settings = learndash_get_setting( $course );
	$lesson_progression_enabled  = learndash_lesson_progression_enabled();
	$courses_options = learndash_get_option( 'sfwd-courses' );
	$lessons_options = learndash_get_option( 'sfwd-lessons' );
	$quizzes_options = learndash_get_option( 'sfwd-quiz' );
	$course_status = learndash_course_status( $course_id, null );
	$has_access = sfwd_lms_has_access( $course_id, $user_id );

	$lessons = learndash_get_course_lessons_list( $course );
	$quizzes = learndash_get_course_quiz_list( $course );
	$has_course_content = ( ! empty( $lessons ) || ! empty( $quizzes ) );

	$has_topics = false;

	if ( ! empty( $lessons) ) {
		foreach ( $lessons as $lesson ) {
			$lesson_topics[ $lesson['post']->ID ] = learndash_topic_dots( $lesson['post']->ID, false, 'array' );
			if ( ! empty( $lesson_topics[ $lesson['post']->ID ] ) ) {
				$has_topics = true;
			}
		}
	}

	$level = ob_get_level();
	ob_start();
	include( SFWD_LMS::get_template( 'course_content_shortcode', null, null, true ) );
	$content = learndash_ob_get_clean( $level );
	$content = str_replace( array("\n", "\r"), ' ', $content );
	$user_has_access = $has_access? 'user_has_access':'user_has_no_access';

	/**
	 * Filter course content shortcode
	 * 
	 * @since 2.1.0
	 */
	return '<div class="learndash '.$user_has_access.'" id="learndash_post_'.$course_id.'">'.apply_filters( 'learndash_content', $content, $post ).'</div>';
}

add_shortcode( 'course_content', 'learndash_course_content_shortcode' );
