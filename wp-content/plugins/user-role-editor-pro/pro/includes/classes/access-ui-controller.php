<?php
/*
 * User Role Editor WordPress plugin
 * Class URE_Access_UI_Controller - base class for the access user interface controllers of add-ons, called from User Role Editor page
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v2+ 
 */

class URE_Access_UI_Controller {

    protected $lib = null;
    protected $access_data_key = '';    //  should be initialized at the child constructor
    protected $blocked = null;
    
    
    public function __construct($lib) {
        
        $this->lib = $lib;
        
    }
    // end of __construct()
    
    
    /**
     * Load widgets access data for role
     * @param string $role_id
     * @return array
     */
    public function load_access_data_for_role($role_id) {
        
        $access_data = get_option($this->access_data_key);
        if (is_array($access_data) && array_key_exists($role_id, $access_data)) {
            $result =  $access_data[$role_id];
        } else {
            $result = array();
        }
        
        return $result;
    }
    // end of load_access_data_for_role()
    
    
    public function load_access_data_for_user($user) {
    
        if (is_object($user)) {
            $id = $user->ID;
        } else if (is_int($user)) {
            $id = $user;
            $user = get_user_by('id', $user);
        } else {
            $user = get_user_by('login', $user);
            $id = $user->ID;
        }
        
        $blocked = get_user_meta($user->ID, $this->access_data_key, true);
        if (!is_array($blocked)) {
            $blocked = array();
        }
        
        $access_data = get_option($this->access_data_key);
        if (empty($access_data)) {
            $access_data = array();
        }
        
        foreach ($user->roles as $role) {
            if (isset($access_data[$role])) {
                $blocked = array_merge($blocked, $access_data[$role]);
            }
        }
        
        $blocked = array_unique ($blocked);
        
        return $blocked;
    }
    // end of load_access_data_for_user()

    
    protected function get_access_data_from_post() {
        
        $keys_to_skip = array('action', 'ure_nonce', '_wp_http_referer', 'ure_object_type', 'ure_object_name', 'user_role');
        $access_data = array();
        foreach ($_POST as $key=>$value) {
            if (in_array($key, $keys_to_skip)) {
                continue;
            }
            $access_data[] = $key;
        }
        
        return $access_data;
    }
    // end of get_access_data_from_post()
        
    
    public function save_access_data_for_role($role_id) {
        $access_for_role = $this->get_access_data_from_post();
        $access_data = get_option($this->access_data_key);        
        if (!is_array($access_data)) {
            $access_data = array();
        }
        if (count($access_for_role)>0) {
            $access_data[$role_id] = $access_for_role;
        } else {
            unset($access_data[$role_id]);
        }
        update_option($this->access_data_key, $access_data);
    }
    // end of save_access_data_for_role()
    
    
    public function save_access_data_for_user($user_login) {
        //$access_for_user = $this->get_access_data_from_post();
        // TODO ...
    }
    // end of save_menu_access_data_for_role()   
                    
    
    protected function get_allowed_roles($user) {
        $allowed_roles = array();
        if (empty($user)) {   // request for Role Editor - work with currently selected role
            $current_role = filter_input(INPUT_POST, 'current_role', FILTER_SANITIZE_STRING);
            $allowed_roles[] = $current_role;
        } else {    // request from user capabilities editor - work with that user roles
            $allowed_roles = $user->roles;
        }
        
        return $allowed_roles;
    }
    // end of get_allowed_roles()
    
    
    public function is_restriction_aplicable() {
        if ($this->lib->multisite && is_super_admin()) {
            return false;
        }
        
        if (!$this->lib->multisite && current_user_can('administrator')) {
            return false;
        }

        return true;
    }
    // end of is_restriction_aplicable()
    
    
    public function get_blocked_items() {
        global $current_user;
        
        if ($this->blocked===null) {
            $this->blocked = $this->load_access_data_for_user($current_user);
        }
        
        return $this->blocked;
    }
    // end of get_blocked_items()
}
// end of URE_Access_UI_Controller class