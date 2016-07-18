<?php
/**
 * Plugin Presence Checker
 *
 * Checks if some plugin is active
 */
class URE_Plugin_Presence {

    private static $active_plugins = null;

    private static $plugin_ids = array(
        'woocommerce'=>'woocommerce/woocommerce.php',
        'woocommerce-bookings'=>'woocommerce-bookings/woocommmerce-bookings.php',
        'visual-composer'=>'js_composer/js_composer.php'
        );

    /**
     * Returns true if plugin $plugin_id is active
     * @param string $plugin_id - plugin ID, for example 'woocommerce/woocommerce.php'
     * @return type
     */
    public static function is_active($plugin_id) {

        if (!isset(self::$plugin_ids[$plugin_id])) {
            syslog(LOG_NOTICE, 'URE_Plugin_Presence::is_active(): Plugin ID is unknown: '. $plugin_id);
            return false;
        }
        
        if (self::$active_plugins===null) {
            self::$active_plugins = array();
        }        
        $file_id = self::$plugin_ids[$plugin_id];
        if (isset(self::$active_plugins[$file_id])) {
            return self::$active_plugins[$file_id];
        }
        
        $full_list = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $list1 = get_site_option('active_sitewide_plugins', array());
            if (!empty($list1)) {
                $full_list = array_merge($full_list, array_keys($list1));
            }
        }
        $result = in_array($file_id, $full_list);
        self::$active_plugins[$file_id] = $result;

        return $result;
    }
    // end of is_active()

}
// end of URE_Plugin_Presence
