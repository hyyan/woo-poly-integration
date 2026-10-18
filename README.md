# Hyyan Integration for WooCommerce and Polylang

Build a multilingual WooCommerce store with Polylang. Translate products and store pages, synchronize shared product data, and let customers shop in their chosen language.

[Documentation](https://github.com/hyyan/woo-poly-integration/wiki) · [Getting started](https://github.com/hyyan/woo-poly-integration/wiki/Getting-Started) · [Report an issue](https://github.com/hyyan/woo-poly-integration/issues)

## Features

- Translate products, categories, tags, attributes, shipping classes, galleries, and variations.
- Synchronize shared product metadata, stock, and coupons across translations.
- Translate store pages, endpoints, payment and shipping text, and order emails.
- Keep the cart consistent when customers switch languages, including classic and block cart/checkout flows.
- Filter reports by language or combine translated product sales, with legacy and HPOS order storage support.
- Manage integration features and synchronized fields under **Settings → WooPoly**.

## Requirements and compatibility

The plugin declares WordPress 5.4+, PHP 7.0+, WooCommerce 3.0+, and Polylang 2.0+. Your installed WordPress, WooCommerce, and Polylang versions may require newer PHP versions.

The current 1.5.1 development checkout has been tested with WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10, and PHP 8.5.11. Older supported versions have not all been retested. Back up your store and verify updates on a staging site first.

Choose your default language before creating variable products; changing it afterward is unsupported. Polylang's **The language is set from content** URL mode is also unsupported.

## Installation and setup

1. Install and activate WooCommerce and Polylang, then configure your store languages and default language.
2. Install this plugin through **Plugins → Add New**, or upload its release ZIP, and activate it.
3. Review **Settings → WooPoly**, translate your WooCommerce pages, and check their language assignments.
4. Translate product categories and attributes as needed, then create and link product translations.
5. Test product selection, stock, the cart, checkout, and order emails in every store language before going live.

See the [getting started guide](https://github.com/hyyan/woo-poly-integration/wiki/Getting-Started) for details. Composer users can install the published package with `composer require hyyan/woo-poly-integration`.

## Help and contributions

Please use [GitHub issues](https://github.com/hyyan/woo-poly-integration/issues) for bug reports and feature requests. Include your plugin versions, steps to reproduce, and relevant logs with private data removed. See the [contribution guidelines](.github/CONTRIBUTING.md).

Code, documentation, translations, and reproducible bug reports are welcome.

## Running tests

Run these commands from the repository root. Install Docker with Compose v2, Bash, and Node.js 24.15 or newer. Docker must be running. WordPress, WooCommerce, Polylang, PHP, and PHPUnit are installed inside the test containers.

### Automated tests

Set up the local WordPress test site, then run the PHP suites:

```sh
tests/bin/setup.sh
tests/bin/run.sh
```

Setup downloads the pinned dependencies on the first run. The suites cover unit tests, WordPress AJAX requests, product translations, stock, carts, orders, and admin and storefront behavior. Use the disposable test database only; these tests create and delete product data. The site listens at `http://localhost:8098`.

Run the JavaScript tests and check that minified assets match their sources:

```sh
npm ci
npm run check:js
npm run test:js
```

After changing `public/js/Cart.js` or `public/js/Variables.js`, run `npm run build:js` and include the generated `.min.js` files with your changes. Enable the optional local pre-commit check with `npm run hooks:install`.

GitHub Actions runs these checks on pushes and pull requests, with PHP 8.3, 8.4, and 8.5. It also validates the workflow files and checks release preparation with publishing disabled.

Stop the automated test site while keeping its data:

```sh
docker compose -f tests/compose.yaml stop
```

### Testing in a browser

The separate manual test store requires Make and rsync in addition to the tools above. Start it with:

```sh
make up
```

Open `http://localhost:8097` or `http://localhost:8097/wp-admin/`. Sign in as `manual-admin`, `manual-manager`, `manual-customer`, or `manual-subscriber`; each account uses the password `manual-password`. These accounts belong only to the local test store.

The store includes English and German products, linked variations, stock, coupons, and offline checkout. Check product editing, variation removal, stock synchronization, language switching, cart continuity, checkout, refunds, and order emails in both languages. Captured emails are available under **Tools → Manual store mail**.

Use `make classic` or `make block` to switch checkout formats. Run `make up` again to install your latest source changes while keeping the store data. `make stop` stops the store; `make reset` **deletes the manual store data** and recreates the fixtures. Clear browser cookies after a reset.

## Keep in touch

Created by **Hyyan Abo Fakher**. If WooPoly is useful to you, a [star on GitHub](https://github.com/hyyan/woo-poly-integration) helps others discover it. You are also welcome to follow my work on [GitHub](https://github.com/hyyan) or [LinkedIn](https://www.linkedin.com/in/hyyan/).

## Translations

- Arabic by [Hyyan Abo Fakher](https://github.com/hyyan).
- Spanish by [nunhes](https://github.com/nunhes).

## License

[MIT](LICENSE).
