# DAFO Social Login for WordPress

A comprehensive social login plugin for WordPress that enables users to login with Google, Facebook, BlueSky, Apple, LinkedIn, and Yahoo! accounts. Includes WooCommerce integration support.

## Features

- 🔐 **Multiple OAuth Providers**: Support for 6 major social platforms
  - Google OAuth 2.0
  - Facebook Login
  - BlueSky OAuth
  - Apple Sign In
  - LinkedIn OAuth 2.0
  - Yahoo OAuth 2.0

- 🎨 **Easy Integration**: 
  - Automatic integration with WordPress login page
  - WooCommerce login page support
  - Shortcode support `[dafo_social_login]` for custom placement
  
- 🛡️ **Secure**:
  - CSRF protection with nonces
  - Secure OAuth 2.0 implementation
  - Sanitized inputs and outputs

- 👥 **User Management**:
  - Automatic WordPress user creation
  - Link social accounts to existing users
  - Store social profile data as user meta

## Installation

1. Upload the plugin files to the `/wp-content/plugins/dafo-social-login` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Settings > Social Login to configure your OAuth credentials

## Configuration

### General Setup

After activation, go to **Settings > Social Login** in your WordPress admin panel.

### Provider Setup

For each provider you want to enable:

1. **Enable the provider** by checking the enable checkbox
2. **Enter your OAuth credentials** (Client ID, Client Secret, etc.)
3. **Configure your OAuth app** with the redirect URI shown on the settings page

### Getting OAuth Credentials

#### Google
1. Go to [Google Cloud Console](https://console.developers.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add the redirect URI from the plugin settings
6. Copy Client ID and Client Secret to plugin settings

#### Facebook
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app or select existing one
3. Add Facebook Login product
4. Configure OAuth Redirect URIs with the URL from plugin settings
5. Copy App ID and App Secret to plugin settings

#### BlueSky
1. Register your application with BlueSky
2. Configure OAuth settings
3. Copy credentials to plugin settings

#### Apple
1. Go to [Apple Developer](https://developer.apple.com/)
2. Create a Services ID
3. Configure Sign In with Apple
4. Generate and download private key
5. Copy Service ID, Team ID, Key ID, and Private Key to plugin settings

#### LinkedIn
1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/)
2. Create a new app
3. Add OAuth 2.0 redirect URLs
4. Copy Client ID and Client Secret to plugin settings

#### Yahoo
1. Go to [Yahoo Developer Network](https://developer.yahoo.com/)
2. Create a new app
3. Configure OAuth settings
4. Copy Client ID and Client Secret to plugin settings

## Usage

### Default Integration

The plugin automatically adds social login buttons to:
- WordPress login page
- WooCommerce login page (if WooCommerce is active)

### Shortcode

Use the shortcode anywhere in your content:

```
[dafo_social_login]
```

### Template Integration

Add to your theme templates:

```php
<?php
if (function_exists('dafo_social_login_buttons')) {
    echo do_shortcode('[dafo_social_login]');
}
?>
```

## WooCommerce Integration

The plugin automatically integrates with WooCommerce if it's installed. Social login buttons will appear on:
- My Account login page
- Checkout page (for guest users)

## Security

- All OAuth implementations follow official provider guidelines
- CSRF protection using WordPress nonces
- Secure token handling
- Input sanitization and output escaping
- No passwords stored for social login users

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- SSL certificate (required by most OAuth providers)
- OpenSSL extension (for Apple Sign In)

## Database

The plugin creates a custom table `wp_dafo_social_login_connections` to store social login connections and stores additional user meta data.

### User Meta Keys
- `dafo_social_login_{provider}` - Provider user ID
- `dafo_social_login_{provider}_data` - Full provider user data

## Uninstallation

When you uninstall the plugin:
- All plugin options are removed
- User meta data is cleaned up
- Custom database table is dropped

## Support

For issues, questions, or contributions, please visit:
[https://github.com/dafogary/DAFO_WP_SocialLogin](https://github.com/dafogary/DAFO_WP_SocialLogin)

## License

GPL v2 or later

## Changelog

### 1.0.0
- Initial release
- Google OAuth integration
- Facebook OAuth integration
- BlueSky OAuth integration
- Apple Sign In integration
- LinkedIn OAuth integration
- Yahoo OAuth integration
- WooCommerce support
- Admin settings interface
- Shortcode support