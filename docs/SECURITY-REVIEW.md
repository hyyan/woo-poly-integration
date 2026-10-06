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

The settings menu is registered only for manage_options users. Bundled settings forms use the WordPress Settings API. Existing contributor browser rejection evidence is preserved. Remaining save hook, input and output checker findings still require grouped source review.

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

Review remaining checker findings against their owning source, fix confirmed defects in shared cause batches, then run Plugin Check on the actual distributable archive. Exclude development fixtures and evidence from the release archive. Recheck the oldest supported WordPress, WooCommerce, Polylang and PHP combinations, custom shop roles, variation stock behavior and upgrade preservation. The remote CI matrix has not run. Production rollout and Plugins Team submission have not occurred. These remain release verification requirements, not evidence of a new exploit.

## Additional fixes from the complete findings review

Separate local commits now encode category script values (4b02c13), restore translation transport certificate verification (26fbe5f), and escape status locale and path text (ecd05e6). Production direct file guards are committed as 9d093a2. The current complete suite passes with 83 tests and 225 assertions. These results supersede the earlier suite counts, while the earlier checker inventory remains a dated snapshot.

Additional production files changed are Taxonomies/Categories.php for script value encoding, Tools/TranslationsDownloader.php for certificate verification, Admin/StatusReport.php for text escaping and direct execution guard, and Views/social.php for direct execution guard. Additional regression files are CategoryOutputSecurityTest.php, TranslationTransportSecurityTest.php, StatusOutputSecurityTest.php and DirectFileAccessTest.php. Before and after test output, Chrome evidence and applicable checker scans use category-output, translation-tls, status-output and direct-access filename prefixes in docs/evidence. The tracker, journal and individual checker dispositions were updated for each independent change.

The full findings review remains incomplete. The earlier pending CSV import patch is still separate and uncommitted. No push, deployment or Plugins Team submission occurred.

## Output batch

The shared output batch corrects seven translated templates, gateway instructions for BACS, COD and cheque, the BACS bank heading and notice class attributes. Plain translation output uses esc_html_e or esc_html__. Intended rich text uses wp_kses_post and the endpoint translation URL uses esc_url. Gateway output is filtered after formatting. Flash notice class values use esc_attr while existing owned HTML messages and social widgets retain their composition. Public hooks, message identifiers, original translation strings and signatures remain unchanged.

The production file list and reasons are recorded in the journal. TemplateOutputSecurityTest.php and GatewayOutputSecurityTest.php add 17 shared regression cases. The complete suite passes 100 tests with 275 assertions. Before and after Chrome evidence and the full checker scan use the output-batch prefix. Plugin Check now reports 302 production rows, 92 errors and 210 warnings. Ten remaining output rows are explicitly reviewed native or owned HTML. The downloader error boundary, script wrapper callers and settings renderer still require source review. The batch inventory distinguishes those pending cases from corrected source and reviewed HTML. No complete release clearance is claimed.

| Output batch file | Reason |
| --- | --- |
| src/Hyyan/WPI/Views/admin.php | Escape the settings heading |
| src/Hyyan/WPI/Views/Admin/about.php | Escape labels and filter translated descriptive HTML |
| src/Hyyan/WPI/Views/Admin/getHelp.php | Escape help text and link labels |
| src/Hyyan/WPI/Views/Admin/support.php | Escape the heading and filter translated support HTML |
| src/Hyyan/WPI/Views/Messages/activateError.php | Escape dependency notice text and version labels |
| src/Hyyan/WPI/Views/Messages/endpointsTranslations.php | Escape the translation URL and label and filter the formatted notice |
| src/Hyyan/WPI/Views/Messages/support.php | Escape the heading and filter translated notice HTML |
| src/Hyyan/WPI/Gateways/GatewayBACS.php | Filter formatted customer instructions and bank details and escape the bank heading |
| src/Hyyan/WPI/Gateways/GatewayCOD.php | Filter formatted customer instructions in email and the thank you page |
| src/Hyyan/WPI/Gateways/GatewayCheque.php | Filter formatted customer instructions in email and the thank you page |
| src/Hyyan/WPI/Tools/FlashMessages.php | Escape notice class attributes and retain existing message HTML |
| tests/integration/TemplateOutputSecurityTest.php | Shared negative and preservation cases for seven templates |
| tests/integration/GatewayOutputSecurityTest.php | Shared negative and preservation cases for all gateways and notice attributes |
| docs/BATCHES.md and docs/evidence/batch-inventory.json | Group checker findings and forum reports and retain explicit dispositions |
| goal.txt, docs/ISSUES.md, docs/JOURNAL.md and this review | Record the batch workflow, verification, scope and remaining work |
| docs/evidence files with template-output, gateway-output and output-batch prefixes | Preserve baseline failures, current regressions, browser acceptance, lint and checker output |
| docs/evidence/plugin-check-security-review-normalized.json | Update individual output finding dispositions from the inspected batch |

## Request boundary batch

The next shared batch corrects the early language download request boundary and protected editor defaults. It validates and normalizes report and taxonomy request data while preserving native save verification and read only filters. The downloader exception values are data until their sole production caller displays them. Language.php now filters that HTML with wp_kses_post. The six exception escaping findings have that source disposition and a malicious translation regression.

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Language.php | Verify native language nonce and management permission, validate fields and locale, filter displayed error HTML |
| src/Hyyan/WPI/Product/Product.php | Validate source IDs and source read permission before copying defaults, sanitize language input |
| src/Hyyan/WPI/Reports.php | Validate route strings, normalize ID lists and retain every selected product when combining translations |
| src/Hyyan/WPI/Taxonomies/Categories.php | Validate copy and save values, retain native empty thumbnail clearing |
| src/Hyyan/WPI/Taxonomies/Attributes.php | Validate and sanitize the display route selector |
| src/Hyyan/WPI/Coupon.php | Guard missing or malformed display route input |
| src/Hyyan/WPI/Endpoints.php | Validate and sanitize the settings tab selector |
| tests/integration/RequestInputSecurityTest.php | Shared permission, malformed input, output boundary and behavior preservation regressions |
| docs/evidence files with request prefixes | Preserve failing cases, current suite, native browser workflows, caller boundaries, lint and full checker output |
| docs/BATCHES.md, goal.txt, docs/ISSUES.md, docs/JOURNAL.md and security review inventories | Record shared dispositions, verification, changed files and remaining work |

The complete suite passes 123 tests and 337 assertions. Current Plugin Check still has 298 production rows. Source dispositions cover 79 current request rows and 16 output rows. Native browser checks and the controlled callback checks are distinguished in the journal. The full compatibility matrix, remaining save paths, actual release archive scan and Plugins Team submission remain pending.

## Product synchronization request batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Utilities.php | Shared integer, registered language and readable source request helpers |
| src/Hyyan/WPI/Product/Meta.php | Validate screen and copy inputs before metadata synchronization |
| src/Hyyan/WPI/Product/Variable.php | Validate variation and default synchronization requests before mutation |
| src/Hyyan/WPI/Product/Variation.php | Use registered target language values when creating and translating variations |
| tests/integration/ProductTranslationInputTest.php | Shared malformed input, denied source and normal behavior regressions with callback cleanup |
| docs/evidence/product-request files | Baseline failures, passing suite, syntax checks, native Chrome creation and publish and complete Plugin Check output |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Current and historical source dispositions with previous raw inventory preserved |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Batch scope, every changed file, acceptance and remaining work |

The full suite passes 143 tests and 416 assertions. Native Chrome creation and publication preserve the copied defaults, variation price and weight. Malformed and denied requests are integration evidence. Current production inventory contains 265 rows with 147 pending review or correction. Navigation inputs, current URL handling, other batches, full compatibility matrix and release archive review remain pending.

## Navigation request batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Widgets/LayeredNav.php | Remove malformed filter shapes before rendering, normalize legacy IDs, sanitize and translate current WooCommerce slugs |
| src/Hyyan/WPI/Utilities.php | Validate server value shapes, unslash and sanitize current URL output and retain valid URL structure |
| tests/frontend/bootstrap.php and phpunit-frontend.xml | Run WordPress and WooCommerce in actual frontend context without the admin integration constants |
| tests/frontend/NavigationRequestTest.php | Cover malformed filters, current and legacy formats and multiple selection |
| tests/integration/CurrentUrlRequestTest.php | Cover normal, missing, malformed and control character server values |
| tests/bin/run.sh | Include the real frontend suite in the normal test command |
| docs/evidence/navigation-request files | Preserve baseline failures, full suites, native browser and controlled helper acceptance, syntax checks, cleanup and complete checker output |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Refresh current and historical dispositions without discarding prior reviewed findings |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Record changed files, verification scope and remaining findings |

All 151 tests pass with 435 assertions. Native browser filtering and selection pass after removal of instrumentation. Current URL helper proof uses controlled calls because no production caller exists. The helper preserves the request host as before and performs URL sanitization, not a host authorization decision. Current inventory contains 260 production rows with 137 pending review or correction. The complete maintenance release is not yet cleared.
