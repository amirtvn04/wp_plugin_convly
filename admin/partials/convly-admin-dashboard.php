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

<!--<div class="wrap convly-dashboard">-->
<!--     Pages List-->
<!--    <div class="convly-pages-section">-->
<!--        <div class="convly-pages-header">-->
<!--            <h2>--><?php //_e('All Pages', 'convly'); ?><!--</h2>-->
<!--            <div class="convly-pages-actions">-->
<!--                <button id="convly-export-pdf" class="button button-secondary">-->
<!--                    --><?php //_e('Export PDF Report', 'convly'); ?>
<!--                </button>-->
<!--                <button id="convly-sync-pages" class="button button-secondary"-->
<!--                        title="--><?php //_e('Sync with WordPress pages', 'convly'); ?><!--">-->
<!--                    <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>-->
<!--                    --><?php //_e('Sync Pages', 'convly'); ?>
<!--                </button>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!--         Tabs-->
<!--        <div class="convly-tabs">-->
<!--            <ul class="convly-tab-list">-->
<!--                <li><a href="#" class="convly-tab active" data-tab="pages">--><?php //_e('Pages', 'convly'); ?><!--</a></li>-->
<!--                <li><a href="#" class="convly-tab" data-tab="products">--><?php //_e('Products', 'convly'); ?><!--</a></li>-->
<!--                <li><a href="#" class="convly-tab" data-tab="posts">--><?php //_e('Posts', 'convly'); ?><!--</a></li>-->
<!--                <li class="convly-custom-tabs"></li>-->
<!--                <li><a href="#" class="convly-add-tab" title="--><?php //_e('Add Custom Tab', 'convly'); ?><!--">+</a></li>-->
<!--            </ul>-->
<!--        </div>-->
<!--        <div id="tabs" class="flex gap-x-3 mt-15 border-b border-gray3 text-base font-semibold">-->
<!--            <button class="convly-tab tab-btn active-tab" data-tab="pages">Pages</button>-->
<!--            <button class="convly-tab tab-btn" data-tab="products">Products</button>-->
<!--            <button class="convly-tab tab-btn" data-tab="posts">Posts</button>-->
<!--            <div class="convly-custom-tabs"></div>-->
<!--            <button class="convly-add-tab tab-btn">+</button>-->
<!--        </div>-->
<!---->
<!--         Filters and Sorting-->
<!--        <div class="convly-filters">-->
<!--            <div class="convly-filter-group">-->
<!--                <label>--><?php //_e('Date Range:', 'convly'); ?><!--</label>-->
<!--                <select>-->
<!--                    <option value="all">--><?php //_e('All Time', 'convly'); ?><!--</option>-->
<!--                    <option value="today">--><?php //_e('Today', 'convly'); ?><!--</option>-->
<!--                    <option value="yesterday">--><?php //_e('Yesterday', 'convly'); ?><!--</option>-->
<!--                    <option value="7_days">--><?php //_e('Last 7 Days', 'convly'); ?><!--</option>-->
<!--                    <option value="30_days">--><?php //_e('Last 30 Days', 'convly'); ?><!--</option>-->
<!--                    <option value="custom">--><?php //_e('Custom Range', 'convly'); ?><!--</option>-->
<!--                </select>-->
<!--                <div  style="display:none;">-->
<!--                    <input type="date" id="convly--from"/>-->
<!--                    <span>--><?php //_e('to', 'convly'); ?><!--</span>-->
<!--                    <input type="date" id="convly--to"/>-->
<!--                </div>-->
<!--            </div>-->
<!---->
<!--            <div class="convly-filter-group">-->
<!--                <label>--><?php //_e('Sort By:', 'convly'); ?><!--</label>-->
<!--                <select >-->
<!--                    <option value="views_desc" selected>--><?php //_e('Views (High to Low)', 'convly'); ?><!--</option>-->
<!--                    <option value="conversion_rate_desc">--><?php //_e('Conversion Rate (High to Low)', 'convly'); ?><!--</option>-->
<!--                    <option value="clicks_desc">--><?php //_e('Clicks (High to Low)', 'convly'); ?><!--</option>-->
<!--                    <option value="name_asc">--><?php //_e('Name (A-Z)', 'convly'); ?><!--</option>-->
<!--                    <option value="name_desc">--><?php //_e('Name (Z-A)', 'convly'); ?><!--</option>-->
<!--                </select>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!-- Pages Table -->
<!--        <div class="convly-table-wrapper">-->
<!--            <table class="convly-pages-table widefat fixed striped">-->
<!--                <thead>-->
<!--                <tr>-->
<!--                    <th class="column-status">--><?php //_e('Status', 'convly'); ?><!--</th>-->
<!--                    <th class="column-name">--><?php //_e('Page Name', 'convly'); ?><!--</th>-->
<!--                    <th class="column-visitors">--><?php //_e('Unique Visitors', 'convly'); ?><!--</th>-->
<!--                    <th class="column-views">--><?php //_e('Total Views', 'convly'); ?><!--</th>-->
<!--                    <th class="column-clicks">--><?php //_e('Button Clicks', 'convly'); ?><!--</th>-->
<!--                    <th class="column-rate">--><?php //_e('Conversion Rate', 'convly'); ?><!--</th>-->
<!--                    <th class="column-actions">--><?php //_e('Actions', 'convly'); ?><!--</th>-->
<!--                </tr>-->
<!--                </thead>-->
<!--                <tbody id="convly-pages-list">-->
<!--                <tr>-->
<!--                    <td colspan="7" class="convly-loading">--><?php //_e('Loading...', 'convly'); ?><!--</td>-->
<!--                </tr>-->
<!--                </tbody>-->
<!--            </table>-->
<!--        </div>-->
<!---->
<!--         Pagination-->
<!--        <div class="convly-pagination">-->
<!--            <div class="tablenav-pages">-->
<!--                <span class="displaying-num"></span>-->
<!--                <span class="pagination-links"></span>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

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
                <button id="convly-tab-submit" type="submit"
                        class="button button-primary"><?php _e('Add Tab', 'convly'); ?></button>
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


<div class="container text-xl convly_container">
    <!-- header -->
    <header>
        <div class="h-22.5 bg-black1 rounded-xl text-white/80 flex items-center justify-between px-6 mt-5">
            <ul id="navbar" class="convly_navbar">
                <li><a href="<?php echo admin_url('admin.php?page=convly'); ?>"
                       class="nav-link active_nav">Dashboard</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=convly-page-details'); ?>"
                       class="nav-link">Details</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=convly-settings'); ?>" class="nav-link">Settings</a>
                </li>
            </ul>
            <img src="<?php echo CONVLY_PLUGIN_URL; ?>admin/images/image7.png" alt="convly">
        </div>

    </header>

    <!-- section 1 -->
    <div class="flex mt-7.5 gap-x-7.5 items-stretch">

        <div class="w-70/100">
            <!-- numbers -->
            <div class="grid grid-cols-3 gap-x-8">
                <div id="card1" class="convly_card convly-card bg-white rounded-xl p-7.5" data-metric="conversion_rate">
                    <!--                    <h5 class="text-gray-500 font-semibold">-->
                    <?php //_e('Conversion Rate', 'convly'); ?><!--</h5>-->
                    <div class="convly-metric flex justify-between items-center mt-2.5">
                        <span class="convly-metric-value text-40 font-bold"></span>
                        <span class="convly-metric-change text-lg font-semibold ${changeClass} rounded-3xl px-3.5 py-1"></span>
                    </div>
                </div>
                <div id="card2" class="convly_card convly-card bg-white rounded-xl p-7.5" data-metric="total_views">
                    <!--                    <h5 class="text-gray-500 font-semibold">-->
                    <?php //_e('Unique Visitors', 'convly'); ?><!--</h5>-->
                    <div class="convly-metric flex justify-between items-center mt-2.5">
                        <span class="convly-metric-value text-40 font-bold"></span>
                        <span class="convly-metric-change text-lg font-semibold ${changeClass} rounded-3xl px-3.5 py-1"></span>
                    </div>
                </div>
                <div id="card3" class="convly_card convly-card bg-white rounded-xl p-7.5" data-metric="total_clicks">
                    <!--                    <h5 class="text-gray-500 font-semibold">-->
                    <?php //_e('All Clicks', 'convly'); ?><!--</h5>-->
                    <div class="convly-metric flex justify-between items-center mt-2.5">
                        <span class="convly-metric-value text-40 font-bold"></span>
                        <span class="convly-metric-change text-lg font-semibold ${changeClass} rounded-3xl px-3.5 py-1"></span>
                    </div>
                </div>

            </div>

            <!-- chart -->
            <div id="chart-section" class="rounded-xl bg-white mt-7.5 p-6 relative z-0">

            </div>
        </div>


        <div id="top5-container" class="convly-top5-section w-30/100">

        </div>
    </div>

    <!-- section 2 Table -->
    <div id="table-section" class="rounded-xl bg-white mt-7.5 py-10 px-7.5 mb-40">

    </div>


</div>

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

    //   ----------------------------------

    //   ----------------------------------


    //   ----------------------------------

    //   ----------------------------------

    // setTimeout(() => loadTop5(loadData.top5()), 2500);


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

<script type="text/javascript">
    jQuery(document).ready(function ($) {


        // Add manage button for custom tabs

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
        // loadSummaryCards();
        // loadChart('30_days');
        // loadPages();
    });
</script>