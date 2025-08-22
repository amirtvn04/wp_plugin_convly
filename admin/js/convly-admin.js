/**
 * Convly Admin JavaScript
 */


(function($) {
    'use strict';

    // Global variables
    let mainChart = null;
    let currentTab = 'pages';
    let currentPage = 1;
    let itemsPerPage = 20;

    // Initialize
    $(document).ready(function() {
        // Load initial data
        loadSummaryCards();
        loadMainChart('30_days');
        loadPagesList();

        // Event handlers
        bindEventHandlers();
    });

    // Bind event handlers
    function bindEventHandlers() {
        // Period selectors for summary cards
        $('.convly-period-select').on('change', function() {
            const metric = $(this).data('metric');
            const period = $(this).val();
            loadSummaryCard(metric, period);
        });

        // Chart period buttons
        $('.convly-period-btn').on('click', function() {
            $('.convly-period-btn').removeClass('active');
            $(this).addClass('active');
            const period = $(this).data('period');
            loadMainChart(period);
        });

        // Tab switching
        $('.convly-tab').on('click', function(e) {
            e.preventDefault();
            $('.convly-tab').removeClass('active');
            $(this).addClass('active');
            currentTab = $(this).data('tab');
            currentPage = 1;
            loadPagesList();
        });

        // Add custom tab
        $('.convly-add-tab').on('click', function(e) {
            e.preventDefault();
            $('#convly-custom-tab-modal').show();
        });

        // Custom tab form submission
        $('#convly-custom-tab-form').on('submit', function(e) {
            e.preventDefault();
            addCustomTab();
        });

        // Date filter
        $('#convly-date-filter').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.convly-custom-date-range').show();
            } else {
                $('.convly-custom-date-range').hide();
                loadPagesList();
            }
        });

        // Custom date range
        $('#convly-date-from, #convly-date-to').on('change', function() {
            if ($('#convly-date-from').val() && $('#convly-date-to').val()) {
                loadPagesList();
            }
        });

        // Sort by
        $('#convly-sort-by').on('change', function() {
            loadPagesList();
        });

        // Export PDF
        $('#convly-export-pdf').on('click', function() {
            exportPDFReport();
        });

        // Modal close buttons
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

    // Load summary cards
    function loadSummaryCards() {
        $('.convly-card').each(function() {
            const metric = $(this).data('metric');
            const period = $(this).find('.convly-period-select').val();
            loadSummaryCard(metric, period);
        });
    }

    // Load individual summary card
    function loadSummaryCard(metric, period) {
        const $card = $(`.convly-card[data-metric="${metric}"]`);
        const $value = $card.find('.convly-metric-value');
        const $change = $card.find('.convly-metric-change');

        $value.html('<span class="convly-spinner"></span>');
        $change.text('');

        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_stats',
                nonce: convly_ajax.nonce,
                metric: metric,
                period: period
            },
            success: function(response) {
                if (response.success) {
                    $value.text(response.data.value);
                    
                    if (response.data.change) {
                        const changeText = response.data.change > 0 ? '+' + response.data.change + '%' : response.data.change + '%';
                        $change.text(changeText);
                        $change.removeClass('positive negative');
                        $change.addClass(response.data.change > 0 ? 'positive' : 'negative');
                    }
                }
            },
            error: function() {
                $value.text('-');
                showNotification(convly_ajax.i18n.error, 'error');
            }
        });
    }

    // Load main chart
    function loadMainChart(period) {
        $.ajax({
            url: convly_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convly_get_stats',
                nonce: convly_ajax.nonce,
                type: 'chart_data',
                period: period
            },
            success: function(response) {
                if (response.success) {
                    renderMainChart(response.data);
                }
            },
            error: function() {
                showNotification(convly_ajax.i18n.error, 'error');
            }
        });
    }

    // Render main chart
    function renderMainChart(data) {
        const ctx = document.getElementById('convly-main-chart').getContext('2d');

        if (mainChart) {
            mainChart.destroy();
        }

        mainChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: convly_ajax.i18n.views,
                    data: data.views,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: convly_ajax.i18n.clicks,
                    data: data.clicks,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
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

    // Load pages list
    function loadPagesList() {
        const $tbody = $('#convly-pages-list');
        $tbody.html(`<tr><td colspan="7" class="convly-loading">${convly_ajax.i18n.loading}</td></tr>`);

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
            success: function(response) {
                if (response.success) {
                    renderPagesList(response.data);
                } else {
                    $tbody.html(`<tr><td colspan="7">${response.data}</td></tr>`);
                }
            },
            error: function() {
                $tbody.html(`<tr><td colspan="7">${convly_ajax.i18n.error}</td></tr>`);
            }
        });
    }

    // Render pages list
    function renderPagesList(data) {
        const $tbody = $('#convly-pages-list');
        $tbody.empty();

        if (data.items.length === 0) {
            $tbody.html('<tr><td colspan="7">No pages found</td></tr>');
            return;
        }

        data.items.forEach(function(page) {
            const conversionRate = page.unique_visitors > 0 ? 
    ((page.total_clicks / page.unique_visitors) * 100).toFixed(1) : 0;
            
            const conversionClass = conversionRate >= 10 ? 'high' : 
                                  conversionRate >= 5 ? 'medium' : 'low';

            const row = `
    <tr data-page-id="${page.page_id}">
        <td class="column-status">
            <label class="convly-status-toggle">
                <input type="checkbox" ${page.is_active == 1 ? 'checked' : ''} 
                       data-page-id="${page.page_id}">
                <span class="convly-status-slider"></span>
            </label>
        </td>
        <td class="column-name">
            <strong><a href="${page.page_url}" target="_blank">${escapeHtml(page.page_title)}</a></strong>
        </td>
        <td class="column-visitors">${page.unique_visitors}</td>
        <td class="column-views">${page.total_views}</td>
        <td class="column-clicks">
            ${page.has_buttons == 1 ? 
                page.total_clicks : 
                `<button class="button button-small convly-add-button" 
                        data-page-id="${page.page_id}">${convly_ajax.i18n.add_button}</button>`
            }
        </td>
        <td class="column-rate">
            <span class="convly-conversion-rate ${conversionClass}">
                ${conversionRate}%
            </span>
        </td>
        <td class="column-actions">
            <a href="${getDetailsUrl(page.page_id)}" class="convly-action-btn primary">
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
        $('.convly-status-toggle input').off('change').on('change', function() {
            const pageId = $(this).data('page-id');
            const isActive = $(this).is(':checked');
            togglePageStatus(pageId, isActive);
        });

        // Add button
        $('.convly-add-button').off('click').on('click', function() {
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
            success: function(response) {
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
    $('#convly-button-form').on('submit', function(e) {
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
            success: function(response) {
                if (response.success) {
                    $('#convly-button-modal').hide();
                    loadPagesList();
                    showNotification('Button saved successfully', 'success');
                } else {
                    showNotification(response.data, 'error');
                }
            },
            error: function() {
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
            success: function(response) {
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

        $.each(params, function(key, value) {
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
        $notice.on('click', '.notice-dismiss', function() {
            $notice.remove();
        });
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
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