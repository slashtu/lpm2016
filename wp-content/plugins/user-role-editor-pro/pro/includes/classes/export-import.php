<?php
/*
 * User Role Editor WordPress plugin
 * Class to export/import roles
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v3
 * 
*/

class Ure_Export_Import {

    // reference to the code library object
    protected $lib = null;
    // array with roles to import
    private $roles = null;
    
    public function __construct() {
        
        $this->lib = URE_Lib_Pro::get_instance();
        add_action('ure_role_edit_toolbar_service', array(&$this, 'add_toolbar_buttons'));
        add_action('ure_load_js', array(&$this, 'add_js'));
        add_action('ure_dialogs_html', array(&$this, 'import_roles_dialog_html'));
        add_action('ure_process_user_request', array(&$this, 'import_roles'));
        add_action('ure_process_user_request', array(&$this, 'import_roles_notification'));
        add_action('init', array(&$this, 'export_roles'));        
        
    }
    // end of __construct()
    
    public function add_toolbar_buttons() {
        $shown = false;
        if (current_user_can('ure_export_roles')) {
            $shown = true;
?>
                
                  <button id="ure_export_roles_button" class="ure_toolbar_button" title="Export All Roles to your local disk">Export</button> 
<?php
        }
        if (current_user_can('ure_import_roles')) {
            $shown = true;
?>

                  <button id="ure_import_roles_button" class="ure_toolbar_button" title="Import Roles from your local disk">Import</button>
<?php
        }
        if ( $shown && ( !is_multisite() || (is_network_admin() && is_super_admin()) ) ) {
?>
                  <hr />
               
<?php
        }
    }
    // end of add_toolbar_buttons()
    
    
    public function add_js() {
        wp_register_script( 'ure-js-exp-imp', plugins_url( '/pro/js/ure-js-exp-imp.js', URE_PLUGIN_FULL_PATH ) );
        wp_enqueue_script ( 'ure-js-exp-imp' );
        wp_localize_script( 'ure-js-exp-imp', 'ure_data_exp_imp', 
                array(
                    'export_roles' => esc_html__('Export', 'user-role-editor'),
                    'import_roles' => esc_html__('Import', 'user-role-editor'),
                    'import_roles_title' => esc_html__('Import Roles', 'user-role-editor'),
                    'select_file_with_roles' => esc_html__('Select file with roles data', 'user-role-editor')
                ));
    }
    // end of add_js()
    
    
    private function base64_enc_value(&$value, $key) {
        if (is_string($value)) {
            $value = base64_encode($value);
        }
    }
    // end of base64_enc_value()
    
    
    private function base64_dec_value(&$value, $key) {
        if (is_string($value)) {
            $value = base64_decode($value);
        }
    }
    // end of base64_dec_value()
    
    
    public function export_roles() {        
        global $pagenow;
        
        if ($pagenow!=='users.php') {
            return; 
        }
        if (!isset($_GET['page']) || $_GET['page']!=='users-'.URE_PLUGIN_FILE) {
            return;
        }
        if (!isset($_POST['action']) || $_POST['action']!=='export-roles') {
            return;
        }
        if (empty($_POST['ure_nonce']) || !wp_verify_nonce($_POST['ure_nonce'], 'user-role-editor')) {
            echo '<h3>Wrong nonce. Action prohibitied.</h3>';
            exit;
        }
                
        if (!current_user_can('ure_export_roles')) {
            echo esc_html__('You do not have sufficient permissions to use this add-on.', 'user-role-editor');
            exit; 
        }
        
        $this->lib->get_user_roles();
        $roles_tmp = $this->lib->roles;
        array_walk_recursive($roles_tmp, array($this, 'base64_enc_value'));
        $serialized_roles = base64_encode(serialize($roles_tmp));                
        $timestamp = date('_Y-m-d_h_i_s', current_time('timestamp'));

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false); // required for certain browsers 
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="ure-roles-backup'. $timestamp .'.dat";');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($serialized_roles));

        echo $serialized_roles;

        exit;                
    }
    // end of export_roles()
    
    
    public function import_roles_dialog_html() {
        if (!current_user_can('ure_import_roles')) {
            return;
        }
?>   
<div id="ure_import_roles_dialog" class="ure-modal-dialog">
    <form name="ure_import_roles_form" id="ure_import_roles_form" method="post" enctype="multipart/form-data">  
  <div style="padding:10px;">
    <div class="ure-label"><?php echo __('Select file with roles: ', 'user-role-editor'); ?></div>
    <div class="ure-input"><input type="file" name="roles_file" id="roles_file" style="width: 350px;"/></div>
<?php		
	if (is_multisite() && is_main_site( get_current_blog_id() ) && is_super_admin()) {
			$hint = __('If checked, then apply action to ALL sites of this Network');
			$checked = 'checked="checked"';
			$fontColor = 'color:#FF0000;';
?>
			<div style="clear: left; display:block; margin-left:10px; <?php echo $fontColor;?>" id="ure_import_to_all_div">
					<input type="checkbox" name="ure_import_to_all" id="ure_import_to_all" value="1" 
							<?php echo $checked; ?> title="<?php echo $hint;?>" onclick="ure_importToAllOnClick(this)"/>
					<label for="ure_import_to_all" title="<?php echo $hint;?>"><?php _e('Apply to All Sites', 'user-role-editor');?></label>
			</div>
<?php
}		
?>
    <input type="hidden" name="action" id="action" value="import-roles" />
  </div>  
    </form>    
</div>     
                  
<?php        
    }
    // end of import_roles_dialog_html()
    
    
    protected function save_imported_roles() {
        $this->lib->apply_to_all = isset($_POST['ure_import_to_all']) ? 1 : 0;
        if ($this->lib->apply_to_all && is_multisite() && is_super_admin()) {   
           $result = $this->save_roles_to_all_sites();
        } else {
           $result = $this->save_roles_to_current_site();
        }
        
        return $result;
    }
    // end of save_imported_roles()
        
    
    public function import_roles() {        
        
        if ($_POST['action']!=='import-roles' || !isset($_FILES['roles_file'])) {
            return;
        }        
        if (current_user_can('ure_import_roles')) {        
            $upload_dir = wp_upload_dir();
            if (empty($upload_dir['error'])) {           
                $upload_file = $upload_dir['path'] . '/roles_data.tmp';
                $result = false;
                if (move_uploaded_file($_FILES['roles_file']['tmp_name'], $upload_file)) {
                    $roles_tmp = unserialize(base64_decode(file_get_contents($upload_file)));
                    array_walk_recursive($roles_tmp, array($this, 'base64_dec_value'));
                    $this->roles = $roles_tmp;                    
                    unlink($upload_file);
                    // check array structure
                    $result = $this->validate_roles();
                    if ($result->success) { 
                        $result->success = $this->save_imported_roles();
                    }           
                }        
            } else {
                $result = new StdClass();
                $result->success = false;
                $result->message = esc_html__('File upload error.', 'user-role-editor') .' '. $upload_dir['error'];
            }
        } else {
            $result = new StdClass();
            $result->success = false;
            $result->message = esc_html__('You do not have sufficient permissions to use this add-on.', 'user-role-editor');                        
        }
                        
        $reload_link = wp_get_referer();
        $reload_link = esc_url_raw(remove_query_arg('action', $reload_link));
        ?>    
        	<script type="text/javascript" >
             jQuery.ure_postGo('<?php echo $reload_link; ?>', 
                      { action: 'roles_import_note', 
                        result: <?php echo ($result->success ? 1 : 0);?>,  
                        message: '<?php echo $result->message;?>',
                        ure_nonce: ure_data.wp_nonce} );
        	</script>  
        <?php
        exit;
    }
    // end of import_roles()

    
    protected function validate_role($role_id, $role) {
        
        $result = $this->lib->init_result();
        
        $sanitized_role_id = sanitize_key($role_id);
        if ($role_id!==$sanitized_role_id) {
            $result->message = esc_html__('Import failure: role ID contains invalid characters.', 'user-role-editor');            
        } elseif (!is_array($role)) {
            $result->message = esc_html__('Import failure: role should have an array structure.', 'user-role-editor');            
        } elseif (count($role)!==2) { // only two items 'name' and 'capabilities' should be in role array
            $result->message = esc_html__('Import failure: role '.$role_id.' array is not valid (> 2 items).', 'user-role-editor');            
        } elseif (!isset($role['name']) || !isset($role['capabilities'])) {   // wrong role array structure
            $result->message = esc_html__('Import failure: Wrong role '.$role_id.' array structure: name and capabilities not found.', 'user-role-editor');
        } elseif ($role['name']!==($name = sanitize_text_field($role['name']))) {  // wrong characters in the role name
            $result->message = esc_html__('Import failure: Wrong characters in the role name - sanitized version:', 'user-role-editor').' '.$name;        
        } elseif (!is_array($role['capabilities'])) {
            $result->message = esc_html__('Import failure: role capabilities should have an array structure.', 'user-role-editor');
        } else {
            $result->success = true;
        }
                        
        return $result;        
    }
    // end of validate_role();
    
    
    protected function sanitize_capability($key) {
    
        $filter0 = '/[^a-zA-Z0-9_\-\s\/]/';   //  should be a valid PHP regular expression
        $filter = apply_filters('ure_sanitize_capability_filter', $filter0);
        $key1 = preg_replace($filter, '', $key);
        
        return $key1;
    }
    // end of sanitize_capability()
    

    protected function validate_capabilities($role_id, $capabilities) {
        $result = $this->lib->init_result();
        foreach($capabilities as $key=>$value) {
            $result->success = false;
            $sanitized_key = $this->sanitize_capability($key);
            if ($key!==$sanitized_key) {    // illegial character found at the capability identifire
                $result->message = esc_html__('Import failure: Wrong characters in the capability ID - sanitized version:', 'user-role-editor').' '.$sanitized_key;
                break;
            } elseif (!is_bool($value) && $value!=1 && $value!=0) {
                $message = __('Import failure: Role "%s", wrong capability value for "%s" - only 1 and 0 are allowed, but "%s" found', 'user-role-editor');
                $result->message = esc_html(sprintf($message, $role_id, $key, $value));
                break;
            } else {
                $result->success = true;
            }
            // add missed capabilities to the administrator role
            if ($role_id!=='administrator' && !isset($this->roles['administrator']['capabilities'][$key])) {
                $this->roles['administrator']['capabilities'][$key] = true;            
            }
        }   // foreach($role['capabilities']        
        
        return $result;
    }
    // end of validate_capabilities()
    
    
    // add missed standard capabilities to the administrator role
    protected function restore_missed_built_in_wp_caps() {
        
        $built_in_wp_caps = $this->lib->get_built_in_wp_caps();
        foreach($built_in_wp_caps as $key=>$value) {
            if (!isset($this->roles['administrator']['capabilities'][$key])) {
                $this->roles['administrator']['capabilities'][$key] = 1;
            }
        }

    }
    // end of restore_missed_built_in_wp_caps()
    
    
    // for security reasons: prevent deletion of administrator role itself or its critical capabilities 
    // via use of import of broken/changed file
    private function validate_roles() {
        
        $result = $this->lib->init_result();
        if (!is_array($this->roles) || count($this->roles)==0) {   // not valid roles array
            $result->message = __('Import failure: Roles file is broken possibly.', 'user-role-editor');
            return $result;
        }
        // add administrator role if it's missed at the roles to import
        if (!isset($this->roles['administrator'])) {
            $this->roles['administrator'] = array('name'=>'Administrator', 'capabilities'=>array('read'=>1));
        }                
        
        foreach($this->roles as $role_id=>$role) {
            $result = $this->validate_role($role_id, $role);
            if (!$result->success) {
                return $result;
            }
            if (count($role['capabilities'])>0) {
                $result = $this->validate_capabilities($role_id, $role['capabilities']);                
                if (!$result->success) {
                    return $result;
                }                        
            }
        }
        // foreach($this->roles

        $this->restore_missed_built_in_wp_caps();
                
        $result->success = true;
        
        return $result;
    }
    // end of validate_roles()

    
    private function save_roles_to_current_site() {
        global $wpdb;
        
        $option_name = $wpdb->prefix . 'user_roles';
        update_option($option_name, $this->roles);                    
        // If option data is the same as the current one at the database then update_option returns "false". 
        // So you never know if the data was not change as they are equal or the database update error took place :(
        // There is no sense to check the returning value from it. As DB error is the rare fact, lets count 
        // the operation is always successful
        
        return true;
    }
    // end of save_roles_to_current_site()
    

    private function wp_api_network_roles_update() {
        global $wpdb;

        $old_blog = $wpdb->blogid;
        foreach ($this->lib->blog_ids as $blog_id) {
            switch_to_blog($blog_id);                
            $this->save_roles_to_current_site();
        }
        switch_to_blog($old_blog);                    
        
        return true;
    }
    // end of wp_api_network_roles_update()


    // this way is more robust for the large blog networks
    function direct_network_roles_update() {
        global $wpdb;

        $serialized_roles = serialize($this->roles);
        foreach ($this->lib->blog_ids as $blog_id) {
            $prefix = $wpdb->get_blog_prefix($blog_id);
            $options_table_name = $prefix . 'options';
            $option_name = $prefix . 'user_roles';
            $query = "update $options_table_name
                set option_value='$serialized_roles'
                where option_name='$option_name'
                limit 1";
            $wpdb->query($query);            
            if ($wpdb->last_error) {
                return false;
            }
        }  // foreach()
        
        return true;
    }
    // end of direct_network_roles_update()
    
    
    private function save_roles_to_all_sites() {        
                
        if ($this->lib->is_full_network_synch()) {
            $result = $this->direct_network_roles_update();
        } else {
            $result = $this->wp_api_network_roles_update();
        }
        
        return $result;
    }
    // save_roles_to_all_sites()
            
    
    public function import_roles_notification() {
        if ($_REQUEST['action'] == 'roles_import_note') {             
            if ($_REQUEST['result']==1) {
                $this->lib->notification = esc_html__('Roles are imported successfully', 'user-role-editor');
            } elseif (!empty($_REQUEST['message'])) {
                $this->lib->notification = sanitize_text_field($_REQUEST['message']);
            } else {
                $this->lib->notification = esc_html__('Unknown error: Roles import was failed', 'user-role-editor');
            }
        }
    }
    
}
// end of Ure_Export_Import class