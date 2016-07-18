<?php
/*
 * User Role Editor Pro WordPress plugin options page
 *
 * @Author: Vladimir Garagulya
 * @URL: http://role-editor.com
 * @package UserRoleEditor
 *
 */

?>
      <tr>
        <td>
            <input type="checkbox" name="activate_admin_menu_access_module" id="activate_admin_menu_access_module" value="1" 
            <?php echo ($activate_admin_menu_access_module==1) ? 'checked="checked"' : ''; ?> /> 
            <label for="activate_admin_menu_access_module"><?php esc_html_e('Activate Administrator Menu Access module', 'user-role-editor'); ?></label>
        </td>
        <td>
        </td>
      </tr>
      <tr>
        <td>
            <input type="checkbox" name="activate_widgets_access_module" id="activate_widgets_access_module" value="1" 
            <?php echo ($activate_widgets_access_module==1) ? 'checked="checked"' : ''; ?> /> 
            <label for="activate_widgets_access_module"><?php esc_html_e('Activate Widgets Access module', 'user-role-editor'); ?></label>
        </td>
        <td>
        </td>
      </tr>
      <tr>
        <td>
            <input type="checkbox" name="activate_meta_boxes_access_module" id="activate_meta_boxes_access_module" value="1" 
            <?php echo ($activate_meta_boxes_access_module==1) ? 'checked="checked"' : ''; ?> /> 
            <label for="activate_meta_boxes_access_module"><?php esc_html_e('Activate Meta Boxes Access module', 'user-role-editor'); ?></label>
        </td>
        <td>
        </td>
      </tr>
      <tr>
        <td>
            <input type="checkbox" name="activate_other_roles_access_module" id="activate_other_roles_access_module" value="1" 
            <?php echo ($activate_other_roles_access_module==1) ? 'checked="checked"' : ''; ?> /> 
            <label for="activate_other_roles_access_module"><?php esc_html_e('Activate Other Roles Access module', 'user-role-editor'); ?></label>
        </td>
        <td>
        </td>
      </tr>      
      <tr>
        <td>
            <input type="checkbox" name="manage_plugin_activation_access" id="manage_plugin_activation_access" value="1" 
            <?php echo ($manage_plugin_activation_access==1) ? 'checked="checked"' : ''; ?> /> 
            <label for="manage_plugin_activation_access"><?php esc_html_e('Activate per plugin user access management for plugins activation', 'user-role-editor'); ?></label>
        </td>
        <td>
        </td>
      </tr>      
      <tr>
          <td cospan="2"><h3><?php esc_html_e('Content editing restrictions', 'user-role-editor');?></h3></td>
      </tr>
      <tr>
        <td>
            <input type="checkbox" name="activate_create_post_capability" id="activate_create_post_capability" value="1" 
            <?php echo ($activate_create_post_capability==1) ? 'checked="checked"' : ''; ?> /> 
            <label for="activate_create_post_capability"><?php esc_html_e('Activate "Create" capability for posts/pages/custom post types', 'user-role-editor'); ?></label>
        </td>
        <td>
        </td>
      </tr>      
      <tr>
        <td>
            <input type="checkbox" name="manage_posts_edit_access" id="manage_posts_edit_access" value="1" 
            <?php echo ($manage_posts_edit_access==1) ? 'checked="checked"' : ''; ?> /> 
            <label for="manage_posts_edit_access"><?php esc_html_e('Activate user access management to editing selected posts, pages, custom post types', 'user-role-editor'); ?></label>
        </td>
        <td>
        </td>
      </tr>
      <tr>
        <td>
            <input type="checkbox" name="force_custom_post_types_capabilities" id="force_custom_post_types_capabilities" value="1" 
            <?php echo ($force_custom_post_types_capabilities==1) ? 'checked="checked"' : ''; ?> /> 
            <label for="force_custom_post_types_capabilities"><?php esc_html_e('Force custom post types to use their own capabilities', 'user-role-editor'); ?></label>
        </td>
        <td>
        </td>
      </tr>
<?php
if (class_exists('GFForms')) {
?>
      <tr>
        <td>
            <input type="checkbox" name="manage_gf_access" id="manage_gf_access" value="1" 
            <?php echo ($manage_gf_access==1) ? 'checked="checked"' : ''; ?> />
            <label for="manage_gf_access"><?php esc_html_e('Activate per form user access management for Gravity Forms', 'user-role-editor'); ?></label>
        </td>
        <td> 
        </td>
      </tr>
<?php
    
}
?>
      <tr>
          <td cospan="2"><h3><?php esc_html_e('Content view restrictions', 'user-role-editor');?></h3></td>
      </tr>
      <tr>
          <td>
              <input type="checkbox" name="activate_content_for_roles_shortcode" id="activate_content_for_roles_shortcode" value="1" 
                    <?php echo ($activate_content_for_roles_shortcode==1) ? 'checked="checked"' : ''; ?> />
              <label for="activate_content_for_roles_shortcode"><?php esc_html_e('Activate [user_role_editor roles="role1, role2, ..."] shortcode', 'user-role-editor'); ?></label>
          </td>
          <td>              
          </td>
      </tr>
      <tr>
          <td>
              <input type="checkbox" name="activate_content_for_roles" id="activate_content_for_roles" value="1" 
                    <?php echo ($activate_content_for_roles==1) ? 'checked="checked"' : ''; ?> />
              <label for="activate_content_for_roles"><?php esc_html_e('Activate content view restrictions', 'user-role-editor'); ?></label>              
          </td>
          <td>              
          </td>
      </tr>
      <tr>
          <td>
             Default message for post access error:<br/>
             <textarea id="post_access_error_message" name="post_access_error_message" rows="5" cols="70"><?php echo $post_access_error_message;?></textarea> 
          </td>
          <td>
          </td>    
      </tr> 
