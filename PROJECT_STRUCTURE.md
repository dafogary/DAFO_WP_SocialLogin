# DAFO Social Login - Project Structure

## Complete File Structure

```
DAFO_WP_SocialLogin/
│
├── dafo-social-login.php          # Main plugin file with metadata and initialization
├── uninstall.php                   # Cleanup script when plugin is deleted
│
├── includes/                       # Core plugin classes
│   ├── class-dafo-social-login.php           # Main controller class
│   ├── class-dafo-sl-oauth-handler.php       # OAuth flow handler
│   ├── class-dafo-sl-admin.php               # Admin settings interface
│   ├── class-dafo-sl-login-form.php          # Frontend login form display
│   │
│   └── platforms/                  # Provider-specific integrations
│       ├── class-dafo-sl-linkedin.php
│       ├── class-dafo-sl-facebook.php
│       ├── class-dafo-sl-google.php
│       ├── class-dafo-sl-apple.php
│       ├── class-dafo-sl-instagram.php
│       └── class-dafo-sl-bluesky.php
│
├── assets/                         # Frontend resources
│   ├── css/
│   │   ├── social-login.css       # Frontend button styles
│   │   └── admin.css              # Admin interface styles
│   │
│   └── js/
│       ├── social-login.js        # Frontend interactions
│       └── admin.js               # Admin interface interactions
│
├── languages/                      # Internationalization
│   └── dafo-social-login.pot      # Translation template
│
├── README.md                       # Comprehensive documentation
├── INSTALL.md                      # Quick installation guide
├── CHANGELOG.md                    # Version history
├── CONTRIBUTING.md                 # Contribution guidelines
├── LICENSE                         # GPL v2 license
└── .gitignore                      # Git ignore rules
```

## Component Overview

### Core Files

#### dafo-social-login.php
- Plugin header with metadata
- Defines plugin constants
- Includes required files
- Handles activation/deactivation
- Creates database table on activation
- Sets default options

#### includes/class-dafo-social-login.php
- Main plugin controller
- Registers OAuth endpoints
- Handles callback routing
- Enqueues scripts and styles
- Manages settings retrieval

#### includes/class-dafo-sl-oauth-handler.php
- Initializes all provider instances
- Routes OAuth callbacks to correct provider
- Processes user authentication
- Creates/links WordPress user accounts
- Manages social connections database
- Generates usernames for new users

#### includes/class-dafo-sl-admin.php
- Creates admin settings page
- Registers settings with WordPress
- Sanitizes all user inputs
- Renders settings interface
- Displays redirect URIs for each provider

#### includes/class-dafo-sl-login-form.php
- Displays social login buttons
- Adds to login/registration pages
- Provides shortcode functionality
- Shows error messages
- Includes provider-specific SVG icons

### Provider Classes

Each provider class implements:
- `get_auth_url()` - Generates OAuth authorization URL
- `handle_callback()` - Processes OAuth callback
- `get_access_token()` - Exchanges code for token
- `get_user_data()` - Retrieves user profile
- `get_redirect_uri()` - Returns callback URL

#### LinkedIn (class-dafo-sl-linkedin.php)
- Uses OAuth 2.0 with OpenID Connect
- Scopes: openid, profile, email
- Returns: id, email, name, picture

#### Facebook (class-dafo-sl-facebook.php)
- Uses Facebook Graph API v18.0
- Scopes: email, public_profile
- Returns: id, email, first_name, last_name, name, picture

#### Google (class-dafo-sl-google.php)
- Uses Google OAuth 2.0
- Scopes: openid, email, profile
- Returns: id, email, given_name, family_name, name, picture

#### Apple (class-dafo-sl-apple.php)
- Uses Sign in with Apple
- Scopes: name, email
- Requires JWT client secret generation
- Returns: id (sub), email (name only on first auth)

#### Instagram (class-dafo-sl-instagram.php)
- Uses Instagram Basic Display API
- Scopes: user_profile, user_media
- Returns: id, username (no email provided)

#### Bluesky (class-dafo-sl-bluesky.php)
- Uses AT Protocol authentication
- Direct credential authentication (handle + password)
- Returns: did (id), handle, display_name, avatar (no email)

## Database Schema

### Table: wp_dafo_social_connections

```sql
CREATE TABLE wp_dafo_social_connections (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    provider varchar(50) NOT NULL,
    provider_user_id varchar(255) NOT NULL,
    access_token text,
    refresh_token text,
    token_expires datetime,
    user_data text,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_provider (user_id, provider),
    UNIQUE KEY provider_user (provider, provider_user_id)
);
```

## WordPress Options

### dafo_social_login_settings

Stored as serialized array with keys:
- `enabled_providers` (array)
- `linkedin_client_id` (string)
- `linkedin_client_secret` (string)
- `facebook_app_id` (string)
- `facebook_app_secret` (string)
- `google_client_id` (string)
- `google_client_secret` (string)
- `apple_client_id` (string)
- `apple_team_id` (string)
- `apple_key_id` (string)
- `apple_private_key` (text)
- `instagram_client_id` (string)
- `instagram_client_secret` (string)
- `bluesky_enabled` (boolean)
- `show_on_login` (boolean)
- `show_on_register` (boolean)
- `auto_create_account` (boolean)
- `redirect_after_login` (url)

## URL Routing

### OAuth Callback URLs

WordPress rewrite rules handle:
```
/dafo-social-login/{provider}/callback
```

Query vars:
- `dafo_social_login` = 1
- `provider` = {provider_name}

### Shortcode

```
[dafo_social_login]
```

## Hooks & Filters

### Actions
- `dafo_social_login_authenticated` - After successful login
- `dafo_social_login_connection_created` - After new connection

### Filters
- `dafo_social_login_user_data` - Modify user data before account creation
- `dafo_social_login_redirect_url` - Modify redirect URL after login

## Security Features

1. **CSRF Protection**: Nonces in OAuth state parameter
2. **Input Sanitization**: All inputs sanitized
3. **Output Escaping**: All outputs escaped
4. **Prepared Statements**: Database queries use wpdb->prepare()
5. **Secure Tokens**: Tokens stored in database, not cookies
6. **Email Verification**: Accounts linked via verified emails
7. **Random Passwords**: wp_generate_password() for new users

## Integration Points

### WordPress Hooks Used
- `plugins_loaded` - Initialize plugin
- `init` - Register endpoints
- `template_redirect` - Handle callbacks
- `wp_enqueue_scripts` - Load frontend assets
- `login_enqueue_scripts` - Load login page assets
- `admin_menu` - Add settings page
- `admin_init` - Register settings
- `admin_enqueue_scripts` - Load admin assets
- `login_form` - Display on login page
- `register_form` - Display on registration page
- `login_head` - Display error messages

### WordPress Functions Used
- `wp_create_user()` - Create new users
- `wp_set_auth_cookie()` - Log users in
- `get_user_by()` - Find existing users
- `username_exists()` - Check username availability
- `wp_remote_get()` / `wp_remote_post()` - HTTP requests
- `wp_create_nonce()` / `wp_verify_nonce()` - Security tokens
- `add_rewrite_rule()` - Custom URL routing
- `flush_rewrite_rules()` - Update permalinks

## Future Enhancement Opportunities

1. Profile picture import
2. Account unlinking interface
3. Social sharing features
4. Analytics dashboard
5. More provider support (Twitter/X, Microsoft, GitHub)
6. Custom role assignment by provider
7. Profile data synchronization
8. Two-factor authentication
9. Webhook support
10. REST API endpoints

## Development Workflow

1. All code follows WordPress Coding Standards
2. Security first: sanitize, validate, escape
3. Backwards compatibility maintained
4. Comprehensive documentation
5. Translation ready
6. Tested on multiple WordPress versions
7. No external dependencies (uses WordPress HTTP API)

## Requirements

- WordPress 5.0+
- PHP 7.2+
- MySQL 5.6+
- HTTPS (for production)
- cURL and OpenSSL PHP extensions

---

This structure provides a complete, production-ready WordPress social login plugin with support for 6 major platforms.
