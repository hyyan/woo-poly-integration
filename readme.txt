=== Hyyan WooCommerce Polylang Integration===
Contributors: hyyan
Tags: cms, commerce, e-commerce, e-shop, ecommerce, multilingual, products, shop, woocommerce, polylang ,bilingual, international, language, localization, multilanguage, multilingual, translate, translation
Requires at least: 3.8
Tested up to: 4.2
Stable tag: 0.20
License: MIT
License URI: https://github.com/hyyan/woo-poly-integration/blob/master/LICENSE

Integrates Woocommerce With Polylang 

== Description ==

This plugin makes it possible to run multilingual e-commerce sites using
WooCommerce and Polylang . It makes products and store pages translatable, lets 
visitors switch languages and order products in their language. and all that in 
same interface you love.

= Features  =

- [√] Page Translation
- [√] Product Translation
  - [√] Categories
  - [√] Tags
  - [√] Attributes
  - [√] Shipping Classes
  - [√] Meta Synchronization
  - [√] Variation Product
  - [√] Product Gallery
- [√] Order Translation
- [√] Cart Synchronization `Without Variation Support`
- [√] Coupon Synchronization
- [√] Emails
- [√] Reports
  - [√] Filter by language
  - [ ] Combine reports for all languages


= What you need to know about this plugin =

1. The plugin stills in development , so you might find bugs
2. The plugin doesn't implement full integration yet (Working on it)
3. The plugin support variable products , but using them will `disallow you to 
  change the default language` , because of the way the plugin implements this
  support. So you have to make sure to choose the default language before start
  adding new variable products.

= Setup your environment =

* Make sure to setup your woocommerce permalinks correctly
* You need to translate woocommerce pages by yourself
* The plugin will handle the rest for you

= Contributing =

Everyone is welcome to help contribute and improve this plugin. There are several
ways you can contribute:

* Reporting issues (please read [issue guidelines](https://github.com/necolas/issue-guidelines))
* Suggesting new features
* Writing or refactoring code
* Fixing [issues](https://github.com/hyyan/woo-poly-integration/issues)

== Installation ==

= Classical way =

1. Download the plugin as zip archive and then upload it to your wordpress plugins folder and
extract it there.
2. Activate the plugin from your admin panel

= Composer way =

1. run composer command : ``` composer require hyyan/woo-poly-integration```

== Frequently Asked Questions ==

= Does this work with other e-commerce plugins ? =

No. This plugin is for polylang and woocommerce

= Does this work with WPML plugin? =

No. This plugin is for polylang and woocommerce

= What do I need to do in my theme =

Well , Nothing 

= Products Category or tags pages are blank =

Just make sure to setup your permalinks , and every thing will be fine , I promise


== Screenshots ==

1. Adding and translating products from the same interface you love
2. Orders can capture the user language 

== Changelog ==

= 0.20 =
* Added the ability to sync total_sales when stock value is changed
* Fixed database error in sales_be_category reports

= 0.19 =
* Added the ability to set the write permalinks that can work with polylang if the default woocomerce permalinks are used

= 0.18 =
* Added basic support for reports (filter by language)
* General fixes

= 0.17.2 =
* Fixed issue#2 (https://github.com/hyyan/woo-poly-integration/issues/2)

= 0.17.1 =
* Removed wrong php used statement

= 0.17 =
* Fixed (Polylang language switcher is disabled even if there is no variable products)
* Added the ability to sync product category custom fields

= 0.16 =
* Added support for product gallery translation

= 0.15 =
* Extended meta list to include _visibility

= 0.14 =
* Released in the wordpress repository
