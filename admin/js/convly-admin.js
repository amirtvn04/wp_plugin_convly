/**
 * Convly Admin JavaScript
 */


(function ($) {
    'use strict';

    // Global variables
    let currentTab = 'pages';
    let currentPage;

    // Initialize
    $(document).ready(function () {
        // Load initial data
        loadSummaryCards();
        loadMainChart('7_days', true);
        loadTopPages('pages', true);
        loadPagesList(true, 1);

        // Event handlers
        bindEventHandlers();
    });

    // Bind event handlers
    function bindEventHandlers() {
        // Modal close buttons
        $('.convly-modal-close, .convly-modal-cancel').on('click', function () {
            document.body.style.overflow = '';
            $('.convly-modal').hide();
        });

        // Close modal when clicking outside
        $('.convly-modal').on('click', function (e) {
            if (e.target === this) {
                document.body.style.overflow = '';
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
            'total_views': 'Total Views',
            'total_clicks': 'Total Clicks',
        };

        const title = titles[metric] || metric;

        $card.addClass("loading");
        $card.html(`
        <div class="convly_skeleton h-7 w-44 mb-4"></div>
        <div class="flex justify-between items-center mt-2.5">
            <span class="convly_skeleton w-22 h-10"></span>
            <span class="convly_skeleton h-10 w-20"></span>
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
                $card.addClass('fade-in');
                if (response.success) {
                    $card.removeClass("loading").html(`
                    <h5 class="text-gray-500 font-semibold">${title}</h5>
                    <div class="convly-metric flex justify-between items-center mt-2.5">
                        <span class="convly-metric-value text-40 font-bold">${response.data.value}</span>
                        <span class="convly-metric-change text-lg font-semibold rounded-3xl px-3.5 py-1"></span>
                    </div>
                `);

                    const $change = $card.find('.convly-metric-change');

                    if (response.data.change !== undefined && response.data.change !== null) {
                        const changeText = response.data.change > 0
                            ? '+' + response.data.change + '%'
                            : response.data.change + '%';

                        $change.text(changeText)
                            .removeClass('text-green-600 bg-green-100 text-red-600 bg-red-100 text-gray-600 bg-gray-100')
                            .addClass(
                                response.data.change > 0 ? 'text-green-600 bg-green-100' :
                                    response.data.change < 0 ? 'text-red-600 bg-red-100' :
                                        'text-gray-600 bg-gray-100'
                            );
                    }
                }
            },
            error: function () {
                $card.removeClass("loading").html(`
                <h5 class="text-gray-500 font-semibold">${title}</h5>
                <div class="convly-metric flex justify-between items-center mt-2.5">
                    <span class="convly-metric-value text-40 font-bold">-</span>
                    <span class="convly-metric-change text-lg font-semibold rounded-3xl px-3.5 py-1">N/A</span>
                </div>
            `);
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
        $target.removeClass("fade-in");

        if (isInitialLoad) {
            $target.html(`
            <div class="convly_skeleton h-10 w-44 mb-6"></div>
            <div class="flex items-center gap-x-7 mt-8 mb-9">
                <div class="convly_skeleton w-25 h-7"></div>
                <div class="convly_skeleton w-25 h-7"></div>
                <div class="convly_skeleton w-25 h-7"></div>
                <div class="convly_skeleton w-25 h-7"></div>
                <div class="convly_skeleton w-25 h-7"></div>
            </div>
            <div class="convly_skeleton h-87.5 w-full"></div>
        `);
        } else {
            $target.html(`<div class="convly_skeleton h-87.5 w-full"></div>`);
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
                    $target.addClass('fade-in');
                    $target.removeClass("loading");

                    if (isInitialLoad) {
                        $target.html(`
                        <h5 class="text-3xl font-bold">Main Page</h5>
                        
                        <div id="active-bg" 
                            class="absolute top-0 left-0 h-full rounded-3xl bg-gray-100 transition-all duration-300 -z-10">
                        </div>
                        
                        <div id="time-filter" 
                            class="text-base flex items-center gap-x-7.5 mt-6.5 font-medium text-gray-500 *:cursor-pointer">
                            <span class="item_filter item_filter_1" data-period="12_months">12 months</span>
                            <span class="item_filter item_filter_1" data-period="6_months">6 months</span>
                            <span class="item_filter item_filter_1" data-period="3_months">3 months</span>
                            <span class="item_filter item_filter_1" data-period="30_days">30 days</span>
                            <span class="item_filter item_filter_1 active" data-period="7_days">7 days</span>
                            <span class="item_filter item_filter_1" data-period="24_hours">24 hours</span>
                        </div>
                        
                        <div id="convly-chart" class="mt-5"></div>
                    `);

                        setupChartEventHandlers();

                        renderMainChart(response.data, $('#convly-chart')[0]);
                    } else {
                        $target.addClass('fade-in');
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
        $target.removeClass("fade-in");
        $target.attr('class', originalClasses);
        $target.html(originalHtml);
        showNotification(convly_ajax.i18n.error, 'error');
    }

    function setupChartEventHandlers() {
        $('.item_filter_1').off('click');
        $('.item_filter_1').on('click', function () {
            const period = $(this).data('period');
            loadMainChart(period, false);
        });

        const container = document.getElementById('time-filter');
        const bg = document.getElementById('active-bg');
        const buttons = container.querySelectorAll(".item_filter_1");

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
                type: 'category',
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

    // Load Top 5
    function loadTopPages(tab = 'pages', showSkeleton = true) {
        const $container = $('#top5-container');
        const $content = $('#top5-content');
        const $tabs = $('#top5-tabs');

        $content.removeClass('fade-in');

        if (showSkeleton) {
            $container.html(`
            <div class="rounded-xl bg-white p-6 relative" style="flex: 1">
                <div class="convly_skeleton h-10 w-44 mb-6"></div>
                <div class="flex items-center gap-x-7 mt-8 mb-9">
                    <div class="convly_skeleton w-25 h-7"></div>
                    <div class="convly_skeleton w-25 h-7"></div>
                    <div class="convly_skeleton w-25 h-7"></div>
                </div>
                <div class="convly_skeleton w-full" style="height: 90px"></div>
                <div class="convly_skeleton w-full" style="height: 90px; margin-top: 13px"></div>
                <div class="convly_skeleton w-full" style="height: 90px; margin-top: 13px"></div>
                <div class="convly_skeleton w-full" style="height: 90px; margin-top: 13px"></div>
                <div class="convly_skeleton w-full" style="height: 90px; margin-top: 13px"></div>
            </div>
        `);
        } else {
            $content.html(`
                <div class="convly_skeleton w-full" style="height: 90px"></div>
                <div class="convly_skeleton w-full" style="height: 90px; margin-top: 13px"></div>
                <div class="convly_skeleton w-full" style="height: 90px; margin-top: 13px"></div>
                <div class="convly_skeleton w-full" style="height: 90px; margin-top: 13px"></div>
                <div class="convly_skeleton w-full" style="height: 90px; margin-top: 13px"></div>
`);
        }

        $.ajax({
            url: convly_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'convly_get_top_pages',
                nonce: convly_ajax.nonce,
                tab: tab,
                limit: 5
            },
            success: function (response) {
                if (response.success) {
                    $container.addClass('fade-in');
                    renderTopPages(response.data, tab);
                } else {
                    showError('Failed to load top pages');
                    $content.html('<div class="no_page">No data available!</div>');
                    $content.addClass('fade-in');
                }
            },
            error: function (xhr, status, error) {
                console.error('Top pages load error:', error);
                showError('Network error occurred');
                $content.html('<div class="no_page">Load failed!</div>');
            }
        });
    }

    function renderTopPages(data, tab) {
        const $container = $('#top5-container');
        const $list = $('.convly-top5-list');
        const items = data.items || data.top_items || [];

        $container.html(`
        <div class="convly-top5-header">
            <h5 class="text-3xl font-bold">Top Fives</h5>
            
            <div id="active-bg-2" class="absolute top-0 left-0 h-full rounded-3xl bg-gray-100 transition-all duration-300 "></div>
            
            <div id="time-filter-2" class="text-base flex items-center gap-x-5 mt-6.5 font-medium text-gray-500">
                <span class="tab-button item_filter item_filter_2 ${tab === 'pages' ? 'active' : ''}" data-tab="pages">Pages</span>
                <span class="tab-button item_filter item_filter_2 ${tab === 'products' ? 'active' : ''}" data-tab="products">Products</span>
                <span class="tab-button item_filter item_filter_2 ${tab === 'posts' ? 'active' : ''}" data-tab="posts">Posts</span>
            </div>
        </div>
        
        <div id="top5-content" class="convly-top5-content">
            ${renderTopPagesList(items)}
        </div>
    `);

        setupTopPagesEvents();
    }

    function renderTopPagesList(items) {
        if (!items || items.length === 0) {
            return '<div class="no_page fade-in">No data available!</div>';
        }

        let html = '<div class="convly-top5-list fade-in">';

        items.forEach((item, index) => {
            const conversionRate = item.conversion_rate ? parseFloat(item.conversion_rate).toFixed(1) : '0.0';
            const rankClass = index === 0 ? 'rank-1' : index === 1 ? 'rank-2' : index === 2 ? 'rank-3' : '';

            html += `
            <div class="convly-top5-item ${rankClass}">
                <div class="convly-rank">${index + 1}</div>
                <div class="convly-content">
                    <div class="convly-title">${escapeHtml(item.post_title || item.page_title || 'Untitled')}</div>
                    <div class="convly-url">${escapeHtml(item.page_url || item.guid || '')}</div>
                </div>
                <div class="convly-stats">
                    <span class="convly-rate">${conversionRate}%</span>
                    <div class="convly-details">
                        <span>${item.unique_visitors || 0} visits</span>
                        <span>${item.total_clicks || 0} clicks</span>
                    </div>
                </div>
            </div>
        `;
        });

        html += '</div>';
        return html;
    }

    function setupTopPagesEvents() {
        $(document).off('click', '.tab-button').on('click', '.tab-button', function (e) {
            e.preventDefault();

            const tab = $(this).data('tab');
            if (!tab) return;

            loadTopPages(tab, false);
        });

        const container = document.getElementById('time-filter-2');
        const bg = document.getElementById('active-bg-2');
        const buttons = container.querySelectorAll(".item_filter_2");

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

    // Load pages list
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function loadPagesList(showSkeleton = true, currentPage) {
        const $table_section = $('#table-section');
        const $tbody = $('#convly_page_list');

        if (showSkeleton) {
            $table_section.html(`
            <div class="convly_skeleton h-10 w-44 mb-8"></div>
            <div class="flex items-center gap-x-7 mt-8 mb-20">
                <div class="convly_skeleton w-35 h-9"></div>
                <div class="convly_skeleton w-35 h-9"></div>
                <div class="convly_skeleton w-35 h-9"></div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-4 border-b border-gray-200">
                    ${Array(7).fill('<div class="convly_skeleton w-22 h-6"></div>').join('')}
                </div>
                <div class="space-y-5 divide-y divide-gray-200">
                    ${Array(4).fill(`
                        <div class="flex justify-between items-center py-4">
                            ${Array(7).fill('<div class="convly_skeleton h-7 w-29"></div>').join('')}
                        </div>
                    `).join('')}
                </div>
            </div>
        `);
        } else {
            $tbody.html(`
            <tr>
                <td colspan="7">
                    <div class="space-y-4 py-8">
                        ${Array(5).fill(`
                            <div class="flex justify-between items-center py-4">
                                ${Array(7).fill('<div class="convly_skeleton h-7 w-29"></div>').join('')}
                            </div>
                        `).join('')}
                    </div>
                </td>
            </tr>
        `);
        }

        const searchTerm = $('#page-search-input').val() ? $('#page-search-input').val().trim() : '';

        const requestData = {
            action: 'convly_get_page_list',
            nonce: convly_ajax.nonce,
            tab: currentTab,
            page: currentPage,
            per_page: $('#convly-per-page').val() || 10,
            sort_by: $('#convly-sort-by').val(),
            date_filter: $('#convly-date-filter').val(),
            search: searchTerm
        };

        if ($('#convly-date-filter').val() === 'custom') {
            requestData.date_from = $('#convly-date-from').val();
            requestData.date_to = $('#convly-date-to').val();
        }

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: requestData,
            success: function (response) {
                if (response.success) {
                    if (showSkeleton) {
                        renderFullTable(response.data);
                    } else {
                        renderPagesList(response.data);
                    }
                } else {
                    showError(response.data || 'Failed to load data');
                }
            },
            error: function () {
                showError(convly_ajax.i18n.error);
            }
        });
    }

    function renderFullTable(data) {
        const $table_section = $('#table-section');
        $table_section.addClass('fade-in');

        $table_section.html(`
        <div class="flex items-center justify-between">
            <h5 class="text-3xl font-bold">All Pages</h5>
            <div class="text-base font-semibold">
                <button id="convly-export-pdf" class="px-5 py-2.5 border border-black/20 rounded-xl mr-4 cursor-pointer convly-export-btn">Export Report</button>
                <button id="convly-sync-pages" class="px-5 py-2.5 border border-black/20 rounded-xl cursor-pointer convly-sync-btn">
                <span class="dashicons dashicons-update convly-spin" style="vertical-align: middle;"></span> Sync Pages</button>
            </div>
        </div>
        
        <div id="tabs" class="flex gap-x-3 mt-15 border-b border-gray3 text-base font-semibold">
            <button class="convly-tab tab-btn active-tab" data-tab="pages">Pages</button>
            <button class="convly-tab tab-btn" data-tab="products">Products</button>
            <button class="convly-tab tab-btn" data-tab="posts">Posts</button>
            <div class="convly-custom-tabs"></div>
            <button class="convly-add-tab tab-btn">+</button>
        </div>

        <!-- Filters -->
                <div class="flex items-center gap-10 mt-7">

        <div class="convly-search-container">
            <input type="text" id="page-search-input" placeholder="Search Page Name ..." class="convly_input" />
            <div class="convly-search-spinner">
                <div class="convly-spinner-circle"></div>
            </div>
        </div>

                    <div class="flex items-center space-x-2" style="position: relative">
                        <span class="text-gray-500 font-medium text-sm">Date Range:</span>
                        <select id="convly-date-filter"
                            class="convly_slect">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="7_days">Last 7 Days</option>
                            <option value="30_days">Last 30 Days</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    <div class="convly-custom-date-range" style="display:none;">
                        <input type="date" id="convly-date-from" class="convly_slect"/>
                        <span>To</span>
                        <input type="date" id="convly-date-to" class="convly_slect"/>
                    </div>
                    </div>

                    <div class="convly-filter-group flex items-center space-x-2">
                        <span class="text-gray-500 font-medium text-sm">Sort By:</span>
                        <select id="convly-sort-by" class="convly_slect">
                            <option value="views_desc">Views (High to Low)</option>
                            <option value="conversion_rate_desc">Conversion Rate (High to Low)</option>
                            <option value="clicks_desc">Clicks (High to Low)</option>
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                        </select>
                    </div>
                    
                    <div class="convly-filter-group flex items-center space-x-2">
                        <span class="text-gray-500 font-medium text-sm">Item Per Page:</span>
                        <select id="convly-per-page" class="convly_slect">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select>
                    </div>
                </div>
       
        <table class="w-full table-auto divide-y divide-gray-200 mt-8">
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
        
        <div class="convly-pagination">
            <div id="convly-page-info"></div>
            <div id="convly-page-links" class="page-links"></div>
        </div>

    `);

        $('#convly-date-filter').val(data.date_filter || 'all');
        $('#convly-sort-by').val(data.sort_by || 'views_desc');
        $('#convly-per-page').val(data.per_page || '10');

        renderPagesList(data);
        bindTableEvents();
    }

    function bindTableEvents() {
        // Add custom tab
        $('.convly-add-tab').on('click', function (e) {
            e.preventDefault();
            const tab_modal = document.getElementById('convly-custom-tab-modal');
            tab_modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        // Custom tab form submission
        $('#convly-custom-tab-form').on('submit', function (e) {
            e.preventDefault();
            addCustomTab();
        });

        $('#convly-date-filter').change(function () {
            if ($(this).val() === 'custom') {
                $('.convly-custom-date-range').show();
            } else {
                $('.convly-custom-date-range').hide();
                currentPage = 1;
                loadPagesList(false, currentPage);
            }
        });

        // Custom date range
        $('#convly-date-from, #convly-date-to').on('change', function () {
            if ($('#convly-date-from').val() && $('#convly-date-to').val()) {
                loadPagesList(false);
            }
        });

        $('#convly-sort-by').change(function () {
            currentPage = 1;
            loadPagesList(false, currentPage);
        });

        $('#convly-per-page').change(function () {
            currentPage = 1;
            loadPagesList(false, currentPage);
        });

        // Search input
        const searchInput = $('#page-search-input');
        if (searchInput.length > 0) {
            searchInput.off('input').on('input', debounce(function (e) {

                const searchSpinner = $('.convly-search-spinner')

                const searchTerm = searchInput.val().trim();

                if (searchTerm.length === 0 || searchTerm.length >= 3) {
                    currentPage = 1;
                    searchSpinner.addClass('searching');
                    loadPagesList(false, currentPage);
                }

                setTimeout(() => {
                    searchSpinner.removeClass('searching');
                }, 600);
            }, 500));
        }

        // Export PDF
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

            const dateRange = $('#convly-pdf-date-range').val();
            const tab = $('.convly-tab.active-tab').data('tab') || 'pages';

            const form = $('<form>', {
                method: 'POST',
                action: convly_ajax.ajax_url
            });

            const params = {
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

        // Sync pages with WordPress
        $('#convly-sync-pages').on('click', function () {
            const $button = $(this);
            if (!$button.data('original')) {
                $button.data('original', $button.html());
            }
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
                        loadPagesList(false);
                        loadTopPages('pages', false);
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
                    $button.html($button.data('original'));
                }
            });
        });

        // Tab click handling
        function setupTabClickHandling() {
            $(document).off('click', '.convly-tab').on('click', '.convly-tab', function (e) {
                e.preventDefault();

                $('.convly-tab').removeClass('active-tab');
                $(this).addClass('active-tab');

                const tabSlug = $(this).data('tab');
                currentTab = tabSlug;
                currentPage = 1;

                loadPagesList(false, currentPage);
            });
        }

        // Initialize all tab functionality
        function initCustomTabs() {
            loadCustomTabs();
            setupTabDeletion();
            setupTabClickHandling();
        }

        // On document ready
        $(document).ready(function () {
            initCustomTabs();

            // Enter key support in input
            $('#convly-tab-name').on('keypress', function (e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    addCustomTab();
                }
            });
        });

        //toggle label input position
        const inputs = document.querySelectorAll('.convly_custom_input');

        inputs.forEach(input => {
            const parent = input.closest('.convly_custom_input_box');

            input.addEventListener('input', () => {
                if (input.value !== '') {
                    parent.classList.add('not-empty');
                } else {
                    parent.classList.remove('not-empty');
                }
            });

            if (input.value === '') {
                parent.classList.remove('not-empty');
            } else {
                parent.classList.add('not-empty');
            }
        });

        $(document).on('click', '.convly-manage-tab', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var tabSlug = $(this).data('tab-slug');
            var tabName = $(this).data('tab-name');

            $('#convly-current-tab-name').text(tabName);
            $('#convly-manage-tab-modal').data('tab-slug', tabSlug);
            $('#convly-manage-tab-modal').css('display', 'flex');
            document.body.style.overflow = 'hidden';

            // Load available items
            loadAvailableItems('pages');
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
                            options += '<option value="' + item.page_id + '">' + item.page_title + '</option>';
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
                            html += '<tr class="*:py-6 fade-in">';
                            html += '<td>' + item.page_title + '</td>';
                            html += '<td style="width: 0px" "><button class="convly_badge convly-remove-from-tab" data-page-id="' + item.page_id + '">Remove</button></td>';
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
            let $button = $('#convly-add-to-tab');
            let originalText = $button.html();
            $button.html(`<span class="spinner_tab"></span> Adding...`).prop('disabled', true);

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
                        $button.html('Added!');

                        setTimeout(function() {
                            $button.html(originalText).prop('disabled', false);
                        }, 1500);

                        loadTabItems(tabSlug);
                        loadPagesList(false);
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
                        loadPagesList(false);
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        });
    }

    function renderPagesList(data) {
        const $tbody = $('#convly_page_list');
        const $pagination = $('.convly-pagination');
        $tbody.empty();

        if (!data.items || data.items.length === 0) {
            $tbody.html('<tr><td colspan="7" class="no_page text-gray-500 fade-in">No pages found!</td></tr>');
            $pagination.hide();
            return;
        }

        data.items.forEach(function (page) {
            const conversionRate = page.unique_visitors > 0 ?
                ((page.total_clicks / page.unique_visitors) * 100).toFixed(1) : '0.0';

            const conversionClass = conversionRate >= 50 ? 'high' :
                conversionRate >= 20 ? 'medium' : 'low';

            const row = `
            <tr class="*:py-6 fade-in">
                <td class="column-status">
                    <label class="convly-status-toggle relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" ${page.is_active == 1 ? 'checked' : ''} 
                               data-page-id="${page.page_id}" onchange="togglePageStatus(this)"/>
                        <span class="group peer bg-white rounded-full duration-300 w-13 h-6 ring-2 ring-red-500 after:duration-300 after:bg-red-500 peer-checked:after:bg-green-500 peer-checked:ring-green-500 after:rounded-full after:absolute after:h-4 after:w-4 after:top-1 after:left-1 peer-checked:after:translate-x-7">
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
                page.total_clicks :
                `<button class="cursor-pointer px-3 py-2.5 border border-gray4 rounded-10px convly-add-button" data-page-id="${page.page_id}">Add Button</button>`
            }
                </td>
                <td class="column-rate">
                    <span class="text-lg font-semibold rounded-3xl px-4 py-1 ${conversionClass}">
                        ${conversionRate}%    
                    </span>
                </td>
                <td class="column-actions">
                    <a href="?page=convly-page-details&page_id=${page.page_id}" class="convly_badge">
                        ${convly_ajax.i18n.details}
                    </a>
                </td>
            </tr>
        `;

            $tbody.append(row);
        });

        $pagination.show();
        renderPagination(data.current_page, data.total_pages, data.per_page, data.total);
        bindPageListEvents();
    }

    function renderPagination(currentPage, totalPages, perPage, total) {
        const $pagination = $('#convly-page-links');
        $pagination.empty();

        $pagination.append(`
        <button class="page-btn prev ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>‹</button>
    `);

        let pages = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) pages.push(i);
        } else {
            if (currentPage <= 4) {
                pages = [1, 2, 3, 4, 5, '...', totalPages];
            } else if (currentPage >= totalPages - 3) {
                pages = [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
            } else {
                pages = [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages];
            }
        }

        pages.forEach(p => {
            if (p === '...') {
                $pagination.append(`<span class="dots">...</span>`);
            } else {
                $pagination.append(`
                <button class="page-btn ${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>
            `);
            }
        });

        $pagination.append(`
        <button class="page-btn next ${currentPage === totalPages ? 'disabled' : ''}" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>›</button>
    `);

        const $info = $('#convly-page-info');
        $info.empty();

        let start = ((currentPage - 1) * perPage) + 1
        let end = (currentPage * perPage)

        if(end > total) end = total;

        $info.html(`
        <span class="convly-page-info">Results: ${start} - ${end} of ${total}</span>
        `);

        $('#convly-page-links button[data-page]').off('click').on('click', function () {
            const page = parseInt($(this).data('page'));
            if (!isNaN(page) && page >= 1 && page <= totalPages) {
                currentPage = page;

                loadPagesList(false, currentPage);
            }
        });
    }

    function showError(message) {
        if (typeof showNotification === 'function') {
            showNotification(message, 'error');
        } else {
            alert('Error: ' + message);
        }
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

        const tab_modal = document.getElementById('convly-button-modal');
        tab_modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        $('#convly-button-modal').show();
    }

    // Handle button form submission
    $('#convly-button-form').on('submit', function (e) {
        e.preventDefault();

        const inputs = document.querySelectorAll('.convly_custom_input');

        const $button = $('#convly_save_button');
        $button.html(`<span class="spinner_tab"></span> Adding...`).prop('disabled', true);

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
                    $('.convly_custom_input_box').removeClass('not-empty');
                    inputs.value = '';
                    loadPagesList(false);
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
        const tabName = $('#convly-tab-name').val().trim();
        const inputs = document.querySelectorAll('.convly_custom_input');

        // Validation
        if (!tabName) {
            showNotification('Please enter a tab name', 'error');
            return;
        }

        // Show loading state
        const $submitBtn = $('#convly-tab-submit');
        const originalText = $submitBtn.html();
        $submitBtn.html('<span class="spinner_tab"></span> Adding...').prop('disabled', true);

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
                    $('#convly-tab-name').val(''); // Clear input
                    $('.convly_custom_input_box').removeClass('not-empty');
                    inputs.value = '';

                    // Add new tab to UI
                    renderCustomTab(response.data);
                    showNotification('Custom tab added successfully', 'success');
                    document.body.style.overflow = '';

                    // Trigger click on new tab
                    $(`.convly-tab[data-tab="${response.data.tab_slug}"]`).trigger('click');
                } else {
                    showNotification(response.data || 'Failed to add tab', 'error');
                    document.body.style.overflow = '';
                }
            },
            error: function (xhr, status, error) {
                showNotification('Network error: ' + error, 'error');
                document.body.style.overflow = '';
            },
            complete: function () {
                // Restore button state
                $submitBtn.html(originalText).prop('disabled', false);
                document.body.style.overflow = '';
            }
        });
    }

    // Load custom tabs
    function loadCustomTabs() {
        const $container = $('.convly-custom-tabs');
        $container.addClass('loading');

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'GET', // Changed to GET for better caching
            data: {
                action: 'convly_get_custom_tabs',
                nonce: convly_ajax.nonce
            },
            success: function (response) {
                if (response.success && response.data.length > 0) {
                    renderCustomTabs(response.data);
                } else {
                    $container.hide(); // Hide if no tabs
                }
            },
            error: function () {
                showNotification('Failed to load custom tabs', 'error');
            },
            complete: function () {
                $container.removeClass('loading');
            }
        });
    }

    // Render single tab
    function renderCustomTab(tab) {
        const tabHtml = `
        <div class="convly-tab-item" data-tab-slug="${tab.tab_slug}">
            <button class="tab-btn convly-tab" data-tab="${tab.tab_slug}">
                ${escapeHtml(tab.tab_name)}
            </button>
            <div class="convly-tab-actions">
                <button class="convly-manage-tab" data-tab-slug="${tab.tab_slug}" 
                        data-tab-name="${tab.tab_name}" title="Manage">
                    <span class="dashicons dashicons-admin-generic"></span>
                </button>
                <button class="convly-delete-tab" data-tab-id="${tab.id}" 
                        data-tab-slug="${tab.tab_slug}" title="Delete">
                    <span class="dashicons dashicons-no"></span>
                </button>
            </div>
        </div>
    `;

        $('.convly-custom-tabs').append(tabHtml).show();
    }

    // Render all tabs
    function renderCustomTabs(tabs) {
        const $container = $('.convly-custom-tabs');
        let tabsHtml = '';

        tabs.forEach(function (tab) {
            tabsHtml += `
            <div class="convly-tab-item" data-tab-slug="${tab.tab_slug}">
                <button class="tab-btn convly-tab" data-tab="${tab.tab_slug}">
                    ${escapeHtml(tab.tab_name)}
                </button>
                <div class="convly-tab-actions">
                    <button class="convly-manage-tab" data-tab-slug="${tab.tab_slug}" 
                            data-tab-name="${tab.tab_name}" title="Manage">
                        <span class="dashicons dashicons-admin-generic"></span>
                    </button>
                    <button class="convly-delete-tab" data-tab-id="${tab.id}" 
                            data-tab-slug="${tab.tab_slug}" title="Delete">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
            </div>
        `;
        });

        $container.html(tabsHtml).show();
    }

    // Delete tab
    function setupTabDeletion() {
        $(document).off('click', '.convly-delete-tab').on('click', '.convly-delete-tab', function (e) {
            e.stopPropagation();
            e.preventDefault();

            const $button = $(this);
            const tabId = $button.data('tab-id');
            const tabSlug = $button.data('tab-slug');
            const tabName = $button.closest('.convly-tab-item').find('.convly-tab').text();

            if (confirm(`Are you sure you want to delete the tab "${tabName}"?`)) {
                // Show loading state
                $button.html('<span class="spinner_tab"></span>').prop('disabled', true);

                $.ajax({
                    url: convly_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'convly_delete_custom_tab',
                        nonce: convly_ajax.nonce,
                        tab_id: tabId,
                        tab_slug: tabSlug
                    },
                    success: function (response) {
                        if (response.success) {
                            // Remove tab from UI
                            $(`.convly-tab-item[data-tab-slug="${tabSlug}"]`).remove();

                            // Hide container if no tabs left
                            if ($('.convly-tab-item').length === 0) {
                                $('.convly-custom-tabs').hide();
                            }

                            // Switch to first available tab
                            const $firstTab = $('.convly-tab').first();
                            if ($firstTab.length) {
                                $firstTab.trigger('click');
                            }

                            showNotification('Tab deleted successfully', 'success');
                        } else {
                            showNotification(response.data || 'Failed to delete tab', 'error');
                        }
                    },
                    error: function () {
                        showNotification('Network error occurred', 'error');
                    },
                    complete: function () {
                        $button.html('<span class="dashicons dashicons-no"></span>').prop('disabled', false);
                    }
                });
            }
        });
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