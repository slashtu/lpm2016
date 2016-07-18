<?php
/**
 * Group functions
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\Groups
 */



/**
 * Email message to group
 * 
 * @since 2.1.0
 */
function learndash_group_emails() {
	if ( isset( $_POST['group'] ) && isset( $_POST['text'] ) && isset( $_POST['sub'] ) ) {

		if ( empty( $_POST['group'] ) || empty( $_POST['text'] ) || empty( $_POST['sub'] ) ) {
			echo __( 'Empty value', 'learndash' );
			exit;
		}

		/**
		 * include pluggable.php functions for use
		 */
		require_once ABSPATH . 'wp-includes/pluggable.php';
		
		$current_user       = wp_get_current_user();
		$group_leader_email = $current_user->user_email;
		$group_leader_name  = $current_user->display_name;
		$status             = '';
		$er                 = learndash_get_groups_user_ids( $_POST['group'] );
		$sent               = '';
		$notsent            = '';

		foreach ( $er as $k => $v ) {

			$user    = get_userdata( $er[ $k ] );
			$email   = $user->user_email;
			$message = nl2br( stripcslashes( $_POST['text'] ) );
			$sub     = $_POST['sub'];

			$headers = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			// old format	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			//$headers .= "From: ".$user->display_name." <".$user->user_email.">" ;
			$headers .= 'From: ' . $group_leader_name . ' <' . $group_leader_email . '>';

			$state = wp_mail( $email, $sub, $message, $headers );

			if ( $state ) {
				$sent .= empty( $sent) ? $user->user_email : ', ' . $user->user_email;
			} else {
				$notsent .= empty( $notsent) ? $user->user_email : ', ' . $user->user_email;
			}

		}

		if ( ! empty( $notsent) ) {
			echo "\n" . __( 'Email could not be sent to: ', 'learndash' ) . $notsent . ' ' . $group_leader_email . ':' . $group_leader_name;
		}

		if ( ! empty( $sent) ) {
			echo "\n" . __( 'Email sent to: ', 'learndash' ) . $sent;
		}

		exit;
	}
}

add_action( 'init', 'learndash_group_emails' );



/**
 * Register groups post type
 * 
 * @since 2.1.0
 */
function learndash_groups_post_content() {

	$labels = array(
		'name'               => __( 'LearnDash Groups', 'learndash' ),
		'singular_name'      => __( 'LearnDash Group', 'learndash' ),
		'add_new'            => __( 'Add New', 'learndash' ),
		'add_new_item'       => __( 'Add New LearnDash Group', 'learndash' ),
		'edit_item'          => __( 'Edit LearnDash Group', 'learndash' ),
		'new_item'           => __( 'New LearnDash Group', 'learndash' ),
		'all_items'          => __( 'LearnDash Groups', 'learndash' ),
		'updated'            => __( 'LearnDash Group Updated.', 'learndash' ),
		'view_item'          => __( 'View LearnDash Group', 'learndash' ),
		'search_items'       => __( 'Search LearnDash Group', 'learndash' ),
		'not_found'          => __( 'No LearnDash Group found', 'learndash' ),
		'not_found_in_trash' => __( 'No LearnDash Group found in the Trash', 'learndash' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'LearnDash Groups', 'learndash' ),
	);

	$capabilities = array(
		'read_post'              => 'read_group',
		'publish_posts'          => 'publish_groups',
		'edit_posts'             => 'edit_groups',
		'edit_others_posts'      => 'edit_others_groups',
		'delete_posts'           => 'delete_groups',
		'delete_others_posts'    => 'delete_others_groups',
		'read_private_posts'     => 'read_private_groups',
		'delete_post'            => 'delete_group',
		'edit_published_posts'   => 'edit_published_groups',
		'delete_published_posts' => 'delete_published_groups',
	);

	if ( is_admin() ) {
		$admin = get_role( 'administrator' );
		if ( ! $admin->has_cap( 'edit_groups' ) ) {
			foreach ( $capabilities as $key => $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	$args = array(
		'labels'              => $labels,
		'description'         => __( 'Holds LearnDash user Groups', 'learndash' ),
		'public'              => false,
		'menu_position'       => 10,
		'show_in_menu'        => true,
		'supports'            => array( 'title', 'editor'), //, 'custom-fields', 'author'
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'capabilities'        => $capabilities,
		'map_meta_cap'        => true,
	);

	/**
	 * Filter post type registration args
	 * 
	 * @var array $args
	 */
	$args = apply_filters( 'learndash_post_args_groups', $args );
	register_post_type( 'groups', $args );

}

add_action( 'init', 'learndash_groups_post_content' );



/**
 * Set 'updated' admin messages for Groups post type
 * 
 * @since 2.1.0
 * 
 * @param  array $messages
 * @return array $messages
 */
function learndash_group_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['groups'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'LearnDash Group updated.', 'learndash' ), esc_url( get_permalink( $post_ID ) ) ),
		2  => __( 'Custom field updated.', 'learndash' ),
		3  => __( 'Custom field deleted.', 'learndash' ),
		4  => __( 'LearnDash Group updated.', 'learndash' ),
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'LearnDash Group restored to revision from %s', 'learndash' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, /* translators: %s: date and time of the revision */
		6  => sprintf( __( 'Group published.', 'learndash' ), esc_url( get_permalink( $post_ID ) ) ),
		7  => __( 'LearnDash Group saved.', 'learndash' ),
		8  => sprintf( __( 'LearnDash Group submitted. ', 'learndash' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9  => sprintf(__( 'LearnDash Group scheduled for: <strong>%1$s</strong>. ', 'learndash' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ), // translators: Publish box date format, see http://php.net/date
		10 => sprintf( __( 'Group draft updated.', 'learndash' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
}

add_filter( 'post_updated_messages', 'learndash_group_updated_messages' );



/**
 * Add Group Leader role
 * 
 * @since 2.1.0
 */
function learndash_add_group_admin_role() {
	$group_leader = get_role( 'group_leader' );

	if ( is_null( $group_leader ) ) {
		$group_leader = add_role( 'group_leader', 'Group Leader', array( 'read' => true ) );
	}
}

add_action( 'init', 'learndash_add_group_admin_role' );


/**
 * Get all group leaders user ids
 *
 * @return array of group leaders user ids
 * @since 2.1.2
 */
function learndash_all_group_leader_ids() {
	$group_leader_user_ids = array();
	$group_leader_users = learndash_all_group_leaders();
	if (!empty($group_leader_users)) {
		$group_leader_user_ids = wp_list_pluck( $group_users, 'ID' );
	}
	return $group_leader_user_ids;
}

/**
 * Get all group leaders
 *
 * @return array of group leaders user objects
 */
function learndash_all_group_leaders() {
	$transient_key = "learndash_group_leaders";
	$group_user_objects = get_transient( $transient_key );
	if ( $group_user_objects === false ) {
	
		$user_query_args = array(
			'role'		=>	'group_leader',
			'orderby'	=>	'display_name',
			'order'		=>	'ASC'
		);

		$user_query = new WP_User_Query( $user_query_args );
		if ( isset( $user_query->results ) ) {
			$group_user_objects = $user_query->results;
		} else {
			$group_user_objects = array();
		}
		
		set_transient( $transient_key, $group_user_objects, MINUTE_IN_SECONDS );
	}
	return $group_user_objects;
}

/**
 * Outputs list of group users in a table
 *
 * @since 2.1.0
 * 
 * @param  int 		$group_id
 * @return string
 */
function learndash_group_user_list( $group_id ) {
	global $wpdb;
	
	$current_user = wp_get_current_user();

	//echo "group_id[". $group_id ."]<br />";
	
	if ( empty( $current_user->ID ) || ! $current_user->has_cap( 'group_leader' ) ) {
		return __( 'Please login as a Group Administrator', 'learndash' );
	}

	$users = learndash_get_groups_users( $group_id );
	if ( ! empty( $users ) ) {
		?>
		<table cellspacing="0" class="wp-list-table widefat fixed groups_user_table">
		<thead>
			<tr>
				<th class="manage-column column-sno " id="sno" scope="col" ><?php _e( 'S. No.', 'learndash' );?></th>
				<th class="manage-column column-name " id="group" scope="col"><?php _e( 'Name', 'learndash' );?></th>
				<th class="manage-column column-name " id="group" scope="col"><?php _e( 'Username', 'learndash' );?></th>
				<th class="manage-column column-name " id="group" scope="col"><?php _e( 'Email', 'learndash' );?></th>
				<th class="manage-column column-action" id="action" scope="col"><?php _e( 'Action', 'learndash' );?></span></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="manage-column column-sno " id="sno" scope="col" ><?php _e( 'S. No.', 'learndash' );?></th>
				<th class="manage-column column-name " id="group" scope="col"><?php _e( 'Name', 'learndash' );?></th>
				<th class="manage-column column-name " id="group" scope="col"><?php _e( 'Username', 'learndash' );?></th>
				<th class="manage-column column-name " id="group" scope="col"><?php _e( 'Email', 'learndash' );?></th>
				<th class="manage-column column-action" id="action" scope="col"><?php _e( 'Action', 'learndash' );?></span></th>
			</tr>
		</tfoot>
		<tbody>
			<?php $sn = 1;
				foreach ( $users as $user ) {
					$name = isset( $user->display_name) ? $user->display_name : $user->user_nicename;
					?>
						<tr>
							<td><?php echo $sn++;?></td>
							<td><?php echo $name;?></td>
							<td><?php echo $user->user_login;?></td>
							<td><?php echo $user->user_email;?></td>
							<td><a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page&group_id=' . $group_id . '&user_id=' . $user->ID );?>"><?php _e( 'Report', 'learndash' );?></a></td>
						</tr>
					<?php
				}
			?>
		</tbody>
		</table>
		<?php
	} else {
		return __( 'No users.', 'learndash' );
	}

}
// FPM: This is registered but is anyone using it? The related function takes a 
// group_id int. For a proper shortcode handler is should take an array where
// group_id is passed as in [learndash_group_user_list group_id="123"]
add_shortcode( 'learndash_group_user_list', 'learndash_group_user_list' );



/**
 * Get list of enrolled courses for a group
 *
 * @since 2.1.0
 * 
 * @param  int 		$group_id
 * @return array 	list of courses
 */
function learndash_group_enrolled_courses( $group_id ) {
	global $wpdb;

	if ( is_numeric( $group_id ) ) {
		$col = $wpdb->get_col( " SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'learndash_group_enrolled_" . $group_id . "'" );
		$ids = implode( ',', $col );

		if ( ! empty( $ids) ) {
			$col = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND ID IN (" . implode( ',', $col ) . ')' );
			return $col;
		}
	}

	return array();
}



/**
 * Is a group enrolled in a certain course
 *
 * @since 2.1.0
 * 
 * @param  int $group_id
 * @param  int $course_id
 * @return bool
 */
function learndash_group_has_course( $group_id, $course_id ) {
	return get_post_meta( $course_id, 'learndash_group_enrolled_' . $group_id, true ) > 0;
}



/**
 * Gets timestamp of when course is available to group
 *
 * @since 2.1.0
 * 
 * @param  int 		$group_id
 * @param  int 		$course_id
 * @return string   time stamp
 */
function learndash_group_course_access_from( $group_id, $course_id ) {
	return get_post_meta( $course_id, 'learndash_group_enrolled_' . $group_id, true );
}



/**
 * Does a course belong to a group the user is in
 *
 * @since 2.1.0
 * 
 * @param  int $user_id
 * @param  int $course_id
 * @return bool
 */
function learndash_user_group_enrolled_to_course( $user_id, $course_id ) {
	$group_ids = learndash_get_users_group_ids( $user_id );

	foreach ( $group_ids as $group_id ) {
		if ( learndash_group_has_course( $group_id, $course_id ) ) {
			return true;
		}
	}

	return false;
}



/**
 * Gets timestamp of when course is available to a user in a group
 *
 * @since 2.1.0
 * 
 * @param  int 		$user_id
 * @param  int 		$course_id
 * @return string   timestamp
 */
function learndash_user_group_enrolled_to_course_from( $user_id, $course_id ) {
	$group_ids     = learndash_get_users_group_ids( $user_id );
	$enrolled_from = time() + 10000;

	foreach ( $group_ids as $group_id ) {
		$enrolled_from_temp = learndash_group_course_access_from( $group_id, $course_id );

		if ( ! empty( $enrolled_from_temp ) && $enrolled_from_temp < $enrolled_from ) {
			$enrolled_from = $enrolled_from_temp;
		}
	}

	if ( $enrolled_from <= time() ) {
		$user = get_userdata( $user_id );
		$user_registered = strtotime( $user->user_registered );

		if ( $user_registered > $enrolled_from ) {
			return $user_registered;
		} else {
			return $enrolled_from;
		}
	} else {
		return null;
	}
}



/**
 * Get group ids that the user is an administrator of
 *
 * @since 2.1.0
 * 
 * @param  int 		$user_id
 * @return array 	list of group ids
 */
function learndash_get_administrators_group_ids( $user_id ) {
	global $wpdb;

	if ( is_numeric( $user_id ) ) {
		if ( user_can( $user_id, 'manage_options' ) ) {
			return learndash_get_groups( true );
		}
		$col = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key LIKE %s AND user_id = %d", 'learndash_group_leaders_%', $user_id ) );
		if ( ! empty( $col) && ! empty( $col[0] ) ) {
			$col = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND ID IN (" . implode( ',', $col ) . ')' );
			return $col;
		}
	}

	return array();	
}



/**
 * Get all groups
 * 
 * @since 2.1.0
 * 
 * @param  bool 	$id_only 	return id's only
 * @return array         		groups
 */
function learndash_get_groups( $id_only = false ) {
	$groups = get_posts( 'post_type=groups&posts_per_page=-1' );

	if ( $id_only ) {
		$group_ids = array();

		foreach ( $groups as $group ) {
			$group_ids[ $group->ID ] = $group->ID;
		}

		return $group_ids;
	} else {
		return $groups;
	}
}



/**
 * Get a users group id's
 * 
 * @since 2.1.0
 * 
 * @param  int 		$user_id
 * @return array    list of groups user belongs to
 */
function learndash_get_users_group_ids( $user_id ) {
	global $wpdb;

	if ( is_numeric( $user_id ) ) {
		$col = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM ". $wpdb->usermeta ." WHERE meta_key LIKE %s AND user_id = %d and meta_value != ''", 
			'learndash_group_users_%', $user_id ) );
		if ( ! empty( $col) && ! empty( $col[0] ) ) {
			$col = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND ID IN (" . implode( ',', $col ) . ')' );
			return $col;
		}
	}

	return array();
}



/**
 * Get users ids that belong to a group
 * 
 * @since 2.1.0
 * 
 * @param  int 		$group_id
 * @param  bool 	$bypass_transient to ignore transient cache
 * @return array 	array of user ids that belong to group
 */
function learndash_get_groups_user_ids( $group_id = 0, $bypass_transient = false) {
	/*
	global $wpdb;

	if ( is_numeric( $group_id ) ) {
		return $wpdb->get_col( " SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'learndash_group_users_" . $group_id . "' AND meta_value = '" . $group_id . "'" );
	} else {
		return array();
	}
	*/
	
	$group_user_ids = array();
	if (!empty( $group_id ) ) {
		$group_users = learndash_get_groups_users( $group_id, $bypass_transient );
		if (!empty( $group_users ) ) {
			$group_user_ids = wp_list_pluck( $group_users, 'ID' );
		}
	}
	return $group_user_ids;
}

/**
 * Get users objects that belong to a group
 * 
 * @since 2.1.2
 * 
 * @param  int 		$group_id
 * @param  bool 	$bypass_transient to ignore transient cache
 * @return array 	list of users objects that belong to group
 */
function learndash_get_groups_users( $group_id, $bypass_transient = false ) {
	//echo "group_id[". $group_id ."] [". $bypass_transient ."]<br />";
	
	if (!$bypass_transient) {
		$transient_key = "learndash_group_users_" . $group_id;
		$group_users_objects = get_transient( $transient_key );
	} else {
		$group_users_objects = false;
	}
	//echo "group_users_objects<pre>"; print_r($group_users_objects); echo "</pre>";
	
	if ( $group_users_objects === false ) {
	
		// For this group get the group leaders. They will be excluded from the regular users. 
		$group_leader_user_ids = learndash_get_groups_administrator_ids( $group_id );
		
		$user_query_args = array(
			'exclude' 	=> 	$group_leader_user_ids,
			'orderby' 	=>	'display_name',
			'order'	 	=>	'ASC',
			'meta_query' => array(
				array(
					'key'     	=> 	'learndash_group_users_'. intval( $group_id ),
					'value'   	=> 	intval( $group_id ),
					'compare' 	=> 	'=',
					'type'		=>	'NUMERIC'
				)
			)
		);
		
		$user_query = new WP_User_Query( $user_query_args );
		
		if ( isset( $user_query->results ) ) {
			$group_users_objects = $user_query->results;
		} else {
			$group_users_objects = array();
		}
		
		if (!$bypass_transient) {
			set_transient( $transient_key, $group_users_objects, MINUTE_IN_SECONDS );
		}
	}

	return $group_users_objects;
}


/**
 * Set Group Users users 
 * 
 * @since 2.1.2
 * 
 * @param  int 		$group_id
 * @param  array 	list of users objects
 * @return none
 */
function learndash_set_groups_users( $group_id = 0, $group_users_new = array() ) {

	$group_users_old = learndash_get_groups_user_ids( $group_id, true );
	
	if ($group_users_old != $group_users_new) {
		if (!empty($group_users_new)) {
			foreach ( $group_users_new as $ga ) {
				if ( ! in_array( $ga, $group_users_old ) ) {
					update_user_meta( $ga, 'learndash_group_users_' . $group_id, $group_id );
				} else {
					$group_user_pos = array_search( $ga, $group_users_old);
					if ($group_user_pos !== false) {
						unset( $group_users_old[$group_user_pos] );
					} 
				}
			}
		}
		
		if (!empty($group_users_old)) {
			foreach ( $group_users_old as $ga ) {
				delete_user_meta( $ga, 'learndash_group_users_' . $group_id, null );
			}
		}
	
		$transient_key = "learndash_group_users_" . $group_id;
		delete_transient( $transient_key );
	}
}

/**
 * Get admin id's that belong to group
 * 
 * @since 2.1.0
 * 
 * @param  int 		$group_id
 * @param  bool 	$bypass_transient to ignore transient cache
 * @return array 	array of goup leader user ids
 */
function learndash_get_groups_administrator_ids( $group_id, $bypass_transient = false ) {
	/*
	global $wpdb;

	if ( is_numeric( $group_id ) ) {
		return $wpdb->get_col( " SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'learndash_group_leaders_" . $group_id . "' AND meta_value = '" . $group_id . "'" );
	} else {
		return array();
	}
	*/
	
	$group_leader_user_ids = array();
	
	if ( !empty( $group_id ) ) {
		$group_leader_users = learndash_get_groups_administrators( $group_id, $bypass_transient );
		if ( !empty( $group_leader_users ) ) {
			$group_leader_user_ids = wp_list_pluck( $group_leader_users, 'ID' );
		}
	}
	return $group_leader_user_ids;
}

/**
 * Get group_leader user objects that belong to group
 * 
 * @since 2.1.2
 * 
 * @param  int 		$group_id
 * @param  bool 	$bypass_transient to ignore transient cache
 * @return array 	array of group leader user objects
 */
function learndash_get_groups_administrators( $group_id, $bypass_transient = false ) {
	
	if ( !$bypass_transient ) {
		$transient_key = "learndash_group_leaders_" . $group_id;
		$group_user_objects = get_transient( $transient_key );
	} else {
		$group_user_objects = false;
	}
	if ( $group_user_objects === false ) {
		
		$user_query_args = array(
			'role'		=>	'group_leader',
			'orderby'	=>	'display_name',
			'order'		=>	'ASC',
			'meta_query' => array(
				array(
					'key'     	=> 	'learndash_group_leaders_'. intval( $group_id ),
					'value'   	=> 	intval( $group_id ),
					'compare' 	=> 	'=',
					'type'		=>	'NUMERIC'
				)
			)
		);
		$user_query = new WP_User_Query( $user_query_args );
		if ( isset( $user_query->results ) ) {
			$group_user_objects = $user_query->results;
		} else {
			$group_user_objects = array();
		}
		
		if ( !$bypass_transient ) {
			set_transient( $transient_key, $group_user_objects, MINUTE_IN_SECONDS );
		}
	}
	
	return $group_user_objects;
}

/**
 * Set Group Leader/Administrator users 
 * 
 * @since 2.1.2
 * 
 * @param  int 		$group_id
 * @param  array 	list of users objects
 * @return none
 */
function learndash_set_groups_administrators( $group_id = 0, $group_leaders_new = array() ) {
	
	if (!empty( $group_id )) {
	
		$group_leaders_old = learndash_get_groups_administrator_ids( $group_id, true );

		// Compare the group leaders list. If the lists are not equal then go into processing...
		if ($group_leaders_old != $group_leaders_new) {

			// First spin through the new leader ids. 
			if (!empty($group_leaders_new)) {
				foreach ( $group_leaders_new as $ga ) {
					if ( ! in_array( $ga, $group_leaders_old ) ) {
						// If a new id doesn't exists in the old list we add the user_meta
						update_user_meta( $ga, 'learndash_group_leaders_' . $group_id, $group_id );

					} else {
						// However, if it does exist in the old list. We
						// remove it. Later what is left in the old list
						// we will remove the user_meta
						$group_leader_pos = array_search( $ga, $group_leaders_old);
						if ($group_leader_pos !== false) {
							unset( $group_leaders_old[$group_leader_pos] );
						} 
					}
				}
			}
	
			// We are removing any other old group leader user id not saved by the previous loop
			if (!empty($group_leaders_old)) {
				foreach ( $group_leaders_old as $ga ) {
					delete_user_meta( $ga, 'learndash_group_leaders_' . $group_id, null );
				}
			}
	
			// Finally clear our cache for other services 
			$transient_key = "learndash_group_leaders_" . $group_id;
			delete_transient( $transient_key );
		} 
	}
}

/**
 * Register Group Administration submenu page
 * 
 * @since 2.1.0
 */
function learndash_group_admin_menu() {

	require_once( LEARNDASH_LMS_PLUGIN_DIR .'includes/admin/class-learndash-admin-groups-users-list.php' );
	$ld_admin_groups_users_list = new Learndash_Admin_Groups_Users_list();

	$pagehook = add_submenu_page( 
		'admin.php', 
		__( 'Group Administration', 'learndash' ), 
		__( 'Group Administration', 'learndash' ), 
		'group_leader', 
		'group_admin_page', 
		array( $ld_admin_groups_users_list, 'learndash_group_admin_menu_page') 
	);
	add_action( 'load-'. $pagehook, array( $ld_admin_groups_users_list, 'on_load') );	
}

add_action( 'admin_menu', 'learndash_group_admin_menu', 1 );


/**
 * Is user a group leader
 *
 * @since 2.1.0
 * 
 * @param  int|object  $user
 * @return bool
 */
function is_group_leader( $user ) {
	if ( is_numeric( $user ) ) {
		$user_id = $user;
	} else {
		$user_id = @$user->ID;
	}

	return is_numeric( $user_id ) && user_can( $user_id, 'group_leader' ) && ! user_can( $user_id, 'manage_options' );
}



/**
 * Is group leader an admin of a group this user belongs to
 *
 * @since 2.1.0
 * 
 * @param  int 	$group_leader_id
 * @param  int 	$user_id
 * @return bool
 */
function learndash_is_group_leader_of_user( $group_leader_id, $user_id ) {
	$admin_groups     = learndash_get_administrators_group_ids( $group_leader_id );
	$has_admin_groups = ! empty( $admin_groups ) && is_array( $admin_groups ) && ! empty( $admin_groups[0] );

	foreach ( $admin_groups as $group_id ) {
		$learndash_is_user_in_group = learndash_is_user_in_group( $user_id, $group_id );

		if ( $learndash_is_user_in_group ) {
			return true;
		}
	}

	return false;
}



/**
 * Does user belong to group
 *
 * @since 2.1.0
 * 
 * @param  int 	$user_id
 * @param  int 	$group_id
 * @return bool
 */
function learndash_is_user_in_group( $user_id, $group_id ) {
	return get_user_meta( $user_id, 'learndash_group_users_' . $group_id, true );
}



/**
 * Shortcode to HTML output of display a users list of groups
 * 
 * @since 2.1.0
 * 
 * @param  array 	$attr 	shortcode attributes
 * @return string           shortcode output
 */
function learndash_user_groups( $attr ) {

	$shortcode_atts = shortcode_atts(
		array(
			'user_id' => '',
		), 
		$attr
	);

	extract( $shortcode_atts );

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return '';
	}

	$admin_groups = learndash_get_administrators_group_ids( $user_id );
	$user_groups = learndash_get_users_group_ids( $user_id );
	$has_admin_groups = ! empty( $admin_groups ) && is_array( $admin_groups ) && ! empty( $admin_groups[0] );
	$has_user_groups  = ! empty( $user_groups ) && is_array( $user_groups ) && ! empty( $user_groups[0] );

	return SFWD_LMS::get_template('user_groups_shortcode', array(
			'admin_groups'     => $admin_groups,
			'user_groups'      => $user_groups,
			'has_admin_groups' => $has_admin_groups,
			'has_user_groups'  => $has_user_groups,
		)
	);
}

add_shortcode( 'user_groups', 'learndash_user_groups' );



/**
 * Delete group id from all users meta when group is deleted
 *
 * @todo  restrict function to only run if post type is grou
 *        will run against db everytime a post is deleted
 * 
 * @since 2.1.0
 * 
 * @param  int 	$pid  id of group
 * @return bool       successful deletion
 */
function learndash_delete_group( $pid ) {
	global $wpdb;

	if ( ! empty( $pid ) && is_numeric( $pid ) ) {
		$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'learndash_group_users_' . $pid, 'meta_value' => $pid ) );
		$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'learndash_group_leaders_' . $pid, 'meta_value' => $pid ) );
	}

	return true;
}

add_action( 'delete_post', 'learndash_delete_group', 10 );



/**
 * Update a users group access
 * 
 * @since 2.1.0
 * 
 * @param  int  $user_id
 * @param  int  $group_id
 * @param  bool $remove
 */
function ld_update_group_access( $user_id, $group_id, $remove = false ) {
	if ( $remove ) {

		delete_user_meta( $user_id, 'learndash_group_users_' . $group_id );

		/**
		 * Run actions after group access is removed for user
		 * 
		 * @since 2.1.0
		 * 
		 * @param  int  $user_id
		 */
		do_action( 'ld_removed_group_access', $user_id, $group_id );

	} else {
		
		update_user_meta( $user_id, 'learndash_group_users_' . $group_id, $group_id );

		/**
		 * Run actions after group access is added for user
		 * 
		 * @since 2.1.0
		 * 
		 * @param  int  $user_id
		 */
		do_action( 'ld_added_group_access', $user_id, $group_id );

	}
}

function learndash_binary_selector_pager_ajax() {
	//echo "_POST<pre>"; print_r($_POST); echo "</pre>";
	if ((isset($_POST['query-data'])) && (!empty($_POST['query-data']))) {
		$query_object = new WP_Query($_POST['query-data']);
		//echo "query_object<pre>"; print_r($query_object); echo "</pre>";
		if ((isset($query_object->posts)) && (!empty($query_object->posts))) {
			$reply_data = array();
			$reply_data['data_query'] = $query_object->query;
			$reply_data['options'] = '';
			foreach($query_object->posts as $item) {
				$reply_data['options'] .= '<option class="" value="'. $item->ID .'">'. $item->post_title .'</option>';
			}
			echo json_encode($reply_data);
		}
	}
	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_learndash_binary_selector_pager', 'learndash_binary_selector_pager_ajax' );
