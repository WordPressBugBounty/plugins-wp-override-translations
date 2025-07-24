<?php

class WP_Override_Translations_Admin {

    protected $pluginDetails;

    public function __construct() {

        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $this->pluginDetails = get_plugin_data(WP_OVERRIDE_TRANSLATIONS_PLUGIN_DIR . '/index.php');

        add_action('admin_init', [$this, 'settings_init']);
        add_action('admin_menu', [$this, 'add_menu_admin']);
    }

    public function add_menu_admin() {
        add_options_page('WP Override Translations', 'WP Override Translations', 'manage_options', 'wp-override-translations', [$this, 'setting_page']);
    }

    public function settings_init() {
        if (get_option(WP_OVERRIDE_TRANSLATIONS_LINES) === false) {
            add_option(WP_OVERRIDE_TRANSLATIONS_LINES, []);
        }

        register_setting(WP_OVERRIDE_TRANSLATIONS, WP_OVERRIDE_TRANSLATIONS_LINES, [$this, 'validate_translations_and_save']);
    }

    public function validate_translations_and_save($strings) {

        $updateTranslations = [];

        if (!empty($strings['original']) && count($strings['original']) > 0) {

            foreach ($strings['original'] as $key => $value) {

                if (!empty($value)) {
                    $js_enabled = '0';
                    if (isset($strings['js_enabled']) && is_array($strings['js_enabled'])) {
                        if (isset($strings['js_enabled'][$key])) {
                            $js_enabled = $strings['js_enabled'][$key];
                        }
                    }

                    $updateTranslations[] = [
                        'original' => wp_kses_post($value),
                        'overwrite' => isset($strings['overwrite'][$key]) ? wp_kses_post($strings['overwrite'][$key]) : '',
                        'js_enabled' => $js_enabled,
                        'css_selector' => isset($strings['css_selector'][$key]) ? sanitize_text_field($strings['css_selector'][$key]) : ''
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

        wp_enqueue_script('wp_override_translations_js', WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL . 'js/main.js', ['jquery'], false, true);
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
                            <th scope="column"><?php _e('Enable JS'); ?></th>
                            <th scope="column"><?php _e('CSS Selector'); ?></th>
                            <th scope="column"></th>
                        </tr>
                    </thead>
                    <tbody id="rowsTranslations">
                        <?php $translations = get_option(WP_OVERRIDE_TRANSLATIONS_LINES); ?>
                        <?php if (!empty($translations)) : ?>
                            <?php foreach ($translations as $key => $value) : ?>
                                <tr valign="top" id="row_id_<?php print $key; ?>_translate">
                                    <td>
                                        <input type="text" style="width:100%;" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[original][]" value="<?php if (isset($value['original'])) echo esc_attr($value['original']); ?>" />
                                    </td>
                                    <td>
                                        <input type="text" style="width:100%;" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[overwrite][]" value="<?php if (isset($value['overwrite'])) echo esc_attr($value['overwrite']); ?>" />
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[js_enabled][<?php echo $key; ?>]" value="1" <?php checked(isset($value['js_enabled']) ? $value['js_enabled'] : '0', '1'); ?> />
                                    </td>
                                    <td>
                                        <input type="text" style="width:100%;" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[css_selector][]" value="<?php if (isset($value['css_selector'])) echo esc_attr($value['css_selector']); ?>" placeholder="e.g. #booking_date_from, .my-class" />
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
                            <td style="text-align: center;">
                                <input type="checkbox" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[js_enabled][]" value="1" />
                            </td>
                            <td>
                                <input type="text" style="width:100%;" name="<?php print WP_OVERRIDE_TRANSLATIONS_LINES; ?>[css_selector][]" placeholder="e.g. #booking_date_from, .my-class" />
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
