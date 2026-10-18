# Hyyan Integration for WooCommerce and Polylang

Build a multilingual WooCommerce store with Polylang. Translate products and store pages, synchronize shared product data, and let customers shop in their chosen language.

[Documentation](https://hyyan.github.io/woo-poly-integration/) · [Getting started](https://hyyan.github.io/woo-poly-integration/getting-started/) · [Report an issue](https://github.com/hyyan/woo-poly-integration/issues)

## Features

- Translate products, categories, tags, attributes, shipping classes, galleries, and variations.
- Synchronize shared product metadata, stock, and coupons across translations.
- Translate store pages, endpoints, payment and shipping text, and order emails.
- Keep the cart consistent when customers switch languages, including classic and block cart/checkout flows.
- Filter reports by language or combine translated product sales, with legacy and HPOS order storage support.
- Manage integration features and synchronized fields under **Settings → WooPoly**.

## Requirements and compatibility

The plugin declares WordPress 5.4+, PHP 7.0+, WooCommerce 3.0+, and Polylang 2.0+. Your installed WordPress, WooCommerce, and Polylang versions may require newer PHP versions.

The current 1.6.0 development checkout has been tested with WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10, and PHP 8.5.11. Older supported versions have not all been retested. Back up your store and verify updates on a staging site first.

Choose your default language before creating variable products; changing it afterward is unsupported. Polylang's **The language is set from content** URL mode is also unsupported.

## Installation and setup

1. Install and activate WooCommerce and Polylang, then configure your store languages and default language.
2. Install this plugin through **Plugins → Add New**, or upload its release ZIP, and activate it.
3. Review **Settings → WooPoly**, translate your WooCommerce pages, and check their language assignments.
4. Translate product categories and attributes as needed, then create and link product translations.
5. Test product selection, stock, the cart, checkout, and order emails in every store language before going live.

See the [getting started guide](https://hyyan.github.io/woo-poly-integration/getting-started/) for details.

## Help and contributions

Please use [GitHub issues](https://github.com/hyyan/woo-poly-integration/issues) for bug reports and feature requests. Include your plugin versions, steps to reproduce, and relevant logs with private data removed. See the [contribution guidelines](.github/CONTRIBUTING.md).

Code, documentation, translations, and reproducible bug reports are welcome. See the [testing instructions](.github/CONTRIBUTING.md#running-tests) to get started with local development.

## Keep in touch

Created by **Hyyan Abo Fakher**. If WooPoly is useful to you, a [star on GitHub](https://github.com/hyyan/woo-poly-integration) helps others discover it. You are also welcome to follow my work on [GitHub](https://github.com/hyyan) or [LinkedIn](https://www.linkedin.com/in/hyyan/).

## Translations

- Arabic by [Hyyan Abo Fakher](https://github.com/hyyan).
- Spanish by [nunhes](https://github.com/nunhes).

## License

[MIT](LICENSE).
