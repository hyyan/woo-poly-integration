# Runtime and data preservation batch

Runtime and data preservation batch complete. COMPAT-047 restores combined report totals when the legacy language preference is empty. COMPAT-048 uses the native HPOS orders alias for language joins. COMPAT-049 declares HPOS compatibility through the guarded native feature API. Native legacy and HPOS reports show combined quantity five and revenue fifty, with English two and twenty and German three and thirty. Native network activation and separate site settings, upgrade from stored version 1.5.0 and all optional features disabled pass. Native CSV creation, export and reimport preserve identity, price, category, language and custom metadata. Full suites pass 312 tests with 1061 assertions. The rebuilt artifact has 73 matching installed files in both single site and network installations. Plugin Check retains 231 findings, 66 errors and 165 warnings, with unchanged dispositions. Older native reports and absent feature API bootstrap pass, as does PHP 7.0 syntax. Owned single site, CSV and network fixtures are removed and original options restored. One acceptance group remains: external environment cases. Release metadata and checker submission treatment remain open. No index, commit or publication action occurs. Evidence uses the runtime-batch prefix.

## Corrections and regression evidence

Reports.php reuses the existing default language resolver when both current language and the legacy user preference are empty. ReportCombinationTest covers eight cases with 24 assertions. Two regressions fail before correction. WooCommerce caches filtered report results, so the owned previous report transient was invalidated before native confirmation.

Reports.php now passes the native orders alias to the existing Polylang language join for HPOS queries. ReportStorageQueryTest exercises four filled native queries across legacy storage and HPOS. Legacy controls pass before correction and both HPOS cases fail. All four pass with 12 assertions afterward.

__init__.php registers the native custom_order_tables declaration before WooCommerce initialization and guards environments without FeaturesUtil. HposCompatibilityDeclarationTest fails before correction and passes afterward. The fresh network native settings warning disappears. This declaration does not assert support for other optional WooCommerce features.

## Native acceptance and cleanup

Original Network Activate and both site settings render successfully. Changing primary stock synchronization through the original Save control leaves secondary stock synchronization enabled. Filled HPOS report controls show correct combined and separate totals without database errors. Both sites render their native storefronts.

The original single site admin upgrade preserves metadata selections and feature choices. Saving every optional feature disabled leaves the associated callbacks absent. An independent native stock update does not change its translation. Six original options and the user language preference are restored, and two owned products and two orders are deleted.

The native importer creates one English simple product with price twelve and its category. Original Generate CSV reaches completion. DevTools does not retain the download body, so a temporary observer copies the exact generated header and rows before WooCommerce deletes its temporary file. It verifies the native nonce without saving its value and leaves the export arguments unchanged. Original reimport updates one product and preserves all six checked fields. The product, category, two server CSV files, observer and fixture option are removed. Browser download files are not included in this cleanup claim.

The network cleanup CLI initially fails because its mysql executable is absent. Guarded WordPress database cleanup verifies the owned marker and exact prefix, removes 106 owned tables and confirms zero remain. The network directory and owned browser tab are removed. The original installations remain available.

## Verification

runtime-batch-suite.txt records 11 unit, 283 integration, nine frontend and nine admin tests, totaling 312 tests and 1061 assertions. Source syntax and both diff checks pass. Package and installed manifests match all 73 files. The installed Plugin Check scan has an empty diagnostic stream and unchanged finding dispositions, which do not constitute directory approval.

Older native WordPress 5.4.2, WooCommerce 4.0.0 and Polylang 2.7.4 report queries and guarded bootstrap pass. Both changed production files pass PHP 7.0 syntax. No production change follows the final CSV acceptance, so the full suite and checker are not repeated for fixture and documentation changes alone.

## Files changed

__init__.php adds the guarded HPOS declaration. Reports.php corrects report language fallback and storage alias selection. ReportCombinationTest.php, ReportStorageQueryTest.php and HposCompatibilityDeclarationTest.php provide native regression coverage. Acceptance, issues, release audit, batches, journal, goal and handoff documents record evidence and remaining scope. The evidence inventory points to the current artifact. Fixture helpers remain outside the distributable package.
