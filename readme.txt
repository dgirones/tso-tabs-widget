=== TSO Tabs Widget ===
Contributors: deadko
Tags: widget, tabs, popular posts, recent posts, ajax
Requires at least: 6.1
Tested up to: 7.1
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display popular posts, recent posts, comments and tags in tabbed format with AJAX loading. Compatible with all major cache plugins.

== Description ==

**TSO Tabs Widget** adds a tabbed widget with AJAX loading to your WordPress site.

**Main features:**

* Tabs: Popular, Recent, Comments, Tags
* AJAX loading compatible with LiteSpeed Cache, WP Rocket, W3 Total Cache and others
* Post thumbnails with Small and Large sizes
* AJAX pagination
* Customisable tab order
* Advanced options: excerpt, date, comment count, avatars
* PHP 7.4 to 8.5 compatible
* WordPress 6.1 to 7.0 compatible
* Translations: Catalan (ca_ES), Spanish (es_ES), English (en) via language packs
* Security: nonces on all AJAX requests, full sanitisation and escaping

**Cache compatibility note:**
The visit counter used by the Popular tab runs via AJAX so it works correctly with any full-page cache plugin.

== External services ==

This plugin may contact the following third-party services:

**TSO activation ping** (`https://tusoporteonline.es/blog/pings/neteja.php`)
* **What:** Anonymous plugin lifecycle ping on activate and deactivate.
* **When:** Only when the plugin is activated or deactivated.
* **Data sent:** Event type (`activate` or `deactivate`), plugin name, PHP major/minor version, and site hostname (no full URL or personal data).
* **Terms of use:** https://www.tusoporteonline.es/blog
* **Privacy policy:** https://www.tusoporteonline.es/blog

== Installation ==

1. Upload the `tso-tabs-widget` folder to `/wp-content/plugins/`.
2. Activate the plugin from the WordPress Plugins menu.
3. Go to **Appearance > Widgets** and add the "TSO Tabs Widget" widget to any sidebar.

== Frequently Asked Questions ==

= Is it compatible with LiteSpeed Cache? =
Yes. The visit counter runs via AJAX and does not interfere with full-page caching.

= How do I change the tab order? =
Expand the "Tab Order" section in the widget form and assign a number to each tab.

= Popular posts are not showing correctly. =
The visit counter increments via AJAX. After a fresh install you need new visits to accumulate data. Existing posts start from zero unless you import previous visit data.

= Are Catalan and Spanish translations included? =
Yes. Install the corresponding language pack from WordPress.org or place `.mo` files in the `languages` folder.

== Changelog ==

= 1.0.0 =
* Initial TSO Tabs Widget release for WordPress.org
* AJAX tabbed widget: Popular, Recent, Comments, Tags
* tsotab_ prefix, TSOTAB_ bootstrap, storage layer, enqueued assets
* Cache-friendly view counter via AJAX
* Nonces on all AJAX requests; PHP 7.4+ and WordPress 6.1+
* Languages: Catalan (ca_ES), Spanish (es_ES), English (en)

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Screenshots ==

1. Widget front-end view with Popular and Recent tabs active.
2. Widget settings form in the WordPress admin panel.
