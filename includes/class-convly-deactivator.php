<?php
/**
 * Fired during plugin deactivation
 *
 * @package    Convly
 * @subpackage Convly/includes
 */

class Convly_Deactivator {

    /**
     * Plugin deactivation handler
     */
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('convly_daily_cleanup');
        
        // Clear any transients
        self::clear_transients();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Clear all plugin transients
     */
    private static function clear_transients() {
        global $wpdb;
        
        $transients = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_convly_%' 
             OR option_name LIKE '_transient_timeout_convly_%'"
        );
        
        foreach ($transients as $transient) {
            delete_option($transient);
        }
    }
}