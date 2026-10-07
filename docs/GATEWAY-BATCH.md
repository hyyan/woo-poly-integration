# Configured payment and HPOS guest batch

Four reported security issues remain corrected in the inspected source. Eight acceptance groups remain open. COMPAT-041 is corrected and verified. The current release is not ready for directory submission.


Configured payment and HPOS guest batch: COMPAT-041 corrects configured gateway titles and descriptions in native block cart and checkout data and configured payment title display in receipts. Gateways.php reuses its existing translators and the native block asset registry before WooCommerce registers payment data. Older environments without these block classes retain their existing path. The native order title getter translates display without changing stored order data. Two German block regressions and two German receipt regressions fail before correction. Four combined regression cases pass with 56 assertions for cheque, bank transfer and cash on delivery in both languages and both blocks. Supports and gateway settings are preserved. Native Chrome confirms all three German configured methods and unchanged English methods, and the German receipt title changes to Pruefung Scheck while stored title stays Batch cheque.

The same owned fixture batch verifies German and English classic guest purchases with HPOS and synchronization enabled. Totals are 45.75 and 42.50. The shared coupon global limit rejects the second language use. A native manual refund of one German variation unit records 17.50 and raises both linked Small stocks from 16 to 17 while Large stays 20. A German block guest purchase with HPOS synchronization disabled records 30.75 in OrdersTableDataStore and uses a shop_order_placehold post. These three guest orders preserve language, totals, shipping and linked variation quantities. This does not prove every checkout and storage combination.

Full suites pass 289 tests with 939 assertions. Fresh original variation security execution passes 26 tests with 78 assertions. PHP syntax, PHP 7.0 gateway syntax, shell syntax and both diff checks pass. The older WordPress 5.4.2, WooCommerce 4.0.0 and Polylang 2.7.4 installation safely executes the optional block guard. All 73 installed ZIP hashes match. Plugin Check retains 232 findings, 67 errors and 165 warnings, with identical source dispositions and an empty checker diagnostic stream. Checker dispositions are not directory approval.

Cleanup deletes three owned guest orders, one refund, four classic pages, two parents, four variations, three coupons, one shipping zone, two methods, one class and two owned guest sessions. Ten original options and both language string snapshots are restored exactly. HPOS and its synchronization return to their original disabled values, original block page IDs return, the recorder is removed and the owned guest browser tab is closed. No real mail or hosted payment transport occurs. No index, commit or publication action occurs. Eight shared acceptance groups remain. Remaining purchase cases include other guest storage combinations, multiple packages, variation shipping overrides, category coupon restrictions and downloads. Real external gateways and their callbacks remain separate.

## Files changed in this batch

| File | Reason |
| --- | --- |
| src/Hyyan/WPI/Gateways.php | Translate native block payment data and order payment title display using existing translators |
| tests/integration/BlockGatewayTranslationTest.php | Reproduce native German block and receipt failures and verify three methods in both languages |
| work/gateway-batch-mode.php | Configure or inspect only owned classic checkout, offline gateway and HPOS fixtures |
| work/classic-hpos-batch-mode.php | Configure or inspect only owned classic checkout, offline gateway and HPOS fixtures |
| work/classic-hpos-batch-storage-review.php | Configure or inspect only owned classic checkout, offline gateway and HPOS fixtures |
| work/guest-purchase-batch-cleanup.php | Remove validated owned refunds and classic pages and verify restored storage and page settings |
| docs/ACCEPTANCE.md | Record current findings, exact remaining acceptance scope, validation and next work |
| docs/BATCHES.md | Record current findings, exact remaining acceptance scope, validation and next work |
| docs/ISSUES.md | Record current findings, exact remaining acceptance scope, validation and next work |
| docs/JOURNAL.md | Record current findings, exact remaining acceptance scope, validation and next work |
| docs/SECURITY-REVIEW.md | Record current findings, exact remaining acceptance scope, validation and next work |
| docs/RELEASE-AUDIT.md | Record current findings, exact remaining acceptance scope, validation and next work |
| docs/evidence/batch-inventory.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| goal.txt | Record current findings, exact remaining acceptance scope, validation and next work |
| handoff.md | Record current findings, exact remaining acceptance scope, validation and next work |
| docs/evidence/classic-hpos-batch-before-nosync.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-cleanup.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-final-orders.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-fixture.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-german-order.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-mode.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-nosync-mode.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-orders.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-refund.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-restore-mode.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-restore-sync.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-restored-state.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-storage-review.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-storage.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/classic-hpos-batch-sync.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-chrome.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-installed-hashes.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-mode.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-native-client-nonce.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-older-guard.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-package-build.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-package-contents.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-package-install.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-php-lint.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-php70-lint.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-plugin-check.json | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-plugin-check.stderr.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-receipt-tests-before.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-suite.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-tests-before.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-tests-targeted.txt | Record the actual test, package, checker, native storage, browser or cleanup result |
| docs/evidence/gateway-batch-variation-security.txt | Record the actual test, package, checker, native storage, browser or cleanup result |

No production JavaScript changed. Native WooCommerce owns variation removal and already supplies the required nonce. Development helpers and evidence remain excluded from the ZIP.
