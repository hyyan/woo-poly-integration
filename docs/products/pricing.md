# Pricing and currencies

Pricing is normally synchronised across all language versions of a product but can be turned off under [Settings, Metas](/settings/metadata.md).  It's not clear why you might want to turn this off, generally, Customers are annoyed if they discover that the price changes when switching language, so if you have a good use case please add it here.

## Sale price in one language only

Maybe you have a marketing campaign in one language only and you want to create some offers which only apply to one language?
If you turn off the sale price and final price meta synchronization [Settings, Metas](/settings/metadata.md) your sale prices will not be synchronized and so will only apply to the language they are entered.
This does add to maintenance complexity, it is simpler to leave on.
If you do want to make an offer applying in one language only, you can also do this with coupons, for example make a coupon that applies only to certain language-specific categories.


## Currencies

The ability to turn off price synchronisation is not intended to support different currencies for different languages.
It's not recommended to couple language to currency, since languages such as English, Spanish, French, Arabic are used in many countries with different currencies, and indeed many countries have speakers of multiple languages.  (Eg: Spanish speakers in USA, French speaking provinces of USA and Canada etc etc). 

### Currency Switchers

Currency switchers have different pricing, checkout, and caching behavior. Choose one based on your store's requirements and check its current documentation. The examples below do not guarantee compatibility with the current versions of those extensions; test your chosen combination on a staging site.
 
#### Cache compatibility note.
* Full HTML page caching can reduce server load, but currency-specific pages must not be served to customers using a different currency. Follow your currency switcher's cache configuration instructions.
* Automatic geolocation-based currency switching may not work (because no server-side PHP code is executed - that's why it's fast).  [Geolocation and automatic switch is also not entirely accurate or user friendly.]
* WooCommerce Geolocate (with page caching support) adds an extra url parameter to break the cache by geolocation hash, which also requires some PHP execution to initially get the user geolocation.  While this is "compatible" with caching, some benefits are lost, for example it would be difficult to pre-cache different versions of each page for all the possible geolocations.

#### [Currency Switcher for WooCommerce](https://wordpress.org/plugins/currency-switcher-woocommerce/) by WP Wham
* Check the current edition's currency limits and license on the plugin page.
* Tool has some nice options for Exchange Rate final price corrections
* Supports checkout currency options, including an option to return to the original currency at checkout. Check gateway availability with your payment provider.
* Product-level per-currency pricing can also be set, but is not currently synchronised across languages, there is a code snippet for that at the end of the [Product Meta page](/products/custom-fields.md).
* Check the installed version's cache requirements before enabling page caching.
* [Booster for WooCommerce](https://wordpress.org/plugins/woocommerce-jetpack/) is another extension mentioned in the original comparison. Its current features and compatibility must be checked separately.

#### [FOX Currency Switcher](https://wordpress.org/plugins/woocommerce-currency-switcher/) by realmag777, formerly WOOCS
* Free edition supports 1 additional currency.  
* For page caching, enable its **I am using cache plugin on my site** option and clear the page cache.
* Supports checkout in the selected currency. Check gateway and per-currency settings in the installed version.

#### [Aelia Currency Switcher for WooCommerce](https://aelia.co/shop/currency-switcher-woocommerce/)
* no Free edition
* Free cache plugin: WooCommerce Cache Handler (and Aelia Foundation Classes for WooCommerce which it requires) are free and enable a Cache Handler page within WooCommerce settings with an option for "Enable Ajax Loader"
* Check the vendor's current documentation for cache handling, per-currency payment gateways, and differential pricing.

#### [woocommerce.com Currency Converter Widget](https://woocommerce.com/products/currency-converter-widget/)
* no Free edition
* Uses JavaScript for display conversions. Check your cache setup with the widget enabled.
* limited features, front end indicative price only, checkout always in store base currency
* Currently maintained by Kestrel through the WooCommerce Marketplace.


### Localizing Currency Switchers

Most currency switchers do not implement localization, for example the number formats are fixed per currency without taking into account the current language/locale convention.

Here are some code snippet examples for localization:
* How to Synchronize extra product information to Product translations eg per-currency product pricing: see code snippet at the end of the [Product Meta page](/products/custom-fields.md).
* How to localize [WooCommerce Currency Switcher](https://wordpress.org/plugins/woocommerce-currency-switcher/): this code snippet will override the configured non-localized currency settings with localized settings, similar techniques may apply to other plugins:

```
add_filter( 'woocs_currency_data_manipulation', 'localize_currency_switcher', 10, 1);
/*
 * Localise initialization parameters for WooCommerce Currency Switcher, if installed:
 *  
 * 	    'USD' => array(
 *		'name' => 'USD',
 *		'rate' => 1,
 *		'symbol' => '&#36;',
 *		'position' => 'right',
 *		'is_etalon' => 1,
 *		'description' => 'USA dollar',
 *		'hide_cents' => 0,
 *		'flag' => '',
 */
function localize_currency_switcher($currencies){
    $woo_localized_descriptions = get_woocommerce_currencies();
    foreach ($currencies as $currency){
        $code = $currency['name'];

        //use preset description
        $description = $woo_localized_descriptions[$code];
        if ($description){
            $currencies[$code]['description']=$description . ' (' . $code . ')';
        }

        //localize position and hide_cents where possible
        $locale = pll_current_language('locale'); 
        $formatter = new \NumberFormatter($locale.'@currency='.$code,  \NumberFormatter::CURRENCY);
        if ($formatter){
            $symbol=$formatter->getTextAttribute(\NumberFormatter::CURRENCY_SYMBOL);
            if ($symbol){
                $currencies[$code]['symbol'] = $symbol;
            }
            
            $prefix=$formatter->getTextAttribute(\NumberFormatter::POSITIVE_PREFIX);
            $currencies[$code]['position'] = (strlen($prefix)) ? 'left' : 'right';

            $decimals = $formatter->getAttribute(\NumberFormatter::FRACTION_DIGITS);
            $currencies[$code]['hide_cents'] = ($decimals) ? false : true;
        }
        
    }
    return $currencies;
}
```
