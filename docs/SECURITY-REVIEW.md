# Security maintenance review

Current state: Current verified source 4ac655a: 446 tests with 1498 assertions, 73 matching installed archive files. Installed static and runtime Plugin Check each report 64 findings, 23 errors and 41 warnings. This removes 166 actual rows from the 230 finding baseline. Remaining batches contain 33 request checks, 22 translation checks, two overridable public hook checks, two prepared query checks and five other contracts. Zero findings and the complete published version upgrade feature matrix remain required. The goal remains active. No push, publication or release approval. Evidence uses plugin-check-admin-route-batch.

The reported variation removal paths were inspected against baseline b5f7495 and current source. Local commit 01bfb80 corrects the variation lookup and removal callback. Local commit 0ec30b1 corrects the equivalent early featured product callback. This review adds regression cases and current acceptance evidence. A complete release clearance remains pending.

## Reported issues

| Report | Corrected behavior | Source |
| --- | --- | --- |
| Unvalidated variation IDs | Accept integer values and decimal digit strings, reject other shapes before any mutation, cast IDs before lookup and deletion, check variation post type | Product/Variable.php lines 317 to 340, Product/Variation.php lines 148 to 152 |
| Variable data in SQL | Prepare the relationship key and exact text metadata comparison with %s after integer normalization | Product/Variation.php lines 159 to 163 |
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

## Inline script output batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Utilities.php | Escape script ID attributes while retaining the executable code API |
| src/Hyyan/WPI/Taxonomies/Attributes.php | Encode PHP data, use DOM text and attributes and encode attribute search values |
| src/Hyyan/WPI/Product/Variable.php | Encode dialog data, filter permitted dialog HTML, use text for legacy warnings and restore the native default language UI restriction |
| src/Hyyan/WPI/Product/Meta.php | Encode fields locker and stored product type data and preserve empty selector extension behavior |
| tests/integration/InlineScriptSecurityTest.php | Nine shared unsafe input, normal behavior, selector and current Polylang action regressions with filter cleanup |
| docs/evidence/script-output and script-language files | Preserve baseline failures, full suite, native and controlled Chrome proof, syntax, cleanup and final complete checker output |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Preserve prior dispositions and record inspected script wrapper boundaries |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Record every changed file, compatibility findings, verification scope and remaining work |

All 160 tests pass with 460 assertions. Marker script data, normal attribute search, dialog behavior, wrapper behavior, empty selectors and current native language UI were checked in Chrome. The protected product type method has no current production caller and its retained extension compatibility path has a regression. All eight active wrapper callers were inspected. The default language change restriction remains a UI behavior and does not add a new server authorization policy. Five output checker rows and the wider release audit remain pending.

## Settings output and page maintenance batch

| File | Reason |
| --- | --- |
| vendor/class.settings-api.php | Escape attributes and plain labels, filter rich descriptions, preserve quoted selection and native editor, match owned tab IDs |
| src/Hyyan/WPI/Plugin.php | Escape action links and warning values, return native edit URLs, filter composed page warnings and authorize page maintenance |
| src/Hyyan/WPI/Admin/Settings.php | Escape native menu labels |
| tests/integration/SettingsRendererSecurityTest.php | Sixteen shared field, section, link, quote selection and editor regressions |
| tests/admin/bootstrap.php | Run actual normal admin context without AJAX bypass |
| tests/admin/PageMaintenanceSecurityTest.php | Denied roles, authorized manager warning and translated notice regressions |
| phpunit-admin.xml | Configure normal admin maintenance suite |
| tests/bin/run.sh | Run the admin suite with existing suites |
| docs/evidence/settings-output-tests-before.txt and settings-output-tests-targeted.txt | Preserve failing baseline and initial targeted acceptance |
| docs/evidence/settings-maintenance-tests-before.txt and settings-maintenance-deprecation.txt | Preserve failing role baseline and final clean admin checks |
| docs/evidence/settings-output-tests-after.txt and settings-output-lint.txt | Final four suites and syntax results |
| docs/evidence/settings-output-chrome.json | Native and controlled browser evidence, before and after fragment failure and rejected test action |
| docs/evidence/settings-native-saved.txt and settings-fixture-cleanup.txt | Retained Fields Locker value and restored fixture state |
| docs/evidence/settings-output-plugin-check.json and settings-output-plugin-check.stderr.txt | Complete final scan and stderr |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Preserve exact prior dispositions, refresh reviewed boundaries and current counts |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Batch scope, reasons, evidence and remaining work |

All 181 tests pass with 532 assertions. Settings nonce rejection, unchanged save and normal forms pass in Chrome. The disable Fields Locker test action was rejected by automatic approval review and was not executed. Authorized manager behavior has native context regression and controlled callback proof. Full role login sessions and automatic missing page and base language repair remain manual acceptance cases. The FlashMessages body API retains trusted developer HTML after inspection of every internal producer, including original social widgets. No blanket HTML encoding was applied to native forms or trusted executable widgets. The full maintenance release is not yet cleared.

## Query and diagnostics batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Coupon.php | Normalize request path once for all six optional logs, reject malformed shape and omit query data |
| src/Hyyan/WPI/Product/Meta.php | Pass raw SKU to the prepared string placeholder so stored quotes and backslashes match |
| tests/integration/QueryDiagnosticSecurityTest.php | Fourteen shared real query, immediate relationship and diagnostic input cases |
| work/query-diagnostics-browser.php | Temporary administrator and nonce protected local instrumentation, removed from active lab before completion |
| docs/evidence/query-diagnostics-tests-before.txt | Corrected fixture baseline with five genuine failures against committed source |
| docs/evidence/query-diagnostics-tests-targeted.txt and query-diagnostics-tests-after.txt | Fourteen targeted cases and all 195 tests with 575 assertions |
| docs/evidence/query-diagnostics-chrome.json | Actual controlled Chrome callback results and nonce rejection |
| docs/evidence/query-diagnostics-plugin-check.json and query-diagnostics-plugin-check.stderr.txt | Complete raw final scan and stderr |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Current counts and explicit source dispositions with prior evidence retained |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | File reasons, scope and remaining requirements |

Both direct SQL queries use prepared variable placeholders. Related variation reads stay fresh during mutation decisions. The bundled lookup queries use native WordPress query APIs. Optional diagnostics retain their existing enable flag and path context. Operational error reporting remains. Native editor SKU save rejects the same language duplicate and permits the German SKU. Coupon checkout and third party integration, large catalog profiling and older version verification remain required for wider release acceptance. Controlled API proof does not replace those native feature gates.

Additional evidence: `query-diagnostics-native-tests-before.txt` records four failing native false lookup cases. `query-diagnostics-native-fixture.json` identifies only disposable products. `query-diagnostics-native-state-cleanup.txt` records their final persisted SKUs before deletion. `query-diagnostics-lint.txt` records final syntax checks. The complete test set has 14 cases and 43 assertions. The temporary browser helper preceded the native workflow, so the browser evidence distinguishes those two proof scopes.

The empty SKU compatibility regression first failed after removing the false result shortcut. An explicit empty SKU guard restores the native result while nonempty stored values use the prepared query. Baseline evidence is `query-diagnostics-empty-sku-before.txt`. Original no product ID behavior remains.

## Translation contract batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Admin/Features.php | Remove extra translation argument and retain one literal with the exact original catalog key |
| src/Hyyan/WPI/Product/Meta.php | Use the plugin domain for both owned settings help labels |
| src/Hyyan/WPI/Tools/TranslationsDownloader.php | Document language and repository arguments while retaining installed lookup keys |
| src/Hyyan/WPI/Plugin.php | Document page warning placeholders |
| src/Hyyan/WPI/Privacy.php | Document privacy policy link placeholders, retain WooCommerce defaults and catalog |
| src/Hyyan/WPI/Coupon.php | Document the translated coupon code placeholder |
| src/Hyyan/WPI/Views/Messages/endpointsTranslations.php | Document link arguments and translated placeholder ordering |
| tests/integration/TranslationContractTest.php | Five shared domain, error, endpoint and stable feature key cases |
| work/translation-contract-browser.php | Read only authenticated marker instrumentation, removed from the active lab |
| docs/evidence/translation-contract-tests-before.txt | Reproduced missing plugin domain |
| docs/evidence/translation-contract-tests-targeted.txt and translation-contract-tests-after.txt | Five targeted cases and the full 200 test, 588 assertion suite |
| docs/evidence/translation-contract-chrome.json | Actual translated native settings markers and normal page after cleanup |
| docs/evidence/translation-contract-lint.txt | All touched PHP file syntax checks |
| docs/evidence/translation-contract-plugin-check.json and translation-contract-plugin-check.stderr.txt | Complete raw final scan and stderr |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Preserve current and historical source dispositions and complete remaining rows |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Changed file reasons, evidence scope and remaining gates |

Existing source keys and catalogs remain unchanged. Fully ordered translated placeholders work with retained legacy keys. Dependency text stays in the WooCommerce catalog and business strings stay in Polylang runtime translation. Static reviewed rows remain visible and require their explanations in the Plugins Team review package. Complete Arabic and Spanish coverage, native multilingual shipping and payment workflows and older stack acceptance remain unverified here. The maintenance release is not yet cleared.

## Shared stock and formatting request context batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Product/Stock.php | Prevent posted type flags from disabling stock synchronization outside authorized native product editing |
| src/Hyyan/WPI/LocaleNumbers.php | Preserve the distinct grouping separator fallback |
| tests/frontend/StockRequestContextTest.php | Three genuine frontend contexts verify persisted translated stock |
| tests/integration/StockAdminContextTest.php | Eight role, shape, screen and AJAX action contexts verify both stock hooks |
| tests/integration/LocaleNumberContextTest.php | Four native and legacy formatting cases preserve output and fallback |
| docs/evidence/runtime-context-stock-before.txt, runtime-context-stock-admin-before.txt, runtime-context-stock-route-before.txt and runtime-context-locale-before.txt | Reproduced failures before the corresponding fixes |
| docs/evidence/runtime-context-stock-after.txt and runtime-context-tests-after.txt | Targeted frontend pass and full 215 test, 624 assertion pass |
| docs/evidence/runtime-context-fixture.json and runtime-context-legacy-fixture.json | Disposable fixture IDs |
| docs/evidence/runtime-context-chrome.json | Native browser verification with sensitive receipt URLs and customer data omitted |
| docs/evidence/runtime-context-native-state.txt, runtime-context-legacy-stock.txt and runtime-context-final-native-state.txt | Persisted linked stock and actual order language checks |
| docs/evidence/runtime-context-cleanup.txt and runtime-context-final-cleanup.txt | Owned fixture cleanup |
| docs/evidence/runtime-context-lint.txt | Final PHP syntax checks |
| docs/evidence/runtime-context-plugin-check.json and runtime-context-plugin-check.stderr.txt | Complete raw final Plugin Check and stderr |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Current and historical source dispositions |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Changed file reasons, verification scope and remaining gates |

The ignored work/runtime-context-browser.php helper is local instrumentation and was removed from the active lab. Native state writers retain WordPress and WooCommerce verification. Pure formatting selectors preserve their existing read only contract. Original product edit synchronization remains. Native classic frontend and legacy admin AJAX checkout and editor saves pass. Block checkout, variation stock, HPOS, external integrations and older stacks remain separate acceptance gates. There are 33 pending production review rows and 383 forum reports awaiting content review. The complete maintenance release is not yet cleared.

## Public hook and symbol contract review

| File | Reason |
| --- | --- |
| docs/evidence/public-contract-source-review.json | Current call, resolved name and disposition for each of 25 public naming findings |
| docs/evidence/public-contract-runtime.json | Actual installed plugin hook constants and native status callback registration |
| docs/evidence/public-contract-chrome.json | Native status table, locale rows and help tips, with local file locations omitted |
| docs/evidence/public-contract-integration-tests.txt and public-contract-frontend-tests.txt | Existing relevant regressions, 17 cases and 62 assertions |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Restore explicit naming dispositions after current source review |
| docs/BATCHES.md, docs/CHECKS.md, docs/JOURNAL.md, goal.txt and this review | Scope, evidence, reasons and remaining requirements |

No production files changed in this review. All dynamic names keep the existing plugin prefix. Dependency hooks and retained legacy symbols preserve their contracts. Source naming review does not substitute for remaining native feature acceptance. Native status reports missing WooCommerce catalog files, which remains a catalog acceptance requirement. The current raw Plugin Check remains applicable because production source is unchanged. Eight production runtime rows remain pending, along with distribution and full report review.

## Server runtime compatibility batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Ajax.php and src/Hyyan/WPI/Endpoints.php | Use WordPress URL parsing at all three variable URL component reads |
| src/Hyyan/WPI/Product/Variation.php | Capture original timeout, use guarded WooCommerce helper and restore on all exits |
| tests/integration/RuntimeCompatibilityTest.php | Ten shared URL, persisted variation, timeout and exception cases, also run with native function disabled |
| docs/evidence/runtime-server-tests-before.txt and runtime-server-disabled-before.txt | Actual timeout failures and disabled function fatal errors |
| docs/evidence/runtime-server-tests-targeted.txt, runtime-server-disabled-after.txt and runtime-server-tests-after.txt | Ten batch cases, restricted hosting cases and all 225 tests with 639 assertions |
| docs/evidence/runtime-server-chrome.json | Actual protected production copier invocation, nonce rejection, timeout, linked variation and native fragment results |
| docs/evidence/runtime-server-lint.txt | Four final PHP syntax checks |
| docs/evidence/runtime-server-plugin-check.json and runtime-server-plugin-check.stderr.txt | Complete final raw Plugin Check and stderr |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Current counts and corrected server dispositions, prior contracts retained |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Scope, reasons, evidence and remaining gates |

The ignored work/runtime-server-browser.php instrument requires local environment, administrator capability and a native AJAX nonce. It deletes only its own products in finally and was removed from the active lab. Variation browser evidence is controlled invocation rather than native editor acceptance. Native translated cart fragments use the expected URL and response. Server success and exception behavior and restricted hosting are verified. Three remote badge rows, related social resource review, distribution review, report content review and the full compatibility matrix remain open.

## Admin network resource batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Views/badges.php | Local release, MIT license and accurate repository status controls replace offloaded images |
| src/Hyyan/WPI/Views/social.php | Direct sharing and GitHub actions preserve original share arguments without external scripts or iframe |
| src/Hyyan/WPI/Views/Messages/support.php | Local WordPress author icon and profile action replace the fixed remote avatar |
| tests/integration/AdminNetworkPrivacyTest.php | Six shared network, destination, share argument and malicious label cases |
| tests/frontend/SocialViewCompatibilityTest.php | Preserve the existing social rendering case in genuine frontend WordPress |
| tests/unit/DirectFileAccessTest.php | Retain isolated direct access tests, move the WordPress rendering case to its proper suite |
| tests/integration/TemplateOutputSecurityTest.php | Keep rich text and sharing action acceptance with local controls |
| docs/evidence/network-views-tests-before.txt, network-views-tests-targeted.txt and network-views-tests-after.txt | Six reproduced failures, six corrected cases and all 231 tests with 659 assertions |
| docs/evidence/network-views-chrome.json | Actual before and after native resources, links, support markers and cleanup |
| docs/evidence/network-views-source-review.md | Guideline source, actual archive state, template scope and proof limits |
| docs/evidence/network-views-lint.txt | Seven final PHP syntax checks |
| docs/evidence/network-views-plugin-check.json and network-views-plugin-check.stderr.txt | Complete final raw scan and stderr |
| docs/evidence/batch-inventory.json and plugin-check-security-review-normalized.json | Current counts and corrected network dispositions |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Scope, file reasons, verification and remaining gates |

The ignored work/network-views-browser.php helper was read only, restricted to local administrator rendering and removed after acceptance. Automatic third party counters and resources are replaced by direct actions. Core product, stock, checkout and language contracts are unchanged. Links and encoded share arguments are verified, but signed account publication remains manual and was not attempted. All current production checker rows have source dispositions, with reviewed raw errors and warnings retained. The distributable archive, full report review and native compatibility matrix remain outstanding.

## Distribution and metadata batch

| File | Reason |
| --- | --- |
| .exclude-list | Reuse existing deployment exclusions and omit new development fixtures, tests and evidence |
| .gitattributes | Omit development content from Git exports |
| tests/bin/package.sh | Build a reproducible local archive and full byte manifest without publishing |
| __init__.php | Match existing MIT license identifier |
| readme.txt | Correct changelog hierarchy, five tags and bounded historical upgrade notice |
| docs/DISTRIBUTION.md | Document safe packaging, actual artifact acceptance and remaining gates |
| docs/evidence/distribution-package-build.txt and distribution-package-contents.json | Repeat build hashes and complete 73 file manifest |
| docs/evidence/distribution-package-plugin-check-before.txt, distribution-package-plugin-check.json and distribution-package-plugin-check.stderr.txt | Intermediate and final complete artifact scans, update mode without exclusions |
| docs/evidence/distribution-package-metadata.json and distribution-readme-parser.json | Native archive header and parser verification |
| docs/evidence/distribution-tests-after.txt and distribution-lint.txt | All 231 tests with 659 assertions and root PHP syntax check |
| docs/evidence/distribution-fresh-site.json, distribution-chrome.json, distribution-installed-files.txt and distribution-fresh-browser-log.txt | Fresh native ZIP installation, actual browser forms and served asset hashes, all installed bytes and empty browser log delta |
| docs/evidence/batch-inventory.json | Preserve complete source rows and add actual artifact dispositions |
| docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Counts, inspected findings, reasons and remaining gates |

The local fixture configures only the separate fresh package site and is excluded from the archive. A local mail blocker prevents fixture email delivery. The original store remains intact. No legacy deployment or publication ran. Fresh CLI notices need investigation. Store workflow, older stack and language catalog acceptance remain open. The artifact scan is not clean, and established name and slug warnings need Plugins Team review.

## Store page and endpoint workflow batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Plugin.php | Preserve native block cart and checkout content during automatic translation while keeping classic shortcode filters |
| tests/admin/PageMaintenanceSecurityTest.php | Four real WordPress block and classic page format cases with source preservation and linked creation assertions |
| docs/evidence/store-pages-tests-before.txt, store-pages-tests-serialization-before.txt and store-pages-tests-after.txt | Two format and two serialization baseline failures and full 235 test, 671 assertion acceptance |
| docs/evidence/store-pages-native-data.json and store-pages-chrome.json | Native linked pages, exact content hashes, eight store routes, eight account routes and actual rendered German block cart |
| docs/evidence/store-pages-lint.txt | Production and regression PHP syntax checks |
| docs/evidence/store-pages-package-build.txt and store-pages-package-contents.json | Corrected actual archive and complete file manifest |
| docs/evidence/store-pages-plugin-check.json and store-pages-plugin-check.stderr.txt | Complete corrected installed package scan without exclusions |
| docs/evidence/batch-inventory.json | Actual current package scan and inspected row dispositions |
| docs/ACCEPTANCE.md, docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Scoped native acceptance, cause, reasons and remaining gates |

The automatic creation change operates behind the existing administration capabilities and feature setting. It copies content only from the corresponding source store page. Existing translated content is not replaced. Native block serialization is preserved without manual parsing. Classic extension shortcode filters remain. Custom translated endpoint slugs, filled checkout and account mutations have not been accepted by this batch.

Content insertion uses wp_slash so serialized block JSON retains escaped quotes. The final regression and native fixtures verify this through actual WordPress insertion and block parsing.

## Translated account endpoint batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Endpoints.php | Retain configured endpoint source slugs, build native target language endpoint links and preserve pagination or address values |
| tests/integration/EndpointLanguageSwitchTest.php | Nine real WordPress and Polylang cases with saved and restored catalogs, language and native query state |
| docs/evidence/endpoints-tests-before.txt, endpoints-tests-targeted.txt and endpoints-tests-after.txt | Eight baseline failures, nine corrected cases and all 244 tests with 680 assertions |
| docs/evidence/endpoints-chrome.json | Actual lost endpoint baseline, bilingual native links, pretty and plain navigation, actual marked view order and helper removal |
| docs/evidence/endpoints-lint.txt | Source and regression PHP syntax checks |
| docs/evidence/endpoints-package-build.txt and endpoints-package-contents.json | Installed actual corrected ZIP and complete byte manifest |
| docs/evidence/endpoints-plugin-check.json and endpoints-plugin-check.stderr.txt | Complete installed actual package check without finding exclusions |
| docs/evidence/endpoints-forum-review.json and wordpress-support-capture.json | Eight reviewed captured topics, source fingerprints, evidence and individual unresolved cases |
| docs/evidence/batch-inventory.json | Current package dispositions and report content status |
| docs/ACCEPTANCE.md, docs/BATCHES.md, docs/ISSUES.md, docs/JOURNAL.md, goal.txt and this review | Shared cause, scoped proof, counts and remaining gates |

Endpoint links keep native page origin and query data. This change does not bypass native account, order or password permissions and does not create account mutations. The read only local helper invoked native Polylang output behind local environment, owned table prefix and administrator guards and was removed. Quoted block page preservation from the prior batch remains covered by the full suite. Payment and receipt endpoints still require real checkout acceptance.

## Filled checkout and receipt batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Endpoints.php | Translate native endpoint URLs after late locale selection while preserving path prefix, values and query data |
| tests/integration/EndpointLanguageSwitchTest.php | Add five genuine endpoint regressions and restore hook, catalog and rewrite state |
| tests/bin/setup.sh | Select the verified WordPress 7.1.3 runtime through the native updater |
| docs/evidence/checkout-receipt-tests-before.txt, tests-targeted.txt and tests-after.txt | Baseline failures, targeted endpoint proof and full 249 tests with 685 assertions |
| docs/evidence/checkout-latest-tests.txt and checkout-package-fixture.json | Prior full suite and installed version or fixture identity evidence |
| docs/evidence/checkout-receipt-chrome.json, native-orders.json, refund.json and restored-cart.webp | Sanitized native purchase, persisted order language, stock and native refund evidence |
| docs/evidence/checkout-receipt-classic-mode.json, hpos-mode.json, hpos-block-mode.json and hpos-nosync-classic-mode.json | Native checkout and storage mode configuration evidence |
| docs/evidence/checkout-receipt-hpos-sync.txt, hpos-restore-sync.txt, cleanup-sync.txt and cleanup.json | Native synchronization and guarded restoration of owned fixtures |
| docs/evidence/checkout-receipt-package-build.txt, package-contents.json, plugin-check.json and lint.txt | Actual installed release, complete manifest, complete checker and syntax proof |
| docs/evidence/checkout-receipt-forum-review.json and wordpress-support-capture.json | Eight captured topic content dispositions with exact limits |
| docs/evidence/batch-inventory.json | Complete current package dispositions and report status |
| docs/TESTING.md, ACCEPTANCE.md, BATCHES.md, ISSUES.md, JOURNAL.md, goal.txt and this review | Runtime instructions, scoped acceptance, shared cause and remaining gates |

The new filter does not grant order access or mutate orders. It preserves native WooCommerce authorization and query values. Receipt URL evidence excludes order keys and customer details. The 12 native purchases and one refund cover only the documented simple offline matrix. All owned checkout fixtures and settings were restored. Raw Plugin Check findings remain visible with inspected dispositions. Full release clearance remains pending.

## Cart language and account visibility content review

| File | Reason |
| --- | --- |
| docs/evidence/cart-account-forum-review.json | Twenty full topic content dispositions, source fingerprints and individual unresolved checks |
| docs/evidence/cart-account-chrome.json | Four native order visibility cases in both languages and storage systems |
| docs/evidence/cart-account-native-orders.json | Stored languages and actual authoritative HPOS identity without customer details |
| docs/evidence/cart-account-sync.txt and cart-account-cleanup.json | Native synchronization, guarded fixture deletion and restored account list |
| docs/evidence/batch-inventory.json and wordpress-support-capture.json | Current content status and grouped next checks |
| docs/ACCEPTANCE.md, BATCHES.md, ISSUES.md, JOURNAL.md, goal.txt and this review | Scoped verification and remaining acceptance gates |

No production code was changed in this review. Native account permissions remain in WooCommerce. Only two owned marked fixture orders were created and deleted. Historical resolutions, source absence and current native acceptance are distinct dispositions. Full release clearance remains pending.

## Remaining checkout report classification

| File | Reason |
| --- | --- |
| docs/evidence/checkout-remaining-forum-review.json | Twenty five full captured page dispositions with source fingerprints and unresolved acceptance cases |
| docs/evidence/forum-topic-groups.json | Canonical topic membership and complete content review status across paginated captures |
| docs/evidence/batch-inventory.json | Current page or topic counts, grouping reference and content statuses |
| docs/evidence/wordpress-support-capture.json | Source capture dispositions without dropping original records |
| docs/ACCEPTANCE.md, BATCHES.md, ISSUES.md, JOURNAL.md, goal.txt and this review | Corrected counts, shared acceptance groups and explicit limits |

No production source or permissions changed. Historical confirmations and workarounds are distinguished from current runtime proof. All checkout routed captures are content reviewed, but native shipping, variable prices, existing data and external integration acceptance remain pending. Full release clearance remains pending.

## Native shipping acceptance

| File | Reason |
| --- | --- |
| docs/evidence/shipping-chrome.json | Passing native rate and classic label cases plus unresolved English block language mismatch |
| docs/evidence/shipping-native-class.json and shipping-native-class-cleared.json | Actual saved class assignment and removal on both linked products |
| docs/evidence/shipping-cleanup.json | Owned fixture deletion, settings restoration and native empty block cart proof |
| docs/evidence/batch-inventory.json and wordpress-support-capture.json | Shipping report dispositions narrowed to observed native acceptance |
| docs/ACCEPTANCE.md, BATCHES.md, ISSUES.md, JOURNAL.md, goal.txt and this review | Verified scope and unresolved COMPAT-027 |

No production code or permission checks changed. Native editor and cart requests use their existing WooCommerce capability and nonce handling. Calculated rate proof is limited to the controlled native flat rate and shared class fixture. Full block language compatibility remains unproved and a reproduced mismatch is explicitly open.

## Shared Store API cart and private translation review

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Cart.php | Reuse native cart schemas and existing product selection for block presentation, preserve stored identity and calculated numbers, restore locale globals, enforce shared private product and variation parent read permissions |
| tests/integration/StoreApiCartLanguageTest.php | Native REST and hydration regressions for locale selection, malformed cookies, dynamic prices, nonce denial, guest fallback and authorized private access, including variation links |
| docs/evidence/store-api-cart-tests-before.txt, context-before.txt, price-before.txt and private-before.txt under the same prefix | Record baseline and prototype failures rather than accepting only public happy cases |
| docs/evidence/store-api-cart-tests-targeted.txt and store-api-cart-tests-after.txt | Targeted earlier regression and current complete suite output, final 251 tests and 745 assertions |
| docs/evidence/store-api-cart-package-build.txt, store-api-cart-package-contents.json and store-api-cart-lint.txt | Final reproducible ZIP manifest, PHP syntax and all 73 installed byte checks |
| docs/evidence/store-api-cart-plugin-check.json and batch-inventory.json | Complete current artifact checker rows and retained inspected dispositions |
| docs/evidence/store-api-cart-fixture.json, store-api-cart-chrome.json and store-api-cart-cleanup.json | Sanitized owned fixture IDs, native final bilingual preload and quantity proof and restoration |
| docs/ACCEPTANCE.md, BATCHES.md, ISSUES.md, JOURNAL.md, goal.txt and this review | Corrected scope, new permission finding, test evidence and remaining release gates |

Reviewed the full pending production diff, including the earlier endpoint filter, and the new test fixture cleanup. The cart adapter handles successful cart responses with an items collection and does not change WooCommerce request permissions. Missing or invalid language selection returns the original response. Private product and variation parent translation must be publicly readable or authorized by WordPress. Currency formatting copies do not convert amounts or currency codes. The final archive has 231 reviewed raw findings, not zero findings. Variable cart flows, other Store API response shapes, guest purchases, multiple packages, third party currencies, catalogs and older supported runtimes remain manual acceptance requirements. Source remains uncommitted.

## Variable cart key and stock review

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Cart.php | Reuse native cart key for the same linked variation and item data, preserve distinct selections, validate linked aggregate stock through native stock APIs and escape the complete native catalog exception |
| tests/integration/CartVariationContinuityTest.php | Reproduce duplicate key and stock boundary failures, verify merged quantity, totals, retained source identity and separate variation or extension data |
| docs/evidence/variable-cart-fixture.json, variable-cart-chrome.json and variable-cart-cleanup.json | Owned fixture identity, bilingual native selected price and cart behavior, duplicate baseline, merged line, stock rejection and restoration |
| docs/evidence/variable-cart-tests-before.txt, variable-cart-stock-before.txt, variable-cart-tests-targeted.txt and variable-cart-tests-after.txt | Initial duplicate and stock failures and current full suite result |
| docs/evidence/variable-cart-package-build.txt, variable-cart-package-contents.json and variable-cart-lint.txt | Current ZIP manifest, syntax and installed byte proof |
| docs/evidence/variable-cart-plugin-check-before.json and variable-cart-plugin-check.json | Complete initial and final checker output with new exception rows corrected or source reviewed |
| docs/evidence/batch-inventory.json, wordpress-support-capture.json and checkout-remaining-forum-review.json | Current artifact dispositions and narrowed FORUM-275 and FORUM-399 acceptance scope |
| docs/ACCEPTANCE.md, BATCHES.md, ISSUES.md, JOURNAL.md, goal.txt and this review | Findings, proof, current counts and remaining acceptance gates |

Reviewed the complete pending production diff. Matching requires linked parent and variation metadata plus equality from WooCommerce's own key generator with original extension data. The callback temporarily removes only itself and restores it in finally. Invalid or absent duplicate metadata returns the native key. Stock sums only linked stock manager quantities and retains native backorder semantics through has_enough_stock. Native request validation and cart insertion still own purchase authorization and messages. Final checker includes one new retained dependency catalog domain row. Parent managed stock, global selected attributes, external cart key filters and broader variation acceptance remain explicit manual requirements. Changes remain uncommitted.

## Global term presentation and stock mode review

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Cart.php | Share explicit source and target selected term translation between native Store API view and key matching, leaving stored selection unchanged |
| tests/integration/StoreApiCartLanguageTest.php | Native failing and passing global term responses, stored selection and linked key assertions, isolated taxonomy and locale restoration |
| tests/integration/CartVariationContinuityTest.php | Real parent stock across distinct variations, permitted backorders and native sold individually regressions |
| docs/evidence/variable-stock-matrix-tests.txt, global-cart-tests-before.txt and global-cart-tests-after.txt | Native stock cases, reproduced wrong selected value and current full suite output |
| docs/evidence/global-cart-attribute-setup.json, global-cart-fixture.txt, global-cart-chrome.json and global-cart-cleanup.txt | Sanitized native fixture, bilingual term and stock mode proof, guarded restoration |
| docs/evidence/global-cart-package-build.txt, global-cart-package-contents.json and global-cart-lint.txt | Current artifact manifest, syntax and all installed hashes |
| docs/evidence/global-cart-plugin-check.json and batch-inventory.json | Complete current checker output and preserved inspected dispositions |
| docs/ISSUES.md, ACCEPTANCE.md, BATCHES.md, JOURNAL.md, goal.txt and this review | Current scope, counts, new finding and outstanding acceptance |

The new helper uses WordPress term APIs and Polylang registered language or translation APIs, with no raw SQL or request authority change. Source and target languages come from the actual product parents. The selected display copy uses the readable product returned by the existing private permission boundary, retaining original source data on denied private translations. Missing language or term translation retains the original selected attribute. Amounts and stored identities are unchanged. Final scan remains 232 reviewed rows, not a clean checker claim. Wildcards, missing translated terms, external option or key filters, guest purchase, variation order and refund and older supported stacks remain manual requirements. No index or history change.

## Category content and native archive review

| File | Reason |
| --- | --- |
| docs/evidence/category-forum-review.json | Individual content based dispositions for 26 topics and all 77 posts, with checked source fingerprints and exact unresolved subcases |
| work/review-category-topics.py, record-category-reviews.py, category-archive-fixture.php and category-archive-cleanup.php | Development extraction, inventory update and ownership guarded native fixture tooling, excluded from release |
| docs/evidence/category-archive-fixture.json and category-archive-seed-correction.json | Owned bilingual parent and child fixture and explicit correction of post language seed assignment |
| docs/evidence/category-archive-native-update.json and category-archive-chrome.json | Real product Update relationships, native category listings, page two and actual Polylang widget links |
| docs/evidence/category-archive-cleanup.json | Ownership verified fixture deletion and catalog or native widget settings restoration |
| docs/evidence/batch-inventory.json, wordpress-support-capture.json and forum-topic-groups.json | Current individual content statuses, exact topic counts and corrected current archive digest |
| docs/ISSUES.md, ACCEPTANCE.md, BATCHES.md, JOURNAL.md, goal.txt and this review | Batch findings, observed contracts and explicit limits |

Production source, permissions, request verification and the installed ZIP did not change in this turn. Source review confirms intentional translated category image and display metadata synchronization. Native category edit and product Update use their existing WordPress capability and nonce checks. Actual archive widget behavior is owned by the installed Polylang link builder and currently returns the translated category root. Do not execute historical SQL, site reinstalls, database cleanup or translation flag toggling as blanket fixes. Independent translated images and page retention remain separate behavior decisions. Existing data, third language, disabled settings, theme RTL, external tools and exact old stacks remain unverified. The existing raw checker report retains 232 reviewed rows and full release clearance remains open.

Stock batch file review: Product/Stock.php changes only the two existing temporary callback removal scopes to try and finally. It restores the same callback and priority on exceptional exits and preserves propagated exceptions, identifiers, stock math and normal execution. StockSynchronizationBoundaryTest.php adds real native simple and variation receiver failure tests and a sibling availability boundary. The stock boundary evidence files record failing and passing suites, native package build and lint, current checker results, installed hashes, sanitized purchase observations, fixture ownership and restoration. Stock forum review evidence and the inventory, capture and canonical groups record all fourteen topic dispositions and accurate remaining counts. ISSUES, BATCHES, ACCEPTANCE, JOURNAL and goal record current proof and limitations. Ignored work helpers perform owned lab fixture verification, restoration and topic recording. No additional runtime privilege, request, query or client contract is introduced by this stock callback fix.

Runtime default batch file review: Utilities.php changes one collected default assignment to an append in the existing no term or WP_Error path. It preserves previous default keys without changing request parsing, authorization, query behavior, translated global term handling or public signatures. ProductTranslationInputTest.php reuses its existing owned translation fixture to reproduce and verify two custom default keys. Runtime default evidence files record baseline failure, full passing suites, package build, native persisted defaults, bilingual selectors, native page Quick Edit, cleanup, lint, checker and hashes. Runtime forum review, inventory, capture and canonical topic groups retain individual historical resolutions and explicit unresolved cases. ISSUES, BATCHES, ACCEPTANCE, JOURNAL and goal record the updated scope and counts. Ignored work helpers reuse the existing owned fixture and record source fingerprint checked topic dispositions. No release ready or clean checker claim is made.

Classification batch file review: classification and support forum review evidence records source fingerprints, all captured post counts, individual actual symptoms and next checks. Inventory, capture and canonical topic groups preserve every original capture and update only review records and computed completion counts. ISSUES, BATCHES, ACCEPTANCE, JOURNAL and goal distinguish source inspection, historical author reports and actual runtime proof. Ignored recording helpers normalize only the report ledger. No production, client, query, request, dependency, test or release artifact change was introduced in this batch.

Attribute batch file review: attribute forum review evidence and inventory, capture and canonical groups record sixteen complete topic dispositions, fingerprint checked sources and computed remaining counts. Attribute settings native evidence records only selected metadata keys, with Chrome evidence showing unchecked saved state and restored state. Restoration evidence verifies backed up metadata and language options match after restoration without storing their contents in repository evidence. ISSUES, BATCHES, ACCEPTANCE, JOURNAL and goal record exact native proof and unresolved scopes. Ignored report helper applies these reviewed records. No production source, request, query, client, test or release artifact changed in this batch.

Email batch file review

`src/Hyyan/WPI/Emails.php` changes only the existing locale lifecycle. Existing object language selection, email filters, recipients and native class factory remain. Customer restore filters remain in place and shop completion uses the native `woocommerce_email_sent` action. Contexts restore the outer email during nested sends. No database query, request permission, recipient or transport change is introduced. The native completion action exists since WooCommerce 5.6. Shop completion on older WooCommerce releases is not verified. Third party classes that omit native completion or throw after the locale switch require separate checks.

`tests/integration/AccountEmailTranslationTest.php` adds three native customer and shop lifecycle cases and two nested cases. The baseline exposed the caller locale leak. Each test suppresses transport and deletes its own orders. Existing account field and order string tests are retained.

Updated ledger files `docs/evidence/batch-inventory.json`, `docs/evidence/wordpress-support-capture.json` and `docs/evidence/forum-topic-groups.json` retain captured bodies and identify reviewed continuations by canonical topic. `email-forum-review.json` records every reviewed page, fingerprint, post count, disposition and remaining check. Email locale evidence files record tests, native actions, catalog setup, cleanup, current package contents, installed hashes, lint and Plugin Check output. `docs/ISSUES.md`, `docs/BATCHES.md`, `docs/ACCEPTANCE.md`, `docs/JOURNAL.md`, `docs/SECURITY-REVIEW.md` and `goal.txt` record the resulting status and limits. Temporary scripts under ignored `work/` are lab fixtures and ledger tools, excluded from the release archive.

Reviewed the full pending production diff, including earlier Cart presentation and stock boundary changes, Stock callback restoration and Utilities default accumulation. Both unstaged and staged whitespace checks pass. Earlier native acceptance evidence remains applicable to unchanged paths. This is not a claim that all compatibility acceptance cases or Plugin Check findings have been eliminated.

Product synchronization batch file review

`src/Hyyan/WPI/Product/Variable.php` confines the existing false visibility filter to variation copying and restores its existing save callback even when a copy throws. It retains the original source and target selection, loop, filter priority, child copying and public signatures. The AJAX removal permission and nonce guards are untouched. No metadata normalization, linked product ownership or database query changes are introduced by this patch. Exceptions still propagate. The existing static repeated save guard remains unchanged and is not claimed to retry after an exception.

`tests/integration/VariationCopyLifecycleTest.php` adds success and exception cases against the real WordPress hook system and native variation copy method. Both failed before correction. They prove invisible inclusion during the copy, visibility restoration afterward and save callback recovery. Fixture orders are not created. Owned products, children, hook snapshots, request globals, language and user state are restored. Existing suites are retained.

`docs/evidence/product-forum-review.json` records each of the 86 reviewed pages with source fingerprints, post counts and individual dispositions. Inventory, captured report metadata and canonical group counts are updated without changing the captured source HTML or bodies. The variation copy evidence family records baseline, targeted and full suites, native browser and hook proof, availability, fixture cleanup, syntax checks, actual package manifest, installed hashes and complete Plugin Check output. `docs/ISSUES.md`, `docs/BATCHES.md`, `docs/ACCEPTANCE.md`, `docs/JOURNAL.md`, `docs/SECURITY-REVIEW.md` and `goal.txt` record the result and limits. Ignored work helpers prepare only marked local fixtures and stay outside the release archive. No original external plugin compatibility is inferred from these native checks.

Language report batch file review

Production files and installed release bytes are unchanged. `docs/evidence/language-forum-review.json` records all 62 inspected pages with fingerprints, post counts and individual dispositions. `docs/evidence/batch-inventory.json`, `docs/evidence/wordpress-support-capture.json` and `docs/evidence/forum-topic-groups.json` update reviewed metadata and canonical totals without modifying captured HTML or post bodies. `docs/ISSUES.md`, `docs/BATCHES.md`, `docs/ACCEPTANCE.md`, `docs/JOURNAL.md`, `docs/SECURITY-REVIEW.md` and `goal.txt` record completed content review and remaining runtime gates. The ignored language extraction and recording helpers do not enter the release package.

Shop URL boundary batch file review

`src/Hyyan/WPI/Pages.php` reuses the existing archive handler, WordPress `wp_parse_url` and standard regular expression matching to locate a complete source slug segment in the path. It replaces the last matching segment at its original URL offset, retaining original encoding and every other byte. It does not rebuild query arguments or change the host. Absent paths and absent complete source segments leave the URL unchanged. Existing search, non product archive and front page exclusions remain. No new public method, dependency, persistence or request action is introduced.

`tests/integration/SearchLanguageUrlTest.php` retains existing search and archive cases and adds seven independent URL component cases, all observed failing before correction. Cases include absolute and relative URLs, protocol relative authority, pagination, host only occurrences, query and fragment values and partial path slugs. Both language page fixtures are removed and original query and shop settings restored after every case.

The shop URL evidence family records baseline, targeted and full suites, native page URLs, temporary fixture setup and cleanup, sanitized Chrome results, actual package build and manifest, installed hashes, PHP lint and complete Plugin Check output. `docs/evidence/batch-inventory.json` updates current package hashes, scan location and finding line numbers while retaining identical inspected dispositions. `docs/ISSUES.md`, `docs/BATCHES.md`, `docs/ACCEPTANCE.md`, `docs/JOURNAL.md`, `docs/SECURITY-REVIEW.md` and `goal.txt` record the correction and remaining limits. Ignored work helpers remain outside the release. Reviewed the complete two file patch and both staged and unstaged whitespace checks. Earlier pending patches are unchanged.

Native product relationships batch file review

No production code or tests changed. The product creation evidence family records marked setup, native publication, both repeated Updates, duplicate link checks, selected duplicate child removal, bilingual Chrome selections, asserted invariants and exact cleanup. The ledger and package manifest remain unchanged. `docs/ACCEPTANCE.md` now distinguishes verified native creation and attributes from remaining conversion and configuration cases. `docs/ISSUES.md`, `docs/BATCHES.md`, `docs/JOURNAL.md`, `docs/SECURITY-REVIEW.md` and `goal.txt` record this batch and limits. Ignored work helpers create only owned fixtures, read their state and remove them with ownership checks. They are excluded from the release artifact.

The native duplicate removal observation confirms both the WooCommerce operation and the integration AJAX hook preserve unrelated source children when copied variation references have been cleared. This does not extend deletion permission claims beyond the previously verified administrator and shop manager security cases. No request nonce or private transport data is recorded.

Native conversion and metadata batch: the original Polylang Add German translation control published a simple translation with independent content. Native source conversion changed both products to variable. Generate variations and bulk pricing created four linked combinations in each language at 13. Catalog visibility synchronized hidden and visible in both directions of the setting. Native metadata settings disabled price, Variables and Taxes groups, leaving translated price fields editable. German bulk pricing at 17 survived its own Update and a source Update while English remained 13. Native storefront defaults selected the correct children and displayed the independent prices. No production patch was required. Two owned parents, eight children, two attribute taxonomies and eight terms were removed. Exact Polylang, metadata and feature settings were restored. Evidence: product-metadata-native-review.json and its native snapshots. Tax fields were absent with lab tax calculation disabled, so independent tax editing remains a manual check. Larger variation matrices, wildcard attributes and third language remain open. Source and artifact remain the verified 274 test batch with 836 assertions and the same 232 inspected checker rows.

Native tax metadata batch: enabled WooCommerce tax controls through its original General settings form. Published a simple German translation through the native Polylang control, preserving taxable status, reduced rate class and price 20. Disabling both tax metadata fields through the plugin settings allowed independent German None status and zero rate class. German Update and source Update retained those values. Reenabling the two fields and updating the source restored taxable status and reduced rate in the translated product. Native editor reload confirmed the restored fields. All prices stayed 20. No production patch was needed. Two owned products were removed and exact tax enable and metadata settings restored. Evidence: tax-metadata-native-review.json and related snapshots. This closes simple product independent tax editing, not tax rate calculation, suffix translation or variation tax overrides. No source changes required repeating the existing 274 tests, 836 assertions, installed artifact hashes or 232 finding checker scan.

Native media batch: assigned featured and gallery images through original WordPress media pickers, created a German product through native Polylang and verified synchronization with media translation disabled. The picker retained only the last individually selected image, so the second gallery image was added separately before asserting the full two image gallery. German frontend loaded the featured and both gallery images. Native gallery removal propagated empty values. Disabling gallery metadata synchronization let an independent German gallery survive both language Updates. Featured removal initially raced its native AJAX update, so it was repeated after the editor reported thumbnail ID minus one and removed the link. Both stored featured values then cleared. Activated Polylang media translation through its native settings, assigned fixture languages, and created a German attachment from its original plus control with translated alt text. The file was shared, gallery translation rendered German alt text and native featured assignment mapped source attachment 129 to translated attachment 132. No production patch was required. Owned two products and four attachment records were deleted, including their owned image files, and exact Polylang and metadata options restored. Evidence: media-acceptance-native-review.json, Chrome observations and corresponding database snapshots. Existing unlinked media, original third party gallery extensions and wider media migration remain separate checks. No source change required repeating the current full suite, package hashes or checker scan.

Current verification update: the corrected original variation paths were freshly inspected after the configured payment batch. VariationSecurityTest passes 26 cases with 78 assertions and all suites pass 289 tests with 939 assertions. The actual installed native WooCommerce client still sends security from delete_variations_nonce, created with delete-variations. The only two production AJAX callbacks and the only two direct queries were reinspected. The featured callback retains its existing role and nonce guards. Both variable queries remain prepared. New gateway display code performs no database mutation and introduces no request endpoint. All 73 installed archive hashes match and checker dispositions remain unchanged. Release requirements remain open.
