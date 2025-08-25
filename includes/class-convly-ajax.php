<?php
/**
 * Handle AJAX requests
 *
 * @package    Convly
 * @subpackage Convly/includes
 */

class Convly_Ajax
{

    /**
     * Set no-cache headers
     */
    private function set_no_cache_headers()
    {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }


    /**
     * Track page view
     */
    public function track_view()
    {
        $this->set_no_cache_headers();
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'convly_tracking_nonce')) {
            wp_die();
        }

        // Check if tracking is enabled
        if (!get_option('convly_enable_tracking', 1)) {
            wp_die();
        }

        // Get data
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
        $page_title = isset($_POST['page_title']) ? sanitize_text_field($_POST['page_title']) : '';
        $page_type = isset($_POST['page_type']) ? sanitize_text_field($_POST['page_type']) : 'page';
        $visitor_id = isset($_POST['visitor_id']) ? sanitize_text_field($_POST['visitor_id']) : '';

        // Detect device type
        $device_type = wp_is_mobile() ? 'mobile' : 'desktop';

        // Check if user should be tracked
        if (is_user_logged_in() && !get_option('convly_track_logged_in_users', 0)) {
            wp_die();
        }

        // Save to database
        global $wpdb;
        $table_views = $wpdb->prefix . 'convly_views';
        $table_pages = $wpdb->prefix . 'convly_pages';

        // Add page to tracking if not exists
        $wpdb->insert(
            $table_pages,
            array(
                'page_id' => $page_id,
                'page_url' => $page_url,
                'page_title' => $page_title,
                'page_type' => $page_type
            ),
            array('%d', '%s', '%s', '%s')
        );

        // Insert view record
        $result = $wpdb->insert(
            $table_views,
            array(
                'page_id' => $page_id,
                'page_url' => $page_url,
                'page_title' => $page_title,
                'page_type' => $page_type,
                'visitor_id' => $visitor_id,
                'device_type' => $device_type,
                'view_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        wp_send_json_success($result);
    }

    /**
     * Track button click
     */
    public function track_click()
    {
        $this->set_no_cache_headers();
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'convly_tracking_nonce')) {
            wp_die();
        }

        // Get data
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $button_id = isset($_POST['button_id']) ? sanitize_text_field($_POST['button_id']) : '';
        $visitor_id = isset($_POST['visitor_id']) ? sanitize_text_field($_POST['visitor_id']) : '';

        // Detect device type
        $device_type = wp_is_mobile() ? 'mobile' : 'desktop';

        // Get button name from configurations
        global $wpdb;
        $table_buttons = $wpdb->prefix . 'convly_buttons';
        $button_name = $wpdb->get_var($wpdb->prepare(
            "SELECT button_name FROM $table_buttons WHERE page_id = %d AND button_css_id = %s",
            $page_id,
            $button_id
        ));

        // Save click
        $table_clicks = $wpdb->prefix . 'convly_clicks';
        $result = $wpdb->insert(
            $table_clicks,
            array(
                'page_id' => $page_id,
                'button_id' => $button_id,
                'button_name' => $button_name ?: $button_id,
                'visitor_id' => $visitor_id,
                'device_type' => $device_type,
                'click_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );

        wp_send_json_success($result);
    }

    /**
     * Track scroll depth
     */
    public function track_scroll()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'convly_tracking_nonce')) {
            wp_die();
        }

        // Get data
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $visitor_id = isset($_POST['visitor_id']) ? sanitize_text_field($_POST['visitor_id']) : '';
        $scroll_depth = isset($_POST['scroll_depth']) ? intval($_POST['scroll_depth']) : 0;

        // Save to database
        global $wpdb;
        $table_scroll = $wpdb->prefix . 'convly_scroll_depth';

        $wpdb->replace(
            $table_scroll,
            array(
                'page_id' => $page_id,
                'visitor_id' => $visitor_id,
                'max_scroll_depth' => $scroll_depth,
                'view_date' => current_time('mysql')
            ),
            array('%d', '%s', '%d', '%s')
        );

        wp_send_json_success();
    }

    /**
     * Get statistics data
     */
    public function get_stats()
    {
        $this->set_no_cache_headers();
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'summary';
        $metric = isset($_POST['metric']) ? sanitize_text_field($_POST['metric']) : '';
        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '24_hours';

        if ($type === 'chart_data') {
            $data = $this->get_chart_data($period);
        } else {
            $data = $this->get_metric_data($metric, $period);
        }

        wp_send_json_success($data);
    }

    /**
     * Get metric data
     */
    private function get_metric_data($metric, $period)
    {
        global $wpdb;
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        // Calculate date range
        $date_range = $this->get_date_range($period);
        $current_start = $date_range['start'];
        $current_end = $date_range['end'];

        // Calculate previous period for comparison
        $period_diff = strtotime($current_end) - strtotime($current_start);
        $previous_start = date('Y-m-d H:i:s', strtotime($current_start) - $period_diff);
        $previous_end = $current_start;

        switch ($metric) {
            case 'total_views':
                $current_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_views WHERE view_date BETWEEN %s AND %s",
                    $current_start,
                    $current_end
                ));

                $previous_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_views WHERE view_date BETWEEN %s AND %s",
                    $previous_start,
                    $previous_end
                ));
                break;

            case 'total_clicks':
                $current_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_clicks WHERE click_date BETWEEN %s AND %s",
                    $current_start,
                    $current_end
                ));

                $previous_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_clicks WHERE click_date BETWEEN %s AND %s",
                    $previous_start,
                    $previous_end
                ));
                break;

            case 'conversion_rate':
                // Current period - based on unique visitors
                $current_visitors = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM $table_views WHERE view_date BETWEEN %s AND %s",
                    $current_start,
                    $current_end
                ));

                $current_clicks = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_clicks WHERE click_date BETWEEN %s AND %s",
                    $current_start,
                    $current_end
                ));

                $current_value = $current_visitors > 0 ? round(($current_clicks / $current_visitors) * 100, 1) : 0;

                // Previous period - based on unique visitors
                $previous_visitors = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM $table_views WHERE view_date BETWEEN %s AND %s",
                    $previous_start,
                    $previous_end
                ));

                $previous_clicks = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_clicks WHERE click_date BETWEEN %s AND %s",
                    $previous_start,
                    $previous_end
                ));

                $previous_value = $previous_visitors > 0 ? round(($previous_clicks / $previous_visitors) * 100, 1) : 0;

                $current_value = $current_value . '%';
                break;

            default:
                $current_value = 0;
                $previous_value = 0;
        }

        // Calculate change
        $change = 0;
        if ($previous_value > 0 && $metric !== 'conversion_rate') {
            $change = round((($current_value - $previous_value) / $previous_value) * 100, 1);
        } elseif ($previous_value == 0 && $metric !== 'conversion_rate') {
            $change = $current_value;
        } elseif ($metric === 'conversion_rate') {
            $current_num = floatval($current_value);
            $change = round($current_num - $previous_value, 1);
        }

        return array(
            'value' => $current_value,
            'change' => $change
        );
    }

    /**
     * Get chart data
     */
    private function get_chart_data($period)
    {
        global $wpdb;
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        $date_range = $this->get_date_range($period);
        $labels = array();
        $views_data = array();
        $clicks_data = array();

        // Generate labels and get data based on period
        switch ($period) {
            case '24_hours':
                // Hourly data
                for ($i = 23; $i >= 0; $i--) {
                    $hour = date('Y-m-d H:00:00', strtotime("-$i hours"));
                    $labels[] = date('H:00', strtotime($hour));

                    $views = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_views 
                         WHERE view_date >= %s AND view_date < DATE_ADD(%s, INTERVAL 1 HOUR)",
                        $hour,
                        $hour
                    ));

                    $clicks = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks 
                         WHERE click_date >= %s AND click_date < DATE_ADD(%s, INTERVAL 1 HOUR)",
                        $hour,
                        $hour
                    ));

                    $views_data[] = intval($views);
                    $clicks_data[] = intval($clicks);
                }
                break;

            case '7_days':
            case '30_days':
                // Daily data
                $days = $period === '7_days' ? 7 : 30;
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $labels[] = date('M d', strtotime($date));

                    $views = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_views 
                         WHERE DATE(view_date) = %s",
                        $date
                    ));

                    $clicks = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks 
                         WHERE DATE(click_date) = %s",
                        $date
                    ));

                    $views_data[] = intval($views);
                    $clicks_data[] = intval($clicks);
                }
                break;

            case '3_months':
            case '6_months':
            case '12_months':
                // Monthly data
                $months = $period === '3_months' ? 3 : ($period === '6_months' ? 6 : 12);
                for ($i = $months - 1; $i >= 0; $i--) {
                    $month = date('Y-m', strtotime("-$i months"));
                    $labels[] = date('M Y', strtotime($month . '-01'));

                    $views = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_views 
                         WHERE DATE_FORMAT(view_date, '%%Y-%%m') = %s",
                        $month
                    ));

                    $clicks = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks 
                         WHERE DATE_FORMAT(click_date, '%%Y-%%m') = %s",
                        $month
                    ));

                    $views_data[] = intval($views);
                    $clicks_data[] = intval($clicks);
                }
                break;
        }

        return array(
            'labels' => $labels,
            'views' => $views_data,
            'clicks' => $clicks_data
        );
    }

    /**
     * Get date range for period
     */
    private function get_date_range($period)
    {
        $end = current_time('mysql');

        switch ($period) {
            case '24_hours':
                $start = date('Y-m-d H:i:s', strtotime('-24 hours'));
                break;
            case '7_days':
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case '30_days':
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case '3_months':
                $start = date('Y-m-d 00:00:00', strtotime('-3 months'));
                break;
            case '6_months':
                $start = date('Y-m-d 00:00:00', strtotime('-6 months'));
                break;
            case '12_months':
                $start = date('Y-m-d 00:00:00', strtotime('-12 months'));
                break;
            default:
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
        }

        return array(
            'start' => $start,
            'end' => $end
        );
    }

    /**
     * Get pages list
     */
    public function get_page_list()
    {
        $this->set_no_cache_headers();

        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';
        $table_buttons = $wpdb->prefix . 'convly_buttons';

        // Get parameters - اضافه کردن پارامتر جستجو
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'pages';
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = -1;
        $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'conversion_rate_desc';
        $date_filter = isset($_POST['date_filter']) ? sanitize_text_field($_POST['date_filter']) : 'all';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : ''; // پارامتر جستجو

        // Build WHERE clause
        $where = array("p.page_type = %s");
        $where_values = array($tab);

        // اضافه کردن شرط جستجو
        if (!empty($search)) {
            $where[] = "(p.page_title LIKE %s OR p.page_url LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        // Date filter
        if ($date_filter !== 'all') {
            $date_range = $this->get_date_range_for_filter($date_filter, $_POST);
            if ($date_range) {
                $where[] = "v.view_date BETWEEN %s AND %s";
                $where_values[] = $date_range['start'];
                $where_values[] = $date_range['end'];
            }
        }

        $where_clause = implode(' AND ', $where);

        // Build ORDER BY clause
        $order_by = $this->get_order_by_clause($sort_by);

        // Get total count - با در نظر گرفتن جستجو
        $total_query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT p.page_id) 
         FROM $table_pages p
         LEFT JOIN $table_views v ON p.page_id = v.page_id
         WHERE $where_clause",
            $where_values
        );
        $total = $wpdb->get_var($total_query);

        // Calculate offset
        $offset = ($page - 1) * $per_page;

        // Get all WordPress pages/posts first
        $wp_posts = get_posts(array(
            'post_type' => $tab === 'products' ? 'product' : ($tab === 'posts' ? 'post' : 'page'),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            's' => $search // اضافه کردن جستجو در وردپرس
        ));

        // Insert pages into tracking table if not exists
        foreach ($wp_posts as $post) {
            $existing_page = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_pages WHERE page_id = %d",
                $post->ID
            ));

            if (!$existing_page) {
                $wpdb->insert(
                    $table_pages,
                    array(
                        'page_id' => $post->ID,
                        'page_url' => get_permalink($post->ID),
                        'page_title' => $post->post_title,
                        'page_type' => $tab,
                        'is_active' => 1
                    ),
                    array('%d', '%s', '%s', '%s', '%d')
                );
            }
        }

        // Get pages with stats - با در نظر گرفتن جستجو
        $query = $wpdb->prepare(
            "SELECT 
            p.*,
            IFNULL(COUNT(DISTINCT v.visitor_id), 0) as unique_visitors,
            IFNULL(COUNT(v.id), 0) as total_views,
            IFNULL(COUNT(DISTINCT c.id), 0) as total_clicks,
            EXISTS(SELECT 1 FROM $table_buttons WHERE page_id = p.page_id) as has_buttons
         FROM $table_pages p
         LEFT JOIN $table_views v ON p.page_id = v.page_id
         LEFT JOIN $table_clicks c ON p.page_id = c.page_id
         WHERE $where_clause
         GROUP BY p.page_id
         ORDER BY $order_by",
            $where_values
        );

        $results = $wpdb->get_results($query, ARRAY_A);

        wp_send_json_success(array(
            'items' => $results,
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'search' => $search, // بازگرداندن عبارت جستجو
            'sort_by' => $sort_by,
            'date_filter' => $date_filter
        ));
    }


    /**
     * Get Top pages
     */
    public function get_top_pages_list()
    {
        $this->set_no_cache_headers();

        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        global $wpdb;
        $table_pages   = $wpdb->prefix . 'convly_pages';
        $table_views   = $wpdb->prefix . 'convly_views';
        $table_clicks  = $wpdb->prefix . 'convly_clicks';
        $table_buttons = $wpdb->prefix . 'convly_buttons';

        // دریافت تب (pages یا posts یا products)
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'pages';

        $where        = array("p.page_type = %s");
        $where_values = array($tab);

        $query = $wpdb->prepare(
            "SELECT 
            p.*,
            IFNULL(COUNT(DISTINCT v.visitor_id), 0) as unique_visitors,
            IFNULL(COUNT(v.id), 0) as total_views,
            IFNULL(COUNT(DISTINCT c.id), 0) as total_clicks,
            (IFNULL(COUNT(DISTINCT c.id), 0) / NULLIF(COUNT(v.id), 0)) * 100 as conversion_rate,
            EXISTS(SELECT 1 FROM $table_buttons WHERE page_id = p.page_id) as has_buttons
         FROM $table_pages p
         LEFT JOIN $table_views v ON p.page_id = v.page_id
         LEFT JOIN $table_clicks c ON p.page_id = c.page_id
         WHERE " . implode(' AND ', $where) . "
         GROUP BY p.page_id
         ORDER BY conversion_rate DESC
         LIMIT 5",
            $where_values
        );

        $results = $wpdb->get_results($query, ARRAY_A);

        wp_send_json_success(array(
            'items' => $results,
            'tab'   => $tab
        ));
    }




    /**
     * Get date range for filter
     */
    private function get_date_range_for_filter($filter, $post_data)
    {
        switch ($filter) {
            case 'today':
                return array(
                    'start' => date('Y-m-d 00:00:00'),
                    'end' => current_time('mysql')
                );
            case 'yesterday':
                return array(
                    'start' => date('Y-m-d 00:00:00', strtotime('-1 day')),
                    'end' => date('Y-m-d 23:59:59', strtotime('-1 day'))
                );
            case '7_days':
                return array(
                    'start' => date('Y-m-d 00:00:00', strtotime('-7 days')),
                    'end' => current_time('mysql')
                );
            case '30_days':
                return array(
                    'start' => date('Y-m-d 00:00:00', strtotime('-30 days')),
                    'end' => current_time('mysql')
                );
            case 'custom':
                if (isset($post_data['date_from']) && isset($post_data['date_to'])) {
                    return array(
                        'start' => sanitize_text_field($post_data['date_from']) . ' 00:00:00',
                        'end' => sanitize_text_field($post_data['date_to']) . ' 23:59:59'
                    );
                }
                break;
        }
        return null;
    }

    /**
     * Get ORDER BY clause
     */
    private function get_order_by_clause($sort_by)
    {
        switch ($sort_by) {
            case 'views_desc':
                return 'total_views DESC';
            case 'clicks_desc':
                return 'total_clicks DESC';
            case 'conversion_rate_desc':
                return '(total_clicks / NULLIF(unique_visitors, 0)) DESC';
            case 'name_asc':
                return 'p.page_title ASC';
            case 'name_desc':
                return 'p.page_title DESC';
            default:
                return '(total_clicks / NULLIF(total_views, 0)) DESC';
        }
    }

    /**
     * Toggle page status
     */
    public function toggle_page_status()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 0;

        if (!$page_id) {
            wp_send_json_error('Invalid page ID');
        }

        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';

        $result = $wpdb->update(
            $table_pages,
            array('is_active' => $is_active),
            array('page_id' => $page_id),
            array('%d'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success('Status updated');
        } else {
            wp_send_json_error('Failed to update status');
        }
    }

    /**
     * Add button configuration
     */
    public function add_button()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $button_css_id = isset($_POST['button_css_id']) ? sanitize_text_field($_POST['button_css_id']) : '';
        $button_name = isset($_POST['button_name']) ? sanitize_text_field($_POST['button_name']) : '';
        $button_type = isset($_POST['button_type']) ? sanitize_text_field($_POST['button_type']) : 'button';

        if (!$page_id || !$button_css_id || !$button_name) {
            wp_send_json_error('Missing required fields');
        }

        global $wpdb;
        $table_buttons = $wpdb->prefix . 'convly_buttons';

        // Check if button already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_buttons WHERE page_id = %d AND button_css_id = %s",
            $page_id,
            $button_css_id
        ));

        if ($exists) {
            wp_send_json_error('Button with this CSS ID already exists for this page');
        }

        $result = $wpdb->insert(
            $table_buttons,
            array(
                'page_id' => $page_id,
                'button_css_id' => $button_css_id,
                'button_name' => $button_name,
                'button_type' => $button_type,
                'is_active' => 1,
                'created_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%d', '%s')
        );

        if ($result) {
            wp_send_json_success(array('id' => $wpdb->insert_id));
        } else {
            wp_send_json_error('Failed to add button');
        }
    }

    /**
     * Update button configuration
     */
    public function update_button()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $button_id = isset($_POST['button_id']) ? intval($_POST['button_id']) : 0;
        $button_css_id = isset($_POST['button_css_id']) ? sanitize_text_field($_POST['button_css_id']) : '';
        $button_name = isset($_POST['button_name']) ? sanitize_text_field($_POST['button_name']) : '';
        $button_type = isset($_POST['button_type']) ? sanitize_text_field($_POST['button_type']) : 'button';

        if (!$button_id || !$button_css_id || !$button_name) {
            wp_send_json_error('Missing required fields');
        }

        global $wpdb;
        $table_buttons = $wpdb->prefix . 'convly_buttons';

        $result = $wpdb->update(
            $table_buttons,
            array(
                'button_css_id' => $button_css_id,
                'button_name' => $button_name,
                'button_type' => $button_type
            ),
            array('id' => $button_id),
            array('%s', '%s', '%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success('Button updated');
        } else {
            wp_send_json_error('Failed to update button');
        }
    }

    /**
     * Delete button configuration
     */
    public function delete_button()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $button_id = isset($_POST['button_id']) ? intval($_POST['button_id']) : 0;

        if (!$button_id) {
            wp_send_json_error('Invalid button ID');
        }

        global $wpdb;
        $table_buttons = $wpdb->prefix . 'convly_buttons';

        $result = $wpdb->delete(
            $table_buttons,
            array('id' => $button_id),
            array('%d')
        );

        if ($result) {
            wp_send_json_success('Button deleted');
        } else {
            wp_send_json_error('Failed to delete button');
        }
    }

    /**
     * Add custom tab
     */
    public function add_custom_tab()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $tab_name = isset($_POST['tab_name']) ? sanitize_text_field($_POST['tab_name']) : '';

        if (!$tab_name) {
            wp_send_json_error('Tab name is required');
        }

        // Create slug
        $tab_slug = sanitize_title($tab_name);

        global $wpdb;
        $table_tabs = $wpdb->prefix . 'convly_tabs';

        // Check if tab already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_tabs WHERE tab_slug = %s",
            $tab_slug
        ));

        if ($exists) {
            wp_send_json_error('Tab with this name already exists');
        }

        // Get max order
        $max_order = $wpdb->get_var("SELECT MAX(tab_order) FROM $table_tabs");
        $new_order = $max_order ? $max_order + 1 : 1;

        $result = $wpdb->insert(
            $table_tabs,
            array(
                'tab_name' => $tab_name,
                'tab_slug' => $tab_slug,
                'tab_order' => $new_order,
                'created_date' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s')
        );

        if ($result) {
            wp_send_json_success(array(
                'id' => $wpdb->insert_id,
                'tab_name' => $tab_name,
                'tab_slug' => $tab_slug
            ));
        } else {
            wp_send_json_error('Failed to add tab');
        }
    }

    /**
     * Get custom tabs
     */
    public function get_custom_tabs()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        global $wpdb;
        $table_tabs = $wpdb->prefix . 'convly_tabs';

        $tabs = $wpdb->get_results(
            "SELECT * FROM $table_tabs ORDER BY tab_order ASC",
            ARRAY_A
        );

        wp_send_json_success($tabs);
    }

    /**
     * Delete custom tab
     */
    public function delete_custom_tab()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $tab_id = isset($_POST['tab_id']) ? intval($_POST['tab_id']) : 0;

        if (!$tab_id) {
            wp_send_json_error('Invalid tab ID');
        }

        global $wpdb;
        $table_tabs = $wpdb->prefix . 'convly_tabs';
        $table_pages = $wpdb->prefix . 'convly_pages';

        // Get tab slug
        $tab_slug = $wpdb->get_var($wpdb->prepare(
            "SELECT tab_slug FROM $table_tabs WHERE id = %d",
            $tab_id
        ));

        // Remove pages from this tab
        $wpdb->update(
            $table_pages,
            array('custom_tab' => null),
            array('custom_tab' => $tab_slug),
            array('%s'),
            array('%s')
        );

        // Delete tab
        $result = $wpdb->delete(
            $table_tabs,
            array('id' => $tab_id),
            array('%d')
        );

        if ($result) {
            wp_send_json_success('Tab deleted');
        } else {
            wp_send_json_error('Failed to delete tab');
        }
    }

    /**
     * Get available items to add to tab
     */
    public function get_available_items()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : 'page';

        // Get WordPress posts
        $args = array(
            'post_type' => $item_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC'
        );

        // Get items not already in a custom tab
        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';

        $assigned_ids = $wpdb->get_col(
            "SELECT page_id FROM $table_pages WHERE custom_tab IS NOT NULL"
        );

        if (!empty($assigned_ids)) {
            $args['post__not_in'] = $assigned_ids;
        }

        $posts = get_posts($args);

        wp_send_json_success($posts);
    }

    /**
     * Get items in a custom tab
     */
    public function get_tab_items()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $tab_slug = isset($_POST['tab_slug']) ? sanitize_text_field($_POST['tab_slug']) : '';

        if (!$tab_slug) {
            wp_send_json_error('Invalid tab');
        }

        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';

        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_pages WHERE custom_tab = %s",
            $tab_slug
        ), ARRAY_A);

        wp_send_json_success($items);
    }

    /**
     * Add items to custom tab
     */
    public function add_items_to_tab()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $tab_slug = isset($_POST['tab_slug']) ? sanitize_text_field($_POST['tab_slug']) : '';
        $item_ids = isset($_POST['item_ids']) ? array_map('intval', $_POST['item_ids']) : array();

        if (!$tab_slug || empty($item_ids)) {
            wp_send_json_error('Invalid data');
        }

        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';

        $success = true;
        foreach ($item_ids as $item_id) {
            $post = get_post($item_id);
            if (!$post) continue;

            // First, insert/update in tracking table
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_pages WHERE page_id = %d",
                $item_id
            ));

            if ($existing) {
                // Update existing
                $result = $wpdb->update(
                    $table_pages,
                    array(
                        'custom_tab' => $tab_slug,
                        'page_type' => $tab_slug
                    ),
                    array('page_id' => $item_id),
                    array('%s', '%s'),
                    array('%d')
                );
            } else {
                // Insert new
                $result = $wpdb->insert(
                    $table_pages,
                    array(
                        'page_id' => $item_id,
                        'page_url' => get_permalink($item_id),
                        'page_title' => $post->post_title,
                        'page_type' => $tab_slug,
                        'custom_tab' => $tab_slug,
                        'is_active' => 1
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%d')
                );
            }

            if ($result === false) {
                $success = false;
            }
        }

        if ($success) {
            wp_send_json_success('Items added to tab');
        } else {
            wp_send_json_error('Some items could not be added');
        }
    }

    /**
     * Remove item from custom tab
     */
    public function remove_item_from_tab()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;

        if (!$page_id) {
            wp_send_json_error('Invalid page ID');
        }

        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';

        // Get original post type from WordPress
        $post = get_post($page_id);
        $original_type = 'pages'; // default

        if ($post) {
            if ($post->post_type === 'product') {
                $original_type = 'products';
            } elseif ($post->post_type === 'post') {
                $original_type = 'posts';
            } elseif ($post->post_type === 'page') {
                $original_type = 'pages';
            }
        }

        // Just update the record - DON'T DELETE IT!
        $result = $wpdb->update(
            $table_pages,
            array(
                'custom_tab' => null,        // Remove from custom tab
                'page_type' => $original_type // Restore original type
            ),
            array('page_id' => $page_id),
            array('%s', '%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Item removed from tab and returned to ' . $original_type,
                'original_type' => $original_type
            ));
        } else {
            wp_send_json_error('Failed to remove item from tab');
        }
    }

    /**
     * Generate PDF report
     */
    public function generate_pdf_report()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_die('Unauthorized');
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'convly_ajax_nonce')) {
            wp_die('Invalid nonce');
        }

        // Get parameters
        $date_filter = isset($_POST['date_filter']) ? sanitize_text_field($_POST['date_filter']) : 'all';
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'pages';

        // Get date range
        $date_range = $this->get_date_range_for_filter($date_filter, $_POST);

        // Get report data
        $report_data = $this->get_report_data($tab, $date_range);

        // Generate PDF
        require_once CONVLY_PLUGIN_DIR . 'includes/class-convly-pdf-generator.php';
        $pdf_generator = new Convly_PDF_Generator();
        $pdf_generator->generate_report($report_data, $date_range);
    }

    /**
     * Generate PDF report for single page
     */
    public function generate_page_pdf()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_die('Unauthorized');
        }

        // Verify nonce
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_GET['nonce']) ? $_GET['nonce'] : '');
        if (!wp_verify_nonce($nonce, 'convly_ajax_nonce')) {
            wp_die('Invalid nonce');
        }

        // Get parameters
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : (isset($_GET['page_id']) ? intval($_GET['page_id']) : 0);
        $date_filter = isset($_POST['date_filter']) ? sanitize_text_field($_POST['date_filter']) : (isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : 'all');

        if (!$page_id) {
            wp_die('Invalid page ID');
        }

        // Get date range
        $request_data = !empty($_POST) ? $_POST : $_GET;
        $date_range = $this->get_date_range_for_filter($date_filter, $request_data);

        // Get page data
        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        // Get page info
        $page_info = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_pages WHERE page_id = %d",
            $page_id
        ));

        if (!$page_info) {
            wp_die('Page not found');
        }

        // Get page stats
        $where_clause = "v.page_id = %d";
        $where_values = array($page_id);

        if ($date_range) {
            $where_clause .= " AND v.view_date BETWEEN %s AND %s";
            $where_values[] = $date_range['start'];
            $where_values[] = $date_range['end'];
        }

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
        COUNT(DISTINCT v.visitor_id) as unique_visitors,
        COUNT(v.id) as total_views,
        COUNT(DISTINCT c.id) as total_clicks
     FROM $table_views v
     LEFT JOIN $table_clicks c ON v.page_id = c.page_id AND v.visitor_id = c.visitor_id
     WHERE $where_clause",
            $where_values
        ));

        $stats->conversion_rate = $stats->unique_visitors > 0 ?
            round(($stats->total_clicks / $stats->unique_visitors) * 100, 2) : 0;

        // Prepare report data
        $report_data = array(
            'page_info' => $page_info,
            'stats' => $stats
        );

        // Generate PDF
        require_once CONVLY_PLUGIN_DIR . 'includes/class-convly-pdf-generator.php';
        $pdf_generator = new Convly_PDF_Generator();
        $pdf_generator->generate_single_page_report($report_data, $date_range);
    }

    /**
     * Get report data
     */
    private function get_report_data($tab, $date_range)
    {
        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        $where = array("p.page_type = %s");
        $where_values = array($tab);

        if ($date_range) {
            $where[] = "v.view_date BETWEEN %s AND %s";
            $where_values[] = $date_range['start'];
            $where_values[] = $date_range['end'];
        }

        $where_clause = implode(' AND ', $where);

        $query = $wpdb->prepare(
            "SELECT 
        p.*,
        COUNT(DISTINCT v.visitor_id) as unique_visitors,
        COUNT(v.id) as total_views,
        COUNT(DISTINCT c.id) as total_clicks,
        (COUNT(DISTINCT c.id) / NULLIF(COUNT(DISTINCT v.visitor_id), 0) * 100) as conversion_rate
     FROM $table_pages p
     LEFT JOIN $table_views v ON p.page_id = v.page_id
     LEFT JOIN $table_clicks c ON p.page_id = c.page_id
     WHERE $where_clause AND p.is_active = 1
     GROUP BY p.page_id
     ORDER BY conversion_rate DESC",
            $where_values
        );

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Get page statistics
     */
    public function get_page_stats()
    {
        $this->set_no_cache_headers();
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $metric = isset($_POST['metric']) ? sanitize_text_field($_POST['metric']) : '';
        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '7_days';

        if (!$page_id) {
            wp_send_json_error('Invalid page ID');
        }

        global $wpdb;
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        $date_range = $this->get_date_range($period);
        $current_start = $date_range['start'];
        $current_end = $date_range['end'];

        switch ($metric) {
            case 'page_views':
                $current_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_views WHERE page_id = %d AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $current_start,
                    $current_end
                ));

                // Get device breakdown
                $mobile_views = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_views 
                     WHERE page_id = %d AND device_type = 'mobile' 
                     AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $current_start,
                    $current_end
                ));

                $desktop_views = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_views 
                     WHERE page_id = %d AND device_type = 'desktop' 
                     AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $current_start,
                    $current_end
                ));

                $total_views = $mobile_views + $desktop_views;
                $mobile_percentage = $total_views > 0 ? round(($mobile_views / $total_views) * 100, 1) : 0;
                $desktop_percentage = $total_views > 0 ? round(($desktop_views / $total_views) * 100, 1) : 0;

                $data = array(
                    'value' => number_format($current_value),
                    'device_breakdown' => array(
                        'mobile' => $mobile_percentage,
                        'desktop' => $desktop_percentage
                    )
                );
                break;

            case 'unique_visitors':
                $current_value = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM $table_views 
                     WHERE page_id = %d AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $current_start,
                    $current_end
                ));

                $data = array('value' => number_format($current_value));
                break;

            case 'conversion_rate':
                $visitors = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM $table_views 
         WHERE page_id = %d AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $current_start,
                    $current_end
                ));

                $clicks = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_clicks 
         WHERE page_id = %d AND click_date BETWEEN %s AND %s",
                    $page_id,
                    $current_start,
                    $current_end
                ));

                $rate = $visitors > 0 ? round(($clicks / $visitors) * 100, 1) : 0;
                $data = array('value' => $rate . '%');
                break;

            case 'scroll_depth':
                $avg_scroll = $wpdb->get_var($wpdb->prepare(
                    "SELECT AVG(max_scroll_depth) FROM {$wpdb->prefix}convly_scroll_depth 
         WHERE page_id = %d AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $current_start,
                    $current_end
                ));

                $data = array('value' => round($avg_scroll ?: 0) . '%');

                // Get scroll breakdown
                $scroll_25 = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}convly_scroll_depth 
         WHERE page_id = %d AND max_scroll_depth >= 25 
         AND view_date BETWEEN %s AND %s",
                    $page_id, $current_start, $current_end
                ));

                $scroll_50 = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}convly_scroll_depth 
         WHERE page_id = %d AND max_scroll_depth >= 50 
         AND view_date BETWEEN %s AND %s",
                    $page_id, $current_start, $current_end
                ));

                $scroll_75 = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}convly_scroll_depth 
         WHERE page_id = %d AND max_scroll_depth >= 75 
         AND view_date BETWEEN %s AND %s",
                    $page_id, $current_start, $current_end
                ));

                $scroll_100 = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}convly_scroll_depth 
         WHERE page_id = %d AND max_scroll_depth >= 100 
         AND view_date BETWEEN %s AND %s",
                    $page_id, $current_start, $current_end
                ));

                $total_scrolls = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}convly_scroll_depth 
         WHERE page_id = %d AND view_date BETWEEN %s AND %s",
                    $page_id, $current_start, $current_end
                ));

                if ($total_scrolls > 0) {
                    $data['breakdown'] = array(
                        '25' => round(($scroll_25 / $total_scrolls) * 100),
                        '50' => round(($scroll_50 / $total_scrolls) * 100),
                        '75' => round(($scroll_75 / $total_scrolls) * 100),
                        '100' => round(($scroll_100 / $total_scrolls) * 100)
                    );
                }
                break;

            default:
                $data = array('value' => 0);
        }

        // Calculate change from previous period
        $period_diff = strtotime($current_end) - strtotime($current_start);
        $previous_start = date('Y-m-d H:i:s', strtotime($current_start) - $period_diff);
        $previous_end = $current_start;

        // Get previous value for comparison
        $previous_value = $this->get_previous_value($metric, $page_id, $previous_start, $previous_end);
        $current_numeric = floatval(str_replace(',', '', $data['value']));

        if ($previous_value > 0) {
            $change = round((($current_numeric - $previous_value) / $previous_value) * 100, 1);
            $data['change'] = $change;
        }

        wp_send_json_success($data);
    }

    /**
     * Get page chart data
     */
    public function get_page_chart_data()
    {
        $this->set_no_cache_headers();
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $chart_type = isset($_POST['chart_type']) ? sanitize_text_field($_POST['chart_type']) : 'views';
        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '7_days';

        if (!$page_id) {
            wp_send_json_error('Invalid page ID');
        }

        global $wpdb;
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        $labels = array();
        $views_data = array();
        $visitors_data = array();
        $clicks_data = array();
        $conversion_data = array();

        switch ($period) {
            case '24_hours':
                // Hourly data
                for ($i = 23; $i >= 0; $i--) {
                    $hour = date('Y-m-d H:00:00', strtotime("-$i hours"));
                    $labels[] = date('H:00', strtotime($hour));

                    $views = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_views 
                     WHERE page_id = %d AND view_date >= %s 
                     AND view_date < DATE_ADD(%s, INTERVAL 1 HOUR)",
                        $page_id, $hour, $hour
                    ));

                    $visitors = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(DISTINCT visitor_id) FROM $table_views 
                     WHERE page_id = %d AND view_date >= %s 
                     AND view_date < DATE_ADD(%s, INTERVAL 1 HOUR)",
                        $page_id, $hour, $hour
                    ));

                    $clicks = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks 
                     WHERE page_id = %d AND click_date >= %s 
                     AND click_date < DATE_ADD(%s, INTERVAL 1 HOUR)",
                        $page_id, $hour, $hour
                    ));

                    $views_data[] = intval($views);
                    $visitors_data[] = intval($visitors);
                    $clicks_data[] = intval($clicks);
                    $conversion_data[] = $visitors > 0 ? round(($clicks / $visitors) * 100, 1) : 0;
                }
                break;

            case '7_days':
            case '30_days':
                // Daily data
                $days = $period === '7_days' ? 7 : 30;
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $labels[] = date('M d', strtotime($date));

                    $views = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_views 
                     WHERE page_id = %d AND DATE(view_date) = %s",
                        $page_id, $date
                    ));

                    $visitors = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(DISTINCT visitor_id) FROM $table_views 
                     WHERE page_id = %d AND DATE(view_date) = %s",
                        $page_id, $date
                    ));

                    $clicks = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks 
                     WHERE page_id = %d AND DATE(click_date) = %s",
                        $page_id, $date
                    ));

                    $views_data[] = intval($views);
                    $visitors_data[] = intval($visitors);
                    $clicks_data[] = intval($clicks);
                    $conversion_data[] = $visitors > 0 ? round(($clicks / $visitors) * 100, 1) : 0;
                }
                break;

            case '3_months':
                // Weekly data
                for ($i = 11; $i >= 0; $i--) {
                    $week_start = date('Y-m-d', strtotime("-$i weeks"));
                    $week_end = date('Y-m-d', strtotime("-$i weeks +6 days"));
                    $labels[] = date('M d', strtotime($week_start));

                    $views = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_views 
                     WHERE page_id = %d AND DATE(view_date) BETWEEN %s AND %s",
                        $page_id, $week_start, $week_end
                    ));

                    $visitors = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(DISTINCT visitor_id) FROM $table_views 
                     WHERE page_id = %d AND DATE(view_date) BETWEEN %s AND %s",
                        $page_id, $week_start, $week_end
                    ));

                    $clicks = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks 
                     WHERE page_id = %d AND DATE(click_date) BETWEEN %s AND %s",
                        $page_id, $week_start, $week_end
                    ));

                    $views_data[] = intval($views);
                    $visitors_data[] = intval($visitors);
                    $clicks_data[] = intval($clicks);
                    $conversion_data[] = $visitors > 0 ? round(($clicks / $visitors) * 100, 1) : 0;
                }
                break;
        }

        wp_send_json_success(array(
            'labels' => $labels,
            'views' => $views_data,
            'visitors' => $visitors_data,
            'clicks' => $clicks_data,
            'conversion_rates' => $conversion_data
        ));
    }

    /**
     * Get page buttons
     */
    public function get_page_buttons()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;

        if (!$page_id) {
            wp_send_json_error('Invalid page ID');
        }

        global $wpdb;
        $table_buttons = $wpdb->prefix . 'convly_buttons';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        $buttons = $wpdb->get_results($wpdb->prepare(
            "SELECT b.*, COUNT(c.id) as total_clicks
             FROM $table_buttons b
             LEFT JOIN $table_clicks c ON b.page_id = c.page_id AND b.button_css_id = c.button_id
             WHERE b.page_id = %d AND b.is_active = 1
             GROUP BY b.id",
            $page_id
        ), ARRAY_A);

        wp_send_json_success($buttons);
    }

    /**
     * Get button chart data
     */
    public function get_button_chart_data()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        $button_id = isset($_POST['button_id']) ? intval($_POST['button_id']) : 0;
        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '7_days';

        if (!$button_id) {
            wp_send_json_error('Invalid button ID');
        }

        global $wpdb;
        $table_buttons = $wpdb->prefix . 'convly_buttons';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        // Get button info
        $button = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_buttons WHERE id = %d",
            $button_id
        ));

        if (!$button) {
            wp_send_json_error('Button not found');
        }

        $labels = array();
        $values = array();

        switch ($period) {
            case '7_days':
            case '30_days':
                // Daily data
                $days = $period === '7_days' ? 7 : 30;
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $labels[] = date('M d', strtotime($date));

                    $count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks 
                         WHERE page_id = %d AND button_id = %s AND DATE(click_date) = %s",
                        $button->page_id,
                        $button->button_css_id,
                        $date
                    ));

                    $values[] = intval($count);
                }
                break;

            case '3_months':
                // Weekly data
                for ($i = 11; $i >= 0; $i--) {
                    $week_start = date('Y-m-d', strtotime("-$i weeks"));
                    $week_end = date('Y-m-d', strtotime("-$i weeks +6 days"));
                    $labels[] = date('M d', strtotime($week_start));

                    $count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_clicks 
                         WHERE page_id = %d AND button_id = %s 
                         AND DATE(click_date) BETWEEN %s AND %s",
                        $button->page_id,
                        $button->button_css_id,
                        $week_start,
                        $week_end
                    ));

                    $values[] = intval($count);
                }
                break;
        }

        wp_send_json_success(array(
            'labels' => $labels,
            'values' => $values
        ));
    }

    /**
     * Helper function to get previous value for comparison
     */
    private function get_previous_value($metric, $page_id, $start, $end)
    {
        global $wpdb;
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        switch ($metric) {
            case 'page_views':
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_views 
                     WHERE page_id = %d AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $start,
                    $end
                ));

            case 'unique_visitors':
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM $table_views 
                     WHERE page_id = %d AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $start,
                    $end
                ));

            case 'conversion_rate':
                $visitors = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT visitor_id) FROM $table_views 
         WHERE page_id = %d AND view_date BETWEEN %s AND %s",
                    $page_id,
                    $start,
                    $end
                ));

                $clicks = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_clicks 
         WHERE page_id = %d AND click_date BETWEEN %s AND %s",
                    $page_id,
                    $start,
                    $end
                ));

                return $visitors > 0 ? round(($clicks / $visitors) * 100, 1) : 0;

                return $views > 0 ? round(($clicks / $views) * 100, 1) : 0;
        }

        return 0;
    }

    /**
     * Export all data as CSV
     */
    public function export_all_data()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_die('Unauthorized');
        }

        // Verify nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'convly_export_nonce')) {
            wp_die('Invalid nonce');
        }

        global $wpdb;
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="convly-data-' . date('Y-m-d') . '.csv"');

        // Create file pointer
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Views data
        fputcsv($output, array('=== PAGE VIEWS ==='));
        fputcsv($output, array('Page ID', 'Page URL', 'Page Title', 'Visitor ID', 'Device Type', 'View Date'));

        $views = $wpdb->get_results("SELECT * FROM $table_views ORDER BY view_date DESC", ARRAY_A);
        foreach ($views as $view) {
            fputcsv($output, $view);
        }

        // Empty line
        fputcsv($output, array());

        // Clicks data
        fputcsv($output, array('=== BUTTON CLICKS ==='));
        fputcsv($output, array('Page ID', 'Button ID', 'Button Name', 'Visitor ID', 'Device Type', 'Click Date'));

        $clicks = $wpdb->get_results("SELECT * FROM $table_clicks ORDER BY click_date DESC", ARRAY_A);
        foreach ($clicks as $click) {
            fputcsv($output, $click);
        }

        fclose($output);
        exit;
    }

    /**
     * Clear old data
     */
    public function clear_old_data()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        global $wpdb;
        $table_views = $wpdb->prefix . 'convly_views';
        $table_clicks = $wpdb->prefix . 'convly_clicks';

        $date_threshold = date('Y-m-d H:i:s', strtotime('-12 months'));

        // Delete old views
        $views_deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_views WHERE view_date < %s",
            $date_threshold
        ));

        // Delete old clicks
        $clicks_deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_clicks WHERE click_date < %s",
            $date_threshold
        ));

        $message = sprintf(
            __('Deleted %d old views and %d old clicks', 'convly'),
            $views_deleted,
            $clicks_deleted
        );

        wp_send_json_success($message);
    }

    /**
     * Reset all data
     */
    public function reset_all_data()
    {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        global $wpdb;
        $tables = array(
            'convly_views',
            'convly_clicks',
            'convly_buttons',
            'convly_pages',
            'convly_tabs'
        );

        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $wpdb->query("TRUNCATE TABLE $table_name");
        }

        wp_send_json_success(__('All tracking data has been reset', 'convly'));
    }

    /**
     * Sync pages with WordPress
     */
    public function sync_pages() {
        // Check permissions
        if (!current_user_can('manage_convly')) {
            wp_send_json_error('Unauthorized');
        }

        // Verify nonce
        if (!check_ajax_referer('convly_ajax_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        global $wpdb;
        $table_pages = $wpdb->prefix . 'convly_pages';

        $removed_count = 0;
        $added_count = 0;

        // 1. Remove deleted pages
        $tracked_pages = $wpdb->get_results(
            "SELECT page_id, page_type FROM $table_pages WHERE custom_tab IS NULL",
            ARRAY_A
        );

        foreach ($tracked_pages as $tracked) {
            $post = get_post($tracked['page_id']);

            // If post doesn't exist or is in trash, remove it
            if (!$post || $post->post_status === 'trash') {
                $wpdb->delete(
                    $table_pages,
                    array('page_id' => $tracked['page_id']),
                    array('%d')
                );
                $removed_count++;
            }
        }

        // 2. Add new published pages
        $post_types = array('page', 'post', 'product');

        foreach ($post_types as $post_type) {
            $wp_posts = get_posts(array(
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));

            $type_name = $post_type === 'page' ? 'pages' :
                ($post_type === 'product' ? 'products' : 'posts');

            foreach ($wp_posts as $post) {
                // Check if already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_pages WHERE page_id = %d",
                    $post->ID
                ));

                if (!$exists) {
                    $wpdb->insert(
                        $table_pages,
                        array(
                            'page_id' => $post->ID,
                            'page_url' => get_permalink($post->ID),
                            'page_title' => $post->post_title,
                            'page_type' => $type_name,
                            'is_active' => 1
                        ),
                        array('%d', '%s', '%s', '%s', '%d')
                    );
                    $added_count++;
                }
            }
        }

        $message = sprintf(
            __('Sync completed! Added: %d pages, Removed: %d pages', 'convly'),
            $added_count,
            $removed_count
        );

        wp_send_json_success(array(
            'message' => $message,
            'added' => $added_count,
            'removed' => $removed_count
        ));
    }

}