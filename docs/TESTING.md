# Regression testing

Use the disposable Docker lab only. Runtime/database volumes belong to Compose project `woo-poly-lab`; HTTP binds only to localhost:8098. Never point integration tests at a real store. Existing lab data is retained; `setup.sh` configures only lab dependencies/languages/users.

## Automated checks

From repository root:

```sh
tests/bin/setup.sh
tests/bin/run.sh
```

Unit tests isolate price localization and require PHP intl. Integration tests boot actual WordPress/WooCommerce/Polylang, execute the registered AJAX hook chain, and assert real database effects. Fixtures are deleted after each test. The unpatched injection regression can delete other products in this disposable database; never run against valuable data.

Pinned runtime: WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10, WP-CLI 2.12.0, PHPUnit 12.5.38. Test-tool PHP minimum does not change plugin runtime minimum. CI runs PHP 8.3/8.4/8.5 for every pull request and push. Older runtime coverage remains to be added before a complete compatibility claim.

## Chrome acceptance before each fix commit

Use Chrome MCP exclusively. Preserve the scenario, user role, URL, request/response, before/after database effects, screenshot and exact installed versions in docs/evidence and docs/JOURNAL.md. Browser acceptance is a separate gate; PHP tests alone cannot prove it.

Security cases: subscriber/customer rejection; administrator missing/invalid nonce; malformed/nested/nonexistent IDs; no SQL delay; legitimate administrator/shop-manager translated removal; untouched sibling and unrelated products; translated stock behavior.

Product cases: English/German variable selectors and prices; empty dimensions; selected variation added to cart; original admin numeric values; language switch and cart continuity. Full feature matrix is tracked in docs/ACCEPTANCE.md.

The available Docker seed image contains WordPress 7.1.2. Setup uses the native WordPress updater to select 7.1.3 before testing. WORDPRESS_VERSION can select another explicit runtime for compatibility verification. Both owned sites were updated to 7.1.3 through this native path. Full setup was not repeated after this script change.
