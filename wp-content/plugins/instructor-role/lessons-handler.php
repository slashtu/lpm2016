<?php

add_filter('learndash_current_admin_tabs_on_page', 'wdm_remove_tabs_lessons', 10, 4);

/**
 * To remove "Lesson options" tab from admin lessons' page to instructor
 */
function wdm_remove_tabs_lessons($current_page_id_data, $admin_tabs, $admin_tabs_on_page, $current_page_id)
{

    if (wdm_is_instructor()) {
        $admin_tabs = $admin_tabs;
        $admin_tabs_on_page = $admin_tabs_on_page;
        $lesson_pages = array( 'sfwd-lessons', 'edit-sfwd-lessons' ); // lesson page IDs

        if (in_array($current_page_id, $lesson_pages)) { // if admin lessons page
            foreach ($current_page_id_data as $key => $value) {
                if (50 == $value) { // lesson options tab
                    unset($current_page_id_data[ $key ]);
                    break;
                }
            }
        }
    }
    return $current_page_id_data;
}
