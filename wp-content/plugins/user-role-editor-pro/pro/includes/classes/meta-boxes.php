<?php

/*
 * User Role Editor WordPress plugin
 * Class URE_Metaboxes - support stuff for Meta boxes Access add-on
 * Author: Vladimir Garagulya
 * Author email: support@role-editor.com
 * Author URI: https://www.role-editor.com
 * License: GPL v2+ 
 */

class URE_Meta_Boxes extends URE_Access_UI_Controller { 
    
    const META_BOXES_LIST_COPY_KEY = 'ure_meta_boxes_list_copy';
    
    private static $meta_boxes_list = null;
    
    
    public function __construct($lib) {
        
        parent::__construct($lib);
        $this->access_data_key = 'ure_meta_boxes_access_data';
        
        add_action('do_meta_boxes', array($this, 'update_meta_boxes_list_copy'), 1);
        if (class_exists('acf_input')) {    
            // catch Advanced Custom Fields plugin meta boxes, as it adds them using 'admin_head' and other actions
            // core/input.php, forms/post.php
            add_action('acf/input/admin_head', array($this, 'update_meta_boxes_list_copy'), 90);
            add_action('acf/input/admin_head', array($this, 'remove_blocked_metaboxes'), 99);
        }
        add_action('add_meta_boxes', array($this, 'remove_blocked_metaboxes'), 99);
        add_action('wp_dashboard_setup', array($this, 'remove_blocked_metaboxes'), 99);
        add_action('wp_user_dashboard_setup', array($this, 'remove_blocked_metaboxes'), 99);
    }
    // end of __construct()

    
    public function update_meta_boxes_list_copy() {
        global $wp_meta_boxes;
        
        if (empty($wp_meta_boxes)) { 
            return;
        }
         
        self::$meta_boxes_list = get_option(self::META_BOXES_LIST_COPY_KEY, array());
        foreach($wp_meta_boxes as $screen=>$contexts) {            
            foreach($contexts as $context=>$priorities) {
                foreach($priorities as $priority=>$meta_boxes) {
                    foreach($meta_boxes as $meta_box) {
                        if (empty($meta_box) || !isset($meta_box['id']) || !isset($meta_box['title'])) {
                            continue;
                        }
                        $mb = new StdClass();
                        $mb->id = $meta_box['id'];
                        $mb->title = $meta_box['title'];
                        $mb->screen = $screen;
                        $mb->context = $context;
                        $mb->priority = $priority;
                        $mb_hash = md5($mb->id . $mb->screen . $mb->context);
                        self::$meta_boxes_list[$mb_hash] = $mb;
                    }
                }
            }                        
        }
        
        update_option(self::META_BOXES_LIST_COPY_KEY, self::$meta_boxes_list);                
        
    }
    // end of update_meta_boxes_list_copy()
    
    
    public function remove_blocked_metaboxes() {
        
        if (!$this->is_restriction_aplicable()) {
            return;
        }
        
        $this->get_blocked_items();
        if (empty($this->blocked)) {
            return;
        }
        
        $all_meta_boxes = $this->get_all_meta_boxes();
        foreach($this->blocked as $mb_hash) {
            $blocked_mb = $all_meta_boxes[$mb_hash];
            remove_meta_box($blocked_mb->id, $blocked_mb->screen, $blocked_mb->context);
        }
        
    }
    // end of remove_blocked_metaboxes()
    
    
    public function get_all_meta_boxes() {
        
        if (self::$meta_boxes_list==null) {
            self::$meta_boxes_list = get_option(self::META_BOXES_LIST_COPY_KEY, array());
        }
        
        return self::$meta_boxes_list;
    }
    // end of get_all_meta_boxes()
    
    
    public function asort_screen($a, $b) {
        
        if ($a['mb']->screen!==$b['mb']->screen) {
            $result = $a['mb']->screen>$b['mb']->screen;
        } else {
            $result = $a['mb']->title>$b['mb']->title;
        }
        
        return $result;
    }
    // end of asort()
    
    
    private function sort_meta_boxes($meta_boxes) {

        $tmp = array();
        foreach ($meta_boxes as $key=>$meta_box) { 
            if (!isset($meta_box->id)) {
                continue;
            }
            $tmp[] = array('hash'=>$key, 'mb'=>$meta_box);
        }
        usort($tmp, array($this, 'asort_screen'));

        $sorted = array();
        foreach ($tmp as $rec) {
            $sorted [$rec['hash']] = $rec['mb'];
        }

        return $sorted;
    }
    // end of sort_meta_boxes()


    public function get_html($user=null) {
        
        $allowed_roles = $this->get_allowed_roles($user);
        if (empty($user)) {
            $ure_object_type = 'role';
            $ure_object_name = $allowed_roles[0];
            $blocked_items = $this->load_access_data_for_role($ure_object_name);
        } else {
            $ure_object_type = 'user';
            $ure_object_name = $user->user_login;
            $blocked_items = $this->load_access_data_for_user($ure_object_name);
        }
        $meta_boxes_list = $this->sort_meta_boxes($this->get_all_meta_boxes());
        if (empty($meta_boxes_list)) {
            $answer = array(
                'result'=>'success', 
                'message'=>'Widgets permissions for '+ $ure_object_name, 
                'html'=>'<span style="color: red;">'. 
                    esc_html__('Please open post, page and (custom post type) editor page to initilize the list of available meta_boxes', 'user-role-editor') .
                    '</span>');
            return $answer;
        }
                
        $readonly_mode = (!$this->lib->multisite && $allowed_roles[0]=='administrator') || 
                         ($this->lib->multisite && !is_super_admin());         
        ob_start();
?>
<form name="ure_meta_boxes_access_form" id="ure_meta_boxes_access_form" method="POST"
      action="<?php echo URE_WP_ADMIN_URL . URE_PARENT.'?page=users-'.URE_PLUGIN_FILE;?>" >
<table id="ure_meta_boxes_access_table">
    <th style="color:red;"><?php esc_html_e('Block', 'user-role-editor');?></th>
    <th class="ure-cell"><?php esc_html_e('Title', 'user-role-editor');?></th>        
    <th class="ure-cell"><?php esc_html_e('Id','user-role-editor');?></th>
<?php
        $current_screen = '-';
        foreach($meta_boxes_list as $key=>$item) {            
            if ($item->screen!==$current_screen) {
                $current_screen = $item->screen;
?>
    <tr>
        <th colspan="3"><?php echo ucfirst($current_screen);?></th>
    </tr>
<?php    
            }
?>
    <tr>
        <td>   
<?php 
    if (!$readonly_mode) {
        $checked = in_array($key, $blocked_items) ? 'checked' : '';
?>
            <input type="checkbox" name="<?php echo $key;?>" id="<?php echo $key;?>" <?php echo $checked;?> />
<?php
    }
?>
        </td>
        <td class="ure-cell"><?php echo $item->title;?></td>
        <td class="ure-cell"><?php echo $item->id;?></td>        
    </tr>        
<?php
        }   // foreach($meta_boxes_list)
?>
</table> 
    <input type="hidden" name="action" id="action" value="ure_update_meta_boxes_access" />
    <input type="hidden" name="ure_object_type" id="ure_object_type" value="<?php echo $ure_object_type;?>" />
    <input type="hidden" name="ure_object_name" id="ure_object_name" value="<?php echo $ure_object_name;?>" />
<?php
    if ($ure_object_type=='role') {
?>
    <input type="hidden" name="user_role" id="ure_role" value="<?php echo $ure_object_name;?>" />
<?php
    }
?>
    <?php wp_nonce_field('user-role-editor', 'ure_nonce'); ?>
</form>    
<?php    
        $html = ob_get_contents();
        ob_end_clean();        
                        
        return array('result'=>'success', 'message'=>'Widgets permissions for '+ $ure_object_name, 'html'=>$html);        
    }
    // end of get_html()
}
// end of URE_Metaboxes class