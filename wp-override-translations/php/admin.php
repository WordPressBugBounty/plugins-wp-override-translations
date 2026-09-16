<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_Override_Translations_Admin {

    public function __construct() {
        add_action('admin_init', [$this, 'settings_init']);
        add_action('admin_menu', [$this, 'add_menu_admin']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu_admin() {
        add_options_page(
            __('Override String Translations', 'wp-override-translations'),
            __('Override Translations', 'wp-override-translations'),
            'manage_options',
            'wp-override-translations',
            [$this, 'setting_page']
        );
    }

    public function settings_init() {
        register_setting(WP_OVERRIDE_TRANSLATIONS, WP_OVERRIDE_TRANSLATIONS_LINES, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'validate_translations_and_save'],
        ]);
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'settings_page_wp-override-translations') {
            return;
        }

        wp_enqueue_style('wp-override-translations-admin', WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL . 'css/admin.css', [], WP_OVERRIDE_TRANSLATIONS_VERSION);
        wp_enqueue_script('wp-override-translations-admin', WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL . 'js/admin.js', [], WP_OVERRIDE_TRANSLATIONS_VERSION, true);
    }

    /**
     * Each row is posted as its own array (name="option[<row>][field]"), so fields stay bound to their row
     * even when rows are removed or a checkbox is left unchecked. The output has the same shape, so running
     * it again on an already saved value is harmless.
     */
    public function validate_translations_and_save($rows) {

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wp-override-translations'));
        }

        $translations = [];

        if (!is_array($rows)) {
            return $translations;
        }

        foreach ($rows as $row) {
            if (!is_array($row) || empty($row['original'])) {
                continue;
            }

            $translations[] = [
                'original' => wp_kses_post($row['original']),
                'overwrite' => isset($row['overwrite']) ? wp_kses_post($row['overwrite']) : '',
                'js_enabled' => empty($row['js_enabled']) ? '0' : '1',
                'css_selector' => isset($row['css_selector']) ? sanitize_text_field($row['css_selector']) : '',
            ];
        }

        return $translations;
    }

    public function setting_page() {
        $translations = get_option(WP_OVERRIDE_TRANSLATIONS_LINES);
        $translations = is_array($translations) ? array_values($translations) : [];
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p class="description">
                <?php esc_html_e('Enter the text as your visitors see it (matching is case-insensitive) and the text to show instead. HTML is allowed in the replacement. If some text is still not replaced (e.g. text loaded by JavaScript), enable JS and enter the CSS selector of the element that contains it.', 'wp-override-translations'); ?>
            </p>

            <form method="post" action="options.php">
                <?php settings_fields(WP_OVERRIDE_TRANSLATIONS); ?>

                <div class="wot-card">
                    <h2>
                        <?php esc_html_e('Translation Overrides', 'wp-override-translations'); ?>
                        <span class="wot-counter" id="wot-count"><?php echo esc_html(count($translations)); ?></span>
                    </h2>

                    <table class="wot-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Original Translation', 'wp-override-translations'); ?></th>
                                <th><?php esc_html_e('New translation (Override)', 'wp-override-translations'); ?></th>
                                <th class="column-js"><?php esc_html_e('JS', 'wp-override-translations'); ?></th>
                                <th><?php esc_html_e('CSS Selector', 'wp-override-translations'); ?></th>
                                <th class="column-actions"></th>
                            </tr>
                        </thead>
                        <tbody id="wot-rows">
                            <?php
                            foreach ($translations as $index => $translation) {
                                $this->render_row($index, $translation);
                            }
                            $this->render_row(count($translations));
                            ?>
                        </tbody>
                    </table>

                    <template id="wot-row-template"><?php $this->render_row('__INDEX__'); ?></template>
                </div>

                <div class="wot-actions">
                    <?php submit_button(__('Save', 'wp-override-translations'), 'primary', 'submit', false); ?>
                    <button type="button" class="button" id="wot-add-row"><?php esc_html_e('Add New Override', 'wp-override-translations'); ?></button>
                </div>
            </form>
        </div>
<?php
    }

    protected function render_row($index, $translation = []) {
        $name = WP_OVERRIDE_TRANSLATIONS_LINES . '[' . $index . ']';
        $translation = wp_parse_args($translation, [
            'original' => '',
            'overwrite' => '',
            'js_enabled' => '0',
            'css_selector' => '',
        ]);
?>
        <tr>
            <td>
                <input type="text" class="wot-original" name="<?php echo esc_attr($name . '[original]'); ?>" value="<?php echo esc_attr($translation['original']); ?>" aria-label="<?php esc_attr_e('Original Translation', 'wp-override-translations'); ?>" />
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr($name . '[overwrite]'); ?>" value="<?php echo esc_attr($translation['overwrite']); ?>" aria-label="<?php esc_attr_e('New translation (Override)', 'wp-override-translations'); ?>" />
            </td>
            <td class="column-js">
                <input type="checkbox" name="<?php echo esc_attr($name . '[js_enabled]'); ?>" value="1" <?php checked($translation['js_enabled'], '1'); ?> aria-label="<?php esc_attr_e('Enable JS', 'wp-override-translations'); ?>" />
            </td>
            <td>
                <input type="text" name="<?php echo esc_attr($name . '[css_selector]'); ?>" value="<?php echo esc_attr($translation['css_selector']); ?>" placeholder="<?php esc_attr_e('e.g. #booking_date_from, .my-class', 'wp-override-translations'); ?>" aria-label="<?php esc_attr_e('CSS Selector', 'wp-override-translations'); ?>" />
            </td>
            <td class="column-actions">
                <button type="button" class="wot-delete-btn" aria-label="<?php esc_attr_e('Remove override', 'wp-override-translations'); ?>">
                    <span class="dashicons dashicons-no" aria-hidden="true"></span>
                </button>
            </td>
        </tr>
<?php
    }
}
