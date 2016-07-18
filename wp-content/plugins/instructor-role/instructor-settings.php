<?php

/**
*   @since version 2.1
*   Display of HTML content on Instructor Settings page.
*   This function is called from file "commission.php" in function instuctor_page_callback()
*/
function wdmir_instructor_settings()
{
    ?><div class="wrap">
	<h2><?php echo __('Instructor Settings', 'learndash') ?></h2>
	<form method="post" action="">
	<?php
    wp_nonce_field('instructor_setting_nonce_action', 'instructor_setting_nonce', true, true);
    do_action('wdmir_settings_before_table');
    ?>
		<table class="form-table wdmir-form-table">
			<tbody>
				<?php
                do_action('wdmir_settings_before');
                $wdmir_admin_settings = get_option('_wdmir_admin_settings', array());

                // Product Review
                $review_product = '';
                if (isset($wdmir_admin_settings['review_product']) && '1' == $wdmir_admin_settings['review_product']) {
                    $review_product = 'checked';
                }

                // Course Review
                $review_course = '';
                if (isset($wdmir_admin_settings['review_course']) && '1' == $wdmir_admin_settings['review_course']) {
                    $review_course = 'checked';
                }

                //instructor mail
                $wl8_en_inst_mail = '';
                if (isset($wdmir_admin_settings['instructor_mail']) && $wdmir_admin_settings['instructor_mail'] == '1') {
                    $wl8_en_inst_mail = 'checked';
                }

    ?>
				<tr>
					<th scope="row"><label for="wdmir_review_product"><?php echo __('Review Product', 'learndash');
    ?></label></th>
					<td><input name="wdmir_review_product" type="checkbox" id="wdmir_review_product" <?php echo $review_product;
    ?>>
					<?php echo __('Enable admin approval for WooCommerce product updates', 'learndash');
    ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wdmir_review_course"><?php echo __('Review Course', 'learndash');
    ?></label></th>
					<td><input name="wdmir_review_course" type="checkbox" id="wdmir_review_course"<?php echo $review_course;
    ?>>
					<?php echo __('Enable admin approval for LearnDash course updates', 'learndash');
    ?>
					</td>
				</tr>


                <tr>
                    <th scope="row"><label for="wdm_enable_instructor_mail"><?php echo __('Instructor Email');
    ?></label></th>
                    <td><input name="wdm_enable_instructor_mail" type="checkbox" id="wdm_enable_instructor_mail"<?php echo $wl8_en_inst_mail;
    ?>>
                    <?php echo __('Enable email notification for instructor on quiz completion', 'learndash');
    ?>
                    </td>
                </tr>
                

				<tr>
					<th scope="row"><label for="wdmir_review_course_content"><?php echo __('Review Course Content', 'learndash');
    ?></label></th>
					<td>
					<?php
                        $editor_settings = array('textarea_rows' => 100, 'editor_height' => 200);
                        wp_editor(($wdmir_admin_settings['review_course_content'] ? $wdmir_admin_settings['review_course_content'] : ''), 'wdmir_review_course_content', $editor_settings);
                    ?>
					</td>
				</tr>

				<?php
                do_action('wdmir_settings_after');
    ?>
			</tbody>
		</table>
		<?php
            do_action('wdmir_settings_after_table');
    ?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save Changes', 'learndash');
    ?>"></p>
	</form>
</div>
<?php

} // function wdmir_instructor_settings()

/*
*   @since version 2.1
*   Saving HTML form content of Instructor Settings page.
*
*/
add_action('init', 'wdmir_settings_save');
function wdmir_settings_save()
{
    if (isset($_POST['instructor_setting_nonce']) && wp_verify_nonce($_POST['instructor_setting_nonce'], 'instructor_setting_nonce_action') && is_admin()) {

        $wdmir_admin_settings = array();

        do_action('wdmir_settings_save_before');

        // Product Review
        $wdmir_admin_settings['review_product'] = '';
        if (isset($_POST['wdmir_review_product'])) {
            $wdmir_admin_settings['review_product'] = 1;
        }

        // Course Review
        $wdmir_admin_settings['review_course'] = '';
        if (isset($_POST['wdmir_review_course'])) {
            $wdmir_admin_settings['review_course'] = 1;
        }

        //Enable instructor mail
        $wdmir_admin_settings['instructor_mail'] = '';
        if (isset($_POST['wdm_enable_instructor_mail'])) {
            $wdmir_admin_settings['instructor_mail'] = 1;
        }

        // Course Review
        $wdmir_admin_settings['review_course_content'] = '';
        if (isset($_POST['wdmir_review_course_content'])) {
            $wdmir_admin_settings['review_course_content'] = $_POST['wdmir_review_course_content'];
        }

        // Saving instructor settings option
        update_option('_wdmir_admin_settings', $wdmir_admin_settings);

        do_action('wdmir_settings_save_after');

        wp_redirect($_POST['_wp_http_referer']);
    }
}
