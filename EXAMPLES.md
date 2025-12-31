# DAFO Social Login - Usage Examples

## Example 1: Basic WordPress Login

The plugin automatically adds social login buttons to the WordPress login page at `/wp-login.php`.

**What users see:**
```
[Username] [____________]
[Password] [____________]
[Log In Button]

--- Or login with ---

[Google] [Facebook] [Apple] [LinkedIn] [Yahoo]
```

## Example 2: Custom Login Page

Create a custom login page using the shortcode:

```php
<?php
/**
 * Template Name: Custom Login
 */

get_header();
?>

<div class="custom-login-page">
    <h2>Login to Your Account</h2>
    
    <?php wp_login_form(); ?>
    
    <!-- Social Login Buttons -->
    <?php echo do_shortcode('[dafo_social_login]'); ?>
    
    <p>Don't have an account? <a href="/register">Sign up</a></p>
</div>

<?php
get_footer();
?>
```

## Example 3: WooCommerce Integration

The plugin automatically integrates with WooCommerce My Account page.

**Automatic integration at:**
- `/my-account/` - WooCommerce login
- `/checkout/` - Checkout page (for guest users)

## Example 4: Widget Integration

Add social login to a sidebar widget:

1. Go to **Appearance > Widgets**
2. Add a **Text** or **HTML** widget
3. Enter the shortcode:
```
[dafo_social_login]
```

## Example 5: Membership Site

For a membership site with restricted content:

```php
<?php
// In your template file

if (!is_user_logged_in()) {
    ?>
    <div class="members-only-notice">
        <h3>Members Only Content</h3>
        <p>Please login to view this content.</p>
        
        <!-- Standard login form -->
        <?php wp_login_form(array('redirect' => get_permalink())); ?>
        
        <!-- Social login buttons -->
        <?php echo do_shortcode('[dafo_social_login]'); ?>
    </div>
    <?php
} else {
    // Display member content
    the_content();
}
?>
```

## Example 6: Modal Login Popup

Create a login modal with social options:

```html
<!-- Button to trigger modal -->
<button id="login-modal-btn">Login</button>

<!-- Modal HTML -->
<div id="login-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Login</h2>
        
        <?php echo do_shortcode('[dafo_social_login]'); ?>
        
        <div class="divider">OR</div>
        
        <?php wp_login_form(); ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#login-modal-btn').click(function() {
        $('#login-modal').fadeIn();
    });
    
    $('.close').click(function() {
        $('#login-modal').fadeOut();
    });
});
</script>
```

## Example 7: Registration Page with Social Login

Combine registration form with social login:

```php
<?php
/**
 * Template Name: Register
 */

get_header();
?>

<div class="registration-page">
    <h2>Create Your Account</h2>
    
    <!-- Quick signup with social -->
    <div class="social-registration">
        <h3>Quick Sign Up</h3>
        <?php echo do_shortcode('[dafo_social_login]'); ?>
    </div>
    
    <div class="divider">OR</div>
    
    <!-- Traditional registration form -->
    <div class="traditional-registration">
        <h3>Register with Email</h3>
        <?php 
        // Your custom registration form
        // or use a registration plugin
        ?>
    </div>
</div>

<?php
get_footer();
?>
```

## Example 8: Profile Page - Show Connected Accounts

Display which social accounts are connected:

```php
<?php
// In user profile or account page

$user_id = get_current_user_id();

$providers = array(
    'google' => 'Google',
    'facebook' => 'Facebook',
    'bluesky' => 'BlueSky',
    'apple' => 'Apple',
    'linkedin' => 'LinkedIn',
    'yahoo' => 'Yahoo'
);

echo '<h3>Connected Social Accounts</h3>';
echo '<ul class="connected-accounts">';

foreach ($providers as $key => $name) {
    $connected = get_user_meta($user_id, 'dafo_social_login_' . $key, true);
    
    if ($connected) {
        echo '<li><strong>' . esc_html($name) . ':</strong> Connected ✓</li>';
    } else {
        echo '<li><strong>' . esc_html($name) . ':</strong> Not connected</li>';
    }
}

echo '</ul>';
?>
```

## Example 9: Redirect After Login

Customize where users go after social login:

```php
<?php
// In your theme's functions.php

add_filter('dafo_social_login_redirect_url', 'custom_social_login_redirect', 10, 2);

function custom_social_login_redirect($redirect_url, $user) {
    // Redirect to dashboard for administrators
    if (user_can($user, 'manage_options')) {
        return admin_url();
    }
    
    // Redirect to account page for customers
    if (class_exists('WooCommerce')) {
        return wc_get_account_endpoint_url('dashboard');
    }
    
    // Default to home page
    return home_url();
}
?>
```

## Example 10: Custom Button Styling

Override the default button styles:

```css
/* In your theme's style.css or custom CSS */

/* Container */
.dafo-social-login-container {
    margin: 30px 0;
}

/* Buttons */
.dafo-social-login-button {
    padding: 15px 25px;
    border-radius: 8px;
    font-size: 16px;
}

/* Individual provider colors */
.dafo-social-login-google {
    background: linear-gradient(to right, #4285f4, #34a853);
}

.dafo-social-login-facebook {
    background: linear-gradient(to right, #1877f2, #0e5fb8);
}

/* Hover effects */
.dafo-social-login-button:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
```

## Example 11: Conditional Provider Display

Show different providers on different pages:

```php
<?php
// In your template

// Only show Google and Facebook on homepage
if (is_front_page()) {
    echo do_shortcode('[dafo_social_login]');
}

// Show all providers on login page
elseif (is_page('login')) {
    echo do_shortcode('[dafo_social_login]');
}

// Show business-focused providers on B2B section
elseif (is_page('business')) {
    // Note: This requires custom modification to the plugin
    echo '<p>Login with your business account:</p>';
    echo do_shortcode('[dafo_social_login]');
}
?>
```

## Example 12: Track Social Login Analytics

Track which provider users prefer:

```php
<?php
// In functions.php

add_action('wp_login', 'track_social_login', 10, 2);

function track_social_login($user_login, $user) {
    // Check if this was a social login
    $providers = array('google', 'facebook', 'bluesky', 'apple', 'linkedin', 'yahoo');
    
    foreach ($providers as $provider) {
        $social_id = get_user_meta($user->ID, 'dafo_social_login_' . $provider, true);
        if ($social_id) {
            // Track in your analytics
            // Example: Google Analytics, Mixpanel, etc.
            do_action('custom_track_event', 'social_login', $provider);
            break;
        }
    }
}
?>
```

## Notes

- All examples assume the plugin is activated and configured
- Modify styling to match your theme
- Test thoroughly before deploying to production
- Ensure HTTPS is enabled for OAuth to work
- Keep credentials secure and never commit to version control

## Additional Resources

- See INSTALL.md for setup instructions
- See README.md for complete documentation
- See CONTRIBUTING.md for customization guidelines
