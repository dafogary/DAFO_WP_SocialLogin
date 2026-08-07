# Contributing to DAFO Social Login

Thank you for considering contributing to DAFO Social Login! This document outlines the process for contributing to this project.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue on GitHub with:
- Clear description of the bug
- Steps to reproduce
- Expected vs actual behavior
- WordPress version
- PHP version
- Plugin version
- Any relevant error messages

### Suggesting Features

Feature requests are welcome! Please create an issue with:
- Clear description of the feature
- Use case and benefits
- Any examples from other implementations

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/your-feature-name`
3. **Make your changes**
4. **Test thoroughly**
5. **Commit with clear messages**: `git commit -m "Add: feature description"`
6. **Push to your fork**: `git push origin feature/your-feature-name`
7. **Create a Pull Request**

## Development Guidelines

### Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use meaningful variable and function names
- Comment complex logic
- Keep functions focused and single-purpose

### File Structure

```
dafo-social-login/
├── assets/
│   ├── css/
│   └── js/
├── includes/
│   ├── platforms/
│   └── *.php (core classes)
├── languages/
├── dafo-social-login.php (main file)
└── uninstall.php
```

### Testing

Before submitting:
1. Test on a fresh WordPress installation
2. Test with different providers
3. Check for PHP errors and warnings
4. Test on different browsers
5. Verify security (nonces, sanitization, escaping)

### Security

- Always sanitize input
- Always escape output
- Use WordPress nonces for forms
- Never store plain text passwords
- Use prepared statements for database queries

### Adding New Providers

To add a new social login provider:

1. Create a new class in `includes/platforms/`
2. Implement required methods:
   - `get_auth_url()` - Generate OAuth URL
   - `handle_callback()` - Process OAuth callback
3. Add provider to `class-dafo-sl-oauth-handler.php`
4. Add provider settings to `class-dafo-sl-admin.php`
5. Add provider icon to `class-dafo-sl-login-form.php`
6. Add provider styles to `assets/css/social-login.css`
7. Update documentation

Example structure:
```php
<?php
class DAFO_SL_NewProvider {
    private $auth_url = 'https://provider.com/oauth/authorize';
    private $token_url = 'https://provider.com/oauth/token';
    private $api_url = 'https://api.provider.com/';
    
    public function get_auth_url() {
        // Return OAuth authorization URL
    }
    
    public function handle_callback() {
        // Process callback and return user data
    }
    
    private function get_access_token($code) {
        // Exchange code for token
    }
    
    private function get_user_data($access_token) {
        // Get user profile data
    }
    
    private function get_redirect_uri() {
        return home_url('/dafo-social-login/newprovider/callback');
    }
}
```

## Code Review Process

All pull requests will be reviewed for:
- Code quality and standards
- Security considerations
- Performance impact
- Backwards compatibility
- Documentation updates

## Documentation

When adding features:
- Update README.md
- Update INSTALL.md if setup changes
- Add inline code comments
- Update language files if adding new strings

## Testing Checklist

- [ ] Code follows WordPress standards
- [ ] All inputs are sanitized
- [ ] All outputs are escaped
- [ ] Nonces are used for forms
- [ ] Database queries use prepared statements
- [ ] Tested on fresh WordPress install
- [ ] Tested with multiple providers
- [ ] No PHP errors or warnings
- [ ] Works on PHP 7.2+
- [ ] Works on WordPress 5.0+
- [ ] Documentation updated
- [ ] Language strings added to .pot file

## Questions?

Feel free to create an issue for any questions about contributing!

## License

By contributing, you agree that your contributions will be licensed under the GPL v2 or later license.
