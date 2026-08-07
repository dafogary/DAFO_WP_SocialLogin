# DAFO Social Login - WordPress Plugin

A comprehensive WordPress plugin that enables social login functionality with multiple platforms including LinkedIn, Facebook, Google, Apple, Instagram, and Bluesky.

## Features

- **Multiple Social Platforms**: Support for 6 major social login providers
  - LinkedIn
  - Facebook
  - Google
  - Apple Sign In
  - Instagram
  - Bluesky
  
- **Admin Control**: Easy-to-use admin interface to enable/disable providers and configure API credentials

- **Flexible Integration**: 
  - Automatic display on WordPress login and registration pages
  - Automatic display on UsersWP login and registration forms
  - Switchable compatibility mode: Standard WordPress only, UsersWP only, or both
  - Shortcode support for custom placement: `[dafo_social_login]`
  
- **Smart User Management**:
  - Automatic account creation for new users
  - Links social accounts to existing WordPress users via email
  - Secure token storage and management
  
- **Customizable**:
  - Configure redirect URL after successful login
  - Choose where to display social login buttons
  - Control automatic account creation

## Installation

1. **Upload the plugin**:
   - Download or clone this repository
   - Upload the `DAFO_WP_SocialLogin` folder to `/wp-content/plugins/`
   - Or install via WordPress admin by uploading the zip file

2. **Activate the plugin**:
   - Go to WordPress Admin → Plugins
   - Find "DAFO Social Login" and click "Activate"

3. **Configure settings**:
   - Go to Settings → Social Login
   - Enable desired social platforms
   - Configure API credentials for each platform

## Configuration Guide

### LinkedIn Setup

1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/)
2. Create a new app or select existing app
3. Add redirect URI: `https://yoursite.com/dafo-social-login/linkedin/callback`
4. Copy Client ID and Client Secret to plugin settings
5. Request access to "Sign In with LinkedIn" product
6. Enable the LinkedIn provider in plugin settings

### Facebook Setup

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app or select existing app
3. Add "Facebook Login" product
4. Add redirect URI in Facebook Login Settings: `https://yoursite.com/dafo-social-login/facebook/callback`
5. Copy App ID and App Secret to plugin settings
6. Make app live (remove development mode)
7. Enable the Facebook provider in plugin settings

### Google Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing project
3. Enable "Google+ API"
4. Create OAuth 2.0 credentials (Web application)
5. Add authorized redirect URI: `https://yoursite.com/dafo-social-login/google/callback`
6. Copy Client ID and Client Secret to plugin settings
7. Enable the Google provider in plugin settings

### Apple Setup

1. Go to [Apple Developer](https://developer.apple.com/)
2. Create a new App ID or select existing
3. Enable "Sign in with Apple" capability
4. Create a Service ID for web authentication
5. Configure redirect URI: `https://yoursite.com/dafo-social-login/apple/callback`
6. Create a private key for "Sign in with Apple"
7. Download the .p8 key file and copy its contents
8. Copy Service ID, Team ID, Key ID, and Private Key to plugin settings
9. Enable the Apple provider in plugin settings

### Instagram Setup

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app with Instagram product
3. Configure Instagram Basic Display
4. Add redirect URI: `https://yoursite.com/dafo-social-login/instagram/callback`
5. Copy App ID and App Secret to plugin settings
6. **Note**: Instagram doesn't provide email addresses via API (accounts will be created without email)
7. Enable the Instagram provider in plugin settings

### Bluesky Setup

1. Bluesky uses decentralized authentication
2. No API credentials required
3. Simply enable the Bluesky provider in plugin settings
4. Users will authenticate with their Bluesky handle and password
5. **Note**: Bluesky doesn't expose email addresses (accounts will be created without email)

## Usage

### Automatic Display

Once configured, social login buttons will automatically appear on:
- WordPress login page (`wp-login.php`)
- WordPress registration page (if enabled in settings)
- UsersWP login form
- UsersWP registration form

### UsersWP

When [UsersWP](https://wordpress.org/plugins/userswp/) is active, the plugin now:

- Renders social login buttons inside the default UsersWP login and registration templates
- Preserves UsersWP login/register redirect targets through the OAuth round-trip
- Keeps the UsersWP register form ID on new social signups so user-type assignments still apply

### Shortcode

Use the shortcode to display social login buttons anywhere:

```php
[dafo_social_login]
```

Example in a page or post:
```
Want to join? Sign in with:
[dafo_social_login]
```

### Template Integration

Add to your theme template:

```php
<?php echo do_shortcode('[dafo_social_login]'); ?>
```

## Settings

Access plugin settings at: **WordPress Admin → Settings → Social Login**

### General Settings

- **Compatibility Mode**: Choose where the plugin should attach its built-in social buttons
  - Standard WordPress forms only
  - UsersWP forms only
  - Both standard WordPress and UsersWP

- **Display Options**: Choose where to show social login buttons
  - Show on login page
  - Show on registration page

- **Account Creation**: Toggle automatic account creation for new users

- **Redirect After Login**: Set custom URL for post-login redirect (default: admin dashboard)

### Provider Settings

For each provider:
- Enable/disable the provider
- Configure API credentials (Client ID, Client Secret, etc.)
- View redirect URI to add to provider settings

## Database

The plugin creates a custom table `wp_dafo_social_connections` to store:
- User social account connections
- OAuth tokens
- Provider-specific user data

## Security Features

- **CSRF Protection**: State parameters with nonces for OAuth flows
- **Secure Token Storage**: Encrypted storage of access tokens
- **Email Verification**: Links accounts via verified email addresses
- **Password Generation**: Secure random passwords for new accounts

## Limitations & Notes

1. **Instagram**: Does not provide email addresses through the API. Users will be created with a generated email address (`username@instagram.sociallogin.local`). They can still log in via Instagram anytime.

2. **Bluesky**: Uses a different authentication model. Requires user handle and password. Email not exposed, so accounts are created with generated email addresses.

3. **Apple**: Only sends user information (name) on first authorization. Subsequent logins only provide user ID.

4. **Social-Only Accounts**: Users who sign up via Instagram or Bluesky will have accounts marked as "social-only" and won't receive email notifications unless they add a real email address to their profile later.

5. **SSL Required**: Most OAuth providers require HTTPS. Ensure your site has a valid SSL certificate.

6. **Provider Approval**: Some providers (LinkedIn, Facebook) require app review before going live.

## Troubleshooting

### Redirect URI Mismatch

Ensure the redirect URI in your provider settings exactly matches:
```
https://yoursite.com/dafo-social-login/[provider]/callback
```

Replace `[provider]` with: linkedin, facebook, google, apple, instagram, or bluesky

### After Installation, URLs Don't Work

Go to **Settings → Permalinks** and click "Save Changes" to flush rewrite rules.

### "Invalid State Parameter" Error

This usually means:
- Cookie/session issues
- Time synchronization problems
- Nonce verification failed

Try:
- Clear browser cache and cookies
- Check server time is synchronized
- Ensure WordPress nonce functionality is working

### No Email Error

Some providers (Instagram, Bluesky) don't provide email addresses. This is now handled automatically:
- The plugin generates a unique email address for these users (e.g., `username@instagram.sociallogin.local`)
- Users can log in anytime using their social account
- These accounts are marked as "social-only" in user meta
- Users can later add a real email address in their WordPress profile if needed
- No additional configuration required

## Developer Hooks

### Filters

```php
// Modify user data before account creation
add_filter('dafo_social_login_user_data', function($user_data, $provider) {
    // Modify $user_data
    return $user_data;
}, 10, 2);

// Modify redirect URL after login
add_filter('dafo_social_login_redirect_url', function($url, $user) {
    // Return custom URL
    return $url;
}, 10, 2);
```

### Actions

```php
// After successful social login
add_action('dafo_social_login_authenticated', function($user, $provider, $user_data) {
    // Custom actions
}, 10, 3);

// After new social connection created
add_action('dafo_social_login_connection_created', function($user_id, $provider, $provider_user_id) {
    // Custom actions
}, 10, 3);
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher
- SSL certificate (HTTPS)
- cURL and OpenSSL PHP extensions

## Support

For issues, questions, or contributions:
- GitHub: [https://github.com/dafogary/DAFO_WP_SocialLogin](https://github.com/dafogary/DAFO_WP_SocialLogin)

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Support for 6 social platforms
- Admin configuration interface
- Automatic and manual integration options
- Secure OAuth implementation

## Credits

Developed by Gary DAFO

## Privacy & Data

This plugin:
- Stores OAuth tokens securely in the database
- Does not share user data with third parties
- Follows WordPress coding standards
- Respects user privacy settings

Ensure your site has appropriate privacy policies covering social login data collection.