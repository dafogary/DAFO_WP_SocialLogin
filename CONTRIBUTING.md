# Contributing to DAFO Social Login

Thank you for your interest in contributing to DAFO Social Login! This document provides guidelines for contributing to the project.

## Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Focus on the code, not the person
- Help others learn and grow

## How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported in Issues
2. If not, create a new issue with:
   - Clear title and description
   - Steps to reproduce
   - Expected vs actual behavior
   - WordPress version, PHP version, and plugin version
   - Any error messages or logs

### Suggesting Enhancements

1. Check if the enhancement has been suggested
2. Create a new issue describing:
   - The problem you're trying to solve
   - Your proposed solution
   - Any alternatives you've considered
   - How it benefits other users

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Test thoroughly
5. Commit with clear messages (`git commit -m 'Add amazing feature'`)
6. Push to your fork (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## Development Guidelines

### Coding Standards

Follow WordPress Coding Standards:
- [PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- [HTML Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/html/)
- [CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

### Security

- Sanitize all inputs
- Escape all outputs
- Use nonces for form submissions
- Use WordPress functions for database queries
- Never store plain text passwords
- Follow OAuth security best practices

### Testing

Before submitting a PR:
- Test on a fresh WordPress installation
- Test with WooCommerce enabled/disabled
- Test each OAuth provider you modified
- Check for PHP errors and warnings
- Verify no JavaScript console errors

### File Structure

```
dafo-social-login/
├── assets/
│   └── css/
│       ├── admin.css
│       └── frontend.css
├── includes/
│   ├── class-dafo-social-login.php
│   ├── class-dafo-social-login-admin.php
│   ├── class-dafo-social-login-frontend.php
│   ├── class-dafo-social-login-oauth.php
│   ├── class-dafo-social-login-activator.php
│   ├── class-dafo-social-login-deactivator.php
│   └── providers/
│       ├── class-dafo-oauth-provider.php
│       └── class-dafo-{provider}-provider.php
├── dafo-social-login.php
└── uninstall.php
```

### Adding a New Provider

To add a new OAuth provider:

1. Create a new provider class in `includes/providers/`
2. Extend `DAFO_OAuth_Provider` base class
3. Implement required methods:
   - `get_authorization_url($state)`
   - `get_user_data()`
   - `get_access_token($code)`
4. Add provider to admin settings
5. Add provider styles to frontend CSS
6. Update documentation

Example provider class:

```php
<?php
class DAFO_NewProvider_Provider extends DAFO_OAuth_Provider {
    
    public function __construct() {
        $this->provider_name = 'newprovider';
        $options = get_option('dafo_social_login_settings', array());
        $this->client_id = $options['newprovider_client_id'] ?? '';
        $this->client_secret = $options['newprovider_client_secret'] ?? '';
    }
    
    public function get_authorization_url($state) {
        // Build OAuth URL
    }
    
    protected function get_access_token($code) {
        // Exchange code for token
    }
    
    public function get_user_data() {
        // Get user data from provider
        // Return array with: id, email, first_name, last_name, display_name
    }
}
```

## Documentation

When adding features:
- Update README.md
- Update INSTALL.md if setup changes
- Add inline code comments for complex logic
- Follow PHPDoc standards for functions

## Git Commit Messages

- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters
- Reference issues and pull requests

Examples:
```
Add LinkedIn OAuth provider
Fix Google token refresh issue
Update documentation for Apple setup
Refactor OAuth base class for reusability
```

## Questions?

Feel free to:
- Open an issue for discussion
- Contact the maintainers
- Check existing issues for similar questions

## License

By contributing, you agree that your contributions will be licensed under the GPL v2 or later license.
