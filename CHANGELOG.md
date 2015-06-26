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
