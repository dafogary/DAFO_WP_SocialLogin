<?php
/**
 * BlueSky OAuth Provider
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_BlueSky_Provider extends DAFO_OAuth_Provider {
    
    public function __construct() {
        $this->provider_name = 'bluesky';
        $options = get_option('dafo_social_login_settings', array());
        $this->client_id = isset($options['bluesky_client_id']) ? $options['bluesky_client_id'] : '';
        $this->client_secret = isset($options['bluesky_client_secret']) ? $options['bluesky_client_secret'] : '';
    }
    
    public function get_authorization_url($state) {
        // BlueSky OAuth implementation
        // Note: As of now, BlueSky's OAuth implementation may vary
        // This is a placeholder for the OAuth flow
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->get_redirect_uri(),
            'response_type' => 'code',
            'scope' => 'atproto transition:generic',
            'state' => $state
        );
        
        return 'https://bsky.social/oauth/authorize?' . http_build_query($params);
    }
    
    protected function get_access_token($code) {
        $token_url = 'https://bsky.social/oauth/token';
        
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
        
        // Get user profile from BlueSky API
        $user_info_url = 'https://bsky.social/xrpc/com.atproto.server.getSession';
        
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
            'id' => isset($user_info['did']) ? $user_info['did'] : '',
            'email' => $user_info['email'],
            'username' => isset($user_info['handle']) ? $user_info['handle'] : '',
            'display_name' => isset($user_info['displayName']) ? $user_info['displayName'] : '',
            'first_name' => '',
            'last_name' => '',
            'profile_picture' => isset($user_info['avatar']) ? $user_info['avatar'] : ''
        );
    }
}
