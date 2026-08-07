<?php
/**
 * Instagram OAuth Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_Instagram {
    
    private $auth_url = 'https://api.instagram.com/oauth/authorize';
    private $token_url = 'https://api.instagram.com/oauth/access_token';
    private $api_url = 'https://graph.instagram.com/';
    
    public function get_auth_url($state = '') {
        $settings = get_option('dafo_social_login_settings', array());
        
        if (empty($settings['instagram_client_id'])) {
            return '';
        }
        
        $params = array(
            'client_id' => $settings['instagram_client_id'],
            'redirect_uri' => $this->get_redirect_uri(),
            'scope' => 'user_profile,user_media',
            'response_type' => 'code',
            'state' => !empty($state) ? $state : DAFO_SL_OAuth_Handler::create_authorization_state('instagram'),
        );
        
        return $this->auth_url . '?' . http_build_query($params);
    }
    
    public function handle_callback() {
        if (!isset($_GET['code'])) {
            return new WP_Error('no_code', __('No authorization code received', 'dafo-social-login'));
        }
        
        $code = sanitize_text_field($_GET['code']);
        
        // Exchange code for access token
        $token_data = $this->get_access_token($code);
        
        if (is_wp_error($token_data)) {
            return $token_data;
        }
        
        // Get user data
        $user_data = $this->get_user_data($token_data['access_token'], $token_data['user_id']);
        
        if (is_wp_error($user_data)) {
            return $user_data;
        }
        
        // Add token data to user data
        $user_data['access_token'] = $token_data['access_token'];
        
        return $user_data;
    }
    
    private function get_access_token($code) {
        $settings = get_option('dafo_social_login_settings', array());
        
        $params = array(
            'client_id' => $settings['instagram_client_id'],
            'client_secret' => $settings['instagram_client_secret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->get_redirect_uri(),
            'code' => $code,
        );
        
        $response = wp_remote_post($this->token_url, array(
            'body' => $params,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error_type'])) {
            return new WP_Error('token_error', $body['error_message'] ?? 'Token request failed');
        }
        
        return $body;
    }
    
    private function get_user_data($access_token, $user_id) {
        $fields = 'id,username,account_type';
        
        $response = wp_remote_get($this->api_url . $user_id . '?fields=' . $fields . '&access_token=' . $access_token);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $profile = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($profile['id'])) {
            return new WP_Error('profile_error', __('Failed to retrieve user profile', 'dafo-social-login'));
        }
        
        // Instagram doesn't provide email through API
        // We'll need to generate a unique identifier or ask user for email
        return array(
            'id' => $profile['id'],
            'email' => '', // Instagram doesn't provide email
            'first_name' => '',
            'last_name' => '',
            'name' => $profile['username'] ?? '',
            'username' => $profile['username'] ?? '',
        );
    }
    
    private function get_redirect_uri() {
        return home_url('/dafo-social-login/instagram/callback');
    }
}
