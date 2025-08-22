<?php
/**
 * Admin Dashboard View
 *
 * @package    Convly
 * @subpackage Convly/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap convly-dashboard">
    <!-- Top Navigation -->
    <div class="convly-top-nav">
        <ul class="convly-nav-menu">
            <li><a href="<?php echo admin_url('admin.php?page=convly'); ?>" class="active">Dashboard</a></li>
            <li><a href="<?php echo admin_url('admin.php?page=convly-page-details'); ?>">Details</a></li>
            <li><a href="<?php echo admin_url('admin.php?page=convly-settings'); ?>">Settings</a></li>
        </ul>

        <!-- برای اضافه کردن لوگو -->
        <img src="<?php echo CONVLY_PLUGIN_URL; ?>admin/images/logo.png" alt="Convly" class="convly-nav-logo">
    </div>
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Summary Cards -->
    <div class="convly-summary-cards">
        <div >
            <h3><?php _e('Total Conversion Rate', 'convly'); ?></h3>
            <div class="convly-metric">
                <span class="convly-metric-value">-</span>
                <span class="convly-metric-change"></span>
            </div>
            <div class="convly-period-selector">
                <select class="convly-period-select" data-metric="conversion_rate">
                    <option value="24_hours"><?php _e('24 Hours', 'convly'); ?></option>
                    <option value="7_days" selected><?php _e('7 Days', 'convly'); ?></option>
                    <option value="30_days"><?php _e('30 Days', 'convly'); ?></option>
                    <option value="3_months"><?php _e('3 Months', 'convly'); ?></option>
                    <option value="12_months"><?php _e('12 Months', 'convly'); ?></option>
                </select>
            </div>
        </div>

        <div class="convly-card" data-metric="total_clicks">
            <h3><?php _e('Total Clicks', 'convly'); ?></h3>
            <div class="convly-metric">
                <span class="convly-metric-value">-</span>
                <span class="convly-metric-change"></span>
            </div>
            <div class="convly-period-selector">
                <select class="convly-period-select" data-metric="total_clicks">
                    <option value="24_hours"><?php _e('24 Hours', 'convly'); ?></option>
                    <option value="7_days" selected><?php _e('7 Days', 'convly'); ?></option>
                    <option value="30_days"><?php _e('30 Days', 'convly'); ?></option>
                    <option value="3_months"><?php _e('3 Months', 'convly'); ?></option>
                    <option value="12_months"><?php _e('12 Months', 'convly'); ?></option>
                </select>
            </div>
        </div>

        <div class="convly-card" data-metric="total_views">
            <h3><?php _e('Total Views', 'convly'); ?></h3>
            <div class="convly-metric">
                <span class="convly-metric-value">-</span>
                <span class="convly-metric-change"></span>
            </div>
            <div class="convly-period-selector">
                <select class="convly-period-select" data-metric="total_views">
                    <option value="24_hours"><?php _e('24 Hours', 'convly'); ?></option>
                    <option value="7_days" selected><?php _e('7 Days', 'convly'); ?></option>
                    <option value="30_days"><?php _e('30 Days', 'convly'); ?></option>
                    <option value="3_months"><?php _e('3 Months', 'convly'); ?></option>
                    <option value="12_months"><?php _e('12 Months', 'convly'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Chart -->
    <div class="convly-chart-container">
        <div class="convly-chart-header">
            <h2><?php _e('Views & Clicks Overview', 'convly'); ?></h2>
            <div class="convly-chart-period-selector">
                <button class="convly-period-btn" data-period="24_hours"><?php _e('24 hours', 'convly'); ?></button>
                <button class="convly-period-btn" data-period="7_days"><?php _e('7 days', 'convly'); ?></button>
                <button class="convly-period-btn active"
                        data-period="30_days"><?php _e('30 days', 'convly'); ?></button>
                <button class="convly-period-btn" data-period="3_months"><?php _e('3 months', 'convly'); ?></button>
                <button class="convly-period-btn" data-period="6_months"><?php _e('6 months', 'convly'); ?></button>
                <button class="convly-period-btn" data-period="12_months"><?php _e('12 months', 'convly'); ?></button>
            </div>
        </div>
        <div class="convly-chart-wrapper">
            <canvas id="convly-main-chart"></canvas>
        </div>
    </div>

    <!-- Pages List -->
    <div class="convly-pages-section">
        <div class="convly-pages-header">
            <h2><?php _e('All Pages', 'convly'); ?></h2>
            <div class="convly-pages-actions">
                <button id="convly-export-pdf" class="button button-secondary">
                    <?php _e('Export PDF Report', 'convly'); ?>
                </button>
                <button id="convly-sync-pages" class="button button-secondary"
                        title="<?php _e('Sync with WordPress pages', 'convly'); ?>">
                    <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                    <?php _e('Sync Pages', 'convly'); ?>
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="convly-tabs">
            <ul class="convly-tab-list">
                <li><a href="#" class="convly-tab active" data-tab="pages"><?php _e('Pages', 'convly'); ?></a></li>
                <li><a href="#" class="convly-tab" data-tab="products"><?php _e('Products', 'convly'); ?></a></li>
                <li><a href="#" class="convly-tab" data-tab="posts"><?php _e('Posts', 'convly'); ?></a></li>
                <li class="convly-custom-tabs"></li>
                <li><a href="#" class="convly-add-tab" title="<?php _e('Add Custom Tab', 'convly'); ?>">+</a></li>
            </ul>
        </div>

        <!-- Filters and Sorting -->
        <div class="convly-filters">
            <div class="convly-filter-group">
                <label><?php _e('Date Range:', 'convly'); ?></label>
                <select id="convly-date-filter">
                    <option value="all"><?php _e('All Time', 'convly'); ?></option>
                    <option value="today"><?php _e('Today', 'convly'); ?></option>
                    <option value="yesterday"><?php _e('Yesterday', 'convly'); ?></option>
                    <option value="7_days"><?php _e('Last 7 Days', 'convly'); ?></option>
                    <option value="30_days"><?php _e('Last 30 Days', 'convly'); ?></option>
                    <option value="custom"><?php _e('Custom Range', 'convly'); ?></option>
                </select>
                <div class="convly-custom-date-range" style="display:none;">
                    <input type="date" id="convly-date-from"/>
                    <span><?php _e('to', 'convly'); ?></span>
                    <input type="date" id="convly-date-to"/>
                </div>
            </div>

            <div class="convly-filter-group">
                <label><?php _e('Sort By:', 'convly'); ?></label>
                <select id="convly-sort-by">
                    <option value="views_desc" selected><?php _e('Views (High to Low)', 'convly'); ?></option>
                    <option value="conversion_rate_desc"><?php _e('Conversion Rate (High to Low)', 'convly'); ?></option>
                    <option value="clicks_desc"><?php _e('Clicks (High to Low)', 'convly'); ?></option>
                    <option value="name_asc"><?php _e('Name (A-Z)', 'convly'); ?></option>
                    <option value="name_desc"><?php _e('Name (Z-A)', 'convly'); ?></option>
                </select>
            </div>
        </div>

        <!-- Pages Table -->
        <div class="convly-table-wrapper">
            <table class="convly-pages-table widefat fixed striped">
                <thead>
                <tr>
                    <th class="column-status"><?php _e('Status', 'convly'); ?></th>
                    <th class="column-name"><?php _e('Page Name', 'convly'); ?></th>
                    <th class="column-visitors"><?php _e('Unique Visitors', 'convly'); ?></th>
                    <th class="column-views"><?php _e('Total Views', 'convly'); ?></th>
                    <th class="column-clicks"><?php _e('Button Clicks', 'convly'); ?></th>
                    <th class="column-rate"><?php _e('Conversion Rate', 'convly'); ?></th>
                    <th class="column-actions"><?php _e('Actions', 'convly'); ?></th>
                </tr>
                </thead>
                <tbody id="convly-pages-list">
                <tr>
                    <td colspan="7" class="convly-loading"><?php _e('Loading...', 'convly'); ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="convly-pagination">
            <div class="tablenav-pages">
                <span class="displaying-num"></span>
                <span class="pagination-links"></span>
            </div>
        </div>
    </div>
</div>

<!-- Add Custom Tab Modal -->
<div id="convly-custom-tab-modal" class="convly-modal" style="display:none;">
    <div class="convly-modal-content">
        <span class="convly-modal-close">&times;</span>
        <h2><?php _e('Add Custom Tab', 'convly'); ?></h2>
        <form id="convly-custom-tab-form">
            <p>
                <label for="convly-tab-name"><?php _e('Tab Name:', 'convly'); ?></label>
                <input type="text" id="convly-tab-name" name="tab_name" required/>
            </p>
            <p>
                <button type="submit" class="button button-primary"><?php _e('Add Tab', 'convly'); ?></button>
                <button type="button" class="button convly-modal-cancel"><?php _e('Cancel', 'convly'); ?></button>
            </p>
        </form>
    </div>
</div>

<!-- Add/Edit Button Modal -->
<div id="convly-button-modal" class="convly-modal" style="display:none;">
    <div class="convly-modal-content">
        <span class="convly-modal-close">&times;</span>
        <h2 id="convly-button-modal-title"><?php _e('Add Button', 'convly'); ?></h2>
        <form id="convly-button-form">
            <input type="hidden" id="convly-button-id" name="button_id"/>
            <input type="hidden" id="convly-page-id" name="page_id"/>

            <p>
                <label for="convly-button-css-id"><?php _e('Button CSS ID:', 'convly'); ?></label>
                <input type="text" id="convly-button-css-id" name="button_css_id" required/>
                <span class="description"><?php _e('Enter the CSS ID of the button (without #)', 'convly'); ?></span>
            </p>

            <p>
                <label for="convly-button-name"><?php _e('Button Name:', 'convly'); ?></label>
                <input type="text" id="convly-button-name" name="button_name" required/>
                <span class="description"><?php _e('A friendly name for this button', 'convly'); ?></span>
            </p>

            <p>
                <label for="convly-button-type"><?php _e('Type:', 'convly'); ?></label>
                <select id="convly-button-type" name="button_type">
                    <option value="button"><?php _e('Button', 'convly'); ?></option>
                    <option value="link"><?php _e('Link', 'convly'); ?></option>
                </select>
            </p>

            <p>
                <button type="submit" class="button button-primary"><?php _e('Save Button', 'convly'); ?></button>
                <button type="button" class="button convly-modal-cancel"><?php _e('Cancel', 'convly'); ?></button>
            </p>
        </form>
    </div>
</div>

<!-- PDF Export Date Range Modal -->
<div id="convly-pdf-export-modal" class="convly-modal" style="display:none;">
    <div class="convly-modal-content">
        <span class="convly-modal-close">&times;</span>
        <h2><?php _e('Export PDF Report', 'convly'); ?></h2>
        <form id="convly-pdf-export-form">
            <p>
                <label for="convly-pdf-date-range"><?php _e('Select Date Range:', 'convly'); ?></label>
                <select id="convly-pdf-date-range" name="date_range">
                    <option value="all"><?php _e('All Time', 'convly'); ?></option>
                    <option value="today"><?php _e('Today', 'convly'); ?></option>
                    <option value="yesterday"><?php _e('Yesterday', 'convly'); ?></option>
                    <option value="7_days" selected><?php _e('Last 7 Days', 'convly'); ?></option>
                    <option value="30_days"><?php _e('Last 30 Days', 'convly'); ?></option>
                    <option value="3_months"><?php _e('Last 3 Months', 'convly'); ?></option>
                    <option value="6_months"><?php _e('Last 6 Months', 'convly'); ?></option>
                    <option value="12_months"><?php _e('Last 12 Months', 'convly'); ?></option>
                    <option value="custom"><?php _e('Custom Range', 'convly'); ?></option>
                </select>
            </p>

            <div id="convly-pdf-custom-dates" style="display:none;">
                <p>
                    <label for="convly-pdf-date-from"><?php _e('From:', 'convly'); ?></label>
                    <input type="date" id="convly-pdf-date-from" name="date_from"/>
                </p>
                <p>
                    <label for="convly-pdf-date-to"><?php _e('To:', 'convly'); ?></label>
                    <input type="date" id="convly-pdf-date-to" name="date_to"/>
                </p>
            </div>

            <p>
                <button type="submit" class="button button-primary"><?php _e('Export PDF', 'convly'); ?></button>
                <button type="button" class="button convly-modal-cancel"><?php _e('Cancel', 'convly'); ?></button>
            </p>
        </form>
    </div>
</div>

<!-- Manage Custom Tab Items Modal -->
<div id="convly-manage-tab-modal" class="convly-modal" style="display:none;">
    <div class="convly-modal-content" style="max-width: 700px;">
        <span class="convly-modal-close">&times;</span>
        <h2><?php _e('Manage Tab Items', 'convly'); ?> - <span id="convly-current-tab-name"></span></h2>

        <div class="convly-tab-items-section">
            <h3><?php _e('Add Items to Tab', 'convly'); ?></h3>
            <div class="convly-item-selector">
                <select id="convly-item-type">
                    <option value="page"><?php _e('Pages', 'convly'); ?></option>
                    <option value="post"><?php _e('Posts', 'convly'); ?></option>
                    <option value="product"><?php _e('Products', 'convly'); ?></option>
                </select>

                <select id="convly-available-items" multiple style="width: 100%; height: 200px; margin-top: 10px;">
                    <!-- Items will be loaded here -->
                </select>

                <button type="button" id="convly-add-to-tab" class="button button-primary" style="margin-top: 10px;">
                    <?php _e('Add Selected Items', 'convly'); ?>
                </button>
            </div>
        </div>

        <div class="convly-current-items-section" style="margin-top: 30px;">
            <h3><?php _e('Current Items in Tab', 'convly'); ?></h3>
            <table class="widefat" id="convly-tab-items-table">
                <thead>
                <tr>
                    <th><?php _e('Page Name', 'convly'); ?></th>
                    <th><?php _e('Type', 'convly'); ?></th>
                    <th><?php _e('Actions', 'convly'); ?></th>
                </tr>
                </thead>
                <tbody>
                <!-- Current items will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        console.log('Emergency loader started!');

        var mainChart = null;

        // Load summary cards
        function loadSummaryCards() {
            $('.convly-card').each(function () {
                var $card = $(this);
                var metric = $card.data('metric');
                var period = $card.find('.convly-period-select').val() || '7_days';

                $.ajax({
                    url: convly_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'convly_get_stats',
                        nonce: convly_ajax.nonce,
                        metric: metric,
                        period: period
                    },
                    success: function (response) {
                        if (response.success) {
                            $card.find('.convly-metric-value').text(response.data.value);
                            if (response.data.change) {
                                var changeText = response.data.change > 0 ? '+' + response.data.change + '%' : response.data.change + '%';
                                $card.find('.convly-metric-change').text(changeText);
                                $card.find('.convly-metric-change').addClass(response.data.change > 0 ? 'positive' : 'negative');
                            }
                        }
                    }
                });
            });
        }

        // Period change handler for cards
        $('.convly-period-select').on('change', function () {
            var $card = $(this).closest('.convly-card');
            var metric = $card.data('metric');
            var period = $(this).val();

            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_get_stats',
                    nonce: convly_ajax.nonce,
                    metric: metric,
                    period: period
                },
                success: function (response) {
                    if (response.success) {
                        $card.find('.convly-metric-value').text(response.data.value);
                        if (response.data.change) {
                            var changeText = response.data.change > 0 ? '+' + response.data.change + '%' : response.data.change + '%';
                            $card.find('.convly-metric-change').text(changeText);
                            $card.find('.convly-metric-change').removeClass('positive negative');
                            $card.find('.convly-metric-change').addClass(response.data.change > 0 ? 'positive' : 'negative');
                        }
                    }
                }
            });
        });

        // Load main chart
        function loadChart(period) {
            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_get_stats',
                    nonce: convly_ajax.nonce,
                    type: 'chart_data',
                    period: period || '30_days'
                },
                success: function (response) {
                    if (response.success) {
                        var ctx = document.getElementById('convly-main-chart').getContext('2d');

                        if (mainChart) {
                            mainChart.destroy();
                        }

                        mainChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: response.data.labels,
                                datasets: [{
                                    label: 'Views',
                                    data: response.data.views,
                                    borderColor: 'rgb(54, 162, 235)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                                    tension: 0.4
                                }, {
                                    label: 'Clicks',
                                    data: response.data.clicks,
                                    borderColor: 'rgb(75, 192, 192)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    }
                }
            });
        }

        // Chart period buttons
        $('.convly-period-btn').on('click', function () {
            $('.convly-period-btn').removeClass('active');
            $(this).addClass('active');
            loadChart($(this).data('period'));
        });

        // Load pages list
        function loadPages() {
            var currentTab = $('.convly-tab.active').data('tab') || 'pages';

            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_get_page_list',
                    nonce: convly_ajax.nonce,
                    tab: currentTab,
                    page: 1,
                    per_page: 20,
                    sort_by: $('#convly-sort-by').val() || 'views_desc',
                    date_filter: $('#convly-date-filter').val() || 'all'
                },
                success: function (response) {
                    console.log('Pages response:', response);
                    if (response.success && response.data.items) {
                        var html = '';
                        if (response.data.items.length === 0) {
                            html = '<tr><td colspan="7">No pages found</td></tr>';
                        } else {
                            response.data.items.forEach(function (page) {
                                var rate = page.total_views > 0 ? ((page.total_clicks / page.total_views) * 100).toFixed(1) : 0;
                                var rateClass = rate >= 10 ? 'high' : (rate >= 5 ? 'medium' : 'low');

                                html += '<tr data-page-id="' + page.page_id + '">';
                                html += '<td class="column-status">';
                                html += '<label class="convly-status-toggle">';
                                html += '<input type="checkbox" ' + (page.is_active == 1 ? 'checked' : '') + ' data-page-id="' + page.page_id + '">';
                                html += '<span class="convly-status-slider"></span>';
                                html += '</label>';
                                html += '</td>';
                                html += '<td class="column-name"><strong><a href="' + page.page_url + '" target="_blank">' + page.page_title + '</a></strong></td>';
                                html += '<td class="column-visitors">' + page.unique_visitors + '</td>';
                                html += '<td class="column-views">' + page.total_views + '</td>';
                                html += '<td class="column-clicks">';
                                if (page.has_buttons == 1) {
                                    html += page.total_clicks;
                                } else {
                                    html += '<button class="button button-small convly-add-button" data-page-id="' + page.page_id + '">Add Button</button>';
                                }
                                html += '</td>';
                                html += '<td class="column-rate"><span class="convly-conversion-rate ' + rateClass + '">' + rate + '%</span></td>';
                                html += '<td class="column-actions">';
                                html += '<a href="?page=convly-page-details&page_id=' + page.page_id + '" class="convly-action-btn primary">Details</a>';
                                html += '</td>';
                                html += '</tr>';
                            });
                        }
                        $('#convly-pages-list').html(html);

                        // Bind events for new elements
                        bindPageEvents();
                    }
                }
            });
        }

        // Bind page events
        function bindPageEvents() {
            // Status toggle
            $('.convly-status-toggle input').off('change').on('change', function () {
                var pageId = $(this).data('page-id');
                var isActive = $(this).is(':checked');

                $.ajax({
                    url: convly_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'convly_toggle_page_status',
                        nonce: convly_ajax.nonce,
                        page_id: pageId,
                        is_active: isActive ? 1 : 0
                    },
                    success: function (response) {
                        if (response.success) {
                            loadPages(); // Reload to reorder
                        }
                    }
                });
            });

            // Add button
            $('.convly-add-button').off('click').on('click', function () {
                var pageId = $(this).data('page-id');
                $('#convly-page-id').val(pageId);
                $('#convly-button-form')[0].reset();
                $('#convly-page-id').val(pageId);
                $('#convly-button-modal').show();
            });
        }

        // Add manage button for custom tabs
        function loadCustomTabs() {
            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_get_custom_tabs',
                    nonce: convly_ajax.nonce
                },
                success: function (response) {
                    if (response.success && response.data.length > 0) {
                        var tabsHtml = '';
                        response.data.forEach(function (tab) {
                            tabsHtml += '<li>';
                            tabsHtml += '<a href="#" class="convly-tab" data-tab="' + tab.tab_slug + '">' + tab.tab_name + '</a>';
                            tabsHtml += '<button class="convly-manage-tab" data-tab-slug="' + tab.tab_slug + '" data-tab-name="' + tab.tab_name + '" title="Manage">⚙</button>';
                            tabsHtml += '<button class="convly-delete-tab" data-tab-id="' + tab.id + '" title="Delete">×</button>';
                            tabsHtml += '</li>';
                        });
                        $('.convly-custom-tabs').html(tabsHtml);
                    }
                }
            });
        }

// Manage tab items
        $(document).on('click', '.convly-manage-tab', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var tabSlug = $(this).data('tab-slug');
            var tabName = $(this).data('tab-name');

            $('#convly-current-tab-name').text(tabName);
            $('#convly-manage-tab-modal').data('tab-slug', tabSlug);
            $('#convly-manage-tab-modal').show();

            // Load available items
            loadAvailableItems('page');
            // Load current items in tab
            loadTabItems(tabSlug);
        });

// Load available items
        function loadAvailableItems(type) {
            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_get_available_items',
                    nonce: convly_ajax.nonce,
                    item_type: type
                },
                success: function (response) {
                    if (response.success) {
                        var options = '';
                        response.data.forEach(function (item) {
                            options += '<option value="' + item.ID + '">' + item.post_title + '</option>';
                        });
                        $('#convly-available-items').html(options);
                    }
                }
            });
        }

// Load items in tab
        function loadTabItems(tabSlug) {
            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_get_tab_items',
                    nonce: convly_ajax.nonce,
                    tab_slug: tabSlug
                },
                success: function (response) {
                    if (response.success) {
                        var html = '';
                        response.data.forEach(function (item) {
                            html += '<tr>';
                            html += '<td>' + item.page_title + '</td>';
                            html += '<td>' + item.page_type + '</td>';
                            html += '<td><button class="button button-small convly-remove-from-tab" data-page-id="' + item.page_id + '">Remove</button></td>';
                            html += '</tr>';
                        });
                        $('#convly-tab-items-table tbody').html(html || '<tr><td colspan="3">No items in this tab</td></tr>');
                    }
                }
            });
        }

// Change item type
        $('#convly-item-type').on('change', function () {
            loadAvailableItems($(this).val());
        });

// Add items to tab
        $('#convly-add-to-tab').on('click', function () {
            var selectedItems = $('#convly-available-items').val();
            var tabSlug = $('#convly-manage-tab-modal').data('tab-slug');

            if (!selectedItems || selectedItems.length === 0) {
                alert('Please select items to add');
                return;
            }

            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_add_items_to_tab',
                    nonce: convly_ajax.nonce,
                    tab_slug: tabSlug,
                    item_ids: selectedItems
                },
                success: function (response) {
                    if (response.success) {
                        loadTabItems(tabSlug);
                        loadPages(); // Reload main list
                    }
                }
            });
        });

// Remove item from tab
        $(document).on('click', '.convly-remove-from-tab', function () {
            if (!confirm('Remove this item from the custom tab? It will return to its original tab.')) {
                return;
            }

            var pageId = $(this).data('page-id');
            var tabSlug = $('#convly-manage-tab-modal').data('tab-slug');

            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_remove_item_from_tab',
                    nonce: convly_ajax.nonce,
                    page_id: pageId
                },
                success: function (response) {
                    if (response.success) {
                        // Show success message
                        if (response.data.message) {
                            alert(response.data.message);
                        }

                        // Reload current tab items
                        loadTabItems(tabSlug);

                        // Reload main pages list
                        loadPages();
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        });

// Sync pages with WordPress
        $('#convly-sync-pages').on('click', function () {
            var $button = $(this);
            var originalText = $button.html();

            // Show loading state
            $button.prop('disabled', true);
            $button.html('<span class="dashicons dashicons-update convly-spin" style="vertical-align: middle;"></span> Syncing...');

            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_sync_pages',
                    nonce: convly_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // Show success message
                        alert(response.data.message);
                        // Reload pages list
                        loadPages();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function () {
                    alert('Failed to sync pages');
                },
                complete: function () {
                    // Restore button
                    $button.prop('disabled', false);
                    $button.html(originalText);
                }
            });
        });

        // Tab switching - Use event delegation for dynamic tabs
        $(document).on('click', '.convly-tab', function (e) {
            e.preventDefault();
            $('.convly-tab').removeClass('active');
            $(this).addClass('active');
            loadPages();
        });

        // Filters
        $('#convly-date-filter, #convly-sort-by').on('change', function () {
            loadPages();
        });

        // Date filter custom range
        $('#convly-date-filter').on('change', function () {
            if ($(this).val() === 'custom') {
                $('.convly-custom-date-range').show();
            } else {
                $('.convly-custom-date-range').hide();
            }
        });

        // Load custom tabs
        function loadCustomTabs() {
            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_get_custom_tabs',
                    nonce: convly_ajax.nonce
                },
                success: function (response) {
                    if (response.success && response.data.length > 0) {
                        var tabsHtml = '';
                        response.data.forEach(function (tab) {
                            tabsHtml += '<li class="convly-custom-tab-wrapper">';
                            tabsHtml += '<a href="#" class="convly-tab custom-tab" data-tab="' + tab.tab_slug + '">' + tab.tab_name + '</a>';
                            tabsHtml += '<div class="convly-tab-buttons">';
                            tabsHtml += '<button class="convly-manage-tab" data-tab-slug="' + tab.tab_slug + '" data-tab-name="' + tab.tab_name + '" title="Manage">⚙</button>';
                            tabsHtml += '<button class="convly-delete-tab" data-tab-id="' + tab.id + '" title="Delete">×</button>';
                            tabsHtml += '</div>';
                            tabsHtml += '</li>';
                        });
                        $('.convly-custom-tabs').html(tabsHtml);
                    }
                }
            });
        }

        // Add custom tab
        $('.convly-add-tab').on('click', function (e) {
            e.preventDefault();
            $('#convly-custom-tab-modal').show();
        });

        $('#convly-custom-tab-form').on('submit', function (e) {
            e.preventDefault();
            var tabName = $('#convly-tab-name').val();

            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convly_add_custom_tab',
                    nonce: convly_ajax.nonce,
                    tab_name: tabName
                },
                success: function (response) {
                    if (response.success) {
                        $('#convly-custom-tab-modal').hide();
                        $('#convly-tab-name').val('');
                        loadCustomTabs();
                    } else {
                        alert(response.data);
                    }
                }
            });
        });

        // Delete tab
        $(document).on('click', '.convly-delete-tab', function (e) {
            e.stopPropagation();
            if (confirm('Are you sure you want to delete this tab?')) {
                var tabId = $(this).data('tab-id');
                $.ajax({
                    url: convly_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'convly_delete_custom_tab',
                        nonce: convly_ajax.nonce,
                        tab_id: tabId
                    },
                    success: function (response) {
                        if (response.success) {
                            loadCustomTabs();
                            $('.convly-tab.active[data-tab]').first().trigger('click');
                        }
                    }
                });
            }
        });

        // Export PDF
        // Export PDF - نمایش modal به جای export مستقیم
        $('#convly-export-pdf').on('click', function () {
            $('#convly-pdf-export-modal').show();
        });

// Handle PDF date range selection
        $('#convly-pdf-date-range').on('change', function () {
            if ($(this).val() === 'custom') {
                $('#convly-pdf-custom-dates').show();
            } else {
                $('#convly-pdf-custom-dates').hide();
            }
        });

// Handle PDF export form submission
        $('#convly-pdf-export-form').on('submit', function (e) {
            e.preventDefault();

            var dateRange = $('#convly-pdf-date-range').val();
            var tab = $('.convly-tab.active').data('tab') || 'pages';

            var form = $('<form>', {
                method: 'POST',
                action: convly_ajax.ajax_url
            });

            var params = {
                action: 'convly_generate_pdf_report',
                nonce: convly_ajax.nonce,
                date_filter: dateRange,
                tab: tab
            };

            if (dateRange === 'custom') {
                params.date_from = $('#convly-pdf-date-from').val();
                params.date_to = $('#convly-pdf-date-to').val();
            }

            $.each(params, function (key, value) {
                $('<input>').attr({
                    type: 'hidden',
                    name: key,
                    value: value
                }).appendTo(form);
            });

            $('#convly-pdf-export-modal').hide();
            form.appendTo('body').submit().remove();
        });

        // Button form submission
        $('#convly-button-form').on('submit', function (e) {
            e.preventDefault();

            var formData = {
                action: $('#convly-button-id').val() ? 'convly_update_button' : 'convly_add_button',
                nonce: convly_ajax.nonce,
                page_id: $('#convly-page-id').val(),
                button_id: $('#convly-button-id').val(),
                button_css_id: $('#convly-button-css-id').val(),
                button_name: $('#convly-button-name').val(),
                button_type: $('#convly-button-type').val()
            };

            $.ajax({
                url: convly_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        $('#convly-button-modal').hide();
                        loadPages();
                    } else {
                        alert(response.data);
                    }
                }
            });
        });

        // Modal handlers
        $('.convly-modal-close, .convly-modal-cancel').on('click', function () {
            $('.convly-modal').hide();
        });

        $('.convly-modal').on('click', function (e) {
            if (e.target === this) {
                $(this).hide();
            }
        });

        // Initial load
        loadSummaryCards();
        loadChart('30_days');
        loadPages();
        loadCustomTabs();
    });
</script>


<div class="container text-xl convly_container">
    <!-- header -->
    <header>
        <div class="h-22.5 bg-black1 rounded-xl text-white/80 flex items-center justify-between px-6">
            <ul id="navbar" class="convly_navbar">
                <li><a href="<?php echo admin_url('admin.php?page=convly'); ?>" class="nav-link active_nav">Dashboard</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=convly-page-details'); ?>" class="nav-link">Details</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=convly-settings'); ?>" class="nav-link">Settings</a></li>
            </ul>
            <img src="<?php echo CONVLY_PLUGIN_URL; ?>admin/images/image7.png" alt="convly">
        </div>

    </header>

    <!-- section 1 -->
    <div class="flex mt-7.5 gap-x-7.5 items-stretch">

        <div class="w-70/100">
            <!-- numbers -->
            <div class="grid grid-cols-3 gap-x-8">
                <div id="card1" class="convly_card convly-card bg-white rounded-xl p-7.5" data-metric="conversion_rate" >
                    <div class="convly-metric">
                        <span class="convly-metric-value">-</span>
                        <span class="convly-metric-change"></span>
                    </div><div class="skeleton h-7 w-44 mb-4 "></div>
                    <div class="flex justify-between items-center mt-2.5">
                        <span class="skeleton w-22 h-10"></span>
                        <span class="skeleton h-10 w-20"></span>
                    </div>
                </div>
                <div id="card2" class="convly_card bg-white rounded-xl p-7.5">
                    <div class="skeleton h-7 w-44 mb-4 "></div>
                    <div class="flex justify-between items-center mt-2.5">
                        <span class="skeleton w-22 h-10"></span>
                        <span class="skeleton h-10 w-20"></span>
                    </div>
                </div>
                <div id="card3" class="convly_card bg-white rounded-xl p-7.5">
                    <div class="skeleton h-7 w-44 mb-4 "></div>
                    <div class="flex justify-between items-center mt-2.5">
                        <span class="skeleton w-22 h-10"></span>
                        <span class="skeleton h-10 w-20"></span>
                    </div>
                </div>

            </div>

            <!-- chart -->
            <div id="chart-section" class="rounded-xl bg-white mt-7.5 p-6 relative z-0">
                <div class="skeleton h-10 w-44 mb-6"></div>

                <div class="flex items-center gap-x-7 mt-8 mb-9">
                    <div class="skeleton w-25 h-7"></div>
                    <div class="skeleton w-25 h-7"></div>
                    <div class="skeleton w-25 h-7"></div>
                    <div class="skeleton w-25 h-7"></div>
                    <div class="skeleton w-25 h-7"></div>
                </div>

                <div class="skeleton h-87.5 w-full"></div>
            </div>
        </div>


        <div class="w-30/100 flex flex-col">
            <section id="top5" class="rounded-xl bg-white p-6 relative z-0">
                <div class="skeleton h-10 w-44 mb-6"></div>

                <div class="flex items-center gap-x-7 mt-8 mb-9">
                    <div class="skeleton w-25 h-7"></div>
                    <div class="skeleton w-25 h-7"></div>
                    <div class="skeleton w-25 h-7"></div>
                </div>

                <div class="skeleton h-50 w-full"></div>
            </section>
            <section class="rounded-xl bg-white p-6 mt-7.5 flex-1">

            </section>
        </div>
    </div>

    <!-- section 2 Table -->
    <div id="table-section" class="rounded-xl bg-white mt-7.5 py-10 px-7.5 mb-40">
        <div class="skeleton h-10 w-44 mb-8"></div>

        <div class="flex items-center gap-x-7 mt-8 mb-20">
            <div class="skeleton w-35 h-9"></div>
            <div class="skeleton w-35 h-9"></div>
            <div class="skeleton w-35 h-9"></div>
        </div>

        <div class="space-y-4">
            <!-- Table Header -->
            <div class="flex justify-between items-center py-4 border-b border-gray-200">
                <div class="skeleton w-22 h-6"></div>
                <div class="skeleton w-22 h-6"></div>
                <div class="skeleton w-22 h-6"></div>
                <div class="skeleton w-22 h-6"></div>
                <div class="skeleton w-22 h-6"></div>
                <div class="skeleton w-22 h-6"></div>
            </div>

            <!-- Table Rows -->
            <div class="space-y-5 divide-y divide-gray-200">
                <div class="flex justify-between items-center py-4">
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                </div>
                <div class="flex justify-between items-center py-4">
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                </div>
                <div class="flex justify-between items-center py-4">
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                </div>
                <div class="flex justify-between items-center py-4">
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                    <div class="skeleton h-7 w-29"></div>
                </div>
            </div>
        </div>
    </div>


</div>



<!--<script src="js/apexcharts.js"></script>-->

<script>
    function initFilter(containerId, bgId) {
        const container = document.getElementById(containerId);
        const bg = document.getElementById(bgId);
        const buttons = container.querySelectorAll(".item_filter");

        function moveBackgroundTo(el) {
            const {offsetLeft, offsetTop, offsetWidth, offsetHeight} = el;
            bg.style.width = `${offsetWidth}px`;
            bg.style.height = `${offsetHeight}px`;
            bg.style.transform = `translateX(${offsetLeft}px) translateY(${offsetTop}px)`;
        }

        buttons.forEach(btn => {
            btn.addEventListener("click", () => {
                buttons.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                moveBackgroundTo(btn);
            });
        });

        const activeBtn = container.querySelector(".active") || buttons[0];
        if (activeBtn) {
            bg.classList.remove("transition-all", "duration-300");
            moveBackgroundTo(activeBtn);

            requestAnimationFrame(() => {
                bg.classList.add("transition-all", "duration-300");
            });
        }
    }

</script>

<script>
    const loadData = {
        card1: () => ({
            title: "Conversion Rate",
            number: "126",
            change: "+ 10%",
            changeType: "positive"
        }),
        card2: () => ({
            title: "Unique Visitors",
            number: "68%",
            change: "- 2%",
            changeType: "negative"
        }),
        card3: () => ({
            title: "All Clicks",
            number: "540K",
            change: "+ 15%",
            changeType: "positive"
        }),
        chart: () => ({
            title: "Main Page",
            filters: ["12 months", "3 months", "30 days", "7 days", "24 hours"]
        }),
        table: () => ({
            title: "All Pages",
            data: [
                {
                    status: "active",
                    name: "Academy",
                    visitors: "5",
                    viwe: "499",
                    clicks: null,
                    conversion: "80%",
                    action: "Details"
                },
                {
                    status: "active",
                    name: "weblog",
                    visitors: "12",
                    viwe: "459",
                    clicks: null,
                    conversion: "67%",
                    action: "Details"
                },
                {
                    status: "not_active",
                    name: "about us",
                    visitors: "25",
                    viwe: "493",
                    clicks: "15",
                    conversion: "60%",
                    action: "Details"
                },
                {
                    status: "not_active",
                    name: "contact us",
                    visitors: "8",
                    viwe: "436",
                    clicks: "6",
                    conversion: "75%",
                    action: "Details"
                }
            ]
        }),
        top5: () => ({
            title: "Top Fives",
            filters: ["Pages", "Products", "Posts"],
            pages: ["abount us", "contact us", "paymanet", "landing page", "shop"]
        })
    };

    //   ----------------------------------

    function loadCard(cardId, data) {
        const card = document.getElementById(cardId);
        const changeClass = data.changeType === 'positive' ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100';

        card.innerHTML = `
                <h5 class="text-gray-500 font-semibold">${data.title}</h5>
                <div class="flex justify-between items-center mt-2.5">
                    <span class="text-40 font-bold">${data.number}</span>
                    <span class="text-lg font-semibold ${changeClass} rounded-3xl px-3.5 py-1">${data.change}</span>
                </div>
            `;

        card.classList.add('fade-in');
    }

    //   ----------------------------------

    var options = {
        series: [{
            name: 'Viwe',
            data: [31, 40, 28, 51, 42, 109, 100]
        }, {
            name: 'Click',
            data: [11, 32, 45, 32, 34, 52, 41]
        }],
        chart: {
            height: 350,
            type: 'area',
            toolbar: {
                show: false
            },
            fontFamily: 'Montserrat'
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        xaxis: {
            type: 'datetime',
            categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            },
        },
    };

    //   ----------------------------------

    function loadChart(data) {
        const section = document.getElementById('chart-section');

        section.innerHTML = `
                <h5 class="text-3xl font-bold">${data.title}</h5>

                <div id="time-filter" class="text-base flex items-center gap-x-7 mt-6.5 font-medium text-gray-500">
                    ${data.filters.map((filter, index) =>
            `<span class="item_filter cursor-pointer ${index === 0 ? 'active' : ''}">${filter}</span>`
        ).join('')}
                </div>

            <div id="active-bg"
                class="absolute top-0 left-0 h-full rounded-3xl bg-gray-100 transition-all duration-300 -z-10"></div>

                <div id="chart" class="mt-5"></div>
            `;

        //   ----------------------------------

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();

        //   ----------------------------------

        initFilter("time-filter", "active-bg");

        section.classList.add('fade-in');
    }

    //   ----------------------------------

    function loadTable(data) {
        const section = document.getElementById('table-section');

        section.innerHTML = `
                <div class="flex items-center justify-between">
                    <h5 class="text-3xl font-bold">${data.title}</h5>
                    <div class="text-base font-semibold">
                        <button class="px-5 py-2.5 border border-black/20 rounded-xl mr-4 cursor-pointer">Export
                            Report</button>
                        <button class="px-5 py-2.5 border border-black/20 rounded-xl cursor-pointer">Sync Pages</button>
                    </div>
                </div>

                <div id="tabs" class="flex gap-x-3 mt-15 border-b border-gray3 text-base font-semibold">
                    <button class="tab-btn active-tab">Pages</button>
                    <button class="tab-btn">Products</button>
                    <button class="tab-btn">Posts</button>
                    <button class="tab-btn">+</button>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-10 mt-7">
                    <input type="text" placeholder="Search Page Name ..."
                        class="convly_input" />

                    <div class="flex items-center space-x-2">
                        <span class="text-gray-500 font-medium text-sm">Date Range:</span>
                        <select
                            class="convly_slect">
                            <option>All Time</option>
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="text-gray-500 font-medium text-sm">Sort By:</span>
                        <select
                            class="convly_slect">
                            <option>Views (High to Low)</option>
                            <option>Views (Low to High)</option>
                            <option>Conversion Rate</option>
                        </select>
                    </div>
                </div>

                <table class="w-full table-auto divide-y divide-gray-200 mt-20">
                    <thead class="text-sm text-left">
                        <tr class="*:font-medium *:py-4 text-gray-500">
                            <th>STATUS</th>
                            <th class="w-1/3">PAGE NAME</th>
                            <th>UNIQUE VISITORS</th>
                            <th>TOTAL VIEWS</th>
                            <th>BUTTON CLICKS</th>
                            <th>CONVERSION RATE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="text-base font-semibold divide-y divide-gray-200">
                        ${data.data.map(row => `

                        <tr class="*:py-6">
                            <td>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input ${row.status === "active" ? 'checked' : ''} type="checkbox" class="sr-only peer" value="" />
                                    <div
                                        class="group peer bg-white rounded-full duration-300 w-13 h-6 ring-2 ring-red-500 after:duration-300 after:bg-red-500 peer-checked:after:bg-green-500 peer-checked:ring-green-500 after:rounded-full after:absolute after:h-4 after:w-4 after:top-1 after:left-1 after:flex after:justify-center after:items-center peer-checked:after:translate-x-7 peer-hover:after:scale-95">
                                    </div>
                                </label>
                            </td>
                            <td>${row.name}</td>
                            <td>${row.visitors}</td>
                            <td>${row.viwe}</td>
                            <td>
                                ${row.clicks === null ? '<button class="cursor-pointer px-3 py-2.5 border border-gray4 rounded-10px">Add Button</button>' : row.clicks}
                            </td>
                            <td>
                                <span class="text-lg font-semibold text-green-600 bg-green-100 rounded-3xl px-4 py-1">
                                ${row.conversion}</span>
                            </td>
                            <td>
                                <a href="#" class="convly_badge">
                                    ${row.action}
                                </a>
                            </td>
                        </tr>

                        `).join('')}
                    </tbody>
                </table>
            `;

        section.classList.add('fade-in');

        const tabs = document.querySelectorAll("#tabs .tab-btn");
        tabs.forEach(btn => {
            btn.addEventListener("click", () => {
                tabs.forEach(b => b.classList.remove("active-tab"));
                btn.classList.add("active-tab");
            });
        });
    }

    function loadTop5(data) {
        const Top5 = document.getElementById('top5');

        Top5.innerHTML = `
                    <h5 class="text-3xl font-bold">${data.title}</h5>

                    <div id="active-bg-2"
                        class="absolute top-0 left-0 h-full rounded-3xl bg-gray-100 transition-all duration-300 -z-10">
                    </div>

                    <div id="time-filter-2"
                        class="text-base flex items-center gap-x-5 mt-6.5 font-medium text-gray-500 *:cursor-pointer">
                        ${data.filters.map((filter, index) =>
            `<span class="item_filter cursor-pointer ${index === 0 ? 'active' : ''}">${filter}</span>`
        ).join('')}
                    </div>

                    <div class="rounded-xl bg-gray1 mt-6 p-7 text-base font-semibold convly_top5">
                        ${data.pages.map((page) => `
                        <h5>${page}</h5>
                        `).join('')}
                    </div>
            `;

        initFilter("time-filter-2", "active-bg-2");

        Top5.classList.add('fade-in');
    }

    //   ----------------------------------

    setTimeout(() => loadCard('card1', loadData.card1()), 1000);
    setTimeout(() => loadCard('card2', loadData.card2()), 2500);
    setTimeout(() => loadCard('card3', loadData.card3()), 2000);
    setTimeout(() => loadChart(loadData.chart()), 2500);
    setTimeout(() => loadTable(loadData.table()), 3000);
    setTimeout(() => loadTop5(loadData.top5()), 2500);


</script>

<script>
    var options = {
        series: [{
            name: 'Viwe',
            data: [31, 40, 28, 51, 42, 109, 100]
        }, {
            name: 'Click',
            data: [11, 32, 45, 32, 34, 52, 41]
        }],
        chart: {
            height: 350,
            type: 'area',
            toolbar: {
                show: false
            },
            fontFamily: 'Montserrat'
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        xaxis: {
            type: 'datetime',
            categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            },
        },
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>


<script>
    const links = document.querySelectorAll("#navbar .nav-link");

    links.forEach(link => {
        link.addEventListener("click", (e) => {
            links.forEach(l => l.classList.remove("active_nav"));

            link.classList.add("active_nav");
        });
    });
</script>
