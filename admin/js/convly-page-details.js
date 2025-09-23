/**
 * Convly Page Details JavaScript
 */

(function ($) {
    'use strict';

    // Global variables
    let viewsChart = null;
    let buttonCharts = {};
    const pageId = window.convlyPageId || 0;

    // Initialize
    $(document).ready(function () {
        if (!pageId) {
            return;
        }

        // Load initial data
        loadPageStats();
        loadViewsChart('7_days', true);
        loadButtons();

        // Event handlers
        bindEventHandlers();
    });

    // Bind event handlers
    function bindEventHandlers() {
        // Button form submission
        $('#convly-button-form').on('submit', function (e) {
            e.preventDefault();
            saveButton();
        });

        // Modal close
        $('.convly-modal-close, .convly-modal-cancel').on('click', function () {
            $('.convly-modal').hide();
            document.body.style.overflow = '';
        });

        // Close modal when clicking outside
        $('.convly-modal').on('click', function (e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
    }

// Export PDF button
    $('#convly-export-page-pdf').on('click', function (e) {
        e.preventDefault();
        $('#convly-page-pdf-modal').show();
    });

    // PDF date range selection
    $('#convly-page-pdf-range').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#convly-page-pdf-custom-dates').show();
        } else {
            $('#convly-page-pdf-custom-dates').hide();
        }
    });

    // PDF export form submission
    $('#convly-page-pdf-form').on('submit', function (e) {
        e.preventDefault();
        exportPagePDF();
    });

    // Load page statistics
    function loadPageStats() {
        // Load each metric card
        $('.convly-card-datails').each(function () {
            const metric = $(this).data('metric');
            loadMetricCard(metric,'7_days', true);
        });
    }

    // Load individual metric card
    function loadMetricCard(metric, period, isInitialLoad) {
        const $card = $(`.convly-card-datails[data-metric="${metric}"]`);
        const $target = isInitialLoad ? $card : $('.convly-metric');

        const $card_2 = $('.convly-scroll-breakdown');
        const $card_3 = $('.convly-device-breakdown');
        const $target_2 = isInitialLoad ? $card_2 : $card_2.find('.breakdown-content');
        const $target_3 = isInitialLoad ? $card_3 : $card_3.find('.breakdown-content');

        const titles = {
            'page_views': 'Page Views',
            'unique_visitors': 'Unique Visitors',
            'conversion_rate': 'Conversion Rate',
            'scroll_depth': 'Scroll Depth',
        };

        const title = titles[metric] || metric;
        $target.removeClass("fade-in");

        if (isInitialLoad) {
            $target.html(`
        <div class="convly_skeleton h-7 w-44 mb-4"></div>
        <div class="flex justify-between items-center mt-2.5">
            <span class="convly_skeleton w-22 h-10"></span>
            <span class="convly_skeleton h-10 w-20"></span>
        </div>
            `);

            $target_2.html(`
                    <div class="convly_skeleton h-7 w-44 mb-4"></div>
            <div class="convly_skeleton w-full" style="height: 230px"></div>

            `);

            $target_3.html(`
                    <div class="convly_skeleton h-7 w-44 mb-4"></div>
            <div class="convly_skeleton w-full" style="height: 230px;"></div>

            `);
        } else {
            $target.html(`
            <span class="convly_skeleton w-22 h-10" style="margin-top: 10px"></span>
            <span class="convly_skeleton h-10 w-20" style="margin-top: 10px"></span>
            `);

            $target_2.html(`
            <div class="convly_skeleton w-full" style="height: 230px;"></div>
            `);

            $target_3.html(`
            <div class="convly_skeleton w-full" style="height: 230px;"></div>
            `);
        }

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_page_stats',
                nonce: convly_ajax.nonce,
                page_id: pageId,
                metric: metric,
                period: period
            },
            success: function (response) {
                $target.addClass('fade-in');
                if (response.success) {
                    let displayValue = response.data.value;

                    $card.removeClass("loading").html(`
                    <h5 class="text-gray-500 font-semibold" style="font-size: 18px">${title}</h5>
                    <div class="convly-metric flex justify-between items-center mt-2.5">
                        <span class="convly-metric-value font-bold" style="font-size: 28px">${displayValue}</span>
                        <span class="convly-metric-change text-lg font-semibold rounded-3xl px-3.5 py-1"></span>
                    </div>
                `);
                    const $change = $card.find('.convly-metric-change');

                    // Device breakdown for page views
                    if (metric === 'page_views' && response.data.device_breakdown) {
                        $card_3.html(`
                                    <h5 class="text-gray-500 font-semibold" style="font-size: 18px">Device Type</h5>
                                    <div class="breakdown-content mt-2.5" id="chart-type" style="display: flex; justify-content: center; align-items: center;"></div>
                        `)

                        const mobile = response.data.device_breakdown.mobile;
                        const desktop = response.data.device_breakdown.desktop;

                        var options = {
                            series: [mobile, desktop],
                            chart: {
                                width: 350,
                                type: 'donut',
                            },
                            labels: [
                                'Mobile',
                                'Desktop',
                            ],
                            dataLabels: {
                                enabled: false
                            },
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: 200
                                    },
                                    legend: {
                                        show: false
                                    }
                                }
                            }],
                            legend: {
                                position: 'right',
                                offsetY: 0,
                                height: 230,
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#chart-type"), options);
                        chart.render();
                    }

                    // Scroll depth breakdown
                    if (metric === 'scroll_depth' && response.data.breakdown) {
                       $card_2.html(`
                                    <h5 class="text-gray-500 font-semibold" style="font-size: 18px">Scroll Depth Level</h5>
                                   <div class="breakdown-content mt-2.5" id="chart-depth"></div>
                       `)

                        const b = response.data.breakdown;
                        const c = response.data.value;

                        var options = {
                            series: [{
                                name: "Scroll Depth",
                                data: [
                                    {x: '25%', y: b['25']},
                                    {x: '50%', y: b['50']},
                                    {x: '75%', y: b['75']},
                                    {x: '100%', y: b['100']},
                                    {x: 'ave', y: c}
                                ]
                            }],
                            dataLabels: {
                                enabled: false
                            },
                            chart: {
                                type: 'bar',
                                height: 230,
                                toolbar: {
                                    show: false
                                },
                            },
                            xaxis: {
                                categories: ['25% Scroll Depth', '50% Scroll Depth', '75% Scroll Depth', '100% Scroll Depth', 'Average Scroll Depth'],
                                type: 'category'
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#chart-depth"), options);
                        chart.render();

                    }

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
                $value.text('-');
            }
        });
    }

    // Load views chart
    function loadViewsChart(period, isInitialLoad = true) {
        const $target = isInitialLoad ? $('#convly-view-chart-container') : $('#convly-view-chart');

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
                action: 'convly_get_page_chart_data',
                nonce: convly_ajax.nonce,
                page_id: pageId,
                chart_type: 'all_metrics',
                period: period
            },
            success: function (response) {
                if (response.success) {
                    $target.addClass('fade-in');
                    $target.removeClass("loading");

                    if (isInitialLoad) {
                        $target.html(`
                        <h5 class="text-3xl font-bold">Page Views</h5>
                        
                        <div id="active-bg-3" 
                            class="absolute top-0 left-0 h-full rounded-3xl bg-gray-100 transition-all duration-300 -z-10">
                        </div>
                        
                        <div id="time-filter" 
                            class="text-base flex items-center mt-6.5 font-medium text-gray-500 *:cursor-pointer" style="column-gap: 15px">
                            <span class="item_filter item_filter_3" data-period="12_months">12 months</span>
                            <span class="item_filter item_filter_3" data-period="6_months">6 months</span>
                            <span class="item_filter item_filter_3" data-period="3_months">3 months</span>
                            <span class="item_filter item_filter_3" data-period="30_days">30 days</span>
                            <span class="item_filter item_filter_3 active" data-period="7_days">7 days</span>
                            <span class="item_filter item_filter_3" data-period="24_hours">24 hours</span>
                        </div>
                        
                        <div id="convly-view-chart" class="mt-5"></div>
                    `);

                        setupViewChartEventHandlers();

                        renderViewsChart(response.data);
                    } else {
                        $target.addClass('fade-in');
                        renderViewsChart(response.data);
                    }
                }
            }
        });
    }

    function setupViewChartEventHandlers() {
        $('.item_filter_3').off('click');
        $('.item_filter_3').on('click', function () {
            const period = $(this).data('period');
            loadViewsChart(period, false);

            $('.convly-card-datails').each(function () {
                const metric = $(this).data('metric');
                loadMetricCard(metric, period, false);
            });
        });

        const container = document.getElementById('time-filter');
        const bg = document.getElementById('active-bg-3');
        const buttons = container.querySelectorAll(".item_filter_3");

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

    // Render views chart
    function renderViewsChart(data) {
        const chartElement = document.querySelector('#convly-view-chart');

        if (window.convlyChart) {
            window.convlyChart.destroy();
        }

        const options = {
            series: [{
                name: 'Views',
                data: data.views || []
            },{
                name: 'Visitors',
                data: data.visitors || []
            },{
                name: 'Conversion Rate',
                data: data.conversion_rates || []
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

    // Load buttons
    function loadButtons(isInitialLoad = true) {
        const $target = $('.convly-buttons-section');
        const $target_chart = $('.convly-button-charts')

        if (isInitialLoad) {
            $target.html(`
                    <div class="convly_skeleton h-10 w-44 mb-8"></div>

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

            $target_chart.html(`
                <div class="rounded-xl bg-white" style="padding: 22px; margin-top: 22px">
                    <div class="convly_skeleton h-7 w-44 mb-4"></div>
                    <div class="convly_skeleton w-full" style="height: 230px;"></div>
                </div>             
                <div class="rounded-xl bg-white" style="padding: 22px; margin-top: 22px">
                    <div class="convly_skeleton h-7 w-44 mb-4"></div>
                    <div class="convly_skeleton w-full" style="height: 230px;"></div>
                </div>                
                <div class="rounded-xl bg-white" style="padding: 22px; margin-top: 22px">
                    <div class="convly_skeleton h-7 w-44 mb-4"></div>
                    <div class="convly_skeleton w-full" style="height: 230px;"></div>
                </div>
            `)
        }


        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_page_buttons',
                nonce: convly_ajax.nonce,
                page_id: pageId
            },
            success: function (response) {
                if (response.success) {
                    $target.html(`
                            <div class="flex items-center justify-between">
            <h5 class="text-3xl font-bold">Tracked Buttons</h5>
            <div class="text-base font-semibold">
                <button id="convly-export-pdf"
                        class="convly-add-button px-5 py-2.5 border border-black/20 rounded-xl mr-4 cursor-pointer convly-export-btn"
                        data-page-id="${pageId}">+ Add Button
                </button>
            </div>
        </div>

        <div class="convly-buttons-list" id="convly-buttons-list">

        </div>
                    `);

                    renderButtons(response.data);
                    loadButtonCharts(response.data);
                    bindButtonHandlers();
                }
            }
        });
    }

    function bindButtonHandlers() {
        // Add button
        $('.convly-add-button').on('click', function () {
            openButtonModal(false);
        });
    }

    // Render buttons list
    function renderButtons(buttons) {
        const $list = $('#convly-buttons-list');

        if (buttons.length === 0) {
            $list.html('<p>' + convly_ajax.i18n.no_buttons + '</p>');
            return;
        }

        let html = '<table class="w-full divide-y divide-gray-200 mt-8" style="table-layout: fixed">';
        html += '<thead class="text-sm text-left"><tr class="*:font-medium *:py-4 text-gray-500">';
        html += '<th style="width: 33%">' + convly_ajax.i18n.button_name + '</th>';
        html += '<th>' + convly_ajax.i18n.css_id + '</th>';
        html += '<th>' + convly_ajax.i18n.type + '</th>';
        html += '<th>' + convly_ajax.i18n.clicks + '</th>';
        html += '<th>' + convly_ajax.i18n.actions + '</th>';
        html += '</tr></thead><tbody class="text-base font-semibold divide-y divide-gray-200">';

        buttons.forEach(function (button) {
            html += '<tr class="*:py-6 fade-in">';
            html += '<td><strong>' + button.button_name + '</strong></td>';
            html += '<td><code>#' + button.button_css_id + '</code></td>';
            html += '<td>' + button.button_type + '</td>';
            html += '<td>' + button.total_clicks + '</td>';
            html += '<td>';
            html += '<a class="convly_badge convly-edit-button" data-button-id="' + button.id + '">' + convly_ajax.i18n.edit + '</a> ';
            html += '<a class="convly_badge convly-delete-button" data-button-id="' + button.id + '">' + convly_ajax.i18n.delete + '</a>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $list.html(html);

        // Bind button actions
        $('.convly-edit-button').on('click', function () {
            const buttonId = $(this).data('button-id');
            const button = buttons.find(b => b.id == buttonId);
            openButtonModal(button);
        });

        $('.convly-delete-button').on('click', function () {
            if (confirm(convly_ajax.i18n.confirm_delete)) {
                deleteButton($(this).data('button-id'));
            }
        });
    }

    // Load button charts
    function loadButtonCharts(buttons) {
        const $container = $('#convly-button-charts');
        $container.empty();

        if (buttons.length === 0) {
            return;
        }

        buttons.forEach(function (button) {
            // Create chart container
            const chartHtml = `
                <div class="rounded-xl bg-white" style="padding: 22px; margin-top: 22px" data-button-id="${button.id}">
                    <div style="display: flex; align-items: center; justify-content: space-between">
                        <h5>${button.button_name} - ${convly_ajax.i18n.clicks}</h5>
<!--                        <div class="convly-chart-period-selector">-->
<!--                            <button class="convly-button-period-btn active" data-period="7_days" data-button-id="${button.id}">7 days</button>-->
<!--                            <button class="convly-button-period-btn" data-period="30_days" data-button-id="${button.id}">30 days</button>-->
<!--                            <button class="convly-button-period-btn" data-period="3_months" data-button-id="${button.id}">3 months</button>-->
<!--                        </div>-->
                    </div>
                    <div class="convly-button-chart" id="convly-button-chart-${button.id}">

                    </div>
                </div>
            `;

            $container.append(chartHtml);

            // Load chart data
            loadButtonChart(button.id, '7_days');
        });

        // Bind period selector events
        $('.convly-button-period-btn').on('click', function () {
            const buttonId = $(this).data('button-id');
            const period = $(this).data('period');

            // Update active state
            $(`.convly-button-period-btn[data-button-id="${buttonId}"]`).removeClass('active');
            $(this).addClass('active');

            // Reload chart
            loadButtonChart(buttonId, period);
        });
    }

    // Load individual button chart
    function loadButtonChart(buttonId, period) {
        const $chart = $('#convly-button-chart-' + buttonId);
        const original = $chart.html();

        $chart.html(`
         <div class="convly_skeleton w-full" style="height: 230px;"></div>
        `)

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_button_chart_data',
                nonce: convly_ajax.nonce,
                button_id: buttonId,
                period: period
            },
            success: function (response) {
                if (response.success) {
                    $chart.html(original);
                    renderButtonChart(buttonId, response.data);
                } else {
                    $chart.html(`<div class="text-red-500 p-4">Error loading chart</div>`);
                }
            },
            error: function () {
                $chart.html(`<div class="text-red-500 p-4">Request failed</div>`);
            }
        });
    }

    // Render button chart
    function renderButtonChart(buttonId, data) {
        const chart = document.getElementById('convly-button-chart-' + buttonId);

        if (window.convlyCharts && window.convlyCharts[buttonId]) {
            window.convlyCharts[buttonId].destroy();
        }

        const options = {
            series: [{
                name: 'Clicks',
                data: data.values || []
            }],
            chart: {
                height: 230,
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

        window.convlyChart = new ApexCharts(chart, options);
        window.convlyChart.render();
    }

    // Open button modal
    function openButtonModal(button = null) {
        if (button) {
            $('#convly-button-modal-title').text(convly_ajax.i18n.edit_button);
            $('#convly-button-id').val(button.id);
            $('#convly-button-css-id').val(button.button_css_id);
            $('#convly-button-name').val(button.button_name);
            $('#convly-button-type').val(button.button_type);
        } else {
            $('#convly-button-modal-title').text(convly_ajax.i18n.add_button);
            $('#convly-button-form')[0].reset();
            $('#convly-page-id').val(pageId);
        }

        $('#convly-button-modal').css('display', 'flex');
    }

    // Save button
    function saveButton() {
        const buttonId = $('#convly-button-id').val();
        const action = buttonId ? 'convly_update_button' : 'convly_add_button';

        const data = {
            action: action,
            nonce: convly_ajax.nonce,
            page_id: pageId,
            button_id: buttonId,
            button_css_id: $('#convly-button-css-id').val(),
            button_name: $('#convly-button-name').val(),
            button_type: $('#convly-button-type').val()
        };

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    $('#convly-button-modal').hide();
                    loadButtons(false);
                    showNotification('Button saved successfully', 'success');
                } else {
                    showNotification(response.data, 'error');
                }
            }
        });
    }

    // Delete button
    function deleteButton(buttonId) {
        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_delete_button',
                nonce: convly_ajax.nonce,
                button_id: buttonId
            },
            success: function (response) {
                if (response.success) {
                    loadButtons(false);
                    showNotification('Button deleted successfully', 'success');
                } else {
                    showNotification(response.data, 'error');
                }
            }
        });
    }

    // Show notification
    function showNotification(message, type = 'info') {
        const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
        $('.convly-page-details').prepend($notice);

        setTimeout(function () {
            $notice.fadeOut(function () {
                $(this).remove();
            });
        }, 3000);
    }

// Export page PDF
    function exportPagePDF() {
        const dateRange = $('#convly-page-pdf-range').val();

        let url = convly_ajax.ajax_url + '?action=convly_generate_page_pdf';
        url += '&nonce=' + convly_ajax.nonce;
        url += '&page_id=' + pageId;
        url += '&date_filter=' + dateRange;

        if (dateRange === 'custom') {
            const dateFrom = $('#convly-page-pdf-date-from').val();
            const dateTo = $('#convly-page-pdf-date-to').val();

            if (!dateFrom || !dateTo) {
                alert('Please select both start and end dates');
                return;
            }

            url += '&date_from=' + dateFrom;
            url += '&date_to=' + dateTo;
        }

        $('#convly-page-pdf-modal').hide();

        // باز کردن در تب جدید
        window.open(url, '_blank');
    }

})(jQuery);