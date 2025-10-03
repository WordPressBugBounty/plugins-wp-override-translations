<?php

class WP_Override_Translations {

    protected $overrides;
    protected $translationMap = [];
    protected $jsEnabledTranslations = [];

    public function __construct() {
        $this->overrides = get_option(WP_OVERRIDE_TRANSLATIONS_LINES);
        $this->buildTranslationMaps();
        add_filter('gettext', [$this, 'apply_translate_string']);
        add_filter('ngettext', [$this, 'apply_translate_string']);
        add_action('wp_enqueue_scripts', [$this, 'apply_translate_string_javascript']);
    }

    protected function buildTranslationMaps() {
        if (!is_array($this->overrides)) {
            return;
        }

        foreach ($this->overrides as $override) {
            if (!empty($override['original']) && !empty($override['overwrite'])) {
                $this->translationMap[$override['original']] = $override['overwrite'];

                if (isset($override['js_enabled']) && $override['js_enabled'] == '1') {
                    $this->jsEnabledTranslations[$override['original']] = [
                        'translation' => $override['overwrite'],
                        'selector' => isset($override['css_selector']) ? $override['css_selector'] : ''
                    ];
                }
            }
        }
    }

    public function apply_translate_string($translatedString) {

        if (empty($this->translationMap)) {
            return $translatedString;
        }

        foreach ($this->translationMap as $findOriginal => $replaceOverwrite) {
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
				try {
					const ATTRIBUTES_TO_CHECK = ['placeholder', 'title', 'alt'];
					let isObserving = false;
					let observer = null;

					// Partial and case-insensitive replacement
					const replaceTextUsingMap = (text, translations) => {
						if (!text) return text;

						try {
							// Sort keys from longest to shortest to avoid premature partial replacements
							const sortedKeys = Object.keys(translations).sort((a, b) => b.length - a.length);

							sortedKeys.forEach(original => {
								const translation = translations[original];
								// Build a regex with word boundaries, case-insensitive
								const regex = new RegExp(`\\b${escapeRegExp(original)}\\b`, 'gi');
								text = text.replace(regex, match => preserveCase(match, translation));
							});

							return text;
						} catch (e) {
							console.warn('Error in replaceTextUsingMap:', e);
							return text;
						}
					};

					// Escape special characters in regex
					const escapeRegExp = (string) => {
						try {
							return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
						} catch (e) {
							console.warn('Error in escapeRegExp:', e);
							return string;
						}
					};

					// Preserve original capitalization
					const preserveCase = (source, target) => {
						try {
							if (source === source.toUpperCase()) return target.toUpperCase();
							if (source === source.toLowerCase()) return target.toLowerCase();
							if (source[0] === source[0].toUpperCase()) return target.charAt(0).toUpperCase() + target.slice(1);
							return target;
						} catch (e) {
							console.warn('Error in preserveCase:', e);
							return target;
						}
					};

					// Collect all unique selectors from translations
					const collectSelectors = () => {
						try {
							const selectors = new Set();
							Object.values(translations).forEach(item => {
								if (item.selector && item.selector.trim()) {
									// Separate multiple selectors
									const selectorList = item.selector.split(',').map(s => s.trim()).filter(s => s);
									selectorList.forEach(sel => selectors.add(sel));
								}
							});
							return Array.from(selectors);
						} catch (e) {
							console.warn('Error collecting selectors:', e);
							return [];
						}
					};

					const selectors = collectSelectors();

					// Create translation map for replacements
					const translationTextMap = {};
					try {
						Object.entries(translations).forEach(([original, data]) => {
							translationTextMap[original] = data.translation;
						});
					} catch (e) {
						console.warn('Error creating translation map:', e);
					}

					if (selectors.length > 0) {
						const applyTranslations = (targetElements = null) => {
							const elementsToCheck = targetElements || selectors.flatMap(sel => {
								try {
									return Array.from(document.querySelectorAll(sel));
								} catch (e) {
									console.warn('Invalid selector:', sel, e);
									return [];
								}
							});

							elementsToCheck.forEach(el => {
								try {
									// 1. Text content translation
									const processNode = (node) => {
										try {
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
										} catch (e) {
											console.warn('Error processing node:', e);
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
										try {
											const attrValue = el.getAttribute(attr);
											if (attrValue) {
												const newAttr = replaceTextUsingMap(attrValue.trim(), translationTextMap);
												if (attrValue !== newAttr) {
													el.setAttribute(attr, newAttr);
												}
											}
										} catch (e) {
											console.warn('Error processing attribute:', attr, e);
										}
									});
								} catch (e) {
									console.warn('Error applying translations to element:', el, e);
								}
							});
						};

						// Apply immediately
						applyTranslations();

						// Use MutationObserver for better performance instead of setInterval
						if (window.MutationObserver && !isObserving) {
							observer = new MutationObserver((mutations) => {
								try {
									const addedElements = [];
									mutations.forEach(mutation => {
										if (mutation.type === 'childList') {
											mutation.addedNodes.forEach(node => {
												if (node.nodeType === Node.ELEMENT_NODE) {
													// Check if the added element matches any selector
													selectors.forEach(sel => {
														try {
															if (node.matches && node.matches(sel)) {
																addedElements.push(node);
															}
															// Also check descendants
															const descendants = node.querySelectorAll ? node.querySelectorAll(sel) : [];
															addedElements.push(...Array.from(descendants));
														} catch (e) {
															console.warn('Error checking selector on mutation:', sel, e);
														}
													});
												}
											});
										} else if (mutation.type === 'attributes') {
											// Re-check the element if its attributes changed
											selectors.forEach(sel => {
												try {
													if (mutation.target.matches && mutation.target.matches(sel)) {
														addedElements.push(mutation.target);
													}
												} catch (e) {
													console.warn('Error checking selector on attribute mutation:', sel, e);
												}
											});
										}
									});

									if (addedElements.length > 0) {
										applyTranslations(addedElements);
									}
								} catch (e) {
									console.warn('Error in MutationObserver callback:', e);
								}
							});

							observer.observe(document.body, {
								childList: true,
								subtree: true,
								attributes: true,
								attributeFilter: ATTRIBUTES_TO_CHECK
							});

							isObserving = true;
						} else if (!window.MutationObserver) {
							// Fallback to interval for older browsers
							console.warn('MutationObserver not supported, falling back to interval');
							setInterval(() => applyTranslations(), 500); // Less frequent than before
						}
					}
				} catch (e) {
					console.error('Critical error in WP Override Translations:', e);
				}
			});

		JS;

        wp_add_inline_script('translations-inline-js', $inline_js);
    }
}
