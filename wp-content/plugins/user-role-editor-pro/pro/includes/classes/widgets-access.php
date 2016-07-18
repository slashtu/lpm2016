<?php

/*
 * User Role Editor WordPress plugin
 * Class URE_Admin_Menu_Access - prohibit selected menu items for role or user
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v2+ 
 */

class URE_Widgets_Access {

// reference to the code library object
    private $lib = null;    
    private $objects = null;
    private $notice = '';
    private $unregistered_widgets = null;
    private $blocked = null;

    public function __construct($lib) {
        
        $this->lib = $lib;
        $this->objects = new URE_Widgets($this->lib);
        
        add_action('ure_role_edit_toolbar_service', array(&$this, 'add_toolbar_buttons'));
        add_action('ure_load_js', array($this, 'add_js'));
        add_action('ure_dialogs_html', array($this, 'dialog_html'));
        add_action('ure_process_user_request', array($this, 'update_data'));                        
        add_action('widgets_init', array($this, 'unregister_blocked_widgets'), 100);        
        add_action('wp_ajax_widgets-order', array($this, 'ajax_widgets_order'), 0);

    }
    // end of __construct()

    
    protected function get_blocked_widgets() {
        global $current_user;
        
        if ($this->blocked===null) {
            $this->blocked = $this->objects->load_access_data_for_user($current_user);
        }
        
        return $this->blocked;
    }
    // end of get_blocked()
    

    protected function is_restriction_aplicable() {
        if ($this->lib->multisite && is_super_admin()) {
            return false;
        }
        
        if (!$this->lib->multisite && current_user_can('administrator')) {
            return false;
        }
        
        $this->get_blocked_widgets();
        if (empty($this->blocked)) {            
            return false;            
        }
        
        return true;
    }
    // end of is_restriction_aplicable()

    
    public function add_toolbar_buttons() {
        if (current_user_can('ure_widgets_access')) {
?>                
        <button id="ure_widgets_access_button" class="ure_toolbar_button" 
                title="<?php esc_html_e('Prohibit access to selected widgets','user-role-editor');?>">
            <?php esc_html_e('Widgets', 'user-role-editor');?></button>                     
<?php

        }
    }
    // end of add_toolbar_buttons()


    public function add_js() {
        wp_register_script( 'ure-widgets-access', plugins_url( '/pro/js/ure-pro-widgets-access.js', URE_PLUGIN_FULL_PATH ) );
        wp_enqueue_script ( 'ure-widgets-access' );
        wp_localize_script( 'ure-widgets-access', 'ure_data_widgets_access',
                array(
                    'widgets' => esc_html__('Widgets', 'user-role-editor'),
                    'dialog_title' => esc_html__('Widgets', 'user-role-editor'),
                    'update_button' => esc_html__('Update', 'user-role-editor'),
                    'edit_theme_options_required' => esc_html__('Turn ON "edit_theme_options" capability to manage widgets permissions', 'user-role-editor')
                ));
    }
    // end of add_js()    
    
    
    public function dialog_html() {
        
?>
        <div id="ure_widgets_access_dialog" class="ure-modal-dialog">
            <div id="ure_widgets_access_container">
            </div>    
        </div>
<?php        
        
    }
    // end of dialog_html()

            
    public function update_data() {
    
        if (!isset($_POST['action']) || $_POST['action']!=='ure_update_widgets_access') {
            return;
        }
        
        if (!current_user_can('ure_widgets_access')) {
            $this->lib->notification = esc_html__('URE: you do not have enough permissions to access this module.', 'user-role-editor');
            return;
        }
        
        $ure_object_type = filter_input(INPUT_POST, 'ure_object_type', FILTER_SANITIZE_STRING);
        if ($ure_object_type!=='role' && $ure_object_type!=='user') {
            $this->lib->notification = esc_html__('URE: widgets access: Wrong object type. Data was not updated.', 'user-role-editor');
            return;
        }
        $ure_object_name = filter_input(INPUT_POST, 'ure_object_name', FILTER_SANITIZE_STRING);
        if (empty($ure_object_name)) {
            $this->lib->notification = esc_html__('URE: widgets access: Empty object name. Data was not updated', 'user-role-editor');
            return;
        }
                        
        if ($ure_object_type=='role') {
            $this->objects->save_access_data_for_role($ure_object_name);
        } else {
            $this->objects->save_access_data_for_user($ure_object_name);
        }
        
        $this->lib->notification = esc_html__('Widgets access: data was updated successfully', 'user-role-editor');
    }
    // end of update_data()
                
    
    public function unregister_blocked_widgets() {
             
        if (!$this->is_restriction_aplicable()) {
            return;
        }
                        
        $widgets = $this->objects->get_all_widgets();
        $this->unregistered_widgets = array();
        foreach($this->blocked as $widget) {
            $this->unregistered_widgets[$widget] = $widgets[$widget]->id_base;
            unregister_widget($widget);            
        }        
        
    }
    // end of unregister_blocked_widgets()

    
    /* 
     * Widget list decoding code was written on the base of wp_ajax_widgets_order() from wp-admin/ajax-actions.php
     * 
     */
    private function decode_widgets_list($widgets_list) {
        $widgets = array();
        $widgets_raw = explode(',', $widgets_list);
        foreach ($widgets_raw as $key => $widget_id_str) {
            if (strpos($widget_id_str, 'widget-') === false) {
                continue;
            }
            $widgets[$key] = substr($widget_id_str, strpos($widget_id_str, '_') + 1);
        }
        
        return $widgets;
    }
    // end of decode_widget_id_str()
    
    
    /**
     * Convert string from POST to the sidebars with widgets array     
     * @return array
     */
    private function get_sidebars_from_post() {
        if (!is_array($_POST['sidebars'])) {
            return array();
        }
        $sidebars = array();
        foreach ($_POST['sidebars'] as $key=>$widgets_list) {            
            if (empty($widgets_list)) {
                continue;
            }                                    
            $sidebars[$key] = $this->decode_widgets_list($widgets_list);
        }                
        
        return $sidebars;
    }
    // end of get_sidebars_from_post()
    
    
    private function get_id_base_from_str($widget_id_str) {
       
        $id_base = substr($widget_id_str, 0, strrpos($widget_id_str, '-'));
       
       return $id_base;
    }
    // get_id_base_from_str()
    
    
    private function is_widget_blocked($id_base) {
        
        $result = false;
        foreach($this->blocked as $widget_class) {
            if ($this->unregistered_widgets[$widget_class]===$id_base) {
                $result = true;
                break;
            }
        }
        
        return $result;
    }
    // end of is_widget_blocked()
    
    
    private function get_active_widgets_blocked_for_current_role() {
        
        $sidebars_to_save = array();            
        $sidebars = wp_get_sidebars_widgets();
        foreach ($sidebars as $key => $widgets_list) {
            if ($key == 'wp_inactive_widgets') {
                $sidebars_to_save[$key] = $widgets_list;
                continue;
            }
            $widgets_to_save = array();
            foreach ($widgets_list as $id_str) {
                $id_base = $this->get_id_base_from_str($id_str);
                if ($this->is_widget_blocked($id_base)) {
                    $ind = count($widgets_to_save);
                    $widgets_to_save[$ind] = 'widget-'. $ind .'_'. $id_str;
                }
            }
            $sidebars_to_save[$key] = $widgets_to_save;
        }
        
        return $sidebars_to_save;
    }
    // end of get_active_widgets_blocked_for_current_role()
    
    
    /**
     * Process situation, when user with restricted role updates sidebar, which has active widgets, to which role does not have access.
     * We should add those blocked active widgets back to the $POST['sidebars'] in order do not lose them after update
     * 
     */
    public function ajax_widgets_order() {
        
        if (!$this->is_restriction_aplicable()) {
            return;
        }                
        
        $sidebars_to_save = $this->get_active_widgets_blocked_for_current_role();                       
        $sidebars_from_post = $this->get_sidebars_from_post();
        foreach($sidebars_from_post as $key=>$widgets_list) {                
            foreach($widgets_list as $id_str) {
                $id_base = $this->get_id_base_from_str($id_str);
                if (!in_array($id_str, $sidebars_to_save[$key])) {
                    $ind = count($sidebars_to_save[$key]);
                    $sidebars_to_save[$key][$ind] = 'widget-'. $ind .'_'. $id_str;
                }
            }
            $_POST['sidebars'][$key] = implode(',', $sidebars_to_save[$key]);
        }            
        
        wp_ajax_widgets_order();
    }
    // end of ajax_widgets_order()
                        
}
// end of URE_Widgets_Access class
