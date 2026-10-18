# Getting started

## Summary

1. Before you start using WordPress you probably should already fix your base site language and permalink structure
1. after activating WooCommerce you should run through the initial setup 
1. after Polylang you should add the languages you need and set the permalinks
1. after activating woo-poly-integration, you must visit Settings, WooPoly /wp-admin/options-general.php?page=hyyan-wpi and set the configuration to match your use case and press Save
1. note, if activating Polylang on an existing installation, existing items will not have a language set and may not be translatable, be sure to set the options you require before translating.  Specifically, under Polylang settings - "Languages" on the admin menu - a message will come up about some items not having a default language set, with an option to click to set these to the default language:  do this before starting to translate.
1. before translating a set of products, create and translate the [Product Categories and Tags](/products/categories.md) and [Product Attributes](/products/attributes.md) which these products will use


## Preparation

1. First, make sure you have done in the initial [setup](/getting-started/installation.md) including basic configuration of Wordpress, WooCommerce and Polylang.
1. At this point you should know what languages you are going to use and what your urls look like (eg lang2.mysite.com or mysite.com/lang2 etc) and you should have translated some site pages, now you can activate woo-poly-integration


## Global Translation Settings
Before you start translating, you should:
* Check the WooCommerce-Polylang Integration [Features](/settings/features.md) and [Metas](/settings/metadata.md) since **these settings apply site-wide for all products**. 
* See the rest of this documentation for additional discussion for example on [Stock](/products/stock.md) and [Pricing](/products/pricing.md).


## Adding a new Language
For each new language, first there are several steps to do in Polylang and Wordpress:
1. Add new language to Polylang
1. Add translation pages for the standard WooCommerce pages Shop, Basket, Checkout, My Account: Just open each page and press the '+' button next to the language flags in the Languages section, this is [standard Polylang functionality](https://polylang.pro/documentation/support/getting-started/translating-pages-posts-categories-and-tags/). Keep the same type of content as the original language version:
    - Shop: no checkout shortcode is needed; WooCommerce displays the product archive.
    - Basket: the Cart block or `[woocommerce_cart]`.
    - Checkout: the Checkout block or `[woocommerce_checkout]`.
    - My Account: `[woocommerce_my_account]`.
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
