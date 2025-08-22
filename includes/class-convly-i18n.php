<?php
/**
 * Define the internationalization functionality
 *
 * @package    Convly
 * @subpackage Convly/includes
 */

class Convly_i18n {

    /**
     * Load the plugin text domain for translation
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'convly',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}