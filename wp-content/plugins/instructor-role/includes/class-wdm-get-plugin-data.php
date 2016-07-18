<?php

namespace wdmGetPluginDataNS;

class WdmGetPluginData
{

    public static $responseData;

    public static function getDataFromDb($wdm_plugin_data)
    {

        if (null !== self::$responseData) {

            return self::$responseData;
        }

        // $author_name = $wdm_plugin_data['author_name'];
        $plugin_name = $wdm_plugin_data[ 'plugin_name' ];
        // $plugin_short_name = $wdm_plugin_data['plugin_short_name'];
        $plugin_slug = $wdm_plugin_data[ 'plugin_slug' ];
        //$plugin_version = $wdm_plugin_data['plugin_version'];
        $store_url   = $wdm_plugin_data[ 'store_url' ];

        //$get_trans = get_transient('wdm_' . $plugin_slug . '_license_trans');
        $get_trans = get_transient('wdm_' . $plugin_slug . '_license_trans');
        if (! $get_trans) {
            $license_key = trim(get_option('edd_' . $plugin_slug . '_license_key'));

            if ($license_key) {
                $api_params = array(
                    'edd_action'         => 'check_license',
                    'license'            => $license_key,
                    'item_name'          => urlencode($plugin_name),
                    'current_version'    => $wdm_plugin_data[ 'plugin_version' ]
                );

                $response = wp_remote_get(add_query_arg($api_params, $store_url), array(
                    'timeout'    => 15, 'sslverify'  => false, 'blocking'    => true ));

                if (is_wp_error($response)) {
                    return false;
                }

                $license_data = json_decode(wp_remote_retrieve_body($response));

                $valid_response_code = array( '200', '301' );

                $current_response_code = wp_remote_retrieve_response_code($response);

                if ($license_data == null || ! in_array($current_response_code, $valid_response_code)) {
                    //if server does not respond, read current license information
                    $license_status = get_option('edd_' . $plugin_slug . '_license_status', '');
                    if (empty($license_data)) {
                        set_transient('wdm_' . $plugin_slug . '_license_trans', 'server_did_not_respond', 60 * 60 * 24);
                    }
                } else {
                    $license_status = $license_data->license;
                }

                if (empty($license_status)) {
                    return;
                }

                $active_site = WdmGetPluginData::getSiteList($plugin_slug);

                if (isset($license_data->license) && ! empty($license_data->license)) {
                    update_option('edd_' . $plugin_slug . '_license_status', $license_status);
                }

                self::setResponseData($license_status, $active_site, $plugin_slug, true);
                return self::$responseData;
            }
        } else {
            $license_status  = get_option('edd_' . $plugin_slug . '_license_status');
            $active_site     = WdmGetPluginData::getSiteList($plugin_slug);

            self::setResponseData($license_status, $active_site, $plugin_slug);
            return self::$responseData;
        }
    }

    public static function setResponseData($license_status, $active_site, $plugin_slug, $set_transient = false)
    {

        if ($license_status == 'valid') {
            self::$responseData = 'available';
        } elseif ($license_status == 'expired' && ( ! empty($active_site) || $active_site != "")) {
            self::$responseData = 'unavailable';
        } elseif ($license_status == 'expired') {
            self::$responseData = 'available';
        } else {
            $license_status      = 'invalid';
            self::$responseData  = 'unavailable';
        }

        if ($set_transient) {
            if ($license_status == 'valid') {
                $time = 60 * 60 * 24 * 7;
            } else {
                $time = 60 * 60 * 24;
            }
            set_transient('wdm_' . $plugin_slug . '_license_trans', $license_status, $time);
        }
    }

    /**
     * This function is used to get list of sites where license key is already acvtivated.
     *
     * @param type $plugin_slug current plugin's slug
     * @return string  list of site
     *
     * @author Foram Rambhiya
     *
     */
    public static function getSiteList($plugin_slug)
    {
        
        $sites       = get_option('wdm_' . $plugin_slug . '_license_key_sites');
        $max         = get_option('wdm_' . $plugin_slug . '_license_max_site');
        $cur_site    = get_site_url();
        $cur_site    = preg_replace('#^https?://#', '', $cur_site);

        $site_count  = 0;
        $active_site = "";

        if (! empty($sites) || $sites != "") {
            foreach ($sites as $key) {
                foreach ($key as $value) {
                    $value = rtrim($value, "/");

                    if (strcasecmp($value, $cur_site) != 0) {
                        $active_site.= "<li>" . $value . "</li>";
                        $site_count ++;
                    }
                }
            }
        }

        //echo $active_site; exit;
        if ($site_count >= $max) {
            return $active_site;
        } else {
            return "";
        }
    }
}
