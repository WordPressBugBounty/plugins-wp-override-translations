=== Override String Translations ===
Contributors: vluongo
Stable tag: 4.0.0
Tags: gettext, ngettext, string translations, override translation, woocommerce translate, security, performance, translation management
Requires at least: 5.6
Tested up to: 7.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A secure and high-performance WordPress plugin for overriding string translations through your admin panel.

== Description ==

**WP Override Translations** allows you to easily override any text string in WordPress, WooCommerce, and plugin/theme translations directly from your admin panel - no code editing required!

**Key Features:**

* Override any WordPress core, plugin, or theme text strings
* Full WooCommerce compatibility for e-commerce sites
* Support for HTML in translations (bold, links, etc.)
* CSS selector-based DOM string replacement for dynamic content
* Translates all `_e()`, `__()`, `gettext`, and `ngettext` calls
* Real-time JavaScript translation for dynamic elements
* WordPress coding standards compliant

**Security:**

* CSRF protection with WordPress nonces
* User capability validation
* XSS prevention with proper output escaping and DOMParser
* No inline JavaScript handlers
* Sanitized inputs with `wp_kses_post` and `sanitize_text_field`

**Performance:**

* MutationObserver API for DOM changes (no polling intervals)
* Pre-built translation maps for faster processing
* Optimized JavaScript with error handling
* No jQuery dependency

**What You Can Translate:**

* WordPress core strings
* WooCommerce product pages, checkout, cart messages
* Plugin and theme text strings
* Button labels, error messages, form fields
* Any string that uses WordPress translation functions

**Limitations:**

Dynamic strings with placeholders like `%s` or `%d` cannot be translated (e.g., "%s has been added to your cart").

== Installation ==

1. Upload directory `WP Override String Translations` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Click settings in your plugin list (or visit Settings submenu)
4. Done!

== Frequently Asked Questions ==

= How does this plugin work? =
It uses a gettext and ngettext WordPress filter

== Changelog ==

= 4.0.0 =
* **COMPATIBILITY**: Tested and compatible with WordPress 7.0
* **BREAKING**: Removed jQuery dependency - admin JS is now vanilla JavaScript
* **BREAKING**: Minimum PHP version raised to 7.4
* **SECURITY**: Replaced `innerHTML` with `DOMParser` for safe HTML insertion (XSS prevention)
* **SECURITY**: Replaced `json_encode` with `wp_json_encode` for safer JSON output
* **SECURITY**: Sanitized `$_GET['page']` with `sanitize_text_field` and `wp_unslash`
* **SECURITY**: Replaced `print` with `echo` and added `esc_attr`/`esc_html` escaping throughout
* **PERFORMANCE**: Removed fallback `setInterval` polling for browsers without MutationObserver (all modern browsers support it)
* **PERFORMANCE**: Script versioning via plugin version constant for proper cache busting
* **UI**: Redesigned admin interface with card-style layout and modern styling
* **UI**: Added translation counter badge
* **UI**: Added description text for better onboarding
* **UI**: Delete button now appears on dynamically added rows
* **UI**: Event delegation for delete actions (works on new rows without re-binding)
* **CODE**: Used plugin directory constant for `require_once` paths
* **CODE**: Removed unused `$pluginDetails` property and `get_plugin_data` call
* **CODE**: Removed `console.warn` logging from production JS (silent fail)
* **CODE**: Removed word boundary `\b` from JS regex to support non-Latin scripts

= 3.0.0 =
* **MAJOR SECURITY UPDATES**: Added CSRF protection with WordPress nonces
* **MAJOR PERFORMANCE IMPROVEMENTS**: Replaced setInterval with MutationObserver for better browser performance
* **SECURITY**: Added user capability checks for admin functions
* **SECURITY**: Removed inline onClick handlers to prevent XSS vulnerabilities
* **PERFORMANCE**: Pre-built translation maps to eliminate redundant processing loops
* **PERFORMANCE**: Optimized JavaScript error handling with try-catch blocks and graceful degradation
* **STANDARDS**: Fixed all WordPress coding standards violations (variable naming, text domains, etc.)
* **JAVASCRIPT**: Enhanced DOM manipulation with better selector validation
* **JAVASCRIPT**: Added fallback support for older browsers without MutationObserver
* **ADMIN**: Improved user interface with proper event listeners instead of inline JavaScript
* **ADMIN**: Added wp_localize_script for secure PHP-to-JavaScript data transfer
* **TRANSLATION**: Added proper text domain support for all translatable strings
* **CODE QUALITY**: Comprehensive error handling and logging improvements
* **COMPATIBILITY**: Maintained backward compatibility while modernizing codebase

= 2.0.0 =
* Improvements for Wordpress 6.8 support
* Add javascript with selector translate
* Code improvements
* Code Fixes

= 1.5.0 =
* Support for Wordpress 6.x added

= 1.4.0 =
* Support for Wordpress 5.9 added
* Bug fix

= 1.3.0 =
* Support for Wordpress 5.8 added
* Minor bug fix

= 1.2.1 =
* Support for Wordpress 5.7 added

= 1.2.0 =
* Bug Fix and added support for Wordpress 5.6

= 1.1.0 =
* Compatible with Wordpress 5.3
* bug fix

= 1.0.0 =
* First public release


== Screenshots ==

1. Original frontend string
2. Overwrite string from backend
3. Overwritten string
4. Original Woocommerce frontend string
5. Overwrite string from backend
6. Overwritten Woocommerce string with HTML
