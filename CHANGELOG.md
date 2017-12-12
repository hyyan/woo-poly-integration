# Changelog

### 1.0.4

* [Fix #257 , Fix #247](https://github.com/hyyan/woo-poly-integration/commit/9eaf0cabdf25425221c230d4459d26ea82c84605)
* [Fix #248 , Fix #266 upgrader_process_complete is not ideal](https://github.com/hyyan/woo-poly-integration/commit/01bc5b2d3df1c08fa4465c585721b7fbf28ed32e)
* [Merge pull request #253 from marian-kadanka/fix-is-front-page-conditional-tag](https://github.com/hyyan/woo-poly-integration/commit/13409a5ea2c1ec7eef252670879d8048a9207ff7)
* [Update tested wp version](https://github.com/hyyan/woo-poly-integration/commit/2837e83a97c9c68f96ce06ea3f23e459bdf6ea82)
* [Fix #260 - add the "Requires PHP" tag in the readme.txt](https://github.com/hyyan/woo-poly-integration/commit/6c73f8a1c4e4b94ffeec2e35e05a57446d4706b8)
* [fixes #268 setup coupon translations only when needed](https://github.com/hyyan/woo-poly-integration/commit/d192347e20d1f4370372276ab55ee77020ad35a0)
* [Merge pull request #263 from szepeviktor/patch-1](https://github.com/hyyan/woo-poly-integration/commit/4fd73ee17ecbbf0710247a98731a096d3c9d8db6)
* [Fix is_front_page() not working on WC shop page set as site's static front page](https://github.com/hyyan/woo-poly-integration/commit/2dc44a136bbef665cad4cd000894b3192e4b5332)
* [Fix Ajax endpoint URL](https://github.com/hyyan/woo-poly-integration/commit/9f9b7581260c87f84ce9f35100ecd872b974e58d)
* [Fix #247 - Woocommerce [products] shortcode and Polylang](https://github.com/hyyan/woo-poly-integration/commit/c18a2735173d62f631d6cdd3679fc68d0786a682)

### 1.0.3

* Fix PHP Fatal error: Class 'NumberFormatter' not found

### 1.0.2
* Fixes #200 Polylang version check fails to detect Polylang PRO
* Cart.js Updated in line with WooCommerce 3.1 cart-fragments.js
* Fixes #215 add string translations for Coupons (includes WooCommerce Extended Coupon Features if installed)
* addresses #168 with a utility function get_translated_variation to help get translated products or variations
* Fixes #217 BACS bank_details() update for woocommerce3
* partially implements #208 WooCommerce 3.1 CSV Import/Export by adding support for synchronising Product Meta and Product Attributes to translated products
* Fixes #213 copy children for Grouped Product
* Fixes #207 suppresses login customization to allow "Pay for Order" links to work when customer is not logged in 
(after login continue to payment page instead of my account home)
* Fixes #212 update deleteRelatedVariation for woocommerce3 warnings
* Fixes #209 cart filling up error logs with variations message

* Fixes #195 Locale number formatting for prices and built-in attributes
* Fixes #190 Products Quick Edit now synchronizes translations
* Fixes #187 Wordpress 4.8 breaks translation for new Variation Products
* Fixes #184 Stock update incorrect if customer switches language while checking out 
* Fixes #186 Shipping Method translation regression from 1.0.1
* Fixes #188 When translating Variations, code tries to create copies of untranslated terms
* Fixes #182 email translation extension hooks thanks to @vendidero/WooCommerce Germanized
* Fixes #181 additional filters in Meta synchronization thanks to @vendidero/WooCommerce Germanized

### 1.0.1

* Fixes #170 when WooCommerce 3.0.8+ active, product variation titles corrected in cart and orders
* Added Documentation links to new wiki Documentation pages
* Added minified javascript (enable SCRIPT_DEBUG to use unminified versions)
* Fixes #174 Error in autofill of missing translations of parent category
* Fixes #175 WooCommerce doesn't pass loop name for some shortcodes
* Fixes #10  Initial setup issues if Polylang is not yet configured

### 1.0.0

**Thanks for @jon007 and @decarvalhoaa for the amazing work in order to release this new version**

This release fixes a number of issues around handling of attributes and translations.
In particular:

1. New translations can now use auto-copy of source language, to help save time translating.
   In future a machine translation will be added.
	 Copy option covers Product Title, Short Description and Long Description.
   Also when creating a new product, any missing Product Categories, Tags and Attributes are copied,
   to avoid unexpected problems which occur if a translation is saved with missing term translations.

2. it is now possible to set up the system to allow different types of product attributes
to be synchronised, translated, or independent in each language. The default options will be:
 - Translation and Synchronization Enabled for Product Attributes
 - Synchronization off for Custom Product Attributes

In this case choose how to set up your product attributes as follows:
 - Translated Attribute?  Add in Products\Attributes and turn on Translation in Polylang at:
        Languages\Settings\Custom Taxonomies 
 - Synchronised Attribute? [eg same value in all languages, eg product code, numeric properties] 
				Add in Products\Attributes and leave Translation turned off in Polyang.
 - Different value in each language? add directly to Product as a Custom Product Attribute

* Enh: synchronisation for Custom Product Attributes and Global Product Attributes can now be 
       turned on and off independently in 
			 Settings\WooPoly, Metas List, Attributes Metas, Custom Product Attributes.
       The fields locker is unlocked for the attribute types which are not synchronized.
* Fix: Global Product Attributes can now be individually configured in Polylang:  
			 When Settings\WooPoly Translation attributes is checked then attributes appear in Polylang:
       Languages\Settings\Custom Taxonomies lists the individual taxonomies 
			 Previously all attributes translation were forced on: now they can be selectively turned
       on and off.  This means that there is no longer any need to create dummy translations for 
       untranslateable values such as reference codes and numeric fields.  Fixes #127.
CHANGE: new Product Attributes are no longer automatically enabled for translation,
       After creating new Attribute, enable Translation in Polylang if needed by checking:
			 Languages\Settings\Custom Taxonomies 
* Enh: Missing Term Translations are now added by default. Fixes #72
			 Applies to Products\Categories, Products\Tags, Products\Attributes 
       Previously missing term translations caused
* Fixes: #148 WooCommerce3 product_visibility is now a taxonomy not a meta item  
* Fixes: #153 Fields locker doesn't correctly lock Product Attributes of type Select 
* Fixes: #147 When adding new variations, tool should also add the new variation to other languages
* Fixes #149 Enable duplication of variable products
* Fix #137 #131 #130 #110 #117, #97, #94, #84, #83, #82 adaptations for wooCommerce 3.0
* Fix #136 Variable product stock sync issue where stock managed at parent level
* Enh #132 Add settings Page link to plugins page
* Fix #128 Allow variation description to be editable in translations
* Fix #129 #138 Account page only shows orders in current language
* Fix #112 Shipping Class are not sync for Product Variations
* Fix #140, #142, #143, #89, #70 Email Translation issues
* Fix #145 correct link from Polylang to Attributes Strings translations
* Fix #95 WooCommmerce product shortcodes not filtering by language
* Fix #104 Tax by allowing translation of Price Display Suffix


### 0.29.1

* Improve Multisite compatibility 
* Fix variation description couldnt be translated
* Fix PHP Notices when translating variable products with variations
* Fix wc translation download

###0.29

* Fix PHP notice in Reports when products dont have translations
* Fixed PHP notice due to Polylang deprecated functions
* Fixed Wordpress database error in reports page
* Fixed missing argument warning in order detailspage and emails
* Fixed strpos() empty needle warning for empty endpoints
* Fixed removing email instructions from 3rd party payment gateways
* Fixed not detecting whether polylang pro is active
* Fixed unable to unselect a complete settings section [Issue #51](https://github.com/hyyan/woo-poly-integration/issues/51)
* Fixed Fields Locker is not working in variation tab [Issue #76](https://github.com/hyyan/woo-poly-integration/issues/76)
* Tested and confirmed working on WordPress 4.6.1, Polylang 2.0.7 and WooCommerce 2.6.7

###0.28

* [Fixed order emails translation](https://github.com/hyyan/woo-poly-integration/pull/49)
* [Fixed shipment methods translation and added support for WooCommerce 2.6.](https://github.com/hyyan/woo-poly-integration/pull/50)
* [Fixed payment gateways translation](https://github.com/hyyan/woo-poly-integration/pull/52)
* [WC2.6 cart page ajax support](https://github.com/hyyan/woo-poly-integration/pull/53)
* [Fixed backend html orders screen](https://github.com/hyyan/woo-poly-integration/pull/55)
* [Fixed product type dropdown selection](https://github.com/hyyan/woo-poly-integration/pull/56)
* [Fixed translation of products variations created before plugin activation](https://github.com/hyyan/woo-poly-integration/pull/60)
* [Fixed variable products default attributes sync](https://github.com/hyyan/woo-poly-integration/pull/61)
* [Fixed variable products (non-taxonomies) attributes sync](https://github.com/hyyan/woo-poly-integration/pull/62)
* [Fixed product shipping class for websites running WooCommerce 2.6 or higher](https://github.com/hyyan/woo-poly-integration/pull/63)
* [Fixed cart translation](https://github.com/hyyan/woo-poly-integration/pull/64)
* [Fixed coupons with multiple products](https://github.com/hyyan/woo-poly-integration/pull/65)
* [Fixed coupon with multiple products](https://github.com/hyyan/woo-poly-integration/pull/66)
* Tested and confirmed working on WordPress 4.6.1 and Polylang 2.0.4

###0.27

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


###0.26 (Not Released In Wordpress.org)

* Fixed product duplication in shop page when default language is changed
* Fixed total sales is syned even if product is not managing stock
* Fixed duplicator class PHP notice when product is being edited in quick mode
* Fixed random behaviour for product type sync 
* Fixed tax class are not synced

###0.25

* Add the ability to handle the locale code of Paypal checkout
* Fixed locale for emails that are triggered by a Paypal IPN message
* Fixed fields locker is not working in Firefox browser

###0.24

* Added support for Layered Nav Widget
* Added support for endpoints translation
* Fixed products are duplicated when shop page is set as front page
* Fixed [Unable to open order details after 0.20 upgrade](https://wordpress.org/support/topic/unable-to-open-order-details-after-20-upgrade)
* Fixed translations links are not hidden in the order page
* Fixed email is not translated when complete button is used in orders table
* General code improvements

###0.23

* Added support for Woocommerce search widget @see [Duplicated search result](https://wordpress.org/support/topic/duplicated-search-result)
* Fixed translation downloader tries to download woo translations for en_US locale
* Fixed wrong product duplicate behavior 

###0.22

* Added Translation Downloader to auto download woocommerce translation files when a new polylang language is add
* Added Arabic translation
* Fixed translation links are hidden in posts page
* General code improvements

###0.21

* Added admin interface to allow user to control plugin features
* Added link for every attribute to search for its translation in the polylang strings table
* Added generic fields locker
* Added POT file for translation
* Fixed product_type is not synced in 0.20 version
* General code improvements

###0.20

* Added the ability to sync total_sales when stock value is changed
* Added the ability to combine product report with its translation
* Added the ability to combine category report with its translation
* Fixed database error in sales_be_category reports
* Fixed Orders Interface to use the current user language instead of the order language

###0.19

* Added the ability to set the write permalinks that can work with polylang if the default woocomerce permalinks are used

###0.18

* Added basic support for reports (filter by language)
* General fixes

###0.17.2

* Fixed issue#2 (https://github.com/hyyan/woo-poly-integration/issues/2)

###0.17.1

* Removed wrong php used statement

###0.17

* Fixed (Polylang language switcher is disabled even if there is no variable products)
* Added the ability to sync product category custom fields

###0.16

* Added support for product gallery translation

###0.15

* Extended meta list to include _visibility

###0.14

* Made ready to release to wordpress

###0.13

* Coupons support variation product

###0.12

* Added support for emails

###0.11.2

* Added forgotten return statement

###0.11.1

* Corrected query used to get the orders list in my account page

###0.11

* Improved support for product variations 
* Imporved support for orders

###0.10

* Added the ability to handle shop page translation

###0.9

* Refactoring Code 
* Added support for variation products


###0.8

* Refactoring Code 
* General Improvements

###0.7

* Added support for attributes translation

###0.6

* Added support for order translation

###0.5

* Added support for coupon translation

###0.4

* General improvements for product meta sync

###0.3

* added porduct restoreStockQuantity function

###0.2

* Added product meta and stock sync support

###0.1

* Initial commit
