<?php
/**
 * Functions for wp-admin
 *
 * @since 2.1.0
 *
 * @package LearnDash\Admin
 */


/**
 * Output for admin head
 *
 * Adds learndash icon next to the LearnDash LMS menu item
 *
 * @since 2.1.0
 */
function learndash_admin_head() {
	?>
		<style> #adminmenu #toplevel_page_learndash-lms div.wp-menu-image:before { content: "\f472"; } </style>
	<?php
}

add_action( 'admin_head', 'learndash_admin_head' );



/**
 * Hide top level menu when there are no submenus
 *
 * @since 2.1.0
 */
function learndash_hide_menu_when_not_required() {
	?>
		<script>
		jQuery(window).ready(function() {
		if(jQuery(".toplevel_page_learndash-lms").length && jQuery(".toplevel_page_learndash-lms").find("li").length <= 1)
			jQuery(".toplevel_page_learndash-lms").hide();
		});
		</script>
	<?php
}

add_filter( 'admin_footer', 'learndash_hide_menu_when_not_required', 99 );



/**
 * Scripts/styles for admin
 *
 * @since 2.1.0
 */
function learndash_load_admin_resources() {
	global $pagenow, $post;
	global $learndash_post_types, $learndash_pages;;

	if ( in_array( @$_GET['page'], $learndash_pages ) || in_array( @$_GET['post_type'], $learndash_post_types ) || $pagenow == 'post.php' && in_array( $post->post_type, $learndash_post_types ) ) {
		wp_enqueue_style( 'learndash_style', plugins_url( 'assets/css/style.css', dirname( dirname( __FILE__ ) ) ), array(), LEARNDASH_VERSION );
	}

	if ( $pagenow == 'post.php' && $post->post_type == 'sfwd-quiz' || $pagenow == 'post-new.php' && @$_GET['post_type'] == 'sfwd-quiz' ) {
		wp_enqueue_script( 'ld-proquiz_admin_js', plugins_url( 'vendor/wp-pro-quiz/js/wpProQuiz_admin.min.js', dirname( __FILE__ ) ), array( 'jquery' ) );
	}

	if ( $pagenow == 'post-new.php' && @$_GET['post_type'] == 'sfwd-lessons' || $pagenow == 'post.php' && @get_post( @$_GET['post'] )->post_type == 'sfwd-lessons' ) {
		wp_enqueue_style( 'ld-datepicker-css', plugins_url( 'assets/css/jquery-ui.css', dirname( dirname( __FILE__ ) ) ), array(), LEARNDASH_VERSION );
	}
}

add_action( 'admin_enqueue_scripts', 'learndash_load_admin_resources' );



/**
 * Register admin menu pages
 *
 * @since 2.1.0
 */
function learndash_menu() {
	if ( ! is_admin() ) {
		return;
	}

	add_menu_page(
		__( 'LearnDash LMS', 'learndash' ),
		__( 'LearnDash LMS', 'learndash' ),
		'read',
		'learndash-lms',
		null,
		null,
		null
	);

	add_submenu_page(
		'learndash-lms-non-existant',
		__( 'LearnDash Reports', 'learndash' ),
		__( 'LearnDash Reports', 'learndash' ),
		'manage_options',
		'learndash-lms-reports',
		'learndash_lms_reports_page'
	);

	add_submenu_page(
		'learndash-lms-non-existant',
		__( 'Certificate Shortcodes', 'learndash' ),
		__( 'Certificate Shortcodes', 'learndash' ),
		'manage_options',
		'learndash-lms-certificate_shortcodes',
		'learndash_certificate_shortcodes_page'
	);

	add_submenu_page(
		'learndash-lms-non-existant',
		sprintf( __( '%s Shortcodes', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
		sprintf( __( '%s Shortcodes', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
		'edit_courses',
		'learndash-lms-course_shortcodes',
		'learndash_course_shortcodes_page'
	);

	$remove_from_submenu = array(
		'options-general.php?page=nss_plugin_license-sfwd_lms-settings' => __( 'LearnDash LMS License', 'learndash' ),
		'admin.php?page=learndash-lms-reports' => 'Reports',
	);

	$remove_from_menu = array(
		'edit.php?post_type=sfwd-courses',
		'edit.php?post_type=sfwd-lessons',
		'edit.php?post_type=sfwd-quiz',
		'edit.php?post_type=sfwd-topic',
		'edit.php?post_type=sfwd-certificates',
		'edit.php?post_type=sfwd-assignment',
		'edit.php?post_type=groups',
	);

	global $submenu;

	$add_submenu = array(
		array(
			'name' 	=> LearnDash_Custom_Label::get_label( 'courses' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-courses',
		),
		array(
			'name' 	=> LearnDash_Custom_Label::get_label( 'lessons' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-lessons',
		),
		array(
			'name' 	=> LearnDash_Custom_Label::get_label( 'topics' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-topic',
		),
		array(
			'name' 	=> LearnDash_Custom_Label::get_label( 'quizzes' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		array(
			'name' 	=> __( 'Certificates', 'learndash' ),
			'cap'	=> 'edit_courses',
			'link'	=> 'edit.php?post_type=sfwd-certificates',
		),
		array(
			'name' 	=> __( 'Assignments', 'learndash' ),
			'cap'	=> 'edit_assignments',
			'link'	=> 'edit.php?post_type=sfwd-assignment',
		),
		'groups' => array(
			'name' 	=> __( 'Groups', 'learndash' ),
			'cap'	=> 'edit_groups',
			'link'	=> 'edit.php?post_type=groups',
		),
		array(
			'name' 	=> __( 'Reports', 'learndash' ),
			'cap'	=> 'manage_options',
			'link'	=> 'admin.php?page=learndash-lms-reports',
		),
		array(
			'name' 	=> __( 'Settings', 'learndash' ),
			'cap'	=> 'manage_options',
			'link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses'
		),
		array(
			'name' 	=> __( 'Group Administration','learndash' ),
			'cap'	=> 'group_leader',
			'link'	=> 'admin.php?page=group_admin_page',
		),
	);

	 /**
	 * Filter submenu array before it is registered
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $add_submenu
	 */
	$add_submenu = apply_filters( 'learndash_submenu', $add_submenu );
	$location = 500;

	foreach ( $add_submenu as $key => $add_submenu_item ) {
		if ( current_user_can( $add_submenu_item['cap'] ) ) {
			$submenu['learndash-lms'][ $location++ ] = array( $add_submenu_item['name'], $add_submenu_item['cap'], $add_submenu_item['link'] );
		}
	}

	foreach ( $remove_from_menu as $menu ) {
		if ( isset( $submenu[ $menu ] ) ) {
			remove_menu_page( $menu );
		}
	}

	foreach ( $remove_from_submenu as $menu => $remove_submenu_items ) {
		if ( isset( $submenu[ $menu ] ) && is_array( $submenu[ $menu ] ) ) {
			foreach ( $submenu[ $menu] as $key => $item ) {
				if ( isset( $item[0] ) && in_array( $item[0], $remove_submenu_items ) ) {
					unset( $submenu[ $menu ][ $key ] );
				}
			}
		}
	}

}

add_action( 'admin_menu', 'learndash_menu', 1000 );



/**
 * Set up admin tabs for each admin menu page under LearnDash
 *
 * @since 2.1.0
 */
function learndash_admin_tabs() {
	if ( ! is_admin() ) {
		return;
	}

	$admin_tabs = array(
		0 => array(
			'link'	=> 'post-new.php?post_type=sfwd-courses',
			'name'	=> __( 'Add New', 'learndash' ),
			'id'	=> 'sfwd-courses',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		10 => array(
			'link'	=> 'edit.php?post_type=sfwd-courses',
			'name'	=> LearnDash_Custom_Label::get_label( 'courses' ),
			'id'	=> 'edit-sfwd-courses',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		24 => array(
			'link'	=> 'edit-tags.php?taxonomy=category&post_type=sfwd-courses',
			'name'	=> __( 'Categories', 'learndash' ),
			'id'	=> 'edit-category',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		26 => array(
			'link'	=> 'edit-tags.php?taxonomy=post_tag&post_type=sfwd-courses',
			'name'	=> __( 'Tags', 'learndash' ),
			'id'	=> 'edit-post_tag',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		28 => array(
			'link'	=> 'admin.php?page=learndash-lms-course_shortcodes',
			'name'	=> sprintf( __( '%s Shortcodes', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
			'id'	=> 'admin_page_learndash-lms-course_shortcodes',
			'cap'	=> 'edit_courses',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses',
		),
		30 => array(
			'link'	=> 'post-new.php?post_type=sfwd-lessons',
			'name'	=> __( 'Add New', 'learndash' ),
			'id'	=> 'sfwd-lessons',
			'menu_link'	=> 'edit.php?post_type=sfwd-lessons',
		),
		40 => array(
			'link'	=> 'edit.php?post_type=sfwd-lessons',
			'name'	=> LearnDash_Custom_Label::get_label( 'lessons' ),
			'id'	=> 'edit-sfwd-lessons',
			'menu_link'	=> 'edit.php?post_type=sfwd-lessons',
		),
		50 => array(
			'link'	=> 'edit.php?post_type=sfwd-lessons&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-lessons',
			'name'	=> sprintf( __( '%s Options', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
			'id'	=> 'sfwd-lessons_page_sfwd-lms_sfwd_lms_post_type_sfwd-lessons',
			'menu_link'	=> 'edit.php?post_type=sfwd-lessons',
		),
		60 => array(
			'link'	=> 'post-new.php?post_type=sfwd-topic',
			'name'	=> __( 'Add New', 'learndash' ),
			'id'	=> 'sfwd-topic',
			'menu_link'	=> 'edit.php?post_type=sfwd-topic',
		),
		70 => array(
			'link'	=> 'edit.php?post_type=sfwd-topic',
			'name'	=> LearnDash_Custom_Label::get_label( 'topics' ),
			'id'	=> 'edit-sfwd-topic',
			'menu_link'	=> 'edit.php?post_type=sfwd-topic',
		),
		80 => array(
			'link'	=> 'post-new.php?post_type=sfwd-quiz',
			'name'	=> __( 'Add New', 'learndash' ),
			'id'	=> 'sfwd-quiz',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		90 => array(
			'link'	=> 'edit.php?post_type=sfwd-quiz',
			'name'	=> LearnDash_Custom_Label::get_label( 'quizzes' ),
			'id'	=> 'edit-sfwd-quiz',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		100 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz&module=globalSettings',
			'name'	=> sprintf( __( '%s Options', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'id'	=> 'admin_page_ldAdvQuiz_globalSettings',
			'cap'	=> 'wpProQuiz_change_settings',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		101 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz',
			'name'	=> __( 'Import/Export', 'learndash' ),
			'id'	=> 'admin_page_ldAdvQuiz',
			'cap'	=> 'wpProQuiz_export',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		95 => array(
			'link'	=> 'post.php?post=[post_id]&action=edit',
			'name'	=> sprintf( __( 'Edit %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
			'id'	=> 'sfwd-quiz_edit',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		102 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz&module=question&quiz_id=[quiz_id]&post_id=[post_id]',
			'name'	=> __( 'Questions', 'learndash' ),
			'id'	=> 'admin_page_ldAdvQuiz_question',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		104 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz&module=statistics&id=[quiz_id]&post_id=[post_id]',
			'name'	=> __( 'Statistics', 'learndash' ),
			'id'	=> 'admin_page_ldAdvQuiz_statistics',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		106 => array(
			'link'	=> 'admin.php?page=ldAdvQuiz&module=toplist&id=[quiz_id]&post_id=[post_id]',
			'name'	=> __( 'Leaderboard', 'learndash' ),
			'id'	=> 'admin_page_ldAdvQuiz_toplist',
			'menu_link'	=> 'edit.php?post_type=sfwd-quiz',
		),
		110 => array(
			'link'	=> 'post-new.php?post_type=sfwd-certificates',
			'name'	=> __( 'Add New', 'learndash' ),
			'id'	=> 'sfwd-certificates',
			'menu_link'	=> 'edit.php?post_type=sfwd-certificates',
		),
		120 => array(
			'link'	=> 'edit.php?post_type=sfwd-certificates',
			'name'	=> __( 'Certificates', 'learndash' ),
			'id'	=> 'edit-sfwd-certificates',
			'menu_link'	=> 'edit.php?post_type=sfwd-certificates',
		),
		130 => array(
			'link'	=> 'admin.php?page=learndash-lms-certificate_shortcodes',
			'name'	=> __( 'Certificate Shortcodes', 'learndash' ),
			'id'	=> 'admin_page_learndash-lms-certificate_shortcodes',
			'menu_link'	=> 'edit.php?post_type=sfwd-certificates',
		),
		135 => array(
			'link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
			'name'	=> __( 'PayPal Settings', 'learndash' ),
			'id'	=> 'sfwd-courses_page_sfwd-lms_sfwd_lms_post_type_sfwd-courses',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
		),
		136 => array(
			'link'	=> 'admin.php?page=learndash_custom_label',
			'name'	=> __( 'Custom Labels', 'learndash' ),
			'id'	=> 'admin_page_learndash_custom_label',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
		),
		140 => array(
			'link'	=> 'admin.php?page=nss_plugin_license-sfwd_lms-settings',
			'name'	=> __( 'LMS License', 'learndash' ),
			'id'	=> 'admin_page_nss_plugin_license-sfwd_lms-settings',
			'menu_link'	=> 'edit.php?post_type=sfwd-courses&page=sfwd-lms_sfwd_lms.php_post_type_sfwd-courses',
		),
		150 => array(
			'external_link'	=> 'http://support.learndash.com',
			'target' => '_blank',
			'name'	=> __( 'Support', 'learndash' ),
			'id'	=> 'external_link_support_learndash',
		),
		160 => array(
			'link'	=> 'admin.php?page=learndash-lms-reports',
			'name'	=> __( 'User Reports', 'learndash' ),
			'id'	=> 'admin_page_learndash-lms-reports',
			'menu_link'	=> 'admin.php?page=learndash-lms-reports',
		),
		22 => array(
			'link'	=> 'edit.php?post_type=sfwd-transactions',
			'name'	=> __( 'Transactions', 'learndash' ),
			'id'	=> 'edit-sfwd-transactions',
			'menu_link'	=> 'admin.php?page=learndash-lms-reports',
		),
		170 => array(
			'link'	=> 'edit.php?post_type=groups',
			'name'	=> __( 'LearnDash Groups', 'learndash' ),
			'id'	=> 'edit-groups',
			'menu_link'	=> 'edit.php?post_type=groups',
		),
		180 => array(
			'link'	=> 'edit.php?post_type=sfwd-assignment',
			'name'	=> __( 'Assignments', 'learndash' ),
			'id'	=> 'edit-sfwd-assignment',
			'menu_link'	=> 'edit.php?post_type=sfwd-assignment',
		),
		'group_admin_page'	=> array(
			'id'	=> 'admin_page_group_admin_page',
			'name' 	=> __( 'Group Administration','learndash' ),
			'cap'	=> 'group_leader',
			'menu_link'	=> 'admin.php?page=group_admin_page',
		),
	);

	/**
	 * Filter array of tabs setup for LearnDash admin pages
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $admin_tabs
	 */
	$admin_tabs = apply_filters( 'learndash_admin_tabs', $admin_tabs );

	foreach ( $admin_tabs as $key => $admin_tab ) {
		if ( ! empty( $admin_tab['cap'] ) ) {
			if ( ! current_user_can( $admin_tab['cap'] ) ) {
				unset( $admin_tabs[ $key ] );
			}
		}
	}

	$admin_tabs_on_page = array(
			'edit-sfwd-courses'	=> array( 0, 10, 28, 24, 26 ),
			'sfwd-courses' => array( 0, 10, 28,  24, 26 ),
			'admin_page_learndash-lms-course_shortcodes' => array( 0, 10, 28,  24, 26 ),

			'edit-sfwd-lessons'	=> array( 30, 40, 50 ),
			'sfwd-lessons_page_sfwd-lms_sfwd_lms_post_type_sfwd-lessons' => array( 30, 40, 50 ),
			'sfwd-lessons' => array( 30, 40, 50 ),

			'edit-sfwd-topic' => array( 60, 70 ),
			'sfwd-topic' => array( 60, 70 ),

			'edit-sfwd-quiz' => array( 80, 90, 100, 101 ),
			'sfwd-quiz' => array( 80, 90, 100, 101 ),
			'sfwd-quiz_edit' => array( 80, 90, 100, 101, 95 ),
			'admin_page_ldAdvQuiz' => array( 80, 90, 100, 101 ),
			'admin_page_ldAdvQuiz_globalSettings' => array( 80, 90, 100, 101 ),

			'edit-sfwd-certificates' => array( 110, 120, 130 ),
			'admin_page_learndash-lms-certificate_shortcodes' => array( 110, 120, 130 ),
			'sfwd-certificates'	=> array( 110, 120, 130 ),

			'admin_page_learndash-lms-reports' => array( 160, 22 ),
			'edit-sfwd-transactions' => array( 160, 22 ),

			'sfwd-courses_page_sfwd-lms_sfwd_lms_post_type_sfwd-courses' => array( 135, 136, 140, 150 ),
			'admin_page_nss_plugin_license-sfwd_lms-settings' => array( 135, 136, 140, 150 ),
			'admin_page_learndash_custom_label' => array( 135, 136, 140, 150 ),
	);

	if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sfwd-courses' ) {
		$admin_tabs_on_page['edit-category'] = array( 0, 10, 28, 24, 26 );
		$admin_tabs_on_page['edit-post_tag'] = array( 0, 10, 28, 24, 26 );
	}

	$current_page_id = get_current_screen()->id;//echo $current_page_id;

	$post_id = ! empty( $_GET['post_id'] ) ? $_GET['post_id'] : ( empty( $_GET['post'] ) ? 0 : $_GET['post'] );

	if ( empty( $post_id ) && ! empty( $_GET['quiz_id'] ) && $current_page_id == 'admin_page_ldAdvQuiz' ) {
		$post_id = learndash_get_quiz_id_by_pro_quiz_id( $_GET['quiz_id'] );
	}

	if ( $current_page_id == 'sfwd-quiz' || $current_page_id == 'admin_page_ldAdvQuiz' ) {

		if ( ! empty( $_GET['module'] ) ) {
			$current_page_id = $current_page_id.'_'.$_GET['module'];
			if ( empty( $admin_tabs_on_page[ $current_page_id ] ) ) {
				$admin_tabs_on_page[ $current_page_id ] = $admin_tabs_on_page['admin_page_ldAdvQuiz'];
			}
		} else if ( ! empty( $_GET['post'] ) ) {
			$current_page_id = $current_page_id.'_edit';
		}

		if ( ! empty( $post_id ) ) {
			$quiz_id = learndash_get_setting( $post_id, 'quiz_pro', true );

			if ( ! empty( $quiz_id ) ) {
				$admin_tabs_on_page[ $current_page_id ] = array( 80, 90, 100, 101, 95, 102, 104, 106 );
				foreach ( $admin_tabs_on_page[ $current_page_id ] as $admin_tab_id ) {
					$admin_tabs[ $admin_tab_id ]['link'] = str_replace( '[quiz_id]', $quiz_id, $admin_tabs[ $admin_tab_id ]['link'] );
				}
			}

		}

	}

	/**
	 * Filter admin tabs on page
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $admin_tabs_on_page
	 */
	$admin_tabs_on_page = apply_filters( 'learndash_admin_tabs_on_page', $admin_tabs_on_page, $admin_tabs, $current_page_id );

	if ( empty( $admin_tabs_on_page[ $current_page_id ] ) ) {
		$admin_tabs_on_page[ $current_page_id ] = array();
	}

	/**
	 * Filter current admin tabs on page
	 *
	 * @since 2.1.0
	 *
	 * @param  array  $learndash_current_admin_tabs_on_page
	 */
	$admin_tabs_on_page[ $current_page_id ] = apply_filters( 'learndash_current_admin_tabs_on_page', $admin_tabs_on_page[ $current_page_id ], $admin_tabs, $admin_tabs_on_page, $current_page_id );

	//	echo $current_page_id;
	if ( ! empty( $post_id ) ) {
		foreach ( $admin_tabs_on_page[ $current_page_id ] as $admin_tab_id ) {
			$admin_tabs[ $admin_tab_id ]['link'] = str_replace( '[post_id]', $post_id, $admin_tabs[ $admin_tab_id ]['link'] );
		}
	}

	if ( ! empty( $admin_tabs_on_page[ $current_page_id ] ) && count( $admin_tabs_on_page[ $current_page_id ] ) ) {
		echo '<h1 class="nav-tab-wrapper">';
		$tabid = 0;
		foreach ( $admin_tabs_on_page[ $current_page_id] as $admin_tab_id ) {
			if ( ! empty( $admin_tabs[ $admin_tab_id ]['id'] ) ) {
				$class = ( $admin_tabs[ $admin_tab_id ]['id'] == $current_page_id ) ? 'nav-tab nav-tab-active' : 'nav-tab';
				$url = ! empty( $admin_tabs[ $admin_tab_id ]['external_link'] ) ? $admin_tabs[ $admin_tab_id ]['external_link'] : admin_url( $admin_tabs[ $admin_tab_id ]['link'] );
				$target = ! empty( $admin_tabs[ $admin_tab_id ]['target'] ) ? 'target="'.$admin_tabs[ $admin_tab_id ]['target'].'"':'';
				echo '<a href="'.$url.'" class="'.$class.' nav-tab-'.$admin_tabs[ $admin_tab_id ]['id'].'"  '.$target.'>'.$admin_tabs[ $admin_tab_id ]['name'].'</a>';
			}
		}
		echo '</h1>';
	}

	foreach ( $admin_tabs as $admin_tab ) {
		if ( $current_page_id == trim( $admin_tab['id'] ) ) {
			global $learndash_current_page_link;
			$learndash_current_page_link = trim( @$admin_tab['menu_link'] );
			add_action( 'admin_footer', 'learndash_select_menu' );
			break;
		}
	}
}

add_action( 'all_admin_notices', 'learndash_admin_tabs' );



/**
 * Change label in admin bar on single topic to 'Edit Topic'
 *
 * @todo  consider for deprecation, action is commented
 *
 * @since 2.1.0
 */
function learndash_admin_bar_link() {
	global $wp_admin_bar;
	global $post;

	if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
		return;
	}

	if ( is_single() && $post->post_type == 'sfwd-topic' ) {
		$wp_admin_bar->add_menu( array(
			'id' => 'edit_fixed',
			'parent' => false,
			'title' => sprintf( __( 'Edit %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'topic' ) ),
			'href' => get_edit_post_link( $post->id )
		) );
	}
}



/**
 * Output Reports Page
 *
 * @since 2.1.0
 */
function learndash_lms_reports_page() {
	?>
		<div  id="learndash-reports"  class="wrap">
			<h1><?php _e( 'User Reports', 'learndash' ); ?></h1>
			<br>
			<div class="sfwd_settings_left">
				<div class=" " id="sfwd-learndash-reports_metabox">
					<div class="inside">
						<a class="button-primary" href="<?php echo admin_url( 'admin.php?page=learndash-lms-reports&action=sfp_update_module&nonce-sfwd='.wp_create_nonce( 'sfwd-nonce' ).'&page_options=sfp_home_description&courses_export_submit=Export' ); ?>"><?php printf( __( 'Export User %s Data', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></a>
						<a class="button-primary" href="<?php echo admin_url( 'admin.php?page=learndash-lms-reports&action=sfp_update_module&nonce-sfwd='.wp_create_nonce( 'sfwd-nonce' ).'&page_options=sfp_home_description&quiz_export_submit=Export' ); ?>"><?php printf( __( 'Export %s Data', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?></a>
						<?php
							/**
							 * Run actions after report page buttons print
							 *
							 * @since 2.1.0
							 */
							do_action( 'learndash_report_page_buttons' );
						?>
					</div>
				</div>
			</div>
		</div>
	<?php
}



/**
 * Add Javascript to admin footer
 *
 * @since 2.1.0
 */
function learndash_select_menu() {
	global $learndash_current_page_link;
	?>
		<script type="text/javascript">
		jQuery(window).load( function( $) {
			jQuery("body").removeClass("sticky-menu");
			jQuery("#toplevel_page_learndash-lms, #toplevel_page_learndash-lms > a").removeClass('wp-not-current-submenu' );
			jQuery("#toplevel_page_learndash-lms").addClass('current wp-has-current-submenu wp-menu-open' );
			<?php if ( ! empty( $learndash_current_page_link ) ) : ?>
				jQuery("#toplevel_page_learndash-lms a[href='<?php echo $learndash_current_page_link;?>']").parent().addClass("current");
			<?php endif; ?>
		});
		</script>
	<?php
};



/**
 * Shortcode columns in admin for Quizes
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function add_shortcode_data_columns( $cols ) {
	return array_merge(
		array_slice( $cols, 0, 3 ),
		array( 'shortcode' => __( 'Shortcode', 'learndash' ) ),
		array_slice( $cols, 3 )
	);
}



/**
 * Assigned Course columns in admin for Lessons and Quizes
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function add_course_data_columns( $cols ) {
	return array_merge(
		array_slice( $cols, 0, 3 ),
		array( 
			'course' => sprintf( __( 'Assigned %s', 'learndash' ) , LearnDash_Custom_Label::get_label( 'course' ) ) 
		),
		array_slice( $cols, 3 )
	);
}



/**
 * Assigned Lesson & Assigned Course columns in admin for Topics and Assignments
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function add_lesson_data_columns( $cols ) {
	return array_merge(
		array_slice( $cols, 0, 3 ),
		array(
			'lesson' => sprintf( __( 'Assigned %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'lesson' ) ),
			'course' => sprintf( __( 'Assigned %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ),
		),
		array_slice( $cols, 3 )
	);
}



/**
 * Status columns in admin for Assignments
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function add_assignment_data_columns( $cols ) {
	return array_merge(
		array_slice( $cols, 0, 3 ),
		array(
			'approval_status' => __( 'Status', 'learndash' ),
		),
		array_slice( $cols, 3 )
	);
}



/**
 * Remove tags column for quizzes
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function remove_tags_column( $cols ){
	unset( $cols['tags'] );
	return $cols;
}



/**
 * Remove categories column for quizzes
 *
 * @since 2.1.0
 *
 * @param array 	$cols 	admin columns for post type
 * @return array 	$cols 	admin columns for post type
 */
function remove_categories_column( $cols ){
	unset( $cols['categories'] );
	return $cols;
}



/**
 * Output approval status for assignment in admin column
 *
 * @since 2.1.0
 *
 * @param  string 	$column_name
 * @param  int 		$id
 */
function manage_asigned_assignment_columns( $column_name, $id ) {
	switch ( $column_name ) {
		case 'approval_status':
			if ( learndash_is_assignment_approved_by_meta( $id ) ) {
				$url = admin_url( 'edit.php?post_type='.@$_GET['post_type'].'&approval_status=1' );
				echo '<a href="'.$url.'">'.__( 'Approved', 'learndash' ).'</a>';
			} else {
				$url = admin_url( 'edit.php?post_type='.@$_GET['post_type'].'&approval_status=0' );
				echo '<a href="'.$url.'">'.__( 'Not Approved', 'learndash' ).'</a>';
			}
			break;
	}
}



/**
 * Output values for Assigned Courses in admin columns
 * for lessons, quizzes, topics, assignments
 *
 * @since 2.1.0
 *
 * @param  string 	$column_name
 * @param  int 		$id
 */
function manage_asigned_course_columns( $column_name, $id ){
	switch ( $column_name ) {
		case 'shortcode':
			$quiz_pro = learndash_get_setting( $id, 'quiz_pro', true );
			if ( ! empty( $quiz_pro) ) {
				echo '[LDAdvQuiz '.$quiz_pro.']';
			} else {
				echo '-';
			}
			break;
		case 'course':
			$url = admin_url( 'edit.php?post_type='.@$_GET['post_type'].'&course_id='.learndash_get_course_id( $id ) );
			if ( learndash_get_course_id( $id ) ){
				echo '<a href="'.$url .'">'.get_the_title( learndash_get_course_id( $id ) ).'</a>';
			} else {
				echo '&#8212;';
			}
			break;

		case 'lesson':
			$parent_id = learndash_get_setting( $id, 'lesson' );
			if ( ! empty( $parent_id ) ) {
				$url = admin_url( 'edit.php?post_type='.@$_GET['post_type'].'&lesson_id='.$parent_id );
				echo '<a href="'.$url.'">'.get_the_title( $parent_id ).'</a>';
			} else {
				echo  '&#8212;';
			}
			break;
		default:
			break;
	}
}



/**
 * Output select dropdown before the filter button to filter post listing
 * by course
 *
 * @since 2.1.0
 */
function restrict_listings_by_course() {
	global $pagenow;

	if ( is_admin() AND $pagenow == 'edit.php'  AND isset( $_GET['post_type'] ) AND ( $_GET['post_type'] == 'sfwd-lessons' OR $_GET['post_type'] == 'sfwd-topic' OR $_GET['post_type'] == 'sfwd-quiz' OR $_GET['post_type'] == 'sfwd-assignment') ) {

		$filters = get_posts( 'post_type=sfwd-courses&posts_per_page=-1' );
		echo "<select name='course_id' id='course_id' class='postform'>";
		echo "<option value=''>". sprintf( __( 'Show All %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'courses' ) ).'</option>';

		foreach ( $filters as $post ) {
			echo '<option value='. $post->ID, ( $_GET['course_id'] == $post->ID ? ' selected="selected"' : '').'>' . $post->post_title .'</option>';
		}

		echo '</select>';

		if ( $_GET['post_type'] == 'sfwd-topic' OR $_GET['post_type'] == 'sfwd-assignment' ) {
			$filters = get_posts( 'post_type=sfwd-lessons&posts_per_page=-1' );
			echo "<select name='lesson_id' id='lesson_id' class='postform'>";
			echo "<option value=''>".sprintf( __( 'Show All %s', 'learndash' ), LearnDash_Custom_Label::get_label( 'lessons' ) ).'</option>';
			foreach ( $filters as $post ) {
				echo '<option value='. $post->ID, ( $_GET['lesson_id'] == $post->ID ? ' selected="selected"' : '').'>' . get_the_title( $post->ID ) .'</option>';
			}
			echo '</select>';
		}

		if ( $_GET['post_type'] == 'sfwd-assignment' ) {
			if ( isset( $_GET['approval_status'] ) ) {
				if ( $_GET['approval_status'] == 1 ) {
					$selected_1 = 'selected="selected"';
					$selected_0 = '';
				}
			} else if ( $_GET['approval_status'] == 0 ) {
				$selected_0 = 'selected="selected"';
				$selected_1 = '';
			}

			?>
				<select name='approval_status' id='approval_status' class='postform'>
					<option value='-1'><?php _e( 'Approval Status', 'learndash' ); ?></option>
					<option value='1' <?php echo $selected_1; ?>><?php _e( 'Approved', 'learndash' ); ?></option>
					<option value='0' <?php echo $selected_0; ?>><?php _e( 'Not Approved', 'learndash' ); ?></option>
				</select>
			<?php
		}

	}
}



/**
 * Filter queries in admin post listing by what user selects
 *
 * @since 2.1.0
 *
 * @param  object $query 	WP_Query object
 * @return object $q_vars    WP_Query object
 */
function course_table_filter( $query ) {
	global $pagenow;
	$q_vars = &$query->query_vars;

	if ( is_admin() AND $pagenow == 'edit.php'  AND ! empty( $_GET['course_id'] ) AND ( $query->query['post_type'] == 'sfwd-lessons' OR $query->query['post_type'] == 'sfwd-topic' OR $query->query['post_type'] == 'sfwd-quiz' OR $query->query['post_type'] == 'sfwd-assignment') ) {
		$q_vars['meta_query'][] = array(
			'key' => 'course_id',
			'value'	=> $_GET['course_id'],
		);
	}

	if ( is_admin() AND $pagenow == 'edit.php'  AND ! empty( $_GET['lesson_id'] ) AND ( $query->query['post_type'] == 'sfwd-topic' OR $query->query['post_type'] == 'sfwd-assignment') ) {
		$q_vars['meta_query'][] = array(
			'key' => 'lesson_id',
			'value'	=> $_GET['lesson_id'],
		);
	}

	if ( is_admin() AND $pagenow == 'edit.php'  AND isset( $_GET['approval_status'] ) AND ( $query->query['post_type'] == 'sfwd-topic' OR $query->query['post_type'] == 'sfwd-assignment') ) {
		if ( $_GET['approval_status'] == 1 ) {
			$q_vars['meta_query'][] = array(
				'key' => 'approval_status',
				'value'	=> 1,
			);
		} else if ( $_GET['approval_status'] == 0 ) {
			$q_vars['meta_query'][] = array(
				'key' => 'approval_status',
				'compare' => 'NOT EXISTS',
			);
		}
	}
}



/**
 * Generate lesson id's and course id's once for all existing lessons, quizzes and topics
 *
 * @since 2.1.0
 */
function learndash_generate_patent_course_and_lesson_id_onetime() {

	if ( isset( $_GET['learndash_generate_patent_course_and_lesson_ids_onetime'] ) || get_option( 'learndash_generate_patent_course_and_lesson_ids_onetime', 'yes' ) == 'yes' ) {
		$quizzes = get_posts( 'post_type=sfwd-quiz&posts_per_page=-1' );

		if ( ! empty( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {
				update_post_meta( $quiz->ID, 'course_id', learndash_get_course_id( $quiz->ID ) );
				$meta = get_post_meta( $quiz->ID, '_sfwd-quiz', true );
				if ( ! empty( $meta['sfwd-quiz_lesson'] ) ) {
					update_post_meta( $quiz->ID, 'lesson_id', $meta['sfwd-quiz_lesson'] );
				}
			}//exit;
		}

		$topics = get_posts( 'post_type=sfwd-topic&posts_per_page=-1' );

		if ( ! empty( $topics) ) {
			foreach ( $topics as $topic ) {
				update_post_meta( $topic->ID, 'course_id', learndash_get_course_id( $topic->ID ) );
				$meta = get_post_meta( $topic->ID, '_sfwd-topic', true );
				if ( ! empty( $meta['sfwd-topic_lesson'] ) ) {
					update_post_meta( $topic->ID, 'lesson_id', $meta['sfwd-topic_lesson'] );
				}
			}
		}

		$lessons = get_posts( 'post_type=sfwd-lessons&posts_per_page=-1' );

		if ( ! empty( $lessons) ) {
			foreach ( $lessons as $lesson ) {
				update_post_meta( $lesson->ID, 'course_id', learndash_get_course_id( $lesson->ID ) );
			}
		}

		update_option( 'learndash_generate_patent_course_and_lesson_ids_onetime', 'no' );

	}
}

add_action( 'admin_init', 'learndash_generate_patent_course_and_lesson_id_onetime' );



/**
 * On post save, update post id's that maintain relationships between
 * courses, lessons, topics, and quizzes
 *
 * @since 2.1.0
 *
 * @param  int $post_id
 */
function learndash_patent_course_and_lesson_id_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( empty( $post_id ) || empty( $_POST['post_type'] ) ) {
		return '';
	}

	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	if ( 'sfwd-lessons' == $_POST['post_type'] || 'sfwd-quiz' == $_POST['post_type'] || 'sfwd-topic' == $_POST['post_type'] ) {
		if ( isset( $_POST[ $_POST['post_type'].'_course'] ) ) {
			update_post_meta( $post_id, 'course_id', @$_POST[ $_POST['post_type'].'_course'] );
		}
	}

	if ( 'sfwd-topic' == $_POST['post_type'] || 'sfwd-quiz' == $_POST['post_type'] ) {
		if ( isset( $_POST[ $_POST['post_type'].'_lesson'] ) ) {
			update_post_meta( $post_id, 'lesson_id', @$_POST[ $_POST['post_type'].'_lesson'] );
		}
	}

	if ( 'sfwd-lessons' == $_POST['post_type'] || 'sfwd-topic' == $_POST['post_type'] ) {
		global $wpdb;

		if ( isset( $_POST[ $_POST['post_type'].'_course'] ) ) {
			$course_id = get_post_meta( $post_id, 'course_id', true );
		}

		if ( ! empty( $course_id ) ) {
			$posts_with_lesson = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'lesson_id' AND meta_value = '%d'", $post_id ) );

			if ( ! empty( $posts_with_lesson) && ! empty( $posts_with_lesson[0] ) ) {
				foreach ( $posts_with_lesson as $post_with_lesson ) {
					$post_course_id = learndash_get_setting( $post_with_lesson, 'course' );

					if ( $post_course_id != $course_id ) {
						learndash_update_setting( $post_with_lesson, 'course', $course_id );

						$quizzes_under_lesson_topic = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'lesson_id' AND meta_value = '%d'", $posts_with_lesson ) );
						if ( ! empty( $quizzes_under_lesson_topic) && ! empty( $quizzes_under_lesson_topic[0] ) ) {
							foreach ( $quizzes_under_lesson_topic as $quiz_post_id ) {
								$quiz_course_id = learndash_get_setting( $quiz_post_id, 'course' );
								if ( $course_id != $quiz_course_id ) {
									learndash_update_setting( $quiz_course_id, 'course', $course_id );
								}
							}
						}
					}
				}
			}

		}

	}
}

add_action( 'save_post', 'learndash_patent_course_and_lesson_id_save' );



/**
 * Output certificate shortcodes content on admin tab
 *
 * @since 2.1.0
 */
function learndash_certificate_shortcodes_page() {
	?>
		<div  id="certificate-shortcodes"  class="wrap">
			<h2><?php _e( 'Certificate Shortcodes', 'learndash' ); ?></h2>
			<div class='sfwd_options_wrapper sfwd_settings_left'>
				<div class='postbox ' id='sfwd-certificates_metabox'>
					<div class="inside">
					<?php
					echo __( '<b>Shortcode Options</b><p>You may use shortcodes to customize the display of your certificates. Provided is a built-in shortcode for displaying user information.</p><p><b>[usermeta]</b><p>This shortcode takes a parameter named field, which is the name of the user meta data field to be displayed.</p><p>Example: <b>[usermeta field="display_name"]</b> would display the user\'s Display Name.</p><p>See <a href="http://codex.wordpress.org/Function_Reference/get_userdata#Notes">the full list of available fields here</a>.</p>', 'learndash' ).
							'<p><b>[quizinfo]</b></p><p>' . __( 'This shortcode displays information regarding quiz attempts on the certificate. This short code can use the following parameters:', 'learndash' ) . '</p>

							<ul>
							<li><b>SHOW</b>: ' . __( 'This parameter determines the information to be shown by the shortcode. Possible values are:
								<ol class="cert_shortcode_parm_list">
									<li>score</li>
									<li>count</li>
									<li>pass</li>
									<li>timestamp</li>
									<li>points</li>
									<li>total_points</li>
									<li>percentage</li>
									<li>quiz_title</li>
									<li>course_title</li>
									<li>timespent</li>
								</ol>
								<br>Example: <b>[quizinfo show="percentage"]%</b> shows the percentage score of the user in the quiz.', 'learndash' ) . '<br><br></li>
							<li><b>FORMAT</b>: ' . __( 'This can be used to change the timestamp format. Default: "F j, Y, g:i a" shows as <i>March 10, 2001, 5:16 pm</i>. <br>Example: <b>[quizinfo show="timestamp" format="Y-m-d H:i:s"]</b> will show as <i>2001-03-10 17:16:18</i>', 'learndash' ) . '</li>
							</ul>
							<p>' . __( 'See <a target="_blank" href="http://php.net/manual/en/function.date.php">the full list of available date formating strings  here.</a>', 	'learndash' ) . '</p>
							<p><b>[courseinfo]</b></p><p>'. __( 'This shortcode displays course related information on the certificate. This short code can use the following parameters:', 'learndash' ) . '</p>
								<ul>
									<li><b>SHOW</b>: ' . __( 'This parameter determines the information to be shown by the shortcode. Possible values are:
										<ol class="cert_shortcode_parm_list">
											<li>course_title</li>
											<li>completed_on</li>
											<li>cumulative_score</li>
											<li>cumulative_points</li>
											<li>cumulative_total_points</li>
											<li>cumulative_percentage</li>
											<li>cumulative_timespent</li>
											<li>aggregate_percentage</li>
											<li>aggregate_score</li>
											<li>aggregate_points</li>
											<li>aggregate_total_points</li>
											<li>aggregate_timespent</li>
										</ol>
										<i>cumulative</i> is average for all quizzes of the course.<br>
										<i>aggregate</i> is sum for all quizzes of the course.<br>
									<br>Example: <b>[courseinfo show="cumulative_score"]</b> shows average points scored across all quizzes on the course.', 'learndash' ) . '<br><br></li>
									<li><b>FORMAT</b>: ' . __( 'This can be used to change the date format. Default: "F j, Y, g:i a" shows as <i>March 10, 2001, 5:16 pm</i>. <br>Example: <b>[courseinfo show="completed_on" format="Y-m-d H:i:s"]</b> will show as <i>2001-03-10 17:16:18</i>', 'learndash' ) . '</li>
								</ul>
							<p>' . __( 'See <a target="_blank" href="http://php.net/manual/en/function.date.php">the full list of available date formating strings  here.</a>',      'learndash' ) . '</p>';
					?>
					</div>
				</div>
			</div>
		</div>
	<?php
}



/**
 * Output course shortcodes content on admin tab
 *
 * @since 2.1.0
 */
function learndash_course_shortcodes_page() {
	?>
	<div  id='course-shortcodes'  class='wrap'>
		<h1><?php printf( __( '%s Shortcodes', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ) ); ?></h1>
		<div class='sfwd_options_wrapper sfwd_settings_left'>
			<div class='postbox ' id='sfwd-course_metabox'>
				<div class='inside'>
				<?php
				echo '<b>' . __( 'Shortcode Options', 'learndash' ) . '</b>
					<p>' . __( 'You may use shortcodes to add information to any page/course/lesson/quiz. Here are built-in shortcodes for displaying relavent user information.', 'learndash' ) . '</p>
					<p><b>[ld_profile]</b></p><p>' . __( 'Displays user\'s enrolled courses, course progress, quiz scores, and achieved certificates.', 'learndash' ) . '</p>

					<br>
					<p><b>[ld_course_list]</b></p><p>' . __( 'This shortcode shows list of courses. You can use this short code on any page if you dont want to use the default /courses page. This short code can take following parameters:', 'learndash' ) . '</p>
					<ul>
					<li><b>num</b>: ' . __( 'limits the number of courses displayed. Example: <b>[ld_course_list num="10"]</b> shows 10 courses.', 'learndash' ) . '</li>
					<li><b>order</b>: ' . __( 'sets order of courses. Possible values: <b>DESC</b>, <b>ASC</b>. Example: <b>[ld_course_list order="ASC"]</b> shows courses in ascending order.', 'learndash' ) . '</li>
					<li><b>orderby</b>: ' . __( 'sets what the list of ordered by. Example: <b>[ld_course_list order="ASC" orderby="title"]</b> shows courses in ascending order by title.', 'learndash' ) . '</li>
					<li><b>tag</b>: ' . __( 'shows courses with mentioned tag. Example: <b>[ld_course_list tag="math"]</b> shows courses having tag math.', 'learndash' ) . '</li>
					<li><b>tag_id</b>: ' . __( 'shows courses with mentioned tag_id. Example: <b>[ld_course_list tag_id="30"]</b> shows courses having tag with tag_id 30.', 'learndash' ) . '</li>
					<li><b>cat</b>: ' . __( 'shows courses with mentioned category id. Example: <b>[ld_course_list cat="10"]</b> shows courses having category with category id 10.', 'learndash' ) . '</li>
					<li><b>category_name</b>: ' . __( 'shows courses with mentioned category slug. Example: <b>[ld_course_list category_name="math"]</b> shows courses having category slug math.', 'learndash' ) . '</li>
					<li><b>mycourses</b>: ' . __( 'show current user\'s courses. Example: <b>[ld_course_list mycourses="true"]</b> shows courses the current user has access to.', 'learndash' ) . '</li>
					<li><b>categoryselector</b>: ' . __( 'shows a category dropdown. Example: <b>[ld_course_list categoryselector="true"]</b>.', 'learndash' ) . '</li>
					<li><b>col</b>: ' . __( 'number of columns to show when using course grid addon. Example: <b>[ld_course_list col="2"]</b> shows 2 columns.', 'learndash' ) . '</li>
					</ul>
					<p>' . __( 'See <a target="_blank" href="https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters">the full list of available orderby options here.</a>', 'learndash' ) . '</p>
					<br>
					<p><b>[ld_lesson_list]</b></p><p>' . __( 'This shortcode shows list of lessons. You can use this short code on any page. This short code can take following parameters: num, order, orderby, tag, tag_id, cat, category_name. See [ld_course_list] above details on using the shortcode parameters.', 'learndash' ) . '</p>
					<br>
					<p><b>[ld_quiz_list]</b></p><p>' . __( 'This shortcode shows list of quizzes. You can use this short code on any page. This short code can take following parameters: num, order, orderby, tag, tag_id, cat, category_name.. See [ld_course_list] above details on using the shortcode parameters.', 'learndash' ) . '</p>
					<br>
					<p><b>[learndash_course_progress]</b></p><p>' . __( 'This shortcode displays users progress bar for the course in any course/lesson/quiz pages.', 'learndash' ) . '</p>
					<br>
					<p><b>[visitor]</b></p><p>' . __( 'This shortcode shows the content if the user is not enrolled in the course. Example usage: <strong>[visitor]</strong>Welcome Visitor!<strong>[/visitor]</strong>', 'learndash' ) . '</p>
					<br>
                    <p><b>[student]</b></p><p>' . __( 'This shortcode shows the content if the user is enrolled in the course. Example usage: <strong>[student]</strong>Welcome Student!<strong>[/student]</strong>', 'learndash' ) . '</p>
					<br>
					<p><b>[course_complete]</b></p><p>' . __( 'This shortcode shows the content if the user has completed the course. Example usage: <strong>[course_complete]</strong> You have completed this course. <strong>[/course_complete]</strong>', 'learndash' ) . '</p>
                    <br>
					<p><b>[user_groups]</b></p><p>' . __( 'This shortcode displays the list of groups users are assigned to as users or leaders.', 'learndash' ) . '</p>
					<br>
					<p><b>[learndash_payment_buttons]</b></p><p>' . sprintf( __( 'This shortcode displays can show the payment buttons on any page. Example: <strong>[learndash_payment_buttons course_id="123"]</strong> shows the payment buttons for %s with %s ID: 123', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::get_label( 'courses' ) ) . '</p>
					<br>
					<p><b>[course_content]</b></p><p>' . sprintf( __( 'This shortcode displays the %s Content table (course lessons, topics, and quizzes) when inserted on a page or post. Example: <strong>[course_content course_id="123"]</strong> shows the course %s for course with %s ID: 123', 'learndash' ), LearnDash_Custom_Label::get_label( 'course' ), LearnDash_Custom_Label::label_to_lower( 'course' ), LearnDash_Custom_Label::get_label( 'course' ) ) . '</p>
					';
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}



/**
 * Add action links to quizzes post listing on post hover
 * Questions, Statistics, Leaderboard
 *
 * @since 2.1.0
 *
 * @param array   $actions An array of row action links
 * @param WP_Post $post    The post object.
 * @return array  $actions An array of row action links
 */
function learndash_quizzes_inline_actions( $actions, $post ) {
	if ( $post->post_type == 'sfwd-quiz' ) {
		$pro_quiz_id = learndash_get_setting( $post, 'quiz_pro', true );

		if ( empty( $pro_quiz_id ) ) {
			return $actions;
		}

		$statistics_link = admin_url( 'admin.php?page=ldAdvQuiz&module=statistics&id='.$pro_quiz_id.'&post_id='.$post->ID );
		$questions_link = admin_url( 'admin.php?page=ldAdvQuiz&module=question&quiz_id='.$pro_quiz_id.'&post_id='.$post->ID );
		$leaderboard_link = admin_url( 'admin.php?page=ldAdvQuiz&module=toplist&id='.$pro_quiz_id.'&post_id='.$post->ID );

		$actions['questions'] = "<a href='".$questions_link."'>".__( 'Questions', 'learndash' ).'</a>';
		$actions['statistics'] = "<a href='".$statistics_link."'>".__( 'Statistics', 'learndash' ).'</a>';
		$actions['leaderboard'] = "<a href='".$leaderboard_link."'>".__( 'Leaderboard', 'learndash' ).'</a>';
	}

	return $actions;
}

add_filter( 'post_row_actions', 'learndash_quizzes_inline_actions', 10, 2 );
