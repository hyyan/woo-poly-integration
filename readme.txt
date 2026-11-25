=== Hyyan Integration for WooCommerce and Polylang ===
Contributors: hyyan
Tags: woocommerce, multilingual, polylang, language, translation
Requires at least: 5.4
Stable tag: 1.7.0
Tested Up To: 7.1
WC requires at least: 3.0.0
WC tested up to: 11.1.2
Requires PHP: 8.3
License: MIT
License URI: https://github.com/hyyan/woo-poly-integration/blob/main/LICENSE

Connect WooCommerce and Polylang for a multilingual store.

== Description ==

Build a multilingual WooCommerce store with Polylang. Translate products and store pages, synchronize shared product data, and let customers shop in their chosen language.

Documentation: WordPress admin → Tools → WooPoly Documentation | Getting started: Settings → WooPoly | [Report an issue](https://github.com/hyyan/woo-poly-integration/issues)

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

The plugin requires WordPress 5.4+, PHP 8.3+, WooCommerce 3.0+, and Polylang 2.0+. Your installed dependencies may require newer WordPress or PHP versions.

Version 1.7.0 has been tested with WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10, and PHP 8.5.11. Older supported versions have not all been retested. Back up your store and test updates on a staging site first.

Choose your default language before creating variable products; changing it afterward is unsupported. Polylang's "The language is set from content" URL mode is also unsupported.

= Setup your environment =

1. Install and activate this plugin, WooCommerce, and Polylang in any order.
2. Open Settings > WooPoly. Follow the checklist to activate dependencies, choose languages, and create or link your store pages.
3. Translate product categories and attributes as needed, then create and link product translations.
4. Test product selection, stock, cart, checkout, and emails in every store language before going live.

= A note from Hyyan =

I started WooPoly in 2015 to help shop owners sell in more than one language. WooPoly has been free from day one. My commitment is to keep it free and open source.

If WooPoly has helped you run your shop, would you [star it on GitHub](https://github.com/hyyan/woo-poly-integration)? It lets me know the work is useful. If you prefer WordPress.org, [a review](https://wordpress.org/support/plugin/woo-poly-integration/reviews/#new-post) about what worked for your store helps the next shop owner decide. Thank you for giving WooPoly a place in your shop.

[Share WooPoly on LinkedIn](https://www.linkedin.com/feed/?shareActive=true&text=%40Hyyan%20Abo%20Fakher%20created%20WooPoly%20in%202015%20to%20connect%20WooCommerce%20with%20Polylang.%20With%20WooPoly%2C%20a%20shop%20can%20list%20the%20same%20product%20in%20several%20languages%20and%20keep%20one%20stock%20count%20across%20all%20its%20translations.%0A%0AHyyan%20made%20WooPoly%20free%20and%20open%20source%20from%20day%20one%20and%20has%20pledged%20to%20keep%20it%20that%20way.%20If%20you%20know%20someone%20building%20a%20multilingual%20store%2C%20pass%20this%20along.%0A%0Ahttps%3A%2F%2Fwordpress.org%2Fplugins%2Fwoo-poly-integration%2F).

— [Hyyan Abo Fakher](https://www.linkedin.com/in/hyyan/)

= Translations =

* Arabic by [Hyyan Abo Fakher](https://github.com/hyyan)
* Spanish by [nunhes](https://github.com/nunhes)

= Contributing =

Everyone is welcome to help contribute and improve this plugin. There are several
ways you can contribute:

* Reporting issues (please read [issue guidelines](https://github.com/hyyan/woo-poly-integration/blob/main/.github/CONTRIBUTING.md))
* Suggesting new features
* Writing or refactoring [code](https://github.com/hyyan/woo-poly-integration)
* Improving the bundled documentation
* Fixing [issues](https://github.com/hyyan/woo-poly-integration/issues)

== Installation ==

= Standard install =

In your site Admin, go to Plugins, Add New and search for Hyyan WooCommerce Polylang Integration and install it.

= Classical way =

1. Download the plugin as zip archive and then upload it to your wordpress plugins folder and
extract it there.

Activate the plugin, then open Settings > WooPoly. Its setup checklist guides you
through WooCommerce, Polylang, your languages, and translated store pages.

Please note the getting started notes:
In WordPress, open Settings → WooPoly.


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
= 1.7.0 =

* feat: filter WooCommerce REST products by language
* feat: filter WooCommerce REST product collections by language
* feat: translate WooCommerce products and metadata with DeepL
* feat: integrate the DeepL workflow with the bundled product guide
* fix: register the DeepL key in WooPoly feature settings
* fix: configure the store DeepL key in WooPoly settings
* feat: manage personal DeepL keys in the product editor
* feat: create linked WooCommerce product drafts with DeepL
* fix: preserve paragraph breaks in LinkedIn share links
* feat: add maintainer pledge and project sharing to WooPoly
* feat: bundle the WooPoly guide in WordPress admin
* fix: preserve adjusted prices on translated cart rows
* Remove the translated cart discount documentation
* Explain discounted row subtotals in translated carts
* Preserve adjusted cart prices on translated product rows
* Explain setup progress and how to resume the checklist
* Guide merchants through multilingual store setup
* Merge pull request #572 from hyyan/dependabot/npm_and_yarn/multi-4e2dc1f270
* fix: publish complete release changelog in WordPress readme

[Full changelog](https://github.com/hyyan/woo-poly-integration/blob/main/CHANGELOG.md).

== Upgrade Notice ==

= 1.7.0 =
Review the changelog and back up your store before updating.