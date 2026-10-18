# Price localization

Price localisation should based on currency and locale: users in different places expect to see different currency strings.

For example, let's take an amount in Euros:
     $amount = '12345.678';
     $currency = 'EUR';

How users expect to see this depends on their locale: their location and language convention:
```
     Country  Locale  Format
     UK:      en_GB: €12,345.68
     Germany: de_DE: 12.345,68 €
     France:  fr_FR: 12 345,68 €
```
In PHP this is implemented by built-in libraries, as eg:
```
     $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
     echo $locale . ': ' . $formatter->formatCurrency($amount, $currency);
```

The approach taken by the plugin option Features, 'Use locale number formats' (since v1.0.2) is to use the configured language locale in Polylang to do the formatting.

Note that the decimal separator and thousands separator formatting is applied to other numeric values where possible as part of the localisation solution.

## Limitations

### Same language, different conventions.
Different countries use the same language with some different variations.  If you only have one locale version of the language in Polylang, there are some differences that would be missed.

For example:
* in Australia, Australian dollar is '$', so US Dollar is 'USD'.
* for American audience, '$' always means 'USD' so Australian dollar price should be marked differently eg "A$".
The same goes for Singapore Dollar, Hong Kong Dollar which all should be presented as $ in their local markets.

If you need to implement this, hook the WooCommerce `apply_filters( 'woocommerce_currency_symbol', $currency_symbol, $currency )` and apply your own detailed locale formatting.  (This needs a method for getting the detailed user locale, the exact method, eg from user selection / geolocation / browser settings / Request Accept-Language etc, which is out of scope of this plugin).


### WooCommerce limitations.

The tool localizes the formatting parameters used by WooCommerce where possible rather than completely replacing all number formatting within WooCommerce.

The WooCommerce parameters include:
* decimal_separator DECIMAL_SEPARATOR_SYMBOL
* thousand_separator (',' '.' or ' ')  GROUPING_SEPARATOR_SYMBOL
* decimals (number of decimal places or FRACTION_DIGITS)
* price_format (show currency symbol to left or right of number)

This means that the parameters do not include unusual digit groupings, multipliers etc.

### Environment

There could be limitations based on locales available in the server installation.
If so, code should fall back to standard WooCommerce / plugin configured behaviour.

## Alternatives 
This feature can be turned off, for example you may have a localisation solution based on user country information obtained from geolocation or other means.  Please feel free to use the code structure in LocaleNumbers to create your own solution.

## Compatibility
Currency-switcher compatibility depends on the extension and its version. See [Pricing](/products/pricing.md) for examples and test your chosen combination on a staging site.
