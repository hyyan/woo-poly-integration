# How translations work

woo-poly-integration is an extension to Polylang translation tool which allows translation of additional user input (by Shop staff) in WooCommerce settings screens.

Let's try to explain that in terms of what you see on the screen:
 
* **Adminstration screens and front-end labels:** Non-editable text on screens is all translated by wordpress translation mechanism which uses .po and .mo files as a dictionaries for each plugin/theme and language.
* **String Values in settings screens:** The initial value should be included in the wordpress translation files, however once the value is changed in the screen it won't match up with the dictionary so a different mechanism is needed.   WooCommerce Polylang Integration adds the relevant WooCommerce strings to Polylang [Strings translations](/translations/strings.md).  However if you don't see the value you need there, it may be that it hasn't been modified yet and is still using the default value in the translation files.
* **Data items:** for example actual Products and Product Data, a different version of the Product or attribute should be created for each language.


## Wordpress Translation mechanism
ALL text which is created at design time should be translated using standard WordPress translation mechanism.

The built-in WordPress translation mechanism uses these types of functions in the source code to return localized text:

```
//return the text or translation for this domain
__('text to translate', 'text domain')
//echo the text or translation
_e('text to translate', 'text domain')
```

These functions look up the text value in the language file for the current text domain.
The text domain is normally the name of the component and the related language file: normally every plugin and every theme has its own language files for each language.  These are the .mo and .po files stored in wp-content\languages and also in some cases under the individual plug or theme \languages directory.

This applies or should apply to all text which is created at design time, this includes titles, field labels, messages *and the default values for those fields*.

If any of this design time text is not translated there are two likely possibilities to check:

1. the text item has not been translated or the translation file is out of date.  The Plugin Loco Translate can help check and fix this.  Use the Sync function to re-scan the source code and it will find texts translated by the __() and _e() functions which is missing from the language file.  You can then create new files for additional languages or fill in missing language screens.
2. if the string does not appear in the translation files even after synchronisation, probably source code does not use the the __() and _e() functions or refers to wrong text domain.  This needs change in the source code. 

_Tip: Remember to check if the string you are looking for is actually from WooCommerce or from an additional plugin you have installed, which should have its own language file._

## Polylang Translation mechanism

Polylang translates text which is created at runtime.
Of course you also need to create the translation for this to work.

There are two types of translation within Polylang:

### Polylang Post and Taxonomy Translations
Polylang translates Posts (including Product which is a special type of Post), Menus, Categories, Tags, Product Attributes, by allowing the site editors to create different language versions of these items.
At runtime when an item is requested, Polylang filters the request to find the version of the item corresponding to the current language.  It does this by creating special Language Taxonomies which are used to link the translations together, and by adding the Language panel to the Post/Product screens and the plus and tick symbols for the translation status. 
If there is no version for the current language the item may be considered unavailable and the user may be redirected to the home page for the requested language. 

### Polylang Strings Translations
These are translated in the Polylang strings translation table (Languages, Strings translations - normally found at /wp-admin/admin.php?page=mlang_strings) and used from code like this:
```
//register string for translation
pll_register_string($name, $string, $group, $multiline);
//return the text or translation
pll__($variable)
//echo the text or translation
pll_e($variable)
```

See [Strings translations](/translations/strings.md) in this documentation for more information.


## Further information
* [WordPress translation handbook](https://make.wordpress.org/polyglots/handbook/)
* [Loco Translate](https://en-gb.wordpress.org/plugins/loco-translate/)
* [WooCommerce translation project](https://translate.wordpress.org/projects/wp-plugins/woocommerce)
* [Translate this plugin](https://translate.wordpress.org/projects/wp-plugins/woo-poly-integration)
* [Polylang Documentation](https://polylang.wordpress.com/documentation/)
