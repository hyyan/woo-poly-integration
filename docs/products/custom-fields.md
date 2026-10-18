# Custom fields

## Variations and custom fields note

```
/*
 * stop synchronisation of field
 */
add_filter( 'woo-poly.product.disabledMetaSync', 'dontSyncMyCustomField', 10, 1 );
function dontSyncMyCustomField( $fieldsNotToSync ) {
	$fieldsNotToSync[] = 'my_field';
	return $fieldsNotToSync;
}
/*
 * stop locking of field
 */
add_filter( 'woo-poly.fieldsLockerVariableExcludeSelectors', 'dontLockMyCustomField', 10, 1 );
function dontLockMyCustomField( $selectorsNotToLock ) {
	$selectorsNotToLock[] = '[name^="my_field"]';
	return $selectorsNotToLock;
}

```
there are two separate concepts here, whether the field is locked or not, and whether the field is synchronized or not.

just change my_field with the real name of your field

## Standard products and Polyang meta synchronisation
Currently this plugins uses Polylang post meta synchronisation except for variable products.
This may change in the future as everything woocommerce now should be done using the woocommerce api rather than the wordpress post and post meta apis.

woo-poly-integration [Meta settings](/settings/metadata.md) extends Polylang Meta synchronisation to cover the WooCommerce Meta information for Products _as provided by a standard WooCommerce installation_.

Additional plugins may extend Products to add additional information which is not handled by woo-poly.
The basic control to allow synchronisation of additional meta is in Polylang, see Languages, Settings, Synchronisation, Custom fields: 
![Related Polylang settings](../../assets/polylang-settings-sync.gif)

If this is enabled, then Polylang does the following:

- if the field name (meta_key) starts with `'_'` underscore character, the field is not synchronised
- otherwise the field is synchronised.

Not sure which meta keys you need to check?
3 ways to tell:

1. Inspect the input field in the Product Edit window, the name="" attribute will normally help identify the meta_key
2. If you have database access, inspect the product's rows in the `postmeta` table using your installation's table prefix (normally `wp_postmeta`). In the product edit URL, `?post=1234&action=edit` identifies product ID `1234`. Do not edit database rows directly.
3. check the code, debug

Here is an example of changing the Polylang default behaviour so some fields which are not normally synchronised to translated Products can be synchronised.

Currency Switcher for WooCommerce By Algoritmika Ltd adds some additional meta at product level if you set prices in other currencies at the product level, they will be saved like this  in wp_postmeta under meta keys such as:
```
_alg_currency_switcher_per_product_regular_price_EUR
_alg_currency_switcher_per_product_sale_price_EUR
_alg_currency_switcher_per_product_regular_price_GBP
_alg_currency_switcher_per_product_sale_price_GBP
``` 

Since these fields start with `'_'` they will not normally be synchronised by Polylang.
To change this we can use a Polylang filter like this:

```
/**
 * Polylang meta filter, if true meta item will not be synchronized.
 *
 *
 * @param string      $meta_key Meta key
 * @param string|null $meta_type
 * @return bool True if the key is protected, false otherwise.
 */
function allowSyncCurrencyMeta($protected, $meta_key, $meta_type)
{
    $meta_prefix = '_alg_currency_switcher_per_product_';
    $length = strlen($meta_prefix);
    if (substr($meta_key, 0, $length) === $meta_prefix){
        return false;
    } else {
        return $protected;
    }
}
add_filter( 'is_protected_meta', 'allowSyncCurrencyMeta', 10, 3);
```

The same principle works if you want to stop a field being synchronized, simply catch the $meta_key and return true to remove it from synchronization.
