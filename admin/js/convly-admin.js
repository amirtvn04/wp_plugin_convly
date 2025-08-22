/**
 * Convly Admin JavaScript
 */


(function ($) {
    'use strict';

    // Global variables
    let mainChart = null;
    let currentTab = 'pages';
    let currentPage = 1;
    let itemsPerPage = 20;

    // Initialize
    $(document).ready(function () {
        // Load initial data
        loadSummaryCards();
        loadMainChart('30_days', true);
        loadPagesList();

        // Event handlers
        bindEventHandlers();
    });

    // Bind event handlers
    function bindEventHandlers() {
        // Period selectors for summary cards
        $('.convly-period-select').on('change', function () {
            const metric = $(this).data('metric');
            const period = $(this).val();
            loadSummaryCard(metric, period);
        });

        // Tab switching
        $('.convly-tab').on('click', function (e) {
            e.preventDefault();
            $('.convly-tab').removeClass('active');
            $(this).addClass('active');
            currentTab = $(this).data('tab');
            currentPage = 1;
            loadPagesList();
        });

        // Add custom tab
        $('.convly-add-tab').on('click', function (e) {
            e.preventDefault();
            $('#convly-custom-tab-modal').show();
        });

        // Custom tab form submission
        $('#convly-custom-tab-form').on('submit', function (e) {
            e.preventDefault();
            addCustomTab();
        });

        // Date filter
        $('#convly-date-filter').on('change', function () {
            if ($(this).val() === 'custom') {
                $('.convly-custom-date-range').show();
            } else {
                $('.convly-custom-date-range').hide();
                loadPagesList();
            }
        });

        // Custom date range
        $('#convly-date-from, #convly-date-to').on('change', function () {
            if ($('#convly-date-from').val() && $('#convly-date-to').val()) {
                loadPagesList();
            }
        });

        // Sort by
        $('#convly-sort-by').on('change', function () {
            loadPagesList();
        });

        // Export PDF
        $('#convly-export-pdf').on('click', function () {
            exportPDFReport();
        });

        // Modal close buttons
        $('.convly-modal-close, .convly-modal-cancel').on('click', function () {
            $('.convly-modal').hide();
        });

        // Close modal when clicking outside
        $('.convly-modal').on('click', function (e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
    }

    // Load summary cards
    function loadSummaryCards() {
        $('.convly-card').each(function () {
            const metric = $(this).data('metric');
            const period = $(this).find('.convly-period-select').val();
            loadSummaryCard(metric, period);
        });
    }

    // Load individual summary card
    function loadSummaryCard(metric, period) {
        const $card = $(`.convly-card[data-metric="${metric}"]`);

        const titles = {
            'conversion_rate': 'Conversion Rate',
            'total_views': 'Unique Visitors',
            'total_clicks': 'All Clicks',
        };

        const title = titles[metric] || metric;

        $card.addClass("loading");
        $card.html(`
        <div class="skeleton h-7 w-44 mb-4"></div>
        <div class="flex justify-between items-center mt-2.5">
            <span class="skeleton w-22 h-10"></span>
            <span class="skeleton h-10 w-20"></span>
        </div>
    `);

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
                    $card.removeClass("loading").html(`
                    <h5 class="text-gray-500 font-semibold">${title}</h5>
                    <div class="convly-metric flex justify-between items-center mt-2.5">
                        <span class="convly-metric-value text-40 font-bold">${response.data.value}</span>
                        <span class="convly-metric-change text-lg font-semibold rounded-3xl px-3.5 py-1"></span>
                    </div>
                `);

                    const $change = $card.find('.convly-metric-change');
                    if (response.data.change) {
                        const changeText = response.data.change > 0 ? '+' + response.data.change + '%' : response.data.change + '%';
                        $change.text(changeText)
                            .removeClass('text-green-600 bg-green-100 text-red-600 bg-red-100')
                            .addClass(response.data.change > 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100');
                    }
                } else {
                    $card.removeClass("loading").html(`
                    <h5 class="text-gray-500 font-semibold">${title}</h5>
                    <div class="convly-metric flex justify-between items-center mt-2.5">
                        <span class="convly-metric-value text-40 font-bold">${response.data.value}</span>
                        <span class="convly-metric-change text-lg font-semibold rounded-3xl px-3.5 py-1"></span>
                    </div>                `);
                }
            },
            error: function () {
                $card.removeClass("loading").html(`
                    <h5 class="text-gray-500 font-semibold">${title}</h5>
                    <div class="convly-metric flex justify-between items-center mt-2.5">
                        <span class="convly-metric-value text-40 font-bold">${response.data.value}</span>
                        <span class="convly-metric-change text-lg font-semibold rounded-3xl px-3.5 py-1"></span>
                    </div>            `);
                showNotification(convly_ajax.i18n.error, 'error');
            }
        });
    }

    // Load main chart
    function loadMainChart(period, isInitialLoad = true) {
        const $target = isInitialLoad ? $('#chart-section') : $('#convly-chart');

        const originalHtml = $target.html();
        const originalClasses = $target.attr('class');

        $target.addClass("loading");

        if (isInitialLoad) {
            $target.html(`
            <div class="skeleton h-10 w-44 mb-6"></div>
            <div class="flex items-center gap-x-7 mt-8 mb-9">
                <div class="skeleton w-25 h-7"></div>
                <div class="skeleton w-25 h-7"></div>
                <div class="skeleton w-25 h-7"></div>
                <div class="skeleton w-25 h-7"></div>
                <div class="skeleton w-25 h-7"></div>
            </div>
            <div class="skeleton h-87.5 w-full"></div>
        `);
        } else {
            $target.html(`<div class="skeleton h-87.5 w-full"></div>`);
        }

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_stats',
                nonce: convly_ajax.nonce,
                type: 'chart_data',
                period: period
            },
            success: function (response) {
                if (response.success) {
                    $target.removeClass("loading");

                    if (isInitialLoad) {
                        $target.html(`
                        <h5 class="text-3xl font-bold">Main Page</h5>
                        
                        <div id="active-bg" 
                            class="absolute top-0 left-0 h-full rounded-3xl bg-gray-100 transition-all duration-300 -z-10">
                        </div>
                        
                        <div id="time-filter" 
                            class="text-base flex items-center gap-x-7.5 mt-6.5 font-medium text-gray-500 *:cursor-pointer">
                            <span class="item_filter" data-period="12_months">12 months</span>
                            <span class="item_filter" data-period="6_months">6 months</span>
                            <span class="item_filter" data-period="3_months">3 months</span>
                            <span class="item_filter active" data-period="30_days">30 days</span>
                            <span class="item_filter" data-period="7_days">7 days</span>
                            <span class="item_filter" data-period="24_hours">24 hours</span>
                        </div>
                        
                        <div id="convly-chart" class="mt-5"></div>
                    `);

                        initFilter("time-filter", "active-bg");
                        setupChartEventHandlers();

                        renderMainChart(response.data, $('#convly-chart')[0]);
                    } else {
                        renderMainChart(response.data, $target[0]);
                    }
                } else {
                    handleChartError($target, originalHtml, originalClasses);
                }
            },
            error: function () {
                handleChartError($target, originalHtml, originalClasses);
            }
        });
    }

    function handleChartError($target, originalHtml, originalClasses) {
        $target.removeClass("loading");
        $target.attr('class', originalClasses);
        $target.html(originalHtml);
        showNotification(convly_ajax.i18n.error, 'error');
    }

    function setupChartEventHandlers() {
        $('.item_filter').off('click');
        $('.item_filter').on('click', function () {
            const period = $(this).data('period');
            loadMainChart(period, false);
        });
    }

    function renderMainChart(data, chartElement) {
        if (window.convlyChart) {
            window.convlyChart.destroy();
        }

        const options = {
            series: [{
                name: 'View',
                data: data.views || []
            }, {
                name: 'Click',
                data: data.clicks || []
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
                categories: data.labels || []
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
        };

        window.convlyChart = new ApexCharts(chartElement, options);
        window.convlyChart.render();
    }

    // Load pages list
    function loadPagesList() {
        const $table_section = $('#table-section');

        $table_section.html(`
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
        `)

        const data = {
            action: 'convly_get_page_list',
            nonce: convly_ajax.nonce,
            tab: currentTab,
            page: currentPage,
            per_page: -1,
            sort_by: $('#convly-sort-by').val(),
            date_filter: $('#convly-date-filter').val()
        };

        if ($('#convly-date-filter').val() === 'custom') {
            data.date_from = $('#convly-date-from').val();
            data.date_to = $('#convly-date-to').val();
        }

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    $table_section.html(`
                <div class="flex items-center justify-between">
                    <h5 class="text-3xl font-bold">All Pages</h5>
                    <div class="text-base font-semibold">
                        <button class="px-5 py-2.5 border border-black/20 rounded-xl mr-4 cursor-pointer">Export
                            Report</button>
                        <button class="px-5 py-2.5 border border-black/20 rounded-xl cursor-pointer">Sync Pages</button>
                    </div>
                </div>
                
                <div id="tabs" class="flex gap-x-3 mt-15 border-b border-gray3 text-base font-semibold">
                    <button class="convly-tab tab-btn active-tab" data-tab="pages">Pages</button>
                    <button class="convly-tab tab-btn" data-tab="products">Products</button>
                    <button class="convly-tab tab-btn" data-tab="posts">Posts</button>
                    <button class="convly-custom-tabs tab-btn" style="display: none;"></button>
                    <button class="convly-add-tab tab-btn">+</button>
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



            
            
            <div class="convly-filters">
            <div class="convly-filter-group">
                <label>1</label>
                <select id="convly-date-filter">
                    <option value="all">0</option>
                    <option value="today">0</option>
                    <option value="yesterday">0</option>
                    <option value="7_days">0</option>
                    <option value="30_days">0</option>
                    <option value="custom">1</option>
                </select>
                <div class="convly-custom-date-range" style="display:none;">
                    <input type="date" id="convly-date-from"/>
                    <span>77</span>
                    <input type="date" id="convly-date-to"/>
                </div>
            </div>

            <div class="convly-filter-group">
                <label>cc</label>
                <select id="convly-sort-by">
                    <option value="views_desc" selected>22</option>
                    <option value="conversion_rate_desc">3</option>
                    <option value="clicks_desc">4</option>
                    <option value="name_asc">44</option>
                    <option value="name_desc">3</option>
                </select>
            </div>
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
                    <tbody id="convly_page_list" class="text-base font-semibold divide-y divide-gray-200">

                    </tbody>
                </table>
                `);

                    renderPagesList(response.data);


                    const tabs = document.querySelectorAll("#tabs .tab-btn");

                    tabs.forEach(btn => {
                        btn.addEventListener("click", () => {
                            tabs.forEach(b => b.classList.remove("active-tab"));
                            btn.classList.add("active-tab");
                        });
                    });


                    // Tab switching
                    $('.convly-tab').on('click', function (e) {
                        e.preventDefault();
                        $('.convly-tab').removeClass('active');
                        $(this).addClass('active');
                        currentTab = $(this).data('tab');
                        currentPage = 1;
                        loadPagesList();
                    });

                    // Add custom tab
                    $('.convly-add-tab').on('click', function (e) {
                        $('#convly-custom-tab-modal').show();
                    });

                } else {
                    $table_section.html(`<tr><td colspan="7">${response.data}</td></tr>`);
                }
            },
            error: function () {
                $table_section.html(`<tr><td colspan="7">${convly_ajax.i18n.error}</td></tr>`);
            }
        });
    }

    // Render pages list
    function renderPagesList(data) {
        const $tbody = $('#convly_page_list');
        $tbody.empty();

        if (data.items.length === 0) {
            $tbody.html('<tr><td colspan="7">No pages found</td></tr>');
            return;
        }

        data.items.forEach(function (page) {
            const conversionRate = page.unique_visitors > 0 ?
                ((page.total_clicks / page.unique_visitors) * 100).toFixed(1) : 0;

            const conversionClass = conversionRate >= 10 ? 'high' :
                conversionRate >= 5 ? 'medium' : 'low';

            const row = `
                        <tr class="*:py-6">
                            <td class="column-status">
                                <label class="convly-status-toggle relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" value="" ${page.is_active == 1 ? 'checked' : ''} data-page-id="${page.page_id}"/>
                                    <span
                                        class="group peer bg-white rounded-full duration-300 w-13 h-6 ring-2 ring-red-500 after:duration-300 after:bg-red-500 peer-checked:after:bg-green-500 peer-checked:ring-green-500 after:rounded-full after:absolute after:h-4 after:w-4 after:top-1 after:left-1 after:flex after:justify-center after:items-center peer-checked:after:translate-x-7 peer-hover:after:scale-95">
                                    </span>
                                </label>
                            </td>
                            <td class="column-name">
                            <a href="${page.page_url}" target="_blank">${escapeHtml(page.page_title)}</a>
                            </td>
                            <td class="column-visitors">${page.unique_visitors}</td>
                            <td class="column-views">${page.total_views}</td>
                            <td class="column-clicks">
                                ${page.has_buttons == 1 ?
                                page.total_clicks : `
                                <button class="cursor-pointer px-3 py-2.5 border border-gray4 rounded-10px convly-add-button">Add Button</button>
                                `}
                            </td>
                            <td class="column-rate">
                                <span class="text-lg font-semibold text-green-600 bg-green-100 rounded-3xl px-4 py-1 ${conversionClass}">
                                ${conversionRate}%    
                                </span>
                            </td>
                            <td class="column-actions">
                                <a href="${getDetailsUrl(page.page_id)}" class="convly_badge">
                                    ${convly_ajax.i18n.details}
                                </a>
                            </td>
                        </tr>                        
`;

            $tbody.append(row);
        });

        // Bind events for new elements
        bindPageListEvents();

        // Update pagination
        updatePagination(data.total, data.per_page, data.current_page);
    }

    // Bind events for page list items
    function bindPageListEvents() {
        // Status toggle
        $('.convly-status-toggle input').off('change').on('change', function () {
            const pageId = $(this).data('page-id');
            const isActive = $(this).is(':checked');
            togglePageStatus(pageId, isActive);
        });

        // Add button
        $('.convly-add-button').off('click').on('click', function () {
            const pageId = $(this).data('page-id');
            openButtonModal(pageId);
        });
    }

    // Toggle page status
    function togglePageStatus(pageId, isActive) {
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
                    showNotification('Page status updated', 'success');
                } else {
                    showNotification(response.data, 'error');
                    // Revert the checkbox
                    $(`.convly-status-toggle input[data-page-id="${pageId}"]`).prop('checked', !isActive);
                }
            }
        });
    }

    // Open button modal
    function openButtonModal(pageId, buttonId = null) {
        $('#convly-page-id').val(pageId);
        $('#convly-button-id').val(buttonId || '');

        if (buttonId) {
            $('#convly-button-modal-title').text('Edit Button');
            // Load button data would go here
        } else {
            $('#convly-button-modal-title').text(convly_ajax.i18n.add_button);
            $('#convly-button-form')[0].reset();
            $('#convly-page-id').val(pageId);
        }

        $('#convly-button-modal').show();
    }

    // Handle button form submission
    $('#convly-button-form').on('submit', function (e) {
        e.preventDefault();

        const formData = {
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
                    loadPagesList();
                    showNotification('Button saved successfully', 'success');
                } else {
                    showNotification(response.data, 'error');
                }
            },
            error: function () {
                showNotification('Failed to save button', 'error');
            }
        });
    });

    // Add custom tab
    function addCustomTab() {
        const tabName = $('#convly-tab-name').val();

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
                    // Add new tab to UI
                    const newTab = `<a href="#" class="convly-tab" data-tab="${response.data.tab_slug}">${response.data.tab_name}</a>`;
                    $('.convly-custom-tabs').append(newTab);
                    showNotification('Custom tab added successfully', 'success');
                } else {
                    showNotification(response.data, 'error');
                }
            }
        });
    }

    // Export PDF report
    function exportPDFReport() {
        const form = $('<form>', {
            method: 'POST',
            action: convly_ajax.ajax_url
        });

        const params = {
            action: 'convly_generate_pdf_report',
            nonce: convly_ajax.nonce,
            date_filter: $('#convly-date-filter').val(),
            tab: currentTab
        };

        if ($('#convly-date-filter').val() === 'custom') {
            params.date_from = $('#convly-date-from').val();
            params.date_to = $('#convly-date-to').val();
        }

        $.each(params, function (key, value) {
            $('<input>').attr({
                type: 'hidden',
                name: key,
                value: value
            }).appendTo(form);
        });

        form.appendTo('body').submit().remove();
    }

    function updatePagination(total, perPage, current) {
        // Pagination disabled
        $('.convly-pagination').hide();
    }

    // Get details URL
    function getDetailsUrl(pageId) {
        return `${window.location.origin}/wp-admin/admin.php?page=convly-page-details&page_id=${pageId}`;
    }

    // Show notification
    function showNotification(message, type = 'info') {
        const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
        $('.wrap').prepend($notice);

        // Make it dismissible
        $notice.on('click', '.notice-dismiss', function () {
            $notice.remove();
        });

        // Auto remove after 5 seconds
        setTimeout(function () {
            $notice.fadeOut(function () {
                $(this).remove();
            });
        }, 5000);
    }

    // Escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

// Helper function to prevent caching
    function getNoCacheData(data) {
        data._nocache = Date.now();
        return data;
    }

})(jQuery);