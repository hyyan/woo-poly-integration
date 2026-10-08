# Remaining compatibility findings

Current verified source df79acb: 687 tests with 1842 assertions, 73 matching installed archive files. Installed static and runtime Plugin Check each report 13 findings, one error and 12 warnings. This removes 217 actual rows from the 230 finding baseline. Remaining batches contain six request checks, two overridable public hook checks, two prepared query checks and three other contracts. Zero findings and the complete published version upgrade feature matrix remain required. The goal remains active. No push, publication or release approval. Evidence uses plugin-check-report-range-batch.

These 13 findings remain open. Inspected boundaries are not fixes or accepted exceptions.

| ID | File and line | Finding | Inspected boundary |
| --- | --- | --- | --- |
| EX-001 | src/Hyyan/WPI/Utilities.php:272 | OutputNotEscaped | Immediate trusted script wrapper escapes the element ID. All seven production callers use fixed code or WordPress JSON with HTML hex flags for dynamic values. HTML escaping the script body would break those controls and the public returned script contract. |
| EX-002 | src/Hyyan/WPI/LocaleNumbers.php:94 | Recommended | Request flag selects numeric display formatting only. Input must be numeric. No request value is stored. |
| EX-003 | src/Hyyan/WPI/LocaleNumbers.php:115 | Recommended | Request flag selects a display separator only. No request value is stored. |
| EX-004 | src/Hyyan/WPI/Widgets/LayeredNav.php:52 | Recommended | Public shop filtering rejects arrays, unslashes and sanitizes string input, then normalizes term IDs or slugs. Only request memory and the native legacy chosen attribute global are updated. |
| EX-005 | src/Hyyan/WPI/Widgets/LayeredNav.php:52 | Recommended | Public shop filtering rejects arrays, unslashes and sanitizes string input, then normalizes term IDs or slugs. Only request memory and the native legacy chosen attribute global are updated. |
| EX-006 | src/Hyyan/WPI/Widgets/LayeredNav.php:56 | Recommended | Public shop filtering rejects arrays, unslashes and sanitizes string input, then normalizes term IDs or slugs. Only request memory and the native legacy chosen attribute global are updated. |
| EX-007 | src/Hyyan/WPI/Widgets/LayeredNav.php:60 | Recommended | Public shop filtering rejects arrays, unslashes and sanitizes string input, then normalizes term IDs or slugs. Only request memory and the native legacy chosen attribute global are updated. |
| EX-008 | src/Hyyan/WPI/Widgets/LayeredNav.php:79 | NonPrefixedVariableFound | The legacy native WooCommerce chosen attribute global retains its public name. Values are sanitized and translated before assignment. Renaming it would disconnect existing consumers. |
| EX-009 | src/Hyyan/WPI/Product/Variation.php:170 | DirectQuery | Prepared exact relationship query uses validated integer IDs represented as strings for text metadata. Native posts cache versions invalidate these lookups after metadata, status and deletion writes. Cache entries expire after sixty seconds. Prepared predicates remain unchanged. Large catalog performance remains unmeasured. |
| EX-010 | src/Hyyan/WPI/Product/Meta.php:914 | DirectQuery | Prepared SKU query uses %s for the stored SKU and %d for the excluded product ID. Native posts cache versions invalidate these lookups after metadata, status and deletion writes. Cache entries expire after sixty seconds. Prepared predicates remain unchanged. Large catalog performance remains unmeasured. |
| EX-011 | __init__.php:0 | trademarked_term | Existing WordPress.org directory slug begins with woo. Changing it changes installation and update identity. Plugins Team acceptance or a coordinated directory migration is required. |
| EX-012 | src/Hyyan/WPI/Admin/AbstractSettings.php:43 | DynamicHooknameFound | Existing public settings hook is generated as woo-poly.settings.%s_sections. The prefix is fixed and the suffix is the native settings ID. |
| EX-013 | src/Hyyan/WPI/Admin/AbstractSettings.php:61 | DynamicHooknameFound | Existing public settings hook is generated as woo-poly.settings.%s_fields. The prefix is fixed and the suffix is the native settings ID. |
