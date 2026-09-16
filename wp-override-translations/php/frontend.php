<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_Override_Translations {

    protected $search = [];
    protected $replace = [];
    protected $jsEnabledTranslations = [];

    public function __construct() {
        $overrides = get_option(WP_OVERRIDE_TRANSLATIONS_LINES);

        if (!is_array($overrides)) {
            return;
        }

        $translationMap = [];

        foreach ($overrides as $override) {
            if (empty($override['original']) || empty($override['overwrite'])) {
                continue;
            }

            $translationMap[$override['original']] = $override['overwrite'];

            if (!empty($override['js_enabled'])) {
                $this->jsEnabledTranslations[$override['original']] = [
                    'translation' => $override['overwrite'],
                    'selector' => isset($override['css_selector']) ? $override['css_selector'] : '',
                ];
            }
        }

        if (empty($translationMap)) {
            return;
        }

        // Longest originals first, so "Add to cart" is matched before "cart" whatever the saved order
        uksort($translationMap, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        $this->search = array_keys($translationMap);
        $this->replace = array_values($translationMap);

        add_filter('gettext', [$this, 'apply_translate_string']);
        add_filter('ngettext', [$this, 'apply_translate_string']);
        add_filter('gettext_with_context', [$this, 'apply_translate_string']);
        add_filter('ngettext_with_context', [$this, 'apply_translate_string']);

        if (!empty($this->jsEnabledTranslations)) {
            add_action('wp_enqueue_scripts', [$this, 'apply_translate_string_javascript']);
        }
    }

    public function apply_translate_string($translatedString) {

        if (strpos($translatedString, '<') === false) {
            return str_ireplace($this->search, $this->replace, $translatedString);
        }

        // Replace only the text between tags, never tag names or attribute values
        $parts = preg_split('/(<[^>]*>)/', $translatedString, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $index => $part) {
            if ($index % 2 === 0) {
                $parts[$index] = str_ireplace($this->search, $this->replace, $part);
            }
        }

        return implode('', $parts);
    }

    public function apply_translate_string_javascript() {
        wp_enqueue_script('wp-override-translations', WP_OVERRIDE_TRANSLATIONS_PLUGIN_URL . 'js/frontend.js', [], WP_OVERRIDE_TRANSLATIONS_VERSION, true);
        wp_add_inline_script('wp-override-translations', 'var wpOtTranslations = ' . wp_json_encode($this->jsEnabledTranslations, JSON_HEX_TAG) . ';', 'before');
    }
}
