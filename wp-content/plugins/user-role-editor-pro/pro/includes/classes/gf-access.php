<?php
/*
 * Gravity Forms Access Restrict on per site - per user - per form basis class
 * part of User Role Editor Pro plugin
 * Author: Vladimir Garagulya
 * email: vladimir@shinephp.com
 * 
 */

class URE_GF_Access {
    
    private $lib = null;
    private $user_meta_key = '';
    private $form_table_name = '';
    private $form_from_key = '';
    private $count_forms_query = '';
    private $allowed_forms_list = null;
    
    public function __construct(Ure_Lib $lib) {
    
        global $wpdb;
        
        $this->lib = $lib;
        $this->user_meta_key = $wpdb->prefix . 'ure_allow_gravity_forms';
        $this->form_table_name = GFFormsModel::get_form_table_name();
        $this->form_from_key = "FROM {$this->form_table_name}";
        // GF, v. 1.8.5: forms_model.php, line 223, function get_form_count()
        $this->count_forms_query = "
            SELECT
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_trash = 0) as total,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_active=1 AND is_trash = 0 ) as active,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_active=0 AND is_trash = 0 ) as inactive,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_trash=1) as trash
            ";
        add_action( 'edit_user_profile', array(&$this, 'edit_user_allowed_forms_list'), 10, 2 );     
        add_action( 'profile_update', array(&$this, 'save_user_allowed_forms_list'), 10 );
        add_action( 'admin_head', array(&$this, 'prohibited_links_redirect') );
        //add_action( 'admin_enqueue_scripts', array( &$this, 'load_js' ) );
        add_action('admin_init', array(&$this, 'set_final_hooks'));
        
    }
    // end of __construct()
    
    
    
    protected function add_gf_import_capability() {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        $admin_role = $wp_roles->get_role('administrator');
        if (!empty($admin_role) && !$admin_role->has_cap('gravityforms_import')) {        
            $wp_roles->use_db = true;   //  save changes to the database
            $admin_role->add_cap('gravityforms_import');
        }        
        
    }
    // end of add_gf_import_capability()
    
    
    public function set_final_hooks() {
        global $current_user;
        
        $this->add_gf_import_capability();
        
        if ( $this->lib->user_is_admin($current_user->ID) ) {
            return;
        }
        
        $min_cap = $this->lib->user_can_which($current_user, GFCommon::all_caps());
        if (!empty($min_cap)) {
            add_filter('query', array(&$this, 'restrict_form_list' ));
        }
                        
    }
    // end of set_final_hooks()
    
    
    public function edit_user_allowed_forms_list($user) {

        global $current_user;

        $result = stripos($_SERVER['REQUEST_URI'], 'network/user-edit.php');
        if ($result !== false) {  // exit, this code just for single site user profile only, not for network admin center
            return;
        }
        if (!$this->lib->user_is_admin($current_user->ID)) {
            return;
        }
        
        $min_cap = $this->lib->user_can_which($user, GFCommon::all_caps());
        if (empty($min_cap)) {
            return;
        }
        
        $allow_gravity_forms = get_user_meta($user->ID, $this->user_meta_key, true);
?>        
        <h3><?php _e('Gravity Forms Restrictions', 'user-role-editor'); ?></h3>
<table class="form-table">
        		<tr>
        			<th scope="row"><?php esc_html_e('Allow access to forms with ID (comma separated) ', 'user-role-editor'); ?></th>
        			<td>
               <input type="text" name="ure_allow_gravity_forms" id="ure_allow_gravity_forms" value="<?php echo $allow_gravity_forms; ?>" size="40" />
        			</td>
        		</tr>
        </table>		        
        
<?php        
    }
    // end of set_user_allowed_forms_list()

    
        // save additional user roles when user profile is updated, as WordPress itself doesn't know about them
    public function save_user_allowed_forms_list($user_id) {

        if (!current_user_can('edit_users', $user_id)) {
            return;
        }
        
        // update Gravity Forms access restriction: comma separated GF IDs list
        if (isset($_POST['ure_allow_gravity_forms'])) {
            $gf_list = explode(',', trim($_POST['ure_allow_gravity_forms']));
            if (count($gf_list)>0) {
                $gf_id_arr = array();
                foreach($gf_list as $gf_id) {
                    $gf_id = (int) $gf_id;  // save interger values only
                    if ($gf_id>0) {
                        $gf_id_arr[] = $gf_id;
                    }
                }
                $gf_list_str = implode(', ', $gf_id_arr);
            }            
        } else {
            $gf_list_str = '';
        }
        update_user_meta($user_id, $this->user_meta_key, $gf_list_str);
    }
    // end of save_allowed_forms_list()    
    
    
    private function get_allowed_forms() {
        
        global $current_user;
    
        if ($this->allowed_forms_list==null) {
            $this->allowed_forms_list = array();
            $allow_gravity_forms = get_user_meta($current_user->ID, $this->user_meta_key, true);
            if (!empty($allow_gravity_forms)) {                
                $this->allowed_forms_list = explode(',', $allow_gravity_forms);
                for ($i=0; $i<count($this->allowed_forms_list); $i++) {
                    $this->allowed_forms_list[$i] = trim($this->allowed_forms_list[$i]);
                }                
            }
        }
        
        $allowed_forms_list = apply_filters('ure_get_allowed_gf_forms', $this->allowed_forms_list);
        
        return $allowed_forms_list;
    }
    // end of get_allowed_forms()
    
    
    protected function check_import_link() {
        global $current_user;
        
        $link = 'admin.php?page=gf_export&view=import_form';
        if (stripos($_SERVER['REQUEST_URI'], $link)===false || 
            $this->lib->user_has_capability($current_user, 'administrator')) {
            return false;
        }
                
        $allowed_forms_list = $this->get_allowed_forms();
        if ($this->lib->user_has_capability($current_user, 'gravityforms_import') && count($allowed_forms_list)==0) {
            return false;
        }
?>
        <script>
            document.location.href = '<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=gf_export'; ?>';
        </script>
<?php                    
                die;
        
    }
    // end of check_import_link()
    
        
    public function prohibited_links_redirect() {
        
        global $current_user;
        
        $min_cap = $this->lib->user_can_which($current_user, GFCommon::all_caps());
        if ( empty($min_cap) ) {
            return;   
        }
    
        $this->check_import_link();
        
        $result = false;
        $links_to_block = array(
            'admin.php?page=gf_edit_forms&id=', 
            'admin.php?page=gf_edit_forms&view=settings', 
            'admin.php?page=gf_entries&id=',
            'admin.php?page=gf_entries&view=entries&id='
            );
        foreach ( $links_to_block as $link ) {
            $result = stripos($_SERVER['REQUEST_URI'], $link);
            if ($result !== false) {
                break;
            }
        }
        if ($result === false) {    // other URL, no need to block
            return;
        }    

        $id = 0;
        // extract form id
        $args = wp_parse_args($_SERVER['REQUEST_URI'], array() );    
        if ( isset($args['id']) ) {
            $id = (int) $args['id'];
        } elseif (isset($_POST['action_argument'])) {   // delete, duplicate
            $id = (int) $_POST['action_argument'];            
        } elseif (isset($_POST['form'])) {  // bulk actions
            $allowed_forms_list = $this->get_allowed_forms();                
            foreach($_POST['form'] as $form_id) {
                if (!in_array($form_id, $allowed_forms_list)) {
                    $id = $form_id;
                    break;
                }
            }
        }
        if (!isset($allowed_forms_list)) {
           $allowed_forms_list = $this->get_allowed_forms();                
        }
        if ($id>0) {
            if (count($allowed_forms_list)==0) {   // no limits
                return;
            }                
            if ( !in_array($id, $allowed_forms_list) ) {    // access to this form is prohibited - redirect user back to the forms list
                // its late to user wp_redirect() ad WP sent some headers already, so use this method for redirection
?>
        <script>
            document.location.href = '<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=gf_edit_forms'; ?>';
        </script>
<?php                    
                die;
            }
        }
                                    
    }
    // end of prohibited_links_redirect()

    
    protected function filter_form_list_gf_pages($query) {
        
        $allowed_forms = $this->get_allowed_forms();
        if (count($allowed_forms)>0) {
            $allowed_forms_str = implode(',', $allowed_forms);
            if (substr(trim($query), 0, 11)==='SELECT f.id') {                   
                $insert_where_str = "f.id in ($allowed_forms_str)"; 
                $orderby_pos = strpos($query, 'ORDER BY');
                $where_pos = strpos($query, 'WHERE');
                if ($where_pos===false) {
                  $insert_where_str = 'WHERE '.$insert_where_str;
                } else {
                    $insert_where_str = 'AND '.$insert_where_str;
                }
                if ($orderby_pos!==false) {
                    $query = substr($query, 0, $orderby_pos - 1) .' '. $insert_where_str .' '. substr($query, $orderby_pos);
                } else {
                    $query = $query .' '. $insert_where_str;
                }
            } else if ($query==$this->count_forms_query) {
                $query = "
            SELECT
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_trash = 0 AND id in ($allowed_forms_str)) as total,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_active=1 AND is_trash = 0 AND id in ($allowed_forms_str)) as active,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_active=0 AND is_trash = 0 AND id in ($allowed_forms_str)) as inactive,
            (SELECT count(0) FROM {$this->form_table_name} WHERE is_trash=1 AND id in ($allowed_forms_str)) as trash
            ";
            }
        }                        
        
        return $query;
        
    }
    // end of filter_form_list_gf_pages()
    
        
    protected function dashboard_widget_query_injection($query, $where_field, $inject_key) {
        
        $allowed_forms = $this->get_allowed_forms();
        if (count($allowed_forms)>0) {
            $allowed_forms_str = implode(',', $allowed_forms);
            $insert_where_str = ' AND '. $where_field. " in ($allowed_forms_str)"; 
            $groupby_pos = strpos($query, $inject_key.' BY');                
            $query = substr($query, 0, $groupby_pos - 1) .' '. $insert_where_str .' '. substr($query, $groupby_pos);
        }
        
        return $query;
    }
    // end of dashboard_widget_query_injection()
    
        
    public function restrict_form_list($query) {
        
        $page = GFForms::get_page();
        if ( in_array($page, array('form_list','form_editor','form_settings','entry_list', 'export_entry', 'export_form')) && 
             strpos($query, $this->form_from_key)!==false) {
            $query = $this->filter_form_list_gf_pages($query);
            return $query;
        }
        if (is_blog_admin()) {  // if not admin dashboard - nothing to change
            $uri = trim($_SERVER['REQUEST_URI']);
            $question_pos = strpos($uri, '?');
            if ($question_pos!==false) {
                $uri = substr($uri, 0, $question_pos);
            }
            $uri_len = strlen($uri);
            $compare1 = substr($uri, $uri_len - 19);
            if ($compare1!=='/wp-admin/index.php') {
                $compare2 = substr($uri, $uri_len - 10);
                if ($compare2!=='/wp-admin/') {
                    return $query;
                }            
            }
            // set filter for dashboard GF widget queries at forms_model.php, v. 1.8.3 get_form_summary(), line # 170
            if (substr(trim($query), 0, 17)==='SELECT l.form_id,') {
                $query = $this->dashboard_widget_query_injection($query, 'l.form_id', 'GROUP');
            } elseif (substr(trim($query), 0, 17)==='SELECT id, title,') {
                $query = $this->dashboard_widget_query_injection($query, 'id', 'ORDER');
            }        
        }
               
        return $query;
    }
    // restrict_form_list()
    
}
// end of URE_GF_Access
