# Native terms and navigation acceptance


Navigation acceptance batch complete. Native attribute rename and unassigned term assignment pass. Both filtered shop archives and single result search redirects isolate the correct language. Category token paths and native Polylang links resolve both translated products. Shop front pages pass in both languages after native language cache refresh and a fresh browser context. Three copied German cart headings are translated through the native block editor and Save control while English content stays unchanged. Original block content and five options are restored. Fifteen existing navigation tests pass with 28 assertions and all 73 artifact hashes remain identical. Production source is unchanged, so the 294 test suite, 1000 assertions and 231 finding scan remain current. Four shared acceptance groups remain. Full evidence and fixture corrections are in NAVIGATION-BATCH.md. No index, commit or publication action occurs.

## Native proof

| Case | Observed result |
| --- | --- |
| Attribute rename | Original WooCommerce attribute Update persists Navigation renamed. Native product attributes show Navigation Neu in German and Navigation renamed in English |
| Existing unassigned term | Existing term starts without a language. Original native term Update assigns English without recreating the term |
| Shop queries | Price ordering and minimum and maximum price queries return only the selected language fixture product |
| Product search | Each single result search redirects to the correct language product |
| Category token paths | Both products resolve beneath their own category slug. Native Polylang switcher links preserve each translated category path |
| Shop front page | Both home routes display only their own product. With the language front URL setting enabled, German resolves at the language root and the native switcher returns to the English site root |
| Copied block content | Three German cart headings change through the native editor and original Save button. Frontend German headings change and English headings remain unchanged. Cleanup restores original German content |

## Fixture corrections and scope

The first product setup saves both products under the English context, causing both to use the English category and cross language related products to appear. Correcting the fixture context yields each own category and no cross language recommendation. This was not reproduced as a production defect.

The front URL fixture requires Polylang's native language cache refresh after direct option changes. The first browser context retains an earlier redirect. A fresh context resolves the German language root correctly after refresh. No homepage production correction is made.

The cleanup initially compares an integer option cache value with its original string value. WordPress persists scalar option values as strings, so the assertion is corrected to compare serialized storage representations. Cleanup safely resumes, skips already removed records and avoids another update of already restored block content. No failed cleanup is reported as complete.

Copied block headings are authored content. Their translations are edited through WordPress and are not replaced automatically in source pages. The existing block layout and controls remain preserved. Custom themes and alternate domain environments retain separate external requirements.

## Files changed

| Files | Purpose |
| --- | --- |
| work/navigation-batch-setup.php | Create one owned bilingual taxonomy and product fixture and snapshot configuration and strings |
| work/navigation-batch-switcher.php | Render the original Polylang switcher through its native API |
| work/navigation-batch-cleanup.php | Remove owned products, categories, terms and attribute and restore options, strings and existing block content |
| docs/evidence/navigation-batch files | Record native controls, persisted data, routes, browser content, regressions, artifact continuity and cleanup |
| docs/ACCEPTANCE.md, docs/ISSUES.md, docs/JOURNAL.md, docs/BATCHES.md, docs/RELEASE-AUDIT.md, goal.txt and handoff.md | Close the defined navigation group and record four remaining groups |

Cleanup removes two products, two categories, three terms and one attribute. Five options and both language string snapshots are restored. The existing German cart content is restored exactly. The temporary switcher and fixture option are removed, both owned frontend tabs close and the admin tab returns to its original home page. No order, account or external transport is submitted. WordPress revision history from the native page edit remains as normal history. Production source is unchanged and no full suite, package build or checker repetition is needed.
