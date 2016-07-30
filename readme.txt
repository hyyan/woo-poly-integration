=== Hyyan WooCommerce Polylang Integration===
Contributors: hyyan
Tags: cms, commerce, e-commerce, e-shop, ecommerce, multilingual, products, shop, woocommerce, polylang ,bilingual, international, language, localization, multilanguage, multilingual, translate, translation
Requires at least: 3.8
Tested up to: 4.5
Stable tag: 0.27
License: MIT
License URI: https://github.com/hyyan/woo-poly-integration/blob/master/LICENSE

Integrates Woocommerce With Polylang 

== Description ==

This plugin makes it possible to run multilingual e-commerce sites using
WooCommerce and Polylang.It makes products and store pages translatable, lets 
visitors switch languages and order products in their language. and all that from
the same interface you love.

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
- [√] Cart Synchronization `Without Variation Support`
- [√] Coupon Synchronization
- [√] Emails
- [√] Reports
  - [√] Filter by language
  - [√] Combine reports for all languages


= What you need to know about this plugin =

1. The plugin needs `PHP5.3 or above`
2. This plugin is developed in sync with [Polylang](https://wordpress.org/plugins/polylang) 
   and [WooCommerce](https://wordpress.org/plugins/woocommerce/) latest version
3. The plugin support variable products , but using them will `disallow you to 
   change the default language` , because of the way the plugin implements this
   support. So you have to make sure to choose the default language before you start
   adding new variable products.
4. Polylang URL modifications method `The language is set from content` is not 
   supported yet

= Setup your environment =

1. You need to translate woocommerce pages by yourself
2. The plugin will handle the rest for you

= Translations =

* Arabic by [Hyyan Abo Fakher](https://github.com/hyyan)

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

1. Add and translate products from the same interface you love
2. Products meta is synced , no need to do anything by your own
3. Orders use the customer chosen language 
4. Orders language can be changed 
5. Get reports in specific language and combine reports for all langauges
6. Control plugin features from its admin page 

== Changelog ==
= 0.27 =
* Updated [TranslationsDownloader](https://github.com/hyyan/woo-poly-integration/pull/32) to fetch languages files from woocommerce translation project
* Fixed Issue [#12 : Wrong Return URL after Payment](https://github.com/hyyan/woo-poly-integration/issues/12)
* Fixed Issue [#46 : PLugin is not activated when wordpress multisite is enabled ](https://github.com/hyyan/woo-poly-integration/issues/46)
* Fixed Issue [#26 : variation product and stock sync with language ](https://github.com/hyyan/woo-poly-integration/issues/26)
* Fixed Issue [#35 : Error Message: The plugin can not function correctly](https://github.com/hyyan/woo-poly-integration/issues/35)
* Fixed Issue [#16 : Catchable fatal error: Order List on Dashboard getProductTranslationByObject() is being given a Boolean instead of an WC_Product Object](https://github.com/hyyan/woo-poly-integration/issues/16)
* Fixed Issue [#42 : pll_get_post not defined error](https://github.com/hyyan/woo-poly-integration/issues/42)
* Fixed Issue [#43 : Call to undefined function Hyyan\WPI\pll_default_language()](https://github.com/hyyan/woo-poly-integration/issues/43)
* Fixed Issue [#44 : PLL()->model->get_translations is deprecated](https://github.com/hyyan/woo-poly-integration/issues/44)
* Fixed Issue [#45 : Fatal error: Call to undefined function Hyyan\WPI\pll_get_post_language()](https://github.com/hyyan/woo-poly-integration/issues/45)

= 0.25 =
* Add the ability to handle the locale code of Paypal checkout
* Fixed locale for emails that are triggered by a Paypal IPN message
* Fixed fields locker is not working in Firefox browser

= 0.24 =
* Added support for Layered Nav Widget
* Added support for endpoints translation
* Fixed products are duplicated when shop page is set as front page
* Fixed [Unable to open order details after 0.20 upgrade](https://wordpress.org/support/topic/unable-to-open-order-details-after-20-upgrade)
* Fixed translations links are not hidden in the order page
* Fixed email is not translated when complete button is used in orders table
* General code improvements

= 0.23 =
* Added support for Woocommerce search widget @see [Duplicated search result](https://wordpress.org/support/topic/duplicated-search-result)
* Fixed translation downloader tries to download woo translations for en_US locale
* Fixed wrong product duplicate behavior 

= 0.22 =
* Added Translation Downloader to auto download woocommerce translation files when a new polylang language is add
* Added Arabic translation
* Fixed translation links are hidden in posts page
* General code improvements

= 0.21 =
* Added admin interface to allow user to control plugin features
* Added link for every attribute to search for its translation in the polylang strings table
* Added generic fields locker
* Added POT file for translation
* Fixed product_type is not synced in 0.20 version
* General code improvements

= 0.20 =
* Added the ability to sync total_sales when stock value is changed
* Added the ability to combine product report with its translation
* Added the ability to combine category report with its translation
* Fixed database error in sales_be_category reports
* Fixed Orders Interface to use the current user language instead of the order language

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

== Upgrade Notice ==

= 0.20 =
The release includes many new features for reports , improve the support for emails , and include 
new bug fixes

= 0.25 =
The release includes important fixes , update immediately 