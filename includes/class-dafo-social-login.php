<?php
/**
 * Main plugin class
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Social_Login {
    
    protected $admin;
    protected $frontend;
    protected $oauth;
    
    public function __construct() {
        $this->load_dependencies();
    }
    
    private function load_dependencies() {
        $this->admin = new DAFO_Social_Login_Admin();
        $this->frontend = new DAFO_Social_Login_Frontend();
        $this->oauth = new DAFO_Social_Login_OAuth();
    }
    
    public function run() {
        // Initialize admin interface
        if (is_admin()) {
            add_action('admin_menu', array($this->admin, 'add_admin_menu'));
            add_action('admin_init', array($this->admin, 'register_settings'));
            add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_styles'));
        }
        
        // Initialize frontend
        add_action('login_enqueue_scripts', array($this->frontend, 'enqueue_styles'));
        add_action('login_form', array($this->frontend, 'display_login_buttons'));
        add_shortcode('dafo_social_login', array($this->frontend, 'social_login_shortcode'));
        
        // OAuth callbacks
        add_action('init', array($this->oauth, 'handle_callback'));
        
        // WooCommerce integration
        if (class_exists('WooCommerce')) {
            add_action('woocommerce_login_form_end', array($this->frontend, 'display_login_buttons'));
        }
    }
    
    public static function get_option($key, $default = '') {
        $options = get_option('dafo_social_login_settings', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    public static function update_option($key, $value) {
        $options = get_option('dafo_social_login_settings', array());
        $options[$key] = $value;
        update_option('dafo_social_login_settings', $options);
    }
}
