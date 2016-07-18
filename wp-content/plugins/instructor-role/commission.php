<?php
add_action('admin_head', 'wdm_instructor_table_setup');

/*
 * Creating wdm_instructor_commission table
 */
function wdm_instructor_table_setup()
{
    global $wpdb;
    $table_name = $wpdb->prefix.'wdm_instructor_commission';
    $sql = 'CREATE TABLE '.$table_name.' (
                id INT NOT NULL AUTO_INCREMENT,
        user_id int,
        order_id int,
        product_id int,
        actual_price float,
        commission_price float,
        transaction_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
                );';
    require_once ABSPATH.'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}


add_filter('admin_menu', 'instuctor_menu', 2000);

/*
 * adding Istructor commission menu inside learndash-lms menu
 */
function instuctor_menu()
{
    global $wdmir_plugin_data;
    include_once 'includes/class-wdm-get-plugin-data.php';
    $get_data_from_db = wdmGetPluginDataNS\WdmGetPluginData::getDataFromDb($wdmir_plugin_data);
    if ('available' == $get_data_from_db) {
        add_submenu_page('learndash-lms', 'Instructor', 'Instructor', 'instructor_reports', 'instuctor', 'instuctor_page_callback');
    }
}


/*
 * Adding tabs inside intructor commission page
 *
 */
function instuctor_page_callback()
{
    $current_tab = isset($_GET[ 'tab' ]) ? $_GET[ 'tab' ] : 'instructor';
    //check whether email tab should exist for instrutor or not.
    $wdmid_admin_setting = get_option('_wdmir_admin_settings', array());
    $wl8_show_email_tab = false;
    //If admin select instructor mail option then we need to display only three tabs.

    if (array_key_exists('instructor_mail', $wdmid_admin_setting) && $wdmid_admin_setting['instructor_mail'] == 1) {
        $wl8_show_email_tab = true;
        if (!is_super_admin() && $current_tab != 'export' && $current_tab != 'email') {
            $current_tab = 'commission_report';
        } elseif (!is_super_admin() && $current_tab != 'commission_report' && $current_tab != 'email') {
            $current_tab = 'export';
        } elseif (!is_super_admin() && $current_tab != 'commission_report' && $current_tab != 'export') {
            $current_tab = 'email';
        }
    } elseif (!is_super_admin() && $current_tab != 'export') {
        $current_tab = 'commission_report';
    }
    
    wl8ShowTabs($current_tab, $wl8_show_email_tab);

    ?>

    <?php do_action('instuctor_tab_add', $current_tab);
    ?>
    </h2> 
    <?php wl8ShowCurrentTab($current_tab);
}


/**
 * Functions shows all tabs depending on conditions.
 */
function wl8ShowTabs($current_tab, $wl8_show_email_tab)
{
    ?>
    <h2 class="nav-tab-wrapper">

        <?php
        if (is_super_admin()) {
            ?>
            <a class="nav-tab <?php echo(($current_tab == 'instructor') ? 'nav-tab-active' : '') ?> " href="?page=instuctor&tab=instructor"><?php echo __('Instructor', 'learndash');
            ?></a>
        <?php

        }
    ?>

    <?php
    wl8ShowCommissionAndExportContent($current_tab);
    ?>

    
    <?php
    wl8ShowMailTab($current_tab, $wl8_show_email_tab);
    ?>

        <?php if (is_super_admin()) {
    ?>
                <a class="nav-tab <?php echo(($current_tab == 'settings') ? 'nav-tab-active' : '') ?>" href="?page=instuctor&tab=settings"><?php echo __('Settings', 'learndash');
    ?></a>
        <?php

}
}


/**
 * Function shows mail tab.
 */
function wl8ShowMailTab($current_tab, $wl8_show_email_tab)
{
    if (is_super_admin() || $wl8_show_email_tab) {
        ?>
        <a class="nav-tab <?php echo(($current_tab == 'email') ? 'nav-tab-active' : '') ?>" href="?page=instuctor&tab=email"><?php echo __('Email', 'learndash');
        ?></a>
        <?php

    }
}


/**
 * Function shows commision and export content.
 */
function wl8ShowCommissionAndExportContent($current_tab)
{
    ?>

    <a class="nav-tab <?php echo(($current_tab == 'commission_report') ? 'nav-tab-active' : '') ?>" href="?page=instuctor&tab=commission_report"><?php echo __('Commission Report', 'learndash');
    ?></a>
    <a class="nav-tab <?php echo(($current_tab == 'export') ? 'nav-tab-active' : '') ?>" href="?page=instuctor&tab=export"><?php echo __('Export', 'learndash');
    ?></a>

    <?php

}

/**
 * Function shows current tab.
 */
function wl8ShowCurrentTab($current_tab)
{
    switch ($current_tab) {
        case 'instructor':
            wdm_instructor_first_tab();
            break;
        case 'commission_report':
            wdm_instructor_second_tab();
            break;
        case 'export':
            wdm_instructor_third_tab();
            break;
        case 'email':
            if (is_super_admin()) {
                wdmir_instructor_email_settings();
            } else {
                wdmir_individual_instructor_email_setting();
            }
            break;
        case 'settings':
            wdmir_instructor_settings();
            break;

    }

    do_action('instuctor_tab_checking', $current_tab);
}


/**
 * Function returns tab name if set else returns default tab 'instructor'.
 */
function conditionalOperatorForInstructorPageCallback($tab)
{
    if (isset($tab)) {
        return $tab;
    }

    return 'instructor';
}


/**
 * Function to returns css class nav-tab-active.
 */
function conditionalOperatorInstructorPageCallback($current_tab)
{
    if ($current_tab == 'instructor' || $current_tab == 'commission_report'
        || $current_tab == 'export' || $current_tab == 'email' || $current_tab == 'settings') {
        return 'nav-tab-active';
    }

    return '';
}


/*
 * Displaying table for allocating instructor commission percentage
 */
function wdm_instructor_first_tab()
{
    wp_enqueue_script('wdm_footable_pagination', plugins_url('/js/footable.paginate.js', __FILE__), array('jquery'));
    wp_enqueue_script('wdm_commission_js', plugins_url('/js/commission.js', __FILE__), array('jquery'));
    $data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'invalid_percentage' => __('Invalid percentage', 'learndash'),
    );
    wp_localize_script('wdm_commission_js', 'wdm_commission_data', $data);
    // To show values in Commission column
    $instr_commissions = get_option('instructor_commissions', '');
    $instr_commissions = $instr_commissions;
    // To get user Ids of instructors
    $args = array('fields' => array('ID', 'display_name', 'user_email'), 'role' => 'wdm_instructor');
    $instructors = get_users($args);
    ?>
	<br/>
	<div id="reports_table_div" style="padding-right: 5px">
		<div class="CL"></div>
	<?php echo __('Search', 'learndash');
    ?>
		<input id="filter" type="text">
		<select name="change-page-size" id="change-page-size">
			<option value="5"><?php echo __('5 per page', 'learndash');
    ?></option>
			<option value="10"><?php echo __('10 per page', 'learndash');
    ?></option>
			<option value="20"><?php echo __('20 per page', 'learndash');
    ?></option>
			<option value="50"><?php echo __('50 per page', 'learndash');
    ?></option>
		</select>
		<br><br>
		<!--Table shows Name, Email, etc-->
		<table class="footable" data-filter="#filter"  id="wdm_report_tbl" data-page-size="5" >
			<thead>
				<tr>
					<th data-sort-initial="descending" data-class="expand">
						<?php echo __('Name', 'learndash');
    ?>
					</th>
					<th>
						<?php echo __('User email', 'learndash');
    ?>
					</th>
					<th>
						<?php echo __('Commission %', 'learndash');
    ?>
					</th>
					<th data-hide="phone" >
	<?php echo __('Update', 'learndash');
    ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
                if (!empty($instructors)) {
                    foreach ($instructors as $instructor) {
                        $commission_percent = get_user_meta($instructor->ID, 'wdm_commission_percentage', true);
                        if ('' == $commission_percent) {
                            $commission_percent = 0;
                        }
                        //echo '<pre>';print_R($instructor);echo '</pre>';
                        ?>
						<tr>
							<td><?php echo $instructor->display_name;
                        ?></td>
							<td><?php echo $instructor->user_email;
                        ?></td>
							<td><input name="commission_input" size="5" value="<?php echo $commission_percent;
                        ?>" min="0" max="100" type="number" id="input_<?php echo $instructor->ID;
                        ?>"></td>
							<td><a name="update_<?php echo $instructor->ID;
                        ?>" class="update_commission button button-primary" href="#"><?php echo __('Update', 'learndash');
                        ?></a><img class="wdm_ajax_loader"src="<?php echo plugins_url('/images/ajax-loader.gif', __FILE__);
                        ?>" style="display:none;"></td>
						</tr>
						<?php

                    }
                } else {
                    ?>
					<tr>
						<td colspan="4">
					<?php echo __('No instructor found', 'learndash');
                    ?>
						</td>
					</tr>
	<?php

                }
    ?>
			</tbody>
			<tfoot class="hide-if-no-paging">
				<tr>
					<td colspan="4" style="border-radius: 0 0 6px 6px;">
						<div class="pagination pagination-centered hide-if-no-paging"></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	<br/>
	<div id="update_commission_message"></div>


<?php

}

add_action('wp_ajax_wdm_update_commission', 'wdm_update_commission');

/*
 * Updating instructor commission using ajax
 */
function wdm_update_commission()
{
    $percentage = $_POST['commission'];
    $instructor_id = $_POST['instructor_id'];
    if (wdm_is_instructor($instructor_id)) {
        update_user_meta($instructor_id, 'wdm_commission_percentage', $percentage);
        echo __('Updated successfully', 'learndash');
    } else {
        echo __('Oops something went wrong', 'learndash');
    }
    die();
}

/*
 * On woocommerce order complete, adding commission percentage in custom table
 */
function wdm_add_record_to_db($order_id)
{
    $order = new WC_Order($order_id);
    global $wpdb;
   
    $items = $order->get_items();
    foreach ($items as $item) {
        //echo 'item <pre>';print_R($item);echo '</pre>';
        $product_id = $item['product_id'];
        $total = $item['line_total'];
        $product_post = get_post($product_id);
        $author_id = $product_post->post_author;
        if (wdm_is_instructor($author_id)) {
            $commission_percent = get_user_meta($author_id, 'wdm_commission_percentage', true);
            if ('' == $commission_percent) {
                $commission_percent = 0;
            }
            $commission_price = ($total * $commission_percent) / 100;
            $sql = "SELECT id FROM {$wpdb->prefix}wdm_instructor_commission WHERE user_id = $author_id AND order_id = $order_id AND product_id = $product_id";
            $id = $wpdb->get_var($sql);
            $data = array(
                'user_id' => $author_id,
                'order_id' => $order_id,
                'product_id' => $product_id,
                'actual_price' => $total,
                'commission_price' => $commission_price,
            );
            if ('' == $id) {
                $wpdb->insert($wpdb->prefix.'wdm_instructor_commission', $data);
            } else {
                $wpdb->update($wpdb->prefix.'wdm_instructor_commission', $data, array('id' => $id));
            }
        }
    }
}


add_action('woocommerce_order_status_completed', 'wdm_add_record_to_db');

/*
 * Adding transaction details after LD transaction
 */
add_action('added_post_meta', 'wdm_instructor_updated_postmeta', 10, 4);

function wdm_instructor_updated_postmeta($meta_id, $object_id, $meta_key, $meta_value)
{
    global $wpdb;
    $post_type = get_post_type($object_id);
    $meta_id = $meta_id;
    if ('sfwd-transactions' == $post_type && 'course_id' == $meta_key) {
        $course_id = $meta_value;
        $course_post = get_post($course_id);
        $author_id = $course_post->post_author;
        if (wdm_is_instructor($author_id)) {
            $commission_percent = get_user_meta($author_id, 'wdm_commission_percentage', true);
            if ('' == $commission_percent) {
                $commission_percent = 0;
            }
            $total = get_post_meta($object_id, 'payment_gross', true);
            if ('' == $total) {
                $total = 0;
            }
            $commission_price = ($total * $commission_percent) / 100;
            $data = array(
                'user_id' => $author_id,
                'order_id' => $object_id,
                'product_id' => $course_id,
                'actual_price' => $total,
                'commission_price' => $commission_price,
            );
            $wpdb->insert($wpdb->prefix.'wdm_instructor_commission', $data);
        }
    }
}


/*
 * Commission report page
 */
function wdm_instructor_second_tab()
{
    if (!is_super_admin()) {
        $instructor_id = get_current_user_id();
    } else {
        $args = array('fields' => array('ID', 'display_name'), 'role' => 'wdm_instructor');
        $instructors = get_users($args);
        $instructor_id = '';
        if (isset($_REQUEST['wdm_instructor_id'])) {
            $instructor_id = $_REQUEST['wdm_instructor_id'];
        }
        if (empty($instructors)) {
            echo __('No instructor found', 'learndash');

            return;
        }
        ?>
		<form method="post" action="?page=instuctor&tab=commission_report">
			<table>
				<tr>
					<th><?php echo __('Select Instructor:', 'learndash');
        ?></th>
					<td>
						<select name="wdm_instructor_id">
							<?php foreach ($instructors as $instructor) {
    ?>
								<option value="<?php echo $instructor->ID;
    ?>" <?php echo(($instructor_id == $instructor->ID) ? 'selected' : '');
    ?>><?php echo $instructor->display_name;
    ?></option>

		<?php

}
        ?>
						</select>
					</td>

					<td>
						<input type="submit" value="<?php echo __('Submit', 'learndash');
        ?>" class="button-primary">
					</td>
				</tr>
			</table>
		</form>
		<?php

    }
    if ('' != $instructor_id) {
        wdm_commission_report($instructor_id);
    }
}


/*
 * Commission Report page
 *
 */
function wdm_commission_report($instructor_id)
{
    global $wpdb;
    wp_enqueue_script('wdm_footable_pagination', plugins_url('/js/footable.paginate.js', __FILE__), array('jquery'));
    wp_enqueue_script('wdm_instructor_report_js', plugins_url('/js/commission_report.js', __FILE__), array('jquery'));
    $data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'enter_amount' => __('Please Enter amount', 'learndash'),
        'enter_amount_less_than' => __('Please enter amount less than amount to be paid', 'learndash'),
        'added_successfully' => __('Record added successfully', 'learndash'),
    );
    wp_localize_script('wdm_instructor_report_js', 'wdm_commission_data', $data);
    ?>
	<br><br>
	<div id="reports_table_div" style="padding-right: 5px">
		<div class="CL"></div>
	<?php echo __('Search', 'learndash');
    ?>
		<input id="filter" type="text">
		<select name="change-page-size" id="change-page-size">
			<option value="5"><?php echo __('5 per page', 'learndash');
    ?></option>
			<option value="10"><?php echo __('10 per page', 'learndash');
    ?></option>
			<option value="20"><?php echo __('20 per page', 'learndash');
    ?></option>
			<option value="50"><?php echo __('50 per page', 'learndash');
    ?></option>
		</select>
		
		<!--Table shows Name, Email, etc-->
		<br><br>
		<table class="footable" data-filter="#filter" data-page-navigation=".pagination" id="wdm_report_tbl" data-page-size="5" >
			<thead>
				<tr>
					<th data-sort-initial="descending" data-class="expand">
						<?php echo __('Order ID', 'learndash');
    ?>
					</th>
					<th data-sort-initial="descending" data-class="expand">
						<?php echo __('Product / Course Name', 'learndash');
    ?>
					</th>
					<th>
						<?php echo __('Actual Price', 'learndash');
    ?>
					</th>
					<th>
	<?php echo __('Commission Price', 'learndash');
    ?>
					</th>

				</tr>
				<?php do_action('wdm_commission_report_table_header', $instructor_id);
    ?>
			</thead>
			<tbody>
				<?php
                $sql = "SELECT * FROM {$wpdb->prefix}wdm_instructor_commission WHERE user_id = $instructor_id";
                $results = $wpdb->get_results($sql);

                if (!empty($results)) {
                    $amount_paid = 0;
                    foreach ($results as $value) {
                        $amount_paid += $value->commission_price;
                        ?>
						<tr>
							<td>
							<?php if (is_super_admin()) {
    ?>
									<a href="<?php echo(is_super_admin() ? site_url('wp-admin/post.php?post='.$value->order_id.'&action=edit') : '#');
    ?>" target="<?php echo(is_super_admin() ? '_new_blank' : '');
    ?>"><?php echo $value->order_id;
    ?></a>
							
								<?php

} else {
    echo $value->order_id;
}
            ?>
							</td>
							<td><a target="_new_blank"href="<?php echo site_url('wp-admin/post.php?post='.$value->product_id.'&action=edit');
                        ?>"><?php echo get_the_title($value->product_id);
            ?></a></td>
							<td><?php echo $value->actual_price;
                        ?></td>
							<td><?php echo $value->commission_price;
                        ?></td>

						</tr>
                        <?php

                    }
                } else {
                    ?>
					<tr>
						<td><?php echo __('No record found!', 'learndash');
                    ?></td>
					</tr>
                            <?php

                }
                do_action('wdm_commission_report_table', $instructor_id);
    ?>
			</tbody>
			<tfoot >
				<?php
                if (!empty($results)) {
                    $paid_total = get_user_meta($instructor_id, 'wdm_total_amount_paid', true);
                    if ('' == $paid_total) {
                        $paid_total = 0;
                    }
                    
                    $amount_paid = round(($amount_paid - $paid_total), 2);
                    $amount_paid = max($amount_paid, 0);
                    ?>
					<tr>
						<td></td>
						<th style="color:black;font-weight: bold;">
		<?php echo __('Paid Earnings', 'learndash');
                    ?>
						</th>
						<td><a><span id="wdm_total_amount_paid"><?php echo $paid_total;
                    ?></span></a></td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<th style="color:black;font-weight: bold;">
							<?php echo __('Unpaid Earnings', 'learndash');
                    ?>
						</th>
						<td>

							<span id="wdm_amount_paid"><?php echo $amount_paid;
                    ?></span>    <?php if (0 != $amount_paid && is_super_admin()) {
    ?>
								<a href="#" class="button-primary" id="wdm_pay_amount"><?php echo __('Pay', 'learndash');
    ?></a>
					<?php

                    }
                    ?>
						</td>
						<td></td>
					</tr>

	<?php

                }
    ?>
				<tr>
					<td colspan="4" style="border-radius: 0 0 6px 6px;">
						<div class="pagination pagination-centered hide-if-no-paging"></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	<!-- popup div starts -->
	<div id="popUpDiv" style="display: none; top: 627px; left: 17%;">
		<div style="clear:both"></div>
		<table class="widefat" id="wdm_tbl_staff_mail">
			<thead>
				<tr>
					<th colspan="2">
						<strong><?php echo __('Transaction', 'learndash');
    ?></strong>

			<p id="wdm_close_pop" colspan="1" onclick="popup( 'popUpDiv' )"><span>X</span></p>
			</th>
			</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<strong><?php echo __('Paid Earnings', 'learndash');
    ?></strong>
					</td>
					<td>
						<input type="number" id="wdm_total_amount_paid_price" value="" readonly="readonly">
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo __('Unpaid Earnings', 'learndash');
    ?></strong>
					</td>
					<td>
						<input type="number" id="wdm_amount_paid_price" value="" readonly="readonly">
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo __('Enter amount', 'learndash');
    ?></strong>
					</td>
					<td>
						<input type="number" id="wdm_pay_amount_price" value="" >
					</td>
				</tr>
				<?php do_action('wdm_commisssion_report_popup_table', $instructor_id);
    ?>
				<tr>
					<td colspan="2">
						<input type="hidden" id="instructor_id" value="<?php echo $instructor_id;
    ?>">
						<input class="button-primary" type="button" name="wdm_btn_send_mail" value="<?php echo __('Pay', 'learndash');
    ?>" id="wdm_pay_click"><img src="<?php echo plugins_url('/images/ajax-loader.gif', __FILE__);
    ?>" style="display: none" class="wdm_ajax_loader">
					</td>
				</tr>
			</tbody>
		</table>
	</div> <!-- popup div ends -->
<?php

}

add_action('wp_ajax_wdm_amount_paid_instructor', 'wdm_amount_paid_instructor');

/*
 * Update user meta of instructor for amount paid
 *
 */
function wdm_amount_paid_instructor()
{
    if (!is_super_admin()) {
        die();
    }
    $instructor_id = filter_input(INPUT_POST, 'instructor_id', FILTER_SANITIZE_NUMBER_INT);
    if (('' == $instructor_id) || (!wdm_is_instructor($instructor_id))) {
        echo json_encode(array('error' => __('Oops something went wrong', 'learndash')));
        die();
    }

    $total_paid = filter_input(INPUT_POST, 'total_paid', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $amount_tobe_paid = filter_input(INPUT_POST, 'amount_tobe_paid', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $enter_amount = filter_input(INPUT_POST, 'enter_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $usr_instructor_total = get_user_meta($instructor_id, 'wdm_total_amount_paid', true);

    $usr_instructor_total = getUsrInstructorTotal($usr_instructor_total);
    if (('' == $amount_tobe_paid || '' == $enter_amount) || $total_paid != $usr_instructor_total
        || $enter_amount > $amount_tobe_paid) {
        echo json_encode(array('error' => __('Oops something went wrong', 'learndash')));
        die();
    }

    global $wpdb;
    $sql = "SELECT commission_price FROM {$wpdb->prefix}wdm_instructor_commission WHERE user_id = $instructor_id";
    $results = $wpdb->get_col($sql);
    if (empty($results)) {
        echo json_encode(array('error' => __('Oops something went wrong', 'learndash')));
        die();
    } else {
        $vald_amnt_tobe_paid = 0;
        foreach ($results as $value) {
            $vald_amnt_tobe_paid += $value;
        }
        $vald_amnt_tobe_paid = round(($vald_amnt_tobe_paid - $total_paid), 2);
        if ($vald_amnt_tobe_paid != $amount_tobe_paid) {
            echo json_encode(array('error' => __('Oops something went wrong', 'learndash')));
            die();
        }
    }

    $new_paid_amount = round(($total_paid + $enter_amount), 2);
    update_user_meta($instructor_id, 'wdm_total_amount_paid', $new_paid_amount);

    /*
     * instructor_id is id of the instructor
     * enter_amount is amount entered by admin to pay
     * total_paid is the total amount paid by admin to insturctor before current transaction
     * amount_tobe_paid is the amount required to be paid by admin
     * new_paid_amount is the total amount paid to instructor after current transaction
     */
    do_action('wdm_commission_amount_paid', $instructor_id, $enter_amount, $total_paid, $amount_tobe_paid, $new_paid_amount);
    $new_amount_tobe_paid = round(($amount_tobe_paid - $enter_amount), 2);

    $data = array(
        'amount_tobe_paid' => $new_amount_tobe_paid,
        'total_paid' => $new_paid_amount,
    );
    echo json_encode(array('success' => $data));
    die();
}


/**
 * Function returns user instructor total.
 */
function getUsrInstructorTotal($usr_instructor_total)
{
    if ('' == $usr_instructor_total) {
        return 0;
    }

    return $usr_instructor_total;
}

add_action('admin_init', 'wdm_export_commission_report');

/*
 * Export functionality for admin as well as instructor
 */
function wdm_export_commission_report()
{
    if (isset($_GET['wdm_commission_report']) &&  'wdm_commission_report' == $_GET['wdm_commission_report']) {
        global $wpdb;
        $instructor_id = $_REQUEST['wdm_instructor_id'];
        $user_data = get_user_by('id', $instructor_id);
        
        $sql = "SELECT * FROM {$wpdb->prefix}wdm_instructor_commission WHERE user_id=$instructor_id";
        $results = $wpdb->get_results($sql);
        
        $course_progress_data = array();
        $amount_paid = 0;
        if (empty($results)) {
            $row = array('instructor name' => $user_data->display_name);
        } else {
            foreach ($results as $value) {
                $row = array(
                    'order id' => $value->order_id,
                    'instructor name' => $user_data->display_name,
                    'actual price' => $value->actual_price,
                    'commission price' => $value->commission_price,
                    'product name' => get_the_title($value->product_id),
                    'transaction time' => $value->transaction_time,
                );
                $amount_paid = $amount_paid + $value->commission_price;
                $course_progress_data[] = $row;
            }
            $paid_total = get_user_meta($instructor_id, 'wdm_total_amount_paid', true);
            if ('' == $paid_total) {
                $paid_total = 0;
            }
            $amount_paid = round(($amount_paid - $paid_total), 2);
            $amount_paid = max($amount_paid, 0);
            $row = array(
                    'order id' => __('Paid Earnings', 'learndash'),
                    'instructor name' => $paid_total,
                    'actual price' => '',
                    'commission price' => '',
                    'product name' => '',
                    'transaction time' => '',
                    );
            $course_progress_data[] = $row;
            $row = array(
                    'order id' => __('Unpaid Earnings', 'learndash'),
                    'instructor name' => $amount_paid,
                    'actual price' => '',
                    'commission price' => '',
                    'product name' => '',
                    'transaction time' => '',
                    );
            $course_progress_data[] = $row;
        }

        if (file_exists(dirname(__FILE__).'/includes/parsecsv.lib.php')) {
            require_once dirname(__FILE__).'/includes/parsecsv.lib.php';
            $csv = new lmsParseCSVNS\LmsParseCSV();

            $csv->output(true, 'commission_report.csv', $course_progress_data, array_keys(reset($course_progress_data)));

            die();
        }
    }
}

/*
 * Export tab for insturctor and admin
 *
 */
function wdm_instructor_third_tab()
{
    if (!is_super_admin()) {
        $instructor_id = get_current_user_id();
    } else {
        $args = array('fields' => array('ID', 'display_name'), 'role' => 'wdm_instructor');
        $instructors = get_users($args);

        $instructor_id = '';
        if (isset($_REQUEST['wdm_instructor_id'])) {
            if ('-1' == $_REQUEST['wdm_instructor_id']) {
                $instructor_id = '-1';
            } else {
                $instructor_id = $_REQUEST['wdm_instructor_id'];
            }
        }
        if (empty($instructors)) {
            echo __('No instructor found', 'learndash');

            return;
        }
    }
    $url = plugins_url('/js/jquery-ui.js', __FILE__);
    wp_enqueue_script('wdm-date-js', $url, array('jquery'), true);
    $url = plugins_url('/css/jquery-ui.css', __FILE__);
    wp_enqueue_style('wdm-date-css', $url);
    wp_enqueue_script('wdm-datepicker-js', plugins_url('/js/wdm_datepicker.js', __FILE__), array('jquery'));
    $start_date = wdmCheckIsSet($_POST['wdm_start_date']);//isset($_POST['wdm_start_date']) ? $_POST['wdm_start_date'] : '';
        $end_date = wdmCheckIsSet($_POST['wdm_end_date']);//isset($_POST['wdm_end_date']) ? $_POST['wdm_end_date'] : '';
        ?>
		<form method="post" action="?page=instuctor&tab=export">
			<table>
				<?php if (is_super_admin()) {
    ?>
				<tr>
					<th style="float:left;"><?php echo __('Select Instructor:', 'learndash');
    ?></th>
					<td>
						<select name="wdm_instructor_id">
							<option value="-1"><?php echo __('All', 'learndash');
    ?></option>
							<?php foreach ($instructors as $instructor) {
    ?>
								<option value="<?php echo $instructor->ID;
    ?>" <?php echo(($instructor_id == $instructor->ID) ? 'selected' : '');
    ?>><?php echo $instructor->display_name;
    ?></option>

		<?php

}
    ?>
						</select>
					</td>
				</tr>
				<?php

}
    ?>
				<tr>
					<th style="float:left;"><?php echo __('Start Date:', 'learndash');
    ?></th>
					<td>
						<input type="text" name="wdm_start_date" id="wdm_start_date" value="<?php echo $start_date;
    ?>">
					</td>
				</tr>
				<tr>
					<th style="float:left;"><?php echo __('End Date:', 'learndash');
    ?></th>
					<td>
						<input type="text" name="wdm_end_date" id="wdm_end_date" value="<?php echo $end_date;
    ?>">
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" class="button-primary" value="<?php echo __('Submit', 'learndash');
    ?>" id="wdm_submit">
					</td>
				</tr>
			</table>
		</form>
		<?php
        //}
        if ('' != $instructor_id) {
            wdm_export_csv_report($instructor_id, $start_date, $end_date);
        }
}

/**
 * Function to check post is set or not.
 */
function wdmCheckIsSet($post)
{
    if (isset($post)) {
        return $post;
    }
    return '';
}


/*
 * Report filtered by instructor, start and end date
 */
function wdm_export_csv_report($instructor_id, $start_date, $end_date)
{
    global $wpdb;
    wp_enqueue_script('wdm_footable_pagination', plugins_url('/js/footable.paginate.js', __FILE__), array('jquery'));
    ?>
	<br><br>
	<div id="reports_table_div" style="padding-right: 5px">
		<div class="CL"></div>
	<?php echo __('Search', 'learndash');
    ?>
		<input id="filter" type="text">
		<select name="change-page-size" id="change-page-size">
			<option value="5"><?php echo __('5 per page', 'learndash');
    ?></option>
			<option value="10"><?php echo __('10 per page', 'learndash');
    ?></option>
			<option value="20"><?php echo __('20 per page', 'learndash');
    ?></option>
			<option value="50"><?php echo __('50 per page', 'learndash');
    ?></option>
		</select>
		<?php if (file_exists(dirname(__FILE__).'/includes/parsecsv.lib.php')) {
            $url = admin_url('admin.php?page=instuctor&tab=export&wdm_export_report=wdm_export_report&wdm_instructor_id='.$instructor_id.'&start_date='.$start_date.'&end_date='.$end_date);
    ?>
		<a href="<?php echo $url;
    ?>" class="button-primary" style="float:right"><?php echo __('Export CSV', 'learndash');
    ?></a>
		<?php

}
    ?>
		<!--Table shows Name, Email, etc-->
		<br><br>
		<table class="footable" data-filter="#filter" data-page-navigation=".pagination" id="wdm_report_tbl" data-page-size="5" >
			<thead>
				<tr>
					<th data-sort-initial="descending" data-class="expand">
						<?php echo __('Order ID', 'learndash');
    ?>
					</th>
					<th data-sort-initial="descending" data-class="expand">
						<?php echo __('Username', 'learndash');
    ?>
					</th>
					<th data-sort-initial="descending" data-class="expand">
						<?php echo __('Product / Course Name', 'learndash');
    ?>
					</th>
					<th>
						<?php echo __('Actual Price', 'learndash');
    ?>
					</th>
					<th>
	<?php echo __('Commission Price', 'learndash');
    ?>
					</th>

				</tr>
				<?php do_action('wdm_commission_report_table_header', $instructor_id);
    ?>
			</thead>
			<tbody>
				<?php
                $sql = "SELECT * FROM {$wpdb->prefix}wdm_instructor_commission WHERE 1=1 ";
                //echo $start_date;exit;
                if ('-1' != $instructor_id) {
                    $sql .= "AND user_id = $instructor_id ";
                }
                if ('' != $start_date) {
                    $start_date = Date('Y-m-d', strtotime($start_date));
                    $sql .= "AND transaction_time >='$start_date 00:00:00'";
                }
                if ('' != $end_date) {
                    $end_date = Date('Y-m-d', strtotime($end_date));
                    $sql .= " AND transaction_time <='$end_date 23:59:59'";
                }

                $results = $wpdb->get_results($sql);

                if (!empty($results)) {

                    foreach ($results as $value) {
                        $user_details = get_user_by('id', $value->user_id);

                        ?>
						<tr>
							<td>
								<?php if (is_super_admin()) {
    ?>
									<!-- <a href="<?php //echo (is_super_admin() ? site_url('wp-admin/post.php?post=' . $value->order_id . '&action=edit') : '#'); ?>" target="<?php //echo (is_super_admin() ? '_new_blank' : ''); ?>"><?php //echo $value->order_id; ?></a> -->
                                    <a href="<?php echo reduceWdmExportCsvReportComplex($value->order_id);
    ?>" target="<?php echo needToOpenNewDocument();
    ?>"><?php echo $value->order_id;
    ?></a>
							
								<?php

} else {
    echo $value->order_id;
}
                        ?>
							</td>
							<td><?php echo $user_details->display_name;
                        ?></td>
							<td><a target="_new_blank"href="<?php echo site_url('wp-admin/post.php?post='.$value->product_id.'&action=edit');
                        ?>"><?php echo get_the_title($value->product_id);
                        ?></a></td>
							<td><?php echo $value->actual_price;
                        ?></td>
							<td><?php echo $value->commission_price;
                        ?></td>

						</tr>
			<?php

                    }
                } else {
                    ?>
					<tr>
						<td><?php echo __('No record found!', 'learndash');
                    ?></td>
					</tr>
                            <?php

                }
                do_action('wdm_commission_report_table', $instructor_id);
    ?>
			</tbody>
			<tfoot >
				
				<tr>
					<td colspan="5" style="border-radius: 0 0 6px 6px;">
						<div class="pagination pagination-centered hide-if-no-paging"></div>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	<br>
	<?php if (file_exists(dirname(__FILE__).'/includes/parsecsv.lib.php')) {
        $url = admin_url('admin.php?page=instuctor&tab=export&wdm_export_report=wdm_export_report&wdm_instructor_id='.$instructor_id.'&start_date='.$start_date.'&end_date='.$end_date);
    ?>
		<a href="<?php echo $url;
    ?>" class="button-primary" style="float:right"><?php echo __('Export CSV', 'learndash');
    ?></a>
		<?php

}
}

/**
 * Function to return site url to edit post, if current user is super admin.
 */
function reduceWdmExportCsvReportComplex($value)
{
    if (is_super_admin()) {
        return site_url('wp-admin/post.php?post='.$value.'&action=edit');
    }

    return '#';
}

/**
 * Function returns string '_new_blank', if user is super admin.
 */
function needToOpenNewDocument()
{
    if (is_super_admin()) {
        return '_new_blank';
    }

    return '';
}

add_action('admin_init', 'wdm_export_csv_date_filter');


/**
 * Export data filter wise
 */
function wdm_export_csv_date_filter()
{
    if (isset($_GET['wdm_export_report']) &&  'wdm_export_report' == $_GET['wdm_export_report']) {
        global $wpdb;
        $instructor_id = $_REQUEST['wdm_instructor_id'];
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
        $sql = "SELECT * FROM {$wpdb->prefix}wdm_instructor_commission WHERE 1=1";
        if ('' != $instructor_id && '-1' != $instructor_id) {
            $sql .= ' AND user_id='.$instructor_id;
        }
        if ('' != $start_date) {
            $start_date = Date('Y-m-d', strtotime($start_date));
            $sql .= " AND transaction_time >='$start_date 00:00:00'";
        }
        if ('' != $end_date) {
            $end_date = Date('Y-m-d', strtotime($end_date));
            $sql .= " AND transaction_time <='$end_date 23:59:59'";
        }
        
        $results = $wpdb->get_results($sql);
        
        $course_progress_data = array();
       
       
        if (empty($results)) {
            $row = array('No data' => __('No data found', 'learndash'));
        } else {
            foreach ($results as $value) {
                $user_data = get_user_by('id', $value->user_id);
                $row = array(
                    'order id' => $value->order_id,
                    'instructor name' => $user_data->display_name,
                    'actual price' => $value->actual_price,
                    'commission price' => $value->commission_price,
                    'product name' => get_the_title($value->product_id),
                    'transaction time' => $value->transaction_time,
                );

                $course_progress_data[] = $row;
            }
        }

        if (file_exists(dirname(__FILE__).'/includes/parsecsv.lib.php')) {
            require_once dirname(__FILE__).'/includes/parsecsv.lib.php';
            $csv = new lmsParseCSVNS\LmsParseCSV();

            $csv->output(true, 'commission_report.csv', $course_progress_data, array_keys(reset($course_progress_data)));

            die();
        }
    }
}
