# Guest checkout and storage matrix


Guest checkout and storage matrix batch: all twelve cells pass on the current installed artifact. Classic and block checkout each complete guest purchases in English and German with legacy CPT storage, HPOS with synchronization enabled and HPOS with synchronization disabled. One owned fixture and one isolated guest context are reused. Each cell uses native product Add to cart, native checkout fields and the original Place order button. Every receipt is checked against a native stored order snapshot taken before the next storage mode. Every order has customer zero, quantity one of the correct language Small variation, shipping 10.75, total 30.75 and the correct language. Native configured payment title and description and shipping labels follow the checkout language.

HPOS cells use OrdersTableDataStore. Synchronization disabled cells create shop_order_placehold posts while the canonical order remains in HPOS. Synchronization enabled cells create shop_order backups. Legacy cells use WC_Order_Data_Store_CPT. Native synchronization completes before changing storage authority and all earlier order languages, amounts, variation IDs and mode markers survive the transition. Shared Small stock declines from 20 to eight in both languages after twelve orders. Large remains twenty. Receipt pages contain no visible errors. Real hosted payment and mail delivery are not exercised.

No production source changes or new defects were needed. All 73 distributable source hashes match the existing tested manifest. The full suite remains the verified 289 tests and 939 assertions, with the existing 232 finding installed package scan and inspected dispositions. Fixture helper syntax and both diff checks pass. The full suite, rebuild and checker were not repeated for unchanged production source. This batch closes the defined filled purchase and orders acceptance group together with the prior coupon, shipping, refund and download batches. Seven shared acceptance groups remain.

Cleanup synchronizes both stores, deletes twelve owned orders, four classic pages, two parents, four variations, three coupons, one zone, two methods, one class and one owned guest session. Eight original options and both language string snapshots are restored exactly. The recorder is removed and the owned guest tab is closed. Native APIs independently confirm every order is absent from both CPT and HPOS and the original block pages and disabled storage options are restored. No index, commit or publication action occurs.

## Verified cells

| Checkout and storage | Language | Order | Native store | Total |
| --- | --- | --- | --- | --- |
| block-sync | de | 203 | HPOS | 30.75 |
| block-sync | en | 204 | HPOS | 30.75 |
| block-nosync | de | 205 | HPOS | 30.75 |
| block-nosync | en | 206 | HPOS | 30.75 |
| classic-nosync | de | 207 | HPOS | 30.75 |
| classic-nosync | en | 208 | HPOS | 30.75 |
| classic-sync | de | 209 | HPOS | 30.75 |
| classic-sync | en | 210 | HPOS | 30.75 |
| block-cpt | de | 211 | CPT | 30.75 |
| block-cpt | en | 212 | CPT | 30.75 |
| classic-cpt | de | 213 | CPT | 30.75 |
| classic-cpt | en | 214 | CPT | 30.75 |

## Files changed and reasons

| File | Reason |
| --- | --- |
| work/guest-storage-matrix-mode.php | Switch only owned checkout pages and backed up storage options for the six modes |
| work/guest-storage-matrix-inspect.php | Assert native order language, variation, quantity, amount, shipping, storage and linked stock |
| work/guest-purchase-batch-recorder.php | Tag only owned native orders with their active fixture storage mode |
| docs/ACCEPTANCE.md | Record the complete native matrix and close the defined purchase acceptance group |
| docs/BATCHES.md | Record the complete native matrix and close the defined purchase acceptance group |
| docs/ISSUES.md | Record the complete native matrix and close the defined purchase acceptance group |
| docs/JOURNAL.md | Record the complete native matrix and close the defined purchase acceptance group |
| docs/RELEASE-AUDIT.md | Record the complete native matrix and close the defined purchase acceptance group |
| docs/GUEST-STORAGE-MATRIX.md | Record the complete native matrix and close the defined purchase acceptance group |
| goal.txt | Record the complete native matrix and close the defined purchase acceptance group |
| handoff.md | Record the complete native matrix and close the defined purchase acceptance group |
| docs/evidence/guest-storage-matrix-artifact-continuity.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-before-cpt.txt | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-before-nosync.txt | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-block-cpt-mode.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-block-cpt-orders.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-block-nosync-mode.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-block-nosync-orders.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-block-sync-mode.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-block-sync-orders.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-chrome.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-classic-cpt-mode.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-classic-cpt-orders.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-classic-nosync-mode.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-classic-nosync-orders.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-classic-pages.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-classic-sync-mode.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-classic-sync-orders.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-classic-sync-sync.txt | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-cleanup.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-fixture.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-helper-lint.txt | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-restore-mode.txt | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-restore-sync.txt | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |
| docs/evidence/guest-storage-matrix-restored-state.json | Record native matrix settings, receipts, stored orders, stock, syntax, artifact continuity or cleanup |

Development helpers and evidence remain excluded from the distributable archive.
