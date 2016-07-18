<?php

/**
*   @since: version 2.1
*   Display of HTML content on Instructor Email Settings page.
*   This function is called from file "commission.php" in function instuctor_page_callback()
*
*/
function wdmir_instructor_email_settings()
{

    // Shortcuts used in naming variables and elements
    // cra_ = course review admin
    // cri_ = course review instructor
    // pra_ = prodoct review admin
    // pri_ = product review instructor

    $email_settings = get_option('_wdmir_email_settings');

    ?>
	<div class="wrap wdmir-email-wrap">
		<h2><?php echo __('E-mail Settings', 'learndash') ?></h2>
		<form method="post" action="">
<?php
wp_nonce_field('ins_email_setting_nonce_action', 'ins_email_setting_nonce', true, true);
do_action('wdmir_email_settings_before');
?>
			<!-- Emails to admin about course review - starts -->

			<div class="wdmir-section">
				<div class="wdmir-email-heading">
					<span class="heading"><?php echo __('Course Update Notification To Admin', 'learndash') ?></span>
					<a class="wdmir-shortcodes" href="javascript:void(0);">Shortcodes</a>
					<div class="wdmir-shortcode-callback">
						<a href="javascript:void(0);" class="wdmir-shortcode-close">X</a><br />
						<table class="wdmir-shortcode-tbl" cellpadding="4" cellspacing="2">
							<tr>
								<td><code>[ins_profile_link]</code></td>
								<td><?php echo __('Instructor Profile Link', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_first_name]</code></td>
								<td><?php echo __('Instructor First Name', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_last_name]</code></td>
								<td><?php echo __('Instructor Last Name', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_login]</code></td>
								<td><?php echo __('Instructor Login ID', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_id]</code></td>
								<td><?php echo __('Course ID', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_title]</code></td>
								<td><?php echo __('Course Title', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_content_title]</code></td>
								<td><?php echo __('Title of a edited course content', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_content_edit]</code></td>
								<td><?php echo __('Dashboard link of a edited course content', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_update_datetime]</code></td>
								<td><?php echo __('Updated date and time of a course', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[content_update_datetime]</code></td>
								<td><?php echo __('Updated date and time of a content', 'learndash');
    ?></td>
							</tr>
						</table>
					</div>
				</div>
				<table class="form-table wdmir-form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="cra_emails"><?php echo __('Admin E-Mail ID', 'learndash');
    ?>
							</label></th>
							<td>
								<input class="wdmir-email-box" name="cra_emails" type="text" id="cra_emails" 
								value="<?php echo $email_settings['cra_emails'];
    ?>">
								<p class="description">
								<?php
                                echo __('Comma separated E-mail IDs to send course review notification.', 'learndash');
    ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="cra_subject"><?php echo __('Subject', 'learndash');
    ?></label>
							</th>
							<td>
								<input class="wdmir-full-textbox" name="cra_subject" type="text" id="cra_subject" 
								                        value="<?php echo $email_settings['cra_subject'];
    ?>">
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="cra_mail_content">
							<?php echo __('Review Course E-Mail Content', 'learndash');
    ?></label></th>
							<td>
<?php
$editor_settings = array('textarea_rows' => 100, 'editor_height' => 200);
    wp_editor(
        ($email_settings['cra_mail_content'] ? wp_unslash($email_settings['cra_mail_content']) : ''),
        'cra_mail_content',
        $editor_settings
    );
?>
							</td>
						</tr>
					</tbody>
				</table>

				<?php
                    //$template = wdmir_post_shortcodes( 9, $email_settings[ 'cra_mail_content' ] );
                    //echo $template = wdmir_post_shortcodes( 18, $template, true );
                ?>

			</div>

			<!-- Emails to admin about course review - ends -->

			<!-- Emails to instructor about course review - starts -->
			<div class="wdmir-section">
				<div class="wdmir-email-heading">
					<span class="heading"><?php echo __('Course Update Notification To Instructor', 'learndash') ?>
					</span>
					<a class="wdmir-shortcodes" href="javascript:void(0);">Shortcodes</a>
					<div class="wdmir-shortcode-callback">
						<a href="javascript:void(0);" class="wdmir-shortcode-close">X</a><br />
						<table class="wdmir-shortcode-tbl" cellpadding="4" cellspacing="2">
							<tr>
								<td><code>[ins_first_name]</code></td>
								<td><?php echo __('Instructor First Name', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_last_name]</code></td>
								<td><?php echo __('Instructor Last Name', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_login]</code></td>
								<td><?php echo __('Instructor Login ID', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_id]</code></td>
								<td><?php echo __('Course ID', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_title]</code></td>
								<td><?php echo __('Course Title', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_content_title]</code></td>
								<td><?php echo __('Title of a edited course content', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_permalink]</code></td>
								<td><?php echo __('Permalink of a course', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[content_permalink]</code></td>
								<td><?php echo __('Permalink of a content', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[course_content_edit]</code></td>
								<td><?php echo __('Dashboard link of a edited course content', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[approved_datetime]</code></td>
								<td><?php echo __('Approved date and time of a content', 'learndash');
    ?></td>
							</tr>
						</table>
					</div>
				</div>
				<table class="form-table wdmir-form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="cri_subject"><?php echo __('Subject', 'learndash');
    ?></label>
							</th>
							<td>
								<input class="wdmir-full-textbox" name="cri_subject" type="text" id="cri_subject" 
								                    value="<?php echo $email_settings['cri_subject'];
    ?>">
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="cri_mail_content">
							<?php echo __('Review Course E-Mail Content', 'learndash');
    ?></label></th>
							<td>
<?php
$editor_settings = array('textarea_rows' => 100, 'editor_height' => 200);
wp_editor(
    ($email_settings['cri_mail_content'] ? wp_unslash($email_settings['cri_mail_content']) : ''),
    'cri_mail_content',
    $editor_settings
);
?>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
                    //$template = wdmir_post_shortcodes( 9, $email_settings[ 'cri_mail_content' ] );
                    //echo $template = wdmir_post_shortcodes( 18, $template, true );
                ?>
			</div>
			<!-- Emails to instructor about course review - ends -->

			
			<!-- Emails to admin about product update - starts -->

			<div class="wdmir-section">
				<div class="wdmir-email-heading">
					<span class="heading"><?php echo __('Product Update Notification To Admin', 'learndash') ?></span>
					<a class="wdmir-shortcodes" href="javascript:void(0);">Shortcodes</a>
					<div class="wdmir-shortcode-callback">
						<a href="javascript:void(0);" class="wdmir-shortcode-close">X</a><br />
						<table class="wdmir-shortcode-tbl" cellpadding="4" cellspacing="2">
							<tr>
								<td><code>[ins_profile_link]</code></td>
								<td><?php echo __('Instructor Profile Link', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_first_name]</code></td>
								<td><?php echo __('Instructor First Name', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_last_name]</code></td>
								<td><?php echo __('Instructor Last Name', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_login]</code></td>
								<td><?php echo __('Instructor Login ID', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[product_id]</code></td>
								<td><?php echo __('Product ID', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[product_title]</code></td>
								<td><?php echo __('Product Title', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[product_permalink]</code></td>
								<td><?php echo __('Permalink of a product', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[product_update_datetime]</code></td>
								<td><?php echo __('Updated date and time of a product', 'learndash');
    ?></td>
							</tr>
						</table>
					</div>
				</div>
				<table class="form-table wdmir-form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="pra_emails"><?php echo __('Admin E-Mail ID', 'learndash');
    ?>
							   </label></th>
							<td>
								<input class="wdmir-email-box" name="pra_emails" type="text" id="pra_emails" 
								                        value="<?php echo $email_settings['pra_emails'];
    ?>">
								<p class="description">
								<?php echo __('Comma separated E-mail IDs to send product update notification.');
    ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="pra_subject"><?php echo __('Subject', 'learndash');
    ?></label>
							</th>
							<td>
								<input class="wdmir-full-textbox" name="pra_subject" type="text" id="pra_subject" 
								                    value="<?php echo $email_settings['pra_subject'];
    ?>">
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="pra_mail_content">
							<?php echo __('Product Update E-Mail Content', 'learndash');
    ?></label></th>
							<td>
<?php
$editor_settings = array('textarea_rows' => 100, 'editor_height' => 200);
wp_editor(
    ($email_settings['pra_mail_content'] ? wp_unslash($email_settings['pra_mail_content']) : ''),
    'pra_mail_content',
    $editor_settings
);
    ?>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
                    //echo $template = wdmir_post_shortcodes( 27, $email_settings[ 'pra_mail_content' ] );
                    //echo $template = wdmir_post_shortcodes( 18, $template, true );
                ?>
			</div>



			<div class="wdmir-section">
				<div class="wdmir-email-heading">
					<span class="heading">
					    <?php echo __('Product Update Notification To Instructor', 'learndash') ?>
					</span>
					<a class="wdmir-shortcodes" href="javascript:void(0);">Shortcodes</a>
					<div class="wdmir-shortcode-callback">
						<a href="javascript:void(0);" class="wdmir-shortcode-close">X</a><br />
						<table class="wdmir-shortcode-tbl" cellpadding="4" cellspacing="2">
							<tr>
								<td><code>[ins_profile_link]</code></td>
								<td><?php echo __('Instructor Profile Link', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_first_name]</code></td>
								<td><?php echo __('Instructor First Name', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_last_name]</code></td>
								<td><?php echo __('Instructor Last Name', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[ins_login]</code></td>
								<td><?php echo __('Instructor Login ID', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[product_id]</code></td>
								<td><?php echo __('Product ID', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[product_title]</code></td>
								<td><?php echo __('Product Title', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[product_permalink]</code></td>
								<td><?php echo __('Permalink of a product', 'learndash');
    ?></td>
							</tr>
							<tr>
								<td><code>[product_update_datetime]</code></td>
								<td><?php echo __('Updated date and time of a product', 'learndash');
    ?></td>
							</tr>
						</table>
					</div>
				</div>
				<table class="form-table wdmir-form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="pri_subject">
							        <?php echo __('Subject', 'learndash');
    ?></label>
							</th>
							<td>
								<input class="wdmir-full-textbox" name="pri_subject" type="text" id="pri_subject" 
								            value="<?php echo $email_settings['pri_subject'];
    ?>">
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="pri_mail_content">
							            <?php echo __('Product Update E-Mail Content', 'learndash');
    ?></label></th>
							<td>
<?php
$editor_settings = array('textarea_rows' => 100, 'editor_height' => 200);
wp_editor(
    conditionalOperatorWdmirInstructorEmailSettings($email_settings['pri_mail_content']),
    'pri_mail_content',
    $editor_settings
);
?>
							</td>
						</tr>
					</tbody>
				</table>
				<?php
                    //echo $template = wdmir_post_shortcodes( 27, $email_settings[ 'pri_mail_content' ] );
                    //echo $template = wdmir_post_shortcodes( 18, $template, true );
                ?>
			</div>

			<!-- Emails to admin about product update - ends -->
			<?php
            do_action('wdmir_email_settings_after');
    ?>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" 
			                            value="<?php echo __('Save Changes', 'learndash');
    ?>"></p>

		</form>
	</div>
<?php

} // function wdmir_instructor_email_settings()

function conditionalOperatorWdmirInstructorEmailSettings($email_content)
{
    if (!empty($email_content)) {
        return wp_unslash($email_content);
    }

    return '';
}

/**
*   @since version 2.1
*   Saving HTML form content of Instructor Email Settings page.
*
*/
add_action('init', 'wdmir_email_settings_save');
function wdmir_email_settings_save()
{
    if (isset($_POST['ins_email_setting_nonce']) &&
                            wp_verify_nonce($_POST['ins_email_setting_nonce'], 'ins_email_setting_nonce_action') &&
                            is_admin()) {
        $email_settings = array();
        do_action('wdmir_email_settings_save_before');

        // Course Review To Admin - starts
        $email_settings['cra_emails'] = '';
        $email_settings['cra_emails'] = checkIsSets($_POST['cra_emails']);
        
        $email_settings['cra_subject'] = checkIsSets($_POST['cra_subject']);
        
        $email_settings['cra_mail_content'] = checkIsSets($_POST['cra_mail_content']);
        
        // Course Review To Instructor - starts
        $email_settings['cri_subject'] = checkIsSets($_POST['cri_subject']);
        
        $email_settings['cri_mail_content'] = '';
        if (isset($_POST['cri_mail_content'])) {
            $email_settings['cri_mail_content'] = $_POST['cri_mail_content'];
        }

        // Course Review To Instructor - ends

        // Product Review To Admin - starts
        $email_settings['pra_emails'] = '';
        if (isset($_POST['pra_emails'])) {
            $email_settings['pra_emails'] = $_POST['pra_emails'];
        }

        $email_settings['pra_subject'] = '';
        if (isset($_POST['pra_subject'])) {
            $email_settings['pra_subject'] = $_POST['pra_subject'];
        }

        $email_settings['pra_mail_content'] = '';
        if (isset($_POST['pra_mail_content'])) {
            $email_settings['pra_mail_content'] = $_POST['pra_mail_content'];
        }
        // Product Review To Admin - ends

        // Product Review To Instructor - starts
        $email_settings['pri_subject'] = '';
        if (isset($_POST['pri_subject'])) {
            $email_settings['pri_subject'] = $_POST['pri_subject'];
        }

        $email_settings['pri_mail_content'] = '';
        if (isset($_POST['pri_mail_content'])) {
            $email_settings['pri_mail_content'] = $_POST['pri_mail_content'];
        }
        // Product Review To Instructor - ends

        // Saving email settings option
        update_option('_wdmir_email_settings', $email_settings);

        do_action('wdmir_email_settings_save_after');

        wp_redirect($_POST['_wp_http_referer']);
    }
}

function checkIsSets($value)
{
    if (isset($value)) {
        return $value;
    }
    return '';
}

//wl8 changes...

//this action call whenever instructor save email template data from instructor setting page.
add_action('admin_init', 'save_instructor_mail_template_data');

function save_instructor_mail_template_data()
{
    if (!is_user_logged_in()) {
        return false;
    }

    $current_user_id = get_current_user_id();
    if (isset($_POST['instructor_email_update'])) {
        $email_template_data = array();
        if (isset($_POST['instructor_email_sub'])) {
            $email_template_data['mail_sub'] = $_POST['instructor_email_sub'];
        }
        if (isset($_POST['instructor_email_message'])) {
            $email_template_data['mail_content'] = $_POST['instructor_email_message'];
        }

        update_user_meta($current_user_id, 'instructor_email_template', $email_template_data);
    }
}

add_action('learndash_quiz_completed', 'send_email_to_instructor', 10, 1);

function send_email_to_instructor($data)
{
    if (!isset($data)) {
        return false;
    }
    if (wdm_is_instructor($data['quiz']->post_author)) {
        $wdmid_admin_setting = get_option('_wdmir_admin_settings', array());
        //send mail to instructor if admin enable instruction mail option.
        if (!empty($wdmid_admin_setting) && $wdmid_admin_setting['instructor_mail'] == 1) {
            $current_user = get_current_user_id();
            $current_user_details = get_userdata($current_user);
            $wl8_qz_ins_details = get_userdata($data['quiz']->post_author);
            $email_template_data = get_user_meta($data['quiz']->post_author, 'instructor_email_template', true);
            $mail_sub = '';
            $mail_content = '';
            if (!empty($email_template_data)) {
                $mail_sub = $email_template_data['mail_sub'];
                $mail_content = $email_template_data['mail_content'];
            }

            if (empty($mail_sub)) {
                $mail_sub = 'User attempt quiz';
            } else {
                $mail_sub = str_replace('$userid', $current_user, $mail_sub);
                $mail_sub = str_replace('$username', $current_user_details->user_login, $mail_sub);
                $mail_sub = str_replace('$useremail', $current_user_details->user_email, $mail_sub);
                $mail_sub = str_replace('$quizname', $data['quiz']->post_title, $mail_sub);
                $mail_sub = str_replace('$result', $data['percentage'], $mail_sub);
                $mail_sub = str_replace('$points', $data['points'], $mail_sub);
            }
            //wl8 changes ends here.

            if (empty($mail_content)) {
                $mail_content = 'User has attempt following quiz -<br/>';
                $mail_content .= 'UserName: '.$current_user_details->user_login.'<br/>';
                $mail_content .= 'Email: '.$current_user_details->user_email.'<br/>';
                $mail_content .= 'Quiz title: '.$data['quiz']->post_title.'<br/>';
                if ($data['pass']) {
                    $mail_sub .= 'Result: Passed ';
                } else {
                    $mail_sub .= 'Result: Failed';
                }
            } else {
                $mail_content = str_replace('$userid', $current_user, $mail_content);
                $mail_content = str_replace('$username', $current_user_details->user_login, $mail_content);
                $mail_content = str_replace('$useremail', $current_user_details->user_email, $mail_content);
                $mail_content = str_replace('$quizname', $data['quiz']->post_title, $mail_content);
                $mail_content = str_replace('$result', $data['percentage'], $mail_content);
                $mail_content = str_replace('$points', $data['points'], $mail_content);
            }

            add_filter('wp_mail_content_type', 'set_html_content_type', 1);
            wp_mail($wl8_qz_ins_details->user_email, $mail_sub, $mail_content);
        }
    }
}

function set_html_content_type($content_type)
{
    unset($content_type);

    return 'text/html';
}

function wdmir_individual_instructor_email_setting()
{
    $current_user_id = get_current_user_id();
    $prev_stored_data = get_user_meta($current_user_id, 'instructor_email_template', true);
    ?>
    <div class="wl8qcn-email-form">
    <form method="post" action="">
        <div class="wl8qcn-email-heading">
        	<h2>Instructor Email</h2>
        </div>
        
        <div class="wl8qcn-email-sub">
        <label for="email">Email Subject:</label>
            <input id="instructor_email_sub" rows="5" class="instructor_email_sub" name="instructor_email_sub" value="<?php echo !empty($prev_stored_data) ? $prev_stored_data['mail_sub'] : '' ?>">
        </div>

        <div class="wl8qcn-email-content">
        <label for="text">Email Message:</label>
        <?php
        $content = '';
        if (!empty($prev_stored_data)) {
            $content = $prev_stored_data['mail_content'];
        }
        $editor_id = 'instructor_email_message';
        wp_editor($content, $editor_id);
        ?>
        </div>
                
        <div id="instructor_email_template_variable">
        <h4>ALLOWED VARIABLES</h4>
        <table>
        <?php
        $allowed_vars = wl8GetAllowedVars();
        foreach ($allowed_vars as $desc => $var) {
            echo "<tr><td><code>$var</code></td><td>:</td><td>$desc</td></tr>";
        }
        ?>
	    </table>
	    </div>
	    <br/>
	    <input id="instructor_email_update" name="instructor_email_update" class="button button-primary" type="submit" value="save"/>
    </form>
    </div>
    <?php

}

/**
 * Function returns allowed variable list.
 */
function wl8GetAllowedVars()
{
    //allowed variables...
    $vars = array(
        'Userid' => '$userid',
        'Username' => '$username',
        'User\'s email' => '$useremail',
        'Quiz name' => '$quizname',
        'Result in percent' => '$result',
        'Reached points' => '$points',
    );

    return $vars;
}
