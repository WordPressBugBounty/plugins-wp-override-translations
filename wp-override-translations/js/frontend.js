(function () {
    'use strict';

    var translations = window.wpOtTranslations || {};
    var ATTRIBUTES = ['placeholder', 'title', 'alt'];
    var TAG = /<[^>]*>/;

    // Values written by this script, per node. They are never translated again: a replacement that contains
    // its original ("Search" -> "Search products") would otherwise grow at every mutation and freeze the page.
    var written = new WeakMap();

    // Longest originals first, so "Add to cart" is matched before "cart"
    var rules = Object.keys(translations).sort(function (a, b) {
        return b.length - a.length;
    }).map(function (original) {
        var translation = translations[original].translation;
        return {
            original: original,
            translation: translation,
            regex: new RegExp(original.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi'),
            html: TAG.test(translation)
        };
    });

    var hasHtmlRules = rules.some(function (rule) {
        return rule.html;
    });

    var selectors = [];
    Object.keys(translations).forEach(function (original) {
        var selector = (translations[original].selector || '').trim();
        if (!selector || selectors.indexOf(selector) !== -1) {
            return;
        }
        try {
            document.createDocumentFragment().querySelector(selector);
            selectors.push(selector);
        } catch (e) {
            // Invalid selector: skip it, or it would break the whole selector list
        }
    });

    if (!selectors.length) {
        return;
    }

    var selector = selectors.join(', ');

    function preserveCase(source, target) {
        var upper = source.toUpperCase();
        var lower = source.toLowerCase();
        if (upper === lower) return target;
        if (source === upper) return target.toUpperCase();
        if (source === lower) return target.toLowerCase();
        if (source.charAt(0) === upper.charAt(0)) return target.charAt(0).toUpperCase() + target.slice(1);
        return target;
    }

    function translate(text) {
        rules.forEach(function (rule) {
            text = text.replace(rule.regex, function (match) {
                return match === rule.original || rule.html ? rule.translation : preserveCase(match, rule.translation);
            });
        });
        return text;
    }

    function remember(node, key, value) {
        var memo = written.get(node) || {};
        memo[key] = value;
        written.set(node, memo);
    }

    // Translated value, or null when there is nothing to change
    function translated(node, key, value) {
        if (!value || (written.get(node) || {})[key] === value) {
            return null;
        }
        var result = translate(value);
        return result === value ? null : result;
    }

    function translateTextNode(node) {
        var parent = node.parentNode;
        if (!parent || parent.nodeName === 'SCRIPT' || parent.nodeName === 'STYLE') {
            return;
        }

        var result = translated(node, 'text', node.nodeValue);
        if (result === null) {
            return;
        }

        if (!hasHtmlRules || !TAG.test(result)) {
            remember(node, 'text', result);
            node.nodeValue = result;
            return;
        }

        // Replacement with markup, already filtered with wp_kses_post() on save. A template keeps leading spaces.
        var template = document.createElement('template');
        template.innerHTML = result;
        var fragment = template.content;

        var walker = document.createTreeWalker(fragment, NodeFilter.SHOW_TEXT);
        while (walker.nextNode()) {
            remember(walker.currentNode, 'text', walker.currentNode.nodeValue);
        }

        parent.replaceChild(fragment, node);
    }

    function translateAttributes(el) {
        ATTRIBUTES.forEach(function (attr) {
            var result = translated(el, attr, el.getAttribute(attr));
            if (result !== null) {
                remember(el, attr, result);
                el.setAttribute(attr, result);
            }
        });
    }

    function translateElement(el) {
        // Collect first: replacing text nodes while walking would break the walker
        var textNodes = [];
        var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }
        textNodes.forEach(translateTextNode);
        translateAttributes(el);
        el.querySelectorAll('[placeholder], [title], [alt]').forEach(translateAttributes);
    }

    function translateMatches(root) {
        if (root.matches(selector)) {
            translateElement(root);
        }
        root.querySelectorAll(selector).forEach(translateElement);
    }

    function inScope(node) {
        var el = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
        return !!el && !!el.closest(selector);
    }

    function onMutations(mutations) {
        mutations.forEach(function (mutation) {
            var target = mutation.target;

            if (mutation.type === 'characterData') {
                if (inScope(target)) {
                    translateTextNode(target);
                }
            } else if (mutation.type === 'attributes') {
                if (inScope(target)) {
                    translateAttributes(target);
                }
            } else if (mutation.addedNodes.length) {
                var targetInScope = inScope(target);
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === Node.TEXT_NODE) {
                        if (targetInScope) {
                            translateTextNode(node);
                        }
                    } else if (node.nodeType === Node.ELEMENT_NODE) {
                        if (targetInScope) {
                            translateElement(node);
                        } else {
                            translateMatches(node);
                        }
                    }
                });
            }
        });
    }

    function init() {
        translateMatches(document.body);

        new MutationObserver(onMutations).observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true,
            attributes: true,
            attributeFilter: ATTRIBUTES
        });
    }

    // The script may run after DOMContentLoaded, e.g. when an optimization plugin delays it
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
