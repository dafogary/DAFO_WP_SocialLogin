<?php
/**
 * LinkedIn OAuth Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_LinkedIn {
    
    private $auth_url = 'https://www.linkedin.com/oauth/v2/authorization';
    private $token_url = 'https://www.linkedin.com/oauth/v2/accessToken';
    private $api_url = 'https://api.linkedin.com/v2/';
    
    public function get_auth_url($state = '') {
        $settings = get_option('dafo_social_login_settings', array());
        
        if (empty($settings['linkedin_client_id'])) {
            return '';
        }
        
        $params = array(
            'response_type' => 'code',
            'client_id' => $settings['linkedin_client_id'],
            'redirect_uri' => $this->get_redirect_uri(),
            'scope' => 'openid profile email',
            'state' => !empty($state) ? $state : DAFO_SL_OAuth_Handler::create_authorization_state('linkedin'),
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
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->get_redirect_uri(),
            'client_id' => $settings['linkedin_client_id'],
            'client_secret' => $settings['linkedin_client_secret'],
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
        // Get user profile
        $profile_response = wp_remote_get($this->api_url . 'userinfo', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ));
        
        if (is_wp_error($profile_response)) {
            return $profile_response;
        }
        
        $profile = json_decode(wp_remote_retrieve_body($profile_response), true);
        
        if (!isset($profile['sub'])) {
            return new WP_Error('profile_error', __('Failed to retrieve user profile', 'dafo-social-login'));
        }
        
        return array(
            'id' => $profile['sub'],
            'email' => $profile['email'] ?? '',
            'first_name' => $profile['given_name'] ?? '',
            'last_name' => $profile['family_name'] ?? '',
            'name' => $profile['name'] ?? '',
            'picture' => $profile['picture'] ?? '',
        );
    }
    
    private function get_redirect_uri() {
        return home_url('/dafo-social-login/linkedin/callback');
    }
}
