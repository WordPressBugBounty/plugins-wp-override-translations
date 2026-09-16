=== Override String Translations ===
Contributors: vluongo
Tags: gettext, string translations, override translation, woocommerce translate, translation management
Requires at least: 5.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 4.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Replace any text on your site, like button labels or WooCommerce messages, with your own words. No code, no translation files.

== Description ==

Change the words your site shows, without editing code or translation files.

For example, turn "Add to cart" into "Buy now", "Related products" into "You may also like" or "Proceed to checkout" into "Complete your order".

= How it works =

1. Open Settings > Override Translations.
2. Type the text as it appears on your site, then the text you want instead.
3. Save: the new text shows up right away.

= Features =

* Works with the texts of WordPress, your theme and your plugins, WooCommerce included: buttons, labels, notices, form fields, cart and checkout messages.
* Add simple HTML to the new text, such as bold or a link.
* Upper and lower case don't matter: "add to cart" also matches "Add to cart".
* JS mode, for texts a normal override can't reach, like text typed in a page builder or loaded after the page opens: point to the element with a CSS selector and the plugin replaces the text inside it, placeholders and image alt text included.
* The admin area keeps the original texts.
* Available in English and Italian.

== Installation ==

1. Install the plugin from Plugins > Add New, or upload the `wp-override-translations` folder to `/wp-content/plugins/`.
2. Activate it.
3. Go to Settings > Override Translations and add your first override.

== Frequently Asked Questions ==

= What should I type as the original text? =

The text as your visitors read it, in your site's language: on an Italian site type "Aggiungi al carrello", not "Add to cart". Type it as written, not as CSS may display it (e.g. uppercase buttons).

= A text contains a product name or a number. Can I still change it? =

Yes, use its fixed part. To change "Hoodie has been added to your cart", override "has been added to your cart".

= When do I need JS mode? =

When a text is not replaced by a normal override: text typed in page builders or menus, or added by JavaScript after the page loads. Enable JS and enter the CSS selector of the element that contains the text (e.g. `.site-header`). The plugin then replaces the text, and the placeholder, title and alt attributes, inside those elements, even when they change later.

= Does it change the admin area? =

No. Overrides apply to the public site and to its AJAX requests; the admin screens keep the original text.

= Can I translate the plugin into my language? =

Yes. Contribute on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/wp-override-translations/), or start from `languages/wp-override-translations.pot` with a tool like Poedit.

= What happens to my overrides when I delete the plugin? =

They are removed together with the plugin. Deactivating it keeps them.

== Screenshots ==

1. Settings page: the original text, its replacement and, for JS mode, the CSS selector of the element that contains it.

== Changelog ==

= 4.1.0 =
* Tested up to WordPress 7.1.
* Fix: HTML replacements were ignored on plain-text strings.
* Fix: removing a row in the settings could move the JS option to another row.
* Fix: JS mode could freeze the page when the replacement contains the original text (e.g. "Search" to "Search products").
* Fix: JS mode removed the spaces around the replaced text.
* Fix: overrides no longer depend on the order they were saved in; the longest text wins.
* New: context strings (`_x()`, `_nx()`) and front-end AJAX responses are covered.
* New: JS mode also handles content added later inside the selected elements and selectors containing commas.
* New: Italian translation.
* Plugin assets are loaded as cacheable files, only where needed; settings are removed when the plugin is deleted.

= 4.0.0 =
* Tested up to WordPress 7.0; requires PHP 7.4.
* Settings page redesigned, with an override counter and no jQuery.
* Safer output escaping.

= 3.0.0 =
* JS mode reacts to page changes instead of polling.
* Capability check on save, no inline event handlers, translatable interface.

= 2.0.0 =
* New JS mode with CSS selectors. Tested up to WordPress 6.8.

= 1.5.0 =
* WordPress 6.x support.

= 1.4.0 =
* WordPress 5.9 support and bug fixes.

= 1.3.0 =
* WordPress 5.8 support and bug fixes.

= 1.2.1 =
* WordPress 5.7 support.

= 1.2.0 =
* WordPress 5.6 support and bug fixes.

= 1.1.0 =
* WordPress 5.3 support and bug fixes.

= 1.0.0 =
* First release.
