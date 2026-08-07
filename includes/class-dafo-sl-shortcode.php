<?php
/**
 * Shortcode Class - Login/Register Form
 *
 * Provides [dafo_login_register] shortcode for front-end login and registration.
 *
 * Attributes:
 *   mode     - 'both' (default), 'login', or 'register'
 *   redirect - URL to redirect to after login/register (defaults to plugin setting)
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_Shortcode {

    public function __construct() {
        add_shortcode('dafo_login_register', array($this, 'render'));

        // AJAX handlers (non-logged-in users only)
        add_action('wp_ajax_nopriv_dafo_sl_email_login',    array($this, 'handle_email_login'));
        add_action('wp_ajax_nopriv_dafo_sl_email_register', array($this, 'handle_email_register'));

        // Allow logged-in users to call the register handler too (edge-case guard)
        add_action('wp_ajax_dafo_sl_email_login',    array($this, 'handle_email_login'));
        add_action('wp_ajax_dafo_sl_email_register', array($this, 'handle_email_register'));
    }

    /* -----------------------------------------------------------------------
     * Shortcode output
     * --------------------------------------------------------------------- */

    public function render($atts) {
        $atts = shortcode_atts(
            array(
                'mode'     => 'both',   // 'login' | 'register' | 'both'
                'redirect' => '',
            ),
            $atts,
            'dafo_login_register'
        );

        $mode     = in_array($atts['mode'], array('login', 'register', 'both'), true) ? $atts['mode'] : 'both';
        $redirect = !empty($atts['redirect']) ? esc_url_raw($atts['redirect']) : $this->default_redirect();

        // Already logged in
        if (is_user_logged_in()) {
            return '<div class="dafo-sc-logged-in">'
                . '<p>' . esc_html__('You are already logged in.', 'dafo-social-login') . '</p>'
                . '<a href="' . esc_url(wp_logout_url(get_permalink())) . '">'
                . esc_html__('Log out', 'dafo-social-login')
                . '</a>'
                . '</div>';
        }

        // Registration disabled site-wide?
        $registration_open = (bool) get_option('users_can_register');

        ob_start();
        ?>
        <div class="dafo-sc-wrapper" id="dafo-sc-wrapper">

            <?php $this->maybe_render_messages(); ?>

            <?php if ($mode === 'both') : ?>

                <div class="dafo-sc-tabs">
                    <button class="dafo-sc-tab active" data-tab="login">
                        <?php esc_html_e('Login', 'dafo-social-login'); ?>
                    </button>
                    <?php if ($registration_open) : ?>
                    <button class="dafo-sc-tab" data-tab="register">
                        <?php esc_html_e('Register', 'dafo-social-login'); ?>
                    </button>
                    <?php endif; ?>
                </div>

                <div class="dafo-sc-panel active" id="dafo-sc-panel-login">
                    <?php $this->render_login_form($redirect); ?>
                    <?php $this->render_social_buttons($redirect); ?>
                </div>

                <?php if ($registration_open) : ?>
                <div class="dafo-sc-panel" id="dafo-sc-panel-register">
                    <?php $this->render_register_form($redirect); ?>
                    <?php $this->render_social_buttons($redirect); ?>
                </div>
                <?php endif; ?>

            <?php elseif ($mode === 'login') : ?>

                <?php $this->render_login_form($redirect); ?>
                <?php $this->render_social_buttons($redirect); ?>

            <?php elseif ($mode === 'register') : ?>

                <?php if ($registration_open) : ?>
                    <?php $this->render_register_form($redirect); ?>
                    <?php $this->render_social_buttons($redirect); ?>
                <?php else : ?>
                    <p><?php esc_html_e('User registration is currently disabled.', 'dafo-social-login'); ?></p>
                <?php endif; ?>

            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }

    /* -----------------------------------------------------------------------
     * Form partials
     * --------------------------------------------------------------------- */

    private function render_login_form($redirect) {
        $nonce = wp_create_nonce('dafo_sl_email_login');
        ?>
        <form class="dafo-sc-form" id="dafo-sc-login-form" novalidate>
            <input type="hidden" name="action"   value="dafo_sl_email_login">
            <input type="hidden" name="nonce"    value="<?php echo esc_attr($nonce); ?>">
            <input type="hidden" name="redirect" value="<?php echo esc_attr($redirect); ?>">

            <div class="dafo-sc-field">
                <label for="dafo-login-email">
                    <?php esc_html_e('Email or Username', 'dafo-social-login'); ?>
                </label>
                <input type="text" id="dafo-login-email" name="login"
                    autocomplete="username" required>
            </div>

            <div class="dafo-sc-field">
                <label for="dafo-login-password">
                    <?php esc_html_e('Password', 'dafo-social-login'); ?>
                </label>
                <input type="password" id="dafo-login-password" name="password"
                    autocomplete="current-password" required>
            </div>

            <div class="dafo-sc-field dafo-sc-remember">
                <label>
                    <input type="checkbox" name="remember" value="1">
                    <?php esc_html_e('Remember me', 'dafo-social-login'); ?>
                </label>
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="dafo-sc-lost-password">
                    <?php esc_html_e('Lost your password?', 'dafo-social-login'); ?>
                </a>
            </div>

            <div class="dafo-sc-messages" aria-live="polite"></div>

            <button type="submit" class="dafo-sc-submit">
                <?php esc_html_e('Log In', 'dafo-social-login'); ?>
            </button>
        </form>
        <?php
    }

    private function render_register_form($redirect) {
        $nonce = wp_create_nonce('dafo_sl_email_register');
        ?>
        <form class="dafo-sc-form" id="dafo-sc-register-form" novalidate>
            <input type="hidden" name="action"   value="dafo_sl_email_register">
            <input type="hidden" name="nonce"    value="<?php echo esc_attr($nonce); ?>">
            <input type="hidden" name="redirect" value="<?php echo esc_attr($redirect); ?>">

            <div class="dafo-sc-field">
                <label for="dafo-reg-username">
                    <?php esc_html_e('Username', 'dafo-social-login'); ?>
                </label>
                <input type="text" id="dafo-reg-username" name="username"
                    autocomplete="username" required>
            </div>

            <div class="dafo-sc-field">
                <label for="dafo-reg-email">
                    <?php esc_html_e('Email Address', 'dafo-social-login'); ?>
                </label>
                <input type="email" id="dafo-reg-email" name="email"
                    autocomplete="email" required>
            </div>

            <div class="dafo-sc-field">
                <label for="dafo-reg-password">
                    <?php esc_html_e('Password', 'dafo-social-login'); ?>
                </label>
                <input type="password" id="dafo-reg-password" name="password"
                    autocomplete="new-password" required minlength="8">
            </div>

            <div class="dafo-sc-field">
                <label for="dafo-reg-password2">
                    <?php esc_html_e('Confirm Password', 'dafo-social-login'); ?>
                </label>
                <input type="password" id="dafo-reg-password2" name="password2"
                    autocomplete="new-password" required minlength="8">
            </div>

            <div class="dafo-sc-messages" aria-live="polite"></div>

            <button type="submit" class="dafo-sc-submit">
                <?php esc_html_e('Create Account', 'dafo-social-login'); ?>
            </button>
        </form>
        <?php
    }

    private function render_social_buttons($redirect) {
        $settings         = get_option('dafo_social_login_settings', array());
        $enabled_providers = isset($settings['enabled_providers']) ? $settings['enabled_providers'] : array();

        if (empty($enabled_providers)) {
            return;
        }

        $oauth_handler = new DAFO_SL_OAuth_Handler();

        echo '<div class="dafo-social-login-container dafo-sc-social">';
        echo '<div class="dafo-social-login-separator"><span>'
            . esc_html__('Or continue with', 'dafo-social-login')
            . '</span></div>';
        echo '<div class="dafo-social-login-buttons">';

        foreach ($enabled_providers as $provider) {
            $auth_url = $oauth_handler->get_auth_url($provider);

            if (empty($auth_url)) {
                continue;
            }

            // Append redirect param so OAuth callback can honour it
            if (!empty($redirect)) {
                $auth_url = add_query_arg('sc_redirect', urlencode($redirect), $auth_url);
            }

            $provider_name = ucfirst($provider);
            $button_class  = 'dafo-social-login-button dafo-social-' . esc_attr($provider);

            echo '<a href="' . esc_url($auth_url) . '" class="' . esc_attr($button_class) . '">';
            echo $this->get_provider_icon($provider);
            echo '<span>' . sprintf(
                /* translators: %s: provider name */
                esc_html__('Continue with %s', 'dafo-social-login'),
                esc_html($provider_name)
            ) . '</span>';
            echo '</a>';
        }

        echo '</div>';
        echo '</div>';
    }

    /* -----------------------------------------------------------------------
     * AJAX: Email Login
     * --------------------------------------------------------------------- */

    public function handle_email_login() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'dafo_sl_email_login')) {
            wp_send_json_error(array('message' => __('Security check failed. Please refresh the page and try again.', 'dafo-social-login')));
        }

        // Already logged in
        if (is_user_logged_in()) {
            wp_send_json_success(array('redirect' => $this->safe_redirect_url($_POST)));
        }

        $login    = isset($_POST['login'])    ? sanitize_text_field(wp_unslash($_POST['login']))    : '';
        $password = isset($_POST['password']) ? wp_unslash($_POST['password'])                      : '';
        $remember = !empty($_POST['remember']);

        if (empty($login) || empty($password)) {
            wp_send_json_error(array('message' => __('Please enter your username/email and password.', 'dafo-social-login')));
        }

        $credentials = array(
            'user_login'    => $login,
            'user_password' => $password,
            'remember'      => $remember,
        );

        $user = wp_signon($credentials, is_ssl());

        if (is_wp_error($user)) {
            // Generic message — don't reveal whether the username or password was wrong
            wp_send_json_error(array('message' => __('Invalid username/email or password.', 'dafo-social-login')));
        }

        wp_send_json_success(array('redirect' => $this->safe_redirect_url($_POST)));
    }

    /* -----------------------------------------------------------------------
     * AJAX: Email Registration
     * --------------------------------------------------------------------- */

    public function handle_email_register() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'dafo_sl_email_register')) {
            wp_send_json_error(array('message' => __('Security check failed. Please refresh the page and try again.', 'dafo-social-login')));
        }

        // Registration must be open
        if (!get_option('users_can_register')) {
            wp_send_json_error(array('message' => __('User registration is currently disabled.', 'dafo-social-login')));
        }

        // Already logged in
        if (is_user_logged_in()) {
            wp_send_json_success(array('redirect' => $this->safe_redirect_url($_POST)));
        }

        $username  = isset($_POST['username'])  ? sanitize_user(wp_unslash($_POST['username']))  : '';
        $email     = isset($_POST['email'])     ? sanitize_email(wp_unslash($_POST['email']))     : '';
        $password  = isset($_POST['password'])  ? wp_unslash($_POST['password'])                  : '';
        $password2 = isset($_POST['password2']) ? wp_unslash($_POST['password2'])                 : '';

        // --- Validation ---
        if (empty($username)) {
            wp_send_json_error(array('message' => __('Please enter a username.', 'dafo-social-login')));
        }

        if (!validate_username($username)) {
            wp_send_json_error(array('message' => __('This username is invalid. Please use only letters, numbers, spaces, underscores, hyphens, periods and @ symbols.', 'dafo-social-login')));
        }

        if (username_exists($username)) {
            wp_send_json_error(array('message' => __('This username is already taken. Please choose a different one.', 'dafo-social-login')));
        }

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => __('Please enter a valid email address.', 'dafo-social-login')));
        }

        if (email_exists($email)) {
            wp_send_json_error(array('message' => __('An account with this email address already exists.', 'dafo-social-login')));
        }

        if (strlen($password) < 8) {
            wp_send_json_error(array('message' => __('Password must be at least 8 characters.', 'dafo-social-login')));
        }

        if ($password !== $password2) {
            wp_send_json_error(array('message' => __('Passwords do not match.', 'dafo-social-login')));
        }

        // --- Create user ---
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }

        // Send new-user notification emails
        wp_new_user_notification($user_id, null, 'both');

        // Log the user in immediately
        $user = get_user_by('id', $user_id);
        wp_set_auth_cookie($user_id, false);
        do_action('wp_login', $user->user_login, $user);

        wp_send_json_success(array(
            'redirect' => $this->safe_redirect_url($_POST),
            'message'  => __('Account created! You are now logged in.', 'dafo-social-login'),
        ));
    }

    /* -----------------------------------------------------------------------
     * Helpers
     * --------------------------------------------------------------------- */

    private function maybe_render_messages() {
        if (isset($_GET['dafo_sc_error'])) {
            $msg = sanitize_text_field(wp_unslash($_GET['dafo_sc_error']));
            echo '<div class="dafo-sc-notice dafo-sc-notice--error">' . esc_html($msg) . '</div>';
        }
        if (isset($_GET['dafo_sc_success'])) {
            $msg = sanitize_text_field(wp_unslash($_GET['dafo_sc_success']));
            echo '<div class="dafo-sc-notice dafo-sc-notice--success">' . esc_html($msg) . '</div>';
        }
    }

    private function default_redirect() {
        $settings = get_option('dafo_social_login_settings', array());
        return !empty($settings['redirect_after_login']) ? $settings['redirect_after_login'] : home_url('/');
    }

    private function safe_redirect_url($post_data) {
        $redirect = isset($post_data['redirect']) ? esc_url_raw(wp_unslash($post_data['redirect'])) : '';

        // Only allow redirects to the same host
        if (!empty($redirect) && wp_validate_redirect($redirect, false)) {
            return $redirect;
        }

        return $this->default_redirect();
    }

    private function get_provider_icon($provider) {
        $icons = array(
            'linkedin'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>',
            'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>',
            'google'    => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 11v2.4h3.97c-.16 1.029-1.2 3.02-3.97 3.02-2.39 0-4.34-1.979-4.34-4.42 0-2.44 1.95-4.42 4.34-4.42 1.36 0 2.27.58 2.79 1.08l1.9-1.83c-1.22-1.14-2.8-1.83-4.69-1.83-3.87 0-7 3.13-7 7s3.13 7 7 7c4.04 0 6.721-2.84 6.721-6.84 0-.46-.051-.81-.111-1.16h-6.61zm0 0 17 2h-3v3h-2v-3h-3v-2h3v-3h2v3h3v2z" fill-rule="evenodd" clip-rule="evenodd"/></svg>',
            'apple'     => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 17.607c-.786 2.28-3.139 6.317-5.563 6.361-1.608.031-2.125-.953-3.963-.953-1.837 0-2.412.923-3.932.983-2.572.099-6.542-5.827-6.542-10.995 0-4.747 3.308-7.1 6.198-7.143 1.55-.028 3.014 1.045 3.959 1.045.949 0 2.727-1.29 4.596-1.101.782.033 2.979.315 4.389 2.377-3.741 2.442-3.158 7.549.858 9.426zm-5.222-17.607c-2.826.114-5.132 3.079-4.81 5.531 2.612.203 5.118-2.725 4.81-5.531z"/></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
            'bluesky'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 10.5c-1.2-3.6-3.6-7.5-6-7.5s-3.6 2.4-3.6 4.8c0 3.6 2.4 6 6 7.2-3.6 1.2-6 3.6-6 7.2 0 2.4 1.2 4.8 3.6 4.8s4.8-3.9 6-7.5c1.2 3.6 3.6 7.5 6 7.5s3.6-2.4 3.6-4.8c0-3.6-2.4-6-6-7.2 3.6-1.2 6-3.6 6-7.2 0-2.4-1.2-4.8-3.6-4.8s-4.8 3.9-6 7.5z"/></svg>',
        );

        return isset($icons[$provider]) ? $icons[$provider] : '';
    }
}
