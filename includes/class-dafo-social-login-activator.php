<?php
/**
 * Plugin activation handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Social_Login_Activator {
    
    public static function activate() {
        global $wpdb;
        
        // Create custom table for social login connections
        $table_name = $wpdb->prefix . 'dafo_social_login_connections';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            provider varchar(50) NOT NULL,
            provider_user_id varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_provider (user_id, provider),
            KEY provider_user_id (provider_user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        $default_options = array(
            'google_enabled' => '',
            'google_client_id' => '',
            'google_client_secret' => '',
            'facebook_enabled' => '',
            'facebook_app_id' => '',
            'facebook_app_secret' => '',
            'bluesky_enabled' => '',
            'bluesky_client_id' => '',
            'bluesky_client_secret' => '',
            'apple_enabled' => '',
            'apple_client_id' => '',
            'apple_team_id' => '',
            'apple_key_id' => '',
            'apple_private_key' => '',
            'linkedin_enabled' => '',
            'linkedin_client_id' => '',
            'linkedin_client_secret' => '',
            'yahoo_enabled' => '',
            'yahoo_client_id' => '',
            'yahoo_client_secret' => ''
        );
        
        add_option('dafo_social_login_settings', $default_options);
        
        // Add rewrite rules for callbacks
        add_rewrite_rule(
            '^dafo-social-login/callback/([^/]+)/?$',
            'index.php?dafo_social_login_callback=$matches[1]',
            'top'
        );
        
        flush_rewrite_rules();
    }
}
