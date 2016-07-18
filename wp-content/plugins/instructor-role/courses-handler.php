<?php

add_filter('learndash_current_admin_tabs_on_page', 'wdm_remove_tabs_course', 10, 4);

/**
 * To remove "Lesson options" tab from admin lessons' page to instructor
 */
function wdm_remove_tabs_course($current_page_id_data, $admin_tabs, $admin_tabs_on_page, $current_page_id)
{

    if (wdm_is_instructor()) {
        $admin_tabs = $admin_tabs;
        $admin_tabs_on_page = $admin_tabs_on_page;
        $course_pages = array( 'edit-sfwd-courses', 'sfwd-courses','admin_page_learndash-lms-course_shortcodes' ); // lesson page IDs

        if (in_array($current_page_id, $course_pages)) { // if admin lessons page
            foreach ($current_page_id_data as $key => $value) {
                if (24 == $value) { // Categories tab
                    unset($current_page_id_data[ $key ]);
                } elseif (26 == $value) { // Tags tab
                    unset($current_page_id_data[ $key ]);
                } elseif (28 == $value) { // Course Shortcodes
                    unset($current_page_id_data[ $key ]);
                }
            }
        }
    }
    return $current_page_id_data;
}
