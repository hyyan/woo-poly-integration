# Getting started

## Start with the setup checklist

Open **Settings → WooPoly** and follow the [store setup guide](/getting-started/setup.md). The checklist helps you activate the required plugins, choose languages in Polylang, and create or select the store pages for each language.

You can install WooPoly before WooCommerce or Polylang. Return to the checklist after activating and configuring them. Existing stores keep their pages and translations.

Before translating products, review the default language and URL settings in Polylang, then prepare your [categories and tags](/products/categories.md) and [attributes](/products/attributes.md).

## Global Translation Settings
Before you start translating, you should:
* Check the WooCommerce-Polylang Integration [Features](/settings/features.md) and [Metas](/settings/metadata.md) since **these settings apply site-wide for all products**. 
* See the rest of this documentation for additional discussion for example on [Stock](/products/stock.md) and [Pricing](/products/pricing.md).


## Adding a new Language
For each new language, first there are several steps to do in Polylang and Wordpress:
1. Add new language to Polylang
1. Return to **Settings → WooPoly** and use the [page checklist](/getting-started/setup.md#_3-prepare-your-store-pages) to create missing translations or select existing pages. Keep blocks or shortcodes consistent with the original page.
1. Add menu for the new language to Wordpress
The site won't be usable in the new language without these steps!
Next:
* Translate global WooCommerce options which this plugin adds to [Polylang Strings Translations](/translations/strings.md)
* Translate the [Product Categories](/products/categories.md), [Product Tags](/products/tags.md) and [Product Attributes](/products/attributes.md), the same as setting up a new Product Category below.


## Setting up a new Product Category
Before adding your products, please create and translate the Categories, Tags and Attributes which you are going to use for these products:
1. [Product Categories](/products/categories.md) and [Tags](/products/tags.md)
1. [Product Attributes](/products/attributes.md)  Note: after create a new Product attribute, remember to both translate the name in Polylang Strings Translations, and if you also want to translate the Attribute Terms (the values), go to Polylang settings page Languages, Settings, Custom post types and Taxonomies /wp-admin/admin.php?page=mlang_settings and enable translation for the new attribute - see [Product Attributes](/products/attributes.md)
The plugin does have the capability to fill in missing translations for Categories, Tags and Attributes to avoid errors when you create a product translation.  It does this by copying the source language value of the attribute. 
 Any slugs created will be source language slug with language suffix, for example English "color" automatic fill-in translation in Spanish will be created as Name "color" and slug "color-es".

After creating the products it's a good idea to go back to [Product Tags](/products/tags.md) and [Product Attributes](/products/attributes.md) to review any new Tags and attribute Terms which may have been created during the editing process.
