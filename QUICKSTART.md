# DAFO Social Login - Quick Reference

## Plugin Information

- **Name**: DAFO Social Login
- **Version**: 1.0.0
- **Requires WordPress**: 5.0+
- **Requires PHP**: 7.2+
- **License**: GPL v2 or later

## Quick Start

### 1. Install & Activate
1. Upload to `/wp-content/plugins/dafo-social-login/`
2. Activate via WordPress Plugins menu
3. Go to **Settings > Social Login**

### 2. Configure Providers

#### Enable at least one provider:
- ✓ Check "Enable [Provider] Login"
- Enter Client ID and Secret
- Save Settings

### 3. Add Redirect URIs

Copy from plugin settings to your OAuth app:
```
https://yoursite.com/dafo-social-login/callback/[provider]/
```

## Shortcode

```
[dafo_social_login]
```

Use in posts, pages, or widgets.

## Supported Providers

| Provider | OAuth Type | Special Requirements |
|----------|-----------|---------------------|
| Google | OAuth 2.0 | Google Cloud Console |
| Facebook | OAuth 2.0 | Facebook App |
| BlueSky | OAuth | AT Protocol (Beta) |
| Apple | Sign In | Private Key (.p8) |
| LinkedIn | OAuth 2.0 | OpenID Connect |
| Yahoo | OAuth 2.0 | Yahoo Developer |

## Common Settings Locations

### WordPress
- Admin: `Settings > Social Login`
- Database: `wp_options` → `dafo_social_login_settings`
- User Meta: `wp_usermeta` → `dafo_social_login_{provider}`

### WooCommerce
- Auto-integrated on My Account page
- Social login users are WooCommerce customers

## File Structure

```
dafo-social-login/
├── dafo-social-login.php    # Main plugin file
├── includes/
│   ├── class-dafo-social-login.php           # Core class
│   ├── class-dafo-social-login-admin.php     # Admin interface
│   ├── class-dafo-social-login-frontend.php  # Frontend buttons
│   ├── class-dafo-social-login-oauth.php     # OAuth handler
│   └── providers/                             # OAuth providers
├── assets/css/              # Stylesheets
├── uninstall.php           # Cleanup on uninstall
└── Documentation files
```

## Hooks & Filters

### Actions
```php
// After successful social login
do_action('dafo_social_login_after_login', $user, $provider);

// Before user creation
do_action('dafo_social_login_before_create_user', $user_data, $provider);
```

### Filters
```php
// Modify user data before creation
apply_filters('dafo_social_login_user_data', $user_data, $provider);

// Modify redirect URL after login
apply_filters('dafo_social_login_redirect_url', $redirect_url, $user);
```

## Troubleshooting

### Buttons Don't Show
- Check provider is enabled
- Verify not already logged in
- Clear cache

### OAuth Errors
- Verify redirect URIs match exactly
- Ensure HTTPS is enabled
- Check credentials are correct

### User Creation Fails
- Check WordPress allows user registration
- Verify email not already in use
- Check PHP error logs

## Security Features

✓ CSRF protection with nonces  
✓ Input sanitization  
✓ Output escaping  
✓ JWT validation (Apple)  
✓ State parameter validation  
✓ Secure token handling  

## Support & Resources

- **Documentation**: README.md, INSTALL.md
- **Issues**: GitHub Issues
- **Contributing**: CONTRIBUTING.md
- **Changelog**: CHANGELOG.md

## Quick Commands

### Debugging
```php
// Enable WordPress debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Check logs at:
/wp-content/debug.log
```

### Database
```sql
-- View social connections
SELECT * FROM wp_dafo_social_login_connections;

-- View user meta
SELECT * FROM wp_usermeta WHERE meta_key LIKE 'dafo_social_login_%';
```

## Key Functions

```php
// Display social login buttons
do_shortcode('[dafo_social_login]');

// Check if user logged in via social
$provider = get_user_meta($user_id, 'dafo_social_login_provider', true);

// Get plugin settings
$settings = get_option('dafo_social_login_settings');
```

## Version History

- **1.0.0** (2025-12-31): Initial release with 6 OAuth providers

---

For complete documentation, see README.md and INSTALL.md
