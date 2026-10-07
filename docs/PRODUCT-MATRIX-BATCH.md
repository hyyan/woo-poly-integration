# Shared product matrix acceptance

Shared product matrix batch complete. One native three language fixture verifies twenty four combinations per language, seventy two original selector cases, twelve wildcard selections, independent descriptions, custom values and parent and variation tax classes, plus shared unassigned media. COMPAT-045 corrects disabled custom attribute synchronization in both automatic Polylang metadata updates and explicit product copies. COMPAT-046 reuses existing variation synchronization after native CSV imports, including multiple rows for the same parent. A native two row import updates both linked translations without replacing independent custom values or tax classes. Native older copy and import pass on WordPress 5.4.2, WooCommerce 4.0.0 and Polylang 2.7.4. PHP 7.0 syntax passes. The complete suite passes 299 tests with 1023 assertions. All 73 installed artifact hashes match. Actual Plugin Check retains 231 findings, 66 errors and 165 warnings, with unchanged source dispositions and empty diagnostics. The full pending production diff was reviewed again. Cleanup removes three parents, seventy two children, one image, four pages, twenty seven terms, three attributes and 317 newly created Spanish catalog files. Native Polylang Delete removes Spanish. Six original options are restored and the fixture option and owned browser tab are removed. Two shared acceptance groups remain: runtime and data preservation, and external environment cases. Final release metadata and checker submission treatment remain open. No index, commit or publication action occurs.

## Corrected shared paths

Meta::wpi_filter_pll_metas prevents automatic synchronization of the mixed global and custom attribute array when custom synchronization is disabled. Meta::syncProductMeta explicitly copies selected global attributes while retaining the destination custom attributes. Its temporary filters match the source and target and are removed on success or exceptions. New translation copies and enabled custom synchronization retain their original behavior.

Variable::after_product_save also handles the original WooCommerce import completion hook when import synchronization is enabled. Variation imports resolve their actual parent and reuse duplicateVariations. Native imports bypass the editor page restriction and the repeated parent shortcut so every imported row synchronizes. Normal editor behavior remains unchanged.

## Evidence

| Case | Evidence |
| --- | --- |
| Native generation and translation creation | product-matrix-batch-chrome.json records all seventy two original combinations and twelve wildcard selections |
| Custom attribute regression | product-matrix-batch-custom-before.txt records the initial failing native copy. IndependentAttributeCopyTest covers enabled and disabled synchronization, new copies, automatic updates and exception cleanup |
| Native independent values | product-matrix-batch-independent-after-fix.json and product-matrix-batch-independent-repeat-update.json record separate language values and retained tax overrides |
| Native variation import regression | product-matrix-batch-import-before.txt records the failing price copy. VariableProductImportTest covers two rows for the same parent and retained tax overrides |
| Actual importer | product-matrix-batch-imported.json records the original source only price update. product-matrix-batch-imported-after-fix.json records both rows in all three languages |
| Browser price and media display | product-matrix-batch-chrome-after-fix.json records six native imported selections, three independent custom values and the shared image |
| Older stack | product-matrix-batch-older-native.json records actual native metadata copy, CSV import and independent tax preservation |
| Full verification | product-matrix-batch-suite.txt, package-contents.json, installed-hashes.json, plugin-check.json and syntax logs |
| Cleanup | product-matrix-batch-cleanup.json records exact owned fixture removal and configuration restoration |

The browser upload tool rejects local fixture paths. The native importer instead uses its original server file field, an owned CSV in the permitted uploads directory, original mapping and Run the importer controls. Both rows finish successfully. No custom import endpoint substitutes for native controls.

The first browser retries read before attribute AJAX completion and were not accepted as proof. Final checks wait for the actual save, repeat source Update and inspect native stored data and frontend display. An older fixture initially compared numeric metadata flags strictly, while WordPress 5.4 stores those flags as strings. Corrected comparison verifies their values and preserves the independent text exactly. Those fixture corrections are not plugin compatibility defects.

## Files changed in this batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Product/Meta.php | Preserve independent custom attributes through automatic metadata updates and explicit copies |
| src/Hyyan/WPI/Product/Variable.php | Synchronize native variable and variation imports through the existing owner |
| tests/integration/IndependentAttributeCopyTest.php | Verify copy settings, native metadata updates and filter cleanup |
| tests/integration/VariableProductImportTest.php | Reproduce and verify native multiple row import synchronization and independent taxes |
| docs/PRODUCT-MATRIX-BATCH.md | Record batch scope, corrections, evidence, cleanup and remaining requirements |
| docs/ACCEPTANCE.md | Close the two verified product groups |
| docs/ISSUES.md | Record COMPAT-045 and COMPAT-046 and update related report dispositions |
| docs/RELEASE-AUDIT.md | Refresh current artifact evidence and reviewed file purposes |
| docs/BATCHES.md, docs/JOURNAL.md, goal.txt and handoff.md | Record completion and the two remaining groups |
| docs/evidence/batch-inventory.json | Refresh the current package scan and artifact identity without changing findings dispositions |
| docs/evidence/product-matrix-batch files | Store the native, automated, package and cleanup evidence |

Owned preparation, inspection, older stack and cleanup helpers remain in the ignored work directory. They are excluded from the distribution archive.
