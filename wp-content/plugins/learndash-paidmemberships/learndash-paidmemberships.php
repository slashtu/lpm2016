<?php
/**
 * @package LearnDash & Paid Memberships Pro
 * @version 1.0.4
 */
/*
/*
Plugin Name: LearnDash & Paid Memberships Pro
Plugin URI: http://www.learndash.com
Description: LearnDash integration with the Paid Memberships Pro plugin that allows to control the course's access by a user level.
Version: 1.0.4
Author: LearnDash
Author URI: http://www.learndash.com
*/


if(!class_exists('Learndash_Paidmemberships')) {

class Learndash_Paidmemberships{
	static function i18nize() {
		load_plugin_textdomain( 'ld_paidmemberships', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 	
	}

	static function addResources(){
		wp_enqueue_style('ld_paidmemberships', plugins_url('css/ld_paidmemberships.css', __FILE__));
		//wp_enqueue_script('ld_paidmemberships', plugins_url('js/propanel.js', __FILE__), array('jquery'));
	}

	static function admin_init(){
		add_meta_box("credits_meta", "Require Membership", array('Learndash_Paidmemberships', "course_level_list"), "sfwd-courses", "side", "low");
	}

	static function course_level_list(){
		global $post;
		global $wpdb;
		global $membership_levels;
		if(!isset($wpdb->pmpro_membership_levels))
		{
			_e("Please enable Paid Memberships Pro Plugin, and create some levels", "learndash");
			return;
		}	
		$membership_levels = $wpdb->get_results( "SELECT * FROM {$wpdb->pmpro_membership_levels}", OBJECT );
		?>
		
		<?php
		$course_id = learndash_get_course_id($post->ID);
		$level_course_option = get_option('_level_course_option');
		$array_levels=explode(",",$level_course_option[$course_id]);
		
		for($num_cursos=0;$num_cursos<sizeof($membership_levels);$num_cursos++)
		{
			$checked="";
			for($tmp_array_levels=0;$tmp_array_levels<sizeof($array_levels);$tmp_array_levels++){
				if($array_levels[$tmp_array_levels]==$membership_levels[$num_cursos]->id){	
					$checked="checked";
				}
			}
			?>
			<p><input type="checkbox" name="level-curso[<?php echo $num_cursos ?>]" value="<?php echo $membership_levels[$num_cursos]->id; ?>" <?php echo $checked; ?>> <?php echo $membership_levels[$num_cursos]->name; ?></p>
			<?php
		}
	}

	function level_courses_list(){
		global $wpdb;
		?>		
		<h3 class="topborder"><?php _e('LearnDash', 'learndash_pmp');?></h3>
		
		
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e('Courses', 'learndash_pmp');?>:</label></th>
					<td>

		<?php
		echo "<ul>";


		
		$querystr = "SELECT wposts.* FROM $wpdb->posts wposts WHERE wposts.post_type = 'sfwd-courses' AND wposts.post_status = 'publish' ORDER BY wposts.post_title";
		
		$actual_level = $_REQUEST['edit'];
		$level_course_option = get_option('_level_course_option');
		
		$my_query = $wpdb->get_results($querystr, OBJECT);
		
		if( $my_query ) {
			$tmp_num_cursos=0;
			foreach( $my_query as $s ) {
				$checked = '';
				$tmp_levels_course=explode(",",$level_course_option[$s->ID]);
				if(in_array($actual_level, $tmp_levels_course)){
					$checked = 'checked';
				}
				?>
				<li><input type="checkbox" name="cursos[<?php echo $tmp_num_cursos; ?>]" value="<?php echo $s->ID ?>" <?php echo $checked; ?>> <?php echo $s->post_title; ?></li>
				<?php
				$tmp_num_cursos+=1;
			}


			echo "</ul>";


		}
		
		?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		
	}

	function generate_access_list($course_id, $levels){
		global $wpdb;
		$levels_sql = implode(',', $levels);
		$users = $wpdb->get_results("SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id IN ($levels_sql) AND status='active'");
		$user_ids = array();
		foreach($users as $user){
			$user_ids[] = $user->user_id;			
		}

		$meta = get_post_meta( $course_id, '_sfwd-courses', true );
		Learndash_Paidmemberships::reassign_access_list($course_id, $user_ids);
	}

	static function reassign_access_list($course_id, $access_list) {
		$meta = get_post_meta( $course_id, '_sfwd-courses', true );
		$old_access_list = explode(",", $meta['sfwd-courses_course_access_list']);
		foreach ($access_list as $user_id) {
			if(!in_array($user_id, $old_access_list))
				ld_update_course_access($user_id, $course_id); //Add user who was not in old list
		}
		foreach ($old_access_list as $user_id) {
			if(!in_array($user_id, $access_list))
				ld_update_course_access($user_id, $course_id, true); //Remove user who was in old list but not in new list
		}
		$meta = get_post_meta( $course_id, '_sfwd-courses', true );
	
		$level_course_option = get_option('_level_course_option');	
		if(!empty($level_course_option[$course_id]))
			$meta['sfwd-courses_course_price_type'] = 'closed';

//		$meta['sfwd-courses_course_price'] = 'Membership';
		update_post_meta( $course_id, '_sfwd-courses', $meta );
	}

	function save_level_details($saveid){
		global $wpdb;
		$users_pro_list=$wpdb->get_results("SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id = '$saveid' AND status='active'", ARRAY_N);
		//$users_pro_list_id=$wpdb->get_results("SELECT user_id FROM {$wpdb->pmpro_memberships_users} WHERE membership_id = '$saveid'", ARRAY_N);
		
		$new_courses = $_POST['cursos'] ? $_POST['cursos'] : array();

		$courses = get_posts(array(
			'post_type' => 'sfwd-courses',
			'post_status' => 'publish',
			'posts_per_page'   => -1
		));

		$courses_levels = get_option('_level_course_option');

		foreach($courses as $course){
			$refresh = false;
			$levels = $courses_levels[$course->ID] ? explode(',', $courses_levels[$course->ID]) : array();

			//If the course is in the level and it wasn't add it
			if(array_search($course->ID, $new_courses) !== FALSE && array_search($saveid, $levels) === FALSE){
				$refresh = true;
				$levels[] = $saveid;
				$courses_levels[$course->ID] = implode(',', $levels);
			}

			// When the course is not in the level but it was
			else if(array_search($course->ID, $new_courses) === FALSE && array_search($saveid, $levels) !== FALSE){				
				$refresh = true;
				$level_index = array_search($saveid, $levels);
				unset($levels[$level_index]);
				$courses_levels[$course->ID] = implode(',', $levels);
			}

			if($refresh){
				self::generate_access_list($course->ID, $levels);
			}
		}

		update_option("_level_course_option",$courses_levels);
	}

	static function save_details($post_id){
		global $post;
		global $table_prefix, $wpdb;

		if( $_POST['post_type'] && $_POST['post_type'] == 'sfwd-courses'){
			$course_id = learndash_get_course_id($post->ID);
			$meta = get_post_meta( $course_id, '_sfwd-courses', true );
			$access_list = $meta['sfwd-courses_course_access_list']; 
			$level_course_option = get_option('_level_course_option');

			$access_list = array();
			$levels_list = array();

			if ($_POST["level-curso"]) {
				$tmp_levels_list=0;
				foreach ($_POST["level-curso"] as $x) {
					$users_pro_list=$wpdb->get_results("SELECT * FROM {$wpdb->pmpro_memberships_users} WHERE membership_id = '$x' AND status='active'", ARRAY_N);
					foreach ($users_pro_list as $user_pro){
						$access_list[]=$user_pro[1];			
					}
					$levels_list[].=$x;			
				}

				$levels_list_tmp=implode(',',$levels_list);
				$level_course_option[$course_id] = $levels_list_tmp;
			}
			else {
				$level_course_option[$course_id] = '';
			}
			Learndash_Paidmemberships::reassign_access_list($course_id, $access_list);			
			update_option("_level_course_option", $level_course_option);
		}
	}

	static function user_change_level($level, $user_id) {

		$courses = get_posts(array(
			'post_type' => 'sfwd-courses',
			'post_status' => 'publish',
			'posts_per_page'   => -1
		));

		$courses_levels = get_option('_level_course_option');

		foreach($courses as $course){

			$meta = get_post_meta( $course->ID, '_sfwd-courses', true );
			$access = $meta['sfwd-courses_course_access_list'];
			$access_list = array();
			$refresh = false;
			if($access)
				$access_list = explode(',', $access);


			$levels = $courses_levels[$course->ID] ? explode(',', $courses_levels[$course->ID]) : array();
			if(array_search($level, $levels) === FALSE){
				//Remove the user
				ld_update_course_access($user_id, $course->ID, true);
			}
			else{
				//Add the user
				ld_update_course_access($user_id, $course->ID);
			}
		}	
	}

	static function replace_paypal_button($button) {
		return __('You must purchase this course or log-in to view the content.', 'learndash_pmp');
	}
}

add_action( 'admin_enqueue_scripts', array('Learndash_Paidmemberships','addResources'));
add_action( 'plugins_loaded', array('Learndash_Paidmemberships','i18nize'));
add_action( "admin_init", array('Learndash_Paidmemberships', "admin_init"));
add_action( 'save_post', array('Learndash_Paidmemberships', 'save_details'), 1000);
add_action( "pmpro_membership_level_after_other_settings", array('Learndash_Paidmemberships', "level_courses_list"));
add_action( 'pmpro_save_membership_level', array('Learndash_Paidmemberships', 'save_level_details'));
add_action( 'pmpro_after_change_membership_level', array('Learndash_Paidmemberships', 'user_change_level'), 1000,2);
add_filter( 'learndash_payment_button', array('Learndash_Paidmemberships', 'replace_paypal_button'));


}

