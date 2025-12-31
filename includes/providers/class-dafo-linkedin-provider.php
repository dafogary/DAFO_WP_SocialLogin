<?php
/**
 * LinkedIn OAuth Provider
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_LinkedIn_Provider extends DAFO_OAuth_Provider {
    
    public function __construct() {
        $this->provider_name = 'linkedin';
        $options = get_option('dafo_social_login_settings', array());
        $this->client_id = isset($options['linkedin_client_id']) ? $options['linkedin_client_id'] : '';
        $this->client_secret = isset($options['linkedin_client_secret']) ? $options['linkedin_client_secret'] : '';
    }
    
    public function get_authorization_url($state) {
        $params = array(
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $this->get_redirect_uri(),
            'scope' => 'openid profile email',
            'state' => $state
        );
        
        return 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);
    }
    
    protected function get_access_token($code) {
        $token_url = 'https://www.linkedin.com/oauth/v2/accessToken';
        
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => array(
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->get_redirect_uri()
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
        
        // Get user profile
        $user_info_url = 'https://api.linkedin.com/v2/userinfo';
        
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
            'id' => isset($user_info['sub']) ? $user_info['sub'] : '',
            'email' => $user_info['email'],
            'first_name' => isset($user_info['given_name']) ? $user_info['given_name'] : '',
            'last_name' => isset($user_info['family_name']) ? $user_info['family_name'] : '',
            'display_name' => isset($user_info['name']) ? $user_info['name'] : '',
            'profile_picture' => isset($user_info['picture']) ? $user_info['picture'] : ''
        );
    }
}
