<?php
/**
 * OAuth handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Social_Login_OAuth {
    
    private $providers = array();
    
    public function __construct() {
        $this->providers = array(
            'google' => new DAFO_Google_Provider(),
            'facebook' => new DAFO_Facebook_Provider(),
            'bluesky' => new DAFO_BlueSky_Provider(),
            'apple' => new DAFO_Apple_Provider(),
            'linkedin' => new DAFO_LinkedIn_Provider(),
            'yahoo' => new DAFO_Yahoo_Provider()
        );
    }
    
    public function handle_callback() {
        // Handle OAuth initiation
        if (isset($_GET['dafo_social_login']) && isset($_GET['state'])) {
            $provider_name = sanitize_text_field($_GET['dafo_social_login']);
            
            if (isset($this->providers[$provider_name])) {
                $provider = $this->providers[$provider_name];
                
                if ($provider->is_enabled()) {
                    $auth_url = $provider->get_authorization_url($_GET['state']);
                    if ($auth_url) {
                        wp_redirect($auth_url);
                        exit;
                    }
                }
            }
        }
        
        // Handle OAuth callback
        if (isset($_GET['code']) && isset($_GET['state'])) {
            $this->process_callback();
        }
        
        // Handle Apple POST callback
        if (isset($_POST['code']) && isset($_POST['state']) && isset($_POST['id_token'])) {
            $this->process_callback();
        }
    }
    
    private function process_callback() {
        $state_data = $this->decode_state();
        
        if (!$state_data || !isset($state_data['provider'])) {
            wp_die(__('Invalid state parameter', 'dafo-social-login'));
        }
        
        // Verify nonce
        if (!isset($state_data['nonce']) || !wp_verify_nonce($state_data['nonce'], 'dafo_social_login_' . $state_data['provider'])) {
            wp_die(__('Security check failed', 'dafo-social-login'));
        }
        
        $provider_name = $state_data['provider'];
        
        if (!isset($this->providers[$provider_name])) {
            wp_die(__('Invalid provider', 'dafo-social-login'));
        }
        
        $provider = $this->providers[$provider_name];
        
        // Get user data from provider
        $user_data = $provider->get_user_data();
        
        if (!$user_data || !isset($user_data['email'])) {
            wp_redirect(wp_login_url() . '?social_login_error=no_email');
            exit;
        }
        
        // Find or create WordPress user
        $user = $this->find_or_create_user($user_data, $provider_name);
        
        if (is_wp_error($user)) {
            wp_redirect(wp_login_url() . '?social_login_error=user_creation_failed');
            exit;
        }
        
        // Log the user in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        do_action('wp_login', $user->user_login, $user);
        
        // Redirect
        $redirect_to = isset($state_data['redirect']) ? $state_data['redirect'] : home_url();
        wp_safe_redirect($redirect_to);
        exit;
    }
    
    private function decode_state() {
        $state = '';
        
        if (isset($_GET['state'])) {
            $state = $_GET['state'];
        } elseif (isset($_POST['state'])) {
            $state = $_POST['state'];
        }
        
        if (empty($state)) {
            return false;
        }
        
        // Validate base64 encoding before decoding
        if (!preg_match('/^[a-zA-Z0-9\/+]*={0,2}$/', $state)) {
            error_log('DAFO Social Login - Invalid state parameter format');
            return false;
        }
        
        $decoded = base64_decode($state, true);
        
        if ($decoded === false) {
            error_log('DAFO Social Login - Failed to decode state parameter');
            return false;
        }
        
        $data = json_decode($decoded, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('DAFO Social Login - Invalid state JSON: ' . json_last_error_msg());
            return false;
        }
        
        return $data;
    }
    
    private function find_or_create_user($user_data, $provider) {
        // Check if user exists by email
        $user = get_user_by('email', $user_data['email']);
        
        if ($user) {
            // Update user meta with social login info
            update_user_meta($user->ID, 'dafo_social_login_' . $provider, $user_data['id']);
            update_user_meta($user->ID, 'dafo_social_login_' . $provider . '_data', $user_data);
            return $user;
        }
        
        // Create new user
        $username = $this->generate_username($user_data);
        $password = wp_generate_password(20, true, true);
        
        $user_id = wp_create_user($username, $password, $user_data['email']);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Update user data
        $user_update_data = array(
            'ID' => $user_id
        );
        
        if (isset($user_data['first_name'])) {
            $user_update_data['first_name'] = $user_data['first_name'];
        }
        
        if (isset($user_data['last_name'])) {
            $user_update_data['last_name'] = $user_data['last_name'];
        }
        
        if (isset($user_data['display_name'])) {
            $user_update_data['display_name'] = $user_data['display_name'];
        } elseif (isset($user_data['first_name']) && isset($user_data['last_name'])) {
            $user_update_data['display_name'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
        }
        
        wp_update_user($user_update_data);
        
        // Store social login info
        update_user_meta($user_id, 'dafo_social_login_' . $provider, $user_data['id']);
        update_user_meta($user_id, 'dafo_social_login_' . $provider . '_data', $user_data);
        
        // Send new user notification
        wp_new_user_notification($user_id, null, 'user');
        
        return get_user_by('ID', $user_id);
    }
    
    private function generate_username($user_data) {
        $base_username = '';
        
        if (isset($user_data['username'])) {
            $base_username = sanitize_user($user_data['username'], true);
        } elseif (isset($user_data['email'])) {
            $email_parts = explode('@', $user_data['email']);
            $base_username = sanitize_user($email_parts[0], true);
        } elseif (isset($user_data['first_name']) && isset($user_data['last_name'])) {
            $base_username = sanitize_user($user_data['first_name'] . $user_data['last_name'], true);
        }
        
        if (empty($base_username)) {
            $base_username = 'user';
        }
        
        $username = $base_username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }
        
        return $username;
    }
}
