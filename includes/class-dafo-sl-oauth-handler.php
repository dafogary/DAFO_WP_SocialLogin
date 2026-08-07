<?php
/**
 * OAuth Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_OAuth_Handler {
    
    private $providers = array();
    private static $state_transient_prefix = 'dafo_sl_auth_';
    
    public function __construct() {
        $this->init_providers();
    }
    
    private function init_providers() {
        $this->providers['linkedin'] = new DAFO_SL_LinkedIn();
        $this->providers['facebook'] = new DAFO_SL_Facebook();
        $this->providers['google'] = new DAFO_SL_Google();
        $this->providers['apple'] = new DAFO_SL_Apple();
        $this->providers['instagram'] = new DAFO_SL_Instagram();
        $this->providers['bluesky'] = new DAFO_SL_Bluesky();
    }

    public static function create_authorization_state($provider_name, $context = array()) {
        $provider_name = sanitize_key($provider_name);
        $state_key = wp_generate_password(20, false, false);
        $nonce = wp_create_nonce('dafo_social_login_' . $provider_name . '_' . $state_key);

        set_transient(
            self::get_state_transient_key($state_key),
            array(
                'provider' => $provider_name,
                'redirect_url' => self::sanitize_context_url($context['redirect_url'] ?? ''),
                'error_url' => self::sanitize_context_url($context['error_url'] ?? ''),
                'form_type' => isset($context['form_type']) ? sanitize_key($context['form_type']) : '',
                'userswp_form_id' => isset($context['userswp_form_id']) ? absint($context['userswp_form_id']) : 0,
            ),
            HOUR_IN_SECONDS
        );

        return $state_key . ':' . $nonce;
    }

    public static function get_request_state() {
        if (isset($_POST['state'])) {
            return sanitize_text_field(wp_unslash($_POST['state']));
        }

        if (isset($_GET['state'])) {
            return sanitize_text_field(wp_unslash($_GET['state']));
        }

        return '';
    }

    public static function consume_authorization_context($provider_name, $state = '') {
        $provider_name = sanitize_key($provider_name);
        $state = !empty($state) ? sanitize_text_field($state) : self::get_request_state();

        if (empty($state)) {
            return new WP_Error('missing_state', __('Missing state parameter', 'dafo-social-login'));
        }

        $state_parts = explode(':', $state, 2);

        if (2 !== count($state_parts)) {
            if (wp_verify_nonce($state, 'dafo_social_login_' . $provider_name)) {
                return array();
            }

            return new WP_Error('invalid_state', __('Invalid state parameter', 'dafo-social-login'));
        }

        list($state_key, $nonce) = $state_parts;

        if (!wp_verify_nonce($nonce, 'dafo_social_login_' . $provider_name . '_' . $state_key)) {
            return new WP_Error('invalid_state', __('Invalid state parameter', 'dafo-social-login'));
        }

        $context = get_transient(self::get_state_transient_key($state_key));
        delete_transient(self::get_state_transient_key($state_key));

        if (!is_array($context) || ($context['provider'] ?? '') !== $provider_name) {
            return new WP_Error('expired_state', __('Your login session expired. Please try again.', 'dafo-social-login'));
        }

        return $context;
    }

    private static function get_state_transient_key($state_key) {
        return self::$state_transient_prefix . $state_key;
    }

    private static function sanitize_context_url($url) {
        if (empty($url)) {
            return '';
        }

        return wp_validate_redirect($url, '');
    }
    
    public function get_provider($provider_name) {
        if (isset($this->providers[$provider_name])) {
            return $this->providers[$provider_name];
        }
        return null;
    }
    
    public function get_auth_url($provider_name, $context = array()) {
        $provider = $this->get_provider($provider_name);
        if (!$provider) {
            return '';
        }
        
        return $provider->get_auth_url(self::create_authorization_state($provider_name, is_array($context) ? $context : array()));
    }
    
    public function handle_callback($provider_name) {
        $provider_name = sanitize_key($provider_name);
        $provider = $this->get_provider($provider_name);
        $context = array();
        
        if (!$provider) {
            wp_die(__('Invalid provider', 'dafo-social-login'));
        }

        if ('bluesky' !== $provider_name) {
            $context = self::consume_authorization_context($provider_name);

            if (is_wp_error($context)) {
                $this->redirect_with_error($context->get_error_message());
            }
        } else {
            $request_state = self::get_request_state();

            if (!empty($request_state)) {
                $context = self::consume_authorization_context($provider_name, $request_state);

                if (is_wp_error($context)) {
                    $this->redirect_with_error($context->get_error_message());
                }
            }
        }
        
        // Check for errors
        if (isset($_GET['error'])) {
            $error_message = isset($_GET['error_description']) 
                ? sanitize_text_field(wp_unslash($_GET['error_description'])) 
                : __('Authentication failed', 'dafo-social-login');
            
            $this->redirect_with_error($error_message, $context);
        }
        
        // Handle the callback
        try {
            $result = $provider->handle_callback();
            
            if (is_wp_error($result)) {
                $this->redirect_with_error($result->get_error_message(), $context);
            }
            
            // Process user data and login
            $user = $this->process_social_login($provider_name, $result, $context);
            
            if (is_wp_error($user)) {
                $this->redirect_with_error($user->get_error_message(), $context);
            }
            
            // Log the user in
            wp_set_auth_cookie($user->ID, true);
            do_action('wp_login', $user->user_login, $user);
            do_action('dafo_social_login_authenticated', $user, $provider_name, $result);
            
            // Redirect
            $redirect_url = apply_filters('dafo_social_login_redirect_url', $this->get_success_redirect_url($context), $user);

            wp_safe_redirect($redirect_url);
            exit;
            
        } catch (Exception $e) {
            $this->redirect_with_error($e->getMessage(), $context);
        }
    }

    private function redirect_with_error($message, $context = array()) {
        $message = sanitize_text_field($message);
        $redirect_url = !empty($context['error_url']) ? self::sanitize_context_url($context['error_url']) : '';

        if (empty($redirect_url)) {
            $redirect_url = wp_login_url();
        }

        $redirect_url = remove_query_arg('social_login_error', $redirect_url);
        $redirect_url = add_query_arg('social_login_error', $message, $redirect_url);

        wp_safe_redirect($redirect_url);
        exit;
    }

    private function get_success_redirect_url($context = array()) {
        if (!empty($context['redirect_url'])) {
            $redirect_url = self::sanitize_context_url($context['redirect_url']);

            if (!empty($redirect_url)) {
                return $redirect_url;
            }
        }

        $settings = get_option('dafo_social_login_settings', array());
        $redirect_url = !empty($settings['redirect_after_login'])
            ? self::sanitize_context_url($settings['redirect_after_login'])
            : '';

        return !empty($redirect_url) ? $redirect_url : admin_url();
    }

    private function assign_userswp_register_form($user_id, $context = array()) {
        $form_id = isset($context['userswp_form_id']) ? absint($context['userswp_form_id']) : 0;

        if ($form_id <= 0) {
            return;
        }

        if (function_exists('uwp_get_user_register_form')) {
            $form = uwp_get_user_register_form($form_id);
            $has_form = (is_array($form) && isset($form['id'])) || (is_object($form) && isset($form->id));

            if (!$has_form) {
                return;
            }
        }

        update_user_meta($user_id, '_uwp_register_form_id', $form_id);
    }

    private function mark_userswp_social_login($user_id, $provider, $context = array()) {
        if (empty($context['userswp_form_id']) && !class_exists('UsersWP_Forms')) {
            return;
        }

        update_user_meta($user_id, 'is_uwp_social_login', 1);
        update_user_meta($user_id, 'is_uwp_social_login_no_password', 1);
        update_user_meta($user_id, 'uwp_social_login_provider', sanitize_key($provider));
    }
    
    private function process_social_login($provider, $user_data, $context = array()) {
        global $wpdb;

        $user_data = apply_filters('dafo_social_login_user_data', $user_data, $provider);

        if (!is_array($user_data)) {
            return new WP_Error('invalid_user_data', __('Invalid user data from provider', 'dafo-social-login'));
        }
        
        $provider_user_id = isset($user_data['id']) ? $user_data['id'] : '';
        $email = isset($user_data['email']) ? sanitize_email($user_data['email']) : '';
        $first_name = isset($user_data['first_name']) ? sanitize_text_field($user_data['first_name']) : '';
        $last_name = isset($user_data['last_name']) ? sanitize_text_field($user_data['last_name']) : '';
        $name = isset($user_data['name']) ? sanitize_text_field($user_data['name']) : '';
        
        if (empty($provider_user_id)) {
            return new WP_Error('invalid_user_data', __('Invalid user data from provider', 'dafo-social-login'));
        }
        
        // Check if this social account is already connected
        $table_name = $wpdb->prefix . 'dafo_social_connections';
        $connection = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE provider = %s AND provider_user_id = %s",
            $provider,
            $provider_user_id
        ));
        
        if ($connection) {
            // User exists, update connection and return user
            $user = get_user_by('id', $connection->user_id);
            
            if ($user) {
                $this->update_connection($connection->id, $user_data);
                return $user;
            }
        }
        
        // Check if user exists by email (only if email is provided)
        if (!empty($email)) {
            $user = get_user_by('email', $email);
            
            if ($user) {
                // Link this social account to existing user
                $this->create_connection($user->ID, $provider, $provider_user_id, $user_data);
                return $user;
            }
        }
        
        // Create new user if auto-create is enabled
        $settings = get_option('dafo_social_login_settings', array());
        if (empty($settings['auto_create_account'])) {
            return new WP_Error('account_creation_disabled', __('Account creation is disabled', 'dafo-social-login'));
        }
        
        // Generate username and email (if not provided)
        if (empty($email)) {
            // Generate a unique email for providers that don't provide it
            $username = $this->generate_username('', $first_name, $last_name, $name, $provider, $provider_user_id);
            $email = $username . '@' . $provider . '.sociallogin.local';
        } else {
            $username = $this->generate_username($email, $first_name, $last_name, $name, $provider, $provider_user_id);
        }
        
        // Create user
        $user_id = wp_create_user($username, wp_generate_password(), $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Update user meta
        if (!empty($first_name)) {
            update_user_meta($user_id, 'first_name', $first_name);
        }
        if (!empty($last_name)) {
            update_user_meta($user_id, 'last_name', $last_name);
        }

        $this->assign_userswp_register_form($user_id, $context);
        $this->mark_userswp_social_login($user_id, $provider, $context);
        
        // Mark as social-only account if no real email
        if (strpos($email, '.sociallogin.local') !== false) {
            update_user_meta($user_id, 'dafo_social_only_account', true);
            update_user_meta($user_id, 'dafo_social_provider', $provider);
        }
        
        // Create social connection
        $this->create_connection($user_id, $provider, $provider_user_id, $user_data);
        
        // Send notification email only if real email exists
        if (strpos($email, '.sociallogin.local') === false) {
            wp_new_user_notification($user_id, null, 'user');
        }
        
        return get_user_by('id', $user_id);
    }
    
    private function create_connection($user_id, $provider, $provider_user_id, $user_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dafo_social_connections';
        
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'provider' => $provider,
                'provider_user_id' => $provider_user_id,
                'access_token' => isset($user_data['access_token']) ? $user_data['access_token'] : '',
                'refresh_token' => isset($user_data['refresh_token']) ? $user_data['refresh_token'] : '',
                'token_expires' => isset($user_data['token_expires']) ? $user_data['token_expires'] : null,
                'user_data' => json_encode($user_data),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if (false !== $inserted) {
            do_action('dafo_social_login_connection_created', $user_id, $provider, $provider_user_id);
        }
    }
    
    private function update_connection($connection_id, $user_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dafo_social_connections';
        
        $wpdb->update(
            $table_name,
            array(
                'access_token' => isset($user_data['access_token']) ? $user_data['access_token'] : '',
                'refresh_token' => isset($user_data['refresh_token']) ? $user_data['refresh_token'] : '',
                'token_expires' => isset($user_data['token_expires']) ? $user_data['token_expires'] : null,
                'user_data' => json_encode($user_data),
            ),
            array('id' => $connection_id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
    }
    
    private function generate_username($email = '', $first_name = '', $last_name = '', $name = '', $provider = '', $provider_user_id = '') {
        // Try to use name parts first
        if (!empty($first_name) && !empty($last_name)) {
            $username = strtolower($first_name . '.' . $last_name);
        } elseif (!empty($name)) {
            $username = strtolower(str_replace(' ', '.', $name));
        } elseif (!empty($email) && strpos($email, '@') !== false) {
            // Use email prefix if available
            $username = strtolower(substr($email, 0, strpos($email, '@')));
        } else {
            // Fallback: use provider name + portion of user ID
            $username = $provider . '_' . substr(md5($provider_user_id), 0, 8);
        }
        
        // Remove invalid characters
        $username = preg_replace('/[^a-z0-9._]/', '', $username);
        
        // Ensure username is not empty and has minimum length
        if (empty($username) || strlen($username) < 3) {
            $username = $provider . '_user_' . substr(md5($provider_user_id), 0, 6);
        }
        
        // Check if username exists
        $original_username = $username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $original_username . $counter;
            $counter++;
        }
        
        return $username;
    }
}
