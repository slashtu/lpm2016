<?php

/**
 * @package Content Aware Sidebars
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2016 by Joachim Jensen
 */

if ( !defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    die;
}

// Create a helper function for easy SDK access.
function cas_fs()
{
    global  $cas_fs ;
    
    if ( !isset( $cas_fs ) ) {
        // Include Freemius SDK.
        require_once dirname( __FILE__ ) . '/lib/freemius/start.php';
        $cas_fs = fs_dynamic_init( array(
            'id'             => '259',
            'slug'           => 'content-aware-sidebars',
            'public_key'     => 'pk_75513325effa77f024565ef74c9d6',
            'is_premium'     => false,
            'has_addons'     => false,
            'has_paid_plans' => true,
            'menu'           => array(
            'slug'    => 'edit.php?post_type=sidebar',
            'support' => false,
        ),
            'is_live'        => true,
        ) );
    }
    
    return $cas_fs;
}

// Init Freemius.
cas_fs();
global  $cas_fs ;
function cas_fs_connect_message_update(
    $message,
    $user_first_name,
    $plugin_title,
    $user_login,
    $site_link,
    $freemius_link
)
{
    return sprintf(
        __fs( 'hey-x' ) . '<br>' . __( 'Please help us improve %2$s by securely sharing some usage data with %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'content-aware-sidebars' ),
        $user_first_name,
        '<b>' . $plugin_title . '</b>',
        '<b>' . $user_login . '</b>',
        $site_link,
        $freemius_link
    );
}

function cas_fs_connect_message(
    $message,
    $user_first_name,
    $plugin_title,
    $user_login,
    $site_link,
    $freemius_link
)
{
    return sprintf(
        __fs( 'hey-x' ) . '<br>' . __( 'To get the most out of this plugin, %2$s needs to connect your user, %3$s at %4$s, to %5$s.', 'content-aware-sidebars' ),
        $user_first_name,
        '<b>' . $plugin_title . '</b>',
        '<b>' . $user_login . '</b>',
        $site_link,
        $freemius_link
    );
}

$cas_fs->add_filter(
    'connect_message_on_update',
    'cas_fs_connect_message_update',
    10,
    6
);
$cas_fs->add_filter(
    'connect_message',
    'cas_fs_connect_message',
    10,
    6
);
function cas_fs_upgrade()
{
    global  $cas_fs ;
    $flag = 'cas_pro';
    $upgrade_flag = (int) $cas_fs->can_use_premium_code();
    if ( $upgrade_flag != get_option( $flag, 0 ) ) {
        if ( !$upgrade_flag ) {
            //downgrade
            update_option( $flag, $upgrade_flag );
        }
    }
}

add_action( 'admin_footer', 'cas_fs_upgrade', 99 );
function cas_fs_uninstall()
{
    require plugin_dir_path( __FILE__ ) . '/cas_uninstall.php';
}


if ( $cas_fs->is_on() ) {
    $cas_fs->add_action( 'after_uninstall', 'cas_fs_uninstall' );
} elseif ( is_admin() ) {
    //after_uninstall is only run for new users
    register_uninstall_hook( plugin_dir_path( __FILE__ ) . 'content-aware-sidebars.php', 'cas_fs_uninstall' );
}