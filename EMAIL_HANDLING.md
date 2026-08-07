# Email Handling in DAFO Social Login

## Overview

This plugin handles email addresses intelligently based on what each social provider offers. Email is **optional** for social login - users can create accounts and log in without providing an email address.

## How It Works

### Providers WITH Email Support

These providers return email addresses during authentication:
- **LinkedIn**: Always provides verified email
- **Facebook**: Provides email if user grants permission
- **Google**: Always provides verified email
- **Apple**: Provides email (may be relay address if user opts for privacy)

**Behavior:**
- Email is used as-is for account creation
- Can link to existing WordPress accounts via matching email
- User receives standard WordPress welcome email
- Email appears normally in user profile

### Providers WITHOUT Email Support

These providers do NOT return email addresses:
- **Instagram**: API doesn't expose email
- **Bluesky**: Protocol doesn't expose email

**Behavior:**
- Plugin generates a unique email: `username@provider.sociallogin.local`
- Examples:
  - `johndoe@instagram.sociallogin.local`
  - `alice.bsky@bluesky.sociallogin.local`
- Account is marked with user meta: `dafo_social_only_account = true`
- No welcome email is sent
- User can still log in anytime via their social account

## Generated Email Format

```
[username]@[provider].sociallogin.local
```

**Username Generation Priority:**
1. First name + Last name (e.g., `john.doe`)
2. Display name from provider (e.g., `johnsmith`)
3. Provider username (e.g., `johndoe123`)
4. Fallback: `[provider]_[hash]` (e.g., `instagram_a1b2c3d4`)

**Domain:**
- `.sociallogin.local` indicates a generated email
- Not a real email address
- Used for WordPress internal purposes only

## User Meta Markers

When an account is created without a real email:

```php
update_user_meta($user_id, 'dafo_social_only_account', true);
update_user_meta($user_id, 'dafo_social_provider', $provider);
```

**Benefits:**
- Identify social-only accounts
- Skip email notifications
- Show appropriate messages in admin
- Allow future email addition

## Account Linking Logic

### With Email

1. User logs in via social provider (e.g., Google)
2. Provider returns: `john@example.com`
3. Plugin checks: Does `john@example.com` exist in WordPress?
   - **YES**: Link social account to existing user
   - **NO**: Create new user with `john@example.com`

### Without Email

1. User logs in via social provider (e.g., Instagram)
2. Provider returns: `instagram_id_12345`, `username: johndoe`
3. Plugin checks: Does this Instagram ID already exist in connections?
   - **YES**: Log in existing user
   - **NO**: Create new user with generated email `johndoe@instagram.sociallogin.local`

## User Experience

### First Login (Instagram/Bluesky)

1. Click "Continue with Instagram"
2. Authenticate on Instagram
3. Redirected back to WordPress
4. ✅ Logged in successfully
5. Account created with generated email
6. No email sent (no real email address)

### Subsequent Logins

1. Click "Continue with Instagram"
2. Authenticate on Instagram
3. ✅ Instantly logged in
4. Same WordPress account each time

### Adding Real Email Later

Users can add a real email address in WordPress:
1. Go to Profile in WordPress admin
2. Update email address to real email
3. Manually remove `dafo_social_only_account` meta if desired
4. Start receiving WordPress notifications

## Admin View

### User List

Social-only accounts appear normal but with generated email:
- Username: `johndoe`
- Email: `johndoe@instagram.sociallogin.local`

### User Profile

User meta shows:
- `dafo_social_only_account`: `true`
- `dafo_social_provider`: `instagram`

### Social Connections Table

Database stores:
- `user_id`: WordPress user ID
- `provider`: `instagram`
- `provider_user_id`: Instagram's user ID
- `access_token`: OAuth token
- `user_data`: JSON with profile data

## Code Examples

### Check if User is Social-Only

```php
$is_social_only = get_user_meta($user_id, 'dafo_social_only_account', true);
if ($is_social_only) {
    $provider = get_user_meta($user_id, 'dafo_social_provider', true);
    echo "This user signed up via " . $provider;
}
```

### Identify Generated Email

```php
if (strpos($user->user_email, '.sociallogin.local') !== false) {
    echo "Generated email - no notifications sent";
}
```

### Get Social Connections

```php
global $wpdb;
$table = $wpdb->prefix . 'dafo_social_connections';
$connections = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table WHERE user_id = %d",
    $user_id
));
```

## Benefits of This Approach

✅ **No barriers**: Users can sign up instantly without email
✅ **Seamless**: Works exactly like other social logins
✅ **Secure**: Each generated email is unique
✅ **Reversible**: Users can add real email later
✅ **Clean**: No error messages about missing email
✅ **Standards-compliant**: WordPress still requires email for users, so we provide one

## Privacy Considerations

- Generated emails are stored in WordPress database
- Not visible to other users (like any email)
- Not used for communication
- Provider's user ID is stored to link accounts
- OAuth tokens are stored securely
- No personal data is shared without user consent

## Technical Notes

### Why Not Make Email NULL?

WordPress requires email addresses for user accounts:
- `wp_create_user()` requires email parameter
- WordPress core assumes email exists
- Many plugins expect email to be present
- Generated email maintains compatibility

### Domain Choice

`.sociallogin.local` is used because:
- `.local` is reserved for local use (RFC 6762)
- Won't conflict with real domains
- Clearly indicates generated address
- Easy to identify programmatically

### Collision Prevention

Username generation ensures uniqueness:
- Checks `username_exists()` before creation
- Adds numeric suffix if needed (e.g., `johndoe`, `johndoe1`, `johndoe2`)
- Email includes username, so it's also unique
- Provider ID stored separately for definitive matching

## Future Enhancements

Potential improvements:
- Admin interface to update social-only accounts with real emails
- Bulk email collection for social-only users
- Dashboard widget showing social-only account count
- Option to require email for specific providers
- Email verification flow for added addresses
- Social account manager in user profile

---

This approach provides the best user experience while maintaining WordPress compatibility and security standards.
