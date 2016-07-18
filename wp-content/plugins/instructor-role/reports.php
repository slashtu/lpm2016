<?php

/**
 * to add "course reports" sub menu.
 */
add_action('admin_menu', 'add_instructor_submenu', 1001);


/**
 * Function to add instructor submenu.
 */
function add_instructor_submenu()
{
    global $wdmir_plugin_data;
    include_once 'includes/class-wdm-get-plugin-data.php';
    $get_data_from_db = wdmGetPluginDataNS\WdmGetPluginData::getDataFromDb($wdmir_plugin_data);

    if ('available' == $get_data_from_db) {
        add_submenu_page('learndash-lms', __('Course Reports', 'learndash'), __('Course Reports', 'learndash'), 'instructor_reports', 'instructor-lms-reports', 'learndash_lms_instructor_reports_page');
    }
}


/**
 * course reports sub menu call back function.
 */
function learndash_lms_instructor_reports_page()
{
    ?>
	<div  id="learndash-instructor-reports"  class="wrap">
		<h2><?php _e('Course Reports', 'learndash');
    ?></h2>
		<br>
		<div class="sfwd_settings_left">
			<div class=" " id="div-instructor-courses">
				<div class="inside">
					<?php
                    $instructor_courses = get_posts('post_type=sfwd-courses&posts_per_page=-1');

                    $current_post = 0;
                    $found = 0;
                    if (isset($_REQUEST['course_id'])) {
                        $current_post = $_REQUEST['course_id'];
                        $current_course_title = get_the_title($_REQUEST['course_id']);
                        $course_cnt = 1;
                        $found = 1; // added in v1.3
                    } else {
                        $course_cnt = 0;
                    }

                    if (!empty($instructor_courses)) {
                        $wdm_report_str = '';
                        $wdm_report_str .= '<label class="wdm-filter-title">';
                        $wdm_report_str .= __('Select Course', 'learndash').': ';
                        $wdm_report_str .= '</label>';
                        $wdm_report_str .= '<select name = "sel-instructor-courses" id = "instructor-courses" onchange="wdm_change_report( this )">';

                        foreach ($instructor_courses as $value) {
                            if (count(wdm_get_course_users($value->ID)) > 0) {
                                if ($current_post == $value->ID) {
                                    $selected = 'selected';
                                }
                                $wdm_report_str .= '<option value="'.$value->ID.'"'.$selected.'>'.$value->post_title.'</option>';
                                $selected = '';

                                if (0 == $course_cnt) {
                                    $current_post = $value->ID;
                                    $current_course_title = $value->post_title;
                                    $found = 1;
                                }
                                ++$course_cnt;
                            }
                        }

                        $wdm_report_str .= '</select>';
                        if (0 === $found) {
                            echo '<br/>';
                            echo __('No reports to display', 'learndash');
                        } else {
                            echo $wdm_report_str;
                        }
                        echo '<div id="wdm_main_report_div" >';
                        $report_html = wdm_report_html($current_post);

                        $arr_report_html = json_decode($report_html, true);

                        if (isset($arr_report_html['html'])) {
                            echo $arr_report_html['html'];
                        }

                        echo '</div>';
                    } else {
                        echo __('No reports to display', 'learndash');
                    }
    ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
