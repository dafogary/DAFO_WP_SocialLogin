<?php
/**
 * Facebook OAuth Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_Facebook {
    
    private $auth_url = 'https://www.facebook.com/v18.0/dialog/oauth';
    private $token_url = 'https://graph.facebook.com/v18.0/oauth/access_token';
    private $api_url = 'https://graph.facebook.com/v18.0/';
    
    public function get_auth_url($state = '') {
        $settings = get_option('dafo_social_login_settings', array());
        
        if (empty($settings['facebook_app_id'])) {
            return '';
        }
        
        $params = array(
            'client_id' => $settings['facebook_app_id'],
            'redirect_uri' => $this->get_redirect_uri(),
            'scope' => 'email,public_profile',
            'state' => !empty($state) ? $state : DAFO_SL_OAuth_Handler::create_authorization_state('facebook'),
            'response_type' => 'code',
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
        $user_data = $this->get_user_data($token_data['access_token']);
        
        if (is_wp_error($user_data)) {
            return $user_data;
        }
        
        // Add token data to user data
        $user_data['access_token'] = $token_data['access_token'];
        $user_data['token_expires'] = isset($token_data['expires_in']) 
            ? date('Y-m-d H:i:s', time() + $token_data['expires_in']) 
            : null;
        
        return $user_data;
    }
    
    private function get_access_token($code) {
        $settings = get_option('dafo_social_login_settings', array());
        
        $params = array(
            'client_id' => $settings['facebook_app_id'],
            'client_secret' => $settings['facebook_app_secret'],
            'redirect_uri' => $this->get_redirect_uri(),
            'code' => $code,
        );
        
        $response = wp_remote_get($this->token_url . '?' . http_build_query($params));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('token_error', $body['error']['message'] ?? 'Token request failed');
        }
        
        return $body;
    }
    
    private function get_user_data($access_token) {
        $fields = 'id,email,first_name,last_name,name,picture.type(large)';
        
        $response = wp_remote_get($this->api_url . 'me?fields=' . $fields . '&access_token=' . $access_token);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $profile = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($profile['id'])) {
            return new WP_Error('profile_error', __('Failed to retrieve user profile', 'dafo-social-login'));
        }
        
        return array(
            'id' => $profile['id'],
            'email' => $profile['email'] ?? '',
            'first_name' => $profile['first_name'] ?? '',
            'last_name' => $profile['last_name'] ?? '',
            'name' => $profile['name'] ?? '',
            'picture' => isset($profile['picture']['data']['url']) ? $profile['picture']['data']['url'] : '',
        );
    }
    
    private function get_redirect_uri() {
        return home_url('/dafo-social-login/facebook/callback');
    }
}
