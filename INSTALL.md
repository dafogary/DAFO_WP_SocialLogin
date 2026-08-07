# Quick Installation Guide

## Step 1: Install the Plugin

### Option A: Via WordPress Admin
1. Zip the entire plugin folder
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin"
4. Choose the zip file
5. Click "Install Now"
6. Activate the plugin

### Option B: Via FTP/File Manager
1. Upload the entire plugin folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Find "DAFO Social Login" and click "Activate"

## Step 2: Flush Rewrite Rules
After activation:
1. Go to Settings → Permalinks
2. Click "Save Changes" (don't change anything)
3. This ensures OAuth callback URLs work correctly

## Step 3: Configure Providers

### Quick Setup Checklist

#### LinkedIn
- [ ] Create app at [LinkedIn Developers](https://www.linkedin.com/developers/)
- [ ] Add redirect URI: `https://yoursite.com/dafo-social-login/linkedin/callback`
- [ ] Copy Client ID and Client Secret
- [ ] Enable in Settings → Social Login

#### Facebook
- [ ] Create app at [Facebook Developers](https://developers.facebook.com/)
- [ ] Add Facebook Login product
- [ ] Add redirect URI: `https://yoursite.com/dafo-social-login/facebook/callback`
- [ ] Copy App ID and App Secret
- [ ] Enable in Settings → Social Login

#### Google
- [ ] Create project at [Google Cloud Console](https://console.cloud.google.com/)
- [ ] Enable Google+ API
- [ ] Create OAuth credentials
- [ ] Add redirect URI: `https://yoursite.com/dafo-social-login/google/callback`
- [ ] Copy Client ID and Client Secret
- [ ] Enable in Settings → Social Login

#### Apple
- [ ] Create Service ID at [Apple Developer](https://developer.apple.com/)
- [ ] Enable Sign in with Apple
- [ ] Add redirect URI: `https://yoursite.com/dafo-social-login/apple/callback`
- [ ] Generate private key (.p8)
- [ ] Copy Service ID, Team ID, Key ID, and Private Key content
- [ ] Enable in Settings → Social Login

#### Instagram
- [ ] Create app with Instagram at [Facebook Developers](https://developers.facebook.com/)
- [ ] Configure Instagram Basic Display
- [ ] Add redirect URI: `https://yoursite.com/dafo-social-login/instagram/callback`
- [ ] Copy App ID and App Secret
- [ ] Enable in Settings → Social Login
- [ ] **Note**: Email not available from Instagram API

#### Bluesky
- [ ] Simply enable in Settings → Social Login
- [ ] No credentials required
- [ ] **Note**: Email not exposed by Bluesky

## Step 4: Test

1. Log out of WordPress
2. Go to wp-login.php
3. You should see social login buttons
4. Click a button to test the flow

## Common Setup Issues

### Issue: "Page not found" after clicking social button
**Solution**: Go to Settings → Permalinks and click "Save Changes"

### Issue: "Redirect URI mismatch"
**Solution**: Ensure the redirect URI in provider settings exactly matches the one shown in plugin settings

### Issue: SSL/HTTPS errors
**Solution**: Ensure your site has a valid SSL certificate. Most OAuth providers require HTTPS.

## Recommended Settings

For most sites:
- ✅ Show on login page
- ✅ Show on registration page
- ✅ Automatically create account for new users
- Leave redirect URL empty (redirects to admin dashboard)

## Security Notes

1. Never commit or share your API credentials
2. Use strong credentials for production
3. Regularly review connected social accounts
4. Keep WordPress and the plugin updated
5. Use HTTPS (required by most providers)

## Need Help?

- Check the full README.md for detailed documentation
- Visit GitHub repository for issues and updates
- Test in a staging environment first

## Quick Reference: Redirect URIs

```
LinkedIn:  https://yoursite.com/dafo-social-login/linkedin/callback
Facebook:  https://yoursite.com/dafo-social-login/facebook/callback
Google:    https://yoursite.com/dafo-social-login/google/callback
Apple:     https://yoursite.com/dafo-social-login/apple/callback
Instagram: https://yoursite.com/dafo-social-login/instagram/callback
Bluesky:   https://yoursite.com/dafo-social-login/bluesky/callback
```

Replace `yoursite.com` with your actual domain.
