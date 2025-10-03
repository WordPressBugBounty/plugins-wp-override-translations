=== Override String Translations ===
Contributors: vluongo
Stable tag: 3.0.0
Tags: gettext, ngettext, string translations, override translation, woocommerce translate, security, performance, translation management
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A secure and high-performance WordPress plugin for overriding string translations through your admin panel.

== Description ==

**WP Override Translations** allows you to easily override any text string in WordPress, WooCommerce, and plugin/theme translations directly from your admin panel - no code editing required!

**🔒 Security First (v3.0.0)**
* CSRF protection with WordPress nonces
* User capability validation
* XSS prevention with secure coding practices
* No inline JavaScript handlers

**⚡ High Performance (v3.0.0)**
* Modern MutationObserver API for DOM changes (replaces resource-heavy intervals)
* Pre-built translation maps for faster processing
* Optimized JavaScript with comprehensive error handling
* Graceful fallback for older browsers

**✨ Key Features:**
* Override any WordPress core, plugin, or theme text strings
* Full WooCommerce compatibility for e-commerce sites
* Support for HTML in translations (bold, links, etc.)
* CSS selector-based DOM string replacement for dynamic content
* Translates all `_e()`, `__()`, `gettext`, and `ngettext` calls
* Real-time JavaScript translation for dynamic elements
* WordPress coding standards compliant

**📝 What You Can Translate:**
* WordPress core strings
* WooCommerce product pages, checkout, cart messages
* Plugin and theme text strings
* Button labels, error messages, form fields
* Any string that uses WordPress translation functions

**❌ Limitations:**
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
