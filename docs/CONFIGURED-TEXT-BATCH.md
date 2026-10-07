# Configured text batch


Configured text batch: COMPAT-043 reproduces the store notice translator discarding native notice identity, hidden style and accessibility controls. Three native renderer regressions fail before correction. Privacy.php now replaces only the sanitized notice content within the original native markup. Native English and German notices retain identity and controls. Mouse dismissal, keyboard Enter dismissal and persistence after reload pass. Updating the owned notice changes its native ID and makes the new notice visible.

COMPAT-044 reproduces native tax rate names bypassing the existing Tax translator. Tax.php registers rate names from the existing native prepared rate APIs for standard and configured classes and applies the existing translator to the native rate label filter. Stored rate names, percentages and amounts are unchanged. The native regression fails before correction and passes both languages afterward. Polylang's actual admin translations page exposes the configured rate name. Native checkout shows German Steuer Pruefung and English Configured VAT, each with tax four and total twenty four. Price suffixes expand the existing inclusive price placeholder after translation. Registration and checkout privacy text, terms text and their page links use the matching language. The first registration fixture lacked the enabled registration option. The fixture was corrected with an exact backup and both native registration panels then passed.

Full suites pass 294 tests with 1000 assertions. Both production files pass PHP 7.0 syntax. Older WordPress 5.4.2, WooCommerce 4.0.0 and Polylang 2.7.4 native notice and tax APIs pass and restore their options, strings and owned rate. The rebuilt installed ZIP has 73 matching hashes. Plugin Check reports 231 findings, 66 errors and 165 warnings. Exactly one obsolete Privacy gettext domain finding disappears, with no new finding keys. Remaining dispositions are retained and diagnostic output is empty. This is not directory approval.

Exact cleanup deletes two owned products, eight pages and one tax rate and restores eighteen options and both language string snapshots. The fixture option is removed and the owned browser tab is closed. No orders or customer accounts are submitted in this batch. The locale group now retains native language addition and catalog download checks. Six shared acceptance groups remain. Source changes require no new public API or dependency. No index, commit or publication action occurs. Evidence uses the configured-text-batch prefix.

## Files changed

| Files | Reason |
| --- | --- |
| src/Hyyan/WPI/Privacy.php | Preserve native store notice markup, identity and controls |
| src/Hyyan/WPI/Tax.php | Register configured native tax names and use the existing string translator for display |
| tests/integration/StoreNoticeTranslationTest.php | Reproduce lost native attributes, retain dismiss text and verify sanitized translated markup |
| tests/integration/TaxLabelTranslationTest.php | Reproduce native label failure and verify bilingual display without stored rate mutation |
| work/configured-text-batch-setup.php and cleanup.php | Share one guarded bilingual fixture and restore exact options and language strings |
| docs/evidence/configured-text-batch files | Record native failures, passing suites, package hashes, checker delta, browser proof, older API proof and cleanup |
| docs/evidence/batch-inventory.json | Record current installed checker findings and preserve remaining reviewed dispositions |
| docs/ACCEPTANCE.md, docs/ISSUES.md, docs/JOURNAL.md, docs/BATCHES.md, docs/RELEASE-AUDIT.md, goal.txt and handoff.md | Track both fixes and narrow the remaining locale cases |

## Limits

The configured text proof uses native classic checkout. Block checkout specific privacy presentation is not established by this fixture. Catalog download and native language addition remain open. External themes that replace notice markup retain their supplied markup and need their actual environment. No published release, directory clearance or real payment or mail delivery is claimed.
