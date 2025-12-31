# Changelog

All notable changes to DAFO Social Login will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-31

### Added
- Initial release of DAFO Social Login plugin
- Google OAuth 2.0 integration
- Facebook Login integration
- BlueSky OAuth integration
- Apple Sign In integration with JWT support
- LinkedIn OAuth 2.0 integration
- Yahoo OAuth 2.0 integration
- Admin settings interface for managing OAuth credentials
- Frontend login buttons with responsive design
- Automatic WordPress user creation and account linking
- WooCommerce integration support
- Shortcode `[dafo_social_login]` for custom placement
- CSRF protection with WordPress nonces
- Secure OAuth 2.0 implementation
- Custom database table for tracking social connections
- User meta storage for social profile data
- Automatic integration with WordPress login page
- SSL/HTTPS support (required by OAuth providers)
- Uninstall cleanup script
- Comprehensive documentation (README, INSTALL, CONTRIBUTING)
- Branded social login buttons with hover effects
- Security features: input sanitization, output escaping, nonce verification

### Security
- All inputs sanitized using WordPress functions
- All outputs escaped properly
- CSRF protection on all forms
- Secure OAuth token handling
- No plain text password storage for social users
- OpenSSL support for Apple Sign In JWT generation

### Developer Features
- Object-oriented plugin architecture
- Abstract base class for easy provider extension
- WordPress coding standards compliance
- Hooks and filters for extensibility
- Well-documented code with PHPDoc comments

## [Unreleased]

### Planned Features
- Social login statistics and analytics
- Profile picture sync from social accounts
- Two-factor authentication (2FA) integration
- Account unlinking functionality in user profile
- Additional OAuth providers (Twitter/X, Microsoft, GitHub)
- Login button customization options
- Multi-language support (i18n/l10n)
- Login redirect customization
- User role assignment based on social provider
- Social profile data display in admin
- Email verification option
- Privacy policy integration
