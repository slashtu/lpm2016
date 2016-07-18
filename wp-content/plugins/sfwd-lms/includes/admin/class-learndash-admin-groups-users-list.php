<?php

if (!class_exists('Learndash_Admin_Groups_Users_List')) {
	class Learndash_Admin_Groups_Users_List {
		
		function __construct() {
		}
		
		function on_load() {
			
/*
			wp_enqueue_script( 
				'sfwd-admin-groups-script', 
				LEARNDASH_LMS_PLUGIN_URL . 'assets/js/sfwd-admin-groups.js', 
				array( 'jquery' ),
				LEARNDASH_VERSION,
				true
			);
*/			
		}
		
		/**
		 * Group admin page HTML output
		 * 
		 * @since 2.1.0
		 */
		function learndash_group_admin_menu_page() {

			$current_user = wp_get_current_user();

			if ( empty( $current_user->ID) || ! $current_user->has_cap( 'group_leader' ) ) {
				die(__( 'Please login as a Group Administrator', 'learndash' ) );
			}

			global $wpdb;
			$group_ids = learndash_get_administrators_group_ids( $current_user->ID );
			
			if ( ! isset( $_GET['group_id'] ) || ! in_array( $_GET['group_id'], $group_ids ) ) {
				?>
					<div class="wrap">
					<h2><?php _e( 'Group Administration', 'learndash' );?></h2>

					<table cellspacing="0" class="wp-list-table widefat fixed groups_table">
						<thead>
							<tr>
								<th class="manage-column column-sno " id="sno" scope="col"><?php _e( 'S. No.', 'learndash' );?></th>
								<th class="manage-column column-group " id="group" scope="col"><?php _e( 'Group', 'learndash' );?></th>
								<th class="manage-column column-action" id="action" scope="col"><?php _e( 'Action', 'learndash' );?></span><span class="sorting-indicator"></span></th>
							</tr>
						</thead>
						<tfoot>
						<tr>
							<th class="manage-column column-sno " id="sno" scope="col"><?php _e( 'S. No.', 'learndash' );?></th>
							<th class="manage-column column-group " id="group" scope="col"><?php _e( 'Group', 'learndash' );?></th>
							<th class="manage-column column-action" id="action" scope="col"><?php _e( 'Action', 'learndash' );?></span><span class="sorting-indicator"></span></th>
						</tr>
						</tfoot>
						<tbody>
							<?php $sn = 1; ?>
							<?php foreach ( $group_ids as $group_id ) : ?>
								<?php $group = get_post( $group_id ); ?>
								<tr>
									<td><?php echo $sn++; ?></td>
									<td><?php echo $group->post_title; ?></td>
									<td><a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page&group_id=' . $group_id );?>"><?php _e( 'List Users', 'learndash' );?></a> | <a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page&action=sfp_update_module&nonce-sfwd=' . wp_create_nonce( 'sfwd-nonce' ) . '&page_options=sfp_home_description&courses_export_submit=Export&group_id=' . $group_id );?>"><?php _e( 'Export Progress', 'learndash' );?></a> | <a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page&action=sfp_update_module&nonce-sfwd=' . wp_create_nonce( 'sfwd-nonce' ) . '&page_options=sfp_home_description&quiz_export_submit=Export&group_id=' . $group_id );?>"><?php _e( 'Export Results', 'learndash' );?></a><?php do_action( 'learndash_group_admin_page_actions', $group_id );?></td>
								</tr>
							<?php endforeach; ?>
						<tbody>
					</table>
					</div>
				<?php
			} else {
				if ( ! isset( $_GET['user_id'] ) ) {
					$group_id = intval($_GET['group_id']);
					$group = get_post( $group_id );
					if ($group) {
						?>
						<div class="wrap">
							<h2><?php echo __( 'Group Administration', 'learndash' ) . ': ' . $group->post_title;?> <small>| <a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page' );?>"><?php echo __( 'Back', 'learndash' );?></a></small></h2>
							<p>
								<?php echo $group->post_content;?>
							</p>
							<?php echo learndash_group_user_list( $group_id ); ?>
						</div>
						<!-- Email Group feature below the Group Table (on the Group Leader page) -->
						<div id="learndash_groups_page_box">
							<br><br>

							<h2><?php _e( 'Email Users', 'learndash' );?></h2>

							<br/>

							<label for="email"><b><?php _e( 'Email Subject:', 'learndash' );?></b><br/>
								<input id="group_email_sub" rows="5" class="group_email_sub"/>
							</label>

							<br/><br/>

							<label for="text"><b><b><?php _e( 'Email Message:', 'learndash' );?></b><br/>
								<div class="groupemailtext" ><?php wp_editor( '', 'groupemailtext', array( 'media_buttons' => true, 'wpautop' => true) );?></div>
							</label>

							<br/>

							<button id="email_group" type="button"><?php _e( 'Send', 'learndash' );?></button>
							<br/><br/><br/><br/><br/>
						</div>
						<script>
						jQuery(function( $){
							var sending = 0;
							$("#email_group").click(function(){
								tinyMCE.triggerSave();

								$("#email_group").html("<?php _e( 'Sending...', 'learndash' );?>");
								if(sending == 1) {
									alert("<?php _e( 'Please Wait', 'learndash' );?>");
									return;
								}

								sending = 1;
								var gid = <?php echo $group_id?>;
								var txt = $('#groupemailtext').val();
								var sub = $('#group_email_sub').val();

								$.post( "", 
									{ 
										group: gid,
										text: txt,
										sub: sub
									},
									function(data) {
										alert(data);
										$("#email_group").html("<?php _e( 'Send', 'learndash' );?>");
										sending = 0;
										tinyMCE.get('groupemailtext').setContent('');
										$('#group_email_sub').val('');
									}
								);
							});
						});
						</script>
					<?php
					}
				} else {
					$user_id        = $_GET['user_id'];
					$group_id       = $_GET['group_id'];
					$group_user_ids = learndash_get_groups_user_ids( $group_id );
					$user           = get_user_by( 'id', $user_id );
					?>
						<div class="wrap">
							<h2><?php echo __( 'Group Administration', 'learndash' ) . ': ' . $user->display_name;?> <small>| <a href="<?php echo admin_url( 'edit.php?post_type=sfwd-courses&page=group_admin_page&group_id=' . $group_id );?>"><?php echo __( 'Back', 'learndash' );?></a></small></h2>
							<?php if ( in_array( $user_id, $group_user_ids ) ) : ?>
								<?php echo learndash_course_info_shortcode( array( 'user_id' => $user_id ) ); ?>
							<?php endif; ?>
						</div>
					<?php
				}
			}

		}

		// End of functions
	}
}
