<?php
/**
 * Base OAuth Provider Class
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class DAFO_OAuth_Provider {
    
    protected $provider_name;
    protected $client_id;
    protected $client_secret;
    
    abstract public function get_authorization_url($state);
    abstract public function get_user_data();
    abstract protected function get_access_token($code);
    
    public function is_enabled() {
        $options = get_option('dafo_social_login_settings', array());
        return isset($options[$this->provider_name . '_enabled']) && $options[$this->provider_name . '_enabled'] == '1';
    }
    
    protected function get_redirect_uri() {
        return home_url('/dafo-social-login/callback/' . $this->provider_name . '/');
    }
    
    protected function make_request($url, $args = array()) {
        $defaults = array(
            'timeout' => 30,
            'sslverify' => true
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            error_log('DAFO Social Login - Request error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('DAFO Social Login - JSON decode error: ' . json_last_error_msg());
            return false;
        }
        
        return $data;
    }
}
