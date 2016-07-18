<?php
/*
 * User Role Editor Pro WordPress plugin - HTML output
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v3
 * 
 */

class URE_Pro_View {

    public static function add_role_update_network_button() {
?>        
    <div style="margin-top:10px;">
        <button id="ure_update_all_network" class="ure_toolbar_button" title="Update roles for all network">Update Network</button>
    </div>
<?php        
    }
    // end of add_role_update_network_button()

    
    public static function add_user_update_network_button() {
?>        
    <div style="margin-top:10px;">
        <button id="ure_update_all_network" class="ure_toolbar_button" title="Update user roles and capabilities for all network">Update Network</button>
    </div>
<?php        
    }
    // end of add_user_update_network_button()

    
    private static function network_update_dialog_html_role() {
        $lib = URE_Lib_Pro::get_instance();
        $activate_widgets_access_module = $lib->get_option('activate_widgets_access_module', false);

?>    
        <div id="ure_network_update_dialog" class="ure-modal-dialog">
            <div id="ure_network_update_dialog_container">
                <?php echo esc_html__('After confirmation all sites of the network will get permissions from the main site. Are you sure?', 'user-role-editor');?><br><br>
<?php 
        if (!empty($activate_widgets_access_module)) {
?>              
                <?php echo esc_html__('It is possible to replicate also:', 'user-role-editor'); ?><br>
                <input type="checkbox" id="ure_replicate_widgets_access_restrictions0" name="ure_replicate_widgets_access_restrictions0" value="1">
                <label for="ure_replicate_widgets_access_restrictions0"><?php echo esc_html__('Widgets access restrictions', 'user-role-editor');?></label>
<?php
        }
?>
                
            </div>    
        </div>    

<?php            
    }
    // end of network_update_dialog_html_role()
    
    
    private static function network_update_dialog_html_user() {

?>    
        <div id="ure_network_update_dialog" class="ure-modal-dialog">
            <div id="ure_network_update_dialog_container">
                <?php echo esc_html__('After confirmation this user will be added to all sites with the same permissions as he has at the main site. Are you sure?', 'user-role-editor');?><br><br>
                
            </div>    
        </div>    

<?php            
        
    }
    // end of network_update_dialog_html_user()
    
    
    public static function network_update_dialog_html() {
        $lib = URE_Lib_Pro::get_instance();
        if ($lib->ure_object=='role') {
            self::network_update_dialog_html_role();
        } else {
            self::network_update_dialog_html_user();
        }            
    }
    // end of network_update_dialog_html()

    
}
// end of URE_Pro_View class