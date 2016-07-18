<?php
/*
 * Access restriction to plugins Activation on per user basis
 * part of User Role Editor Pro plugin
 * Author: Vladimir Garagulya
 * email: vladimir@shinephp.com
 * 
 */

class URE_Plugins_Activation_Access {
    
    private $lib = null;
    private $user_meta_key = '';
    
    public function __construct(Ure_Lib $lib) {
    
        global $wpdb;
        
        $this->lib = $lib;
        $this->user_meta_key = $wpdb->prefix . 'ure_allow_plugins_activation';        
        
        add_action( 'edit_user_profile', array($this, 'edit_user_allowed_plugins_list'), 10, 2);
        add_action( 'profile_update', array($this, 'save_user_allowed_plugins_list'), 10);
        add_action( 'admin_head', array($this, 'prohibited_links_redirect'));        
        add_action('admin_init', array($this, 'set_final_hooks'));
        add_action( 'admin_enqueue_scripts', array($this, 'admin_load_js' ));
        add_action( 'admin_print_styles-user-edit.php', array($this, 'admin_css_action'));
    }
    // end of __construct()
    
    
    // checks if user can activate plugins
    protected function user_can_activate_plugins($user) {
        
        $result = $this->lib->user_has_capability($user, 'activate_plugins');        
        
        return $result;
    }
    // end of user_can_activate_plugins()
    
    
    public function set_final_hooks() {
        global $current_user;
        
        if (current_user_can('ure_plugins_activation_access')) {    // user is this add-on admin - no limits
            return;
        }
                
        if ( $this->user_can_activate_plugins($current_user) ) {
            $allowed_plugins_list = $this->get_allowed_plugins_list();
            if (count($allowed_plugins_list)>0) {
                add_filter('all_plugins', array(&$this, 'restrict_plugins_list' ));
            }
        }
        
    }
    // end of set_final_hooks()
    
    
    protected function get_allowed_plugins_names($allow_plugins) {

        $allowed_plugins_list = explode(',', $allow_plugins);
        $plugins = get_plugins();
        $allowed_plugins_names = '';
        foreach ($plugins as $key => $plugin) {
          if (in_array($key, $allowed_plugins_list)) {
            if (!empty($allowed_plugins_names)) {
                $allowed_plugins_names .= "\n";
            }
            $allowed_plugins_names = $allowed_plugins_names .$plugin['Name'];
          }
        }

        return $allowed_plugins_names;
    }
    // end of get_allowed_plugins_names()

    
    protected function user_profile_plugins_select() {
        
        $all_plugins = get_plugins();
        echo 'Open drop-down list and turn On/Off checkboxes:<br>'."\n";
        echo '<select multiple="multiple" id="ure_select_allowed_plugins" name="ure_select_allowed_plugins" style="width: 500px;" >'."\n";
        foreach($all_plugins as $plugin_key=>$plugin_data) {
            echo '<option value="'.$plugin_key.'" >'.$plugin_data['Name'].'</option>'."\n";
        }   // foreach()
        echo '</select><br>'."\n";
    }
    // end of user_profile_plugins_select()    
    
    
    public function edit_user_allowed_plugins_list($user) {

        global $current_user;

        $result = stripos($_SERVER['REQUEST_URI'], 'network/user-edit.php');
        if ($result !== false) {  // exit, this code just for single site user profile only, not for network admin center
            return;
        }
        
        if (!current_user_can('ure_plugins_activation_access')) { // current user can not edit available plugins list
            return;
        }
        
        if (user_can($user, 'ure_plugins_activation_access')) {  // edited user can edit available plugins list
            return;
        }
        
        // if edited user can not activate plugins, do not show allowed plugins input field
        if ( !$this->user_can_activate_plugins($user) ) {
            return;
        }
        
        $allow_plugins = get_user_meta($user->ID, $this->user_meta_key, true);
        $show_allowed_plugins = $this->get_allowed_plugins_names($allow_plugins);
?>        
        <h3><?php esc_html_e('Plugins available for activation/deactivation', 'user-role-editor'); ?></h3>
       <?php $this->user_profile_plugins_select();?>
       <textarea name="show_allowed_plugins" id="show_allowed_plugins" cols="80" rows="5" readonly="readonly" /><?php echo $show_allowed_plugins; ?></textarea>
       <input type="hidden" name="ure_allow_plugins" id="ure_allow_plugins" value="<?php echo $allow_plugins; ?>" />
       <input type="hidden" name="ure_user_id" id="ure_user_id" value="<?php echo $user->ID; ?>" />
        <div id="ure_allowed_plugins_dialog" style="display: none;">
            <div id="ure_allowed_plugins_dialog_content" style="padding:10px;">
            </div>
        </div>    
<?php        
    }
    // end of edit_user_allowed_plugins_list()

    
   /**
     * Load plugin javascript stuff
     * 
     * @param string $hook_suffix
     */
    public function admin_load_js($hook_suffix) {
                
        if ($hook_suffix === 'user-edit.php') {
            wp_enqueue_script('jquery-ui-dialog', false, array('jquery-ui-core', 'jquery-ui-button', 'jquery'));
            wp_register_script('ure-jquery-multiple-select', plugins_url('/js/jquery.multiple.select.js', URE_PLUGIN_FULL_PATH));
            wp_enqueue_script('ure-jquery-multiple-select');
            wp_register_script('ure-user-profile-plugins', plugins_url('/pro/js/ure-pro-user-profile-plugins.js', URE_PLUGIN_FULL_PATH));
            wp_enqueue_script('ure-user-profile-plugins');
            wp_localize_script('ure-user-profile-plugins', 'ure_data_plugins', array(
                'wp_nonce' => wp_create_nonce('user-role-editor')                
            ));
        }
    }
    // end of admin_load_js()
    
    
    public function admin_css_action() {        
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('ure-jquery-multiple-select', plugins_url('/css/multiple-select.css', URE_PLUGIN_FULL_PATH), array(), false, 'screen');
    }
    // end of admin_css_action()
                        
        
    /**
     *  Save additional allowed for activation/deactivation plugins list when user profile is updated, 
     *  as WordPress itself doesn't know about this data
     * 
     * @param int $user_id
     * @return void
     */
    public function save_user_allowed_plugins_list($user_id) {

        if (!current_user_can('edit_users', $user_id) || !current_user_can('ure_plugins_activation_access')) {
            return;
        }

        $plugings_list_str = '';
        // update plugins list access restriction: comma separated directoy names list
        if (isset($_POST['ure_allow_plugins'])) {
            $plugins_list = explode(',', $_POST['ure_allow_plugins']);
            if (count($plugins_list)>0) {
                $installed_plugins = get_plugins();
                $validated_list = array();
                foreach($plugins_list as $plugin) {
                    $plugin = trim($plugin);  
                    if (isset($installed_plugins[$plugin])) {
                        $validated_list[] = $plugin;
                    }
                }
                $plugings_list_str = implode(',', $validated_list);
            }            
        }
        update_user_meta($user_id, $this->user_meta_key, $plugings_list_str);
    }
    // end of save_allowed_plugins_list()    
    
    
    private function get_allowed_plugins_list($user_id=0) {
        
        global $current_user;
    
        if (empty($user_id)) {  //  return data for current user
            $user_id = $current_user->ID;
        }
        $data = trim(get_user_meta($user_id, $this->user_meta_key, true));
        if (empty($data)) {
            $allowed_plugins_list = array();
        } else {
            $allowed_plugins_list = explode(',', $data);
        }
            
        return $allowed_plugins_list;
    }
    // end of get_allowed_plugins_list()
    
        
    public function prohibited_links_redirect() {
        
        global $current_user;
        
        if (!$this->user_can_activate_plugins($current_user)) {        
            return;   
        }
            
        if ( stripos($_SERVER['REQUEST_URI'], 'wp-admin/plugins.php?action')===false ) {
            return;
        }    

        $allowed_plugins_list = $this->get_allowed_plugins_list($current_user);
        if (count($allowed_plugins_list)==0) {
            return;
        }
        // extract plugin id
        $args = wp_parse_args($_SERVER['REQUEST_URI'], array() );    
        if ( isset($args['plugin']) ) {            
            if ( !in_array($args['plugin'], $allowed_plugins_list) ) {    // access to this plugins is prohibited - redirect user back to the plugins list
                // its late to use wp_redirect() as WP sent some headers already, so use JavaScript for redirection
?>
        <script>
            document.location.href = '<?php echo get_option('siteurl') . '/wp-admin/plugins.php'; ?>';
        </script>
<?php                    
                die;
            }
        }
                                    
    }
    // end of prohibited_links_redirect()

                
  /** 
   * Filter out prohibeted plugins
   * @param type array $plugins plugins list
   * @return type array $plugins updated plugins list
   */
  public function restrict_plugins_list($plugins) {

    // if multi-site, then allow plugin activation for network superadmins and, if that's specially defined, - for single site administrators too    
    if (current_user_can('ure_plugins_activation_access')) {    
      return $plugins;
    }
    
    $allowed_plugins_list = $this->get_allowed_plugins_list();
    // exclude prohibited plugins from the list
    foreach (array_keys($plugins) as $key) {
      if (!in_array($key, $allowed_plugins_list)) {
        unset($plugins[$key]);
      }
    }

    return $plugins;
  }
  // end of restrict_plugins_list()
  
}
// end of URE_Plugins_Activation_Access
