<?php
/**
 * Support for bbPress user roles and capabilities
 * 
 * Project: User Role Editor WordPress plugin
 * Author: Vladimir Garagulya
 * Author email: vladimir@shinephp.com
 * Author URI: http://shinephp.com
 * 
 **/

class URE_bbPress_Pro extends URE_bbPress {

    
    protected function __construct(Ure_Lib_Pro $lib) {
        
        parent::__construct($lib);
                
        add_action('plugins_loaded', array($this, 'do_not_reload_roles'), 9);        
        add_filter('bbp_get_caps_for_role', array($this, 'get_caps_for_role'), 10, 2);
    }
    // end of __construct()
    
    /**
     * Exclude roles created by bbPress
     * 
     * @global array $wp_roles
     * @return array
     */
    public function get_roles() {
        
        global $wp_roles;                  
        
        return $wp_roles->roles;
    }
    // end of get_roles()
    
    
    /**
     * Returns true if role does not include any capability, false in other case
     * @param array $caps - list of capabilities: cap=>1 or cap=>0
     * @return boolean
     */
    private function is_role_without_caps($caps) {
        if (empty($caps)) {
            return true;
        }
        
        if (!is_array($caps) || count($caps)==0) {
            return true;
        }
        
        $nocaps = true;
        foreach($caps as $turned_on) {
            if ($turned_on) {
                $nocaps = false;
                break;
            }
        }
        
        return $nocaps;        
    }
    // end of is_role_without_caps()
    
    
    public function get_caps_for_role($caps, $role_id) {
    
        global $wp_roles;
            
        $bbp_roles = array(
            bbp_get_keymaster_role(),
            bbp_get_moderator_role(),
            bbp_get_participant_role(),
            bbp_get_spectator_role(),
            bbp_get_blocked_role()
            );
        if (!in_array($role_id, $bbp_roles)) {
            return $caps;
        }
        
        // to exclude endless recursion
        remove_filter('bbp_get_caps_for_role', array($this, 'get_caps_for_role'), 10);
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        // restore it back
        add_filter('bbp_get_caps_for_role', array($this, 'get_caps_for_role'), 10, 2);
        
        if (!isset($wp_roles->roles[$role_id]) ||
            $this->is_role_without_caps($wp_roles->roles[$role_id]['capabilities'])) {
            return $caps;
        }
        
        $caps = $wp_roles->roles[$role_id]['capabilities'];
        
        return $caps;
    }
    // end of get_caps_for_role()
    
    
    public function do_not_reload_roles() {
        remove_action('bbp_loaded', 'bbp_filter_user_roles_option',  16);
        remove_action('bbp_deactivation', 'bbp_remove_caps');
        register_uninstall_hook('bbpress/bbpress.php', 'bbp_remove_caps');
    }
    // end of do_not_reload_roles()
    
}
// end of URE_bbPress_Pro class