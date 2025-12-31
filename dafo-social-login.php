<?php
/**
 * Plugin Name: DAFO Social Login
 * Plugin URI: https://github.com/dafogary/DAFO_WP_SocialLogin
 * Description: Social login integration for WordPress with support for Google, Facebook, BlueSky, Apple, LinkedIn, and Yahoo!
 * Version: 1.0.0
 * Author: DAFO
 * Author URI: https://github.com/dafogary
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dafo-social-login
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DAFO_SOCIAL_LOGIN_VERSION', '1.0.0');
define('DAFO_SOCIAL_LOGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DAFO_SOCIAL_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DAFO_SOCIAL_LOGIN_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/class-dafo-social-login.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/class-dafo-social-login-admin.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/class-dafo-social-login-frontend.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/class-dafo-social-login-oauth.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/providers/class-dafo-oauth-provider.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/providers/class-dafo-google-provider.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/providers/class-dafo-facebook-provider.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/providers/class-dafo-bluesky-provider.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/providers/class-dafo-apple-provider.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/providers/class-dafo-linkedin-provider.php';
require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/providers/class-dafo-yahoo-provider.php';

// Initialize the plugin
function dafo_social_login_init() {
    $plugin = new DAFO_Social_Login();
    $plugin->run();
}
add_action('plugins_loaded', 'dafo_social_login_init');

// Activation hook
register_activation_hook(__FILE__, 'dafo_social_login_activate');
function dafo_social_login_activate() {
    require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/class-dafo-social-login-activator.php';
    DAFO_Social_Login_Activator::activate();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'dafo_social_login_deactivate');
function dafo_social_login_deactivate() {
    require_once DAFO_SOCIAL_LOGIN_PLUGIN_DIR . 'includes/class-dafo-social-login-deactivator.php';
    DAFO_Social_Login_Deactivator::deactivate();
}
