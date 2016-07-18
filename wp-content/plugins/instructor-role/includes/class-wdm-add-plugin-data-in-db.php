<?php

namespace wdmAddPluginDataInDbNS;

if (! class_exists('WdmAddPluginDataInDB')) {

    class WdmAddPluginDataInDB
    {

        /**
         *
         * @var string Short Name for plugin.
         */
        private $plugin_short_name = '';

        /**
         *
         * @var string Slug to be used in url and functions name
         */
        private $plugin_slug = '';

        /**
         *
         * @var string stores the current plugin version
         */
        private $plugin_version = '';

        /**
         *
         * @var string Handles the plugin name
         */
        private $plugin_name = '';

        /**
         *
         * @var string  Stores the URL of store. Retrieves updates from
         *              this store
         */
        private $store_url = '';

        /**
         *
         * @var string  Name of the Author
         */
        private $author_name = '';

        public function __construct($plugin_data)
        {

            $this->author_name       = $plugin_data[ 'author_name' ];
            $this->plugin_name       = $plugin_data[ 'plugin_name' ];
            $this->plugin_short_name = $plugin_data[ 'plugin_short_name' ];
            $this->plugin_slug       = $plugin_data[ 'plugin_slug' ];
            $this->plugin_version    = $plugin_data[ 'plugin_version' ];
            $this->store_url         = $plugin_data[ 'store_url' ];

            add_action('admin_menu', array( $this, 'lMenu' ));
        }

        public function lMenu()
        {

            $this->addData();

            add_plugins_page("{$this->plugin_short_name} License", "{$this->plugin_short_name} License", 'manage_options', $this->plugin_slug . '-license', array(
                $this, 'lPage', ));
        }

        public function lPage()
        {

            include_once trailingslashit(dirname(dirname(__FILE__))) . 'templates/l-page-display.php';
        }


        public function statusUpdate($license_data)
        {
            $status = "";
            if ((empty($license_data->success)) && isset($license_data->error) && ($license_data->error == "expired")) {
                $status = 'expired';
            } elseif ($license_data->license == 'invalid' && isset($license_data->error) && $license_data->error == "revoked") {
                $status = 'disabled';
            } elseif ($license_data->license == 'invalid' && $license_data->activations_left == "0") {
                include_once(plugin_dir_path(__FILE__) . 'class-wdm-get-plugin-data.php');

                $active_site = \wdmGetPluginDataNS\WdmGetPluginData::getSiteList($this->plugin_slug);
                
                if (! empty($active_site) || $active_site != "") {
                    $status = "invalid";
                } else {
                    $status = 'valid';
                }
            } elseif ($license_data->license == 'failed') {
                $status = 'deactivated';
            } else {
                $status = $license_data->license;
            }
            
            update_option('edd_' . $this->plugin_slug . '_license_status', $status);
            return $status;
        }

        public function checkIfNoData($license_data, $current_response_code, $valid_response_code)
        {
            if ($license_data == null || ! in_array($current_response_code, $valid_response_code)) {
                $GLOBALS[ 'wdm_server_null_response' ] = true;
                set_transient('wdm_' . $this->plugin_slug . '_license_trans', 'server_did_not_respond', 60 * 60 * 24);
                return false;
            }
            return true;
        }

        public function activateLicense()
        {

            $license_key = trim($_POST[ 'edd_' . $this->plugin_slug . '_license_key' ]);

            if ($license_key) {
                update_option('edd_' . $this->plugin_slug . '_license_key', $license_key);
                $api_params = array(
                    'edd_action'         => 'activate_license',
                    'license'            => $license_key,
                    'item_name'          => urlencode($this->plugin_name),
                    'current_version'    => $this->plugin_version
                );

                $response = wp_remote_get(add_query_arg($api_params, $this->store_url), array(
                    'timeout'    => 15, 'sslverify'  => false, 'blocking'    => true ));

                if (is_wp_error($response)) {
                    return false;
                }

                $license_data = json_decode(wp_remote_retrieve_body($response));

                $valid_response_code = array( '200', '301' );

                $current_response_code = wp_remote_retrieve_response_code($response);

                $isDataAvailable = $this->checkIfNoData($license_data, $current_response_code, $valid_response_code);
                //cspPrintDebug($license_data); exit;
                if ($isDataAvailable == false) {
                    return;
                }

                $exp_time = 0;
                if (isset($license_data->expires)) {
                    $exp_time = strtotime($license_data->expires);
                }
                $cur_time = time();

                if (isset($license_data->expires) && ($license_data->expires !== false) && $exp_time <= $cur_time) {
                    $license_data->error = "expired";
                }

                if (isset($license_data->renew_link) && ( ! empty($license_data->renew_link) || $license_data->renew_link != "")) {

                    update_option('wdm_' . $this->plugin_slug . '_product_site', $license_data->renew_link);
                }
                
                $this->updateNumberOfSitesUsingLicense($license_data);

                $license_status = $this->statusUpdate($license_data);
                $this->setTransientOnActivation($license_status);
            }
        }

        public function updateNumberOfSitesUsingLicense($license_data)
        {
            
            if (isset($license_data->sites) && ( ! empty($license_data->sites) || $license_data->sites != "" )) {
                update_option('wdm_' . $this->plugin_slug . '_license_key_sites', $license_data->sites);
                update_option('wdm_' . $this->plugin_slug . '_license_max_site', $license_data->license_limit);
            } else {
                update_option('wdm_' . $this->plugin_slug . '_license_key_sites', '');
                update_option('wdm_' . $this->plugin_slug . '_license_max_site', '');
            }
        }

        public function setTransientOnActivation($license_status)
        {
            $trans_var = get_transient('wdm_' . $this->plugin_slug . '_license_trans');
            if (isset($trans_var)) {
                delete_transient('wdm_' . $this->plugin_slug . '_license_trans');
                if (! empty($license_status)) {
                    set_transient('wdm_' . $this->plugin_slug . '_license_trans', $license_status, 60 * 60 * 24);
                }
            }
        }

        public function deactivateLicense()
        {
            $wpep_license_key = trim(get_option('edd_' . $this->plugin_slug . '_license_key'));

            if ($wpep_license_key) {
                $api_params = array(
                    'edd_action'         => 'deactivate_license',
                    'license'            => $wpep_license_key,
                    'item_name'          => urlencode($this->plugin_name),
                    'current_version'    => $this->plugin_version
                );

                $response = wp_remote_get(add_query_arg($api_params, $this->store_url), array(
                    'timeout'    => 15, 'sslverify'  => false, 'blocking'    => true ));

                if (is_wp_error($response)) {
                    return false;
                }


                $license_data = json_decode(wp_remote_retrieve_body($response));

                $valid_response_code = array( '200', '301' );

                $current_response_code = wp_remote_retrieve_response_code($response);

                $isDataAvailable = $this->checkIfNoData($license_data, $current_response_code, $valid_response_code);

                if ($isDataAvailable == false) {
                    return;
                }

                if ($license_data->license == 'deactivated' || $license_data->license == 'failed') {
                    update_option('edd_' . $this->plugin_slug . '_license_status', 'deactivated');
                }
                //delete_transient( 'wdm_' . $this->plugin_slug . '_license_trans' );
                delete_transient('wdm_' . $this->plugin_slug . '_license_trans');

                set_transient('wdm_' . $this->plugin_slug . '_license_trans', $license_data->license, 0);
            }
        }

        public function addData()
        {
            if (isset($_POST[ 'edd_' . $this->plugin_slug . '_license_activate' ])) {
                if (! check_admin_referer('edd_' . $this->plugin_slug . '_nonce', 'edd_' . $this->plugin_slug . '_nonce')) {
                    return;
                }
                $this->activateLicense();
            } elseif (isset($_POST[ 'edd_' . $this->plugin_slug . '_license_deactivate' ])) {
                if (! check_admin_referer('edd_' . $this->plugin_slug . '_nonce', 'edd_' . $this->plugin_slug . '_nonce')) {
                    return;
                }
                $this->deactivateLicense();
            }
        }
    }

}
