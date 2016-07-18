<?php

/*
 * User Role Editor WordPress plugin
 * Content view access management controller
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v2+ 
 */

class URE_Content_View_Restrictions_Controller {

    const ACCESS_DATA_KEY = 'ure_posts_view_access_data';
    
    
    /**
     * Load access data for role
     * @param string $role_id
     * @return array
     */
    public static function load_access_data_for_role($role_id) {
        
        $access_data = get_option(self::ACCESS_DATA_KEY);
        if (is_array($access_data) && array_key_exists($role_id, $access_data)) {
            $result =  $access_data[$role_id];
            if (!isset($result['access_model'])) {
                $result['data'] = $result;
                $result['access_model'] = 1;
                $result['access_error_action'] = 1;
            }
        } else {
            $result = array(
                'access_model'=>1,
                'access_error_action'=>1,
                'data'=>array());
        }
        
        return $result;
    }
    // end of load_access_data_for_role()
    
    
    private static function init_blocked_data($default=0) {
        $blocked = array(
                'access_model'=>$default, 
                'access_error_action'=>$default, 
                'data'=>array());
        
        return $blocked;
    }
    // end of init_blocked_data()
    
    private static function data_merge($target, $source, $object_id) {
        
        if (!isset($source[$object_id])) {
            return $target;
        }
        
        if (!isset($target[$object_id])) {
            $target[$object_id] = $source[$object_id];
        } else {
            $target[$object_id] = array_merge($target[$object_id], $source[$object_id]);
        }
        
        return $target;
    }
    // end of data_merge()
    
    
    private static function merge_blocked_with_roles_data($user, $blocked) {
        
        if (!is_array($blocked)) {
            $blocked = self::init_blocked_data(0);
        }
        if (!is_array($user->roles) || count($user->roles)==0) {
            return $blocked;
        }
        
        $access_data = get_option(self::ACCESS_DATA_KEY);
        if (empty($access_data)) {
            $access_data = array();
        }
        foreach ($user->roles as $role) {
            if (isset($access_data[$role])) {
                if (!isset($access_data[$role]['access_model'])) { // for backward compatibility
                    $access_model = 1;   // Use default (block selected) access model
                    $data = $access_data[$role];
                } else {
                    $access_model = $access_data[$role]['access_model'];
                    $data = $access_data[$role]['data'];
                }
                if (!isset($access_data[$role]['access_error_action'])) {
                    $access_error_action = 1;
                } else {
                    $access_error_action = $access_data[$role]['access_error_action'];
                }
                if (empty($blocked['access_model'])) {  
                    $blocked['access_model'] = $access_model;    // take the 1st found role's access model as the main one                    
                }
                if (empty($blocked['access_error_action'])) {  
                    $blocked['access_error_action'] = $access_error_action;    // take the 1st found role's access error action as the main one                    
                }
                // take into account data with the same access model only as the 1st one found
                if ($access_model==$blocked['access_model']) {                    
                    $blocked['data'] = self::data_merge($blocked['data'], $data, 'posts');
                    $blocked['data'] = self::data_merge($blocked['data'], $data, 'terms');
                }
            }
        }
        
        return $blocked;
    }
    // end of merge_blocked_with_roles_data()
            
    
    public static function load_access_data_for_user($user) {        
        $lib = URE_Lib_Pro::get_instance();
        $user = $lib->get_user($user);
        if (empty($user)) {
            $blocked = self::init_blocked_data(1);
            return $blocked;
        }    
        
        $blocked = get_user_meta($user->ID, self::ACCESS_DATA_KEY, true);                                                      
        $blocked = self::merge_blocked_with_roles_data($user, $blocked);        

        if (empty($blocked['access_model'])) {
            $blocked['access_model'] = 1; // use default value
        }
        if (!isset($blocked['access_error_action']) || empty($blocked['access_error_action'])) {
            $blocked['access_error_action'] = 1; // use default value
        }        
        
        return $blocked;
    }
    // end of load_access_data_for_user()
    

    static private function get_access_data_from_post() {
        $lib = URE_Lib_Pro::get_instance();
        $keys_to_skip = array(
            'action', 
            'ure_nonce', 
            '_wp_http_referer', 
            'ure_object_type', 
            'ure_object_name', 
            'user_role', 
            'ure_access_model',
            'ure_posts_list');
        $access_model = $_POST['ure_access_model'];
        if ($access_model!=1 && $access_model!=2) { // got invalid value
            $access_model = 1;  // use default value
        }        
        $access_error_action = $_POST['ure_post_access_error_action'];
        if ($access_error_action!=1 && $access_error_action!=2) { // got invalid value
            $access_error_action = 1;  // use "return 404 HTTP error" as a default value
        }
        $access_data = array(
            'access_model'=>$access_model, 
            'access_error_action'=>$access_error_action,
            'data'=>array('terms'=>array(), 'posts'=>array()));
        foreach (array_keys($_POST) as $key) {
            if (in_array($key, $keys_to_skip)) {
                continue;
            }
            $value = filter_var($key, FILTER_SANITIZE_STRING);
            $values = explode('_', $value);
            $term_id = $values[1];
            if ($term_id>0) {
                $access_data['data']['terms'][] = $term_id;
            }
        }
        
        if (!empty($_POST['ure_posts_list'])) {
            $posts_list = explode(',', trim($_POST['ure_posts_list']));
            if (count($posts_list)>0) {                
                $access_data['data']['posts'] = $lib->filter_int_array($posts_list);
            }            
        }
        
        return $access_data;
    }
    // end of get_access_data_from_post()
    
    
    public static function save_access_data_for_role($role_id) {        
        $access_data = get_option(self::ACCESS_DATA_KEY);        
        if (!is_array($access_data)) {
            $access_data = array();
        }
        $data = self::get_access_data_from_post();
        if (count($data)>0) {
            $access_data[$role_id] = $data;
        } else {
            unset($access_data[$role_id]);
        }
        update_option(self::ACCESS_DATA_KEY, $access_data);
    }
    // end of save_access_data_for_role()
    
    
    public function save_access_data_for_user($user_login) {
        
        // TODO ...
    }
    // end of save_menu_access_data_for_role()    
    
}
// end of URE_Content_View_Restrictions_Controller class