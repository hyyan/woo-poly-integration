# Regression testing

`VariationSecurityTest` includes real HTTP requests to the lab container's Apache at `http://127.0.0.1/wp-admin/admin-ajax.php`, using temporary WordPress sessions and owned product fixtures. Apache must be running alongside the test process.

To execute the installed WooCommerce removal client and assert its nonce payload:

```sh
docker compose -f tests/compose.yaml exec -T wordpress cat wp-content/plugins/woocommerce/assets/js/admin/meta-boxes-product-variation.js > /tmp/wpi-native-variation-client.js
node tests/bin/verify-variation-client.cjs /tmp/wpi-native-variation-client.js
```

Use the disposable Docker lab only. Runtime/database volumes belong to Compose project `woo-poly-lab`; HTTP binds only to localhost:8098. Never point integration tests at a real store. Existing lab data is retained; `setup.sh` configures only lab dependencies/languages/users.

## Automated checks

From repository root:

```sh
tests/bin/setup.sh
tests/bin/run.sh
```

Unit tests isolate price localization and require PHP intl. Integration tests boot actual WordPress/WooCommerce/Polylang, execute the registered AJAX hook chain, and assert real database effects. Fixtures are deleted after each test. The unpatched injection regression can delete other products in this disposable database; never run against valuable data.

Pinned runtime: WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10, WP-CLI 2.12.0, PHPUnit 12.5.38. Test-tool PHP minimum does not change plugin runtime minimum. CI runs PHP 8.3/8.4/8.5 for every pull request and push. Older runtime coverage remains to be added before a complete compatibility claim.

## Browser checks

Verify the native WooCommerce product editor and storefront in a browser alongside the automated checks. Use disposable products and test accounts with different roles.

- Confirm subscribers and customers cannot remove variations.
- Confirm missing or invalid nonces, malformed or nested identifiers, and nonexistent variations are rejected without modifying product data.
- Confirm administrators and shop managers can remove authorized variations and their translations while sibling and unrelated variations remain unchanged.
- Check parent prices, variation metadata, and translated stock after removal and after saving the product.
- Check English/German variation selectors and prices, adding a selected variation to the cart, and language switching with cart continuity.

Keep generated screenshots, request captures, logs, and run reports outside the repository, for example in `/tmp/woo-poly-test-results/`. Record the tested versions and actual results with those artifacts.

See [Manual Testing Guide](MANUAL-TESTING.md) for the broader functional checklist.

## Runtime selection

The Docker seed image contains WordPress 7.1.2. Setup uses the native WordPress updater to select 7.1.3 before testing. Set `WORDPRESS_VERSION` to select another explicit runtime for compatibility verification.
