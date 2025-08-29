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

<!-- Add Custom Tab Modal -->
<div id="convly-custom-tab-modal" class="convly-modal" style="display:none;">
    <div class="convly_box">
        <span class="convly-modal-close">&times;</span>
        <h2 class="convly_modal_title"><?php _e('Add Custom Tab', 'convly'); ?></h2>
        <p class="convly_modal_subtitle">Here you can create a custom tab and add it to the list of tabs.</p>
        <form id="convly-custom-tab-form">
            <p class="convly_custom_input_box">
                <label class="convly_custom_label" for="convly-tab-name"><?php _e('Tab Name', 'convly'); ?></label>
                <input class="convly_custom_input" type="text" id="convly-tab-name" name="tab_name" required/>
            </p>
            <p class="convly_modal_action">
                <button class="convly_modal_submit" id="convly-tab-submit"
                        type="submit"><?php _e('Add Tab', 'convly'); ?></button>
                <button type="button"
                        class="convly-modal-cancel convly_modal_cancel"><?php _e('Cancel', 'convly'); ?></button>
            </p>

        </form>
    </div>
</div>

<!-- Add/Edit Button Modal -->
<div id="convly-button-modal" class="convly-modal" style="display:none;">
    <div class="convly_box">
        <span class="convly-modal-close">&times;</span>
        <h2 id="convly-button-modal-title" class="convly_modal_title"><?php _e('Add Button', 'convly'); ?></h2>
        <p class="convly_modal_subtitle">Add a button by name and ID to start tracking it.</p>
        <form id="convly-button-form">
            <input type="hidden" id="convly-button-id" name="button_id"/>
            <input type="hidden" id="convly-page-id" name="page_id"/>

            <p class="convly_custom_input_box">
                <label class="convly_custom_label"
                       for="convly-button-css-id"><?php _e('Button CSS ID', 'convly'); ?></label>
                <input class="convly_custom_input" type="text" id="convly-button-css-id" name="button_css_id" required/>
            </p>
            <span class="description"><?php _e('Enter the CSS ID of the button (without #)', 'convly'); ?></span>

            <p class="convly_custom_input_box">
                <label class="convly_custom_label"
                       for="convly-button-name"><?php _e('Button Name', 'convly'); ?></label>
                <input class="convly_custom_input" type="text" id="convly-button-name" name="button_name" required/>
            </p>
            <span class="description"><?php _e('A friendly name for this button', 'convly'); ?></span>

            <p class="convly_modal_select_box">
                <label class="convly_modal_label" for="convly-button-type"><?php _e('Type:', 'convly'); ?></label>
                <select class="convly_custom_select" id="convly-button-type" name="button_type">
                    <option value="button"><?php _e('Button', 'convly'); ?></option>
                    <option value="link"><?php _e('Link', 'convly'); ?></option>
                </select>
            </p>

            <p class="convly_modal_action">
                <button type="submit" class="convly_modal_submit"
                        id="convly_save_button"><?php _e('Save Button', 'convly'); ?></button>
                <button type="button"
                        class="convly-modal-cancel convly_modal_cancel"><?php _e('Cancel', 'convly'); ?></button>
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
    <div class="convly_box">
        <span class="convly-modal-close">&times;</span>
        <h2 class="convly_modal_title"><?php _e('Manage Tab Items', 'convly'); ?> - <span
                    id="convly-current-tab-name"></span></h2>
        <p class="convly_modal_subtitle">Select your favorite pages and then track and manage them in your custom tabs
            section.</p>
        <h3 class="convly_modal_title"><?php _e('Add Items to Tab', 'convly'); ?></h3>
        <div class="convly-item-selector">
            <p class="convly_modal_select_box">
                <label class="convly_modal_label" for="convly-item-type"><?php _e('Type:', 'convly'); ?></label>
                <select class="convly_custom_select" id="convly-item-type" name="button_type">
                    <option value="pages"><?php _e('Pages', 'convly'); ?></option>
                    <option value="posts"><?php _e('Posts', 'convly'); ?></option>
                    <option value="products"><?php _e('Products', 'convly'); ?></option>
                </select>
            </p>

            <select class="convly_custom_select" id="convly-available-items" multiple
                    style="width: 100%; height: 200px; margin-top: 10px;">
                <!-- Items will be loaded here -->
            </select>

            <p class="convly_modal_action">
                <button type="button" id="convly-add-to-tab"
                        class="convly_modal_submit"><?php _e('Add Selected Items', 'convly'); ?></button>
                <button type="button"
                        class="convly-modal-cancel convly_modal_cancel"><?php _e('Cancel', 'convly'); ?></button>
            </p>
        </div>

        <div class="convly-current-items-section" style="margin-top: 30px;">
            <h3 class="convly_modal_title"><?php _e('Current Items in Tab', 'convly'); ?></h3>
            <table class="w-full table-auto divide-y divide-gray-200 mt-8" id="convly-tab-items-table">
                <thead class="text-sm text-left">
                <tr class="*:font-medium *:py-4 text-gray-500">
                    <th><?php _e('PAGE NAME', 'convly'); ?></th>
                    <th><?php _e('ACTIONS', 'convly'); ?></th>
                </tr>
                </thead>
                <tbody class="text-base font-semibold divide-y divide-gray-200">
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
    <div id="table-section" class="rounded-xl bg-white mt-7.5 p-6">


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

        // Modal handlers


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