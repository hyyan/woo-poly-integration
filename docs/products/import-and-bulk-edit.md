# Imports and bulk editing

## Overview
In principle woo-poly-integration does not affect uploads and bulk operations in shop language (wordpress site language).
Issues may arise with other languages, since:
- many fields should be synchronised from base language, not edited or imported separately
- woo-poly-integration does not support separate creation of variations in other languages, variations should be created by synchronisation from first language only.

## Uploads
When updating existing products with WooCommerce's built-in importer, enable **Settings → WooPoly → Features → Synchronize on Import** to apply your synchronization rules to existing translations. This includes updates to variations that are already linked. It does not create translation relationships for independently imported variations.

Here is a suggested sequence, assuming you already have WooCommerce and Polylang installed and running:
### First language
1. for first language follow normal procedure for the tool you are using.  If the tool supports setting the Polylang post language you can do this, otherwise follow the next step.
2. Go to the Polylang admin menu Languages /wp-admin/admin.php?page=mlang and check whether there is a message about posts or pages with no language assigned: if so you can click the link to set these to default language. 
### Other languages
First:
1. ensure the basic setup for the next language is done, see [Getting Started](/getting-started/index.md).
2. highly recommended, check first language Product catalogue to ensuring the Categories, Tags, Attributes etc are set up correctly 
3. Translate the [Product Categories](/products/categories.md), [Product Tags](/products/tags.md) and [Product Attributes](/products/attributes.md)
4. Check the woo-poly-integration Settings [Features](/settings/features.md) and [Metas](/settings/metadata.md) are correct for your use case and you understand which fields will be copied to second language and which will be input.

Then:
- preferably, do _not_ import for second language: instead, until there is a tool for this, translations should be created from the language panel on each product.  Normally the information is all copied over and you just need to correct title/description for the target language.
If you must import second language please bear in mind:
- you don't need to import all the detail for other language since many fields should be synchronised from base language
- do not import variations in other languages, the tool will not be able to link these up to first language, instead the variations will be created once you link the translation to the first language version.
So the process would be:
1. Import products without variations into secondary language
2. Open each new second language product for editing and in the language panel
    1. set the correct language for the new translation and save (if this was not done by your import tool) 
    2. open product _in the first language_ (the main site language) and in the language panel box by the flag for the second language you can type to search for the product to link to.  Take care to choose the right product, since if you choose a product which is already linked as this will unlink it and cause problems later.
    3. once found, save and the translations will be connected and if their are variations, these should be copied across.  
__please note that for Variations, the linking must be done from the first language__
Generally speaking, Variations are the most fragile, with all the tools not just woo-poly, as many tools and also other shop platforms do not support variations completely or support them in a different way.

## Bulk Edit, Quick Edit

### woo-poly 1.0.2 or later
- Bulk Edit or Quick Edit should be done in base language only and meta information will be automatically synchronised to translations.  
The feature is still available on other languages but may have unexpected results, for example updates may be overwritten next time you save the first language version of the product.
Also, if you use the feature while viewing listing of products in multiple languages, you may need to refresh the screen to see the results of synchronisation.

### Historical behavior in woo-poly 1.0.1 or earlier
Bulk Edit / Quick edit from Products admin listing screen does not trigger any synchronisation.
This means that:
- Meta data will not be copied to translations, your translations may be out of date.
- If using in secondary language, updates may be overwritten next time you save the first language version of the product.   
