<?php
/**
 * Plugin deactivation handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class DAFO_Social_Login_Deactivator {
    
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
