<?php
/**
 * Uninstall Script
 * 
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Option to keep or delete data
// Change to false if you want to keep data after uninstall
$delete_data = true;

if ($delete_data) {
    // Delete plugin options
    delete_option('dafo_social_login_settings');
    
    // Delete plugin table
    $table_name = $wpdb->prefix . 'dafo_social_connections';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    // Delete user meta (if any were added)
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'dafo_social_%'");
    
    // Clear any cached data
    wp_cache_flush();
}
