<?php
/**
 * Facebook OAuth Provider
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Facebook_Provider extends DAFO_OAuth_Provider {
    
    public function __construct() {
        $this->provider_name = 'facebook';
        $options = get_option('dafo_social_login_settings', array());
        $this->client_id = isset($options['facebook_app_id']) ? $options['facebook_app_id'] : '';
        $this->client_secret = isset($options['facebook_app_secret']) ? $options['facebook_app_secret'] : '';
    }
    
    public function get_authorization_url($state) {
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->get_redirect_uri(),
            'scope' => 'email,public_profile',
            'state' => $state,
            'response_type' => 'code'
        );
        
        return 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
    }
    
    protected function get_access_token($code) {
        $token_url = 'https://graph.facebook.com/v18.0/oauth/access_token';
        
        $params = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->get_redirect_uri(),
            'code' => $code
        );
        
        $response = $this->make_request($token_url . '?' . http_build_query($params));
        
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
        
        $user_info_url = 'https://graph.facebook.com/v18.0/me';
        
        $params = array(
            'fields' => 'id,email,first_name,last_name,name,picture.type(large)',
            'access_token' => $access_token
        );
        
        $user_info = $this->make_request($user_info_url . '?' . http_build_query($params));
        
        if (!$user_info || !isset($user_info['email'])) {
            return false;
        }
        
        $profile_picture = '';
        if (isset($user_info['picture']['data']['url'])) {
            $profile_picture = $user_info['picture']['data']['url'];
        }
        
        return array(
            'id' => $user_info['id'],
            'email' => $user_info['email'],
            'first_name' => isset($user_info['first_name']) ? $user_info['first_name'] : '',
            'last_name' => isset($user_info['last_name']) ? $user_info['last_name'] : '',
            'display_name' => isset($user_info['name']) ? $user_info['name'] : '',
            'profile_picture' => $profile_picture
        );
    }
}
