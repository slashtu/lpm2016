<?php
/**
 *Plugin Name: Instructor Role
 *Plugin URI: https://wisdmlabs.com/
 *Description: This extension adds a user role 'Instructor' into your WordPress website and provides capabilities to create courses content and track student progress in your LearnDash LMS.
 *Version: 2.3.2
 *Author: WisdmLabs
 *Author URI: https://wisdmlabs.com/
 *Text Domain: learndash
 */

//Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}


global $wdmir_plugin_data;
$wdmir_plugin_data = array(
    'plugin_short_name' => 'Instructor Role', //Plugins short name appears on the License Menu Page
    'plugin_slug'       => 'instructor_role', //this slug is used to store the data in db. License is checked using two options viz edd_<slug>_license_key and edd_<slug>_license_status
    'plugin_version'    => '2.3.2', //Current Version of the plugin. This should be similar to Version tag mentioned in Plugin headers
    'plugin_name'       => 'Instructor Role', //Under this Name product should be created on WisdmLabs Site
    'store_url'         => 'https://wisdmlabs.com/check-update', //Url where program pings to check if update is available and license validity
    'author_name'       => 'WisdmLabs', //Author Name
);


include_once 'includes/class-wdm-add-plugin-data-in-db.php';
new wdmAddPluginDataInDbNS\WdmAddPluginDataInDB($wdmir_plugin_data);


/**
 * This code checks if new version is available
 */
if (! class_exists('WdmPluginUpdater')) {
    include 'includes/wdm-plugin-updater.php';
}

$l_key = trim(get_option('edd_' . $wdmir_plugin_data['plugin_slug'] . '_license_key'));

// setup the updater
new wdmPluginUpdaerNS\WdmPluginUpdater($wdmir_plugin_data['store_url'], __FILE__, array(
    'version'   => $wdmir_plugin_data['plugin_version'], // current version number
    'license'   => $l_key, // license key (used get_option above to retrieve from DB)
    'item_name' => $wdmir_plugin_data['plugin_name'], // name of this plugin
    'author'    => $wdmir_plugin_data['author_name'], //author of the plugin
    ));

$l_key = null;

include_once 'includes/class-wdm-get-plugin-data.php';
$get_data_from_db = wdmGetPluginDataNS\WdmGetPluginData::getDataFromDb($wdmir_plugin_data);

global $wdm_ar_post_types;

// array of all custom post types of LD posts.
$wdm_ar_post_types = array(
    'sfwd-assignment',
    'sfwd-certificates',
    'sfwd-courses',
    'sfwd-lessons',
    'sfwd-quiz',
    'sfwd-topic',
    'product',
);

/**
 * Added in v1.3
 * Added filter for post types
*/
function wdmir_set_post_types()
{
    global $wdm_ar_post_types;
    $wdm_ar_post_types = apply_filters('wdmir_set_post_types', $wdm_ar_post_types);
}
add_action('init', 'wdmir_set_post_types');

require_once 'includes/constants.php';
require_once 'includes/functions.php';

require_once 'assignment-handler.php';
require_once 'lessons-handler.php';
require_once 'courses-handler.php';
require_once 'certificates-handler.php';
require_once 'quiz-handler.php';
require_once 'reports.php';
require_once 'commission.php';
require_once 'instructor-email-settings.php'; // Instructor Email Settings page
require_once 'instructor-settings.php'; // Instructor Settings page
require_once 'admin-approval-publish.php'; //

include_once 'widgets/wdm-instructor-widget.php'; // widgets of instructor
require_once 'instructor-wc/instructor-wc.php'; // handling WooCommerce part of instructor

add_role(ROLE_ID, __(ROLE_NAME), unserialize(WDM_INS_CAPS));


/**
 *  sets current user id as  author id in the main query, excluding assignment page. Because author of assignment is any user not an instructor.
 *
 */
add_filter('pre_get_posts', 'wdm_set_author');

function wdm_set_author($query)
{
    if ($query->is_admin) {
        $wdm_user_id = get_current_user_id();

        if (wdm_is_instructor($wdm_user_id)) {
            $wdmir_exclude_posts = array( 'sfwd-assignment' );

            $wdmir_exclude_posts = apply_filters('wdmir_exclude_post_types', $wdmir_exclude_posts);

            $dont_restrict_user = false;

            if (is_array($query->query['post_type'])) {
                foreach ($query->query['post_type'] as $post_type) {
                    if (! post_type_exists($post_type)) {
                        $dont_restrict_user = true;
                        break;
                    }
                    if (in_array($post_type, $wdmir_exclude_posts)) {
                        $dont_restrict_user = true;

                    }
                }
            } elseif (in_array($query->query['post_type'], $wdmir_exclude_posts)) {
                $dont_restrict_user = true;
            }

            if ($dont_restrict_user) {
                // do nothing
            } else {
                $query->query['author__in'] = $wdm_user_id;
                $query->query_vars['author__in'] = $wdm_user_id;

            }
        }
    }
    return $query;
}


/**
 * to load all scripts and styles
 */
add_action('admin_enqueue_scripts', 'wdm_load_scripts');


/**
 * Function to enqueue scripts.
 */
function wdm_load_scripts()
{

    wp_enqueue_script('wdm_highcharts', plugin_dir_url(__FILE__) . 'js/highchart.js', array( 'jquery' ), '0.0.1', true);

    //    Data table for users who attempted course
    wp_enqueue_script('wdm_dt_footable', plugin_dir_url(__FILE__) . 'js/footable.js', array( 'jquery' ), '0.0.1', false);

    wp_enqueue_script('wdm_dt_filter', plugin_dir_url(__FILE__) . 'js/footable.filter.js', array( 'jquery' ), '0.0.1', false);
    wp_enqueue_script('wdm_dt_sort', plugin_dir_url(__FILE__) . 'js/footable.sort.js', array( 'jquery' ), '0.0.1', false);

    // autosave dependency because of Tinymce editor was making leave page alert when publishing new post.
    wp_dequeue_script('autosave');
    wp_enqueue_script('wdm_reports', plugin_dir_url(__FILE__) . 'js/reports.js', array( 'jquery' /* , 'autosave' */ ), '0.0.1', true);
    //    Custom css
    wp_enqueue_style('wdm_css', plugin_dir_url(__FILE__) . 'css/style.css');
    //    For data table
    wp_enqueue_style('wdm_dt_css_footable', plugin_dir_url(__FILE__) . 'css/footable.core.css');

    wp_enqueue_style('wdm_dt_css_foo_stand', plugin_dir_url(__FILE__) . 'css/footable.standalone.css');

    //    For popup email form
    wp_enqueue_style('wdm_pop_email_css', plugin_dir_url(__FILE__) . 'css/wdm_popup_ins_mail.css' /*, array('editor-style.css')*/);
}


/**
 * to remove update, maintenance, license notices from all users except admin
 */
add_action('admin_head', 'hide_update_notice_to_all_but_admin_users', 1);


/**
 * Function to hide update notification from users those who can't update core.
 */
function hide_update_notice_to_all_but_admin_users()
{
    if (! current_user_can('update_core')) {
        remove_action('admin_notices', 'update_nag', 3);
    }
}


/*
 * to remove "dashboard" tab from admin menu
 */
function wdm_remove_dashboard_tab()
{

    if (wdm_is_instructor()) {
        global $menu;
 
        // to remove Contact Form 7 tab from Dashboard
        $arr_dash_tabs = apply_filters('wdmir_remove_dash_tabs', array( 'contact form 7' ));

        foreach ($menu as $key => $value) {
            // To remove tabs from dashboard
            if (isset($value[3]) && in_array(strtolower($value[3]), $arr_dash_tabs)) {
                unset($menu[ $key ]);
            }
        }

        remove_menu_page('index.php'); //dashboard
    }
}

add_action('admin_menu', 'wdm_remove_dashboard_tab', 99);


/**
 * to add restrictions on various pages to the instructor. As "edit_posts" is assigned to instructor, so to restrict creation of other posts other than LD, this function is used.
 * It validates using current screen base name and $_POST data.
 */
function wdm_this_screen()
{

    $currentScreen = get_current_screen();

    global $post, $wdm_ar_post_types;

    $is_ld = false;

    $arr_ld_post_types = array();
    $arr_ld_post_types = $wdm_ar_post_types;

    array_push($arr_ld_post_types, 'sfwd-assignment'); // access for assignments.

    if ((! empty($post) || ! empty($_POST)) &&
        (in_array($post->post_type, $arr_ld_post_types) || in_array($_POST['post_type'], $arr_ld_post_types))) {
            $is_ld = true;
    }

    if (wdm_is_instructor()) {

        if ($currentScreen->base == 'dashboard') {
            header('Location: ' . site_url() . '/wp-admin/edit.php?post_type=sfwd-courses');
        }

        reduceCyclomaticOfWdmThisScreen($currentScreen->base, $_GET['page'], $_GET['post_type'], $is_ld, $arr_ld_post_types);
    }
}

/**
 * Function to check a variable is set or not.
 */
function checkIfSet($page)
{
    if (isset($page)) {
        return $page;
    }
    return '';
}

/**
 * This function is a part of the function wdm_this_screen.
 */
function reduceCyclomaticOfWdmThisScreen($current_scr_base, $get_page, $get_post_type, $is_ld, $arr_ld_post_types)
{
    $param_page = checkIfSet($get_page);
    if ($current_scr_base == 'edit-tags' || ($current_scr_base == 'post' && ! isset($get_post_type) &&
            ! $is_ld) || $current_scr_base == 'tools' || $current_scr_base == 'edit-comments' ||
             ($current_scr_base == 'edit' && ! isset($get_post_type) && ! $is_ld) || $current_scr_base == 'upload' || $current_scr_base == 'media' ||
             ($current_scr_base == 'edit' && isset($get_post_type) && ! in_array(trim($get_post_type), $arr_ld_post_types)) ||
             ($current_scr_base == 'post' && isset($get_post_type) && ! in_array(trim($get_post_type), $arr_ld_post_types) ||
                ($current_scr_base == 'appearance_page_' . $param_page))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }


}

/**
 * Function to redirect current screen base.
 */
function redirectIfCurrentScrBase($current_scr_base)
{
    if ($current_scr_base == 'dashboard') {
            //echo site_url();
            header('Location: ' . site_url() . '/wp-admin/edit.php?post_type=sfwd-courses');
    }
}

add_action('current_screen', 'wdm_this_screen');


/**
 * To remove count and "mine" tab from the subsubsub menu.
 * It removes the <span> element from the string.
 * It unsets the "mine" key from the $view array.
 */
function wdm_remove_counts($views)
{
    if (wdm_is_instructor()) {
        if (! empty($views)) {
            foreach ($views as $key => $value) {
                if ('mine' == $key) {
                    unset($views[ $key ]);
                    continue;
                }

                $start_pos = strpos($value, '<span');
                $end_pos   = strpos($value, '</a>');

                $views[ $key ] = substr_replace($value, '', $start_pos, ($end_pos - $start_pos));
            }
        }
    }
    return $views;
}

foreach ($wdm_ar_post_types as $value) {
    add_filter('views_edit-' . $value, 'wdm_remove_counts');
}

/**
 * To show warning message that, Instructor role plugin will work if LD plugin is activated. And deactivates self.
 */
function wdm_instructor_ld_dependency_check()
{

    // if multisite
    if (is_multisite()) {
        if (! function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $is_plugin_active = false;

        if (is_plugin_active_for_network('sfwd-lms/sfwd_lms.php')) {
            // in the network
            $is_plugin_active = true;
        } elseif (in_array('sfwd-lms/sfwd_lms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            // in the subsite
            $is_plugin_active = true;
        }

        if (! $is_plugin_active) {
            echo "<div class='error'><p><b>LearnDash LMS</b> plugin is not active. In order to make <b>'Instructor Role'</b> plugin work, you need to install and activate <b>LearnDash LMS</b> first.</p></div>";

            deactivate_plugins(plugin_basename(__FILE__));
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
    } elseif (! in_array('sfwd-lms/sfwd_lms.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        // in single site
        echo "<div class='error'><p><b>LearnDash LMS</b> plugin is not active. In order to make <b>'Instructor Role'</b> plugin work, you need to install and activate <b>LearnDash LMS</b> first.</p></div>";

        deactivate_plugins(plugin_basename(__FILE__));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

// to notify user to activate LearnDash LMS, if not activated
add_action('admin_notices', 'wdm_instructor_ld_dependency_check');


/**
 * To show warning message that, Instructor role plugin will work if LD plugin is activated. And deactivates self in network multisite.
 */
function wdm_instructor_ld_dependency_check_network()
{

    if (! function_exists('is_plugin_active_for_network')) {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
    }

    // Makes sure the plugin is defined before trying to use it
    if (! is_plugin_active_for_network('sfwd-lms/sfwd_lms.php')) {
        echo "<div class='error'><p><b>LearnDash LMS</b> plugin is not active. In order to make <b>'Instructor Role'</b> plugin work, you need to install and activate <b>LearnDash LMS</b> first.</p></div>";

        deactivate_plugins(plugin_basename(__FILE__));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}
add_action('network_admin_notices', 'wdm_instructor_ld_dependency_check_network');


/*
 * to remove posts,comments,etc. tabs from admin menu
 */
function wdm_remove_admin_menus()
{

    // Check that the built-in WordPress function remove_menu_page() exists in the current installation
    if (function_exists('remove_menu_page')) {
        if (wdm_is_instructor()) {
            remove_menu_page('edit.php'); //Posts
            remove_menu_page('edit-comments.php'); //Comment
            remove_menu_page('tools.php'); //Tools
            remove_menu_page('upload.php'); //Media
            remove_menu_page('themes.php'); //themes
        }
    }
}

add_action('admin_menu', 'wdm_remove_admin_menus');


/**
 * to remove dashboard widgets from dashboard page in case header redirect fails
 */
function wdm_remove_dashboard_widgets()
{

    if (wdm_is_instructor()) {
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // right now
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // recent comments
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal'); // incoming links
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal'); // plugins

        remove_meta_box('dashboard_quick_press', 'dashboard', 'normal'); // quick draft
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'normal'); // recent drafts
        remove_meta_box('dashboard_primary', 'dashboard', 'normal'); // wordpress blog
        remove_meta_box('dashboard_secondary', 'dashboard', 'normal'); // other wordpress news
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    }
}

add_action('admin_init', 'wdm_remove_dashboard_widgets');


/**
 * To show message on dashboard page that no data to display to user  in case header redirect fails.
 */
function show_admin_messages()
{

    $currentScreen = get_current_screen();

    if (wdm_is_instructor()) {
        if ($currentScreen->base == 'dashboard') {
            echo '<div class="error"><p>No data to display!</p></div>';
        }
    }
}

add_action('admin_notices', 'show_admin_messages');


/**
 * To remove default copy question ajax action, and load custom ajax action.
 */
function wdm_remove_copy_question_action()
{
    if (wdm_is_instructor()) {
        remove_all_actions('wp_ajax_wp_pro_quiz_load_question');
        add_action('wp_ajax_wp_pro_quiz_load_question', 'wdm_quiz_load_question_for_copy');
    }
}

add_action('admin_init', 'wdm_remove_copy_question_action');


/**
 * This function takes "quiz_id" as an argument and returns all quizzes with questions of same user only. It takes quiz_id argument to exclude current quiz questions.
 *  Here most of the LD code used, with some changes in it.
 */
function wdm_quiz_load_question_for_copy()
{
    $quizId = checkIfSet($_GET['quiz_id']);

    $wdm_current_user = get_current_user_id();

    if (! current_user_can('wpProQuiz_edit_quiz')) {
        echo json_encode(array());
        exit;
    }

    $questionMapper = new WpProQuiz_Model_QuestionMapper();
    $data           = array();

    global $wpdb;

    $res = array();

    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wp_pro_quiz_master ORDER BY id ASC", ARRAY_A);

    foreach ($results as $row) {
        if ($row['result_grade_enabled']) {
            $row['result_text'] = unserialize($row['result_text']);
        }
        $res[] = new WpProQuiz_Model_Quiz($row);
    }

    $quiz = $res;

    foreach ($quiz as $qz) {
        if ($qz->getId() == $quizId) {
            continue;
        }

        $quiz_post_id = learndash_get_quiz_id_by_pro_quiz_id($qz->getId());

        //$wdm_current_user
        $post_author_id = get_post_field('post_author', $quiz_post_id);

        if ($wdm_current_user != $post_author_id) {
            continue;
        }

        $question      = $questionMapper->fetchAll($qz->getId());
        $questionArray = array();

        foreach ($question as $qu) {
            $questionArray[] = array(
                'name' => $qu->getTitle(),
                'id'   => $qu->getId(),
            );
        }

        $data[] = array(
            'name'     => $qz->getName(),
            'id'       => $qz->getId(),
            'question' => $questionArray,
        );
    }

    echo json_encode($data);

    exit;
}


/**
 * To add 'author' role to Instructor. 'author' role is required because, if author role is not there then user does not list in author field in edit course, etc.
 */
function wdm_my_profile_update($user_id)
{

    //Added in v1.4, because WordPress stores meta key of capabilities using prefix of mysql table.
    global $wpdb;
    $wdm_user_roles = get_user_meta($user_id, $wpdb->prefix.'capabilities', false);

    if (wdm_in_array(ROLE_ID, $wdm_user_roles, false)) {
        // search in multidimentional array
        $usr = new WP_User($user_id);
        $usr->add_role('author');
    }
}

add_action('admin_head', 'wdm_remove_template_field');

add_action('admin_menu', 'wdm_reset_author_metabox');

/**
 *  To remove default author meta box and add custom author meta box, to list users having role "authors" or "Instructor" in LD custom post types.
 */
function wdm_reset_author_metabox()
{

    // Determine if user is a network (super) admin. Will also check if user is admin if network mode is disabled.
    if (is_super_admin()) {
        global $wdm_ar_post_types;

        foreach ($wdm_ar_post_types as $value) {
            remove_meta_box('authordiv', $value, 'normal');
            add_meta_box('authordiv', __('Author'), 'wdm_post_author_meta_box', $value);
        }
    }
}


if ('available' == $get_data_from_db) {
    function wdm_admin_notice_license()
    {

        ?>
        <div class="updated">
            <p><?php _e('This is the beta version of the <strong>Instructor Role</strong> plugin. The license will expire within 15 days of downloading the plugin.', 'learndash');?></p>
        </div>
<?php
    }
} // if ( $get_data_from_db == 'available' )
elseif ('expired' == $get_data_from_db) {
    function wdm_admin_notice_expire()
    {

        ?>
        <div class="updated">
            <p><?php _e('Your <strong>Instructor Role</strong> plugin license has expired. Please renew your license for continued support and updates.', 'learndash');?></p>
        </div>
<?php
    }
    add_action('admin_notices', 'wdm_admin_notice_expire');
}

/**
 * Remove/Add capabilities from Instructors.
 * Checks if plugin's license is deactivated or not, if deactivated then removes all caps of 'wdm_instructor' role
 * excluding 'read' cap, when next time, activate license then adds all caps again.
 */
function wdm_set_capabilities()
{

    global $wdmir_plugin_data;
    include_once 'includes/class-wdm-get-plugin-data.php';
    $get_data_from_db = wdmGetPluginDataNS\WdmGetPluginData::getDataFromDb($wdmir_plugin_data);

    //Get License Status

    // Get the role object.
    $wdm_instructor = get_role(ROLE_ID);

    // A list of capabilities to remove from Instructors.
    $wdm_ins_caps = unserialize(WDM_INS_CAPS);

    // if license is deactivated

    if ('available' != $get_data_from_db) {

        foreach ($wdm_ins_caps as $key_cap => $val_cap) {
            if ('read' != $key_cap) {
                // Remove the capability.
                $wdm_instructor->remove_cap($key_cap);
            }
        }
    } else {
        // if 'read_course' cap is not present then add all caps
        if (! isset($wdm_instructor->capabilities['read_course'])) {
            foreach ($wdm_ins_caps as $key_cap => $val_cap) {
                if ('read' != $key_cap) {
                    // add the capability.
                    $wdm_instructor->add_cap($key_cap);

                }
            }
        } // if (!isset($wdm_instructor->capabilities['read_course']))

    }
}
add_action('init', 'wdm_set_capabilities');


//Added in v1.4 to add 'instructor_reports' cap to administrator. This cap is used to show course reports tab.
function wdmir_plugin_activate()
{
    $wdm_admin = get_role('administrator');
    $wdm_admin->add_cap('instructor_reports');
}
register_activation_hook(__FILE__, 'wdmir_plugin_activate');

//Added in v1.4. To remove 'instructor_reports' cap to administrator. This cap is used to show course reports tab.
function wdmir_plugin_deactivate()
{
    $wdm_admin = get_role('administrator');
    $wdm_admin->remove_cap('instructor_reports');
}
register_deactivation_hook(__FILE__, 'wdmir_plugin_deactivate');



/**
 * @description: To edit "dashboard" tabs in admin menu
 *
*/
function wdmirAddDashboardTabs()
{
    if (wdm_is_instructor()) {
        global $menu;
        // Default allowed tabs
        $allowed_tabs = array('products',
            'courses',
            'lessons',
            'quizzes',
            'assignments',
            'topics',
            'certificates',
            'profile',
            'woocommerce');
        // to remove Contact Form 7 tab from Dashboard
        $allowed_tabs = apply_filters('wdmir_add_dash_tabs', $allowed_tabs);
        foreach ($menu as $key => $value) {
        // If not from an array, remove from the menu.
            if (isset($value[0]) && in_array(strtolower($value[0]), $allowed_tabs)) {
                // Do nothing
            } else {
                unset($menu[ $key ]);
            }
        }
        remove_menu_page('index.php'); //dashboard
    }
}
add_action('admin_menu', 'wdmirAddDashboardTabs', 100);
