/**
 * Social Login Frontend Scripts
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {

        // ---------------------------------------------------------------
        // Social login button loading state
        // ---------------------------------------------------------------
        $('.dafo-social-login-button').on('click', function() {
            $(this).addClass('loading');
            $(this).find('span').text('Loading...');
        });

        // ---------------------------------------------------------------
        // Bluesky form submission
        // ---------------------------------------------------------------
        $('.dafo-bluesky-login-form form').on('submit', function(e) {
            var $form   = $(this);
            var $button = $form.find('button[type="submit"]');
            var handle   = $form.find('#bluesky_handle').val();
            var password = $form.find('#bluesky_password').val();

            if (!handle || !password) {
                e.preventDefault();
                alert('Please enter your Bluesky handle and password.');
                return false;
            }

            $button.prop('disabled', true).text('Authenticating...');
        });

        // ---------------------------------------------------------------
        // Shortcode: tab switching
        // ---------------------------------------------------------------
        $(document).on('click', '.dafo-sc-tab', function() {
            var $wrapper = $(this).closest('.dafo-sc-wrapper');
            var tab      = $(this).data('tab');

            $wrapper.find('.dafo-sc-tab').removeClass('active');
            $(this).addClass('active');

            $wrapper.find('.dafo-sc-panel').removeClass('active');
            $wrapper.find('#dafo-sc-panel-' + tab).addClass('active');
        });

        // ---------------------------------------------------------------
        // Shortcode: email login
        // ---------------------------------------------------------------
        $(document).on('submit', '#dafo-sc-login-form', function(e) {
            e.preventDefault();

            var $form    = $(this);
            var $btn     = $form.find('.dafo-sc-submit');
            var $msgs    = $form.find('.dafo-sc-messages');
            var origText = $btn.text();

            $msgs.removeClass('error success').text('');
            $btn.prop('disabled', true).text(dafoscData.loginText || 'Logging in…');

            $.ajax({
                url:    dafoscData.ajaxUrl,
                method: 'POST',
                data:   $form.serialize(),
                success: function(resp) {
                    if (resp.success) {
                        $msgs.addClass('success').text(dafoscData.loginSuccess || 'Login successful. Redirecting…');
                        window.location.href = resp.data.redirect;
                    } else {
                        $msgs.addClass('error').text(resp.data.message);
                        $btn.prop('disabled', false).text(origText);
                    }
                },
                error: function() {
                    $msgs.addClass('error').text(dafoscData.networkError || 'A network error occurred. Please try again.');
                    $btn.prop('disabled', false).text(origText);
                }
            });
        });

        // ---------------------------------------------------------------
        // Shortcode: email registration
        // ---------------------------------------------------------------
        $(document).on('submit', '#dafo-sc-register-form', function(e) {
            e.preventDefault();

            var $form    = $(this);
            var $btn     = $form.find('.dafo-sc-submit');
            var $msgs    = $form.find('.dafo-sc-messages');
            var origText = $btn.text();

            $msgs.removeClass('error success').text('');

            // Client-side password confirmation check
            var pw  = $form.find('[name="password"]').val();
            var pw2 = $form.find('[name="password2"]').val();

            if (pw !== pw2) {
                $msgs.addClass('error').text(dafoscData.passwordMismatch || 'Passwords do not match.');
                return;
            }

            if (pw.length < 8) {
                $msgs.addClass('error').text(dafoscData.passwordShort || 'Password must be at least 8 characters.');
                return;
            }

            $btn.prop('disabled', true).text(dafoscData.registerText || 'Creating account…');

            $.ajax({
                url:    dafoscData.ajaxUrl,
                method: 'POST',
                data:   $form.serialize(),
                success: function(resp) {
                    if (resp.success) {
                        $msgs.addClass('success').text(resp.data.message || (dafoscData.registerSuccess || 'Account created! Redirecting…'));
                        window.location.href = resp.data.redirect;
                    } else {
                        $msgs.addClass('error').text(resp.data.message);
                        $btn.prop('disabled', false).text(origText);
                    }
                },
                error: function() {
                    $msgs.addClass('error').text(dafoscData.networkError || 'A network error occurred. Please try again.');
                    $btn.prop('disabled', false).text(origText);
                }
            });
        });

    });

})(jQuery);
