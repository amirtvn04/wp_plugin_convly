<?php
/**
 * Plugin Name: Convly
 * Plugin URI: https://your-website.com/convly
 * Description: Track conversion rates for each page of your website by monitoring button clicks and page views
 * Version: 1.0.0
 * Author: Amir Tavana
 * Author URI: https://your-website.com
 * License: GPL v2 or later
 * Text Domain: convly
 * Domain Path: /languages
 */

// Log start
error_log('Convly: === Starting plugin load ===');

// Prevent direct access
if (!defined('ABSPATH')) {
    error_log('Convly: Direct access attempted - exiting');
    exit;
}

error_log('Convly: ABSPATH check passed');

// Define plugin constants
if (!defined('CONVLY_VERSION')) {
    define('CONVLY_VERSION', '1.0.0');
    error_log('Convly: CONVLY_VERSION defined');
}

if (!defined('CONVLY_PLUGIN_DIR')) {
    define('CONVLY_PLUGIN_DIR', plugin_dir_path(__FILE__));
    error_log('Convly: CONVLY_PLUGIN_DIR defined as: ' . CONVLY_PLUGIN_DIR);
}

if (!defined('CONVLY_PLUGIN_URL')) {
    define('CONVLY_PLUGIN_URL', plugin_dir_url(__FILE__));
    error_log('Convly: CONVLY_PLUGIN_URL defined as: ' . CONVLY_PLUGIN_URL);
}

if (!defined('CONVLY_PLUGIN_BASENAME')) {
    define('CONVLY_PLUGIN_BASENAME', plugin_basename(__FILE__));
    error_log('Convly: CONVLY_PLUGIN_BASENAME defined');
}

// Check and include required files
error_log('Convly: Starting to include required files...');

// Include activator
$activator_file = CONVLY_PLUGIN_DIR . 'includes/class-convly-activator.php';
error_log('Convly: Checking activator file at: ' . $activator_file);
if (file_exists($activator_file)) {
    error_log('Convly: Activator file exists, including...');
    require_once $activator_file;
    error_log('Convly: Activator file included successfully');
    
    // Check if class exists
    if (class_exists('Convly_Activator')) {
        error_log('Convly: Convly_Activator class exists');
    } else {
        error_log('Convly: ERROR - Convly_Activator class NOT found after include');
    }
} else {
    error_log('Convly: ERROR - Activator file NOT FOUND at: ' . $activator_file);
}

// Include deactivator
$deactivator_file = CONVLY_PLUGIN_DIR . 'includes/class-convly-deactivator.php';
error_log('Convly: Checking deactivator file at: ' . $deactivator_file);
if (file_exists($deactivator_file)) {
    error_log('Convly: Deactivator file exists, including...');
    require_once $deactivator_file;
    error_log('Convly: Deactivator file included successfully');
    
    // Check if class exists
    if (class_exists('Convly_Deactivator')) {
        error_log('Convly: Convly_Deactivator class exists');
    } else {
        error_log('Convly: ERROR - Convly_Deactivator class NOT found after include');
    }
} else {
    error_log('Convly: ERROR - Deactivator file NOT FOUND at: ' . $deactivator_file);
}

// Include core
$core_file = CONVLY_PLUGIN_DIR . 'includes/class-convly-core.php';
error_log('Convly: Checking core file at: ' . $core_file);
if (file_exists($core_file)) {
    error_log('Convly: Core file exists, including...');
    require_once $core_file;
    error_log('Convly: Core file included successfully');
    
    // Check if class exists
    if (class_exists('Convly_Core')) {
        error_log('Convly: Convly_Core class exists');
    } else {
        error_log('Convly: ERROR - Convly_Core class NOT found after include');
    }
} else {
    error_log('Convly: ERROR - Core file NOT FOUND at: ' . $core_file);
}

// Activation hook
error_log('Convly: Setting up activation hook...');
if (class_exists('Convly_Activator')) {
    register_activation_hook(__FILE__, array('Convly_Activator', 'activate'));
    error_log('Convly: Activation hook registered');
} else {
    error_log('Convly: ERROR - Cannot register activation hook, Convly_Activator class not found');
}

// Deactivation hook
error_log('Convly: Setting up deactivation hook...');
if (class_exists('Convly_Deactivator')) {
    register_deactivation_hook(__FILE__, array('Convly_Deactivator', 'deactivate'));
    error_log('Convly: Deactivation hook registered');
} else {
    error_log('Convly: ERROR - Cannot register deactivation hook, Convly_Deactivator class not found');
}

// Initialize the plugin
function run_convly() {
    error_log('Convly: run_convly() function called');
    
    if (class_exists('Convly_Core')) {
        error_log('Convly: Creating new Convly_Core instance...');
        try {
            $plugin = new Convly_Core();
            error_log('Convly: Convly_Core instance created successfully');
            
            if (method_exists($plugin, 'run')) {
                error_log('Convly: Calling run() method...');
                $plugin->run();
                error_log('Convly: run() method executed successfully');
            } else {
                error_log('Convly: ERROR - run() method not found in Convly_Core');
            }
        } catch (Exception $e) {
            error_log('Convly: EXCEPTION - ' . $e->getMessage());
            error_log('Convly: Stack trace: ' . $e->getTraceAsString());
        }
    } else {
        error_log('Convly: ERROR - Convly_Core class not found, cannot initialize plugin');
    }
}

// Check if class exists before running
error_log('Convly: Checking if we can run the plugin...');
if (class_exists('Convly_Core')) {
    error_log('Convly: Convly_Core exists, calling run_convly()');
    run_convly();
} else {
    error_log('Convly: ERROR - Convly_Core class not available, plugin will not run');
    
    // Add admin notice about the error
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>Convly Error: Core class not found. Check error log for details.</p></div>';
    });
}

// Add custom action links
error_log('Convly: Adding plugin action links filter...');
add_filter('plugin_action_links_' . CONVLY_PLUGIN_BASENAME, 'convly_add_action_links');
function convly_add_action_links($links) {
    error_log('Convly: convly_add_action_links called');
    $custom_links = array(
        '<a href="' . admin_url('admin.php?page=convly') . '">' . __('Settings', 'convly') . '</a>',
    );
    return array_merge($custom_links, $links);
}

// Exclude Convly AJAX from cache plugins
add_action('init', function() {
    // WP Rocket
    if (defined('WP_ROCKET_VERSION')) {
        add_filter('rocket_cache_reject_uri', function($urls) {
            $urls[] = 'convly_(.*)';
            return $urls;
        });
    }
    
    // W3 Total Cache
    if (defined('W3TC')) {
        add_filter('w3tc_can_cache', function($can_cache) {
            if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'convly_') === 0) {
                return false;
            }
            return $can_cache;
        });
    }
    
    // WP Super Cache
    if (defined('WPCACHEHOME')) {
        add_filter('wpsc_cache_requests', function($cache) {
            if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'convly_') === 0) {
                return false;
            }
            return $cache;
        });
    }
    
    // LiteSpeed Cache
    if (defined('LSCWP_V')) {
        add_action('litespeed_control_set_nocache', function() {
            if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'convly_') === 0) {
                do_action('litespeed_control_set_nocache');
            }
        });
    }
    
    // Add DONOTCACHEPAGE constant for AJAX requests
    if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'convly_') === 0) {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        if (!defined('DONOTCACHEDB')) {
            define('DONOTCACHEDB', true);
        }
        if (!defined('DONOTCACHEOBJECT')) {
            define('DONOTCACHEOBJECT', true);
        }
    }
});

error_log('Convly: === Plugin load complete ===');

// Add a simple test to show plugin is loaded
add_action('admin_notices', function() {
    if (isset($_GET['convly_debug'])) {
        echo '<div class="notice notice-info"><p>Convly Debug: Plugin file loaded. Check error log for detailed information.</p></div>';
    }
});
?>
