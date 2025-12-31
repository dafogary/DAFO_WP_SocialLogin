<?php
/**
 * Frontend functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Social_Login_Frontend {
    
    public function enqueue_styles() {
        wp_enqueue_style('dafo-social-login-frontend', DAFO_SOCIAL_LOGIN_PLUGIN_URL . 'assets/css/frontend.css', array(), DAFO_SOCIAL_LOGIN_VERSION);
    }
    
    public function display_login_buttons() {
        if (is_user_logged_in()) {
            return;
        }
        
        $options = get_option('dafo_social_login_settings', array());
        
        $providers = array(
            'google' => array(
                'enabled' => isset($options['google_enabled']) && $options['google_enabled'] == '1',
                'name' => 'Google',
                'class' => 'google'
            ),
            'facebook' => array(
                'enabled' => isset($options['facebook_enabled']) && $options['facebook_enabled'] == '1',
                'name' => 'Facebook',
                'class' => 'facebook'
            ),
            'bluesky' => array(
                'enabled' => isset($options['bluesky_enabled']) && $options['bluesky_enabled'] == '1',
                'name' => 'BlueSky',
                'class' => 'bluesky'
            ),
            'apple' => array(
                'enabled' => isset($options['apple_enabled']) && $options['apple_enabled'] == '1',
                'name' => 'Apple',
                'class' => 'apple'
            ),
            'linkedin' => array(
                'enabled' => isset($options['linkedin_enabled']) && $options['linkedin_enabled'] == '1',
                'name' => 'LinkedIn',
                'class' => 'linkedin'
            ),
            'yahoo' => array(
                'enabled' => isset($options['yahoo_enabled']) && $options['yahoo_enabled'] == '1',
                'name' => 'Yahoo!',
                'class' => 'yahoo'
            )
        );
        
        $enabled_providers = array_filter($providers, function($provider) {
            return $provider['enabled'];
        });
        
        if (empty($enabled_providers)) {
            return;
        }
        
        echo '<div class="dafo-social-login-container">';
        echo '<div class="dafo-social-login-separator">';
        echo '<span>' . esc_html__('Or login with', 'dafo-social-login') . '</span>';
        echo '</div>';
        echo '<div class="dafo-social-login-buttons">';
        
        foreach ($enabled_providers as $provider_key => $provider) {
            $auth_url = $this->get_auth_url($provider_key);
            if ($auth_url) {
                echo '<a href="' . esc_url($auth_url) . '" class="dafo-social-login-button dafo-social-login-' . esc_attr($provider['class']) . '">';
                echo '<span class="dafo-social-login-icon"></span>';
                echo '<span class="dafo-social-login-text">' . esc_html($provider['name']) . '</span>';
                echo '</a>';
            }
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    public function social_login_shortcode($atts) {
        ob_start();
        $this->display_login_buttons();
        return ob_get_clean();
    }
    
    private function get_auth_url($provider) {
        $nonce = wp_create_nonce('dafo_social_login_' . $provider);
        $state = base64_encode(json_encode(array(
            'provider' => $provider,
            'nonce' => $nonce,
            'redirect' => isset($_GET['redirect_to']) ? $_GET['redirect_to'] : home_url()
        )));
        
        return add_query_arg(array(
            'dafo_social_login' => $provider,
            'state' => $state
        ), home_url('/'));
    }
}
