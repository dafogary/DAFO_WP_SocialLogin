<?php
/**
 * Google OAuth Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_Google {
    
    private $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth';
    private $token_url = 'https://oauth2.googleapis.com/token';
    private $api_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
    
    public function get_auth_url($state = '') {
        $settings = get_option('dafo_social_login_settings', array());
        
        if (empty($settings['google_client_id'])) {
            return '';
        }
        
        $params = array(
            'client_id' => $settings['google_client_id'],
            'redirect_uri' => $this->get_redirect_uri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => !empty($state) ? $state : DAFO_SL_OAuth_Handler::create_authorization_state('google'),
            'access_type' => 'online',
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
        $user_data['refresh_token'] = isset($token_data['refresh_token']) ? $token_data['refresh_token'] : '';
        $user_data['token_expires'] = isset($token_data['expires_in']) 
            ? date('Y-m-d H:i:s', time() + $token_data['expires_in']) 
            : null;
        
        return $user_data;
    }
    
    private function get_access_token($code) {
        $settings = get_option('dafo_social_login_settings', array());
        
        $params = array(
            'code' => $code,
            'client_id' => $settings['google_client_id'],
            'client_secret' => $settings['google_client_secret'],
            'redirect_uri' => $this->get_redirect_uri(),
            'grant_type' => 'authorization_code',
        );
        
        $response = wp_remote_post($this->token_url, array(
            'body' => $params,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('token_error', $body['error_description'] ?? 'Token request failed');
        }
        
        return $body;
    }
    
    private function get_user_data($access_token) {
        $response = wp_remote_get($this->api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ));
        
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
            'first_name' => $profile['given_name'] ?? '',
            'last_name' => $profile['family_name'] ?? '',
            'name' => $profile['name'] ?? '',
            'picture' => $profile['picture'] ?? '',
        );
    }
    
    private function get_redirect_uri() {
        return home_url('/dafo-social-login/google/callback');
    }
}
