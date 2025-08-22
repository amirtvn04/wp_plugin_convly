<?php
/**
 * Fired during plugin activation
 *
 * @package    Convly
 * @subpackage Convly/includes
 */

class Convly_Activator {

    /**
     * Plugin activation handler
     */
    public static function activate() {
        self::create_database_tables();
        self::add_default_options();
        self::create_custom_capabilities();
        
        // Clear rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private static function create_database_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Table for tracking page views
        $table_views = $wpdb->prefix . 'convly_views';
        $sql_views = "CREATE TABLE IF NOT EXISTS $table_views (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_id bigint(20) NOT NULL,
            page_url varchar(255) NOT NULL,
            page_title varchar(255) NOT NULL,
            page_type varchar(50) NOT NULL,
            visitor_id varchar(64) NOT NULL,
            device_type varchar(20) NOT NULL,
            view_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY page_id (page_id),
            KEY visitor_id (visitor_id),
            KEY view_date (view_date),
            KEY page_type (page_type)
        ) $charset_collate;";

        // Table for tracking button clicks
        $table_clicks = $wpdb->prefix . 'convly_clicks';
        $sql_clicks = "CREATE TABLE IF NOT EXISTS $table_clicks (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_id bigint(20) NOT NULL,
            button_id varchar(255) NOT NULL,
            button_name varchar(255) DEFAULT NULL,
            visitor_id varchar(64) NOT NULL,
            device_type varchar(20) NOT NULL,
            click_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY page_id (page_id),
            KEY button_id (button_id),
            KEY visitor_id (visitor_id),
            KEY click_date (click_date)
        ) $charset_collate;";

        // Table for button configurations
        $table_buttons = $wpdb->prefix . 'convly_buttons';
        $sql_buttons = "CREATE TABLE IF NOT EXISTS $table_buttons (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_id bigint(20) NOT NULL,
            button_css_id varchar(255) NOT NULL,
            button_name varchar(255) NOT NULL,
            button_type varchar(50) DEFAULT 'button',
            is_active tinyint(1) DEFAULT 1,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY page_id (page_id),
            KEY button_css_id (button_css_id)
        ) $charset_collate;";

        // Table for page settings
        $table_pages = $wpdb->prefix . 'convly_pages';
        $sql_pages = "CREATE TABLE IF NOT EXISTS $table_pages (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_id bigint(20) NOT NULL,
            page_url varchar(255) NOT NULL,
            page_title varchar(255) NOT NULL,
            page_type varchar(50) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            custom_tab varchar(100) DEFAULT NULL,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY page_id (page_id),
            KEY page_type (page_type),
            KEY is_active (is_active)
        ) $charset_collate;";

        // Table for custom tabs
        $table_tabs = $wpdb->prefix . 'convly_tabs';
        $sql_tabs = "CREATE TABLE IF NOT EXISTS $table_tabs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            tab_name varchar(100) NOT NULL,
            tab_slug varchar(100) NOT NULL,
            tab_order int(11) DEFAULT 0,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY tab_slug (tab_slug)
        ) $charset_collate;";
		

// Table for scroll depth tracking
$table_scroll = $wpdb->prefix . 'convly_scroll_depth';
$sql_scroll = "CREATE TABLE IF NOT EXISTS $table_scroll (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    page_id bigint(20) NOT NULL,
    visitor_id varchar(64) NOT NULL,
    max_scroll_depth int(3) NOT NULL,
    view_date datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY page_id (page_id),
    KEY visitor_id (visitor_id)
) $charset_collate;";



        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_views);
        dbDelta($sql_clicks);
        dbDelta($sql_buttons);
        dbDelta($sql_pages);
        dbDelta($sql_tabs);
		dbDelta($sql_scroll);
    }

    /**
     * Add default plugin options
     */
    private static function add_default_options() {
        add_option('convly_version', CONVLY_VERSION);
        add_option('convly_enable_tracking', 1);
        add_option('convly_track_logged_in_users', 0);
        add_option('convly_excluded_roles', array());
        add_option('convly_cache_compatibility', 1);
    }

    /**
     * Create custom capabilities
     */
    private static function create_custom_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('manage_convly');
            $role->add_cap('view_convly_reports');
        }
    }
}