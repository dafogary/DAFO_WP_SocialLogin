<?php
/**
 * Google OAuth Provider
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Google_Provider extends DAFO_OAuth_Provider {
    
    public function __construct() {
        $this->provider_name = 'google';
        $options = get_option('dafo_social_login_settings', array());
        $this->client_id = isset($options['google_client_id']) ? $options['google_client_id'] : '';
        $this->client_secret = isset($options['google_client_secret']) ? $options['google_client_secret'] : '';
    }
    
    public function get_authorization_url($state) {
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->get_redirect_uri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online'
        );
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    protected function get_access_token($code) {
        $token_url = 'https://oauth2.googleapis.com/token';
        
        $args = array(
            'method' => 'POST',
            'body' => array(
                'code' => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->get_redirect_uri(),
                'grant_type' => 'authorization_code'
            )
        );
        
        $response = $this->make_request($token_url, $args);
        
        if ($response && isset($response['access_token'])) {
            return $response['access_token'];
        }
        
        return false;
    }
    
    public function get_user_data() {
        if (!isset($_GET['code'])) {
            return false;
        }
        
        $code = sanitize_text_field($_GET['code']);
        $access_token = $this->get_access_token($code);
        
        if (!$access_token) {
            return false;
        }
        
        $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            )
        );
        
        $user_info = $this->make_request($user_info_url, $args);
        
        if (!$user_info || !isset($user_info['email'])) {
            return false;
        }
        
        return array(
            'id' => $user_info['id'],
            'email' => $user_info['email'],
            'first_name' => isset($user_info['given_name']) ? $user_info['given_name'] : '',
            'last_name' => isset($user_info['family_name']) ? $user_info['family_name'] : '',
            'display_name' => isset($user_info['name']) ? $user_info['name'] : '',
            'profile_picture' => isset($user_info['picture']) ? $user_info['picture'] : ''
        );
    }
}
