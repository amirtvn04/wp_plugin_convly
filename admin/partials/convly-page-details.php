<?php
/**
 * Page Details View
 *
 * @package    Convly
 * @subpackage Convly/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get page ID from query parameter
$page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;

if (!$page_id) {
    echo '<div class="notice notice-error"><p>' . __('Invalid page ID', 'convly') . '</p></div>';
    return;
}

// Get page information
global $wpdb;
$table_pages = $wpdb->prefix . 'convly_pages';
$page_info = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_pages WHERE page_id = %d",
    $page_id
));

if (!$page_info) {
    echo '<div class="notice notice-error"><p>' . __('Page not found', 'convly') . '</p></div>';
    return;
}
?>

<div class="text-xl convly_container convly-page-details">
    <!-- header -->
    <header>
        <div class="h-22.5 bg-black1 rounded-xl text-white/80 flex items-center justify-between px-6 mt-5">
            <ul id="navbar" class="convly_navbar">
                <li><a href="<?php echo admin_url('admin.php?page=convly'); ?>"
                       class="nav-link">Dashboard</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=convly-page-details'); ?>"
                       class="nav-link active_nav">Details</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=convly-settings'); ?>" class="nav-link">Settings</a>
                </li>
            </ul>
            <img src="<?php echo CONVLY_PLUGIN_URL; ?>admin/images/image7.png" alt="convly">
        </div>
    </header>

    <!--    <button id="convly-export-page-pdf" class="px-5 py-2.5 border border-black/20 rounded-xl mr-4 cursor-pointer convly-export-btn text-base font-semibold">-->
    <!--        --><?php //_e('Export PDF', 'convly'); ?>
    <!--    </button>-->

    <!-- Summary Cards -->
    <div class="convly_grid_number"
         style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 24px; margin-top: 24px">
        <div class="convly-card-datails bg-white rounded-xl" style="padding: 22px" data-metric="page_views">

        </div>

        <div class="convly-card-datails bg-white rounded-xl" style="padding: 22px" data-metric="unique_visitors">

        </div>

        <div class="convly-card-datails bg-white rounded-xl" style="padding: 22px" data-metric="conversion_rate">

        </div>

        <div class="convly-card-datails bg-white rounded-xl" style="padding: 22px" data-metric="scroll_depth">

        </div>
    </div>


    <!-- chart -->
    <div id="convly-view-chart-container" class="rounded-xl bg-white relative z-0"
         style="margin-top: 24px; padding: 22px">

    </div>

    <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 24px; margin-bottom: 24px; margin-top: 24px">
        <div class="convly-device-breakdown bg-white rounded-xl" style="padding: 22px; grid-column: span 2">

        </div>

        <div class="convly-scroll-breakdown bg-white rounded-xl" style="padding: 22px; grid-column: span 4"
             data-metric="scroll_depth">

        </div>
    </div>

    <div class="convly-buttons-section rounded-xl bg-white" style="padding: 22px">

    </div>

    <!-- Button Charts -->
    <div class="convly-button-charts" id="convly-button-charts">

    </div>
</div>

<!-- Button Modal -->
<div id="convly-button-modal" class="convly-modal" style="display:none;">
    <div class="convly-modal-content convly_box">
        <span class="convly-modal-close">&times;</span>
        <h2 id="convly-button-modal-title" class="convly_modal_title"><?php _e('Add Button', 'convly'); ?></h2>
        <form id="convly-button-form">
            <input type="hidden" id="convly-button-id" name="button_id"/>
            <input type="hidden" id="convly-page-id" name="page_id" value="<?php echo $page_id; ?>"/>

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

<!-- PDF Export Modal for Page -->
<div id="convly-page-pdf-modal" class="convly-modal" style="display:none;">
    <div class="convly-modal-content">
        <span class="convly-modal-close">&times;</span>
        <h2><?php _e('Export Page Report', 'convly'); ?></h2>
        <form id="convly-page-pdf-form">
            <p>
                <label for="convly-page-pdf-range"><?php _e('Select Date Range:', 'convly'); ?></label>
                <select id="convly-page-pdf-range" name="date_range">
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

            <div id="convly-page-pdf-custom-dates" style="display:none;">
                <p>
                    <label for="convly-page-pdf-date-from"><?php _e('From:', 'convly'); ?></label>
                    <input type="date" id="convly-page-pdf-date-from" name="date_from"/>
                </p>
                <p>
                    <label for="convly-page-pdf-date-to"><?php _e('To:', 'convly'); ?></label>
                    <input type="date" id="convly-page-pdf-date-to" name="date_to"/>
                </p>
            </div>

            <p>
                <button type="submit" class="button button-primary"><?php _e('Export PDF', 'convly'); ?></button>
                <button type="button" class="button convly-modal-cancel"><?php _e('Cancel', 'convly'); ?></button>
            </p>
        </form>
    </div>
</div>

<script type="text/javascript">
    // Store page ID for JavaScript
    window.convlyPageId = <?php echo $page_id; ?>;
</script>