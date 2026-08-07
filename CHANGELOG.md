# Changelog

All notable changes to DAFO Social Login will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2026-05-19

### Added
- `[dafo_login_register]` shortcode for front-end login and registration
- Email registration support with username, email, password, and confirm-password fields
- Tab-based UI with Login and Register panels
- AJAX form submission for login and registration (no page reload on errors)
- Social provider buttons displayed within the shortcode form
- Shortcode attributes: `mode` (login/register/both) and `redirect`
- Nonce verification and same-host redirect validation for all AJAX endpoints

## [1.0.0] - 2025-12-31

### Added
- Initial release of DAFO Social Login
- Support for LinkedIn OAuth 2.0 authentication
- Support for Facebook OAuth 2.0 authentication
- Support for Google OAuth 2.0 authentication
- Support for Apple Sign In authentication
- Support for Instagram OAuth 2.0 authentication
- Support for Bluesky authentication
- Admin settings page for managing social login providers
- Toggle to enable/disable individual providers
- Credential management for each provider
- Automatic display on WordPress login page
- Automatic display on WordPress registration page
- Shortcode support: `[dafo_social_login]`
- Automatic user account creation
- Social account linking to existing WordPress users
- Custom database table for social connections
- Secure token storage
- OAuth state verification with nonces
- Custom redirect URL after login
- Responsive social login buttons with provider-specific styling
- Copy-to-clipboard functionality for redirect URIs in admin
- Comprehensive documentation (README, INSTALL, CONTRIBUTING)
- Translation-ready with .pot file
- Uninstall script for clean removal
- **Email-optional account creation**: Users can sign up via Instagram/Bluesky without email
- **Generated email addresses**: Automatic email generation for providers without email support
- **Social-only account markers**: User meta to identify accounts created without real email

### Security
- CSRF protection using WordPress nonces
- Input sanitization for all user inputs
- Output escaping for all displayed data
- Prepared statements for database queries
- Secure token storage in database
- Password-free authentication flow
- Email-based account verification for providers that support it

## [Unreleased]

### Planned Features
- Profile picture import from social platforms
- User account unlinking functionality
- Social sharing capabilities
- Two-factor authentication integration
- Analytics dashboard for social login usage
- Webhook support for real-time updates
- Import social profile data (bio, location, etc.)
- Support for additional providers (Twitter/X, Microsoft, GitHub)
- Multi-language support with translations
- Dark mode support for login buttons
- Customizable button text and styling
- Role assignment based on social provider
- Custom fields mapping from social profiles

### Known Limitations
- Instagram does not provide email addresses via API (handled with generated emails)
- Bluesky does not expose email addresses (handled with generated emails)
- Apple only provides user name on first authorization
- Requires HTTPS for production use
- Some providers require app review before going live

## Version History

### Version Naming
- Major version (X.0.0): Breaking changes or major features
- Minor version (1.X.0): New features, backwards compatible
- Patch version (1.0.X): Bug fixes and minor improvements

---

For more details on each release, see the [GitHub Releases](https://github.com/dafogary/DAFO_WP_SocialLogin/releases) page.
