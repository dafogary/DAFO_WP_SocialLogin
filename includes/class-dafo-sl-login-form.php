<?php
/**
 * Login Form Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_Login_Form {
    
    public function __construct() {
        $settings = get_option('dafo_social_login_settings', array());
        $compatibility_mode = $this->get_compatibility_mode($settings);
        
        if ($this->supports_standard_forms($compatibility_mode) && ($settings['show_on_login'] ?? true)) {
            add_action('login_form', array($this, 'render_wp_login_social_buttons'));
        }
        
        if ($this->supports_standard_forms($compatibility_mode) && ($settings['show_on_register'] ?? true)) {
            add_action('register_form', array($this, 'render_wp_register_social_buttons'));
        }

        if ($this->supports_userswp_forms($compatibility_mode)) {
            add_action('uwp_social_fields', array($this, 'render_userswp_social_buttons'), 30, 2);
        }
        
        add_shortcode('dafo_social_login', array($this, 'render_social_buttons_shortcode'));
        
        add_action('login_head', array($this, 'display_errors'));
    }
    
    public function display_errors() {
        if (isset($_GET['social_login_error'])) {
            $error = sanitize_text_field(wp_unslash($_GET['social_login_error']));
            echo '<div id="login_error">' . esc_html($error) . '</div>';
        }
    }
    
    public function render_wp_login_social_buttons() {
        $this->render_social_buttons($this->get_wp_form_context('login'));
    }

    public function render_wp_register_social_buttons() {
        $this->render_social_buttons($this->get_wp_form_context('register'));
    }

    public function render_userswp_social_buttons($type, $args = array()) {
        if ('login' !== $type && 'register' !== $type) {
            return;
        }

        if (!$this->is_display_enabled_for_form($type)) {
            return;
        }

        $this->display_userswp_errors();
        $this->render_social_buttons($this->get_userswp_context($type, is_array($args) ? $args : array()));
    }

    public function render_social_buttons($context = array()) {
        $settings = get_option('dafo_social_login_settings', array());
        $enabled_providers = $settings['enabled_providers'] ?? array();
        $context = is_array($context) ? $context : array();
        
        if (empty($enabled_providers)) {
            return;
        }
        
        $oauth_handler = new DAFO_SL_OAuth_Handler();
        
        echo '<div class="dafo-social-login-container">';
        echo '<div class="dafo-social-login-separator"><span>' . __('Or continue with', 'dafo-social-login') . '</span></div>';
        echo '<div class="dafo-social-login-buttons">';
        
        foreach ($enabled_providers as $provider) {
            $auth_url = $oauth_handler->get_auth_url($provider, $context);
            
            if (empty($auth_url)) {
                continue;
            }
            
            $provider_name = ucfirst($provider);
            $button_class = 'dafo-social-login-button dafo-social-' . esc_attr($provider);
            
            echo '<a href="' . esc_url($auth_url) . '" class="' . esc_attr($button_class) . '">';
            echo $this->get_provider_icon($provider);
            echo '<span>' . sprintf(__('Continue with %s', 'dafo-social-login'), esc_html($provider_name)) . '</span>';
            echo '</a>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    public function render_social_buttons_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'redirect_to' => '',
                'error_url' => '',
                'type' => '',
                'userswp_form_id' => 0,
            ),
            $atts,
            'dafo_social_login'
        );

        ob_start();
        $this->render_social_buttons(
            array(
                'redirect_url' => $atts['redirect_to'],
                'error_url' => $atts['error_url'],
                'form_type' => $atts['type'],
                'userswp_form_id' => absint($atts['userswp_form_id']),
            )
        );
        return ob_get_clean();
    }

    private function is_display_enabled_for_form($form_type) {
        $settings = get_option('dafo_social_login_settings', array());

        if ('register' === $form_type) {
            return (bool) ($settings['show_on_register'] ?? true);
        }

        return (bool) ($settings['show_on_login'] ?? true);
    }

    private function get_compatibility_mode($settings) {
        $mode = isset($settings['compatibility_mode']) ? sanitize_key($settings['compatibility_mode']) : 'both';

        if (!in_array($mode, array('standard', 'userswp', 'both'), true)) {
            return 'both';
        }

        return $mode;
    }

    private function supports_standard_forms($compatibility_mode) {
        return 'standard' === $compatibility_mode || 'both' === $compatibility_mode;
    }

    private function supports_userswp_forms($compatibility_mode) {
        return 'userswp' === $compatibility_mode || 'both' === $compatibility_mode;
    }

    private function display_userswp_errors() {
        if (!isset($_GET['social_login_error'])) {
            return;
        }

        $error = sanitize_text_field(wp_unslash($_GET['social_login_error']));
        echo '<div class="dafo-social-login-error" role="alert">' . esc_html($error) . '</div>';
    }

    private function get_wp_form_context($form_type) {
        $error_url = 'register' === $form_type && function_exists('wp_registration_url')
            ? wp_registration_url()
            : wp_login_url();

        $context = array(
            'form_type' => $form_type,
            'error_url' => $this->append_requested_redirect($error_url),
        );

        $requested_redirect = $this->get_requested_redirect();
        if (!empty($requested_redirect)) {
            $context['redirect_url'] = $requested_redirect;
        }

        return $context;
    }

    private function get_userswp_context($type, $args) {
        $requested_redirect = $this->get_requested_redirect();
        $context = array(
            'form_type' => $type,
        );

        if ('register' === $type) {
            $context['userswp_form_id'] = !empty($args['id']) ? absint($args['id']) : 1;
        }

        if ('register' === $type && function_exists('uwp_get_register_page_url')) {
            $context['error_url'] = uwp_get_register_page_url();
        } elseif ('login' === $type && function_exists('uwp_get_login_page_url')) {
            $context['error_url'] = uwp_get_login_page_url();
        }

        if (empty($context['error_url'])) {
            $context['error_url'] = 'register' === $type && function_exists('wp_registration_url')
                ? wp_registration_url()
                : wp_login_url();
        }

        $context['error_url'] = $this->append_requested_redirect($context['error_url']);

        if (class_exists('UsersWP_Forms')) {
            $uwp_forms = new UsersWP_Forms();

            if ('register' === $type) {
                $data = array(
                    'uwp_register_form_id' => $context['userswp_form_id'],
                );
                $redirect_page_id = function_exists('uwp_get_option') ? (int) uwp_get_option('register_redirect_to') : 0;

                if (empty($requested_redirect) && -1 === $redirect_page_id && wp_get_referer()) {
                    $context['redirect_url'] = wp_validate_redirect(wp_get_referer(), '');
                } else {
                    $context['redirect_url'] = $uwp_forms->get_register_redirect_url($data, false);
                }
            } else {
                $redirect_page_id = function_exists('uwp_get_option') ? (int) uwp_get_option('login_redirect_to') : 0;

                if (empty($requested_redirect) && -1 === $redirect_page_id && wp_get_referer()) {
                    $context['redirect_url'] = wp_validate_redirect(wp_get_referer(), '');
                } else {
                    $context['redirect_url'] = $uwp_forms->get_login_redirect_url(array(), false);
                }
            }
        }

        if (!empty($requested_redirect)) {
            $context['redirect_url'] = $requested_redirect;
        }

        return $context;
    }

    private function append_requested_redirect($url) {
        $requested_redirect = $this->get_requested_redirect();

        if (empty($requested_redirect)) {
            return $url;
        }

        return add_query_arg('redirect_to', $requested_redirect, $url);
    }

    private function get_requested_redirect() {
        if (!isset($_REQUEST['redirect_to'])) {
            return '';
        }

        return wp_validate_redirect(wp_unslash($_REQUEST['redirect_to']), '');
    }
    
    private function get_provider_icon($provider) {
        $icons = array(
            'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>',
            'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>',
            'google' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M7 11v2.4h3.97c-.16 1.029-1.2 3.02-3.97 3.02-2.39 0-4.34-1.979-4.34-4.42 0-2.44 1.95-4.42 4.34-4.42 1.36 0 2.27.58 2.79 1.08l1.9-1.83c-1.22-1.14-2.8-1.83-4.69-1.83-3.87 0-7 3.13-7 7s3.13 7 7 7c4.04 0 6.721-2.84 6.721-6.84 0-.46-.051-.81-.111-1.16h-6.61zm0 0 17 2h-3v3h-2v-3h-3v-2h3v-3h2v3h3v2z" fill-rule="evenodd" clip-rule="evenodd"/></svg>',
            'apple' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M22 17.607c-.786 2.28-3.139 6.317-5.563 6.361-1.608.031-2.125-.953-3.963-.953-1.837 0-2.412.923-3.932.983-2.572.099-6.542-5.827-6.542-10.995 0-4.747 3.308-7.1 6.198-7.143 1.55-.028 3.014 1.045 3.959 1.045.949 0 2.727-1.29 4.596-1.101.782.033 2.979.315 4.389 2.377-3.741 2.442-3.158 7.549.858 9.426zm-5.222-17.607c-2.826.114-5.132 3.079-4.81 5.531 2.612.203 5.118-2.725 4.81-5.531z"/></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
            'bluesky' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10.5c-1.2-3.6-3.6-7.5-6-7.5s-3.6 2.4-3.6 4.8c0 3.6 2.4 6 6 7.2-3.6 1.2-6 3.6-6 7.2 0 2.4 1.2 4.8 3.6 4.8s4.8-3.9 6-7.5c1.2 3.6 3.6 7.5 6 7.5s3.6-2.4 3.6-4.8c0-3.6-2.4-6-6-7.2 3.6-1.2 6-3.6 6-7.2 0-2.4-1.2-4.8-3.6-4.8s-4.8 3.9-6 7.5z"/></svg>',
        );
        
        return isset($icons[$provider]) ? $icons[$provider] : '';
    }
}
