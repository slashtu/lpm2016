<?php

add_filter('learndash_current_admin_tabs_on_page', 'wdm_remove_tabs_certi', 10, 4);

/**
 * To remove "Lesson options" tab from admin lessons' page to instructor
 */
function wdm_remove_tabs_certi($current_page_id_data, $admin_tabs, $admin_tabs_on_page, $current_page_id)
{

    if (wdm_is_instructor()) {
        $course_pages = array( 'edit-sfwd-certificates', 'sfwd-certificates','admin_page_learndash-lms-certificate_shortcodes' ); // certificate page IDs
        $admin_tabs = $admin_tabs;
        $admin_tabs_on_page = $admin_tabs_on_page;
        if (in_array($current_page_id, $course_pages)) { // if admin lessons page
            foreach ($current_page_id_data as $key => $value) {
                if (130 == $value) { // Categories tab
                    unset($current_page_id_data[ $key ]);
                    break;
                }
            }
        }
    }
    return $current_page_id_data;
}
