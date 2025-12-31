# DAFO Social Login - Installation & Setup Guide

## Quick Start

### 1. Installation

#### Via WordPress Admin
1. Download the plugin zip file
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and select the zip file
4. Click "Install Now" and then "Activate"

#### Manual Installation
1. Upload the `dafo-social-login` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress

### 2. Initial Configuration

After activation:
1. Go to **Settings > Social Login** in WordPress admin
2. You'll see sections for each social login provider
3. Configure the providers you want to use

## Provider Configuration Details

### Google OAuth 2.0

1. **Get Credentials:**
   - Visit [Google Cloud Console](https://console.developers.google.com/)
   - Create a new project or select existing
   - Enable "Google+ API" or "People API"
   - Go to "Credentials" > "Create Credentials" > "OAuth 2.0 Client ID"
   - Select "Web application" as application type
   
2. **Configure OAuth:**
   - Add Authorized JavaScript origins: `https://yourdomain.com`
   - Add Authorized redirect URIs: Copy from plugin settings page
   - It will be like: `https://yourdomain.com/dafo-social-login/callback/google/`
   
3. **Plugin Settings:**
   - Enable Google Login: ✓
   - Google Client ID: (paste from Google Console)
   - Google Client Secret: (paste from Google Console)

### Facebook Login

1. **Get Credentials:**
   - Visit [Facebook Developers](https://developers.facebook.com/)
   - Click "My Apps" > "Create App"
   - Select "Consumer" use case
   - Add "Facebook Login" product to your app
   
2. **Configure OAuth:**
   - Go to Facebook Login > Settings
   - Add Valid OAuth Redirect URIs: Copy from plugin settings
   - It will be like: `https://yourdomain.com/dafo-social-login/callback/facebook/`
   
3. **Plugin Settings:**
   - Enable Facebook Login: ✓
   - Facebook App ID: (from App Dashboard)
   - Facebook App Secret: (from App Dashboard)

### BlueSky OAuth

1. **Get Credentials:**
   - Register your application with BlueSky
   - Note: BlueSky OAuth may still be in development
   
2. **Plugin Settings:**
   - Enable BlueSky Login: ✓
   - BlueSky Client ID: (from BlueSky)
   - BlueSky Client Secret: (from BlueSky)

### Apple Sign In

1. **Get Credentials:**
   - Visit [Apple Developer](https://developer.apple.com/)
   - Go to Certificates, Identifiers & Profiles
   - Create an App ID and Services ID
   - Enable Sign In with Apple capability
   
2. **Configure:**
   - Configure Services ID with Return URLs from plugin settings
   - Create a Key with Sign In with Apple enabled
   - Download the private key (.p8 file)
   
3. **Plugin Settings:**
   - Enable Apple Sign In: ✓
   - Apple Service ID: (your Services ID)
   - Apple Team ID: (10-character Team ID)
   - Apple Key ID: (10-character Key ID)
   - Apple Private Key: (paste content of .p8 file)

### LinkedIn OAuth 2.0

1. **Get Credentials:**
   - Visit [LinkedIn Developers](https://www.linkedin.com/developers/)
   - Click "Create app"
   - Fill in required information
   - Request access to "Sign In with LinkedIn using OpenID Connect"
   
2. **Configure OAuth:**
   - Go to "Auth" tab
   - Add Redirect URLs from plugin settings
   - It will be like: `https://yourdomain.com/dafo-social-login/callback/linkedin/`
   
3. **Plugin Settings:**
   - Enable LinkedIn Login: ✓
   - LinkedIn Client ID: (from Auth tab)
   - LinkedIn Client Secret: (from Auth tab)

### Yahoo OAuth 2.0

1. **Get Credentials:**
   - Visit [Yahoo Developer Network](https://developer.yahoo.com/)
   - Create a new app
   - Select "OpenID Connect Permissions"
   
2. **Configure OAuth:**
   - Add Redirect URI from plugin settings
   - It will be like: `https://yourdomain.com/dafo-social-login/callback/yahoo/`
   
3. **Plugin Settings:**
   - Enable Yahoo Login: ✓
   - Yahoo Client ID: (from app dashboard)
   - Yahoo Client Secret: (from app dashboard)

## Usage

### Default Integration

Once configured, social login buttons will automatically appear on:
- WordPress login page (`/wp-login.php`)
- WooCommerce My Account page (if WooCommerce is installed)

### Shortcode

Add social login buttons anywhere using:
```
[dafo_social_login]
```

Examples:
- In a page/post editor
- In a widget (if widget supports shortcodes)
- In theme files: `<?php echo do_shortcode('[dafo_social_login]'); ?>`

### Custom Template Integration

Add to your theme's template files:

```php
<?php
// Add to login form
if (function_exists('dafo_social_login_buttons')) {
    echo do_shortcode('[dafo_social_login]');
}
?>
```

## Troubleshooting

### Common Issues

**1. Buttons don't appear:**
- Check that at least one provider is enabled
- Verify credentials are entered correctly
- Check that you're not already logged in

**2. OAuth errors:**
- Verify redirect URIs match exactly in provider settings
- Ensure your site has SSL certificate (HTTPS)
- Check that credentials are correct

**3. "Invalid state parameter" error:**
- This is a security error - refresh the page and try again
- Check that cookies are enabled in browser

**4. No email received from provider:**
- Some providers require explicit email permission
- User may need to grant email permission during OAuth

**5. User creation fails:**
- Check WordPress user creation is enabled
- Verify email doesn't already exist with different account
- Check PHP error logs for details

### Debug Mode

To enable debugging, add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Then check `/wp-content/debug.log` for errors.

## Security Notes

### Required Settings
- **SSL Certificate Required**: Most OAuth providers require HTTPS
- **Proper Redirect URIs**: Must match exactly in provider settings
- **Keep Secrets Secret**: Never commit credentials to public repositories

### Best Practices
- Use environment-specific credentials (dev/staging/production)
- Regularly rotate API keys and secrets
- Monitor for suspicious login activity
- Keep WordPress and plugin updated

## WooCommerce Integration

The plugin automatically integrates with WooCommerce:

1. **My Account Page**: Social login buttons appear on login form
2. **User Creation**: Creates both WordPress and WooCommerce customer
3. **Orders**: Social login users can view their orders normally
4. **Checkout**: Returning customers can use social login at checkout

## Database Information

### Tables Created
- `wp_dafo_social_login_connections`: Stores provider connections

### User Meta Stored
- `dafo_social_login_{provider}`: Provider user ID
- `dafo_social_login_{provider}_data`: Full user data from provider

## Uninstallation

To completely remove the plugin:

1. Deactivate the plugin
2. Delete the plugin

**Note**: Uninstalling will:
- Remove all plugin options
- Delete social login connections table
- Remove user meta data
- Users who only logged in via social will need password reset to login

## Support

For issues, feature requests, or contributions:
- GitHub: [https://github.com/dafogary/DAFO_WP_SocialLogin](https://github.com/dafogary/DAFO_WP_SocialLogin)
- WordPress Support: Leave feedback on plugin page

## Version History

### 1.0.0 (Initial Release)
- Google OAuth 2.0 integration
- Facebook Login integration
- BlueSky OAuth integration
- Apple Sign In integration
- LinkedIn OAuth 2.0 integration
- Yahoo OAuth 2.0 integration
- WooCommerce support
- Shortcode support
- Admin settings interface
