<?php

/*
 * User Role Editor WordPress plugin
 * Force post types to use own create_posts capability (including built-in attachments post type)
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v2+ 
 */

class URE_Create_Posts_Cap {
    
    private $lib = null;
    
    public function __construct($lib) {        
        
        $this->lib = $lib;
        add_action('init', array($this, 'activate'), 12, 2);    // execute after URE_Post_Own_Caps
    }
    // end of __construct()
    
    
    /** 
     * Prevent 'insufficient permissions' message from 'edit.php?post_type=custom' link 
     * in case 'create_posts' capability is active, but user does not have 'edit_posts' capability
     */    
    public function allow_edit_post_type($menu_order) {
        global $_wp_menu_nopriv;
        global $_wp_submenu_nopriv;
        global $pagenow;
                
        if ($pagenow!=='edit.php') {
            return $menu_order;
        }
        
        $post_type_name = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_STRING);
        if (empty($post_type_name)) {
            return $menu_order;
        }
        
        if (current_user_can('edit_posts')) {
            return $menu_order;
        }
        
        $post_type = get_post_type_object($post_type_name);
        if (current_user_can($post_type->cap->edit_posts)) {
            unset($_wp_menu_nopriv[$pagenow]);
            unset($_wp_submenu_nopriv[$pagenow][$pagenow]);
        }
        
        return $menu_order;
    }
    // end of allow_edit_post_type()
    
    
    public static function build_cap_name($post_type) {
        $cap = $post_type->cap->create_posts;
        if ($post_type->cap->create_posts == $post_type->cap->edit_posts) {
            if (strpos($post_type->cap->edit_posts, 'edit_') !== false) {
                $cap = str_replace('edit_', 'create_', $post_type->cap->edit_posts);
            } else {
                $cap = 'create_' . $post_type->capability_type . 's';
            }
        }
        
        return $cap;
    }
    // end of build_cap_name()
    

    /**
     * Replace create_posts capability from 'edit_posts' to 'create_posts' for stadnard WP posts,
     * from 'edit_pages' to 'create_pages' for standard WP pages, and do the same for all public custom post types
     * 
     */
    public function activate() {
        global $wp_post_types;
                        
        $post_types = get_post_types(array(), 'objects');
        $_post_types = $this->lib->_get_post_types();
        foreach($post_types as $post_type) {
            if (!in_array($post_type->name, $_post_types)) {
                continue;
            }
            $wp_post_types[$post_type->name]->cap->create_posts = self::build_cap_name($post_type);
        }  
        // use the nearest filter available to the needed injection point
        add_filter('custom_menu_order', array($this, 'allow_edit_post_type'));
                
    }
    // end of activate()        
    
}
// end of URE_Create_Posts_Cap class