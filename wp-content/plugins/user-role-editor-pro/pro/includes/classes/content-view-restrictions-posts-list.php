<?php
/**
 * Exclude prohibited posts/pages and other post types from listings
 * 
 */
class URE_Content_View_Restrictions_Posts_List {

    private $lib = null;
    private $prohibited_posts = null;
    private $post_type = null;
    
    
    public function __construct() {
        
        $this->lib = URE_Lib_Pro::get_instance();
        
        add_action('pre_get_posts', array($this, 'hide_prohibited_posts'));
        add_filter('get_previous_post_where', array($this, 'update_adjacent_post_where'), 10, 5);
        add_filter('get_next_post_where', array($this, 'update_adjacent_post_where'), 10, 5);
        add_filter('get_pages', array($this, 'hide_prohibited_pages'));
        if (class_exists('EM_Events')) {    // Events Manager plugin ( https://wordpress.org/plugins/events-manager )
            add_filter('em_events_output_events', array($this, 'hide_prohibited_events'), 10, 2);
        }        
        
    }
    // end of __construct()


    /**
     * Converts comma separated list of roles to the array with trimmed roles ID inside
     * 
     * @param string $roles_str
     * @return array
     */
    private function extract_roles_from_string($roles_str) {
        $roles = explode(',', $roles_str);
        foreach($roles as $key=>$role) {
            $roles[$key] = trim($role);            
        }
        
        return $roles;
    }
    // end of extract_roles_from_string()
    
    
    private function do_not_restrict_editors($post) {
        if (!is_a( $post, 'WP_Post' )) {
            $post = get_post($post);
            if (empty($post)) {
                return false;
            }
        }
        
        $restrict_even_if_can_edit = apply_filters('ure_restrict_content_view_for_authors_and_editors', false);
        // no restrictions for users who may edit this post/page
        if ($this->lib->can_edit($post) && !$restrict_even_if_can_edit) {
            return true;
        }
        
        return false;
    }
    // end of do_not_restrict_editors()
    

    private function do_not_hide_at_post_level($post) {

        if (!is_a( $post, 'WP_Post' )) {
            $post = get_post($post);
            if (empty($post)) {
                return false;
            }
        }
        
        $ure_content_for_roles = get_post_meta($post->ID, URE_Content_View_Restrictions::content_for_roles, true);
        if (empty($ure_content_for_roles)) {
            return true;
        }

        $roles = $this->extract_roles_from_string($ure_content_for_roles);
        if (count($roles) == 0) {
            return true;
        }

        if ($this->do_not_restrict_editors($post)) {
            return true;
        }

        $ure_prohibit_allow_flag = get_post_meta($post->ID, URE_Content_View_Restrictions::prohibit_allow_flag, true);
        $post_access_error_action = get_post_meta($post->ID, URE_Content_View_Restrictions::post_access_error_action, true);

        // Checks $post_access_error_action==2 to prohibit access in case $post_access_error_action do not set at all yet 
        // and has empty value.
        $result3 = $post_access_error_action == 2 ? true : false;
        if (!is_user_logged_in()) {
            $result = $result3;    // for prohibited access
            return $result;
        } elseif ($ure_prohibit_allow_flag == 1) {
            $result0 = true;
            $result1 = $result3;    // for prohibited access
        } else {
            $result0 = $result3;
            $result1 = true;     // for allowed access
        }

        foreach ($roles as $role) {
            if (current_user_can($role)) {
                return $result1;
            }
        }

        return $result0;
    }

    // end of do_not_hide_at_post_level()


    private function do_not_hide_at_role_level($post) {
        global $current_user;

        $blocked = URE_Content_View_Restrictions_Controller::load_access_data_for_user($current_user);
        if (empty($blocked['data'])) {
            return true;
        }

        if (!is_a( $post, 'WP_Post' )) {
            $post = get_post($post);
            if (empty($post)) {
                return false;
            }
        }
        
        if ($this->do_not_restrict_editors($post)) {
            return true;
        }
        
        if (URE_Content_View_Restrictions::is_object_restricted_for_role($post->ID, $blocked, 'posts')) {
            $result = $blocked['access_error_action'] == 2 ? true : false;
            return $result;
        }

        if (URE_Content_View_Restrictions::is_term_restricted_for_role($post->ID, $blocked)) {
            $result = $blocked['access_error_action'] == 2 ? true : false;
            return $result;
        }

        return true;
    }

    // end of do_not_hide_at_role_level()


    public function hide_prohibited_pages($pages) {
        
        if (is_admin()) {   // execute for front-end only
            return $pages;
        }
        
        if (count($pages)==0) {
            return $pages;
        }

        if ($this->lib->is_super_admin()) {
            return $pages;
        }
        
        $pages1 = array();
        foreach($pages as $page) {
            if ($this->do_not_hide_at_post_level($page) &&
                $this->do_not_hide_at_role_level($page)) {
                $pages1[] = $page;
            }
        }
                
        return $pages1;
    }
    // end of hide_prohibited_pages()

    
    /*
     * Filter events from the Events Manager plugin
     * https://wordpress.org/plugins/events-manager/ 
     */
    public function hide_prohibited_events($events) {
        if (count($events)==0) {
            return $events;
        }
        
        $events1 = array();
        foreach($events as $event) {
            $post = get_post($event->post_id);
            if ($this->do_not_hide_at_post_level($post) &&
                $this->do_not_hide_at_role_level($post)) {
                $events1[] = $event;
            }
        }
        
        return $events1;
    }
    // end of hide_prohibited_events()

    
    private function get_post_level_data_from_db() {
        global $wpdb;
        
        $query = 'select post_id, meta_key, meta_value from '. $wpdb->postmeta; 
        if (isset($this->query_vars['post_type']) && $this->query_vars['post_type']!='any' && $this->query_vars['post_type']!='') {
            $post_type = $this->query_vars['post_type'];
            $query .= " join {$wpdb->posts} on {$wpdb->posts}.ID={$wpdb->postmeta}.post_id"; 
        } else {
            $post_type = false;
        }
        $query .= " where {$wpdb->postmeta}.meta_key='ure_content_for_roles' OR {$wpdb->postmeta}.meta_key='ure_prohibit_allow_flag' OR".
                  " {$wpdb->postmeta}.meta_key='ure_post_access_error_action'".
                  " order by {$wpdb->postmeta}.post_id";
        if (!empty($post_type)) {
            $query .= " AND {$wpdb->posts}.post_type='{$post_type}'";
        }
        
        $data = $wpdb->get_results($query);
        
        return $data;
    }
    // end of get_post_level_data_from_db()
    
    
    private function load_post_level_data() {
        
        $records = $this->get_post_level_data_from_db();
        if (empty($records)) {
            return false;
        }
        $data = array();
        foreach($records as $record) {
            if (!isset($data[$record->post_id])) {
                $data[$record->post_id] = array();
            }
            $data[$record->post_id][$record->meta_key] = $record->meta_value;
        }
        
        return $data;
    }
    // end of load_post_level_data()
    

    /**
     * Loop through the roles for 'prohibited' flag and add post ID to the prohibited list if user has prohibited role
     * @param array $roles
     * @param array $prohibited
     * @param int $post_id
     */
    private function check_roles_for_prohibited($roles, $post_id) {        
        global $current_user;
        
        if (count($roles)==0) {
            return;
        }
        
        $logged_in = is_user_logged_in();        
        if ($logged_in) {
            foreach($roles as $role) {
                if ($this->lib->user_can_role($current_user, $role)) {
                    $this->prohibited_posts[$current_user->ID][] = $post_id;
                    break;
                }
            }
        } else {
            foreach($roles as $role) {
                if ($role=='no_role') {
                    $this->prohibited_posts[$current_user->ID][] = $post_id;
                    break;
                }
            }    
        }
        
    }
    // end of check_roles_for_prohibited()
    
    
    /**
     * Loop through the roles for 'allowed' flag and add post ID to the phohibited list if user does not have allowed role
     * @param array $roles
     * @param array $prohibited
     * @param int $post_id
     */    
    private function check_roles_for_allowed($roles, $post_id) {        
        global $current_user;
        
        if (count($roles)==0) {
            return;
        }
        
        $logged_in = is_user_logged_in();        
        $allowed = false;
        if ($logged_in) {
            foreach($roles as $role) {
                if ($this->lib->user_can_role($current_user, $role)) {
                    $allowed = true;
                    break;
                }
            }
        } else {
            foreach($roles as $role) {
                if ($role=='no_role') {
                    $allowed = true;
                    break;
                }
            }
        }
        if (!$allowed) {
            $this->prohibited_posts[$current_user->ID][] = $post_id;
        }
        
    }
    // end of check_roles_for_allowed()
            
    
    /**
     * Get the list of posts prohibited for current user at the post level
     * 
     */
    private function get_post_level_restrictions() {
    
        $data = $this->load_post_level_data();
        if (empty($data)) {
            return false;
        }
        
        foreach($data as $post_id => $restriction) {
            if (empty($restriction[URE_Content_View_Restrictions::content_for_roles])) { 
                continue;
            }
            if (isset($restriction[URE_Content_View_Restrictions::post_access_error_action]) && 
                $restriction[URE_Content_View_Restrictions::post_access_error_action]==2) { // show access error message
                continue;
            }            
            if ($this->do_not_restrict_editors($post_id)) {
                continue;
            }
            
            $roles = $this->extract_roles_from_string($restriction[URE_Content_View_Restrictions::content_for_roles]);
            if ($restriction[URE_Content_View_Restrictions::prohibit_allow_flag]==1) {   // Prohibited
                $this->check_roles_for_prohibited($roles, $post_id);
            } else {
                $this->check_roles_for_allowed($roles, $post_id);                
            }
        }
        
        return true;
    }
    // end of get_post_level_restrictions()
    
    
    /**
     * Return full list of posts ID except of ID included into $posts0
     * @param array $posts0
     */
    private function reverse_posts_list($posts0) {
        global $wpdb;
        
        if (count($posts0)==0) {    // nothing to reverse - prohibit nothing
            return array();
        }
        
        $do_not_select = implode(',', $posts0);
        $query = "select ID from {$wpdb->posts} where post_status='publish' and ID NOT IN ({$do_not_select})";
        $posts = $wpdb->get_col($query);
        if (!is_array($posts)) {
            $posts = array();
        }

        return $posts;
    }
    // end of reverse_posts_list()
    

    /**
     * Get the list of posts prohibited for current user at the roles level
     * 
     */    
    private function get_role_level_restrictions() {
        global $current_user;
        
        if ($current_user->ID===0) {
            return;
        }
        
        $blocked = URE_Content_View_Restrictions_Controller::load_access_data_for_user($current_user);
        if (empty($blocked)) {
            return;
        }
        
        $posts0 = array();
        if (isset($blocked['data']['posts']) && count($blocked['data']['posts'])>0) {
            $posts0 = $blocked['data']['posts'];
        }
        if (isset($blocked['data']['terms']) && count($blocked['data']['terms'])>0) {
            $posts1 = $this->lib->get_posts_by_terms($blocked['data']['terms']);
            if (count($posts1)) {
                $posts0 = array_merge($posts0, $posts1);
            }
        }
        
        if ($blocked['access_model']==2) {  // Block not selected
            $posts = $this->reverse_posts_list($posts0);                        
        } else {
            $posts = $posts0;
        }
        
        $posts1 = array();
        foreach($posts as $post_id) {
            if ($this->do_not_restrict_editors($post_id)) {
                continue;
            }
            $posts1[] = $post_id;
        }
        
        if (count($posts1)) {
            if (!isset($this->prohibited_posts[$current_user->ID]) || !is_array($this->prohibited_posts[$current_user->ID])) {
                $this->prohibited_posts[$current_user->ID] = array();
            }
            $this->prohibited_posts[$current_user->ID] = array_merge($this->prohibited_posts[$current_user->ID], $posts1);
        }
    }
    // end of get_role_level_restrictons()
    
    
    private function get_current_user_prohibited_posts() {
        global $current_user;
        
        $this->prohibited_posts = get_transient('ure_posts_view_access_prohibited_posts');
        if (!is_array($this->prohibited_posts) || !isset($this->prohibited_posts[$current_user->ID])) {            
            $this->prohibited_posts = array($current_user->ID => array());
            $this->get_post_level_restrictions();
            $this->get_role_level_restrictions();
            set_transient('ure_posts_view_access_prohibited_posts', $this->prohibited_posts, 30);
        }
        
        return $this->prohibited_posts[$current_user->ID];
    }
    // end of get_current_user_prohibited_posts()
        
    
    public function hide_prohibited_posts($wp_query) {
                
        if (is_admin()) {   // execute for front-end only
            return;
        }        
        if ($this->lib->is_super_admin()) { // no limits for super admin
            return;
        }
        
        if (isset($wp_query->query_vars['post_type'])) {
            $this->post_type = $wp_query->query_vars['post_type'];
        } else {
            $this->post_type = '';
        }
        $prohibited_posts = $this->get_current_user_prohibited_posts();
        
        if (count($prohibited_posts)>0) {
            $wp_query->set('post__not_in', $prohibited_posts);
        }
                        
    }
    // end of hide_prohibited_posts()

    
    public function update_adjacent_post_where($where, $in_same_term, $excluded_terms, $taxonomy=null, $post=null) {
        if (is_admin()) {   // execute for front-end only
            return $where;
        }        
        if ($this->lib->is_super_admin()) { // no limits for super admin
            return $where;
        }

        if (empty($post)) {
            $post = get_post();
        }
        $this->post_type = $post->post_type;
        $prohibited_posts = $this->get_current_user_prohibited_posts();
        if (count($prohibited_posts)>0) {
            $posts = implode(',', $prohibited_posts);
            $where .= ' AND p.ID not IN ('. $posts .')';
        }
        
        return $where;
    }
    // end of update_adjacent_post_where()
    

    /**
     * Separate restriction for the wlbdash custom post type as it does not use standard WordPress query for posts
     */
    public function wlb_dashboard_restrict() {
        
        global $wp_meta_boxes;
        foreach($wp_meta_boxes['dashboard'] as $widgets) {
            foreach($widgets['core'] as $key=>$widget) {
                if (strpos($key, 'wlbdash_')!==false) {
                    $data = explode('_', $key);
                    $post_id = (int) $data[1];
                    $post = get_post($post_id);
                    if ($this->do_not_hide_at_post_level($post) &&
                        $this->do_not_hide_at_role_level($post)) {
                        continue;
                    }
                    remove_meta_box($widget['id'], 'dashboard', 'normal');
                }
            }
        }

    }
    // end of wlb_dashboard_restrict    
    
}
// end of URE_Content_View_Restrictions_Posts_List