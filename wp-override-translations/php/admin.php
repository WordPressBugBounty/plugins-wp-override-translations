<?php

class WP_Override_Translations_Admin {

    protected $pluginDetails;

    public function __construct() {

        if (!function_exists('get_plugin_data')) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $this->pluginDetails = get_plugin_data(WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR . '/index.php');

        add_action('admin_init', [$this, 'settings_init']);
        add_action('admin_menu', [$this, 'add_menu_admin']);
    }

    public function add_menu_admin() {
        add_options_page('WP Override Translations', 'WP Override Translations', 'administrator', 'wp-override-translations', [$this, 'setting_page']);
    }

    public function settings_init() {

        register_setting(WP_OVERRIDE_TRANSLATIONS, WP_OVERRIDE_TRANSLATIONS_LINES, [$this, 'validate_translations_and_save']);

//		if (get_option(WP_OVERRIDE_TRANSLATIONS_LINES) === false) {
//
//			update_option(WP_OVERRIDE_TRANSLATIONS_LINES, []);
//		}
    }

    public function validate_translations_and_save($strings) {

        $updateTranslations = [];

        if (!empty($strings['original']) && count($strings['original']) > 0) {

            foreach ($strings['original'] as $key => $value) {

                if (!empty($value)) {
                    $updateTranslations[] = [
                        'original' => $value,
                        'overwrite' => $strings['overwrite'][$key],
                        'descriptions' => $strings['descriptions'][$key],
                    ];
                }
            }
        }

        return $updateTranslations;
    }

    public function setting_page() {

        if (!isset($_GET['page']) || $_GET['page'] != 'wp-override-translations') {
            return true;
        }

        wp_enqueue_script('wp_override_translations_js', WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL . 'js/main.js', ['jquery']);
        ?>
        <div class="wrap">
            <h2><?php _e("WP Override Translations Settings"); ?></h2>

            <form method="POST" action="options.php">

                <?php do_settings_sections(WP_OVERRIDE_TRANSLATIONS); ?>
                <?php settings_fields(WP_OVERRIDE_TRANSLATIONS); ?>

                <table class="form-table">
                    <thead>
                        <tr valign="top">
                            <th scope="column"><?php _e('Original Translation'); ?></th>
                            <th scope="column"><?php _e('New translation (Override)'); ?></th>
                            <th scope="column"><?php _e('Description (visible only to the admin)'); ?></th>
                            <th scope="column"></th>
                        </tr>
                    </thead>
                    <tbody id="rowsTranslations">
                        <?php $translations = get_option(WP_OVERRIDE_TRANSLATIONS_LINES); ?>
                        <?php if (!empty($translations)) : ?>
                            <?php foreach ($translations as $key => $value) : ?>
                                <tr valign="top" id="row_id_<?php print $key; ?>_translate">
                                    <td>
                                        <input type="text" style="width:100%;" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[original][]" value="<?php if (isset($value['original'])) echo esc_html(htmlspecialchars($value['original'])); ?>" />
                                    </td>
                                    <td>
                                        <input type="text" style="width:100%;" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[overwrite][]" value="<?php if (isset($value['overwrite'])) echo esc_html($value['overwrite']); ?>" />
                                    </td>
                                    <td class="td_textarea">
                                        <textarea style="width:100%;" rows="3" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[descriptions][]"><?php if (isset($value['descriptions'])) echo esc_html($value['descriptions']); ?></textarea>
                                    </td>
                                    <td>
                                        <span class="dashicons dashicons-no deleteTranslateAction" style="cursor: pointer; color: red;" id="row_id_<?php print $key; ?>"></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr valign="top">
                            <td>
                                <input type="text" style="width:100%;" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[original][]" />
                            </td>
                            <td>
                                <input type="text" style="width:100%;" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[overwrite][]" />
                            </td>
                            <td class="td_textarea">
                                <textarea style="width:100%;" rows="3" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[descriptions][]"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" style="margin:5px;" value="<?php _e('Save') ?>" />
                    <span class="button-primary" style="margin:5px;" onClick="addRowTranslate();">Add New Overwrite Translate</span>
                </p>
            </form>
        </div>
        <?php
    }

}
