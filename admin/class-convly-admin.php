<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @package    Convly
 * @subpackage Convly/admin
 */

class Convly_Admin
{

    /**
     * The ID of this plugin
     */
    private $plugin_name;

    /**
     * The version of this plugin
     */
    private $version;

    /**
     * Initialize the class and set its properties
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('admin_head', array($this, 'add_no_cache_meta'));

    }

    public function auto_collapse_sidebar() {
        if (isset($_GET['page']) && $_GET['page'] === 'convly') : ?>
            <script>
                (function($){
                    $(document).ready(function(){
                        $('body').addClass('folded');
                    });
                })(jQuery);
            </script>
        <?php
        endif;
    }


    /**
     * Register the stylesheets for the admin area
     */
    public function enqueue_styles()
    {
        // Debug: Always load on admin pages for testing
        if (strpos($_SERVER['REQUEST_URI'], 'page=convly') !== false) {
            wp_enqueue_style($this->plugin_name . "main", CONVLY_PLUGIN_URL . 'admin/css/main.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name, CONVLY_PLUGIN_URL . 'admin/css/convly-admin.css', array(), $this->version, 'all');
            wp_enqueue_style('convly-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css', array(), '4.4.0');
        }
    }

    /**
     * Register the JavaScript for the admin area
     */
    public function enqueue_scripts()
    {
        // Debug: Check if we're on a Convly page
        if (strpos($_SERVER['REQUEST_URI'], 'page=convly') !== false) {
            // Load Chart.js
            wp_enqueue_script('convly-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js', array(), '4.4.0', true);
            wp_enqueue_script('convly-apexchart', CONVLY_PLUGIN_URL . 'admin/js/apexcharts.js', array(), $this->version, true);

            // Load admin script
            wp_enqueue_script($this->plugin_name, CONVLY_PLUGIN_URL . 'admin/js/convly-admin.js', array('jquery', 'convly-chartjs'), time(), true);

            // Localize script for admin dashboard
            wp_localize_script($this->plugin_name, 'convly_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('convly_ajax_nonce'),
                'i18n' => array(
                    'loading' => __('Loading...', 'convly'),
                    'error' => __('An error occurred', 'convly'),
                    'confirm_delete' => __('Are you sure you want to delete this?', 'convly'),
                    'views' => __('Views', 'convly'),
                    'clicks' => __('Clicks', 'convly'),
                    'conversion_rate' => __('Conversion Rate', 'convly'),
                    'add_button' => __('Add Button', 'convly'),
                    'edit' => __('Edit', 'convly'),
                    'delete' => __('Delete', 'convly'),
                    'details' => __('Details', 'convly'),
                    'active' => __('Active', 'convly'),
                    'inactive' => __('Inactive', 'convly'),
                    'no_buttons' => __('No buttons configured', 'convly'),
                    'button_name' => __('Button Name', 'convly'),
                    'css_id' => __('CSS ID', 'convly'),
                    'type' => __('Type', 'convly'),
                    'actions' => __('Actions', 'convly'),
                    'edit_button' => __('Edit Button', 'convly'),
                )
            ));

            // For page details page
            if (isset($_GET['page']) && $_GET['page'] === 'convly-page-details') {
                wp_enqueue_script('convly-page-details', CONVLY_PLUGIN_URL . 'admin/js/convly-page-details.js', array('jquery', 'convly-chartjs', 'convly-apexchart'), $this->version, true);

                // Also localize for page details script
                wp_localize_script('convly-page-details', 'convly_ajax', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('convly_ajax_nonce'),
                    'i18n' => array(
                        'loading' => __('Loading...', 'convly'),
                        'error' => __('An error occurred', 'convly'),
                        'confirm_delete' => __('Are you sure you want to delete this?', 'convly'),
                        'views' => __('Views', 'convly'),
                        'clicks' => __('Clicks', 'convly'),
                        'add_button' => __('Add Button', 'convly'),
                        'edit' => __('Edit', 'convly'),
                        'delete' => __('Delete', 'convly'),
                        'no_buttons' => __('No buttons configured', 'convly'),
                        'button_name' => __('Button Name', 'convly'),
                        'css_id' => __('CSS ID', 'convly'),
                        'type' => __('Type', 'convly'),
                        'actions' => __('Actions', 'convly'),
                        'edit_button' => __('Edit Button', 'convly'),
                    )
                ));
            }
        }
    }

    /**
     * Add admin menu items
     */
    public function add_plugin_admin_menu()
    {
        add_menu_page(
            __('Convly Dashboard', 'convly'),
            __('Convly', 'convly'),
            'manage_options', // Changed from 'manage_convly' to 'manage_options'
            'convly',
            array($this, 'display_admin_dashboard'),
            'dashicons-chart-area',
            30
        );

        add_submenu_page(
            'convly',
            __('Dashboard', 'convly'),
            __('Dashboard', 'convly'),
            'manage_options',
            'convly',
            array($this, 'display_admin_dashboard')
        );

        add_submenu_page(
            'convly',
            __('Page Details', 'convly'),
            __('Page Details', 'convly'),
            'manage_options',
            'convly-page-details',
            array($this, 'display_page_details')
        );

        add_submenu_page(
            'convly',
            __('Settings', 'convly'),
            __('Settings', 'convly'),
            'manage_options',
            'convly-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Render the admin dashboard
     */
    public function display_admin_dashboard()
    {
        require_once CONVLY_PLUGIN_DIR . 'admin/partials/convly-admin-dashboard.php';
    }

    /**
     * Render the page details
     */
    public function display_page_details()
    {
        require_once CONVLY_PLUGIN_DIR . 'admin/partials/convly-page-details.php';
    }

    /**
     * Render the settings page
     */
    public function display_settings_page()
    {
        require_once CONVLY_PLUGIN_DIR . 'admin/partials/convly-settings-page.php';
    }

    /**
     * Register plugin settings
     */
    public function register_settings()
    {
        register_setting('convly_settings', 'convly_enable_tracking');
        register_setting('convly_settings', 'convly_track_logged_in_users');
        register_setting('convly_settings', 'convly_excluded_roles');
        register_setting('convly_settings', 'convly_cache_compatibility');
    }

    /**
     * Add no-cache meta tags to admin pages
     */
    public function add_no_cache_meta()
    {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'convly') !== false) {
            ?>
            <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
            <meta http-equiv="Pragma" content="no-cache"/>
            <meta http-equiv="Expires" content="0"/>
            <?php
        }
    }

    /**
     * Check if current page is a Convly admin page
     */
    private function is_convly_admin_page()
    {
        $screen = get_current_screen();
        return $screen && strpos($screen->id, 'convly') !== false;
    }
}