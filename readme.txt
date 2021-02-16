=== Hyyan WooCommerce Polylang Integration===
Contributors: hyyan, jonathanmoorebcsorg
Tags: cms, woocommerce, multilingual, polylang, bilingual, international, language, localization, multilanguage, multilingual, translate, translation
Requires at least: 5.4
Stable tag: 1.5.0
Tested Up To: 5.6.1
WC requires at least: 3.0.0
WC tested up to: 5.0
Requires PHP: 7.0
License: MIT
License URI: https://github.com/hyyan/woo-poly-integration/blob/master/LICENSE
 
Integrates Woocommerce With Polylang 

== Description ==

**Given that I am not using Wordpress these days and I haven't really been using WooPoly for a while. I am looking for maintainers to take over this project.
If you're interested, please reply to this [issue](https://github.com/hyyan/woo-poly-integration/issues/410) or get in touch with me via email: hyyanaf [at] gmail [dot] com**

---

This plugin makes it possible to run multilingual e-commerce sites using
WooCommerce and Polylang.It makes products and store pages translatable, lets 
visitors switch languages and order products in their language. and all that from
the same interface you love.

> Please do not ask for support on wordpress forum anymore , it is becoming hard for me to follow issues in different places. please if you want help just open a new Github issue.

[Read the full docs](https://github.com/hyyan/woo-poly-integration/wiki)

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

1. The plugin needs `PHP7.0 or above` ie same software versions as WooCommerce
2. This plugin is developed in sync with [Polylang](https://wordpress.org/plugins/polylang) 
   and [WooCommerce](https://wordpress.org/plugins/woocommerce/) latest version
3. The plugin support variable products , but using them will `disallow you to 
   change the default language` , because of the way the plugin implements this
   support. So you have to make sure to choose the default language before you start
   adding new variable products.
4. Polylang URL modifications method `The language is set from content` is not 
   supported

= Setup your environment =

1. You need to translate woocommerce pages by yourself
2. The plugin will handle the rest for you

= Translations =

* Arabic by [Hyyan Abo Fakher](https://github.com/hyyan)
* Spanish by [nunhes](https://github.com/nunhes)

= Contributing =

Everyone is welcome to help contribute and improve this plugin. There are several
ways you can contribute:

* Reporting issues (please read [issue guidelines](https://github.com/hyyan/woo-poly-integration/blob/master/.github/CONTRIBUTING.md))
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

== 1.5.0 ==

* correction to fix #527
* fixes #536 avoid over-setting product type on edit
* re- fixes #534 new variation not visible until save
* re- fixes #527 variation form default values
* fixes #535 variation stock issues

== 1.4.5 ==
* [re-fixes #430, #524 variation duplications](https://github.com/hyyan/woo-poly-integration/issues/476)
* [fixes #526 product type selector issues - variable product appears as simple](https://github.com/hyyan/woo-poly-integration/issues/526)
* [fixes #522, #529 and other JQuery deprecation issues props @mrleemon](https://github.com/hyyan/woo-poly-integration/issues/522)
* [fixes #475 variation stock issues](https://github.com/hyyan/woo-poly-integration/issues/475)
* [fixes #527 default form value for variation lost when creating new translation](https://github.com/hyyan/woo-poly-integration/issues/527)
* [fixes #534 new variable translation, variations not visible until save](https://github.com/hyyan/woo-poly-integration/issues/534)


== 1.4.4 ==
* [implements #476 adding Additional Content to translated email fields](https://github.com/hyyan/woo-poly-integration/issues/476)
* [Addresses multiple email issues causing some fields and default values not to be picked up or translated correctly.](https://github.com/hyyan/woo-poly-integration/wiki/Email-Languages)
* [fixes #502 Cart issues by removing addToCartHandlerVariable()](https://github.com/hyyan/woo-poly-integration/issues/502)
* [fixes #503 Deprecated: get_woocommerce_term_meta](https://github.com/hyyan/woo-poly-integration/issues/503)
* [fixes #466 undefined index in Cart.php on line 188 props nicmare sep 2019](https://github.com/hyyan/woo-poly-integration/issues/466)
* [addresses #449 by adding info to woo status report props jon007 sep 2019](https://github.com/hyyan/woo-poly-integration/issues/449)
* [fixes #446 by allow strings translation for customiser checkout text props jon007 jul 2019](https://github.com/hyyan/woo-poly-integration/issues/446)
* [implements #441 allow taxonomy slugs to be the same in different languages props jon007 jul 2019](https://github.com/hyyan/woo-poly-integration/issues/441)
* [fixes #442 incorrect defaults used where no saved settings exist  props jon007 jul 2019](https://github.com/hyyan/woo-poly-integration/issues/442)


== 1.4.3 ==
* [fixes #434 page checks duplicating pages and other language switching issues](https://github.com/hyyan/woo-poly-integration/commit/8c54fb23afad1bec1f98b2077d6f552646f253b8)
* [fixes #435 fix get_current_screen errors when this plugin called in unanticipated circumstances props danieleftodi YITH Gift Cards](https://github.com/hyyan/woo-poly-integration/commit/7d428208ff3b3a2472ce42f5a2259604ce2128c8)
* [fixes #436 Illegal string offset and Invalid argument in FlashMessages](https://github.com/hyyan/woo-poly-integration/commit/feedc68431bcd26c8d9bd2b4afacf4d7a86a62a1)


== 1.4.2 ==
* [fixes #432 default variation attributes were not synchronised correctly when using more than one attribute](https://github.com/hyyan/woo-poly-integration/commit/7101bcf0786e21515885d5796188420972b01831)
* [fixes #431 multiple email translation issues in different circumstances](https://github.com/hyyan/woo-poly-integration/issues/431)
* [fixes #430 bugfix and enhancement to allow variations to be disabled](https://github.com/hyyan/woo-poly-integration/issues/430)
* [stock synchronisation - avoid errors being logged where product is not translated](https://github.com/hyyan/woo-poly-integration/commit/08c9913105e6ad8183f7bbf35930f25b983d4c50)


== 1.4.1 ==
* [fixes #427 by reversal of #390, #346 Cart: retain price when switching languages due to incompatibility with currency switchers, instead a filter woo-poly.Cart.switchedItem allows customisation of behaviour where necessary](https://github.com/hyyan/woo-poly-integration/issues/427)


== 1.4.0 preview ==

**Thanks to :** 
**@jon007**
**@mweimerskirch **
**@Annih**
**@mrleemon**

* [fixes #408 WooCommerce 3.6.x: by ensuring product lookup data is recalculated for translations and stale product data cache is cleared](https://github.com/hyyan/woo-poly-integration/issues/408)
* [fixes #408 WooCommerce 3.6.x: cart fragment script updates for 3.6](https://github.com/hyyan/woo-poly-integration/commit/89ef0451dd8c7775fe48e20d71dc7836e8e49a96)
* [fixes #411, #299, #391, #361 stock synchronisation issues](https://github.com/hyyan/woo-poly-integration/commit/17e29b9927eb2fea3afbc3947d48e2b68e3d2185)
* [added WooCommerce pages checker: missing WooCommerce pages and translations are added, warning added for pages in incorrect status, addresses common support and setup usability issues such as #379](https://github.com/hyyan/woo-poly-integration/issues/379)
* [fixes #390, #346 Cart: retain price when switching languages props mweimerskirch](https://github.com/hyyan/woo-poly-integration/commit/ec272c0d921e39ebfd497fb2303454d7793bf60d)
* [fixes #364 initial setup: apply initial settings automatically](https://github.com/hyyan/woo-poly-integration/commit/217e33d2843fb82775a92a9d04cd2f7f73e436f6)
* [fixes #234 product admin: click on featured star in products list is now synchronised to translations](https://github.com/hyyan/woo-poly-integration/commit/3bbc1bf30ec90402a8796b16c6681037bd9e5125)
* [fixes #400 shop page url translation: fixes where shop slug is contained in url](https://github.com/hyyan/woo-poly-integration/issues/400)
* [fixes #416 emails: some missing custom string translations added](https://github.com/hyyan/woo-poly-integration/commit/16ad4c51edc8a9610d9039bc2e4953c801892b5e)
* [fixes #414 emails: v1.3.0 bug for translating email strings which are customized in admin](https://github.com/hyyan/woo-poly-integration/issues/414)
* [fixes #409 shortcodes: allow product shortcode to translate in same way as products shortcode](https://github.com/hyyan/woo-poly-integration/commit/2bf61574cf5b9ebb24ccf232de0ba92af879b31b)
* [fixes #419 variations: allow default variation based on custom product specific attribute](https://github.com/hyyan/woo-poly-integration/commit/a71263855dfa35915993ee7f8da8bf3232c46d53)


== 1.3.0 ==

* [fixes #352 by allowing translation of 'invisible' variations](https://github.com/hyyan/woo-poly-integration/commit/cd19fdd01cafcf065fbf21dfaa80dd167ce60e63)
* [fixes #353 by checking language of duplicate skis](https://github.com/hyyan/woo-poly-integration/commit/2a8f35955016485f07764e2fe7f6d33b6b1ceaae)
* [improves #353 fix](https://github.com/hyyan/woo-poly-integration/commit/1d83ef23e96f35c2bb008b5fa37e5157bfc388e4)
* [fixes #348 by assigning language to variations](https://github.com/hyyan/woo-poly-integration/commit/51898f96f2eaa24c322850564cb65923bafd7ddd)
* [fixes #343 by reducing frequency of coupon load](https://github.com/hyyan/woo-poly-integration/commit/edf0993e0a4e7961879e5a95c825da61e0e8b5a7)
* [fixes #354 by including updates from woocommerce](https://github.com/hyyan/woo-poly-integration/commit/42db06e024e794a6e971a515a5c86ce04df081ef)
* [fixes #356](https://github.com/hyyan/woo-poly-integration/commit/29f4895eb2e86c492904a6dcb9b600b850199f40)
* [Fall back to $polylang->pref_lang for AJAX endpoint URL when $polylang->curlang is not available.](https://github.com/hyyan/woo-poly-integration/commit/5a7fc8d704c9666675162d8eba8a53efe5540484)
* [fixed empty attribute for variations](https://github.com/hyyan/woo-poly-integration/commit/3db2c1091082c38bda20ca5c762ad97120bc25ca)
* [Add files via upload](https://github.com/hyyan/woo-poly-integration/commit/f89aec1e7e79987da8fcaec1b63295b3fa123fbc)
* [fixed #370](https://github.com/hyyan/woo-poly-integration/commit/08fe1432fb0981e727409c362c1bbb2892dcb601)
* [fixes #384](https://github.com/hyyan/woo-poly-integration/commit/772acc09640c90aa43b3106dd483ed40e84271c4)
* [Extend the email hooks](https://github.com/hyyan/woo-poly-integration/commit/df276b3184324bb905cdd7e3812bae220c799d02)
* [fixes #401 email subject and title translations](https://github.com/hyyan/woo-poly-integration/commit/89ca15df970ee410513200954f0993fcf0ab93f9)
* [fixes #399](https://github.com/hyyan/woo-poly-integration/commit/90a9e625ead7d6a4429fb4db51174367014df701)

== 1.2.0 ==

**Thanks for : @jon007** For his amazing work which made releasing this new version possible

* [Implements #337 by enabling privacy strings translation](https://github.com/hyyan/woo-poly-integration/commit/d9592822d4041ce299c04576e0a5d2bb1de4d646)
* [Implements #339 by translating store notice](https://github.com/hyyan/woo-poly-integration/commit/244aacfb4a6135beca73876cc856d1868834a9aa)

== 1.1.0 ==

**Thanks for :** 
**@jon007**
**@nunhes**
**@masato-igarashi**
**@marian-kadanka**
**@ahmedofali**

For all the amazing work which made releasing this new version possible

* [add wc version to comment headers](https://github.com/hyyan/woo-poly-integration/commit/756a380a0ef7ee425a55230f429462c471a62328)
* [fixes #283 by handling shortcode with no ids set](https://github.com/hyyan/woo-poly-integration/commit/19dd4b3e0afb12c9c1f73b45e45d2d1e95d1958d)
* [Add Spanish Translate](https://github.com/hyyan/woo-poly-integration/commit/730462cf01c91f7b61259105cce4e9c9bc2ba9b5)
* [fix issue #320](https://github.com/hyyan/woo-poly-integration/commit/5b0fae23200cf3c007ee712eeba43ee43059fdad)
* [Fix #325 Coupon optimization](https://github.com/hyyan/woo-poly-integration/commit/74f8620d55c006fa0650f63d0188a3d7f69c67e3)
* [fix issue #320 (re-commit)](https://github.com/hyyan/woo-poly-integration/commit/2b3c8972f0630ff89bc55d3d3e7caca69e09f006)
* [Merge pull request #322 from nunhes/master](https://github.com/hyyan/woo-poly-integration/commit/1cabc3b86a13861410e2ad4c4200e561e2e10b3f)
* [Update readme](https://github.com/hyyan/woo-poly-integration/commit/8113f35caf90c821204b14891e0b91448ac9bbfc)
* [Merge pull request #326 from masato-igarashi/master](https://github.com/hyyan/woo-poly-integration/commit/e3a8db6225ebc4bc82a78a3d55cffcb76ce47f88)
* [Fix #317](https://github.com/hyyan/woo-poly-integration/commit/c7eb1f786d9d6b0cdfaa224b0d4221b0f1666c05)
* [Fix Ajax endpoint URL again, fixes #319](https://github.com/hyyan/woo-poly-integration/commit/888df716c43c0191c32e2b8bfc11d7ea0a9ed055)
* [Identify woocommerce and polylang if installed as mu-plugins](https://github.com/hyyan/woo-poly-integration/commit/9e6dd41acccd7ec4a6329e937d1b39acb834c7af)
* [Change the way of checking polylang and woocommerce versions](https://github.com/hyyan/woo-poly-integration/commit/7772cff41d362c59610df1c3521f4b8be91713ff)
* [Remove Unneeded code since we use class name to check if plugin is activated or not](https://github.com/hyyan/woo-poly-integration/commit/be1417a2e346f8edfc690d845c451d7c78c42839)
* [Merge pull request #329 from marian-kadanka/fix-319-ajax-endpoint-url-again](https://github.com/hyyan/woo-poly-integration/commit/f9e7209a79ac818bf95d9816f72ccc82eefb532f)
* [Merge pull request #335 from ahmedofali/master](https://github.com/hyyan/woo-poly-integration/commit/70ce81c65e843e3050c282e4b661b04f74c93ac4)

== 1.0.4 ==

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

== 1.0.3 ==
* Fix PHP Fatal error: Class 'NumberFormatter' not found


== 1.0.2 ==
* Fixes #200 Polylang version check fails to detect Polylang PRO
* Cart.js Updated in line with WooCommerce 3.1 cart-fragments.js
* Fixes #215 add string translations for Coupons (includes WooCommerce Extended Coupon Features if installed)
* addresses #168 with a utility function get_translated_variation to help get translated products or variations
* Fixes #217 BACS bank_details() update for woocommerce3
* Fixes #213 copy children for Grouped Product
* partially implements #208 WooCommerce 3.1 CSV Import/Export by adding support for synchronising Product Meta and Product Attributes to translated products
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

== 1.0.1 ==

* Fixes #170 when WooCommerce 3.0.8+ active, product variation titles corrected in cart and orders
* Added Documentation links to new wiki Documentation pages
* Added minified javascript (enable SCRIPT_DEBUG to use unminified versions)
* Fixes #174 Error in autofill of missing translations of parent category
* Fixes #175 WooCommerce doesn't pass loop name for some shortcodes
* Fixes #10  Initial setup issues if Polylang is not yet configured

== 1.0.0 ==

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

* Fixes #123 Fields Locker performance optimisation 
* Fixes #155, fixes #81, fixes #99 Gateways fix gateway load issues by moving initialization to wp_loaded
* Fixes #149 Enable duplication of variable products
* Fixes #165 upsets/crosssells handling in wooCommerce3
* Fixes #159 Attribute Terms synchronization issues
* Fixes: #148 WooCommerce3 product_visibility is now a taxonomy not a meta item  
* Fixes: #153 Fields locker doesn't correctly lock Product Attributes of type Select 
* Fixes: #147 When adding new variations, tool should also add the new variation to other languages
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

== 0.29.1 ==

* Improve Multisite compatibility 
* Fix variation description couldnt be translated
* Fix PHP Notices when translating variable products with variations
* Fix wc translation download

= 0.29 =
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

= 0.28 =
* [Fixed order emails translation](https://github.com/hyyan/woo-poly-integration/pull/49)
* [Fixed shipment methods translation and added support for WooCommerce 2.6.](https://github.com/hyyan/woo-poly-integration/pull/50)
* [Fixed payment gateways translation](https://github.com/hyyan/woo-poly-integration/pull/52)
* [Added WC2.6 cart page ajax support](https://github.com/hyyan/woo-poly-integration/pull/53)
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

= 0.28 =
The release includes important fixes and updates for latest version of 
woocommerce and polylang , please update immediately 

= 0.29 =
The release includes important fixes and updates for latest version of 
woocommerce and polylang , please update immediately 

= 0.29.1 =
The release includes important fixes and updates for latest version of 
woocommerce and polylang , please update immediately 

= 1.0.0 =
The release is the first release for WooCommerce 3.x.
DO NOT install until you upgrade to WooCommerce 3.x.
If you are currently on WooCommerce 2.x, we recommend:
0. Take a backup. And do EVERYTHING in a test environment first.  Test. 
WooCommerce 3 has a large number of breaking changes which may and do break other plugins.
1. temporarily deactivate this plugin, 
2. upgrade WooCommerce and this plugin
3. run WooCommerce database upgrade following the wooCommerce prompt
4. reactivate this plugin

= 1.0.2 =
This release includes a change for Wordpress 4.8 compatibility and is tested with WooCommerce 3.1.
If you are using WooCommerce 3.x please update immediately.
