<?php
/**
 * to check user role is instructor or not
 * @param int $user_id wp user id, if user_id is null then it considers current logged in user_id
 * @return boolean if instructor true, else false
 */
if (! function_exists('wdm_is_instructor')) {
    function wdm_is_instructor($user_id = null)
    {

        if (empty($user_id)) {
            $user_id = get_current_user_id();
        }

        if (is_multisite()) {
            $get_users_obj = get_users(
                array(
                    'blog_id' => get_current_blog_id(),
                    'search'  => $user_id,
                )
            );

                $wdm_roles = $get_users_obj[0]->caps;
        } else {
            //Added in v1.4, because WordPress stores meta key of capabilities using prefix of mysql table.
            global $wpdb;
            $wdm_roles = get_user_meta($user_id, $wpdb->prefix.'capabilities', true);
        }

        if (isset($wdm_roles[ ROLE_ID ]) && $wdm_roles[ ROLE_ID ] == '1') {
            return true;
        }
        return false;
    }
}

/*
 * returns author id if post has author
 * @param int $post_id post id of post
 * @return int author_id author id of post
 */
if (! function_exists('wdm_get_author')) {
    function wdm_get_author($post_id = null)
    {

        if (empty($post_id)) {
            $post_id = get_the_ID();
        }
        if (empty($post_id)) {
            return;
        }

        $postdata = get_post($post_id);

        if (isset($postdata->post_author)) {
            return $postdata->post_author;
        }

        return;
    }
}
/**
 * get all users ids of a course
 * @param int $post_id post id of a course
 * @return array array of users id
 */
if (! function_exists('wdm_get_course_users')) {
    function wdm_get_course_users($post_id)
    {
        $course_access_users = array();

        if (empty($post_id)) {
            return $course_access_users;
        }
        /* commented because, it was not considering users added in the group.
        $course_meta = get_post_meta( $post_id, '_sfwd-courses', true );

        $users = isset( $course_meta[ 'sfwd-courses_course_access_list' ] ) ? $course_meta[ 'sfwd-courses_course_access_list' ] : '';

        $arr_users = explode( ',', $users );

        $course_access_users = array_filter( $arr_users );
         */
        $course_progress_data = array();
        $courses              = array( get_post($post_id) );
        $users                = get_users();
        if (! empty($users)) {
            reduceComplexityOfWdmGetCourseUsers($users, $courses, $course_progress_data);
        }
        
        foreach ($course_progress_data as $value) {
            array_push($course_access_users, $value['user_id']);
        }

        return $course_access_users;
    }
}

function reduceComplexityOfWdmGetCourseUsers($users, $courses, &$course_progress_data)
{
    foreach ($users as $u) {
        $user_id  = $u->ID;
        $usermeta = get_user_meta($user_id, '_sfwd-course_progress', true);
        $usermeta = unserializeForReduceComplexity($usermeta);
        if (! empty($usermeta)) {
            $usermeta = maybe_unserialize($usermeta);
        }

        if (! empty($courses[0])) {
            foreach ($courses as $course) {
                $c = $course->ID;

                if (empty($course->post_title) || ! sfwd_lms_has_access($c, $user_id)) {
                    continue;
                }

                $cv = conditionalOperatorForReduceComplexityFunction($usermeta[ $c ]);

                $cours_completed_meta                                   = get_user_meta($user_id, 'course_completed_' . $course->ID, true);
                $cours_completed_date = getCourseCompleteDate($cours_completed_meta);

                $row = array( 'user_id' => $user_id, 'name' => $u->display_name, 'email' => $u->user_email, 'course_id' => $c, 'course_title' => $course->post_title, 'total_steps' => $cv['total'], 'completed_steps' => $cv['completed'], 'course_completed' => ( ! empty($cv['total']) && $cv['completed'] >= $cv['total']) ? 'YES' : 'NO', 'course_completed_on' => $cours_completed_date );
                $i   = 1;
                if (! empty($cv['lessons'])) {
                    foreach ($cv['lessons'] as $lesson_id => $completed) {
                        if (! empty($completed)) {
                            if (empty($lessons[ $lesson_id ])) {
                                $lesson = $lessons[ $lesson_id ] = get_post($lesson_id);
                            } else {
                                $lesson = $lessons[ $lesson_id ];
                            }

                            $row[ 'lesson_completed_' . $i ] = $lesson->post_title;
                            $i++;
                        }
                    }
                }

                $course_progress_data[] = $row;
                //return $row;
            }
        }
    }
    
}

function unserializeForReduceComplexity($usermeta)
{
    if (! empty($usermeta)) {
            return maybe_unserialize($usermeta);
    }
    return '';
}

function getCourseCompleteDate($cours_completed_meta)
{
    if (empty($cours_completed_meta)) {
        $cours_completed_date = '';
    } else {
        $cours_completed_date = date('F j, Y H:i:s', $cours_completed_meta);
    }
    return $cours_completed_date;
    
}

function conditionalOperatorForReduceComplexityFunction($detail)
{
    if (!empty($detail)) {
        return $detail;
    }
    return array( 'completed' => '', 'total' => '' );
    
}

/**
 * to get course progress of a user
 * @param int $user_id user id of a user
 * @param int $course_id course id of a course
 * @return array course progress data
 */
if (! function_exists('wdm_get_course_progress_in_per')) {
    function wdm_get_course_progress_in_per($user_id, $course_id)
    {

        if (empty($user_id) || empty($course_id)) {
            return;
        }
        $percentage            = 0;
        $cours_completed_date = '-';
        $user_meta             = get_user_meta($user_id, '_sfwd-course_progress', true);
        if (! empty($user_meta)) {
            if (isset($user_meta[ $course_id ])) {
                $percentage = floor(($user_meta[ $course_id ]['completed'] / $user_meta[ $course_id ]['total']) * 100);

                $cours_completed_meta = get_user_meta($user_id, 'course_completed_' . $course_id, true);
                $cours_completed_date = ( ! empty($cours_completed_meta)) ? date('F j, Y H:i:s', $cours_completed_meta) : '';
            }

            $course_arr = array(
                'total_steps'         => $user_meta[ $course_id ]['total'],
                'completed_steps'     => $user_meta[ $course_id ]['completed'],
                'percentage'          => $percentage,
                'course_completed_on' => $cours_completed_date,
            );
        }
        return $course_arr;
    }
}

/**
 * to export course progress data into CSV
 */
add_action('admin_init', 'wdm_learndash_csv_export');

function wdm_learndash_csv_export()
{

    if (empty($_REQUEST['post_id_report']) || empty($_POST['post_id_report'])) {
        return;
    }

    $content = wdm_course_progress_data($_POST['post_id_report']);

    $file_name = sanitize_file_name(get_the_title($_POST['post_id_report']) . '-' . date('Y-m-d')); // file name to export

    if (empty($content)) {
        $content[] = array( 'status' => __('No attempts', 'learndash') );
    }
    require_once dirname(__FILE__) . '/parsecsv.lib.php';
    $csv = new lmsParseCSVNS\LmsParseCSV();
    $csv->output(true, $file_name . '.csv', $content, array_keys(reset($content)));
    die();
}

/**
 *  return course progress data
 * @param int $course_id course id of a course
 * @return array array of data
 */
function wdm_course_progress_data($course_id = null)
{
    $current_user = wp_get_current_user();
    if (empty($current_user) || ! current_user_can('instructor_reports')) {
        return;
    }

    $users              = get_users();


    $course_progress_data = array();

    $lessons     = array();

    $courses = getPostOfWdmCourseProgressData($course_id);
    if (! empty($users)) {
        foreach ($users as $u) {
            $user_id  = $u->ID;
            $usermeta = get_user_meta($user_id, '_sfwd-course_progress', true);

            $usermeta = unserializeForReduceComplexity($usermeta);
            if (! empty($courses[0])) {
                foreach ($courses as $course) {
                    $c = $course->ID;

                    if (empty($course->post_title) || ! sfwd_lms_has_access($c, $user_id)) {
                        continue;
                    }

                    $cv = conditionalOperatorForReduceComplexityFunction($usermeta[$c]);

                    $cours_completed_date = getCourseCompleteDate(cours_completed_meta);
                    $row = array( 'user_id' => $user_id, 'name' => $u->display_name, 'email' => $u->user_email, 'course_id' => $c, 'course_title' => $course->post_title, 'total_steps' => $cv['total'], 'completed_steps' => $cv['completed'], 'course_completed' => checkCompletedSteps($cv['total'], $cv['completed']), 'course_completed_on' => $cours_completed_date );
                    $i   = 1;
                    if (! empty($cv['lessons'])) {
                        foreach ($cv['lessons'] as $lesson_id => $completed) {
                            if (! empty($completed)) {
                                if (empty($lessons[ $lesson_id ])) {
                                    $lesson = $lessons[ $lesson_id ] = get_post($lesson_id);
                                } else {
                                    $lesson = $lessons[ $lesson_id ];
                                }

                                $row[ 'lesson_completed_' . $i ] = $lesson->post_title;
                                $i++;
                            }
                        }
                    }

                    $course_progress_data[] = $row;
                }
            }
        }
    } else {
        $course_progress_data[] = array( 'user_id' => $user_id, 'name' => $u->display_name, 'email' => $u->user_email, 'status' => __('No attempts', 'learndash') );
    }

    return $course_progress_data;
}

function getPostOfWdmCourseProgressData($course_id)
{
    if (! empty($course_id)) {
        $courses = array( get_post($course_id) );
    } else {
        $courses = ld_course_list(array( 'array' => true ));
    }
    return $courses;

}
function checkCompletedSteps($cv_total, $cv_completed)
{
    if (! empty($cv_total) && $cv_completed >= $cv_total) {
        return 'YES';
    }
    return 'NO';
}


/**
 * It generates an HTML to show reports on the course reports page.
 * It is used while page loading and ajax call as well.
 * @param int $current_post post id of selected course
 * @return JSON JSON of data
 */
add_action('wp_ajax_wdm_get_report_html', 'wdm_report_html');

function wdm_report_html($current_post = null)
{
    $current_post = checkIsSetCurrentPost($current_post, $_POST['course_id']);

    if (empty($current_post)) {
        return;
    }

    ob_start();

    $current_course_title = get_the_title($current_post);

    $course_access_users = wdm_get_course_users($current_post);
    $total_users         = count($course_access_users);
    $arr_course_status   = array( 'not_started' => 0, 'in_progress' => 0, 'completed' => 0, 'total' => $total_users );

    $wdm_course_users = array();

    $wdm_page_counter = 10;

    $wdm_page_counter = wdmPageCounter($wdm_page_counter, $_POST['wdm_pagination_select']);

    $wdm_page_users = array();
    $arr_count      = count($course_access_users);
    $wdm_page_count = 0;

    if (! empty($course_access_users)) {
        $cnt = 0;
        foreach ($course_access_users as $user_id) {
            $course_status_user = learndash_course_status($current_post, $user_id);

            $wdm_page_users[ $wdm_page_count ][] = $user_id;

            if (0 == ( ($cnt + 1) % $wdm_page_counter ) || ($cnt + 1) == $arr_count) {
                $wdm_page_users[ $wdm_page_count ] = implode(',', $wdm_page_users[ $wdm_page_count ]);

                $wdm_page_count++;
            }

            if ($cnt < $wdm_page_counter) {
                $user_meta                            = get_userdata($user_id);
                $wdm_course_users[ $cnt ]['user_name']  = $user_meta->data->user_login;
                $wdm_course_users[ $cnt ]['user_email'] = $user_meta->data->user_email;

                $course_progress = wdm_get_course_progress_in_per($user_id, $current_post);

                $wdm_course_users[ $cnt ]['completed_per']       = checkIsSet($course_progress['percentage']);
                $wdm_course_users[ $cnt ]['total_steps']         = checkIsSet($course_progress['total_steps']);
                $wdm_course_users[ $cnt ]['completed_steps']     = checkIsSet($course_progress['completed_steps']);
                $wdm_course_users[ $cnt ]['course_completed_on'] = checkCourseCompletedOn($course_progress['course_completed_on']);

            }

            switch ($course_status_user) {
                //case "Not Started":
                case __('Not Started', 'learndash'):
                    $arr_course_status['not_started']++;
                    break;
                //case "In Progress":
                case __('In Progress', 'learndash'):
                    $arr_course_status['in_progress']++;
                    break;
                //case "Completed":
                case __('Completed', 'learndash'):
                    $arr_course_status['completed']++;
                    break;
            }

            $cnt++;
        }
        $not_started_str = '';
        $in_progress_str = '';
        $completed_str   = '';
        $not_started_per = '';
        $in_progress_per = '';
        $completed_per   = '';

        updateCourseStatus($arr_course_status, $not_started_per, $in_progress_per, $completed_per, $not_started_str, $in_progress_str, $completed_str);
    } // if ( ! empty( $instructor_courses ) )

    $report_js_array = array(
        'not_started_text' => __('Not Started', 'learndash'),
        'in_progress_text' => __('In Progress', 'learndash'),
        'completed_text'   => __('Completed', 'learndash'),
        'not_started_per'  => checkIsSet($not_started_per),
        'in_progress_per'  => checkIsSet($in_progress_per),
        'completed_per'    => checkIsSet($completed_per),
        'graph_heading'    => __("Status of a \"{$current_course_title}\"", 'learndash'),
        'piece_title'      => __('Users', 'learndash'),
        'course_title'     => $current_course_title,
        'admin_ajax_path'  => admin_url('admin-ajax.php'),
        'paged_users'      => ($wdm_page_users),
        'paged_index'      => 0,
    );

    wp_localize_script('wdm_reports', 'wdm_reports_obj', $report_js_array);
    ?>
    <div id="wdm-report-graph">
        <div id="wdm_left_report">
            <?php
            echo $not_started_str;
            echo $in_progress_str;
            echo $completed_str;
    ?>
        </div>
        <div id="wdm_report_div" ></div><!-- highchart div -->
        <!--    added form for mail to all the users of that particular course -->
        <div id="mail_by_instructor">
            <form method="post" id="instructor_message_form">
                <h4 class="learndash_instructor_send_message_label"><?php echo sprintf(__('Send message to all %s users', 'learndash'), '<i>' . $current_course_title . '</i>');?></h4>
                <label><?php _e('Subject:', 'learndash');?> </label><span id="learndash_instructor_subject_err"></span><br>
                <input type="text" size="40" id="learndash_instructor_subject" name="learndash_instructor_subject" style="margin-bottom: 15px;"><br>
                <div class="learndash_instructor_message_label"><label for="learndash_instructor_message_label"><?php _e('Body:', 'learndash');?> </label><span id="learndash_instructor_message_err"></span></div><textarea id="learndash_instructor_message" rows="10" cols="40" id="learndash_propanel_message" name="learndash_instructor_message"></textarea><br>
                <input class="wdm-button" type="submit" name="submit_instructor_email" value="<?php _e('Send Email', 'learndash');?>">
                <input type="hidden" name="course_id" value="<?php echo $current_post;?>">
            </form>

        </div>
        <div class="CL" ></div>
    </div>
    <div id="user_info">
        <h3><?php echo __('User Information', 'learndash');?></h3>
        <div id="reports_table_div">
            <div class="CL"></div>
            <form action="" method="post" id="wdm_pagination_frm">
            <?php echo __('Search', 'learndash');?>
            <input id="filter" type="text"> <?php echo __('Show', 'learndash');?>
                <input type="hidden" value="<?php echo $current_post; ?>" name="course_id" />
                <select name="wdm_pagination_select" onchange="jQuery('#wdm_pagination_frm').submit();">
                    <option value="10" <?php paginationSelectData(10, $wdm_page_counter)

    ?>>10</option>
                    <option value="25" <?php paginationSelectData(25, $wdm_page_counter);

    ?>>25</option>
                    <option value="50" <?php paginationSelectData(50, $wdm_page_counter);

    ?>>50</option>
                    <option value="100" <?php paginationSelectData(100, $wdm_page_counter);

    ?>>100</option>
                </select> <?php echo __('Records', 'learndash');?>
            </form>
            <!--Table shows Name, Email, etc-->
            <table class="footable" data-page-navigation=".pagination" data-filter="#filter" id="wdm_report_tbl" >
                <thead>
                    <tr>
                        <th data-sort-initial="descending" data-class="expand">
                            Name
                        </th>
                        <th >
                            E-Mail ID
                        </th>
                        <th data-hide="phone" >
                            Progress %
                        </th>
                        <th data-hide="phone" >
                            Total Steps
                        </th>
                        <th data-hide="phone" >
                            Completed Steps
                        </th>
                        <th data-hide="phone,tablet" >
                            Completed On
                        </th>
                        <th data-hide="phone,tablet" data-sort-ignore="true">
                            Email
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                wdmGetUserHtmlFunction($wdm_page_users, $current_post);
                ?>
                </tbody>
<?php
writeTFooterfunction($wdm_page_count);
?>
            </table>
        </div>
        <div class="CL"></div>
        <form method="post" action="">
            <input type="hidden" value="<?php echo $current_post;?>" name="post_id_report" id="post_id_report" />
            <input class="wdm-button" type="submit" value="Export Course Data" />
        </form>


    </div>
    <!--For popup email div for individual-->
    <div id="popUpDiv" style="display: none; top: 245.75px; left: 17%;">
        <div style="clear:both"></div>
        <table class="widefat" id="wdm_tbl_staff_mail">
            <thead>
                <tr>
                    <th colspan="2">
                        <strong>Send E-Mail To Individual Member</strong>

            <p id="wdm_close_pop" colspan="1" onclick="popup( 'popUpDiv' )"><span>X</span></p>
            </th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        To
                    </td>
                    <td>
                        <input type="text" id="wdm_staff_mail_id" value="" readonly="readonly">
                    </td>
                </tr>
                <tr>
                    <td>
                        Subject
                    </td>
                    <td>
                        <input type="text" id="wdm_staff_mail_subject" value="">
                    </td>
                </tr>
                <tr>
                    <td>
                        Body
                    </td>
                    <td>
                        <textarea id="wdm_staff_mail_body" rows="8"></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input class="button-primary" type="button" name="wdm_btn_send_mail" value="Send E-Mail" id="wdm_btn_send_mail" onclick="wdm_individual_send_email();"><span id="wdm_staff_mail_msg"></span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!--Email popup-->
    <?php
    $output = ob_get_contents();
    ob_end_clean();

    $data = array(
        'html'            => $output,
        'not_started_per' => $not_started_per,
        'in_progress_per' => $in_progress_per,
        'completed_per'   => $completed_per,
        'graph_heading'   => __("Status of a \"{$current_course_title}\"", 'learndash'),
        'paged_users'     => ($wdm_page_users),
        'paged_index'     => 0,
    );

    if (isset($_POST['course_id'])  &&
    isset($_POST['request_type']) && 'ajax' == $_POST['request_type'] ) {
        echo json_encode($data);
        die();
    } else {
        return json_encode($data);
    }
}

function checkIsSetCurrentPost($current_post, $post_course_id)
{
    if (empty($current_post) && isset($post_course_id)) {
        return $post_course_id;
    }
    return $current_post;
}

function wdmPageCounter($wdm_page_counter, $wdm_pgination_select)
{
    if (isset($wdm_pgination_select) && $wdm_pgination_select < 101) {
        return $wdm_pgination_select;
    }
    return $wdm_page_counter;
}

function checkIsSet($course_progress_data)
{
    if (isset($course_progress_data)) {
        return $course_progress_data;
    }
    return 0;
    
}
function checkCourseCompletedOn($course_progress_data)
{
    if (isset($course_progress_data)) {
        return $course_progress_data;
    }
    return '-';
}
function wdmGetUserHtmlFunction($wdm_page_users, $current_post)
{
    if (count($wdm_page_users) > 0) {
        wdm_get_user_html($wdm_page_users[0], $current_post);
    }
}

function writeTFooterfunction($wdm_page_count)
{
    if ($wdm_page_count > 1) {
        ?>
                <tfoot class="wdm-pagination">
                    <tr>
                    <td colspan="10" id="wdm_paged_td">
        <?php
                echo wdm_get_paged_html($wdm_page_count);
        ?>
                    </td>
                    </tr>
                </tfoot>
        <?php
    }

}
function paginationSelectData($val, $wdm_page_counter)
{
    if ($val == $wdm_page_counter) {
            echo 'selected';
    }
}

function updateCourseStatus($arr_course_status, &$not_started_per, &$in_progress_per, &$completed_per, &$not_started_str, &$in_progress_str, &$completed_str)
{
    if ($arr_course_status['total'] > 0) {
            $not_started_per = round(($arr_course_status['not_started'] / $arr_course_status['total']) * 100, 2);
            $in_progress_per = round(($arr_course_status['in_progress'] / $arr_course_status['total']) * 100, 2);
            $completed_per   = 100 - $not_started_per - $in_progress_per;

            $not_started_str = '<div id="not_started">
                                <span class="color-code"></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                ' . __('Not Started', 'learndash') . ': ' . ($not_started_per) . '% ( ' . $arr_course_status['not_started'] . '/' . $arr_course_status['total'] . ' )
                                </div>';

            $in_progress_str = '<div id="in_progress">
                                <span class="color-code"></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                ' . __('In Progress', 'learndash') . ': ' . ($in_progress_per) . '% ( ' . $arr_course_status['in_progress'] . '/' . $arr_course_status['total'] . ' )
                                </div>';

            $completed_str = '<div id="completed">
                                <span class="color-code"></span>&nbsp;&nbsp;&nbsp;&nbsp;
                                ' . __('Completed', 'learndash') . ': ' . ($completed_per) . '% ( ' . $arr_course_status['completed'] . '/' . $arr_course_status['total'] . ' )
                                </div>';
    }

}
/**
 * To create HTML for pagination
 * @param int $last_page_number last number of the pagination
 * @return string $str string of pagination
 */
function wdm_get_paged_html($last_page_number)
{

    $str = '';
    if ($last_page_number <= 1) {
        return;
    }

    $str = '<div class="tablenav-pages">
                <span class="pagination-links">
                    <a class="first-page wdm-paged" id="wdm_first_page" title="Go to the first page" href="javascript:wdm_js_ajax_pagination(0);">«</a>
                    <a class="prev-page wdm-paged" id="wdm_prev_page" title="Go to the previous page" href="javascript:wdm_js_ajax_pagination(0);">‹</a>
                    <span class="paging-input"><span id="wdm_paged_start_num">1</span> of <span class="total-pages">' . $last_page_number . '</span></span>
                    <a class="next-page wdm-paged" id="wdm_next_page" title="Go to the next page" href="javascript:wdm_js_ajax_pagination(1);">›</a>
                    <a class="last-page wdm-paged" id="wdm_last_page" title="Go to the last page" href="javascript:wdm_js_ajax_pagination(\'' . ($last_page_number - 1) . '\');">»</a>
                </span>
            </div>';

    return $str;
}

/**
 *
 * @return JSON
 */
add_action('wp_ajax_wdm_get_user_html', 'wdm_get_user_html');
/**
 * This function is used to create HTML table rows in report table to given user ids and course id
 */
function wdm_get_user_html($users = '', $current_post = '')
{
    $return_str = '';
    
    $user_ids = wdmGetUsersInGetUserHtml($_POST['users'], $users);
    $user_ids   = explode(',', $user_ids);
    $user_ids   = array_filter($user_ids);
    $current_post = isset($_POST['current_post']) ? $_POST['current_post'] : $current_post;

    if (! empty($user_ids) && ! empty($current_post)) {
        $wdm_course_users = array();
        $cnt              = 0;
        foreach ($user_ids as $user_id) {
            $user_meta                            = get_userdata($user_id);
            $wdm_course_users[ $cnt ]['user_name']  = $user_meta->data->user_login;
            $wdm_course_users[ $cnt ]['user_email'] = $user_meta->data->user_email;

            $course_progress = wdm_get_course_progress_in_per($user_id, $current_post);

            $wdm_course_users[ $cnt ]['completed_per']       = checkIsSet($course_progress['percentage']);
            $wdm_course_users[ $cnt ]['total_steps']         = checkIsSet($course_progress['total_steps']);
            $wdm_course_users[ $cnt ]['completed_steps']     = checkIsSet($course_progress['completed_steps']);
            $wdm_course_users[ $cnt ]['course_completed_on'] = checkCourseCompletedOn($course_progress['course_completed_on']);
            $cnt++;
        }

        foreach ($wdm_course_users as $user_val) {
            $return_str .= '<tr>
                <td>' . $user_val['user_name'] . '</td>
                <td>' . $user_val['user_email'] . '</td>
                <td>' . $user_val['completed_per'] . '</td>
                <td>' . $user_val['total_steps'] . '</td>
                <td>' . $user_val['completed_steps'] . '</td>
                <td>' . $user_val['course_completed_on'] . '</td>
                <td><a href="javascript:wdm_show_email_form(\'' . $user_val['user_email'] . '\');" title="E-Mail to ' . $user_val['user_email'] . '">E-Mail</a></td>
            </tr>';
        }
        echo $return_str;
    } else {
        return;
    }
    if (isset($_POST['users'])) {
        // if ajax call
        die();
    }

}
function wdmGetUsersInGetUserHtml($post_users, $users)
{
    if (isset($post_users)) {
        return $post_users;
    }
    return $users;
}

// To send email to all users
add_action('current_screen', 'send_instructor_emails');

//added function to form instructor email
function send_instructor_emails()
{

    if (empty($_POST['submit_instructor_email']) || empty($_POST['course_id']) || empty($_POST['learndash_instructor_message']) || empty($_POST['learndash_instructor_subject'])) {
        return;
    }

    $course_id = $_POST['course_id'];
    // To get email ids of users
    $course_progress_data = wdm_course_progress_data($course_id);

    if (empty($course_progress_data)) {
        return;
    }
    //To get email ids we will get course progress data
    $users    = $course_progress_data;

    // Check if the "from" input field is filled out
    $message = stripslashes($_POST['learndash_instructor_message']);
    $subject = stripslashes($_POST['learndash_instructor_subject']);

    // message lines should not exceed 70 characters (PHP rule), so wrap it
    $message = wordwrap($message, 70);
    // send mail
    foreach ($users as $user) {
        wp_mail($user['email'], $subject, $message);
    }
    // To redirect to the page after sending email
    $server_req_uri = $_SERVER['REQUEST_URI'];
    $url            = parse_url($server_req_uri, PHP_URL_QUERY);
    parse_str($url, $url_params);
    $url_params_string = '?page=' . $url_params['page'] . '&course_id=' . $course_id;
    $url               = explode('?', $server_req_uri);
    wp_redirect($url[0] . $url_params_string);
    exit;
}

add_action('admin_footer', 'my_admin_footer_function');

function my_admin_footer_function()
{

    echo '<div id="blanket" style="display:none;"></div>';
}

// Handle Ajax request for sending mails to individual member who attempted quiz
add_action('wp_ajax_wdm_send_mail_to_individual_user', 'wdm_send_mail_to_individual_user');
add_action('wp_ajax_nopriv_wdm_send_mail_to_individual_user', 'wdm_send_mail_to_individual_userr');

if (! function_exists('wdm_send_mail_to_individual_user')) {
    function wdm_send_mail_to_individual_user()
    {

        $email = '';
        if (isset($_POST['email'])) {
            $email = $_POST['email'];
        }

        if ($email) {
            if (isset($_POST['subject'])) {
                $subject = $_POST['subject'];
            }

            if (isset($_POST['body'])) {
                $body = $_POST['body'];
            }

            if (wp_mail($email, $subject, $body)) {
                echo '1';
            } //On successful message sent
            else {
                echo '0';
            }
        }

        die;
    }
}

/** to search item in multidimentional array
 */
function wdm_in_array($needle, $haystack, $strict = false)
{

    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && wdm_in_array($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

/**
 * to remove template field from edit question and edit quiz pages.
 */
function wdm_remove_template_field()
{

    if (wdm_is_instructor()) {
        echo '<style>
		input[name=templateName], select[name=templateSaveList], #wpProQuiz_saveTemplate {
			display:none !important;
		}
		select[name=templateLoadId], input[name=templateLoad] {
			display:none !important;
		}
		</style>';
        echo '<script>
		jQuery( document ).ready( function() {
			jQuery( "#wpProQuiz_saveTemplate" ).closest( "div" ).remove();
			jQuery("select[name=templateLoadId]").closest( "div" ).remove();
		});
		</script>';

    }
}

/**
 * Custom Author meta box to display on a edit post page.
 */
function wdm_post_author_meta_box($post)
{

    global $user_ID;
    ?>
	<label class="screen-reader-text" for="post_author_override"><?php _e('Author');?></label>
	<?php
    $wdm_args = array(
        'name'             => 'post_author_override',
        'selected'         => empty($post->ID) ? $user_ID : $post->post_author,
        'include_selected' => true,
    );
    $args = apply_filters('wdm_author_args', $wdm_args);
    wdm_wp_dropdown_users($args);
}

/**
 * To create HTML dropdown element of the users for given argument.
 */
function wdm_wp_dropdown_users($args = '')
{

    $defaults = array(
        'show_option_all'         => '',
        'show_option_none'        => '',
        'hide_if_only_one_author' => '',
        'orderby'                 => 'display_name',
        'order'                   => 'ASC',
        'include'                 => '',
        'exclude'                 => '',
        'multi'                   => 0,
        'show'                    => 'display_name',
        'echo'                    => 1,
        'selected'                => 0,
        'name'                    => 'user',
        'class'                   => '',
        'id'                      => '',
        'include_selected'        => false,
        'option_none_value'       => -1,
    );

    $defaults['selected'] = defaultSelectedOfWpDropdownUsers(get_query_var('author'));

    $rvar                 = wp_parse_args($args, $defaults);
    $show              = $rvar['show'];
    $show_option_all   = $rvar['show_option_all'];
    $show_option_none  = $rvar['show_option_none'];
    $option_none_value = $rvar['option_none_value'];

    $query_args           = wp_array_slice_assoc($rvar, array( 'blog_id', 'include', 'exclude', 'orderby', 'order' ));
    $query_args['fields'] = array( 'ID', 'user_login', $show );

    $users = array_merge(get_users(array( 'role' => 'administrator' )), get_users(array( 'role' => ROLE_ID )), get_users(array( 'role' => 'author' )));

    if (! empty($users) && (count($users) > 1)) {
        $name = esc_attr($rvar['name']);
        if ($rvar['multi'] && ! $rvar['id']) {
            $idd = '';
        } else {
            $idd = wdmGetIddOfWpDropdownUsers($rvar['id'], $name);
        }
        $output = "<select name='{$name}'{$idd} class='" . $rvar['class'] . "'>\n";

        if ($show_option_all) {
            $output .= "\t<option value='0'>$show_option_all</option>\n";
        }

        if ($show_option_none) {
            $_selected = selected($option_none_value, $rvar['selected'], false);
            $output .= "\t<option value='" . esc_attr($option_none_value) . "'$_selected>$show_option_none</option>\n";
        }

        $found_selected = false;
        foreach ((array) $users as $user) {
            $user->ID  = (int) $user->ID;
            $_selected = selected($user->ID, $rvar['selected'], false);
            if ($_selected) {
                $found_selected = true;
            }
            $display = wdmUserShowOfDropdownUsers($user->$show, $user->user_login);
            $output .= "\t<option value='$user->ID'$_selected>" . esc_html($display) . "</option>\n";
        }

        if ($rvar['include_selected'] && ! $found_selected && ($rvar['selected'] > 0)) {
            $user      = get_userdata($rvar['selected']);
            $_selected = selected($user->ID, $rvar['selected'], false);
            
            $display   = wdmUserShowOfDropdownUsers($user->$show, $user->user_login);
            $output .= "\t<option value='$user->ID'$_selected>" . esc_html($display) . "</option>\n";
        }

        $output .= '</select>';
    }
    wdmPrintOutputVariable($rvar['echo'], $output);
    
    return $output;
}

function defaultSelectedOfWpDropdownUsers($query_var_author)
{
    if (is_author()) {
        return $query_var_author;
    }
    return 0;

}
function wdmUserShowOfDropdownUsers($user_show, $user_login)
{
    if (! empty($user_show)) {
        return $user_show;
    }
    return '(' . $user_login . ')';

}
function wdmGetIddOfWpDropdownUsers($rvar_id, $name)
{
    if ($rvar_id) {
        return " id='" . esc_attr($rvar_id) . "'";
    }
    return " id='$name'";
}
function wdmPrintOutputVariable($rvar_echo, $output)
{
    if ($rvar_echo) {
        echo $output;
    }

}
/**
 * To load posts of current user (instructor) only in the backend
 */
function wdm_load_my_courses($options)
{

    if (is_admin()) {
        $wdm_user_id = get_current_user_id();

        if (wdm_is_instructor($wdm_user_id)) {
            $options['author__in'] = $wdm_user_id;
        }
    }
    return $options;
}
add_filter('learndash_select_a_course', 'wdm_load_my_courses');



/**
* @since 2.1
* Get LearnDash content's parent course.
*
*/
function wdmir_get_ld_parent($post_id)
{

    $post = get_post($post_id);

    if (empty($post)) {
        return;
    }
    
    $parent_course_id = 0;

    $post_type = $post->post_type;

    switch ($post_type) {
        case 'sfwd-certificates':
            // Get all quizzes
            $quizzes = get_posts(
                array(
                                'post_type' => 'sfwd-quiz',
                                'posts_per_page'   => -1,
                            )
            );

            foreach ($quizzes as $quiz) {
                $sfwd_quiz = get_post_meta($quiz->ID, '_sfwd-quiz', true);

                if (isset($sfwd_quiz['sfwd-quiz_certificate']) && $sfwd_quiz['sfwd-quiz_certificate'] == $post_id) {
                    if (isset($sfwd_quiz['sfwd-quiz_certificate'])) {
                        $parent_course_id = $sfwd_quiz['sfwd-quiz_course'];
                    } else {
                        $parent_course_id = get_post_meta($quiz->ID, 'course_id');
                    }

                    break;
                }
            }

            break;

        case 'sfwd-lessons':
        case 'sfwd-quiz':
        case 'sfwd-topic':
            $parent_course_id = get_post_meta($post_id, 'course_id', true);
            break;

        case 'sfwd-courses':
            $parent_course_id = $post_id;
            break;

        default:
            $parent_course_id = apply_filters('wdmir_parent_post_id', $post_id);
            break;

    }

    return $parent_course_id;

}

/**
* @since 2.1
* Description: To check if post is pending approval.
* @param $post_id int post ID of a post
* @return array/false string/boolean array of data if post has pending approval.
*/
function wdmir_am_i_pending_post($post_id)
{

    if (empty($post_id)) {
        return false;
    }

    $parent_course_id = wdmir_get_ld_parent($post_id);

    if (empty($parent_course_id)) {
        return false;
    }

    $approval_data = wdmir_get_approval_meta($parent_course_id);

    if (isset($approval_data[ $post_id ]) &&  'pending' == $approval_data[ $post_id ]['status']) {
        return $approval_data[ $post_id ];
    }

    return false;

}

/**
* @since 2.1
* Description: To get approval meta of a course
* @param $course_id int post ID of a course
* @return array/false string/boolean array of data.
*/
function wdmir_get_approval_meta($course_id)
{

    $approval_data = get_post_meta($course_id, '_wdmir_approval', true);

    if (empty($approval_data)) {
        $approval_data = array();
    }
    return $approval_data;

}

/**
* @since 2.1
* Description: To set approval meta of a course
* @param $course_id int post ID of a course
* @param $approval_data array approbval meta data of a course
*/
function wdmir_set_approval_meta($course_id, $approval_data)
{

    update_post_meta($course_id, '_wdmir_approval', $approval_data);

}

/**
* @since 2.1
* Description: To recheck and update course approval data.
* @param $course_id int post ID of a course
* @return $approval_data array updated new approval data.
*/
function wdmir_update_approval_data($course_id)
{

    $approval_data = wdmir_get_approval_meta($course_id);

    if (! empty($approval_data)) {
        foreach ($approval_data as $content_id => $content_meta) {
            $content_meta = $content_meta;
            $parent_course_id = wdmir_get_ld_parent($content_id);

            if ($parent_course_id != $course_id) {
                unset($approval_data[ $content_id ]);
            }
        }

        wdmir_set_approval_meta($course_id, $approval_data);

    }
    return $approval_data;

}


/**
* @since 2.1
* Description: To check if parent post's content has pending approval.
* @param $course_id int post ID of a course
* @return true/false boolean true if course has pending approval.
*/
function wdmir_is_parent_course_pending($course_id)
{

    $approval_data = wdmir_get_approval_meta($course_id);

    if (empty($approval_data)) {
        return false;
    }
    
    foreach ($approval_data as $content_meta) {
        // If pending content found.
        if ('pending' == $content_meta['status']) {
            return true;
        }
    }

}


/**
* @since 2.1
* Description: To send an email using wp_mail() function
* @return boolean value of wp_mail function.
*/

function wdmir_wp_mail($touser, $subject, $message, $headers, $attachments)
{

    if (! empty($touser)) {
        return wp_mail($touser, $subject, $message, $headers, $attachments);
    }
    return false;
}

/**
* @since 2.1
* Description: To set mail content type to HTML
* @return string content format for mails.
*/
function wdmir_html_mail()
{
    return 'text/html';
}

/**
* @since 2.1
* Description: To replace shortcodes in the template for the post.
* @param $post_id int post ID of a post
* @param $template string template to replace words
* @return $template string template by replacing words
*/
function wdmir_post_shortcodes($post_id, $template, $is_course_content = false)
{

    if (empty($template) || empty($post_id)) {
        return $template;
    }
    $post = get_post($post_id);

    if (empty($post)) {
        return $template;
    }

    $post_author_id = $post->post_author;

    $author_login_name = get_the_author_meta('user_login', $post_author_id);

    if ($is_course_content) {
        $find = array(
            '[course_content_title]',
            '[course_content_edit]',
            '[content_update_datetime]',
            '[approved_datetime]',
            '[content_permalink]',
        );

        $replace = array(
            $post->post_title, // [course_content_title]
            admin_url('post.php?post=' . $post_id . '&action=edit'), // [course_content_edit]
            $post->post_modified, // [content_update_datetime]
            $post->post_modified, // [approved_datetime]
            get_permalink($post_id), // [content_permalink]
        );

        $replace = apply_filters('wdmir_content_template_filter', $replace, $find);

    } else {
        $find = array(
            '[post_id]',
            '[course_id]',
            '[product_id]',
            '[post_title]',
            '[course_title]',
            '[product_title]',
            '[post_author]',
            '[course_permalink]',
            '[product_permalink]',
            '[course_update_datetime]',
            '[product_update_datetime]',
            '[ins_profile_link]',
        );

        $replace = array(
            $post_id, // [post_id]
            $post_id, // [course_id]
            $post_id, // [product_id]
            $post->post_title, // [post_title]
            $post->post_title, // [course_title]
            $post->post_title, // [product_title]
            $author_login_name, // [post_author]
            get_permalink($post_id), // [post_permalink]
            get_permalink($post_id), // [product_permalink]
            $post->post_modified, // [course_update_datetime]
            $post->post_modified, // [product_update_datetime]
            //get_edit_user_link( $post_author_id ), // [ins_profile_link]
            admin_url('user-edit.php?user_id='.$post_author_id), // [ins_profile_link]
        );

        $replace = apply_filters('wdmir_course_template_filter', $replace, $find);

    }

    $template = str_replace($find, $replace, $template);

    $template = wdmir_user_shortcodes($post_author_id, $template);

    //echo $template;

    return $template;
}


/**
* @since 2.1
* Description: To replace shortcodes in the template for the User.
* @param $user_id int user ID.
* @param $template string template to replace words
* @return $template string template by replacing words
*/
function wdmir_user_shortcodes($user_id, $template)
{

    if (empty($template) || empty($user_id)) {
        return $template;
    }

    $userdata = get_userdata($user_id);

    $find = array(
        '[ins_first_name]',
        '[ins_last_name]',
        '[ins_login]',
        '[ins_profile_link]',
    );

    $replace = array(
        $userdata->first_name, // [ins_first_name]
        $userdata->last_name, // [ins_last_name]
        $userdata->user_login, // [ins_login]
        //get_edit_user_link( $user_id ), // [ins_profile_link]
        admin_url('user-edit.php?user_id='.$user_id),  // [ins_profile_link]
    );

    $replace = apply_filters('wdmir_user_template_filter', $replace, $find);

    $template = str_replace($find, $replace, $template);

    return $template;

}
