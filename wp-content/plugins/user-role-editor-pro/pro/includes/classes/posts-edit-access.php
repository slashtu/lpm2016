<?php
/*
 * Class: Access restrict to posts/pages on per site - per user - per post/page basis 
 * Project: User Role Editor Pro WordPress plugin
 * Author: Vladimir Garagulya
 * email: support@role-editor.com
 * 
 */

class URE_Posts_Edit_Access {
    
    private $lib = null;
    private $user = null;   // URE_Posts_Edit_Access_User class instance        
    private $screen;

    
    public function __construct() {
    
        $this->lib = Ure_Lib_Pro::get_instance();        
        $this->user = new URE_Posts_Edit_Access_User($this);
        
        add_action('admin_init', array($this, 'set_final_hooks'));
        add_filter('map_meta_cap', array($this, 'block_edit_post'), 10, 4);
                
    }
    // end of __construct()                
            
    
    public function set_final_hooks() {
                
        $wc_bookings_active = URE_Plugin_Presence::is_active('woocommerce-bookings');   // Woocommerce Bookings plugin 
        if ($wc_bookings_active) {
            URE_WC_Bookings::separate_user_transients();            
        }
        
        if (!$this->user->is_restriction_applicable()) {
            return;
        }
        
        // apply restrictions to the post query
        add_action('pre_get_posts', array($this, 'restrict_posts_list' ), 55);

        // apply restrictions to the pages list from stuff respecting get_pages filter
        add_filter('get_pages', array($this, 'restrict_pages_list'));

        // set filters for the correct view count
        //$post_types = get_post_types(array('public'=>true, 'show_ui'=>true));
        $post_types = $this->lib->_get_post_types();
        foreach($post_types as $post_type ){
            add_filter('views_edit-'.$post_type, array($this, 'get_views'));
        }
        // add_filter('wp_count_posts', array($this, 'recount_wp_posts'));  // @TODO

        // restrict categories available for selection at the post editor
        add_filter('list_terms_exclusions', array($this, 'exclude_terms'));        
        // Auto assign to a new create post the 1st from allowed terms
        add_filter('wp_insert_post', array($this, 'auto_assign_term'), 10, 3);
        
        if ($wc_bookings_active) {  
            new URE_WC_Bookings($this->user);
        }
        
    }
    // end of set_final_hooks()
    
        
    public function recount_wp_posts($views) {
        
        
        return $views;
    }
    // end of recount_wp_posts()
            
    
    public function block_edit_post($caps, $cap='', $user_id=0, $args=array()) {
        
        global $current_user;
        
        if (empty($current_user->ID)) {
            return $caps;
        }
        
        if (count($args)>0) {
            $post_id = $args[0];
        } else {
            $post_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT);
        }
        if (empty($post_id)) {
            return $caps;
        }
        
        if ($this->lib->is_super_admin()) {
            return $caps;
        }
                
        $posts_list = $this->user->get_posts_list();                                   
        if (count($posts_list)==0) {        
            return $caps;
        }
        
        $custom_caps = $this->lib->get_edit_custom_post_type_caps();
        if (!in_array($cap, $custom_caps)) {
            return $caps;
        }                
        
        $post = get_post($post_id);        
        if (empty($post)) {
            return $caps;
        }
        
        remove_filter('map_meta_cap', array($this, 'block_edit_post'), 10, 4);  // do not allow endless recursion
        $restrict_it = apply_filters('ure_restrict_edit_post_type', $post->post_type);
        add_filter('map_meta_cap', array($this, 'block_edit_post'), 10, 4);     // restore filter
        if (empty($restrict_it)) {            
            return $caps;
        }
        
        if ($post->post_type=='revision') { // Check access to the related post, not to the revision
            $post_id = $post->post_parent;
        }
                        
        $do_not_allow = in_array($post_id, $posts_list);    // not edit these
        $restriction_type = $this->user->get_restriction_type();
        if ($restriction_type==1) {
            $do_not_allow = !$do_not_allow;   // not edit others
        }
        if ($do_not_allow) {
            $caps[] = 'do_not_allow';
        }                    
        
        return $caps;
    }
    // end of block_edit_post()
                               
    
    private function update_post_query($query) {
        
        $restriction_type = $this->user->get_restriction_type();
        $posts_list = $this->user->get_posts_list();
        if ($restriction_type==1) {   // Allow
            if (count($posts_list)==0) {
                $query->set('p', -1);   // return empty list
            } else {
                $query->set('post__in', $posts_list);
            }
        } elseif ($restriction_type==2) {    // Prohibit
            if (count($posts_list)>0) {
                $query->set('post__not_in', $posts_list);
            }
        }
    }
    // end of update_post_query()
    
             
    private function should_apply_restrictions_to_wp_page() {
    
        global $pagenow;
        
        if (!($pagenow == 'edit.php' || $pagenow == 'upload.php' || 
            ($pagenow=='admin-ajax.php' && !empty($_POST['action']) && $_POST['action']=='query-attachments'))) {
            if (!function_exists('cms_tpv_get_options')) {   // if  "CMS Tree Page View" plugin is not active
                return false;
            } elseif ($pagenow!=='index.php') { //  add Dashboard page for "CMS Tree Page View" plugin widget
                    return false;
            }            
        }
        
        return true;
        
    }
    // end of should_apply_restrictions_to_wp_page()
    
    
    public function restrict_posts_list($query) {                

        if (!$this->should_apply_restrictions_to_wp_page()) {
            return;
        }                        
        
        // do not limit user with Administrator role or the user for whome posts/pages edit restrictions were not set
        if (!$this->user->is_restriction_applicable()) {
            return;
        }

        $suppressing_filters = $query->get('suppress_filters'); // Filter suppression on?

        if ($suppressing_filters) {
            return;
        }                   
        
        if (!empty($query->query['post_type'])) {
            $restrict_it = apply_filters('ure_restrict_edit_post_type', $query->query['post_type']);
            if (empty($restrict_it)) {
                return;
            }         
        }
        
        if ($query->query['post_type']=='attachment') { 
            $show_full_list = apply_filters('ure_attachments_show_full_list', false);
            if ($show_full_list) { // show full list of attachments
                return;
            }
            $restriction_type = $this->user->get_restriction_type();
            $attachments_list = $this->user->get_attachments_list();
            if ($restriction_type==1) {   // Allow
                if (count($attachments_list)==0) {
                    $attachments_list[] = -1;
                }
                $query->set('post__in', $attachments_list);
            } else {    // Prohibit
                $query->set('post__not_in', $attachments_list);
            }            
        } else {
            $this->update_post_query($query);
        }
                       
    }
    // end of restrict_posts_list()

            
    public function restrict_pages_list($pages) {
                
        if (!$this->should_apply_restrictions_to_wp_page()) {
            return $pages;
        }                        
        
        // do not limit user with Administrator role
        if (!$this->user->is_restriction_applicable()) {
            return $pages;
        }
        
        $restrict_it = apply_filters('ure_restrict_edit_post_type', 'page');
        if (empty($restrict_it)) {
            return;
        }
        
        $posts_list = $this->user->get_posts_list();
        if (count($posts_list)==0) {
            return $pages;
        } 
        
        $restriction_type = $this->user->get_restriction_type();
        
        $pages1 = array();
        foreach($pages as $page) {
            if ($restriction_type==1) { // Allow: not edit others
                if (in_array($page->ID, $posts_list)) {    // not edit others
                    $pages1[] = $page;
                    
                }
            } else {    // Prohibit: Not edit these
                if (!in_array($page->ID, $posts_list)) {    // not edit these
                    $pages1[] = $page;                    
                }                
            }
        }
        
        return $pages1;
    }
    // end of restrict pages_list()
        

    /**
     * Initally was taken from Admin for Authors plugin by Marcus Sykes (http://msyk.es)
     * Modified by Vladimir Garagulya (user-role-editor.com)
     * 
     */
    protected function count_posts($type = 'post', $perm = '') {
        global $wpdb;

        $user = wp_get_current_user();

        $cache_key = $type . '_' . $user->ID;

        $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s";

        if ('readable' == $perm && is_user_logged_in()) {
            if ($this->user->is_restricted()) {
                $posts_list = $this->user->get_posts_list();            
                if (count($posts_list)>0) {
                    $restriction_type = $this->user->get_restriction_type();
                    $posts_list_str = implode(',', $posts_list);
                    if ($restriction_type==1) {
                        $query .= " AND ID in ($posts_list_str)";
                    } else {
                        $query .= " AND ID not in ($posts_list_str)";
                    }
                } else {
                    $query .= " AND ID=-1";
                }
            }
            $post_type_object = get_post_type_object($type);
            if (!empty($post_type_object) && !current_user_can($post_type_object->cap->read_private_posts)) {
                $cache_key .= '_' . $perm . '_' . $user->ID;
                $query .= " AND (post_status != 'private' OR ( post_author = '$user->ID' AND post_status = 'private' ))";
            }
        }
        $query .= ' GROUP BY post_status';

        $count = wp_cache_get($cache_key, 'counts');
        if (false !== $count)
            return $count;

        $count = $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

        $stats = array();
        foreach (get_post_stati() as $state)
            $stats[$state] = 0;

        foreach ((array) $count as $row)
            $stats[$row['post_status']] = $row['num_posts'];

        $stats = (object) $stats;
        wp_cache_set($cache_key, $stats, 'counts');

        return $stats;
    }
    // end of count_posts()


    /**
     * Modification to this WP function was done by Marcus Sykes (http://msyk.es)
     * His comments follow untouched:
     * Almost-exact copy of WP_Posts_List_Table::get_views(), but makes subtle changes for $this references and calls internal Admin_For_Authors::wp_count_posts() function instead
	    * Changes highlighted with comments starting //EDIT 
	    */
    public function get_views() {
        global $wpdb, $locked_post_status, $avail_post_stati;

        $this->screen = get_current_screen(); //EDIT - get $screen for use on $this->screen
        $post_type = $this->screen->post_type;
        $post_type_object = get_post_type_object( $post_type );
        
        if (!empty($locked_post_status))
            return array();

        $status_links = array();
        $num_posts = $this->count_posts($post_type, 'readable');
        $class = '';
        $allposts = '';

        $current_user_id = get_current_user_id();
        $user_posts_count = 0;
        if ( !current_user_can( $post_type_object->cap->edit_others_posts ) ) {            
            $exclude_states = get_post_stati( array( 'show_in_admin_all_list' => false ) );
            $query = "SELECT COUNT( 1 ) FROM $wpdb->posts
                        WHERE post_type = %s AND post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' ) AND 
                              post_author = %d";
            if ($this->user->is_restricted()) {
                $posts_list = $this->user->get_posts_list();            
                if (count($posts_list)>0) {
                    $posts_list_str = implode(',', $posts_list);
                    $query .= ' AND ID IN ('. $posts_list_str .')';
                } else {
                    $query .= ' AND ID=1';
                }
            }
            $user_posts_count = $wpdb->get_var($wpdb->prepare($query, $post_type, $current_user_id));
        
            if ($user_posts_count) {
                if (isset($_GET['author']) && ( $_GET['author'] == $current_user_id )) {
                    $class = ' class="current"';
                }
                $status_links['mine'] = "<a href='edit.php?post_type=$post_type&author=$current_user_id'$class>" . 
                                        esc_html__('Mine', 'user-role-editor') . 
                                        ' <span class="count">('. $user_posts_count .')</span></a>';
                $allposts = '&all_posts=1';
            }
        }

        $total_posts = array_sum((array) $num_posts);

        // Subtract post types that are not included in the admin all list.
        foreach (get_post_stati(array('show_in_admin_all_list' => false)) as $state)
            $total_posts -= $num_posts->$state;

        $class = empty($class) && empty($_REQUEST['post_status']) && empty($_REQUEST['show_sticky']) ? ' class="current"' : '';
        $status_links['all'] = "<a href='edit.php?post_type=$post_type{$allposts}'$class>" .  
                    esc_html__('All', 'user-role-editor') . ' <span class="count">('. $total_posts .')</span></a>';

        foreach (get_post_stati(array('show_in_admin_status_list' => true), 'objects') as $status) {
            $class = '';

            $status_name = $status->name;

            if (!is_array($avail_post_stati) || !in_array($status_name, $avail_post_stati))
                continue;

            if (empty($num_posts->$status_name))
                continue;

            if (isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status'])
                $class = ' class="current"';

            $status_links[$status_name] = "<a href='edit.php?post_status=$status_name&amp;post_type=$post_type'$class>" . sprintf(translate_nooped_plural($status->label_count, $num_posts->$status_name), number_format_i18n($num_posts->$status_name)) . '</a>';
        }

        //EDIT - START this whole if statement gets sticky posts stat, copied from WP_Posts_List_Table::_construct() but there's maybe a better way for this
        global $wpdb;
        if ('post' == $post_type && $sticky_posts = get_option('sticky_posts')) {
            $sticky_posts = implode(', ', array_map('absint', (array) $sticky_posts));
            $sticky_posts_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT( 1 ) FROM $wpdb->posts WHERE post_type = %s AND post_status != 'trash' AND ID IN ($sticky_posts)", $post_type));
        }
        //EDIT - END

        if (!empty($sticky_posts_count)) {
            $class = !empty($_REQUEST['show_sticky']) ? ' class="current"' : '';

            $sticky_link = array('sticky' => "<a href='edit.php?post_type=$post_type&amp;show_sticky=1'$class>" . 
                esc_html__('Sticky', 'user-role-editor') . ' <span class="count">('. $sticky_posts_count .')</span></a>');                
            // Sticky comes after Publish, or if not listed, after All.
            $split = 1 + array_search(( isset($status_links['publish']) ? 'publish' : 'all'), array_keys($status_links));
            $status_links = array_merge(array_slice($status_links, 0, $split), $sticky_link, array_slice($status_links, $split));
        }

        return $status_links;
    }
    // end of get_views()

    
    public function exclude_terms($exclusions) {
        
        global $pagenow;
        
        if (!in_array($pagenow, array('edit.php', 'post.php', 'post-new.php'))) {
            return $exclusions;
        }
        
        $terms_list_str = $this->user->get_post_categories_list();
        if (empty($terms_list_str)) {
            return $exclusions;
        }
        
        $restriction_type = $this->user->get_restriction_type();
        if ($restriction_type == 1) {   // allow
            // exclude all except included to the list
            remove_filter('list_terms_exclusions', array($this, 'exclude_terms'));  // delete our filter in order to avoid recursion when we call get_all_category_ids() function
            
            $taxonomies = array_keys(get_taxonomies(array(), 'names')); // get array of registered taxonomies names
            $all_terms = get_terms($taxonomies, array('fields'=>'ids', 'hide_empty'=>0)); // take full categories list from WordPress
            add_filter('list_terms_exclusions', array($this, 'exclude_terms'));  // restore our filter back            
            $terms_list = explode(',', str_replace(' ','',$terms_list_str));
            $terms_to_exclude = array_diff($all_terms, $terms_list); // delete terms ID, to which we allow access, from the full terms list
            $terms_to_exclude_str = implode(',', $terms_to_exclude); 
        } else {    // prohibit
            $terms_to_exclude_str = $terms_list_str;
        }

        $exclusions .= " AND (t.term_id not IN ($terms_to_exclude_str))";   // build WHERE expression for SQL-select command
        
        return $exclusions;
    }
    // end of exclude_terms()
    
    
    /**
     * Assign to a new created post the 1st available taxonomy from allowed taxonomies list
     * 
     * @global string $pagenow
     * @global WPDB $wpdb
     * @param int $post_id
     * @param WP_POST $post
     * @param bool $update
     * @return void
     */
    public function auto_assign_term($post_id, $post, $update) {
        global $pagenow, $wpdb;
        
        if ($pagenow !=='post-new.php') {   // for new added post only
            return;
        }
        
        $terms_list_str = $this->user->get_post_categories_list();
        if (empty($terms_list_str)) {
            return;
        }
        
        $restriction_type = $this->user->get_restriction_type();
        if ($restriction_type!=1) {   // allow
            return;
        }
        $terms_list = explode(',', str_replace(' ','',$terms_list_str));
        
        $registered_taxonomies = get_object_taxonomies($post->post_type, 'names');
        if (empty($registered_taxonomies)) {
            return;
        }
        
        foreach($terms_list as $term_id) {        
            $query = $wpdb->prepare('select taxonomy from '. $wpdb->term_taxonomy .' where term_id=%d', $term_id);
            $taxonomy = $wpdb->get_var($query);
            if (empty($taxonomy)) {
                continue;
            }
            if (in_array($taxonomy, $registered_taxonomies)) {
                // use as a default the 1st taxonomy from the allowed list, available for this post type
                wp_set_post_terms( $post_id, $term_id, $taxonomy);
                break;
            }
        }
        
    }
    // end of auto_assign_term()
    
}
// end of URE_Posts_Edit_Access
