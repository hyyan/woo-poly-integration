# Security re-review handoff

Current verified source 4ac655a: 446 tests with 1498 assertions, 73 matching installed archive files. Installed static and runtime Plugin Check each report 64 findings, 23 errors and 41 warnings. This removes 166 actual rows from the 230 finding baseline. Remaining batches contain 33 request checks, 22 translation checks, two overridable public hook checks, two prepared query checks and five other contracts. Zero findings and the complete published version upgrade feature matrix remain required. The goal remains active. No push, publication or release approval. Evidence uses plugin-check-admin-route-batch.

Current verified database cache batch: source dd816e0, 390 tests with 1347 assertions, 73 matching archive files. Installed static and runtime Plugin Check each retain 122 findings, 45 errors and 77 warnings. Archive SHA256 is 4146862d0a27c38051f596ea3b93e555f8915b2a3ae552324ff9ef7451c6e9e6. Earlier batch records below retain historical evidence. Zero findings and the complete published version upgrade feature matrix remain required. No release approval or completion claim.

Current verified request ID batch: source ac630ad, 388 tests with 1326 assertions, 73 matching archive files. Installed static and runtime Plugin Check each retain 124 findings, 45 errors and 79 warnings. Archive SHA256 is 31cdf305c5c20362aca04f88d822e5c29bb196cf88e5813034e7860d3a01f3fa. Earlier batch records below retain historical evidence. Zero findings and the complete published version upgrade feature matrix remain required. No release approval or completion claim.

The four reported variation removal issues are corrected in the inspected source. The current active Plugin Check goal remains incomplete. No exception has been accepted and no WordPress.org approval or submission is claimed.

| Report | Inspected correction | Evidence |
| --- | --- | --- |
| Unvalidated request variation IDs | Variable::removeVariations validates every member before deletion, accepts integer or ASCII digit string IDs, casts IDs to integers and checks the actual variation type. Variation::getRelatedVariation independently validates direct callers | VariationSecurityTest covers malformed values, whole batch rejection and real database effects |
| Variable data reaches raw related deletion SQL | The relationship query uses wpdb->prepare with %s for the key and the normalized integer represented as a string for the text metadata comparison. Binary comparison rejects malformed suffixes and whitespace while leading zero integer references remain valid. The SKU query uses %s for SKU and %d for excluded product ID | QueryDiagnosticSecurityTest and VariationRelationshipPathsTest, native removal and older exact lookup probes |
| Authenticated users lack sufficient operation restrictions | The early handler requires edit_products and edit_post for the source parent and every affected linked parent before mutation | Subscriber, customer and contributor denial, denied source and target parent cases, authorized administrator and shop manager cases |
| State changing AJAX request lacks verification | The early handler invokes check_ajax_referer with delete-variations and security before deletion | Missing and invalid nonce regressions and native WooCommerce Remove acceptance |

## Actual client and equivalent paths

The current WooCommerce 11.1.2 and supported older WooCommerce 4.0.0 variation clients both send security from delete_variations_nonce for woocommerce_remove_variations. Their actual source was inspected again in this batch. The plugin JavaScript has no independent sender for this action. The existing native client already supplies the required nonce, so no duplicate sender or dependency JavaScript patch was introduced. Evidence is in plugin-check-display-name-batch-native-client.txt and plugin-check-display-name-batch-older-client.txt.

The equivalent early featured product callback requires the native nonce and edit_products before changing translated visibility. Early creation copy hooks verify new-post-translation, edit_products and source read permission in the existing shared request helper. Legacy product creation links without a nonce receive the native nonce through the existing Polylang link filter. Current native links are retained.

Cart, stock and coupon translation and variation copy paths now reuse the corrected relationship lookup. Their native post type, parent, status, language, ordering and limit constraints remain. Empty relationship lists skip the native post query. Tests retain the private parent permission assertions using real integer relationship fixtures.

## Current verification

Latest source commit is 60ce0d0. Version remains 1.5.1. The release headers display Hyyan Integration for WooCommerce and Polylang. The directory slug, text domain, catalog keys, settings IDs, hooks and stored data are retained. WordPress 5.4, WooCommerce 3.0.0 and PHP 7.0 minimum headers retain the published release requirements.

All four existing suites pass 388 tests with 1326 assertions on PHP 8.5.11. The existing default 128 MB test process exceeded its limit after the suite grew. The successful execution uses a 256 MB test process limit, with retained failure evidence and no production configuration change. The existing test runner now applies this same process limit. Sixty one installed PHP files and fifty nine main entry and source files on PHP 7.0 pass syntax. Relevant WordPress code analysis runs through the installed Plugin Check. Current source and index diff checks pass.

The current 73 file archive matches source, manifest and installed package. SHA256 is df51f7017b11dc74ac87f07535e5af7db8f4cb91cbb5fb6e6f27f0aa908de547. Evidence uses plugin-check-display-name-batch, plugin-check-relationship-paths and plugin-check-exact-id-batch. Tests, tools and review documents are excluded from the release archive.

Chrome verifies native shop manager removal, genuine linked deletion and preservation of malformed relationships. Later native Update retains a valid padded translated child and corrects a malformed whitespace relationship through normal synchronization. English and German carts retain the small variation and price thirteen. Chrome also verifies the active plugin row, new display name and unchanged working settings link. Owned fixtures and temporary users are removed, notices restored and the browser returned to blank.

The current stack is WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10 and PHP 8.5.11. Older native probes use WordPress 5.4.2, WooCommerce 4.0.0 and PHP 7.4.9. The exact official Polylang 2.0.1 link method is tested in the older harness. Full historical dependency combinations are not claimed verified.

Actual installed Plugin Check reports 127 findings, 45 errors and 82 warnings, with empty diagnostics. The 230 finding active baseline has 103 actual rows removed. Forty four translation findings and 83 precise compatibility findings remain open. Their source locations and reasons are in PLUGIN-CHECK-TRANSLATION-EXCEPTIONS.md and PLUGIN-CHECK-COMPATIBILITY-EXCEPTIONS.md. Written dispositions are not fixes or accepted exceptions.

## Files, commits and manual requirements

The original handler, query and equivalent featured fixes are in Product/Variable.php, Product/Variation.php and Product/Product.php. Later shared boundary corrections also touch Utilities.php and Cart.php. Their regression files and all maintenance changes are mapped to their actual commit subjects in evidence/current-maintenance-file-history.json. COMMITS.md lists verbatim Conventional Commit subjects and changed file counts excluding tests and configuration, through its stated snapshot commit.

Release completion requires zero installed Plugin Check findings and the complete published version upgrade matrix passing in tests and Chrome. Exception acceptance is no longer a completion path. Independent Plugins Team review remains required. The existing directory slug warning requires a directory identity decision. Performance on a large catalog, remote CI success, actual email delivery and hosted payment callbacks are not claimed verified. External extension defects and service configurations are excluded by the maintainer. No remote push, publication or submission has occurred.

Published minimum evidence uses published-minimum-batch. The actual official 1.5.0 archive was updated in place on WooCommerce 3.0.0. Eight products, their metadata and language relationships, feature settings and Polylang settings remain exact. Chrome verifies the old and new active plugin and bilingual small and large variation selection. Other upgrade feature scenarios remain unfinished.

The runtime enabled scan of the installed package also retains 127 findings with empty diagnostics. The strict report gate rejects this report. No accepted exceptions, suppressions or clean release claim.
