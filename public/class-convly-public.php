<?php
/**
 * The public-facing functionality of the plugin
 *
 * @package    Convly
 * @subpackage Convly/public
 */

class Convly_Public {

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
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side
     */
    public function enqueue_styles() {
        // Usually no styles needed for tracking
    }

    /**
     * Register the JavaScript for the public-facing side
     */
    public function enqueue_scripts() {
        if (!$this->should_track()) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name, 
            CONVLY_PLUGIN_URL . 'public/js/convly-public.js', 
            array('jquery'), 
            $this->version, 
            true
        );

        // Get current page info
        $page_info = $this->get_current_page_info();
        
        // Get buttons to track for this page
        $buttons_to_track = $this->get_buttons_to_track($page_info['page_id']);

        wp_localize_script($this->plugin_name, 'convly_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('convly_tracking_nonce'),
            'page_info' => $page_info,
            'buttons' => $buttons_to_track,
            'visitor_id' => $this->get_visitor_id()
        ));
    }

    /**
     * Add tracking script to footer
     */
    public function add_tracking_script() {
        if (!$this->should_track()) {
            return;
        }

        // Additional inline script for immediate tracking
        ?>
        <script type="text/javascript">
            // Ensure tracking starts as soon as possible
            if (typeof convlyTracker !== 'undefined') {
                convlyTracker.init();
            }
        </script>
        <?php
    }

    /**
     * Check if tracking should be enabled
     */
    private function should_track() {
        // Check if tracking is enabled
        if (!get_option('convly_enable_tracking', 1)) {
            return false;
        }

        // Check if user is logged in and should be tracked
        if (is_user_logged_in() && !get_option('convly_track_logged_in_users', 0)) {
            return false;
        }

        // Check user role exclusions
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $excluded_roles = get_option('convly_excluded_roles', array());
            
            foreach ($user->roles as $role) {
                if (in_array($role, $excluded_roles)) {
                    return false;
                }
            }
        }

        // Don't track admin pages
        if (is_admin()) {
            return false;
        }

        // Check if current page is active for tracking
        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';
        $page_id = $this->get_current_page_id();
        
        if ($page_id) {
            $is_active = $wpdb->get_var($wpdb->prepare(
                "SELECT is_active FROM $table_pages WHERE page_id = %d",
                $page_id
            ));
            
            if ($is_active !== null && !$is_active) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get current page information
     */
    private function get_current_page_info() {
        global $post;
        
        $page_info = array(
            'page_id' => 0,
            'page_url' => '',
            'page_title' => '',
            'page_type' => 'page'
        );

        if (is_singular()) {
            $page_info['page_id'] = get_the_ID();
            $page_info['page_url'] = get_permalink();
            $page_info['page_title'] = get_the_title();
            
            // Determine page type
            if (is_page()) {
                $page_info['page_type'] = 'pages';
            } elseif (is_single()) {
                if (get_post_type() === 'product') {
                    $page_info['page_type'] = 'products';
                } else {
                    $page_info['page_type'] = 'posts';
                }
            }
        } elseif (is_home() || is_front_page()) {
            $page_info['page_id'] = get_option('page_on_front') ?: 0;
            $page_info['page_url'] = home_url('/');
            $page_info['page_title'] = get_bloginfo('name') . ' - ' . get_bloginfo('description');
            $page_info['page_type'] = 'pages';
        } elseif (is_archive()) {
            $page_info['page_url'] = get_the_archive_link();
            
            if (is_category()) {
                $page_info['page_title'] = single_cat_title('', false);
                $page_info['page_type'] = 'posts';
            } elseif (is_tag()) {
                $page_info['page_title'] = single_tag_title('', false);
                $page_info['page_type'] = 'posts';
            } elseif (is_post_type_archive('product')) {
                $page_info['page_title'] = 'Products Archive';
                $page_info['page_type'] = 'products';
            } else {
                $page_info['page_title'] = get_the_archive_title();
                $page_info['page_type'] = 'pages';
            }
        }

        // Check for custom tab assignment
        if ($page_info['page_id']) {
            global $wpdb;
            $table_pages = $wpdb->prefix . 'convly_pages';
            $custom_tab = $wpdb->get_var($wpdb->prepare(
                "SELECT custom_tab FROM $table_pages WHERE page_id = %d",
                $page_info['page_id']
            ));
            
            if ($custom_tab) {
                $page_info['page_type'] = $custom_tab;
            }
        }

        return $page_info;
    }

    /**
     * Get current page ID
     */
    private function get_current_page_id() {
        if (is_singular()) {
            return get_the_ID();
        } elseif (is_home() || is_front_page()) {
            return get_option('page_on_front') ?: 0;
        }
        return 0;
    }

    /**
     * Get buttons to track for current page
     */
    private function get_buttons_to_track($page_id) {
        if (!$page_id) {
            return array();
        }

        global $wpdb;
        $table_buttons = $wpdb->prefix . 'convly_buttons';
        
        $buttons = $wpdb->get_results($wpdb->prepare(
            "SELECT button_css_id, button_name, button_type 
             FROM $table_buttons 
             WHERE page_id = %d AND is_active = 1",
            $page_id
        ), ARRAY_A);

        return $buttons ?: array();
    }

    /**
     * Get or generate visitor ID
     */
    private function get_visitor_id() {
        if (isset($_COOKIE['convly_visitor_id'])) {
            return $_COOKIE['convly_visitor_id'];
        }

        // Generate new visitor ID
        $visitor_id = wp_generate_uuid4();
        
        // Set cookie for 365 days
        setcookie(
            'convly_visitor_id', 
            $visitor_id, 
            time() + (365 * DAY_IN_SECONDS), 
            COOKIEPATH, 
            COOKIE_DOMAIN, 
            is_ssl(), 
            true
        );

        return $visitor_id;
    }

    /**
     * Get the archive link
     */
    private function get_the_archive_link() {
        if (is_category()) {
            return get_category_link(get_query_var('cat'));
        } elseif (is_tag()) {
            return get_tag_link(get_query_var('tag_id'));
        } elseif (is_author()) {
            return get_author_posts_url(get_query_var('author'));
        } elseif (is_date()) {
            if (is_day()) {
                return get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
            } elseif (is_month()) {
                return get_month_link(get_query_var('year'), get_query_var('monthnum'));
            } elseif (is_year()) {
                return get_year_link(get_query_var('year'));
            }
        } elseif (is_post_type_archive()) {
            return get_post_type_archive_link(get_post_type());
        }
        
        return get_home_url();
    }
}