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

<div class="wrap convly-page-details">
    <h1>
        <?php echo esc_html($page_info->page_title); ?>
        <a href="<?php echo admin_url('admin.php?page=convly'); ?>" class="page-title-action">
            <?php _e('Back to Dashboard', 'convly'); ?>
        </a>
        <a href="#" id="convly-export-page-pdf" class="page-title-action">
            <?php _e('Export PDF', 'convly'); ?>
        </a>
    </h1>

    <!-- Summary Cards -->
    <div class="convly-summary-cards">
        <div class="convly-card-datails" data-metric="page_views">
            <h3><?php _e('Page Views', 'convly'); ?></h3>
            <div class="convly-metric">
                <span class="convly-metric-value">-</span>
                <span class="convly-metric-change"></span>
            </div>
            <div class="convly-device-breakdown">
                <span class="convly-device-mobile"></span>
                <span class="convly-device-desktop"></span>
            </div>
        </div>

        <div class="convly-card-datails" data-metric="unique_visitors">
            <h3><?php _e('Unique Visitors', 'convly'); ?></h3>
            <div class="convly-metric">
                <span class="convly-metric-value">-</span>
                <span class="convly-metric-change"></span>
            </div>
        </div>

        <div class="convly-card-datails" data-metric="conversion_rate">
            <h3><?php _e('Conversion Rate', 'convly'); ?></h3>
            <div class="convly-metric">
                <span class="convly-metric-value">-</span>
                <span class="convly-metric-change"></span>
            </div>
        </div>

        <div class="convly-card-datails" data-metric="scroll_depth">
            <h3><?php _e('Average Scroll Depth', 'convly'); ?></h3>
            <div class="convly-metric">
                <span class="convly-metric-value">-</span>
                <span class="convly-metric-suffix">%</span>
            </div>
            <div class="convly-scroll-breakdown">
                <div class="convly-scroll-bar">
                    <div class="convly-scroll-progress"></div>
                </div>
                <div class="convly-scroll-stats">
                    <span class="scroll-25">25%: <strong>-</strong></span>
                    <span class="scroll-50">50%: <strong>-</strong></span>
                    <span class="scroll-75">75%: <strong>-</strong></span>
                    <span class="scroll-100">100%: <strong>-</strong></span>
                </div>
            </div>
        </div>

    </div>


    <!-- Page Views Chart -->
    <div class="convly-chart-container">
        <div class="convly-chart-header">
            <h2><?php _e('Page Views Over Time', 'convly'); ?></h2>
            <div class="convly-chart-period-selector">
                <button class="convly-period-btn" data-period="24_hours"><?php _e('24 hours', 'convly'); ?></button>
                <button class="convly-period-btn active" data-period="7_days"><?php _e('7 days', 'convly'); ?></button>
                <button class="convly-period-btn" data-period="30_days"><?php _e('30 days', 'convly'); ?></button>
                <button class="convly-period-btn" data-period="3_months"><?php _e('3 months', 'convly'); ?></button>
            </div>
        </div>
        <div class="convly-chart-wrapper">
            <canvas id="convly-views-chart"></canvas>
        </div>
    </div>

    <!-- Buttons Section -->
    <div class="convly-buttons-section">
        <div class="convly-section-header">
            <h2><?php _e('Tracked Buttons', 'convly'); ?></h2>
            <button class="button button-primary convly-add-button" data-page-id="<?php echo $page_id; ?>">
                <?php _e('Add Button', 'convly'); ?>
            </button>
        </div>

        <div class="convly-buttons-list" id="convly-buttons-list">
            <div class="convly-loading"><?php _e('Loading buttons...', 'convly'); ?></div>
        </div>
    </div>

    <!-- Button Charts -->
    <div class="convly-button-charts" id="convly-button-charts">
        <!-- Button charts will be dynamically added here -->
    </div>
</div>

<!-- Button Modal (reuse from dashboard) -->
<div id="convly-button-modal" class="convly-modal" style="display:none;">
    <div class="convly-modal-content">
        <span class="convly-modal-close">&times;</span>
        <h2 id="convly-button-modal-title"><?php _e('Add Button', 'convly'); ?></h2>
        <form id="convly-button-form">
            <input type="hidden" id="convly-button-id" name="button_id"/>
            <input type="hidden" id="convly-page-id" name="page_id" value="<?php echo $page_id; ?>"/>

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