# Purchase rules acceptance batch


Purchase rules and downloadable variation batch: native category coupons include or exclude both translated parent categories while products are assigned only to their translated children. All four linked child variations pass inclusion and fail exclusion. A separate uncategorized control fails inclusion and passes exclusion. The exclusion fixture uses fixed_product because native is_valid_for_product does not evaluate fixed_cart coupons. This native API distinction is fixture setup, not a plugin defect.

Native German cart applies 5 to each variation, producing discount 10 and subtotal after discount 40. Native checkout rejects the excluded category coupon with its German validation message while preserving the included coupon and total. Clearing the rejected input uses the normal field and panel controls before order submission. A temporary owned package filter splits Small and Large into separate native shipping packages. Small inherits its parent class, producing ground 10.75. Large uses its own class override, producing ground 14.75. Selecting pickup only for Large changes the total from 65.50 to 53.25 and selecting ground restores 65.50. English native cart and checkout show the same discounts and package amounts with English labels and locale formatting.

The native German guest purchase records customer zero, language de, two variation lines with totals 15 and 25, two shipping lines 10.75 and 14.75, discount 10 and total 65.50 in CPT storage. Both linked Small and Large stocks fall from 20 to 19. Native admin completion preserves both quantities and totals and grants exactly one download for Large. Chrome follows the native guest download control through its URL within the page, returning HTTP 200 and the exact 34 byte synthetic file. A deliberately invalid download key returns HTTP 404 and cannot read the file. Keys and complete download URLs are omitted from evidence.

The first synthetic file location was rejected by native approved directory enforcement. Fixture setup was corrected by registering only a new owned subdirectory through the native approval API. Enforcement stays enabled. Both Large variants remain physical and retain the override class while becoming downloadable. The failed setup file and the final owned file are removed. This does not prove English guest download access, automatic virtual payment completion or real external download delivery.

No production source changed and no new defect was found. All 73 distributable source hashes still match the previously tested archive manifest. The current full suite remains the verified 289 tests and 939 assertions. Existing shipping tests freshly pass two cases with five assertions, fixture helper syntax and both diff checks pass. A full suite, rebuild and Plugin Check were not repeated because production and the distributable artifact are unchanged. The authoritative scan remains gateway-batch-plugin-check.json with 232 reviewed findings, 67 errors and 165 warnings.

Exact cleanup removes one guest order, two parents, four variations, five coupons, four categories, two shipping classes, one zone, two methods, one owned guest session, the synthetic file and its approved directory. Four original options and both language string snapshots are restored exactly. The temporary package filter and order recorder are removed, the owned guest browser tab is closed and mail transport stays suppressed. Native state confirms zero download permissions for the deleted order and enabled directory enforcement. No index, commit or publication action occurs. Eight shared acceptance groups remain, with the purchase group narrowed to the remaining guest checkout and storage combinations.

## Files changed and reasons

| File | Reason |
| --- | --- |
| work/purchase-rules-batch-extend.php | Configure or inspect isolated native category, variation shipping, multiple package and download fixtures |
| work/purchase-rules-batch-packages.php | Configure or inspect isolated native category, variation shipping, multiple package and download fixtures |
| work/purchase-rules-batch-inspect.php | Configure or inspect isolated native category, variation shipping, multiple package and download fixtures |
| work/purchase-rules-batch-download.php | Configure or inspect isolated native category, variation shipping, multiple package and download fixtures |
| work/purchase-rules-batch-download-inspect.php | Configure or inspect isolated native category, variation shipping, multiple package and download fixtures |
| work/guest-purchase-batch-cleanup.php | Validate owned categories, download file and approved directory and remove all owned fixtures |
| docs/ACCEPTANCE.md | Record verified acceptance, unchanged release checks and the exact remaining scope |
| docs/BATCHES.md | Record verified acceptance, unchanged release checks and the exact remaining scope |
| docs/ISSUES.md | Record verified acceptance, unchanged release checks and the exact remaining scope |
| docs/JOURNAL.md | Record verified acceptance, unchanged release checks and the exact remaining scope |
| docs/RELEASE-AUDIT.md | Record verified acceptance, unchanged release checks and the exact remaining scope |
| docs/PURCHASE-RULES-BATCH.md | Record verified acceptance, unchanged release checks and the exact remaining scope |
| goal.txt | Record verified acceptance, unchanged release checks and the exact remaining scope |
| handoff.md | Record verified acceptance, unchanged release checks and the exact remaining scope |
| docs/evidence/purchase-rules-batch-artifact-continuity.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-before-order.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-chrome.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-cleanup.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-completed-orders.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-download-mode.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-download-products.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-existing-shipping-tests.txt | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-extended.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-fixture.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-helper-lint.txt | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-native-downloads.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-native-rules.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-orders.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |
| docs/evidence/purchase-rules-batch-restored-state.json | Preserve actual native rules, browser, order, download, artifact continuity, syntax or cleanup evidence |

All development helpers and evidence are excluded from the distributable archive.
