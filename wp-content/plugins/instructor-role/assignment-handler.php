<?php
/**
 * on the assignment page, author is not set to main query, so all assignment posts load. It shows assignments whose course's author is current user.
 */
add_filter('wp', 'wdm_show_assignments_of_my_course');


/**
 * Function to display assignments of instructor.
 */
function wdm_show_assignments_of_my_course()
{

    global $wp_query;

    if (wdm_is_instructor()) {
        if (count($wp_query->posts) > 0) {
            $wdm_user_id = get_current_user_id();

            foreach ($wp_query->posts as $wdm_key => $wdm_post) {
                $post_id     = $wdm_post->ID;
                $course_id   = get_post_meta($post_id, 'course_id', true);

                if (! empty($course_id)) {
                    $authorID = wdm_get_author($course_id);

                    if ($wdm_user_id != $authorID) {
                        unset($wp_query->posts[ $wdm_key ]);

                        $wp_query->post_count    = $wp_query->post_count - 1;
                        $wp_query->found_posts   = $wp_query->found_posts - 1;
                    }
                }
            }
        }
    }
}


/**
 * for the admin edit post page.
 * It restricts to instructor if she tries to edit assignments of other students which are not part of her course.
 *  */
add_action('admin_init', 'wdm_restrict_assignment_edit');

function wdm_restrict_assignment_edit()
{

    $wdm_user_id = get_current_user_id();

    if (wdm_is_instructor($wdm_user_id)) {
        $post_id = get_the_ID();

        $course_id = get_post_meta($post_id, 'course_id', true);
        if (! empty($course_id)) {
            $authorID = wdm_get_author($course_id);
            if ($wdm_user_id != $authorID) {
                wp_die(__('Cheatin’ uh?'));
            }
        }
    }
}


/**
 *  to add/ remove actions
 */
add_action('init', 'wdm_assignment_actions', 11);

function wdm_assignment_actions()
{

    global $pagenow;

    if (is_admin() and 'edit.php' == $pagenow and isset($_GET['post_type']) and ( 'sfwd-lessons' == $_GET['post_type'] or 'sfwd-topic' == $_GET['post_type'] or 'sfwd-quiz' == $_GET['post_type'] or 'sfwd-assignment' == $_GET['post_type'] ) and wdm_is_instructor()) { // if assignment page
        remove_action('restrict_manage_posts', 'restrict_listings_by_course'); // removes default filter listings function of LD
        add_action('restrict_manage_posts', 'restrict_listings_by_course_wdm'); // removes new filter listings function
    }
}

function restrict_listings_by_course_wdm()
{

    $wdm_user_id = get_current_user_id();

    $filters = get_posts('post_type=sfwd-courses&posts_per_page=-1&author=' . $wdm_user_id);

    echo "<select name='course_id' id='course_id' class='postform'>";
    echo "<option value=''>" . __('Show All Courses', 'learndash') . '</option>';
    foreach ($filters as $post) {
        echo '<option value=' . $post->ID, ($_GET['course_id'] == $post->ID ? ' selected="selected"' : '') . '>' . $post->post_title . '</option>';
    }
    echo '</select>';

    if ('sfwd-topic' == $_GET['post_type'] or  'sfwd-assignment' == $_GET['post_type']) {
        $filters = get_posts('post_type=sfwd-lessons&posts_per_page=-1&author=' . $wdm_user_id);
        echo "<select name='lesson_id' id='lesson_id' class='postform'>";
        echo "<option value=''>" . __('Show All Lessons', 'learndash') . '</option>';
        foreach ($filters as $post) {
            echo '<option value='.conditionalOperatorforRestrictListingByCourse($_GET['lesson_id'], $post->ID).'>'. get_the_title($post->ID) . '</option>';
        }
        echo '</select>';
    }
    if ('sfwd-assignment' == $_GET['post_type']) {
        if (isset($_GET['approval_status'])) {
            if (1 == $_GET['approval_status']) {
                $selected_1  = 'selected="selected"';
                $selected_0  = '';
            }
        } elseif (0 == $_GET['approval_status']) {
            $selected_0  = 'selected="selected"';
            $selected_1  = '';
        }
        ?>
		<select name='approval_status' id='approval_status' class='postform'>
			<option value='-1'><?php _e('Approval Status', 'learndash'); ?></option>
			<option value='1' <?php echo $selected_1; ?>><?php _e('Approved', 'learndash'); ?></option>	
			<option value='0' <?php echo $selected_0; ?>><?php _e('Not Approved', 'learndash'); ?></option>	
		</select>
		<?php
    }
}


/**
 * Function to check lesson id is matching or not with post id.
 */
function conditionalOperatorforRestrictListingByCourse($lesson_id, $post_id)
{
    if ($lesson_id == $post_id) {
        return 'selected="selected"';
    }
    return '';
}


/**
 * To remove author meta box from the edit assignment page.
 */
add_action('admin_init', 'wdm_remove_assignment_author');
function wdm_remove_assignment_author()
{
    if (wdm_is_instructor()) {
        remove_meta_box('authordiv', 'sfwd-assignment', 'normal');
    }
}
