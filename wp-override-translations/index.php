<?php

/**
 * Plugin Name: Override String Translations
 * Plugin URI: https://wordpress-plugins.luongovincenzo.it/plugin/wp-override-translations
 * Description: Change any text of WordPress, WooCommerce, plugins and themes from the admin panel.
 * Version: 4.1.0
 * Author: Vincenzo Luongo
 * Author URI: https://www.luongovincenzo.it/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-override-translations
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WP_OVERRIDE_TRANSLATIONS', 'wp_override_translations_options');
define('WP_OVERRIDE_TRANSLATIONS_LINES', 'wp_override_translations_options_lines');
define('WP_OVERRIDE_TRANSLATIONS_VERSION', '4.1.0');

define('WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR', plugin_dir_path(__FILE__));

class WP_Override_Translations_Init {

    public function __construct() {

        register_activation_hook(__FILE__, [$this, 'plugin_activation']);
        add_action('init', [$this, 'load_textdomain']);

        // Front-end AJAX requests go through admin-ajax.php, where is_admin() is true
        if (!is_admin() || wp_doing_ajax()) {
            require_once WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR . 'php/frontend.php';
            new WP_Override_Translations();
        }

        if (is_admin()) {
            require_once WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR . 'php/admin.php';
            new WP_Override_Translations_Admin();

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_plugin_actions']);
        }
    }

    // Bundled translations; language packs from WordPress.org, when present, still take priority
    public function load_textdomain() {
        load_plugin_textdomain('wp-override-translations', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function plugin_activation() {
        if (get_option(WP_OVERRIDE_TRANSLATIONS_LINES) === false) {
            add_option(WP_OVERRIDE_TRANSLATIONS_LINES, []);
        }
    }

    public function add_plugin_actions($links) {
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=wp-override-translations')) . '">' . esc_html__('Manage Translations', 'wp-override-translations') . '</a>';
        return $links;
    }
}

new WP_Override_Translations_Init();
