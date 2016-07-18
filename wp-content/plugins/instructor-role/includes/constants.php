<?php

if (! defined('ROLE_NAME')) {
    define('ROLE_NAME', 'Instructor');
}
if (! defined('ROLE_ID')) {
    define('ROLE_ID', 'wdm_instructor');
}
if (! defined('WDM_INS_CAPS')) {
    $wdm_ins_caps = array(
        'wpProQuiz_show'               => true, // true allows this capability
        'wpProQuiz_add_quiz'           => true,
        'wpProQuiz_edit_quiz'          => true, // Use false to explicitly deny
        'wpProQuiz_delete_quiz'        => true,
        'wpProQuiz_show_statistics'    => true,
        'read_course'                  => true,
        'publish_courses'              => true,
        'edit_courses'                 => true,
        'delete_courses'               => true,
        'edit_course'                  => true,
        'delete_course'                => true,
        'edit_published_courses'       => true,
        'delete_published_courses'     => true,
        'edit_assignment'              => true,
        'edit_assignments'             => true,
        'publish_assignments'          => true,
        'read_assignment'              => true,
        'delete_assignment'            => true,
        'edit_published_assignments'   => true,
        'delete_published_assignments' => true,
        //    'read_group'                     => true,
        //    'edit_groups'                     => true,
        //    'propanel_widgets'                 => true,
        'read'                         => true,
        'edit_others_assignments'      => true,
        'instructor_reports'           => true, // very important, custom
        'manage_categories'            => true,
        'edit_posts'                   => true,
        'wpProQuiz_toplist_edit'       => true, // to show leaderboard of quiz
        'upload_files'                 => true, // to upload files
    );

    $wdm_ins_wccaps = array(
        'delete_product'            => true,
        'delete_products'           => true,
        'delete_published_products' => true,
        'edit_product'              => true,
        'edit_products'             => true,
        'edit_published_products'   => true,
        'publish_products'          => true,
        'read_product'              => true,
        'assign_product_terms'      => true,

    );

    $wdm_ins_caps = array_merge($wdm_ins_caps, $wdm_ins_wccaps);

    define('WDM_INS_CAPS', serialize($wdm_ins_caps));
}

// Review course constant
if (! defined('WDMIR_REVIEW_COURSE')) {
    $wdmir_admin_settings = get_option('_wdmir_admin_settings', array());
    // If Review Course setting is enabled.
    if (isset($wdmir_admin_settings['review_course']) &&  '1' == $wdmir_admin_settings['review_course']) {
        define('WDMIR_REVIEW_COURSE', true);
    } else {
        define('WDMIR_REVIEW_COURSE', false);
    }
}

// Review product constant
if (! defined('WDMIR_REVIEW_PRODUCT')) {
    $wdmir_admin_settings = get_option('_wdmir_admin_settings', array());
    // If Review Product setting is enabled.
    if (isset($wdmir_admin_settings['review_product']) && '1' == $wdmir_admin_settings['review_product']) {
        define('WDMIR_REVIEW_PRODUCT', true);
    } else {
        define('WDMIR_REVIEW_PRODUCT', false);
    }
}
