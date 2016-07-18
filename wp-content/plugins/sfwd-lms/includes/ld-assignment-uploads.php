<?php
/**
 * Handles assignment uploads and includes helper functions for assignments
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Assignments
 */



/**
 * Upload, delete, and mark assignments as complete
 *
 * @since 2.1.0
 */
function learndash_assignment_process_init() {
	
	if ( isset( $_POST['uploadfile'] ) && isset( $_POST['post'] ) ) {
		$post_id = $_POST['post'];
		$file = $_FILES['uploadfiles'];
		$name = $file['name'];

		if ( ! empty( $file['name'][0] ) ) {
			$file_desc = learndash_fileupload_process( $file, $post_id );
			$file_name = $file_desc['filename'];
			$file_link = $file_desc['filelink'];
			$params = array(
				'filelink' => $file_link, 
				'filename' => $file_name,
			);
		}
	}
	
	if ( ! empty( $_GET['learndash_delete_attachment'] ) ) {
		$post = get_post( $_GET['learndash_delete_attachment'] );

		if ( $post->post_type != 'sfwd-assignment' ) {
			return;
		}

		$current_user_id = get_current_user_id();

		if ( current_user_can( 'manage_options' ) || learndash_is_group_leader_of_user( $current_user_id, $post->post_author ) ) {
			wp_delete_post( $post->ID );
			return;
		}
	}
	
	if ( ! empty( $_POST['attachment_mark_complete'] ) && ! empty( $_POST['userid'] ) ) {
		$lesson_id = $_POST['attachment_mark_complete'];
		$current_user_id = get_current_user_id();
		$user_id = $_POST['userid'];

		if ( current_user_can( 'manage_options' ) || learndash_is_group_leader_of_user( $current_user_id, $user_id ) ) {
			learndash_approve_assignment( $user_id, $lesson_id );
		}
	}
}

add_action( 'parse_request', 'learndash_assignment_process_init', 1 );



/**
 * Get a users assignments
 * 
 * @since 2.1.0
 * 
 * @param  int 	 $post_id
 * @param  int 	 $user_id
 * @return array WP_Post Assigment objects
 */
function learndash_get_user_assignments( $post_id, $user_id ) {
	$opt = array(
		'post_type' => 'sfwd-assignment', 
		'posts_per_page' => - 1, 
		'author' => $user_id, 
		'meta_key' => 'lesson_id', 
		'meta_value' => $post_id,
	);

	return get_posts( $opt );
}



/**
 * Migrate assignments from post meta to Assignments custom post type
 * 
 * @since 2.1.0
 */
function learndash_assignment_migration() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	global $wpdb;
	$old_assignment_ids = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'sfwd_lessons-assignment'" );
	
	if ( ! empty( $old_assignment_ids ) && ! empty( $old_assignment_ids[0] ) ) {

		foreach( $old_assignment_ids as $post_id ) {
			$assignment_meta_data = get_post_meta( $post_id, 'sfwd_lessons-assignment', true );

			if ( ! empty( $assignment_meta_data ) && ! empty( $assignment_meta_data['assignment'] ) ) {
				$assignment_data = $assignment_meta_data['assignment'];
				$post = get_post( $post_id );
				$assignment_posts_ids = array();

				if ( ! empty( $assignment_data ) ) {
					$error = false;

					foreach( $assignment_data as $k => $v ) {

						if ( empty( $v['file_name'] ) ) {
							continue;
						}
						
						$fname = $v['file_name'];
						$dest = $v['file_link'];
						$username = $v['user_name'];
						$dispname = $v['disp_name'];
						$file_path = $v['file_path'];

						if ( ! empty( $v['user_name'] ) ) {
							$user = get_user_by( 'login', $v['user_name'] );
						}

						$course_id = learndash_get_course_id( $post->ID );

						$assignment_meta = array(
							'file_name' => $fname, 
							'file_link' => $dest, 
							'user_name' => $username, 
							'disp_name' => $dispname, 
							'file_path' => $file_path, 
							'user_id' => @$user->ID, 
							'lesson_id' => $post->ID, 
							'course_id' => $course_id, 
							'lesson_title' => $post->post_title, 
							'lesson_type' => $post->post_type, 
							'migrated' => '1',
						);
					   
						$assignment = array(
							'post_title' => $fname, 
							'post_type' => 'sfwd-assignment', 
							'post_status' => 'publish', 
							'post_content' => "<a href='" . $dest . "' target='_blank'>" . $fname . '</a>', 
							'post_author' => @$user->ID,
						);

						$assignment_post_id = wp_insert_post( $assignment );

						if ( $assignment_post_id ) {
							$assignment_posts_ids[] = $assignment_post_id;

							foreach( $assignment_meta as $key => $value ) {
								update_post_meta( $assignment_post_id, $key, $value );
							}

							if ( learndash_is_assignment_approved( $assignment_post_id ) === true ) {
								learndash_approve_assignment_by_id( $assignment_post_id );
							}
						} else {
							$error = true;

							foreach( $assignment_posts_ids as $assignment_posts_id ) {
								wp_delete_post( $assignment_posts_id, true );
							}

							break;
						}

					}

					if ( ! $error ) {
						global $wpdb;
						$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = 'sfwd_lessons-assignment_migrated' WHERE meta_key = 'sfwd_lessons-assignment' AND post_id = '$post_id'" );
					}

				}
			}
		}
	}
}

add_action( 'admin_init', 'learndash_assignment_migration' );



/**
 * Get list of all assignments
 *
 * @todo  first argument not used
 * @since 2.1.0
 * 
 * @param  object 	 $post 	not used
 * @return array     $posts array of post objects
 */
function learndash_get_assignments_list( $post ) {
	$posts = get_posts( 'post_type=sfwd-assignment&posts_per_page=-1' );

	if ( ! empty( $posts ) ) {

		foreach( $posts as $key => $p ) {
			$meta = get_post_meta( $p->ID, '', true );

			foreach( $meta as $meta_key => $value ) {

				if ( is_string( $value ) || is_numeric( $value ) ) {
					$posts[ $key ]->{$meta_key} = $value;
				} else if ( is_string( $value[0] ) || is_numeric( $value[0] ) ) {
					$posts[ $key ]->{$meta_key} = $value[0];
				}
				
				if ( $meta_key == 'file_path' ) {
					$posts[ $key ]->{$meta_key} = rawurldecode( $posts[ $key ]->{$meta_key} );
				}

			}
		}
	}
	
	return $posts;
}



/**
 * Function to handle assignment uploads
 * Takes Post ID, filename as arguments(We don't want to store BLOB data there)
 *
 * @todo  How is this different from learndash_assignment_process_init() ?
 *
 * @since 2.1.0
 * 
 * @param  int $post_id 
 * @param  int $fname    filename
 */
function learndash_upload_assignment_init( $post_id, $fname ) {
	//Initialize an empty array
	global $wp;

	if ( ! function_exists( 'wp_get_current_user' ) ) {
		include ABSPATH . 'wp-includes/pluggable.php';
	}

	$new_assignmnt_meta = array();
	$current_user = wp_get_current_user();
	$username = $current_user->user_login;
	$dispname = $current_user->display_name;
	$userid = $current_user->ID;
	$url_link_arr = wp_upload_dir();
	$url_link = $url_link_arr['baseurl'];
	$dir_link = $url_link_arr['basedir'];
	$file_path = $dir_link . '/assignments/';
	$url_path = $url_link . '/assignments/' . $fname;

	if ( file_exists( $file_path . $fname ) ) {
		$dest = $url_path;
	} else {
		return;
	}
	
	update_post_meta ($post_id, 'sfwd_lessons-assignment', $new_assignmnt_meta );
	$post = get_post( $post_id );
	$course_id = learndash_get_course_id( $post->ID );

	$assignment_meta = array(
		'file_name' => $fname, 
		'file_link' => $dest, 
		'user_name' => $username, 
		'disp_name' => $dispname, 
		'file_path' => rawurlencode( $file_path . $fname ), 
		'user_id' => $current_user->ID, 
		'lesson_id' => $post->ID, 
		'course_id' => $course_id, 
		'lesson_title' => $post->post_title, 
		'lesson_type' => $post->post_type,
	);

	$assignment = array(
		'post_title' => $fname, 
		'post_type' => 'sfwd-assignment', 
		'post_status' => 'publish', 
		'post_content' => "<a href='" . $dest . "' target='_blank'>" . $fname . '</a>', 
		'post_author' => $current_user->ID,
	);

	$assignment_post_id = wp_insert_post( $assignment );

	if ( $assignment_post_id ) {
		foreach ( $assignment_meta as $key => $value ) {
			update_post_meta( $assignment_post_id, $key, $value );
		}
	}
	
	$auto_approve = learndash_get_setting( $post, 'auto_approve_assignment' );

	if ( $auto_approve ) {
		learndash_approve_assignment( $current_user->ID, $post_id );
		learndash_get_next_lesson_redirect( $post );
	}
}



/**
 * Ensure comments are open for assignments
 * 
 * @since 2.1.0
 * 
 * @param bool        	$open    Whether the current post is open for comments.
 * @param int|obj		$post_id The post ID or WP_Post object.
 * @return int|obj		$post_id The post ID or WP_Post object.
 */
function learndash_assignments_comments_open( $open, $post_id ) {
	$post = get_post( $post_id );

	if ( empty( $open ) && @$post->post_type == 'sfwd-assignment' ) {
		if ( is_numeric( $post_id ) ) {
			global $wpdb;
			$wpdb->query( "UPDATE $wpdb->posts SET comment_status = 'open' WHERE ID = '" . $post_id . "'" );
			$open = true;
		}
	}
	
	return $open;
}

add_filter( 'comments_open', 'learndash_assignments_comments_open', 10, 2 );



/**
 * Enable comments when adding new assignment
 * 
 * @since 2.1.0
 * 
 * @param  array $data post data
 * @return array $data post data
 */
function learndash_assignments_comments_on( $data ) {
	if ( $data['post_type'] == 'sfwd-assignment' ) {
		$data['comment_status'] = 'open';
	}

	return $data;
}

add_filter( 'wp_insert_post_data', 'learndash_assignments_comments_on' );



/**
 * Ensure clean filename on upload
 * 
 * @since 2.1.0
 * 
 * @param  string $string file name
 * @return string         clean file name
 */
function learndash_clean_filename( $string ) {
	$string = htmlentities( $string, ENT_QUOTES, 'UTF-8' );
	$string = preg_replace( '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string );
	$string = html_entity_decode( $string, ENT_QUOTES, 'UTF-8' );
	$string = preg_replace( array('~[^0-9a-z.]~i', '~[ -]+~'), ' ', $string );
	$string = str_replace( ' ', '_', $string );
	return trim( $string, ' -' );
}



/**
 * Upload files
 * 
 * @since 2.1.0
 * 
 * @param  array 	$uploadfiles
 * @param  int 		$post_id     assignment id
 * @return array    file description
 */
function learndash_fileupload_process( $uploadfiles, $post_id ) {
	if ( is_array( $uploadfiles ) ) {
		
		foreach( $uploadfiles['name'] as $key => $value ) {
			// look only for uploded files
			if ( $uploadfiles['error'][ $key ] == 0 ) {
				
				$filetmp = $uploadfiles['tmp_name'][ $key ];

				//clean filename
				$filename = learndash_clean_filename( $uploadfiles['name'][ $key ] );

				//extract extension
				if ( ! function_exists( 'wp_get_current_user' ) ) {
					include ABSPATH . 'wp-includes/pluggable.php';
				}

				// get file info
				// @fixme: wp checks the file extension....
				$filetype = wp_check_filetype( basename( $filename ), null );
				$filetitle = preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
				$filename = $filetitle . '.' . $filetype['ext'];
				$upload_dir = wp_upload_dir();
				$upload_dir_base = $upload_dir['basedir'];
				$upload_url_base = $upload_dir['baseurl'];
				$upload_dir_path = $upload_dir_base . '/assignments';
				$upload_url_path = $upload_url_base . '/assignments/';

				if ( ! file_exists( $upload_dir_path ) ) {
					mkdir( $upload_dir_path );
				}

				/**
				 * Check if the filename already exist in the directory and rename the
				 * file if necessary
				 */
				$i = 0;

				while ( file_exists( $upload_dir_path . '/' . $filename ) ) {
					$i++;
					$filename = $filetitle . '_' . $i . '.' . $filetype['ext'];
				}

				$filedest = $upload_dir_path . '/' . $filename;
				$destination = $upload_url_path . $filename;

				/**
				 * Check write permissions
				 */
				if ( ! is_writeable( $upload_dir_path ) ) {
					die( __( 'Unable to write to directory. Is this directory writable by the server?', 'learndash' ) );
					return;
				}

				/**
				 * Save temporary file to uploads dir
				 */
				if ( ! @move_uploaded_file( $filetmp, $filedest ) ) {
					echo( "Error, the file $filetmp could not moved to : $filedest " );
					continue;
				}

				/**
				 * Add upload meta to database
				 *
				 */
				learndash_upload_assignment_init( $post_id, $filename, $filedest );
				$file_desc = array();
				$file_desc['filename'] = $filename;
				$file_desc['filelink'] = $destination;
				return $file_desc;
			}
		}

	}
}



/**
 * Does lesson have assignments
 * 
 * @since 2.1.0
 * 
 * @param  object $post WP_Post assignment
 * @return bool
 */
function lesson_hasassignments( $post ) {
	$post_id = $post->ID;
	$assign_meta = get_post_meta( $post_id, '_' . $post->post_type, true );

	if ( ! empty( $assign_meta[ $post->post_type . '_lesson_assignment_upload'] ) ) {
		$val = $assign_meta[ $post->post_type . '_lesson_assignment_upload'];

		if ( $val == 'on' ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}



/**
 * Add 'Approve' next to certain selects on assignment edit screen in admin
 *
 * @todo  check if needed, jQuery selector seems incorrect
 * 
 * @since 2.1.0
 */
function learndash_assignment_bulk_actions() {
	global $post;
	if ( ! empty( $post->post_type ) && $post->post_type == 'sfwd-assignment' ) {
		?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('approve').text('Approve').appendTo("select[name='action']");
					jQuery('<option>').val('approve').text('Approve').appendTo("select[name='action2']");
				});
			</script>
		<?php
	}
}

add_action( 'admin_footer', 'learndash_assignment_bulk_actions' );



/**
 * Handle approval of assignments in bulk
 * 
 * @since 2.1.0
 */
function learndash_assignment_bulk_actions_approve() {
	
	if ( ( ! empty( $_REQUEST['post'] ) && $_REQUEST['post_type'] == 'sfwd-assignment' && $_REQUEST['action2'] == '-1' && $_REQUEST['action'] == 'approve' ) || ( ! empty( $_REQUEST['post'] ) && $_REQUEST['ld_action'] == 'approve_assignment' ) ) {

		$assignments = $_REQUEST['post'];
		$approval_ids = array();

		foreach( $assignments as $assignment ) {
			$assignment_post = get_post( $assignment );
			$user_id = $assignment_post->post_author;
			$lesson_id = get_post_meta( $assignment_post->ID, 'lesson_id', true );
			
			if ( empty( $approval_ids[ $user_id ] ) || empty( $approval_ids[ $user_id ][ $lesson_id ] ) ) {
				learndash_approve_assignment( $user_id, $lesson_id );
			}
		}

		if ( ! empty( $_REQUEST['ret_url'] ) ) {
			header( 'Location: ' . rawurldecode( $_REQUEST['ret_url'] ) );
			exit;
		}
	}
}

add_action( 'load-edit.php', 'learndash_assignment_bulk_actions_approve' );



/**
 * Approve assignment by id for user
 * 
 * @since 2.1.0
 * 
 * @param  int $assignment_id
 * @return bool
 */
function learndash_approve_assignment_by_id( $assignment_id ) {
	$assignment_post = get_post( $assignment );
	$user_id = $assignment_post->post_author;
	$lesson_id = get_post_meta( $assignment_post->ID, 'lesson_id', true );
	return learndash_approve_assignment( $user_id, $lesson_id );
}



/**
 * Mark assignment true with user id and lesson id
 * 
 * @since 2.1.0
 * 
 * @param  int $user_id
 * @param  int $lesson_id
 * @return bool
 */
function learndash_approve_assignment( $user_id, $lesson_id ) {

	/**
	 * Filter whether assignmnent should be approved or not
	 * 
	 * @since 2.1.0
	 * 
	 * @param  bool
	 */
	$learndash_approve_assignment = apply_filters( 'learndash_approve_assignment', true, $user_id, $lesson_id );

	if ( $learndash_approve_assignment ) {
		$learndash_process_mark_complete = learndash_process_mark_complete( $user_id, $lesson_id );
		//echo $learndash_process_mark_complete;
		
		if ( $learndash_process_mark_complete ) {
			global $wpdb;
			$assignment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'lesson_id' AND meta_value = %d", $lesson_id ) );
			
			foreach( $assignment_ids as $assignment_id ) {
				$assignment = get_post( $assignment_id );
				if ( $assignment->post_author == $user_id ) {
					learndash_assignment_mark_approved( $assignment_id );
				}
			}
		}

		return $learndash_process_mark_complete;
	}
}



/**
 * Update assignments post meta with approval status
 * 
 * @since 2.1.0
 * 
 * @param  int $assignment_id
 */
function learndash_assignment_mark_approved( $assignment_id ) {
	update_post_meta( $assignment_id, 'approval_status', 1 );
}



/**
 * Get assignments approval status
 * 
 * @since 2.1.0
 * 
 * @param  int $assignment_id
 * @return bool
 */
function learndash_is_assignment_approved_by_meta( $assignment_id ) {
	return get_post_meta( $assignment_id, 'approval_status', true );
}



/**
 * Adds inline actions to assignments on post listing hover in admin
 * 
 * @since 2.1.0
 * 
 * @param  array 	$actions 	post actions
 * @param  object 	$post   	WP_Post assignment
 * @return array 	$actions 	post actions
 */
function learndash_assignment_inline_actions( $actions, $post ) {
	if ( $post->post_type == 'sfwd-assignment' ) {
		$download_link = get_post_meta( $post->ID, 'file_link', true );
		$actions['download_assignment'] = "<a href='" . $download_link . "' target='_blank'>" . __( 'Download', 'learndash' ) . '</a>';
		$learndash_assignment_approval_link = learndash_assignment_approval_link( $post->ID );
		
		if ( $learndash_assignment_approval_link ) {
			$actions['approve_assignment'] = "<a href='" . $learndash_assignment_approval_link . "' >" . __( 'Approve', 'learndash' ) . '</a>';
		}
	}

	return $actions;
}

add_filter( 'post_row_actions', 'learndash_assignment_inline_actions', 10, 2 );



/**
 * Restrict assignment listings view to group leader only
 * 
 * @since 2.1.0
 * 
 * @param  object 	$query 	WP_Query
 * @return object 	$query 	WP_Query
 */
function learndash_restrict_assignment_listings( $query ) {
	global $pagenow;
	$q_vars = & $query->query_vars;

	if ( is_admin() AND !current_user_can( 'manage_options' ) AND $pagenow == 'edit.php' AND $query->query['post_type'] == 'sfwd-assignment' ) {
		$user_id = get_current_user_id();

		if ( is_group_leader( $user_id ) ) {
			$group_ids = learndash_get_administrators_group_ids( $user_id );
			$user_ids = array();

			if ( ! empty( $group_ids ) && is_array( $group_ids ) ) {
				foreach( $group_ids as $group_id ) {
					$group_users = learndash_get_groups_user_ids( $group_id );

					if ( ! empty( $group_users ) && is_array( $group_users ) ) {
						foreach( $group_users as $group_user_id ) {
							$user_ids[ $group_user_id ] = $group_user_id;
						}
					}
				}
			}
			
			if ( ! empty( $user_ids ) && count( $user_ids ) ) {
				$q_vars['author__in'] = $user_ids;
			} else {
				$q_vars['author__in'] = - 2;
			}
		}
	}
}

add_filter( 'parse_query', 'learndash_restrict_assignment_listings' );



/**
 * Check if assignment is completed
 * 
 * @since 2.1.0
 * 
 * @param  int 		$assignment_id 		
 * @return bool
 */
function learndash_is_assignment_approved( $assignment_id ) {
	$assignment = get_post( $assignment_id );

	if ( empty( $assignment->ID ) ) {
		return '';
	}
	
	$lesson_id = learndash_get_lesson_id( $assignment->ID );
	
	if ( empty( $lesson_id ) ) {
		return '';
	}
	
	$lesson_completed = learndash_is_lesson_notcomplete( $assignment->post_author, array( $lesson_id => 1 ) );

	if ( empty( $lesson_completed ) ) {
		return true;
	} else {
		return false;
	}
}



/**
 * Get assignment approval url
 * 
 * @since 2.1.0
 * 
 * @param  int $assignment_id
 * @return string assignment approval url
 */
function learndash_assignment_approval_link( $assignment_id ) {
	if ( ! learndash_is_assignment_approved_by_meta( $assignment_id ) ) {
		$approve_url = admin_url( 'edit.php?post_type=sfwd-assignment&ld_action=approve_assignment&post[]=' . $assignment_id . '&ret_url=' . rawurlencode( @$_SERVER['REQUEST_URI'] ) );
		return $approve_url;
	} else {
		return '';
	}
}



/**
 * Register assignment metabox
 * 
 * @since 2.1.0
 */
function learndash_assignment_metabox() {
	add_meta_box( 'learndash_assignment_metabox', __( 'Assignment', 'learndash' ), 'learndash_assignment_metabox_content', 'sfwd-assignment', 'advanced', 'high' );
}

add_action( 'add_meta_boxes', 'learndash_assignment_metabox' );



/**
 * Add Approval Link to assignment metabox
 * 
 * 
 * @since 2.1.0
 */
function learndash_assignment_metabox_content() {
	global $post;
	$file_link = get_post_meta( $post->ID, 'file_link', true );
	echo "<a href='" . $file_link . "' target='_blank'>" . __( 'Download', 'learndash' ) . '</a><br>';
	$learndash_assignment_approval_link = learndash_assignment_approval_link( $post->ID );

	if ( $learndash_assignment_approval_link ) {
		echo "<a href='" . $learndash_assignment_approval_link . "' >" . __( 'Approve', 'learndash' ) . '</a><br>';
	}
}



/**
 * Only allow admins, group leaders, and assignment owners to see assignment
 * 
 * @since 2.1.0
 */
function learndash_assignment_permissions() {
	global $post;

	if ( ! empty( $post->post_type ) && $post->post_type == 'sfwd-assignment' && is_singular() ) {
		$user_id = get_current_user_id();

		if ( current_user_can( 'manage_options' ) ) {
			return;
		}
		
		if ( $post->post_author == $user_id ) {
			return;
		} else if ( learndash_is_group_leader_of_user( $user_id, $post->post_author ) ) {
			return;
		} else {
			wp_redirect( get_bloginfo( 'url' ) );
			exit;
		}
	}
}

add_action( 'wp', 'learndash_assignment_permissions' ); //, 0, 3 );



/**
 * Register Assignments custom post type
 * 
 * @since 2.1.0
 */
function learndash_register_assignment_upload_type() {
	$labels = array(
		'name' => __( 'Assignments', 'learndash' ), 
		'singular_name' => __( 'Assignment', 'learndash' ), 
		'edit_item' => __( 'Edit Assignment', 'learndash' ), 
		'view_item' => __( 'View Assignment', 'learndash' ), 
		'search_items' => __( 'Search Assignments', 'learndash' ), 
		'not_found' => __( 'No assignment found', 'learndash' ), 
		'not_found_in_trash' => __( 'No assignment found in Trash', 'learndash' ), 
		'parent_item_colon' => __( 'Parent:', 'learndash' ), 
		'menu_name' => __( 'Assignments', 'learndash' ),
	);

	$args = array(
		'labels' => $labels, 
		'hierarchical' => false, 
		'supports' => array( 'title', 'comments', 'author' ), 
		'public' => true, 
		'show_ui' => true, 
		'show_in_menu' => true, 
		'show_in_nav_menus' => true, 
		'publicly_queryable' => true, 
		'exclude_from_search' => true, 
		'has_archive' => false, 
		'query_var' => true,
		'rewrite' => array( 'slug' => 'assignment' ), 
		'capability_type' => 'assignment', 
		'capabilities' => array( 
			'read_post' => 'read_assignment', 
			'publish_posts' => 'publish_assignments', 
			'edit_posts' => 'edit_assignments', 
			'edit_others_posts' => 'edit_others_assignments', 
			'delete_posts' => 'delete_assignments', 
			'delete_others_posts' => 'delete_others_assignments', 
			'read_private_posts' => 'read_private_assignments', 
			'edit_post' => 'edit_assignment', 
			'delete_post' => 'delete_assignment', 
			'edit_published_posts' => 'edit_published_assignments', 
			'delete_published_posts' => 'delete_published_assignments',
		), 
		'map_meta_cap' => true,
	);

	register_post_type( 'sfwd-assignment', $args );	
}

add_action( 'init', 'learndash_register_assignment_upload_type' );



/**
 * Setup capabilities for Assignments custom post type
 * 
 * @since 2.1.0
 */
function learndash_add_assignment_caps() {
	$role = get_role( 'administrator' );
	$cap = $role->has_cap( 'delete_others_assignments' );

	if ( empty( $cap ) ) {
		$role->add_cap( 'edit_assignment' );
		$role->add_cap( 'edit_assignments' );
		$role->add_cap( 'edit_others_assignments' );
		$role->add_cap( 'publish_assignments' );
		$role->add_cap( 'read_assignment' );
		$role->add_cap( 'read_private_assignments' );
		$role->add_cap( 'delete_assignment' );
		$role->add_cap( 'edit_published_assignments' );
		$role->add_cap( 'delete_others_assignments' );
		$role->add_cap( 'delete_published_assignments' );
		
		$role = get_role( 'group_leader' );
		$role->add_cap( 'read_assignment' );
		$role->add_cap( 'edit_assignments' );
		$role->add_cap( 'edit_others_assignments' );
		$role->add_cap( 'edit_published_assignments' );
		$role->add_cap( 'delete_others_assignments' );
		$role->add_cap( 'delete_published_assignments' );
	}
}

add_action( 'admin_init', 'learndash_add_assignment_caps' );



/**
 * Delete assignment file when assignment post is deleted
 * 
 * @since 2.1.0
 * 
 * @param  int $post_id 
 */
function learndash_before_delete_assignment( $post_id ) {
	$post = get_post( $post_id );

	if ( $post->post_type != 'sfwd-assignment' ) {
		return;
	}
	
	$file_path = get_post_meta( $post_id, 'file_path', true );
	$file_path = rawurldecode( $file_path );
	
	if ( file_exists( $file_path ) ) {
		unlink( $file_path );
	}
}

add_action( 'before_delete_post', 'learndash_before_delete_assignment' );