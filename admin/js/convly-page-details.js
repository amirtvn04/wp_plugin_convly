/**
 * Convly Page Details JavaScript
 */

(function($) {
    'use strict';

    // Global variables
    let viewsChart = null;
    let buttonCharts = {};
    const pageId = window.convlyPageId || 0;

    // Initialize
    $(document).ready(function() {
        if (!pageId) {
            return;
        }

        // Load initial data
        loadPageStats();
        loadViewsChart('7_days');
        loadButtons();

        // Event handlers
        bindEventHandlers();
    });

    // Bind event handlers
    function bindEventHandlers() {
        // Chart period buttons
        $('.convly-period-btn').on('click', function() {
            $('.convly-period-btn').removeClass('active');
            $(this).addClass('active');
            const period = $(this).data('period');
            loadViewsChart(period);
        });

        // Add button
        $('.convly-add-button').on('click', function() {
            openButtonModal();
        });

        // Button form submission
        $('#convly-button-form').on('submit', function(e) {
            e.preventDefault();
            saveButton();
        });

        // Modal close
        $('.convly-modal-close, .convly-modal-cancel').on('click', function() {
            $('.convly-modal').hide();
        });

        // Close modal when clicking outside
        $('.convly-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
    }

// Export PDF button
        $('#convly-export-page-pdf').on('click', function(e) {
            e.preventDefault();
            $('#convly-page-pdf-modal').show();
        });

        // PDF date range selection
        $('#convly-page-pdf-range').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#convly-page-pdf-custom-dates').show();
            } else {
                $('#convly-page-pdf-custom-dates').hide();
            }
        });

        // PDF export form submission
        $('#convly-page-pdf-form').on('submit', function(e) {
            e.preventDefault();
            exportPagePDF();
        });

    // Load page statistics
    function loadPageStats() {
        // Load each metric card
        $('.convly-card').each(function() {
            const metric = $(this).data('metric');
            loadMetricCard(metric);
        });
    }

    // Load individual metric card
    function loadMetricCard(metric) {
        const $card = $(`.convly-card[data-metric="${metric}"]`);
        const $value = $card.find('.convly-metric-value');
        const $change = $card.find('.convly-metric-change');

        $value.html('<span class="convly-spinner"></span>');

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_page_stats',
                nonce: convly_ajax.nonce,
                page_id: pageId,
                metric: metric,
                period: '7_days'
            },
            success: function(response) {
                if (response.success) {
                    $value.text(response.data.value);
                    
                    if (response.data.change !== undefined) {
                        $change.text(response.data.change + '%');
                        $change.removeClass('positive negative');
                        $change.addClass(response.data.change > 0 ? 'positive' : 'negative');
                    }

                    // Device breakdown for page views
                    if (metric === 'page_views' && response.data.device_breakdown) {
                        const mobile = response.data.device_breakdown.mobile;
                        const desktop = response.data.device_breakdown.desktop;
                        $card.find('.convly-device-mobile').text(`Mobile: ${mobile}%`);
                        $card.find('.convly-device-desktop').text(`Desktop: ${desktop}%`);
                    }
					
					// Scroll depth breakdown
if (metric === 'scroll_depth' && response.data.breakdown) {
    const b = response.data.breakdown;
    $card.find('.scroll-25 strong').text(b['25'] + '%');
    $card.find('.scroll-50 strong').text(b['50'] + '%');
    $card.find('.scroll-75 strong').text(b['75'] + '%');
    $card.find('.scroll-100 strong').text(b['100'] + '%');
    $card.find('.convly-scroll-progress').css('width', response.data.value);
}
                }
            },
            error: function() {
                $value.text('-');
            }
        });
    }

    // Load views chart
function loadViewsChart(period) {
    $.ajax({
        url: convly_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'convly_get_page_chart_data',
            nonce: convly_ajax.nonce,
            page_id: pageId,
            chart_type: 'all_metrics', // تغییر به all_metrics
            period: period
        },
        success: function(response) {
            if (response.success) {
                renderViewsChart(response.data);
            }
        }
    });
}

    // Render views chart
function renderViewsChart(data) {
    const canvasElement = document.getElementById('convly-views-chart');
    if (!canvasElement) {
        console.error('Convly: Chart canvas element not found');
        return;
    }
    
    const ctx = canvasElement.getContext('2d');
    
    if (viewsChart) {
        viewsChart.destroy();
    }

    viewsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Page Views',
                data: data.views,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Unique Visitors',
                data: data.visitors,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Conversion Rate (%)',
                data: data.conversion_rates,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                fill: false,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

    // Load buttons
    function loadButtons() {
        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_page_buttons',
                nonce: convly_ajax.nonce,
                page_id: pageId
            },
            success: function(response) {
                if (response.success) {
                    renderButtons(response.data);
                    loadButtonCharts(response.data);
                }
            }
        });
    }

    // Render buttons list
    function renderButtons(buttons) {
        const $list = $('#convly-buttons-list');
        
        if (buttons.length === 0) {
            $list.html('<p>' + convly_ajax.i18n.no_buttons + '</p>');
            return;
        }

        let html = '<table class="widefat fixed striped">';
        html += '<thead><tr>';
        html += '<th>' + convly_ajax.i18n.button_name + '</th>';
        html += '<th>' + convly_ajax.i18n.css_id + '</th>';
        html += '<th>' + convly_ajax.i18n.type + '</th>';
        html += '<th>' + convly_ajax.i18n.clicks + '</th>';
        html += '<th>' + convly_ajax.i18n.actions + '</th>';
        html += '</tr></thead><tbody>';

        buttons.forEach(function(button) {
            html += '<tr>';
            html += '<td><strong>' + button.button_name + '</strong></td>';
            html += '<td><code>#' + button.button_css_id + '</code></td>';
            html += '<td>' + button.button_type + '</td>';
            html += '<td>' + button.total_clicks + '</td>';
            html += '<td>';
            html += '<button class="button button-small convly-edit-button" data-button-id="' + button.id + '">' + convly_ajax.i18n.edit + '</button> ';
            html += '<button class="button button-small convly-delete-button" data-button-id="' + button.id + '">' + convly_ajax.i18n.delete + '</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        $list.html(html);

        // Bind button actions
        $('.convly-edit-button').on('click', function() {
            const buttonId = $(this).data('button-id');
            const button = buttons.find(b => b.id == buttonId);
            openButtonModal(button);
        });

        $('.convly-delete-button').on('click', function() {
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

        buttons.forEach(function(button) {
            // Create chart container
            const chartHtml = `
                <div class="convly-chart-container" data-button-id="${button.id}">
                    <div class="convly-chart-header">
                        <h2>${button.button_name} - ${convly_ajax.i18n.clicks}</h2>
                        <div class="convly-chart-period-selector">
                            <button class="convly-button-period-btn active" data-period="7_days" data-button-id="${button.id}">7 days</button>
                            <button class="convly-button-period-btn" data-period="30_days" data-button-id="${button.id}">30 days</button>
                            <button class="convly-button-period-btn" data-period="3_months" data-button-id="${button.id}">3 months</button>
                        </div>
                    </div>
                    <div class="convly-chart-wrapper">
                        <canvas id="convly-button-chart-${button.id}"></canvas>
                    </div>
                </div>
            `;
            
            $container.append(chartHtml);
            
            // Load chart data
            loadButtonChart(button.id, '7_days');
        });

        // Bind period selector events
        $('.convly-button-period-btn').on('click', function() {
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
        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_button_chart_data',
                nonce: convly_ajax.nonce,
                button_id: buttonId,
                period: period
            },
            success: function(response) {
                if (response.success) {
                    renderButtonChart(buttonId, response.data);
                }
            }
        });
    }

    // Render button chart
    function renderButtonChart(buttonId, data) {
        const ctx = document.getElementById('convly-button-chart-' + buttonId).getContext('2d');

        if (buttonCharts[buttonId]) {
            buttonCharts[buttonId].destroy();
        }

        buttonCharts[buttonId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: convly_ajax.i18n.clicks,
                    data: data.values,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
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
        
        $('#convly-button-modal').show();
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
            success: function(response) {
                if (response.success) {
                    $('#convly-button-modal').hide();
                    loadButtons();
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
            success: function(response) {
                if (response.success) {
                    loadButtons();
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
        
        setTimeout(function() {
            $notice.fadeOut(function() {
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