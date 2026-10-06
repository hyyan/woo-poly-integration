# Security maintenance review

The reported variation removal paths were inspected against baseline b5f7495 and current source. Local commit 01bfb80 corrects the variation lookup and removal callback. Local commit 0ec30b1 corrects the equivalent early featured product callback. This review adds regression cases and current acceptance evidence. A complete release clearance remains pending.

## Reported issues

| Report | Corrected behavior | Source |
| --- | --- | --- |
| Unvalidated variation IDs | Accept integer values and decimal digit strings, reject other shapes before any mutation, cast IDs before lookup and deletion, check variation post type | Product/Variable.php lines 317 to 340, Product/Variation.php lines 148 to 152 |
| Variable data in SQL | Prepare the relationship key with %s and the normalized variation ID with %d | Product/Variation.php lines 159 to 163 |
| Missing authorization | Require edit_products and edit permission for the original and every linked variation parent before deleting any translation | Product/Variable.php lines 313 to 335 |
| Missing request verification | Invoke check_ajax_referer with the existing delete-variations action and security request field before mutation | Product/Variable.php line 312 |
| Client nonce | WooCommerce owns the request. Its native variation script already sends security from delete_variations_nonce. Current Chrome capture confirms the native Remove button includes it | Installed WooCommerce meta-boxes-product-variation.js lines 1057 to 1059, class-wc-admin-assets.php line 564 |

No second request handler or replacement nonce action is needed. Plugin public/js/Variables.js handles language warnings and does not send removal requests. The server change preserves the original WooCommerce contract.

## Other paths inspected

The production tree registers two AJAX callbacks and uses two direct SQL queries. Both AJAX callbacks originally ran before WooCommerce checks. The featured callback now checks edit_products and the existing woocommerce-feature-product nonce before changing translations. The SKU query in Product/Meta.php already prepares SKU and product ID with %s and %d. Current Plugin Check reports no PreparedSQL or UnescapedDBParameter findings. This statement covers those query checks, not every security property of the plugin.

The settings menu is registered only for manage_options users. Bundled settings forms use the WordPress Settings API. Existing contributor browser rejection evidence is preserved. Remaining save hook, input and output checker findings still require individual review.

## Verification

- Full regression runner passed on PHP 8.5.11. Unit suite has 9 tests and 22 assertions. WordPress integration suite has 63 tests and 167 assertions.
- Expanded variation coverage tests nine invalid value forms without executing a related lookup query, rejects a malformed batch before partial deletion, and checks denied permissions on either parent.
- Existing security cases cover subscriber, customer and contributor denial, missing and invalid nonces, authorized administrator and shop manager removal, corrupt relationships and linked lookup preservation. Featured cases cover role and nonce denial, normal toggles, POST nonce and disabled visibility synchronization.
- Current Chrome administrator requests with missing or invalid nonce, SQL expression, nested batch and decimal ID retained both linked variations and siblings. Native Remove submitted the expected nonce, deleted both linked variations and retained both siblings. Earlier shop manager browser evidence remains historical evidence, while its automated case passed again now.
- All source and regression PHP files passed php -l. Full production and regression diff passed git diff --check. No public signature, nonce name, capability name, translation key or dependency minimum was changed by the security patches.
- Plugin Check completed with 499 findings, 173 errors and 326 warnings. Of these, 345 occur in production source or scripts, 30 in tests and 124 elsewhere. All are preserved with stable REVIEW-CHECK identifiers. No clean checker result is claimed.

## Changed files and reasons

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Product/Variable.php | Verify nonce, capabilities, batch shape and source and translation permissions before deletion |
| src/Hyyan/WPI/Product/Variation.php | Validate direct lookup input, prepare SQL and restrict related deletion to variations |
| src/Hyyan/WPI/Product/Product.php | Prevent unauthorized featured translation changes before native WooCommerce checks |
| tests/integration/VariationSecurityTest.php | Real WordPress database, request and permission regressions, expanded in this review |
| tests/integration/FeaturedProductSecurityTest.php | Real WordPress featured callback regressions |
| phpunit-integration.xml | Integration suite configuration |
| phpunit-unit.xml | Existing unit suite configuration |
| tests/integration/bootstrap.php | Disposable site guard and AJAX termination capture |
| tests/setup-lab.php | Test language and role setup |
| tests/compose.yaml | Isolated WordPress and database lab |
| tests/bin/setup.sh | Install test tools and configure the disposable lab |
| tests/bin/run.sh | Run both suites |
| .github/workflows/tests.yml | Run suites on pushes and pull requests, remote execution still pending |
| docs/TESTING.md | Reproduction commands and browser acceptance rules |
| docs/SECURITY-REVIEW.md | Scope, findings, file list and release limits |
| docs/CHECKS.md | Preserve the new checker result beside the original inventory |
| docs/ISSUES.md | Track the maintenance review and release gate |
| docs/JOURNAL.md | Record verification and next work |
| docs/evidence/security-review-chrome.json | Current rejection, client nonce and linked deletion proof |
| docs/evidence/security-review-tests.txt | Current full regression output |
| docs/evidence/security-review-php-lint.txt | Current PHP syntax output |
| docs/evidence/plugin-check-security-review.json | Full grouped checker output |
| docs/evidence/plugin-check-security-review-normalized.json | Individual checker findings and pending dispositions |
| docs/evidence/plugin-check-security-review.stderr.txt | Checker diagnostic stream, empty on this invocation |

Original per fix security evidence remains in commits 01bfb80 and 0ec30b1. Earlier compatibility changes and the pending CSV import patch are separate work. None is newly bundled into a security source patch by this review.

## Release gate

Review remaining checker findings against their owning source, fix confirmed defects individually, then run Plugin Check on the actual distributable archive. Exclude development fixtures and evidence from the release archive. Recheck the oldest supported WordPress, WooCommerce, Polylang and PHP combinations, custom shop roles, variation stock behavior and upgrade preservation. The remote CI matrix has not run. Production rollout and Plugins Team submission have not occurred. These remain release verification requirements, not evidence of a new exploit.
