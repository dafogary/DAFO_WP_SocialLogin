<?php
/**
 * Bluesky OAuth Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_Bluesky {
    
    private $api_url = 'https://bsky.social/xrpc/';
    
    public function get_auth_url($state = '') {
        // Bluesky uses a different authentication model
        // For now, redirect to a custom login page
        return add_query_arg(
            array(
                'action' => 'dafo_bluesky_login',
                'state' => !empty($state) ? $state : DAFO_SL_OAuth_Handler::create_authorization_state('bluesky'),
            ),
            wp_login_url()
        );
    }
    
    public function handle_callback() {
        // Check if this is a Bluesky login attempt
        if (!isset($_POST['bluesky_handle']) || !isset($_POST['bluesky_password'])) {
            return new WP_Error('no_credentials', __('No Bluesky credentials provided', 'dafo-social-login'));
        }
        
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'dafo_bluesky_login')) {
            return new WP_Error('invalid_nonce', __('Invalid security token', 'dafo-social-login'));
        }
        
        $handle = sanitize_text_field($_POST['bluesky_handle']);
        $password = $_POST['bluesky_password']; // Don't sanitize password
        
        // Authenticate with Bluesky
        $auth_data = $this->authenticate($handle, $password);
        
        if (is_wp_error($auth_data)) {
            return $auth_data;
        }
        
        // Get user profile
        $user_data = $this->get_user_profile($auth_data['accessJwt'], $auth_data['did']);
        
        if (is_wp_error($user_data)) {
            return $user_data;
        }
        
        // Add authentication data
        $user_data['access_token'] = $auth_data['accessJwt'];
        $user_data['refresh_token'] = $auth_data['refreshJwt'] ?? '';
        
        return $user_data;
    }
    
    private function authenticate($handle, $password) {
        $response = wp_remote_post($this->api_url . 'com.atproto.server.createSession', array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'identifier' => $handle,
                'password' => $password,
            )),
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($body['accessJwt'])) {
            $error_message = isset($body['message']) ? $body['message'] : __('Authentication failed', 'dafo-social-login');
            return new WP_Error('auth_failed', $error_message);
        }
        
        return $body;
    }
    
    private function get_user_profile($access_token, $did) {
        $response = wp_remote_get(
            $this->api_url . 'app.bsky.actor.getProfile?actor=' . urlencode($did),
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                ),
            )
        );
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $profile = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!isset($profile['did'])) {
            return new WP_Error('profile_error', __('Failed to retrieve user profile', 'dafo-social-login'));
        }
        
        // Extract name components
        $display_name = $profile['displayName'] ?? $profile['handle'] ?? '';
        $name_parts = explode(' ', $display_name, 2);
        
        return array(
            'id' => $profile['did'],
            'email' => '', // Bluesky doesn't expose email
            'first_name' => $name_parts[0] ?? '',
            'last_name' => $name_parts[1] ?? '',
            'name' => $display_name,
            'handle' => $profile['handle'] ?? '',
            'avatar' => $profile['avatar'] ?? '',
        );
    }
    
    public function render_login_form() {
        // This would be called to show a custom Bluesky login form
        ?>
        <div class="dafo-bluesky-login-form">
            <h3><?php _e('Login with Bluesky', 'dafo-social-login'); ?></h3>
            <form method="post" action="<?php echo esc_url(home_url('/dafo-social-login/bluesky/callback')); ?>">
                <?php wp_nonce_field('dafo_bluesky_login'); ?>
                <?php if (isset($_GET['state'])) : ?>
                    <input type="hidden" name="state" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_GET['state']))); ?>">
                <?php endif; ?>
                <p>
                    <label for="bluesky_handle"><?php _e('Handle or Email', 'dafo-social-login'); ?></label>
                    <input type="text" name="bluesky_handle" id="bluesky_handle" required 
                        placeholder="example.bsky.social" class="input">
                </p>
                <p>
                    <label for="bluesky_password"><?php _e('Password', 'dafo-social-login'); ?></label>
                    <input type="password" name="bluesky_password" id="bluesky_password" required class="input">
                </p>
                <p>
                    <button type="submit" class="button button-primary">
                        <?php _e('Login with Bluesky', 'dafo-social-login'); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }
}
