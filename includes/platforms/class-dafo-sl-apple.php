<?php
/**
 * Apple Sign In Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_Apple {
    
    private $auth_url = 'https://appleid.apple.com/auth/authorize';
    private $token_url = 'https://appleid.apple.com/auth/token';
    
    public function get_auth_url($state = '') {
        $settings = get_option('dafo_social_login_settings', array());
        
        if (empty($settings['apple_client_id'])) {
            return '';
        }
        
        $params = array(
            'client_id' => $settings['apple_client_id'],
            'redirect_uri' => $this->get_redirect_uri(),
            'response_type' => 'code',
            'scope' => 'name email',
            'response_mode' => 'form_post',
            'state' => !empty($state) ? $state : DAFO_SL_OAuth_Handler::create_authorization_state('apple'),
        );
        
        return $this->auth_url . '?' . http_build_query($params);
    }
    
    public function handle_callback() {
        $code = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        
        if (empty($code)) {
            return new WP_Error('no_code', __('No authorization code received', 'dafo-social-login'));
        }
        
        // Exchange code for access token
        $token_data = $this->get_access_token($code);
        
        if (is_wp_error($token_data)) {
            return $token_data;
        }
        
        // Decode ID token to get user data
        $user_data = $this->decode_id_token($token_data['id_token']);
        
        if (is_wp_error($user_data)) {
            return $user_data;
        }
        
        // Apple sends user info only on first authorization
        if (isset($_POST['user'])) {
            $user_info = json_decode(stripslashes($_POST['user']), true);
            if (isset($user_info['name'])) {
                $user_data['first_name'] = $user_info['name']['firstName'] ?? '';
                $user_data['last_name'] = $user_info['name']['lastName'] ?? '';
                $user_data['name'] = trim($user_data['first_name'] . ' ' . $user_data['last_name']);
            }
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
        
        // Generate client secret (JWT)
        $client_secret = $this->generate_client_secret();
        
        if (is_wp_error($client_secret)) {
            return $client_secret;
        }
        
        $params = array(
            'client_id' => $settings['apple_client_id'],
            'client_secret' => $client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->get_redirect_uri(),
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
            return new WP_Error('token_error', $body['error'] ?? 'Token request failed');
        }
        
        return $body;
    }
    
    private function generate_client_secret() {
        $settings = get_option('dafo_social_login_settings', array());
        
        if (empty($settings['apple_team_id']) || empty($settings['apple_key_id']) || empty($settings['apple_private_key'])) {
            return new WP_Error('missing_credentials', __('Apple credentials are incomplete', 'dafo-social-login'));
        }
        
        $header = json_encode([
            'alg' => 'ES256',
            'kid' => $settings['apple_key_id'],
        ]);
        
        $payload = json_encode([
            'iss' => $settings['apple_team_id'],
            'iat' => time(),
            'exp' => time() + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $settings['apple_client_id'],
        ]);
        
        $base64_header = $this->base64_url_encode($header);
        $base64_payload = $this->base64_url_encode($payload);
        
        $signature_input = $base64_header . '.' . $base64_payload;
        
        // Sign with private key
        $private_key = openssl_pkey_get_private($settings['apple_private_key']);
        
        if (!$private_key) {
            return new WP_Error('invalid_key', __('Invalid Apple private key', 'dafo-social-login'));
        }
        
        $signature = '';
        openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);
        
        $base64_signature = $this->base64_url_encode($signature);
        
        return $signature_input . '.' . $base64_signature;
    }
    
    private function decode_id_token($id_token) {
        $parts = explode('.', $id_token);
        
        if (count($parts) !== 3) {
            return new WP_Error('invalid_token', __('Invalid ID token', 'dafo-social-login'));
        }
        
        $payload = json_decode($this->base64_url_decode($parts[1]), true);
        
        if (!isset($payload['sub'])) {
            return new WP_Error('invalid_payload', __('Invalid token payload', 'dafo-social-login'));
        }
        
        return array(
            'id' => $payload['sub'],
            'email' => $payload['email'] ?? '',
            'first_name' => '',
            'last_name' => '',
            'name' => '',
        );
    }
    
    private function base64_url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64_url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    private function get_redirect_uri() {
        return home_url('/dafo-social-login/apple/callback');
    }
}
