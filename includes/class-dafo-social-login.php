<?php
/**
 * Main Social Login Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Social_Login {
    
    private static $instance = null;
    private $oauth_handler;
    private $admin;
    private $login_form;
    private $shortcode;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->init_classes();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'register_endpoints'));
        add_action('template_redirect', array($this, 'handle_oauth_callback'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    private function init_classes() {
        $this->oauth_handler = new DAFO_SL_OAuth_Handler();
        $this->admin = new DAFO_SL_Admin();
        $this->login_form = new DAFO_SL_Login_Form();
        $this->shortcode = new DAFO_SL_Shortcode();
    }
    
    public function register_endpoints() {
        add_rewrite_rule(
            '^dafo-social-login/([^/]+)/callback/?',
            'index.php?dafo_social_login=1&provider=$matches[1]',
            'top'
        );
        
        add_rewrite_tag('%dafo_social_login%', '([^&]+)');
        add_rewrite_tag('%provider%', '([^&]+)');
    }
    
    public function handle_oauth_callback() {
        if (get_query_var('dafo_social_login')) {
            $provider = get_query_var('provider');
            
            if (empty($provider)) {
                wp_die(__('Invalid provider', 'dafo-social-login'));
            }
            
            $this->oauth_handler->handle_callback($provider);
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style(
            'dafo-social-login',
            DAFO_SL_PLUGIN_URL . 'assets/css/social-login.css',
            array(),
            DAFO_SL_VERSION
        );
        
        wp_enqueue_script(
            'dafo-social-login',
            DAFO_SL_PLUGIN_URL . 'assets/js/social-login.js',
            array('jquery'),
            DAFO_SL_VERSION,
            true
        );

        wp_localize_script(
            'dafo-social-login',
            'dafoscData',
            array(
                'ajaxUrl'         => admin_url('admin-ajax.php'),
                'loginText'       => __('Logging in…', 'dafo-social-login'),
                'loginSuccess'    => __('Login successful. Redirecting…', 'dafo-social-login'),
                'registerText'    => __('Creating account…', 'dafo-social-login'),
                'registerSuccess' => __('Account created! Redirecting…', 'dafo-social-login'),
                'passwordMismatch'=> __('Passwords do not match.', 'dafo-social-login'),
                'passwordShort'   => __('Password must be at least 8 characters.', 'dafo-social-login'),
                'networkError'    => __('A network error occurred. Please try again.', 'dafo-social-login'),
            )
        );
    }
    
    public function get_settings() {
        return get_option('dafo_social_login_settings', array());
    }
    
    public function get_enabled_providers() {
        $settings = $this->get_settings();
        return isset($settings['enabled_providers']) ? $settings['enabled_providers'] : array();
    }
}
