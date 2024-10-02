<?php

/**
 * Plugin Name: WP Override Translations
 * Plugin URI: https://wordpress-plugins.luongovincenzo.it/#wp-override-translations
 * Description: Thanks to this plugin you can translate all the strings of your portal through the admin panel.
 * Version: 1.5.0
 * Author: Vincenzo Luongo
 * Author URI: https://www.luongovincenzo.it/
 * License: GPLv2 or later
 * Text Domain: wp-override-translations
 */
if (!defined('ABSPATH')) {
    exit;
}

define('WP_OVERRIDE_TRANSLATIONS', 'wp_override_translations_options');
define('WP_OVERRIDE_TRANSLATIONS_LINES', 'wp_override_translations_options_lines');

define('WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR', plugin_dir_path(__FILE__));

class WP_Override_Translations_Init {

    public function __construct() {

        if (!is_admin()) {
            require_once('php/frontend.php');
            $WP_Override_Translations = new WP_Override_Translations();
        }

        if (is_admin()) {
            require_once('php/admin.php');
            $WP_Override_Translations_Admin = new WP_Override_Translations_Admin();

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_plugin_actions']);
        }
    }

    public function add_plugin_actions($links) {
        $links[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=wp-override-translations')) . '">Manage Translations</a>';
        return $links;
    }

}

$WP_Override_Translations_Init = new WP_Override_Translations_Init();
?>
