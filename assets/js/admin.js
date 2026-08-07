/**
 * Admin Scripts
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Toggle credential fields based on enabled providers
        function toggleProviderFields() {
            $('input[name="dafo_social_login_settings[enabled_providers][]"]').each(function() {
                var provider = $(this).val();
                var isChecked = $(this).is(':checked');
                var $section = $(this).closest('tr').nextAll('h2:contains("' + provider.charAt(0).toUpperCase() + provider.slice(1) + '")').first().nextUntil('h2');
                
                // This is a simple implementation - you may want to enhance it
            });
        }
        
        toggleProviderFields();
        $('input[name="dafo_social_login_settings[enabled_providers][]"]').on('change', toggleProviderFields);
        
        // Copy redirect URI on click
        $(document).on('click', '.form-table code', function() {
            var text = $(this).text();
            
            // Create temporary input
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            
            // Show feedback
            var $original = $(this);
            var originalText = $original.text();
            $original.text('Copied!');
            
            setTimeout(function() {
                $original.text(originalText);
            }, 2000);
        });
    });
    
})(jQuery);
