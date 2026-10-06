# Contributor authorization audit

Checked 2026-10-06 against current plugin source and the disposable WordPress lab.

The [Wordfence advisory](https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/woo-poly-integration/hyyan-woocommerce-polylang-integration-150-missing-authorization) describes missing authorization affecting contributors in versions through 1.5.0. It does not identify the function or operation. The [Patchstack source](https://patchstack.com/database/wordpress/plugin/woo-poly-integration/vulnerability/wordpress-hyyan-woocommerce-polylang-integration-plugin-1-5-0-broken-access-control-vulnerability) also does not expose an operation in its public details. These reports cannot be matched conclusively to a local fix.

Source inventory found two plugin callbacks on WooCommerce AJAX actions. Existing local commits protect linked variation removal and translated featured toggles. This step expands their regression and browser role coverage to contributors. It changes no production behavior.

| Operation | Source and existing contract | Contributor Chrome result |
| --- | --- | --- |
| Linked variation removal | Product/Variable.php checks nonce and edit_products before translated deletion | Valid session nonce request returned -1; both records preserved |
| Featured product toggle | Product/Product.php checks edit_products and WooCommerce nonce before translated mutation | Valid session nonce request redirected without changing either flag |
| Plugin settings page | Admin/Settings.php:55 gates menu registration on manage_options; vendor settings library uses register_setting and native options.php | Direct page returned 403; saved settings hash unchanged |

The settings manage_options gate already existed in commit 90a9e62 from 2019. It is not a new fix. The nested add_options_page capability alone is not proof of a reachable settings bypass.

Evidence: contributor-chrome.json. Full suite: 47 tests and 108 assertions passed. Contributor unit integration paths use valid nonces. Chrome nonces were generated in the signed in session by a temporary lab fixture, then the fixture was removed. Session material was omitted from saved evidence.

SEC-004 remains unresolved. This role check proves these named operations, not every operation mentioned by an undisclosed report. General checker findings, rendered output, automatic page maintenance, term synchronization and other hooks still require their own dispositions.
