<?php
/**
 * Plugin Name: DAFO Social Login
 * Plugin URI: https://github.com/dafogary/DAFO_WP_SocialLogin
 * Description: Allow users to login with LinkedIn, Facebook, Bluesky, Instagram, Google, and Apple. Admins can enable/disable platforms from settings.
 * Version: 1.0.2
 * Author: Gary DAFO
 * Author URI: https://github.com/dafogary
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dafo-social-login
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DAFO_SL_VERSION', '1.0.2');
define('DAFO_SL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DAFO_SL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DAFO_SL_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once DAFO_SL_PLUGIN_DIR . 'includes/class-dafo-social-login.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/class-dafo-sl-oauth-handler.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/class-dafo-sl-admin.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/class-dafo-sl-login-form.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/class-dafo-sl-shortcode.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/platforms/class-dafo-sl-linkedin.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/platforms/class-dafo-sl-facebook.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/platforms/class-dafo-sl-google.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/platforms/class-dafo-sl-apple.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/platforms/class-dafo-sl-instagram.php';
require_once DAFO_SL_PLUGIN_DIR . 'includes/platforms/class-dafo-sl-bluesky.php';

/**
 * Main plugin class
 */
class DAFO_Social_Login_Plugin {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('dafo-social-login', false, dirname(DAFO_SL_PLUGIN_BASENAME) . '/languages');
        
        // Initialize main class
        DAFO_Social_Login::get_instance();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'dafo_social_connections';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            provider varchar(50) NOT NULL,
            provider_user_id varchar(255) NOT NULL,
            access_token text,
            refresh_token text,
            token_expires datetime,
            user_data text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_provider (user_id, provider),
            UNIQUE KEY provider_user (provider, provider_user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        $default_options = array(
            'enabled_providers' => array(),
            'linkedin_client_id' => '',
            'linkedin_client_secret' => '',
            'facebook_app_id' => '',
            'facebook_app_secret' => '',
            'google_client_id' => '',
            'google_client_secret' => '',
            'apple_client_id' => '',
            'apple_team_id' => '',
            'apple_key_id' => '',
            'apple_private_key' => '',
            'instagram_client_id' => '',
            'instagram_client_secret' => '',
            'bluesky_enabled' => false,
            'compatibility_mode' => 'both',
            'show_on_login' => true,
            'show_on_register' => true,
            'auto_create_account' => true,
            'redirect_after_login' => '',
        );
        
        add_option('dafo_social_login_settings', $default_options);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize plugin
DAFO_Social_Login_Plugin::get_instance();
