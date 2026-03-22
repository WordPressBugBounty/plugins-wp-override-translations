<?php

class WP_Override_Translations_Admin {

    public function __construct() {
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

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wp-override-translations'));
        }

        $updateTranslations = [];

        if (!empty($strings['original']) && is_array($strings['original'])) {

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

        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        if ($page !== 'wp-override-translations') {
            return;
        }

        wp_enqueue_script('wp_override_translations_js', WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL . 'js/main.js', [], WP_OVERRIDE_TRANSLATIONS_VERSION, true);

        wp_localize_script('wp_override_translations_js', 'wpOverrideTranslations', [
            'optionName' => WP_OVERRIDE_TRANSLATIONS_LINES,
        ]);

        $translations = get_option(WP_OVERRIDE_TRANSLATIONS_LINES);
        $count = is_array($translations) ? count($translations) : 0;
?>
        <div class="wrap">
            <h1><?php echo esc_html__('WP Override Translations', 'wp-override-translations'); ?></h1>
            <p class="description">
                <?php echo esc_html__('Override any WordPress, WooCommerce, plugin, or theme translation string. Enable JS mode and specify CSS selectors for dynamic content replacement.', 'wp-override-translations'); ?>
            </p>

            <style>
                .wot-card {
                    background: #fff;
                    border: 1px solid #c3c4c7;
                    border-radius: 4px;
                    padding: 20px;
                    margin-top: 15px;
                }
                .wot-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .wot-table thead th {
                    text-align: left;
                    padding: 10px 8px;
                    border-bottom: 2px solid #c3c4c7;
                    font-weight: 600;
                    color: #1d2327;
                }
                .wot-table tbody tr {
                    border-bottom: 1px solid #f0f0f1;
                }
                .wot-table tbody tr:hover {
                    background: #f6f7f7;
                }
                .wot-table td {
                    padding: 8px;
                    vertical-align: middle;
                }
                .wot-table input[type="text"] {
                    width: 100%;
                }
                .wot-table input[type="checkbox"] {
                    margin: 0;
                }
                .wot-table .column-js {
                    width: 70px;
                    text-align: center;
                }
                .wot-table .column-actions {
                    width: 40px;
                    text-align: center;
                }
                .wot-delete-btn {
                    cursor: pointer;
                    color: #b32d2e;
                    font-size: 18px;
                }
                .wot-delete-btn:hover {
                    color: #a00;
                }
                .wot-counter {
                    display: inline-block;
                    background: #2271b1;
                    color: #fff;
                    border-radius: 10px;
                    padding: 1px 8px;
                    font-size: 12px;
                    margin-left: 5px;
                    vertical-align: middle;
                }
                .wot-actions {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                    margin-top: 15px;
                }
                .wot-empty-state {
                    text-align: center;
                    padding: 30px;
                    color: #646970;
                }
            </style>

            <form method="POST" action="options.php">

                <?php do_settings_sections(WP_OVERRIDE_TRANSLATIONS); ?>
                <?php settings_fields(WP_OVERRIDE_TRANSLATIONS); ?>

                <div class="wot-card">
                    <h2 style="margin-top: 0;">
                        <?php echo esc_html__('Translation Overrides', 'wp-override-translations'); ?>
                        <span class="wot-counter" id="wot-count"><?php echo esc_html($count); ?></span>
                    </h2>

                    <table class="wot-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Original Translation', 'wp-override-translations'); ?></th>
                                <th><?php echo esc_html__('New translation (Override)', 'wp-override-translations'); ?></th>
                                <th class="column-js"><?php echo esc_html__('JS', 'wp-override-translations'); ?></th>
                                <th><?php echo esc_html__('CSS Selector', 'wp-override-translations'); ?></th>
                                <th class="column-actions"></th>
                            </tr>
                        </thead>
                        <tbody id="rowsTranslations">
                            <?php if (!empty($translations) && is_array($translations)) : ?>
                                <?php foreach ($translations as $key => $value) : ?>
                                    <tr>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(WP_OVERRIDE_TRANSLATIONS_LINES); ?>[original][]" value="<?php echo isset($value['original']) ? esc_attr($value['original']) : ''; ?>" />
                                        </td>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(WP_OVERRIDE_TRANSLATIONS_LINES); ?>[overwrite][]" value="<?php echo isset($value['overwrite']) ? esc_attr($value['overwrite']) : ''; ?>" />
                                        </td>
                                        <td class="column-js">
                                            <input type="checkbox" name="<?php echo esc_attr(WP_OVERRIDE_TRANSLATIONS_LINES); ?>[js_enabled][<?php echo esc_attr($key); ?>]" value="1" <?php checked(isset($value['js_enabled']) ? $value['js_enabled'] : '0', '1'); ?> />
                                        </td>
                                        <td>
                                            <input type="text" name="<?php echo esc_attr(WP_OVERRIDE_TRANSLATIONS_LINES); ?>[css_selector][]" value="<?php echo isset($value['css_selector']) ? esc_attr($value['css_selector']) : ''; ?>" placeholder="e.g. #booking_date_from, .my-class" />
                                        </td>
                                        <td class="column-actions">
                                            <span class="dashicons dashicons-no wot-delete-btn deleteTranslateAction"></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr>
                                <td>
                                    <input type="text" name="<?php echo esc_attr(WP_OVERRIDE_TRANSLATIONS_LINES); ?>[original][]" />
                                </td>
                                <td>
                                    <input type="text" name="<?php echo esc_attr(WP_OVERRIDE_TRANSLATIONS_LINES); ?>[overwrite][]" />
                                </td>
                                <td class="column-js">
                                    <input type="checkbox" name="<?php echo esc_attr(WP_OVERRIDE_TRANSLATIONS_LINES); ?>[js_enabled][]" value="1" />
                                </td>
                                <td>
                                    <input type="text" name="<?php echo esc_attr(WP_OVERRIDE_TRANSLATIONS_LINES); ?>[css_selector][]" placeholder="e.g. #booking_date_from, .my-class" />
                                </td>
                                <td class="column-actions"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="wot-actions">
                    <input type="submit" class="button-primary" value="<?php echo esc_attr__('Save', 'wp-override-translations'); ?>" />
                    <button type="button" class="button-secondary" id="add-new-translation"><?php echo esc_html__('Add New Override', 'wp-override-translations'); ?></button>
                </div>
            </form>
        </div>
<?php
    }
}
