<?php
/**
 * Admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Social_Login_Admin {
    
    public function add_admin_menu() {
        add_options_page(
            __('DAFO Social Login Settings', 'dafo-social-login'),
            __('Social Login', 'dafo-social-login'),
            'manage_options',
            'dafo-social-login',
            array($this, 'display_admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('dafo_social_login_settings', 'dafo_social_login_settings', array($this, 'sanitize_settings'));
        
        // General settings section
        add_settings_section(
            'dafo_social_login_general',
            __('General Settings', 'dafo-social-login'),
            array($this, 'general_section_callback'),
            'dafo-social-login'
        );
        
        // Google settings
        add_settings_section(
            'dafo_social_login_google',
            __('Google OAuth Settings', 'dafo-social-login'),
            array($this, 'google_section_callback'),
            'dafo-social-login'
        );
        
        add_settings_field(
            'google_enabled',
            __('Enable Google Login', 'dafo-social-login'),
            array($this, 'checkbox_field'),
            'dafo-social-login',
            'dafo_social_login_google',
            array('name' => 'google_enabled')
        );
        
        add_settings_field(
            'google_client_id',
            __('Google Client ID', 'dafo-social-login'),
            array($this, 'text_field'),
            'dafo-social-login',
            'dafo_social_login_google',
            array('name' => 'google_client_id')
        );
        
        add_settings_field(
            'google_client_secret',
            __('Google Client Secret', 'dafo-social-login'),
            array($this, 'password_field'),
            'dafo-social-login',
            'dafo_social_login_google',
            array('name' => 'google_client_secret')
        );
        
        // Facebook settings
        add_settings_section(
            'dafo_social_login_facebook',
            __('Facebook OAuth Settings', 'dafo-social-login'),
            array($this, 'facebook_section_callback'),
            'dafo-social-login'
        );
        
        add_settings_field(
            'facebook_enabled',
            __('Enable Facebook Login', 'dafo-social-login'),
            array($this, 'checkbox_field'),
            'dafo-social-login',
            'dafo_social_login_facebook',
            array('name' => 'facebook_enabled')
        );
        
        add_settings_field(
            'facebook_app_id',
            __('Facebook App ID', 'dafo-social-login'),
            array($this, 'text_field'),
            'dafo-social-login',
            'dafo_social_login_facebook',
            array('name' => 'facebook_app_id')
        );
        
        add_settings_field(
            'facebook_app_secret',
            __('Facebook App Secret', 'dafo-social-login'),
            array($this, 'password_field'),
            'dafo-social-login',
            'dafo_social_login_facebook',
            array('name' => 'facebook_app_secret')
        );
        
        // BlueSky settings
        add_settings_section(
            'dafo_social_login_bluesky',
            __('BlueSky OAuth Settings', 'dafo-social-login'),
            array($this, 'bluesky_section_callback'),
            'dafo-social-login'
        );
        
        add_settings_field(
            'bluesky_enabled',
            __('Enable BlueSky Login', 'dafo-social-login'),
            array($this, 'checkbox_field'),
            'dafo-social-login',
            'dafo_social_login_bluesky',
            array('name' => 'bluesky_enabled')
        );
        
        add_settings_field(
            'bluesky_client_id',
            __('BlueSky Client ID', 'dafo-social-login'),
            array($this, 'text_field'),
            'dafo-social-login',
            'dafo_social_login_bluesky',
            array('name' => 'bluesky_client_id')
        );
        
        add_settings_field(
            'bluesky_client_secret',
            __('BlueSky Client Secret', 'dafo-social-login'),
            array($this, 'password_field'),
            'dafo-social-login',
            'dafo_social_login_bluesky',
            array('name' => 'bluesky_client_secret')
        );
        
        // Apple settings
        add_settings_section(
            'dafo_social_login_apple',
            __('Apple Sign In Settings', 'dafo-social-login'),
            array($this, 'apple_section_callback'),
            'dafo-social-login'
        );
        
        add_settings_field(
            'apple_enabled',
            __('Enable Apple Sign In', 'dafo-social-login'),
            array($this, 'checkbox_field'),
            'dafo-social-login',
            'dafo_social_login_apple',
            array('name' => 'apple_enabled')
        );
        
        add_settings_field(
            'apple_client_id',
            __('Apple Service ID', 'dafo-social-login'),
            array($this, 'text_field'),
            'dafo-social-login',
            'dafo_social_login_apple',
            array('name' => 'apple_client_id')
        );
        
        add_settings_field(
            'apple_team_id',
            __('Apple Team ID', 'dafo-social-login'),
            array($this, 'text_field'),
            'dafo-social-login',
            'dafo_social_login_apple',
            array('name' => 'apple_team_id')
        );
        
        add_settings_field(
            'apple_key_id',
            __('Apple Key ID', 'dafo-social-login'),
            array($this, 'text_field'),
            'dafo-social-login',
            'dafo_social_login_apple',
            array('name' => 'apple_key_id')
        );
        
        add_settings_field(
            'apple_private_key',
            __('Apple Private Key', 'dafo-social-login'),
            array($this, 'textarea_field'),
            'dafo-social-login',
            'dafo_social_login_apple',
            array('name' => 'apple_private_key')
        );
        
        // LinkedIn settings
        add_settings_section(
            'dafo_social_login_linkedin',
            __('LinkedIn OAuth Settings', 'dafo-social-login'),
            array($this, 'linkedin_section_callback'),
            'dafo-social-login'
        );
        
        add_settings_field(
            'linkedin_enabled',
            __('Enable LinkedIn Login', 'dafo-social-login'),
            array($this, 'checkbox_field'),
            'dafo-social-login',
            'dafo_social_login_linkedin',
            array('name' => 'linkedin_enabled')
        );
        
        add_settings_field(
            'linkedin_client_id',
            __('LinkedIn Client ID', 'dafo-social-login'),
            array($this, 'text_field'),
            'dafo-social-login',
            'dafo_social_login_linkedin',
            array('name' => 'linkedin_client_id')
        );
        
        add_settings_field(
            'linkedin_client_secret',
            __('LinkedIn Client Secret', 'dafo-social-login'),
            array($this, 'password_field'),
            'dafo-social-login',
            'dafo_social_login_linkedin',
            array('name' => 'linkedin_client_secret')
        );
        
        // Yahoo settings
        add_settings_section(
            'dafo_social_login_yahoo',
            __('Yahoo OAuth Settings', 'dafo-social-login'),
            array($this, 'yahoo_section_callback'),
            'dafo-social-login'
        );
        
        add_settings_field(
            'yahoo_enabled',
            __('Enable Yahoo Login', 'dafo-social-login'),
            array($this, 'checkbox_field'),
            'dafo-social-login',
            'dafo_social_login_yahoo',
            array('name' => 'yahoo_enabled')
        );
        
        add_settings_field(
            'yahoo_client_id',
            __('Yahoo Client ID', 'dafo-social-login'),
            array($this, 'text_field'),
            'dafo-social-login',
            'dafo_social_login_yahoo',
            array('name' => 'yahoo_client_id')
        );
        
        add_settings_field(
            'yahoo_client_secret',
            __('Yahoo Client Secret', 'dafo-social-login'),
            array($this, 'password_field'),
            'dafo-social-login',
            'dafo_social_login_yahoo',
            array('name' => 'yahoo_client_secret')
        );
    }
    
    public function general_section_callback() {
        echo '<p>' . __('Configure general social login settings.', 'dafo-social-login') . '</p>';
    }
    
    public function google_section_callback() {
        echo '<p>' . __('Get your Google OAuth credentials from <a href="https://console.developers.google.com/" target="_blank">Google Developer Console</a>.', 'dafo-social-login') . '</p>';
        echo '<p><strong>' . __('Redirect URI:', 'dafo-social-login') . '</strong> ' . home_url('/dafo-social-login/callback/google/') . '</p>';
    }
    
    public function facebook_section_callback() {
        echo '<p>' . __('Get your Facebook App credentials from <a href="https://developers.facebook.com/" target="_blank">Facebook Developers</a>.', 'dafo-social-login') . '</p>';
        echo '<p><strong>' . __('Redirect URI:', 'dafo-social-login') . '</strong> ' . home_url('/dafo-social-login/callback/facebook/') . '</p>';
    }
    
    public function bluesky_section_callback() {
        echo '<p>' . __('Configure BlueSky OAuth credentials.', 'dafo-social-login') . '</p>';
        echo '<p><strong>' . __('Redirect URI:', 'dafo-social-login') . '</strong> ' . home_url('/dafo-social-login/callback/bluesky/') . '</p>';
    }
    
    public function apple_section_callback() {
        echo '<p>' . __('Get your Apple Sign In credentials from <a href="https://developer.apple.com/" target="_blank">Apple Developer</a>.', 'dafo-social-login') . '</p>';
        echo '<p><strong>' . __('Redirect URI:', 'dafo-social-login') . '</strong> ' . home_url('/dafo-social-login/callback/apple/') . '</p>';
    }
    
    public function linkedin_section_callback() {
        echo '<p>' . __('Get your LinkedIn OAuth credentials from <a href="https://www.linkedin.com/developers/" target="_blank">LinkedIn Developers</a>.', 'dafo-social-login') . '</p>';
        echo '<p><strong>' . __('Redirect URI:', 'dafo-social-login') . '</strong> ' . home_url('/dafo-social-login/callback/linkedin/') . '</p>';
    }
    
    public function yahoo_section_callback() {
        echo '<p>' . __('Get your Yahoo OAuth credentials from <a href="https://developer.yahoo.com/" target="_blank">Yahoo Developer Network</a>.', 'dafo-social-login') . '</p>';
        echo '<p><strong>' . __('Redirect URI:', 'dafo-social-login') . '</strong> ' . home_url('/dafo-social-login/callback/yahoo/') . '</p>';
    }
    
    public function checkbox_field($args) {
        $options = get_option('dafo_social_login_settings', array());
        $value = isset($options[$args['name']]) ? $options[$args['name']] : '';
        ?>
        <input type="checkbox" 
               name="dafo_social_login_settings[<?php echo esc_attr($args['name']); ?>]" 
               value="1" 
               <?php checked($value, '1'); ?> />
        <?php
    }
    
    public function text_field($args) {
        $options = get_option('dafo_social_login_settings', array());
        $value = isset($options[$args['name']]) ? $options[$args['name']] : '';
        ?>
        <input type="text" 
               name="dafo_social_login_settings[<?php echo esc_attr($args['name']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" />
        <?php
    }
    
    public function password_field($args) {
        $options = get_option('dafo_social_login_settings', array());
        $value = isset($options[$args['name']]) ? $options[$args['name']] : '';
        ?>
        <input type="password" 
               name="dafo_social_login_settings[<?php echo esc_attr($args['name']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text" />
        <?php
    }
    
    public function textarea_field($args) {
        $options = get_option('dafo_social_login_settings', array());
        $value = isset($options[$args['name']]) ? $options[$args['name']] : '';
        ?>
        <textarea name="dafo_social_login_settings[<?php echo esc_attr($args['name']); ?>]" 
                  rows="5" 
                  class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <?php
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['google_enabled'])) {
            $sanitized['google_enabled'] = '1';
        }
        if (isset($input['google_client_id'])) {
            $sanitized['google_client_id'] = sanitize_text_field($input['google_client_id']);
        }
        if (isset($input['google_client_secret'])) {
            $sanitized['google_client_secret'] = sanitize_text_field($input['google_client_secret']);
        }
        
        if (isset($input['facebook_enabled'])) {
            $sanitized['facebook_enabled'] = '1';
        }
        if (isset($input['facebook_app_id'])) {
            $sanitized['facebook_app_id'] = sanitize_text_field($input['facebook_app_id']);
        }
        if (isset($input['facebook_app_secret'])) {
            $sanitized['facebook_app_secret'] = sanitize_text_field($input['facebook_app_secret']);
        }
        
        if (isset($input['bluesky_enabled'])) {
            $sanitized['bluesky_enabled'] = '1';
        }
        if (isset($input['bluesky_client_id'])) {
            $sanitized['bluesky_client_id'] = sanitize_text_field($input['bluesky_client_id']);
        }
        if (isset($input['bluesky_client_secret'])) {
            $sanitized['bluesky_client_secret'] = sanitize_text_field($input['bluesky_client_secret']);
        }
        
        if (isset($input['apple_enabled'])) {
            $sanitized['apple_enabled'] = '1';
        }
        if (isset($input['apple_client_id'])) {
            $sanitized['apple_client_id'] = sanitize_text_field($input['apple_client_id']);
        }
        if (isset($input['apple_team_id'])) {
            $sanitized['apple_team_id'] = sanitize_text_field($input['apple_team_id']);
        }
        if (isset($input['apple_key_id'])) {
            $sanitized['apple_key_id'] = sanitize_text_field($input['apple_key_id']);
        }
        if (isset($input['apple_private_key'])) {
            $sanitized['apple_private_key'] = sanitize_textarea_field($input['apple_private_key']);
        }
        
        if (isset($input['linkedin_enabled'])) {
            $sanitized['linkedin_enabled'] = '1';
        }
        if (isset($input['linkedin_client_id'])) {
            $sanitized['linkedin_client_id'] = sanitize_text_field($input['linkedin_client_id']);
        }
        if (isset($input['linkedin_client_secret'])) {
            $sanitized['linkedin_client_secret'] = sanitize_text_field($input['linkedin_client_secret']);
        }
        
        if (isset($input['yahoo_enabled'])) {
            $sanitized['yahoo_enabled'] = '1';
        }
        if (isset($input['yahoo_client_id'])) {
            $sanitized['yahoo_client_id'] = sanitize_text_field($input['yahoo_client_id']);
        }
        if (isset($input['yahoo_client_secret'])) {
            $sanitized['yahoo_client_secret'] = sanitize_text_field($input['yahoo_client_secret']);
        }
        
        return $sanitized;
    }
    
    public function display_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_GET['settings-updated'])) {
            add_settings_error('dafo_social_login_messages', 'dafo_social_login_message', __('Settings Saved', 'dafo-social-login'), 'updated');
        }
        
        settings_errors('dafo_social_login_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('dafo_social_login_settings');
                do_settings_sections('dafo-social-login');
                submit_button(__('Save Settings', 'dafo-social-login'));
                ?>
            </form>
        </div>
        <?php
    }
    
    public function enqueue_styles($hook) {
        if ('settings_page_dafo-social-login' !== $hook) {
            return;
        }
        
        wp_enqueue_style('dafo-social-login-admin', DAFO_SOCIAL_LOGIN_PLUGIN_URL . 'assets/css/admin.css', array(), DAFO_SOCIAL_LOGIN_VERSION);
    }
}
