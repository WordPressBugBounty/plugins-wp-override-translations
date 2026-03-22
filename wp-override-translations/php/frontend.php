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

                if (isset($override['js_enabled']) && $override['js_enabled'] === '1') {
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
            if (strip_tags($translatedString) !== $translatedString || strip_tags($replaceOverwrite) !== $replaceOverwrite) {
                $translatedString = preg_replace(
                    '/(?<=>)' . preg_quote($findOriginal, '/') . '(?=<)|(?<=>)' . preg_quote($findOriginal, '/') . '$/i',
                    $replaceOverwrite,
                    $translatedString
                );
            } else {
                $translatedString = str_ireplace($findOriginal, $replaceOverwrite, $translatedString);
            }
        }

        return $translatedString;
    }

    public function apply_translate_string_javascript() {

        if (empty($this->jsEnabledTranslations)) {
            return;
        }

        wp_register_script('translations-inline-js', false, [], WP_OVERRIDE_TRANSLATIONS_VERSION, true);
        wp_enqueue_script('translations-inline-js');

        $translations_json = wp_json_encode($this->jsEnabledTranslations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $inline_js = "var wpOtTranslations = " . $translations_json . ";\n";
        $inline_js .= <<<'JS'
			document.addEventListener('DOMContentLoaded', function () {
				try {
					var ATTRIBUTES_TO_CHECK = ['placeholder', 'title', 'alt'];
					var isObserving = false;
					var observer = null;

					var replaceTextUsingMap = function (text, map) {
						if (!text) return text;

						try {
							var sortedKeys = Object.keys(map).sort(function (a, b) { return b.length - a.length; });

							sortedKeys.forEach(function (original) {
								var translation = map[original];
								var regex = new RegExp(escapeRegExp(original), 'gi');
								text = text.replace(regex, function (match) { return preserveCase(match, translation); });
							});

							return text;
						} catch (e) {
							return text;
						}
					};

					var escapeRegExp = function (string) {
						return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
					};

					var preserveCase = function (source, target) {
						if (source === source.toUpperCase()) return target.toUpperCase();
						if (source === source.toLowerCase()) return target.toLowerCase();
						if (source[0] === source[0].toUpperCase()) return target.charAt(0).toUpperCase() + target.slice(1);
						return target;
					};

					var collectSelectors = function () {
						var selectors = [];
						var seen = {};
						Object.keys(wpOtTranslations).forEach(function (key) {
							var item = wpOtTranslations[key];
							if (item.selector && item.selector.trim()) {
								var selectorList = item.selector.split(',').map(function (s) { return s.trim(); }).filter(function (s) { return s; });
								selectorList.forEach(function (sel) {
									if (!seen[sel]) {
										seen[sel] = true;
										selectors.push(sel);
									}
								});
							}
						});
						return selectors;
					};

					var selectors = collectSelectors();

					var translationTextMap = {};
					Object.keys(wpOtTranslations).forEach(function (original) {
						translationTextMap[original] = wpOtTranslations[original].translation;
					});

					if (selectors.length > 0) {
						var applyTranslations = function (targetElements) {
							var elementsToCheck = targetElements || selectors.reduce(function (acc, sel) {
								try {
									return acc.concat(Array.from(document.querySelectorAll(sel)));
								} catch (e) {
									return acc;
								}
							}, []);

							elementsToCheck.forEach(function (el) {
								try {
									var processNode = function (node) {
										if (node.nodeType === Node.TEXT_NODE) {
											var originalText = node.textContent.trim();
											if (originalText) {
												var newText = replaceTextUsingMap(originalText, translationTextMap);
												if (originalText !== newText) {
													node.textContent = newText;
												}
											}
										} else if (node.nodeType === Node.ELEMENT_NODE) {
											Array.from(node.childNodes).forEach(function (child) { processNode(child); });
										}
									};

									var hasHtmlTranslation = Object.keys(wpOtTranslations).some(function (key) {
										var t = wpOtTranslations[key].translation;
										return t && t !== t.replace(/<[^>]*>/g, '');
									});

									if (hasHtmlTranslation && el.childNodes.length === 1 && el.childNodes[0].nodeType === Node.TEXT_NODE) {
										var originalText = el.textContent.trim();
										var newText = replaceTextUsingMap(originalText, translationTextMap);
										if (originalText !== newText && newText.indexOf('<') !== -1) {
											// Use DOMParser to safely parse HTML instead of innerHTML
											var parser = new DOMParser();
											var doc = parser.parseFromString('<span>' + newText + '</span>', 'text/html');
											var parsed = doc.body.firstChild;
											el.textContent = '';
											while (parsed.firstChild) {
												el.appendChild(parsed.firstChild);
											}
										} else if (originalText !== newText) {
											el.textContent = newText;
										}
									} else {
										processNode(el);
									}

									ATTRIBUTES_TO_CHECK.forEach(function (attr) {
										var attrValue = el.getAttribute(attr);
										if (attrValue) {
											var newAttr = replaceTextUsingMap(attrValue.trim(), translationTextMap);
											if (attrValue !== newAttr) {
												el.setAttribute(attr, newAttr);
											}
										}
									});
								} catch (e) {
									// Skip element on error
								}
							});
						};

						applyTranslations();

						if (window.MutationObserver && !isObserving) {
							observer = new MutationObserver(function (mutations) {
								var addedElements = [];
								mutations.forEach(function (mutation) {
									if (mutation.type === 'childList') {
										mutation.addedNodes.forEach(function (node) {
											if (node.nodeType === Node.ELEMENT_NODE) {
												selectors.forEach(function (sel) {
													try {
														if (node.matches && node.matches(sel)) {
															addedElements.push(node);
														}
														var descendants = node.querySelectorAll ? node.querySelectorAll(sel) : [];
														Array.from(descendants).forEach(function (d) { addedElements.push(d); });
													} catch (e) {
														// Skip invalid selector
													}
												});
											}
										});
									} else if (mutation.type === 'attributes') {
										selectors.forEach(function (sel) {
											try {
												if (mutation.target.matches && mutation.target.matches(sel)) {
													addedElements.push(mutation.target);
												}
											} catch (e) {
												// Skip invalid selector
											}
										});
									}
								});

								if (addedElements.length > 0) {
									applyTranslations(addedElements);
								}
							});

							observer.observe(document.body, {
								childList: true,
								subtree: true,
								attributes: true,
								attributeFilter: ATTRIBUTES_TO_CHECK
							});

							isObserving = true;
						}
					}
				} catch (e) {
					// Silent fail in production
				}
			});

		JS;

        wp_add_inline_script('translations-inline-js', $inline_js);
    }
}
