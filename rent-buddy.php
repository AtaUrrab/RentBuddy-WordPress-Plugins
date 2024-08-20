<?php
/*
Plugin Name: Rent Buddy Settings
Description: A plugin to import products from Rent Buddy API.
Version: 1.0
Author: Sapphire Technologies
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the necessary files
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/import-products.php';
require_once plugin_dir_path(__FILE__) . 'includes/pricefactor.php';

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || class_exists( 'WooCommerce' ) ) {
    // Include your custom feature file here
    //include_once 'includes/custom-features.php';
    include_once 'includes/checkout-features.php';
}



// Activation hook
register_activation_hook(__FILE__, 'wpi_activate_plugin');
function wpi_activate_plugin() {
    global $wpdb;
   
   $table_name = $wpdb->prefix . 'wpivv_akey';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        api_key varchar(255) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        PRIMARY KEY  (id)
    ) $charset_collate;";


    $table_name = $wpdb->prefix . 'wpivv_PPF';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        site_name varchar(255) NOT NULL,
        price_factor varchar(255) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY site_name_rent_days (site_name, rent_days)
    ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

// Deactivation hook
register_deactivation_hook(__FILE__, 'wpi_deactivate_plugin');
function wpi_deactivate_plugin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpi_api_keys';
    //$wpdb->query("DROP TABLE IF EXISTS $table_name");
}


// Enqueue the JavaScript file in your plugin
add_action('wp_enqueue_scripts', 'enqueue_plugin_scripts');

function enqueue_plugin_scripts() {
    if (is_checkout()) {
        wp_enqueue_script('plugin-checkout-js', plugins_url('/js/plugin-checkout.js', __FILE__), array('jquery'), null, true);

        // Pass the AJAX URL to the script
        wp_localize_script('plugin-checkout-js', 'ajax_params', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
}