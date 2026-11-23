# Set up your multilingual store

Open **Settings → WooPoly** to see your setup checklist. You can return at any time: the checklist reads your current configuration, so completed steps remain ready and newly added languages appear automatically.

Closing the setup page does not complete a step. A step becomes **Ready** only when its requirements are met; unfinished steps still need attention when you return.

## 1. Connect your plugins

WooPoly needs **WooCommerce** and **Polylang**, or **Polylang Pro**. You can install them in any order. The checklist shows whether each dependency is missing, inactive, or ready. Choose **Install plugin** to open **Plugins → Add New** with that plugin’s listing. Select **Install Now** there, then activate the plugin.

If another plugin opens its setup wizard, complete the relevant steps and return to **Settings → WooPoly**. WooPoly does not redirect you away from the Plugins screen or install dependencies without your action.

## 2. Choose your languages

Choose **Manage languages** to configure languages in Polylang. Add your store’s original language and at least one translation language, then return to WooPoly and choose **Check again**.

Choose your default language carefully. Changing it after creating variable products is unsupported. Polylang’s **The language is set from content** URL mode is also unsupported.

On an existing site, review Polylang’s message about content without a language before translating products. Confirm the language of existing content in Polylang rather than assuming every page or product belongs to the default language.

## 3. Prepare your store pages

The checklist shows **Shop**, **Cart**, **Checkout**, and **My account** for every configured language.

- **Ready** means a page is linked to the language and published. **View page** opens it for review.
- **Missing** means a translation or the original page is absent.
- **Needs a language** means the assigned original page has no Polylang language yet.
- **Draft**, **Private**, or **Trash** means the page needs review. WooPoly does not silently publish or restore it.

Choose **Create missing pages and translations** to prepare missing functional pages. WooCommerce’s native page installer creates missing originals; WooPoly creates and links missing language versions. Existing linked pages and their content are preserved. Repeating the action does not create another copy of an existing translation.

### Use an existing page

If you already designed a page, expand **Use an existing page** in the appropriate table cell. Select the page and choose **Use page**. Choose the original page in the default-language column first, then link its translations.

Only unassigned pages and pages in the selected language are offered. A page already used for another store function or a different translation group cannot be reused here. Use separate pages for Cart, Checkout, and My account.

### Review generated content

New translations preserve the original page’s blocks, shortcodes, and content. Standard WooCommerce interface text is translated by WooCommerce language packs; custom page text is copied for you to review and translate manually. This is not machine translation.

Page titles use available WooCommerce translations. If a title stays in the original language, update translations under **Dashboard → Updates** and edit the page title as needed.

WooPoly does not generate your terms, privacy policy, or other legal content. Review these separately and link their translations through Polylang.

## Finish your store setup

- Add a language switcher to your theme’s navigation using [Polylang’s language switcher guide](https://polylang.pro/documentation/support/guides/the-language-switcher/).
- Finish WooCommerce’s own store setup, including payments, shipping, and taxes.
- Translate your [categories](/products/categories.md), [attributes](/products/attributes.md), and [products](#translate-your-first-product).
- Check the store pages, a test purchase, and customer emails in every language.

**Ready** page status confirms the page relationships and publication state. It does not verify payment configuration, your legal text, or every aspect of your storefront.

Prices and stock are shared across product translations by default. Open **Advanced settings** from the setup checklist to review [features](/settings/features.md) and [metadata synchronization](/settings/metadata.md).

## Translate your first product

1. Open **Products → Add new** and write the product in your default language. Set its product type, price, and stock, then save it.
2. In the product’s **Languages** panel, choose the **+** beside the target language.
3. Write the translated product name and descriptions. Shared data, such as price and stock, follows your WooPoly synchronization settings.
4. Save the translation and check both product pages using your language switcher.

Translate categories and attributes before assigning them to translated products. See the [category guide](/products/categories.md) and [attribute guide](/products/attributes.md).

## Adding another language later

Add the language in Polylang, then return to **Settings → WooPoly**. The page checklist will show the new missing translations. Create or select the pages, review their content, and update your language switcher and navigation.
