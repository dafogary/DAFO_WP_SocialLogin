<?php
/**
 * Uninstall handler
 */

// Exit if accessed directly or not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete options
delete_option('dafo_social_login_settings');

// Delete user meta
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'dafo_social_login_%'");

// Drop custom table
$table_name = $wpdb->prefix . 'dafo_social_login_connections';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Clear any cached data
wp_cache_flush();
