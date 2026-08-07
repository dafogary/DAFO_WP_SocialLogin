<?php
/**
 * Admin Settings Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_SL_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Social Login Settings', 'dafo-social-login'),
            __('Social Login', 'dafo-social-login'),
            'manage_options',
            'dafo-social-login',
            array($this, 'render_settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('dafo_social_login_settings', 'dafo_social_login_settings', array($this, 'sanitize_settings'));
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Enabled providers
        $sanitized['enabled_providers'] = isset($input['enabled_providers']) && is_array($input['enabled_providers']) 
            ? array_map('sanitize_text_field', $input['enabled_providers']) 
            : array();
        
        // LinkedIn
        $sanitized['linkedin_client_id'] = isset($input['linkedin_client_id']) ? sanitize_text_field($input['linkedin_client_id']) : '';
        $sanitized['linkedin_client_secret'] = isset($input['linkedin_client_secret']) ? sanitize_text_field($input['linkedin_client_secret']) : '';
        
        // Facebook
        $sanitized['facebook_app_id'] = isset($input['facebook_app_id']) ? sanitize_text_field($input['facebook_app_id']) : '';
        $sanitized['facebook_app_secret'] = isset($input['facebook_app_secret']) ? sanitize_text_field($input['facebook_app_secret']) : '';
        
        // Google
        $sanitized['google_client_id'] = isset($input['google_client_id']) ? sanitize_text_field($input['google_client_id']) : '';
        $sanitized['google_client_secret'] = isset($input['google_client_secret']) ? sanitize_text_field($input['google_client_secret']) : '';
        
        // Apple
        $sanitized['apple_client_id'] = isset($input['apple_client_id']) ? sanitize_text_field($input['apple_client_id']) : '';
        $sanitized['apple_team_id'] = isset($input['apple_team_id']) ? sanitize_text_field($input['apple_team_id']) : '';
        $sanitized['apple_key_id'] = isset($input['apple_key_id']) ? sanitize_text_field($input['apple_key_id']) : '';
        $sanitized['apple_private_key'] = isset($input['apple_private_key']) ? $input['apple_private_key'] : '';
        
        // Instagram
        $sanitized['instagram_client_id'] = isset($input['instagram_client_id']) ? sanitize_text_field($input['instagram_client_id']) : '';
        $sanitized['instagram_client_secret'] = isset($input['instagram_client_secret']) ? sanitize_text_field($input['instagram_client_secret']) : '';
        
        // Bluesky
        $sanitized['bluesky_enabled'] = isset($input['bluesky_enabled']) ? (bool)$input['bluesky_enabled'] : false;

        // Compatibility mode
        $allowed_modes = array('standard', 'userswp', 'both');
        $sanitized['compatibility_mode'] = isset($input['compatibility_mode']) && in_array($input['compatibility_mode'], $allowed_modes, true)
            ? $input['compatibility_mode']
            : 'both';
        
        // General settings
        $sanitized['show_on_login'] = isset($input['show_on_login']) ? (bool)$input['show_on_login'] : false;
        $sanitized['show_on_register'] = isset($input['show_on_register']) ? (bool)$input['show_on_register'] : false;
        $sanitized['auto_create_account'] = isset($input['auto_create_account']) ? (bool)$input['auto_create_account'] : false;
        $sanitized['redirect_after_login'] = isset($input['redirect_after_login']) ? esc_url_raw($input['redirect_after_login']) : '';
        
        return $sanitized;
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_dafo-social-login') {
            return;
        }
        
        wp_enqueue_style(
            'dafo-social-login-admin',
            DAFO_SL_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            DAFO_SL_VERSION
        );
        
        wp_enqueue_script(
            'dafo-social-login-admin',
            DAFO_SL_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            DAFO_SL_VERSION,
            true
        );
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = get_option('dafo_social_login_settings', array());
        $enabled_providers = isset($settings['enabled_providers']) ? $settings['enabled_providers'] : array();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('dafo_social_login_settings'); ?>
            
            <form method="post" action="options.php">
                <?php settings_fields('dafo_social_login_settings'); ?>
                
                <h2 class="title"><?php _e('Enable Social Platforms', 'dafo-social-login'); ?></h2>
                <p><?php _e('Select which social platforms you want to enable for login:', 'dafo-social-login'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enabled Providers', 'dafo-social-login'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="dafo_social_login_settings[enabled_providers][]" value="linkedin" 
                                        <?php checked(in_array('linkedin', $enabled_providers)); ?>>
                                    <?php _e('LinkedIn', 'dafo-social-login'); ?>
                                </label><br>
                                
                                <label>
                                    <input type="checkbox" name="dafo_social_login_settings[enabled_providers][]" value="facebook" 
                                        <?php checked(in_array('facebook', $enabled_providers)); ?>>
                                    <?php _e('Facebook', 'dafo-social-login'); ?>
                                </label><br>
                                
                                <label>
                                    <input type="checkbox" name="dafo_social_login_settings[enabled_providers][]" value="google" 
                                        <?php checked(in_array('google', $enabled_providers)); ?>>
                                    <?php _e('Google', 'dafo-social-login'); ?>
                                </label><br>
                                
                                <label>
                                    <input type="checkbox" name="dafo_social_login_settings[enabled_providers][]" value="apple" 
                                        <?php checked(in_array('apple', $enabled_providers)); ?>>
                                    <?php _e('Apple', 'dafo-social-login'); ?>
                                </label><br>
                                
                                <label>
                                    <input type="checkbox" name="dafo_social_login_settings[enabled_providers][]" value="instagram" 
                                        <?php checked(in_array('instagram', $enabled_providers)); ?>>
                                    <?php _e('Instagram', 'dafo-social-login'); ?>
                                </label><br>
                                
                                <label>
                                    <input type="checkbox" name="dafo_social_login_settings[enabled_providers][]" value="bluesky" 
                                        <?php checked(in_array('bluesky', $enabled_providers)); ?>>
                                    <?php _e('Bluesky', 'dafo-social-login'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php _e('LinkedIn Settings', 'dafo-social-login'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="linkedin_client_id"><?php _e('Client ID', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="text" id="linkedin_client_id" name="dafo_social_login_settings[linkedin_client_id]" 
                                value="<?php echo esc_attr($settings['linkedin_client_id'] ?? ''); ?>" class="regular-text">
                            <p class="description"><?php _e('Get your LinkedIn Client ID from the LinkedIn Developer Console', 'dafo-social-login'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="linkedin_client_secret"><?php _e('Client Secret', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="password" id="linkedin_client_secret" name="dafo_social_login_settings[linkedin_client_secret]" 
                                value="<?php echo esc_attr($settings['linkedin_client_secret'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Redirect URI', 'dafo-social-login'); ?></th>
                        <td>
                            <code><?php echo home_url('/dafo-social-login/linkedin/callback'); ?></code>
                            <p class="description"><?php _e('Add this URL to your LinkedIn app settings', 'dafo-social-login'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php _e('Facebook Settings', 'dafo-social-login'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="facebook_app_id"><?php _e('App ID', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="text" id="facebook_app_id" name="dafo_social_login_settings[facebook_app_id]" 
                                value="<?php echo esc_attr($settings['facebook_app_id'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="facebook_app_secret"><?php _e('App Secret', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="password" id="facebook_app_secret" name="dafo_social_login_settings[facebook_app_secret]" 
                                value="<?php echo esc_attr($settings['facebook_app_secret'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Redirect URI', 'dafo-social-login'); ?></th>
                        <td>
                            <code><?php echo home_url('/dafo-social-login/facebook/callback'); ?></code>
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php _e('Google Settings', 'dafo-social-login'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="google_client_id"><?php _e('Client ID', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="text" id="google_client_id" name="dafo_social_login_settings[google_client_id]" 
                                value="<?php echo esc_attr($settings['google_client_id'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="google_client_secret"><?php _e('Client Secret', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="password" id="google_client_secret" name="dafo_social_login_settings[google_client_secret]" 
                                value="<?php echo esc_attr($settings['google_client_secret'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Redirect URI', 'dafo-social-login'); ?></th>
                        <td>
                            <code><?php echo home_url('/dafo-social-login/google/callback'); ?></code>
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php _e('Apple Settings', 'dafo-social-login'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="apple_client_id"><?php _e('Service ID', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="text" id="apple_client_id" name="dafo_social_login_settings[apple_client_id]" 
                                value="<?php echo esc_attr($settings['apple_client_id'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="apple_team_id"><?php _e('Team ID', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="text" id="apple_team_id" name="dafo_social_login_settings[apple_team_id]" 
                                value="<?php echo esc_attr($settings['apple_team_id'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="apple_key_id"><?php _e('Key ID', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="text" id="apple_key_id" name="dafo_social_login_settings[apple_key_id]" 
                                value="<?php echo esc_attr($settings['apple_key_id'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="apple_private_key"><?php _e('Private Key (.p8 file content)', 'dafo-social-login'); ?></label></th>
                        <td>
                            <textarea id="apple_private_key" name="dafo_social_login_settings[apple_private_key]" 
                                rows="8" class="large-text code"><?php echo esc_textarea($settings['apple_private_key'] ?? ''); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Redirect URI', 'dafo-social-login'); ?></th>
                        <td>
                            <code><?php echo home_url('/dafo-social-login/apple/callback'); ?></code>
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php _e('Instagram Settings', 'dafo-social-login'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="instagram_client_id"><?php _e('App ID', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="text" id="instagram_client_id" name="dafo_social_login_settings[instagram_client_id]" 
                                value="<?php echo esc_attr($settings['instagram_client_id'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="instagram_client_secret"><?php _e('App Secret', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="password" id="instagram_client_secret" name="dafo_social_login_settings[instagram_client_secret]" 
                                value="<?php echo esc_attr($settings['instagram_client_secret'] ?? ''); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Redirect URI', 'dafo-social-login'); ?></th>
                        <td>
                            <code><?php echo home_url('/dafo-social-login/instagram/callback'); ?></code>
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php _e('Bluesky Settings', 'dafo-social-login'); ?></h2>
                <p><?php _e('Bluesky uses decentralized authentication and does not require API credentials.', 'dafo-social-login'); ?></p>
                
                <h2 class="title"><?php _e('General Settings', 'dafo-social-login'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="compatibility_mode"><?php _e('Compatibility Mode', 'dafo-social-login'); ?></label></th>
                        <td>
                            <select id="compatibility_mode" name="dafo_social_login_settings[compatibility_mode]">
                                <option value="standard" <?php selected($settings['compatibility_mode'] ?? 'both', 'standard'); ?>><?php _e('Standard WordPress forms only', 'dafo-social-login'); ?></option>
                                <option value="userswp" <?php selected($settings['compatibility_mode'] ?? 'both', 'userswp'); ?>><?php _e('UsersWP forms only', 'dafo-social-login'); ?></option>
                                <option value="both" <?php selected($settings['compatibility_mode'] ?? 'both', 'both'); ?>><?php _e('Both standard WordPress and UsersWP', 'dafo-social-login'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose whether the social buttons attach to the default WordPress forms, UsersWP forms, or both.', 'dafo-social-login'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Display Options', 'dafo-social-login'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="dafo_social_login_settings[show_on_login]" value="1" 
                                        <?php checked($settings['show_on_login'] ?? true); ?>>
                                    <?php _e('Show on login page', 'dafo-social-login'); ?>
                                </label><br>
                                
                                <label>
                                    <input type="checkbox" name="dafo_social_login_settings[show_on_register]" value="1" 
                                        <?php checked($settings['show_on_register'] ?? true); ?>>
                                    <?php _e('Show on registration page', 'dafo-social-login'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Account Creation', 'dafo-social-login'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="dafo_social_login_settings[auto_create_account]" value="1" 
                                    <?php checked($settings['auto_create_account'] ?? true); ?>>
                                <?php _e('Automatically create account for new users', 'dafo-social-login'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="redirect_after_login"><?php _e('Redirect After Login', 'dafo-social-login'); ?></label></th>
                        <td>
                            <input type="url" id="redirect_after_login" name="dafo_social_login_settings[redirect_after_login]" 
                                value="<?php echo esc_url($settings['redirect_after_login'] ?? ''); ?>" class="regular-text">
                            <p class="description"><?php _e('Leave empty to redirect to admin dashboard', 'dafo-social-login'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <h2 class="title"><?php _e('Shortcode Usage', 'dafo-social-login'); ?></h2>
            <p><?php _e('Use the following shortcode to display social login buttons anywhere:', 'dafo-social-login'); ?></p>
            <p><code>[dafo_social_login]</code></p>
            <p class="description"><?php _e('The shortcode works independently of the compatibility mode setting.', 'dafo-social-login'); ?></p>
        </div>
        <?php
    }
}
