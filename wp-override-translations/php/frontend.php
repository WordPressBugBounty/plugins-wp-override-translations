<?php

class WP_Override_Translations {

    protected $overrides;
    protected $translationMap = [];
    protected $jsEnabledTranslations = [];

    public function __construct() {
        $this->overrides = get_option(WP_OVERRIDE_TRANSLATIONS_LINES);
        add_filter('gettext', [&$this, 'apply_translate_string']);
        add_filter('ngettext', [&$this, 'apply_translate_string']);
        add_action('wp_enqueue_scripts', [&$this, 'apply_translate_string_javascript']);
    }

    public function apply_translate_string($translatedString) {

        if (!is_array($this->overrides)) {
            return $translatedString;
        }

        foreach ($this->overrides as $override) {
            $this->translationMap[$override['original']] = $override['overwrite'];

            if (isset($override['js_enabled']) && $override['js_enabled'] == '1') {
                $this->jsEnabledTranslations[$override['original']] = [
                    'translation' => $override['overwrite'],
                    'selector' => isset($override['css_selector']) ? $override['css_selector'] : ''
                ];
            }

            $findOriginal = $override['original'];
            $replaceOverwrite = $override['overwrite'];

            // If the string contains HTML, use regex to replace only text and not tags
            if (strip_tags($translatedString) !== $translatedString || strip_tags($replaceOverwrite) !== $replaceOverwrite) {
                // Special handling for HTML
                $translatedString = preg_replace(
                    '/(?<=>)' . preg_quote($findOriginal, '/') . '(?=<)|(?<=>)' . preg_quote($findOriginal, '/') . '$/i',
                    $replaceOverwrite,
                    $translatedString
                );
            } else {
                // Normal replacement for plain text
                $translatedString = str_ireplace($findOriginal, $replaceOverwrite, $translatedString);
            }
        }

        return $translatedString;
    }

    public function apply_translate_string_javascript() {

        if (empty($this->jsEnabledTranslations)) {
            return;
        }

        wp_register_script('translations-inline-js', false);
        wp_enqueue_script('translations-inline-js');

        $translations_json = json_encode($this->jsEnabledTranslations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $inline_js = "const translations = $translations_json; " . PHP_EOL;
        $inline_js .= <<<'JS'
			document.addEventListener('DOMContentLoaded', function () {

				const ATTRIBUTES_TO_CHECK = ['placeholder', 'title', 'alt'];

				// Partial and case-insensitive replacement
				const replaceTextUsingMap = (text, translations) => {
					if (!text) return text;

					// Sort keys from longest to shortest to avoid premature partial replacements
					const sortedKeys = Object.keys(translations).sort((a, b) => b.length - a.length);

					sortedKeys.forEach(original => {
						const translation = translations[original];
						// Build a regex with word boundaries, case-insensitive
						const regex = new RegExp(`\\b${escapeRegExp(original)}\\b`, 'gi');
						text = text.replace(regex, match => preserveCase(match, translation));
					});

					return text;
				};

				// Escape special characters in regex
				const escapeRegExp = (string) => {
					return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
				};

				// Preserve original capitalization
				const preserveCase = (source, target) => {
					if (source === source.toUpperCase()) return target.toUpperCase();
					if (source === source.toLowerCase()) return target.toLowerCase();
					if (source[0] === source[0].toUpperCase()) return target.charAt(0).toUpperCase() + target.slice(1);
					return target;
				};


				// Collect all unique selectors from translations
				const collectSelectors = () => {
					const selectors = new Set();
					Object.values(translations).forEach(item => {
						if (item.selector && item.selector.trim()) {
							// Separate multiple selectors
							const selectorList = item.selector.split(',').map(s => s.trim()).filter(s => s);
							selectorList.forEach(sel => selectors.add(sel));
						}
					});
					return Array.from(selectors);
				};
				
				const selectors = collectSelectors();
				
				// Create translation map for replacements
				const translationTextMap = {};
				Object.entries(translations).forEach(([original, data]) => {
					translationTextMap[original] = data.translation;
				});
				
				if (selectors.length > 0) {
					const applyTranslations = () => {
						selectors.forEach(sel => {
							try {
								const elements = document.querySelectorAll(sel);
								elements.forEach(el => {
									// 1. Text content translation
									// Handles both simple text nodes and content with HTML
									const processNode = (node) => {
										if (node.nodeType === Node.TEXT_NODE) {
											const originalText = node.textContent.trim();
											if (originalText) {
												const newText = replaceTextUsingMap(originalText, translationTextMap);
												if (originalText !== newText) {
													node.textContent = newText;
												}
											}
										} else if (node.nodeType === Node.ELEMENT_NODE) {
											// For elements, process child nodes
											Array.from(node.childNodes).forEach(child => processNode(child));
										}
									};
									
									// If there's HTML in the translation, use innerHTML
									let hasHtmlTranslation = false;
									Object.values(translations).forEach(item => {
										if (item.translation && item.translation !== item.translation.replace(/<[^>]*>/g, '')) {
											hasHtmlTranslation = true;
										}
									});
									
									if (hasHtmlTranslation && el.childNodes.length === 1 && el.childNodes[0].nodeType === Node.TEXT_NODE) {
										const originalText = el.textContent.trim();
										const newText = replaceTextUsingMap(originalText, translationTextMap);
										if (originalText !== newText && newText.includes('<')) {
											el.innerHTML = newText;
										} else if (originalText !== newText) {
											el.textContent = newText;
										}
									} else {
										processNode(el);
									}

									// 2. Partial attribute translation
									ATTRIBUTES_TO_CHECK.forEach(attr => {
										const attrValue = el.getAttribute(attr);
										if (attrValue) {
											const newAttr = replaceTextUsingMap(attrValue.trim(), translationTextMap);
											if (attrValue !== newAttr) {
												el.setAttribute(attr, newAttr);
											}
										}
									});
								});
							} catch (e) {
								console.error('Error applying translations for selector:', sel, e);
							}
						});
					};
					
					// Apply immediately
					applyTranslations();
					
					// Then continue to apply
					setInterval(applyTranslations, 100);
				}
			});

		JS;

        wp_add_inline_script('translations-inline-js', $inline_js);
    }
}
