# Translate a product with DeepL

Create a linked draft in another Polylang language from a saved WooCommerce product. For example, an English shirt can have a French draft with **Couleur** and **Rouge** as its variation options while keeping the variation’s SKU, price, and stock quantity.

## Connect a DeepL API key

Open a saved product as a Shop Manager. In **Translate with DeepL → DeepL API key**, paste your DeepL API Free or Pro key and select **Save key**. You can now translate products from this WordPress account. The field stays blank when you return. To replace your key, enter the new one and save. Choose **Remove personal key** to disconnect it.

If the store already supplies a shared key, WooPoly uses it for accounts without a personal key. Your personal key takes precedence.

## Create and review the draft

1. Add the target language in Polylang. Save the original product with its source language assigned. Save any pending edits before translating.
2. Open the product editor with an account that can manage WooCommerce, edit this product, and create products.
3. In **Translate with DeepL**, choose **Target language**, then **Create translated draft**. This sends the saved translatable text to DeepL.
4. The editor opens the linked draft. Review the product text, attribute choices, variation descriptions, and images. Publish when the translation is ready.

If a language is missing from the selector, check the product’s **Languages** panel. When a linked translation already exists, open it there to edit it. WooPoly leaves reviewed translations in place.

## What gets translated

| Product data | Draft behavior |
| --- | --- |
| Name, short and full descriptions, purchase note | Text is translated. Descriptions containing HTML are sent using DeepL’s HTML mode. |
| Categories, tags, global attribute terms | Enable translation for the relevant taxonomies in Polylang. Existing translated terms are reused, including manually edited names. Missing translations are created and linked. |
| Attributes and variations | Local attribute names and values, global attribute labels, and variation descriptions can be translated. Review the default selection and each variation in the draft. |
| Product and variation images | With media translation enabled in Polylang, an existing translated attachment is reused. New translated attachments share the image file and have their own caption and alternative text. |
| SEO and custom product text | Select eligible text fields in WooPoly’s settings. Operational fields such as SKU, stock, and price are handled as WooCommerce data. |

## Include custom text fields

Open **Settings → WooPoly → Advanced settings → Features**. In **DeepL product text fields**, enter the stored metadata keys, separated by commas. For example, if your store saves a care instruction under `care_instructions`, enter that key and save the settings before creating the draft. Use the stored key even if the field’s label in the editor is different.

Extensions can register additional keys with the `woo-poly.product.translationMetaKeys` filter, which receives the configured keys and the source product. Only include customer-facing text intended to be sent to DeepL. WooPoly skips structured values and excludes WooCommerce internal fields.

## If translation fails

If the panel asks for a key, open **DeepL API key** in the product editor and save your key. For a failed request, check the account quota and whether DeepL supports the selected language pair. Correct the problem and retry from the saved source product.
