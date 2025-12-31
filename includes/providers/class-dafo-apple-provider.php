<?php
/**
 * Apple Sign In Provider
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Apple_Provider extends DAFO_OAuth_Provider {
    
    private $team_id;
    private $key_id;
    private $private_key;
    
    public function __construct() {
        $this->provider_name = 'apple';
        $options = get_option('dafo_social_login_settings', array());
        $this->client_id = isset($options['apple_client_id']) ? $options['apple_client_id'] : '';
        $this->team_id = isset($options['apple_team_id']) ? $options['apple_team_id'] : '';
        $this->key_id = isset($options['apple_key_id']) ? $options['apple_key_id'] : '';
        $this->private_key = isset($options['apple_private_key']) ? $options['apple_private_key'] : '';
    }
    
    public function get_authorization_url($state) {
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->get_redirect_uri(),
            'response_type' => 'code',
            'scope' => 'name email',
            'response_mode' => 'form_post',
            'state' => $state
        );
        
        return 'https://appleid.apple.com/auth/authorize?' . http_build_query($params);
    }
    
    protected function get_access_token($code) {
        $token_url = 'https://appleid.apple.com/auth/token';
        
        $client_secret = $this->generate_client_secret();
        
        if (!$client_secret) {
            return false;
        }
        
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => array(
                'client_id' => $this->client_id,
                'client_secret' => $client_secret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->get_redirect_uri()
            )
        );
        
        $response = $this->make_request($token_url, $args);
        
        if ($response && isset($response['access_token'])) {
            return $response['access_token'];
        }
        
        return false;
    }
    
    private function generate_client_secret() {
        if (empty($this->team_id) || empty($this->key_id) || empty($this->private_key)) {
            return false;
        }
        
        // Apple requires a JWT token as client secret
        // This is a simplified version - in production, use a proper JWT library
        $header = array(
            'alg' => 'ES256',
            'kid' => $this->key_id
        );
        
        $payload = array(
            'iss' => $this->team_id,
            'iat' => time(),
            'exp' => time() + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->client_id
        );
        
        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));
        
        $signature_input = $header_encoded . '.' . $payload_encoded;
        
        // Sign with private key (requires OpenSSL)
        $signature = '';
        if (function_exists('openssl_sign')) {
            $key_resource = openssl_pkey_get_private($this->private_key);
            if ($key_resource) {
                openssl_sign($signature_input, $signature, $key_resource, OPENSSL_ALGO_SHA256);
                openssl_free_key($key_resource);
                $signature_encoded = $this->base64url_encode($signature);
                return $signature_input . '.' . $signature_encoded;
            }
        }
        
        return false;
    }
    
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    public function get_user_data() {
        // Apple sends data via POST
        $code = '';
        $id_token = '';
        
        if (isset($_POST['code'])) {
            $code = sanitize_text_field($_POST['code']);
        } elseif (isset($_GET['code'])) {
            $code = sanitize_text_field($_GET['code']);
        }
        
        if (isset($_POST['id_token'])) {
            $id_token = sanitize_text_field($_POST['id_token']);
        }
        
        if (empty($code)) {
            return false;
        }
        
        // Decode ID token to get user info
        $user_info = array();
        
        if ($id_token) {
            $parts = explode('.', $id_token);
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode($parts[1]), true);
                if ($payload && isset($payload['email'])) {
                    $user_info['email'] = $payload['email'];
                    $user_info['id'] = isset($payload['sub']) ? $payload['sub'] : '';
                }
            }
        }
        
        // Get additional user info if provided
        if (isset($_POST['user'])) {
            $user_data = json_decode(stripslashes($_POST['user']), true);
            if ($user_data) {
                if (isset($user_data['name']['firstName'])) {
                    $user_info['first_name'] = $user_data['name']['firstName'];
                }
                if (isset($user_data['name']['lastName'])) {
                    $user_info['last_name'] = $user_data['name']['lastName'];
                }
            }
        }
        
        if (!isset($user_info['email'])) {
            return false;
        }
        
        return array(
            'id' => $user_info['id'],
            'email' => $user_info['email'],
            'first_name' => isset($user_info['first_name']) ? $user_info['first_name'] : '',
            'last_name' => isset($user_info['last_name']) ? $user_info['last_name'] : '',
            'display_name' => '',
            'profile_picture' => ''
        );
    }
}
