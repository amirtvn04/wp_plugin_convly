<?php
/**
 * Settings Page View
 *
 * @package    Convly
 * @subpackage Convly/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap convly-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php settings_fields('convly_settings'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="convly_enable_tracking">
                        <?php _e('Enable Tracking', 'convly'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               id="convly_enable_tracking" 
                               name="convly_enable_tracking" 
                               value="1" 
                               <?php checked(get_option('convly_enable_tracking', 1), 1); ?> />
                        <?php _e('Enable visitor tracking on the website', 'convly'); ?>
                    </label>
                    <p class="description">
                        <?php _e('When disabled, no tracking data will be collected.', 'convly'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="convly_track_logged_in_users">
                        <?php _e('Track Logged-in Users', 'convly'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               id="convly_track_logged_in_users" 
                               name="convly_track_logged_in_users" 
                               value="1" 
                               <?php checked(get_option('convly_track_logged_in_users', 0), 1); ?> />
                        <?php _e('Track page views and clicks from logged-in users', 'convly'); ?>
                    </label>
                    <p class="description">
                        <?php _e('By default, only guest visitors are tracked.', 'convly'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="convly_excluded_roles">
                        <?php _e('Excluded User Roles', 'convly'); ?>
                    </label>
                </th>
                <td>
                    <?php
                    $excluded_roles = get_option('convly_excluded_roles', array());
                    $all_roles = wp_roles()->roles;
                    
                    foreach ($all_roles as $role_key => $role_info):
                    ?>
                        <label style="display: block; margin-bottom: 5px;">
                            <input type="checkbox" 
                                   name="convly_excluded_roles[]" 
                                   value="<?php echo esc_attr($role_key); ?>"
                                   <?php checked(in_array($role_key, $excluded_roles)); ?> />
                            <?php echo esc_html(translate_user_role($role_info['name'])); ?>
                        </label>
                    <?php endforeach; ?>
                    <p class="description">
                        <?php _e('Users with these roles will not be tracked even if tracking logged-in users is enabled.', 'convly'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="convly_cache_compatibility">
                        <?php _e('Cache Compatibility', 'convly'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               id="convly_cache_compatibility" 
                               name="convly_cache_compatibility" 
                               value="1" 
                               <?php checked(get_option('convly_cache_compatibility', 1), 1); ?> />
                        <?php _e('Enable cache plugin compatibility mode', 'convly'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Ensures tracking works correctly with popular caching plugins.', 'convly'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>

    <hr />

    <h2><?php _e('Data Management', 'convly'); ?></h2>
    
    <div class="convly-data-management">
        <p><?php _e('Manage your tracking data and perform maintenance tasks.', 'convly'); ?></p>
        
        <p>
            <button type="button" class="button" id="convly-export-all-data">
                <?php _e('Export All Data', 'convly'); ?>
            </button>
            <span class="description">
                <?php _e('Export all tracking data as CSV', 'convly'); ?>
            </span>
        </p>

        <p>
            <button type="button" class="button" id="convly-clear-old-data">
                <?php _e('Clear Old Data', 'convly'); ?>
            </button>
            <span class="description">
                <?php _e('Remove tracking data older than 12 months', 'convly'); ?>
            </span>
        </p>

        <p>
            <button type="button" class="button button-link-delete" id="convly-reset-all-data">
                <?php _e('Reset All Data', 'convly'); ?>
            </button>
            <span class="description">
                <?php _e('Warning: This will permanently delete all tracking data!', 'convly'); ?>
            </span>
        </p>
    </div>

    <hr />

    <h2><?php _e('System Information', 'convly'); ?></h2>
    
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Plugin Version', 'convly'); ?></th>
            <td><?php echo CONVLY_VERSION; ?></td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Database Tables', 'convly'); ?></th>
            <td>
                <?php
                global $wpdb;
                $tables = array(
                    'convly_views' => __('Page Views', 'convly'),
                    'convly_clicks' => __('Button Clicks', 'convly'),
                    'convly_buttons' => __('Button Configurations', 'convly'),
                    'convly_pages' => __('Page Settings', 'convly'),
                    'convly_tabs' => __('Custom Tabs', 'convly')
                );
                
                foreach ($tables as $table => $label) {
                    $table_name = $wpdb->prefix . $table;
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
                    $status = $exists ? 
                        '<span style="color: #46b450;">✓ ' . __('Exists', 'convly') . '</span>' : 
                        '<span style="color: #dc3232;">✗ ' . __('Missing', 'convly') . '</span>';
                    
                    echo $label . ': ' . $status . '<br>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Total Records', 'convly'); ?></th>
            <td>
                <?php
                $total_views = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}convly_views");
                $total_clicks = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}convly_clicks");
                
                printf(
                    __('%s page views, %s button clicks', 'convly'),
                    number_format($total_views),
                    number_format($total_clicks)
                );
                ?>
            </td>
        </tr>
		
		<tr>
    <th scope="row">
        <label for="convly_force_no_cache">
            <?php _e('Force No Cache', 'convly'); ?>
        </label>
    </th>
    <td>
        <label>
            <input type="checkbox" 
                   id="convly_force_no_cache" 
                   name="convly_force_no_cache" 
                   value="1" 
                   <?php checked(get_option('convly_force_no_cache', 1), 1); ?> />
            <?php _e('Prevent caching of Convly data (Recommended)', 'convly'); ?>
        </label>
        <p class="description">
            <?php _e('Ensures real-time statistics by preventing cache plugins from caching Convly data.', 'convly'); ?>
        </p>
    </td>
</tr>
		
    </table>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Export all data
    $('#convly-export-all-data').on('click', function() {
        if (confirm('<?php _e('Export all tracking data?', 'convly'); ?>')) {
            window.location.href = '<?php echo admin_url('admin-ajax.php?action=convly_export_all_data&nonce=' . wp_create_nonce('convly_export_nonce')); ?>';
        }
    });

    // Clear old data
    $('#convly-clear-old-data').on('click', function() {
        if (confirm('<?php _e('Remove all tracking data older than 12 months?', 'convly'); ?>')) {
            $.post(ajaxurl, {
                action: 'convly_clear_old_data',
                nonce: '<?php echo wp_create_nonce('convly_ajax_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                }
            });
        }
    });

    // Reset all data
    $('#convly-reset-all-data').on('click', function() {
        const confirmed = prompt('<?php _e('Type "DELETE" to confirm deletion of all tracking data:', 'convly'); ?>');
        if (confirmed === 'DELETE') {
            $.post(ajaxurl, {
                action: 'convly_reset_all_data',
                nonce: '<?php echo wp_create_nonce('convly_ajax_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                }
            });
        }
    });
});
</script>