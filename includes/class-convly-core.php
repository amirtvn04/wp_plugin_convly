<?php
/**
 * The core plugin class
 *
 * @package    Convly
 * @subpackage Convly/includes
 */

class Convly_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks
     */
    protected $loader;

    /**
     * The unique identifier of this plugin
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     */
    protected $version;

    /**
     * Define the core functionality of the plugin
     */
    public function __construct() {
        $this->version = CONVLY_VERSION;
        $this->plugin_name = 'convly';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_ajax_hooks();
    }

    /**
     * Load the required dependencies for this plugin
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters
        require_once CONVLY_PLUGIN_DIR . 'includes/class-convly-loader.php';

        // The class responsible for defining internationalization functionality
        require_once CONVLY_PLUGIN_DIR . 'includes/class-convly-i18n.php';

        // The class responsible for defining all actions that occur in the admin area
        require_once CONVLY_PLUGIN_DIR . 'admin/class-convly-admin.php';

        // The class responsible for defining all actions that occur in the public-facing side
        require_once CONVLY_PLUGIN_DIR . 'public/class-convly-public.php';

        // The class responsible for handling AJAX requests
        require_once CONVLY_PLUGIN_DIR . 'includes/class-convly-ajax.php';

        // The class responsible for tracking functionality
        //require_once CONVLY_PLUGIN_DIR . 'includes/class-convly-tracker.php';

        // The class responsible for reports
        //require_once CONVLY_PLUGIN_DIR . 'includes/class-convly-reports.php';

        $this->loader = new Convly_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization
     */
    private function set_locale() {
        $plugin_i18n = new Convly_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        $plugin_admin = new Convly_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        $plugin_public = new Convly_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('wp_footer', $plugin_public, 'add_tracking_script');
    }

    /**
     * Register all AJAX hooks
     */private function define_ajax_hooks() {
    $plugin_ajax = new Convly_Ajax();
    
    // Public AJAX actions (no privileges required)
    $this->loader->add_action('wp_ajax_nopriv_convly_track_view', $plugin_ajax, 'track_view');
    $this->loader->add_action('wp_ajax_convly_track_view', $plugin_ajax, 'track_view');
	$this->loader->add_action('wp_ajax_convly_sync_pages', $plugin_ajax, 'sync_pages');
    
    $this->loader->add_action('wp_ajax_nopriv_convly_track_click', $plugin_ajax, 'track_click');
    $this->loader->add_action('wp_ajax_convly_track_click', $plugin_ajax, 'track_click');
    
    // Admin AJAX actions
    $this->loader->add_action('wp_ajax_convly_get_stats', $plugin_ajax, 'get_stats');
    $this->loader->add_action('wp_ajax_convly_get_page_list', $plugin_ajax, 'get_page_list');
    $this->loader->add_action('wp_ajax_convly_get_top_pages', $plugin_ajax, 'get_top_pages_list');
    $this->loader->add_action('wp_ajax_convly_toggle_page_status', $plugin_ajax, 'toggle_page_status');
    $this->loader->add_action('wp_ajax_convly_add_button', $plugin_ajax, 'add_button');
    $this->loader->add_action('wp_ajax_convly_update_button', $plugin_ajax, 'update_button');
    $this->loader->add_action('wp_ajax_convly_delete_button', $plugin_ajax, 'delete_button');
    $this->loader->add_action('wp_ajax_convly_add_custom_tab', $plugin_ajax, 'add_custom_tab');
    $this->loader->add_action('wp_ajax_convly_generate_pdf_report', $plugin_ajax, 'generate_pdf_report');
	$this->loader->add_action('wp_ajax_convly_generate_page_pdf', $plugin_ajax, 'generate_page_pdf');
    
    // Page details AJAX actions
    $this->loader->add_action('wp_ajax_convly_get_page_stats', $plugin_ajax, 'get_page_stats');
    $this->loader->add_action('wp_ajax_convly_get_page_chart_data', $plugin_ajax, 'get_page_chart_data');
    $this->loader->add_action('wp_ajax_convly_get_page_buttons', $plugin_ajax, 'get_page_buttons');
    $this->loader->add_action('wp_ajax_convly_get_button_chart_data', $plugin_ajax, 'get_button_chart_data');
	
	$this->loader->add_action('wp_ajax_nopriv_convly_track_scroll', $plugin_ajax, 'track_scroll');
    $this->loader->add_action('wp_ajax_convly_track_scroll', $plugin_ajax, 'track_scroll');
    
    // Settings page AJAX actions
    $this->loader->add_action('wp_ajax_convly_export_all_data', $plugin_ajax, 'export_all_data');
    $this->loader->add_action('wp_ajax_convly_clear_old_data', $plugin_ajax, 'clear_old_data');
    $this->loader->add_action('wp_ajax_convly_reset_all_data', $plugin_ajax, 'reset_all_data');
	
	// Custom tabs management
$this->loader->add_action('wp_ajax_convly_get_custom_tabs', $plugin_ajax, 'get_custom_tabs');
$this->loader->add_action('wp_ajax_convly_delete_custom_tab', $plugin_ajax, 'delete_custom_tab');
$this->loader->add_action('wp_ajax_convly_get_available_items', $plugin_ajax, 'get_available_items');
$this->loader->add_action('wp_ajax_convly_get_tab_items', $plugin_ajax, 'get_tab_items');
$this->loader->add_action('wp_ajax_convly_add_items_to_tab', $plugin_ajax, 'add_items_to_tab');
$this->loader->add_action('wp_ajax_convly_remove_item_from_tab', $plugin_ajax, 'remove_item_from_tab');
}

    /**
     * Run the loader to execute all of the hooks with WordPress
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin
     */
    public function get_version() {
        return $this->version;
    }
}