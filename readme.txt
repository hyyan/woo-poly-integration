=== Hyyan Integration for WooCommerce and Polylang ===
Contributors: hyyan
Tags: woocommerce, multilingual, polylang, language, translation
Requires at least: 5.4
Stable tag: 1.6.0
Tested Up To: 7.1
WC requires at least: 3.0.0
WC tested up to: 11.1.2
Requires PHP: 7.0
License: MIT
License URI: https://github.com/hyyan/woo-poly-integration/blob/main/LICENSE

Connect WooCommerce and Polylang for a multilingual store.

== Description ==

Build a multilingual WooCommerce store with Polylang. Translate products and store pages, synchronize shared product data, and let customers shop in their chosen language.

[Documentation](https://github.com/hyyan/woo-poly-integration/wiki) | [Getting started](https://github.com/hyyan/woo-poly-integration/wiki/Getting-Started) | [Report an issue](https://github.com/hyyan/woo-poly-integration/issues)

For bug reports and feature requests, please use GitHub issues and include your plugin versions and steps to reproduce. Remove private data from any logs you share.

= Features  =

- [√] Auto Download Woocommerce Translation Files
- [√] Page Translation
- [√] Endpoints Translation
- [√] Product Translation
- [√] Categories
- [√] Tags
- [√] Attributes
- [√] Shipping Classes
- [√] Meta Synchronization
- [√] Variation Product
- [√] Product Gallery
- [√] Order Translation
- [√] Stock Synchronization
- [√] Cart Synchronization
- [√] Coupon Synchronization
- [√] Emails
- [√] Reports
- [√] Filter by language
- [√] Combine reports for all languages


= What you need to know about this plugin =

The plugin declares WordPress 5.4+, PHP 7.0+, WooCommerce 3.0+, and Polylang 2.0+. Your installed dependencies may require newer PHP versions.

Version 1.6.0 has been tested with WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10, and PHP 8.5.11. Older supported versions have not all been retested. Back up your store and test updates on a staging site first.

Choose your default language before creating variable products; changing it afterward is unsupported. Polylang's "The language is set from content" URL mode is also unsupported.

= Setup your environment =

1. Configure WooCommerce, Polylang, and your store languages before activating this plugin.
2. Review Settings > WooPoly, translate your store pages, and check their language assignments.
3. Translate product categories and attributes as needed, then create and link product translations.
4. Test product selection, stock, cart, checkout, and emails in every store language before going live.

= Keep in touch =

Created by Hyyan Abo Fakher. If WooPoly is useful to you, a [star on GitHub](https://github.com/hyyan/woo-poly-integration) helps others discover it. You are also welcome to follow my work on [GitHub](https://github.com/hyyan) or [LinkedIn](https://www.linkedin.com/in/hyyan/).

= Translations =

* Arabic by [Hyyan Abo Fakher](https://github.com/hyyan)
* Spanish by [nunhes](https://github.com/nunhes)

= Contributing =

Everyone is welcome to help contribute and improve this plugin. There are several
ways you can contribute:

* Reporting issues (please read [issue guidelines](https://github.com/hyyan/woo-poly-integration/blob/main/.github/CONTRIBUTING.md))
* Suggesting new features
* Writing or refactoring [code](https://github.com/hyyan/woo-poly-integration)
* Improving [documentation](https://github.com/hyyan/woo-poly-integration/wiki)
* Fixing [issues](https://github.com/hyyan/woo-poly-integration/issues)

== Installation ==

= Standard install =

In your site Admin, go to Plugins, Add New and search for Hyyan WooCommerce Polylang Integration and install it.

= Classical way =

1. Download the plugin as zip archive and then upload it to your wordpress plugins folder and
extract it there.

= Composer way =

1. run composer command : ``` composer require hyyan/woo-poly-integration```

In all cases please do ensure you have Polylang and WooCommerce activated and setup
before you Activate this plugin from your admin panel

Please note the getting started notes:
https://github.com/hyyan/woo-poly-integration/wiki/Getting-Started


== Frequently Asked Questions ==

= Does this work with other e-commerce plugins ? =

No. This plugin is for polylang and woocommerce

= Does this work with WPML plugin? =

No. This plugin is for polylang and woocommerce

= What do I need to do in my theme =

No special theme changes are normally required. Test your theme and any checkout extensions on a staging site.

= Products Category or tags pages are blank =

Check the language assignments and translations for your terms, then save Settings > Permalinks. If the problem persists, report the steps to reproduce on GitHub.


== Screenshots ==

1. Add and translate products from the same interface you love
2. Products meta is synced , no need to do anything by your own
3. Orders use the customer chosen language
4. Orders language can be changed
5. Get reports in specific language and combine reports for all langauges
6. Control plugin features from its admin page

== Changelog ==
= 1.6.0 =

* fix: initialize CI fixtures and guard deferred product saves
* fix: secure variation removal and synchronize parent prices
* fix: complete secure plugin maintenance
* fix: preserve variation relationships during stock updates
* fix: reject integer overflow before variation operations
* fix: restrict variation stock and coupons to variations
* fix: verify custom report requests before translation
* fix: centralize native report language selection
* fix: centralize attribute language request fallback
* fix: verify translation language and metadata setup
* fix: verify product editor translation requests
* fix: preserve catalogs with native version aware loading
* fix: use native settings context for endpoint notices
* fix: own message domains and retain WooCommerce translations
* fix: number translation formats and preserve legacy catalogs
* fix: resolve coupon screens and verify translation warning bypass
* fix: verify creation callbacks and resolve attribute screens
* fix: expose verified product creation requests to security checks
* fix: identify plugin hook constants with their owning prefix
* fix: translate stored checkout text through Polylang
* fix: preserve cart stock checks on WooCommerce 3.0
* fix: invalidate cached product lookups on native changes
* fix: normalize request IDs through validated sanitizers
* fix: retain published WooCommerce minimum requirement
* fix: use compliant plugin display name in release headers
* fix: reuse exact variation relationships across lookup paths
* fix: match normalized variation relationships exactly
* fix: verify early product translation creation requests
* fix: validate registered languages across request filters
* fix: defer product type metadata backfill until save
* fix: remove obsolete product type request callbacks
* fix: prefix plugin callbacks and sharing variables
* fix: verify stock edit requests and use native diagnostics
* fix: verify category requests and normalize request identifiers
* fix: sanitize admin notices and translation error output
* fix: preserve language filtered reports under HPOS
* fix: translate configured block and receipt payment titles
* fix: translate configured native tax rate labels
* fix: preserve native translated store notice controls
* fix: preserve variation copy and import synchronization
* fix: preserve independent product attributes during copies
* fix: restore stock synchronization callbacks after errors
* fix: resolve validated translated variation deletion sources
* fix: replace only complete translated shop path segments
* fix: retain product defaults and reload locale catalogs
* fix: restore caller language after translated emails
* fix: preserve translated cart state and validation
* fix: preserve translated checkout receipt endpoints
* fix: preserve translated account endpoints when switching languages
* fix: preserve native block store page translations
* fix: exclude development files from release packages
* fix: remove automatic third-party admin resources
* fix: preserve native URLs and restore variation timeouts
* fix: protect stock synchronization across request contexts
* fix: correct translation calls and preserve catalog contracts
* fix: preserve literal SKU checks and secure coupon diagnostics
* fix: secure settings output and restrict page maintenance
* fix: secure inline script data and preserve editor restrictions
* fix: validate navigation filters and current URL inputs
* fix: validate product synchronization request inputs
* fix: preserve Polylang post fields during product import
* fix: validate language and translation request boundaries
* fix: escape translated template and gateway output
* fix: guard executable files outside WordPress
* fix: escape status report locale and path output
* fix: verify translation download certificates
* fix: encode category translation script values
* fix: load the cookie API required by cart fragments
* fix: preserve absent Polylang language for unknown locales
* fix: translate account email templates
* fix: preserve block checkout order language
* fix: use WordPress locale when checkout language is unavailable
* fix: load rest of world shipping methods
* fix: restore translated shortcodes on PHP 8
* fix: preserve product search language links
* fix: authorize translated featured product toggles
* fix: preserve empty values during decimal localization
* fix: validate and authorize linked variation removal
* fixes #568

[Full changelog](https://github.com/hyyan/woo-poly-integration/blob/main/CHANGELOG.md).

== Upgrade Notice ==

= 1.6.0 =
Review the changelog and back up your store before updating.