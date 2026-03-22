<?php

/**
 * Plugin Name: WP Override Translations
 * Plugin URI: https://wordpress-plugins.luongovincenzo.it/plugin/wp-override-translations
 * Description: Thanks to this plugin you can translate all the strings of your portal through the admin panel.
 * Version: 4.0.0
 * Author: Vincenzo Luongo
 * Author URI: https://www.luongovincenzo.it/
 * License: GPLv2 or later
 * Text Domain: wp-override-translations
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_OVERRIDE_TRANSLATIONS', 'wp_override_translations_options');
define('WP_OVERRIDE_TRANSLATIONS_LINES', 'wp_override_translations_options_lines');
define('WP_OVERRIDE_TRANSLATIONS_VERSION', '4.0.0');

define('WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR', plugin_dir_path(__FILE__));

class WP_Override_Translations_Init {

    public function __construct() {

        register_activation_hook(__FILE__, [$this, 'plugin_activation']);

        if (!is_admin()) {
            require_once WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR . 'php/frontend.php';
            new WP_Override_Translations();
        }

        if (is_admin()) {
            require_once WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR . 'php/admin.php';
            new WP_Override_Translations_Admin();

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_plugin_actions']);
        }
    }

    public function plugin_activation() {
        if (get_option(WP_OVERRIDE_TRANSLATIONS_LINES) === false) {
            add_option(WP_OVERRIDE_TRANSLATIONS_LINES, []);
        }
    }

    public function add_plugin_actions($links) {
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=wp-override-translations')) . '">' . __('Manage Translations', 'wp-override-translations') . '</a>';
        return $links;
    }
}

new WP_Override_Translations_Init();
