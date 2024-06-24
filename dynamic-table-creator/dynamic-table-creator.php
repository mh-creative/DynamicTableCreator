<?php
/**
 * Plugin Name: Dynamic Table Creator
 * Description: A plugin to create and manage HTML tables dynamically from the admin area.
 * Version: 1.0
 * Author: Michael Hayes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the main class file
require_once plugin_dir_path(__FILE__) . 'includes/class-dynamic-table-creator.php';

// Activation hook
register_activation_hook(__FILE__, 'dynamic_table_creator_activate');
function dynamic_table_creator_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dynamic_tables';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        table_data longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Initialize the plugin
add_action('plugins_loaded', array('Dynamic_Table_Creator', 'init'));
?>
