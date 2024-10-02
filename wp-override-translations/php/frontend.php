<?php

class WP_Override_Translations {

    protected $overrides;

    public function __construct() {
        $this->overrides = get_option(WP_OVERRIDE_TRANSLATIONS_LINES);
        add_filter('gettext', [&$this, 'apply_translate_string']);
        add_filter('ngettext', [&$this, 'apply_translate_string']);
    }

    public function apply_translate_string($translatedString) {

        if (!is_array($this->overrides)) {
            return $translatedString;
        }

        foreach ($this->overrides as $override) {
            $findOriginal = $override['original'];
            $replaceOverwrite = $override['overwrite'];

            $translatedString = str_ireplace($findOriginal, $replaceOverwrite, $translatedString);
        }

        return $translatedString;
    }

}
