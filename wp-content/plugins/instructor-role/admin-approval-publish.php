<?php

/**
* @since: 2.1
*/
/*
Logic to send difference of old and new product data.
Use of "pre_post_update" hook to save all data of a product and then compare it with new values in
"save_post" hook. Compare values, create a mail body according to difference, and delete saved data.
*/


/*																						*
*																						*
* --------------------------- For Products - starts ------------------------------------*
*																						*
*/

/**
* @since 2.1
* To validate user and to change default product status accordingly.
*/
function wdmir_product_approval_validate($post_id, $post)
{

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ('auto-draft' == $post->post_status)
           || ('product' != $post->post_type) || (! wdm_is_instructor(get_current_user_id()))) {
        return;
    }

    // If Review Product setting is enabled. Then remove "publish product" capability of instructors.
    if (WDMIR_REVIEW_PRODUCT) {
        $status = 'draft';

        $product = array(
            'ID' => $post->ID,
            'post_status' => apply_filters('wdmtv_product_approval_status', $status),
        );

        //If calling wp_update_post, unhook this function so it doesn't loop infinitely
        remove_action('save_post', 'wdmir_product_approval_validate', 999, 2);

        wp_update_post($product);

        add_action('save_post', 'wdmir_product_approval_validate', 999, 2);

        // ------- to send an email - starts --------- //

        $send_mail = true;
        $send_mail = apply_filters('wdmir_pra_send_mail', $send_mail);

 //If you don't want to send en email on every update,you can apply your own logic and send an email whenever necessary.
        if ($send_mail) {
            $email_settings = get_option('_wdmir_email_settings');

            $pra_emails = isset($email_settings['pra_emails']) ? explode(',', $email_settings['pra_emails']) : '';

            $pra_emails = apply_filters('wdmir_pra_emails', $pra_emails);

            // If any E-Mail ID is not set, then send to admin email
            if (empty($pra_emails)) {
                $pra_emails = get_option('admin_email');
            } else {
                if (is_array($pra_emails)) {
                    $pra_emails = array_filter($pra_emails);
                }

                $subject = conditionalOperator($email_settings['pra_subject']);
                $body = conditionalOperator($email_settings['pra_mail_content']);

                // replacing shortcodes
                $subject    = wdmir_post_shortcodes($post_id, $subject, false);
                $subject    = wdmir_user_shortcodes(get_current_user_id(), $subject);

                $body       = wdmir_post_shortcodes($post_id, $body, false);
                $body       = wdmir_user_shortcodes(get_current_user_id(), $body);

                add_filter('wp_mail_content_type', 'wdmir_html_mail');

                $subject    = apply_filters('wdmir_pra_subject', $subject);
                $body       = apply_filters('wdmir_pra_body', $body);

                wdmir_wp_mail($pra_emails, $subject, $body);

                remove_filter('wp_mail_content_type', 'wdmir_html_mail');

            }
        } // if( $send_mail )

        // ------- to send an email - ends --------- //

    }

}
add_action('save_post', 'wdmir_product_approval_validate', 999, 2);


/**
 * Function to remove slashes from email settings.
 */
function conditionalOperator($email_settings)
{
    if (isset($email_settings)) {
        return wp_unslash($email_settings);
    }
       return 'Product Updated by an Instructor';
}


/**
* @since 2.1
* To Hide and show "Publish" button to instructor to products.
*/
function wdmir_hide_publish_product()
{

    global $post;

    if (! isset($post->post_type) || 'product' != $post->post_type) {
        return;
    }

    if (! wdm_is_instructor(get_current_user_id())) {
        return;
    }

    // If Review Product setting is enabled.
    if (WDMIR_REVIEW_PRODUCT) {
        if ($post->post_status == 'publish') {
            echo '<script>';
            echo 'jQuery("#publishing-action #publish").attr("value","'.__('Save Draft').'");';
            echo '</script>';

        } else {
            // To hide "Publish" button of publish meta box.
            echo '<style>';
            echo '#publishing-action {
				display: none;
			}';
            echo '</style>';
        }

        // To remove "Publish" option from dropdown of quick edit.
    }
}
add_action('admin_footer', 'wdmir_hide_publish_product', 999);

/**
* @since 2.1
* To Send an email notification after a product has been published of an instructor.
*/
function wdmir_product_published_notification($PID, $post)
{
    // check if we should send notification or not.
    if (wdmir_product_published_notification_cond($post)) {
        return;
    }

    // ------- to send an email - starts --------- //

    $send_mail = true;
    $send_mail = apply_filters('wdmir_pri_send_mail', $send_mail);

 //If you don't want to send en email on every update,you can apply your own logic and send an email whenever necessary.
    if ($send_mail) {
        $post_id = $PID;

        $email_settings = get_option('_wdmir_email_settings');

        $pri_emails = get_the_author_meta('user_email', $post->post_author);

        $pri_emails = apply_filters('wdmir_pri_emails', $pri_emails);

        if (! empty($pri_emails)) {
            if (is_array($pri_emails)) {
                $pri_emails = array_filter($pri_emails);
            }

            $subject = isset($email_settings['pri_subject']) ?
            wp_unslash($email_settings['pri_subject']) : 'Product is Published by an Admin';
            $body = isset($email_settings['pri_mail_content']) ?
            wp_unslash($email_settings['pri_mail_content']) : 'Product is Published by an Admin';

            // replacing shortcodes
            $subject    = wdmir_post_shortcodes($post_id, $subject, false);
            $subject    = wdmir_post_shortcodes($parent_course_id, $subject, false);
            $subject    = wdmir_user_shortcodes($post->post_author, $subject);

            $body       = wdmir_post_shortcodes($post_id, $body, false);
            $body       = wdmir_post_shortcodes($parent_course_id, $body, false);
            $body       = wdmir_user_shortcodes($post->post_author, $body);

            add_filter('wp_mail_content_type', 'wdmir_html_mail');

            $subject    = apply_filters('wdmir_pri_subject', $subject);
            $body       = apply_filters('wdmir_pri_body', $body);

            wdmir_wp_mail($pri_emails, $subject, $body);

            remove_filter('wp_mail_content_type', 'wdmir_html_mail');

        }
    } // if( $send_mail )

    // ------- to send an email - ends --------- //

}
add_action('publish_product', 'wdmir_product_published_notification', 10, 2);

/**
* @since 2.3.1
* To check if we should send notification or not after a product has been published of an instructor.
*/
function wdmir_product_published_notification_cond($post)
{
    if (empty($post)) {
        return true;
    }
    // If current post is NOT product OR product author is not an instructor OR current user is an Instructor
    if (($post->post_type != 'product') || (! wdm_is_instructor($post->post_author)) || (wdm_is_instructor(get_current_user_id()))) {
        return true;
    }
    return false;
}
/*																						*
*																						*
* --------------------------- For Products - Ends ------------------------------------	*
*																						*
*/

/*																						*
*																						*
* --------------------------- For Course content - starts ------------------------------*
*																						*
*/

/**
* @since 2.1
* To show custom content if course content is in pending status.
* Filters:
* wdmir_show_course_page: true if you want to show main course page
* wdmir_show_course_content: true if you want to show all course content
*
*/
function wdmir_approval_course_content($content, $post)
{

    if (WDMIR_REVIEW_COURSE && ! current_user_can('manage_options')) {

        // ---------------  If you want to show main course page. --------------- //
        $show_course_page = false;
        $show_course_page = apply_filters('wdmir_show_course_page', $show_course_page);

        if ($show_course_page && $post->post_type == 'sfwd-courses') {
            return $content;
        }

        // ---------------  If you want to show main course page. --------------- //

        // ---------------  If you want to show all course content --------------- //

        $show_course_content = false;
        $show_course_content = apply_filters('wdmir_show_course_content', $show_course_content);

        if ($show_course_content) {
            return $content;
        }

        // ---------------  If you want to show all course content --------------- //

        $prent_course_id = wdmir_get_ld_parent($post->ID);

        if (empty($prent_course_id)) {
            return $content;
        }

        if (wdmir_is_parent_course_pending($prent_course_id)) {
            $settings = get_option('_wdmir_admin_settings', true);

            if (isset($settings['review_course_content']) && ! empty($settings['review_course_content'])) {
                $content = $settings['review_course_content'];

            } else {
                $content = __('This course is under review!!');

            }
        }
    }

    return $content;

}
add_filter('learndash_content', 'wdmir_approval_course_content', 100, 2);


/**
* @since 2.1
* To save that course content is edited by an instructor.
*/
function wdmir_ld_approval_validate($post_id, $post)
{

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ('auto-draft' == $post->post_status)) {
        return;
    }

    // To avoid auto-draft posts.

    $ld_post_types = array(
        'sfwd-certificates',
        'sfwd-courses',
        'sfwd-lessons',
        'sfwd-quiz',
        'sfwd-topic',
    );

    $ld_post_types = apply_filters('wdmtv_validate_ld_post_types', $ld_post_types);

    if (! in_array($post->post_type, $ld_post_types) || (! wdm_is_instructor(get_current_user_id()))) {
        return;
    }

    // If Review Course setting is enabled.
    if (WDMIR_REVIEW_COURSE) {
        $parent_course_id = wdmir_get_ld_parent($post_id);

        if (! empty($parent_course_id)) {
            $approval_data = wdmir_get_approval_meta($parent_course_id);

            if (empty($approval_data)) {
                $approval_data = array();
            }

            $approval_data[ $post_id ]['status'] = 'pending';
            $approval_data[ $post_id ]['update_time'] = current_time('mysql');

            $approval_data = apply_filters('wdmir_approval_post_meta', $approval_data);

            wdmir_set_approval_meta($parent_course_id, $approval_data);
            wdmir_update_approval_data($parent_course_id);

            // ------- to send an email - starts --------- //

            $send_mail = true;
            $send_mail = apply_filters('wdmir_cra_send_mail', $send_mail);

 //If you don't want to send en email on every update,you can apply your own logic and send an email whenever necessary.
            if ($send_mail) {
                $email_settings = get_option('_wdmir_email_settings');

                $cra_emails = wdmirCraEmails($email_settings['cra_emails']);

                $cra_emails = apply_filters('wdmir_cra_emails', $cra_emails);

                // If any E-Mail ID is not set, then send to admin email
                if (empty($cra_emails)) {
                    $cra_emails = get_option('admin_email');
                } else {
                    if (is_array($cra_emails)) {
                        $cra_emails = array_filter($cra_emails);
                    }

                    $subject = wdmir_unslash($email_settings['cra_subject'], 'Course has been Updated by an Instructor');
                    $body    = wdmir_unslash($email_settings['cra_mail_content'], 'Course has been Updated by an Instructor');
                    // replacing shortcodes
                    $subject = wdmir_post_shortcodes($post_id, $subject, true);
                    $subject = wdmir_post_shortcodes($parent_course_id, $subject, false);
                    $subject = wdmir_user_shortcodes(get_current_user_id(), $subject);

                    $body = wdmir_post_shortcodes($post_id, $body, true);
                    $body = wdmir_post_shortcodes($parent_course_id, $body, false);
                    $body = wdmir_user_shortcodes(get_current_user_id(), $body);

                    add_filter('wp_mail_content_type', 'wdmir_html_mail');

                    $subject    = apply_filters('wdmir_cra_subject', $subject);
                    $body       = apply_filters('wdmir_cra_body', $body);

                    wdmir_wp_mail($cra_emails, $subject, $body);

                    remove_filter('wp_mail_content_type', 'wdmir_html_mail');

                }
            } // if( $send_mail )

            // ------- to send an email - ends --------- //

        }
    }

}

add_action('save_post', 'wdmir_ld_approval_validate', 11, 2);


/**
 * Function to remove slashes from email subject.
 */
function wdmir_unslash($content, $default_cont = '')
{
    if (!empty($content)) {
        return wp_unslash($content);
    }
    return $default_cont;
}


/**
 * Function to returns string email settings into array format.
 */
function wdmirCraEmails($email_settings)
{
    if (isset($email_settings)) {
        return explode(',', $email_settings);

    }
    return '';
    
}


/**
* @since 2.1
* To show approval pending meta box to the admin.
* Meta box: wdmir_approval_meta_box
* Callback function: wdmir_approval_meta_box_callback
*/
function wdmir_approval_meta_box()
{
    if (WDMIR_REVIEW_COURSE && current_user_can('manage_options')) {
        add_meta_box(
            'wdmir_approval_meta_box',
            __('Instructor Pending approvals'),
            'wdmir_approval_meta_box_callback',
            'sfwd-courses',
            'side',
            'core'
        );
    }
}
add_action('admin_init', 'wdmir_approval_meta_box');


/**
* @since: 2.1
* Callback funciton of a meta box 'wdmir_approval_meta_box'.
* To show pending approval contents in the meta box.
*/
function wdmir_approval_meta_box_callback($post)
{

    $current_post_id = $post->ID;

    $approval_data = wdmir_get_approval_meta($current_post_id);

    if (empty($approval_data)) {
        echo __('No pending approvals');

    } else {
        $pending_approvals = array();

        foreach ($approval_data as $content_id => $content_meta) {
            // If approval is pending
            if ('pending' == $content_meta['status']) {
                // Check once again that parent did not change
                if (wdmir_get_ld_parent($content_id) == $current_post_id) {
                    $pending_approvals[ $content_id ] = $content_meta;

                }
            } // if( $approval_status == '1' )

        } // foreach ( $approval_data as $content_id => $approval_status )

        if (empty($pending_approvals)) {
            echo __('No pending approvals');

        } else {
            echo "<ul class='wdmir-pendig-box'>";

            foreach ($pending_approvals as $pending_id => $pending_meta) {
                //Sep 19, 2015 @ 10:48
                $updated_date = date('M d, Y @ H:i', strtotime($pending_meta['update_time']));

                echo "<li>
                        <a href='" . get_edit_post_link($pending_id) . "'>" . get_the_title($pending_id) . '</a> 
                        ( '. $updated_date .' )
                        </li>';

            }

            echo '</ul>';

        }
    }

}


/**
* @since 2.1
* To show checkbox in the publish meta box of a content, if content is having admin approval.
*/
function wdmir_approve_field_publish()
{

    if (WDMIR_REVIEW_COURSE && current_user_can('manage_options')) {
        global $post;

        $post_id = $post->ID;

        $pending_data = wdmir_am_i_pending_post($post_id);

        if (! empty($pending_data)) {
            echo '<div class="misc-pub-section misc-pub-section-last">
		        <span id="wdmir_pending">'
                . '<label><input type="checkbox" value="" name="wdmir_approve_field_publish" /> '
                . __('Approve Instructor Update') .' </label>'
                .'</span></div>';

        } // if( !empty( $pending_data ) )

    } // if( WDMIR_REVIEW_COURSE && current_user_can('manage_options') )

}
add_action('post_submitbox_misc_actions', 'wdmir_approve_field_publish');


/**
*
* @since 2.1
* To approve LD content. If checkbox is checked then approve content and remove from approval data of a course.
*
*/
function wdmir_ld_approve_content($post_id, $post)
{

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // To avoid auto-draft posts.
    if ('auto-draft' == $post->post_status) {
        return;
    }

    if (WDMIR_REVIEW_COURSE && current_user_can('manage_options') && isset($_POST['wdmir_approve_field_publish'])) {
        $parent_course_id = wdmir_get_ld_parent($post_id);

        $approval_data = wdmir_get_approval_meta($parent_course_id);

        if (isset($approval_data[ $post_id ])) {
            unset($approval_data[ $post_id ]);

            wdmir_set_approval_meta($parent_course_id, $approval_data);
            wdmir_update_approval_data($parent_course_id);

            // empty means all pending contents are approaved, so send an ack email to instructor
            if (empty($approval_data)) {
                // ------- to send an email - starts --------- //

                $send_mail = true;
                $send_mail = apply_filters('wdmir_cri_send_mail', $send_mail);

 //If you don't want to send en email on every update,you can apply your own logic and send an email whenever necessary.
                if ($send_mail) {
                    $email_settings = get_option('_wdmir_email_settings');

                    $post_author_id = get_post_field('post_author', $parent_course_id);

                    $cri_emails = get_the_author_meta('user_email', $post_author_id);

                    if (! empty($cri_emails)) {
                        $subject = isset($email_settings['cri_subject']) ?
                                wp_unslash($email_settings['cri_subject']) : 'Course has been approved by an admin';
                        $body = isset($email_settings['cri_mail_content']) ?
                                wp_unslash($email_settings['cri_mail_content']) :'Course has been approved by an admin';

                        // replacing shortcodes
                        $subject = wdmir_post_shortcodes($post_id, $subject, true);
                        $subject = wdmir_post_shortcodes($parent_course_id, $subject, false);
                        $subject = wdmir_user_shortcodes(get_current_user_id(), $subject);

                        $body = wdmir_post_shortcodes($post_id, $body, true);
                        $body = wdmir_post_shortcodes($parent_course_id, $body, false);
                        $body = wdmir_user_shortcodes(get_current_user_id(), $body);

                        add_filter('wp_mail_content_type', 'wdmir_html_mail');

                        $subject        = apply_filters('wdmir_cri_subject', $subject);
                        $body           = apply_filters('wdmir_cri_body', $body);
                        $cri_emails     = apply_filters('wdmir_cri_emails', $cri_emails);

                        wdmir_wp_mail($cri_emails, $subject, $body);

                        remove_filter('wp_mail_content_type', 'wdmir_html_mail');

                    }
                } // if( $send_mail )

                // ------- to send an email - ends --------- //

            }
        }
    }

}
add_action('save_post', 'wdmir_ld_approve_content', 11, 2);

/**
*
* @since 2.1
* To update course approval data on course update.
*
*/
function wdmir_on_course_approval_update($post_id, $post)
{

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // To avoid auto-draft posts.
    if ('auto-draft' == $post->post_status) {
        return;
    }

    if (isset($post->post_type) && $post->post_type == 'sfwd-courses') {
        wdmir_update_approval_data($post_id);

    }
    return;

}
add_action('save_post', 'wdmir_on_course_approval_update', 11, 2);


/**
*
* @since 2.1
* Description: To add a "pending" column in the course listing page in a dashboard.
*
*/
function wdmir_pending_column_head($defaults)
{

    $show_column = true;
    $show_column = apply_filters('wdmir_show_pending_column', $show_column);

    if (WDMIR_REVIEW_COURSE && $show_column) {
        $defaults['wdmir_pending'] = __('Pending');
    }
    return $defaults;
}
add_filter('manage_sfwd-courses_posts_columns', 'wdmir_pending_column_head', 10);


/**
*
* @since 2.1
* Description: To show status in a "pending" column in the course listing page in a dashboard.
*
*/
function wdmir_pending_column_content($column_name, $post_ID)
{

    $show_column = true;
    $show_column = apply_filters('wdmir_show_pending_column', $show_column);

    if (WDMIR_REVIEW_COURSE && $show_column) {
        if ('wdmir_pending' == $column_name) {
            if (wdmir_is_parent_course_pending($post_ID)) {
                echo __('Yes');
            } else {
                echo '-';
            }
        }
    }
}

add_action('manage_sfwd-courses_posts_custom_column', 'wdmir_pending_column_content', 10, 2);

/*																						*
*																						*
* --------------------------- For Course content - ends ------------------------------	*
*																						*
*/


/**
*
* @since 2.1
* Description: To show admin message after LD content or product update.
*
*/
function wdmir_instructor_approval_message($message)
{

    if (! wdm_is_instructor(get_current_user_id())) {
        return $message;
    }

    global $post;

    $post_name = '';

    if (WDMIR_REVIEW_COURSE) {
        switch ($post->post_type) {
            case 'sfwd-courses':
                $post_name = __('Course', 'learndash');
                break;
            case 'sfwd-lessons':
                $post_name = __('Lesson', 'learndash');
                break;
            case 'sfwd-topic':
                $post_name = __('Topic', 'learndash');
                break;
            case 'sfwd-quiz':
                $post_name = __('Quiz', 'learndash');
                break;
            case 'sfwd-certificates':
                $post_name = __('Certificate', 'learndash');
                break;
            case 'sfwd-assignment':
                $post_name = __('Assignment', 'learndash');
                break;
        }
    }

    if (! empty($post_name)) {
        $update_msg = __('This', 'learndash').' '.$post_name.' '
                            . __('will be reviewed and published by the admin upon approval.', 'learndash');

        $update_msg = apply_filters('wdmir_updated_message', $update_msg, $post->post_type, $message);

        $message['post'][1]  = $update_msg; // saved
        $message['post'][4]  = $update_msg; // updated
        $message['post'][6]  = $update_msg; // published
        $message['post'][7]  = $update_msg; // saved
        $message['post'][8]  = $update_msg; // submitted
        $message['post'][10] = $update_msg; // saved in draft
    }

    if (WDMIR_REVIEW_PRODUCT && $post->post_type == 'product') {
        $post_name = __('Product', 'learndash');

        $update_msg = __('This', 'learndash').' '
                      .$post_name.' '. __('will be reviewed and published by the admin upon approval.', 'learndash');

        $update_msg = apply_filters('wdmir_updated_message', $update_msg, $post->post_type, $message);

        $message['product'][1]   = $update_msg; // saved
        $message['product'][4]   = $update_msg; // updated
        $message['product'][6]   = $update_msg; // published
        $message['product'][7]   = $update_msg; // saved
        $message['product'][8]   = $update_msg; // submitted
        $message['product'][10]  = $update_msg; // saved in draft
    }
    return $message;
}
add_filter('post_updated_messages', 'wdmir_instructor_approval_message', 11, 1);
