# Remaining compatibility findings

Current verified source bed0aee: 608 tests with 1737 assertions, 73 matching installed archive files. Installed static and runtime Plugin Check each report 33 findings, one error and 32 warnings. This removes 197 actual rows from the 230 finding baseline. Remaining batches contain 26 request checks, two overridable public hook checks, two prepared query checks and three other contracts. Zero findings and the complete published version upgrade feature matrix remain required. The goal remains active. No push, publication or release approval. Evidence uses plugin-check-product-prefill-batch.

These 33 findings remain open. Inspected boundaries are not fixes or accepted exceptions.

| ID | File and line | Finding | Inspected boundary |
| --- | --- | --- | --- |
| EX-001 | src/Hyyan/WPI/Utilities.php:179 | Recommended | Read only scalar getter unslashes and sanitizes the value, then requires a registered Polylang language. Mutation callers separately verify the request. |
| EX-002 | src/Hyyan/WPI/Utilities.php:179 | Recommended | Read only scalar getter unslashes and sanitizes the value, then requires a registered Polylang language. Mutation callers separately verify the request. |
| EX-003 | src/Hyyan/WPI/Utilities.php:182 | Recommended | Read only scalar getter unslashes and sanitizes the value, then requires a registered Polylang language. Mutation callers separately verify the request. |
| EX-004 | src/Hyyan/WPI/Utilities.php:259 | OutputNotEscaped | Immediate trusted script wrapper escapes the element ID. All seven production callers use fixed code or WordPress JSON with HTML hex flags for dynamic values. HTML escaping the script body would break those controls and the public returned script contract. |
| EX-005 | src/Hyyan/WPI/LocaleNumbers.php:94 | Recommended | Request flag selects numeric display formatting only. Input must be numeric. No request value is stored. |
| EX-006 | src/Hyyan/WPI/LocaleNumbers.php:115 | Recommended | Request flag selects a display separator only. No request value is stored. |
| EX-007 | src/Hyyan/WPI/Reports.php:45 | Recommended | Reports route selectors are scalar, unslashed and sanitized. They select report filters rather than a persisted setting. |
| EX-008 | src/Hyyan/WPI/Reports.php:45 | Recommended | Reports route selectors are scalar, unslashed and sanitized. They select report filters rather than a persisted setting. |
| EX-009 | src/Hyyan/WPI/Reports.php:45 | Recommended | Reports route selectors are scalar, unslashed and sanitized. They select report filters rather than a persisted setting. |
| EX-010 | src/Hyyan/WPI/Reports.php:46 | Recommended | Reports route selectors are scalar, unslashed and sanitized. They select report filters rather than a persisted setting. |
| EX-011 | src/Hyyan/WPI/Reports.php:46 | Recommended | Reports route selectors are scalar, unslashed and sanitized. They select report filters rather than a persisted setting. |
| EX-012 | src/Hyyan/WPI/Reports.php:46 | Recommended | Reports route selectors are scalar, unslashed and sanitized. They select report filters rather than a persisted setting. |
| EX-013 | src/Hyyan/WPI/Reports.php:103 | Recommended | Presence of product_ids selects the report query mode. Actual report identifiers are normalized before native report reads. |
| EX-014 | src/Hyyan/WPI/Reports.php:231 | Recommended | Identifier types and digit strings are filtered, then wp_parse_id_list normalizes the list before native queries. Only request memory is updated. |
| EX-015 | src/Hyyan/WPI/Reports.php:235 | Recommended | Identifier types and digit strings are filtered, then wp_parse_id_list normalizes the list before native queries. Only request memory is updated. |
| EX-016 | src/Hyyan/WPI/Reports.php:275 | Recommended | Identifier types and digit strings are filtered, then wp_parse_id_list normalizes the list before native queries. Only request memory is updated. |
| EX-017 | src/Hyyan/WPI/Reports.php:279 | Recommended | Identifier types and digit strings are filtered, then wp_parse_id_list normalizes the list before native queries. Only request memory is updated. |
| EX-018 | src/Hyyan/WPI/Reports.php:340 | Recommended | Sanitized language selector chooses combined report mode. It does not modify persisted data. |
| EX-019 | src/Hyyan/WPI/Reports.php:340 | Recommended | Sanitized language selector chooses combined report mode. It does not modify persisted data. |
| EX-020 | src/Hyyan/WPI/Reports.php:340 | Recommended | Sanitized language selector chooses combined report mode. It does not modify persisted data. |
| EX-021 | src/Hyyan/WPI/Widgets/LayeredNav.php:52 | Recommended | Public shop filtering rejects arrays, unslashes and sanitizes string input, then normalizes term IDs or slugs. Only request memory and the native legacy chosen attribute global are updated. |
| EX-022 | src/Hyyan/WPI/Widgets/LayeredNav.php:52 | Recommended | Public shop filtering rejects arrays, unslashes and sanitizes string input, then normalizes term IDs or slugs. Only request memory and the native legacy chosen attribute global are updated. |
| EX-023 | src/Hyyan/WPI/Widgets/LayeredNav.php:56 | Recommended | Public shop filtering rejects arrays, unslashes and sanitizes string input, then normalizes term IDs or slugs. Only request memory and the native legacy chosen attribute global are updated. |
| EX-024 | src/Hyyan/WPI/Widgets/LayeredNav.php:60 | Recommended | Public shop filtering rejects arrays, unslashes and sanitizes string input, then normalizes term IDs or slugs. Only request memory and the native legacy chosen attribute global are updated. |
| EX-025 | src/Hyyan/WPI/Widgets/LayeredNav.php:79 | NonPrefixedVariableFound | The legacy native WooCommerce chosen attribute global retains its public name. Values are sanitized and translated before assignment. Renaming it would disconnect existing consumers. |
| EX-026 | src/Hyyan/WPI/Product/Product.php:249 | Recommended | Request language only changes get_terms query arguments and must be a registered language. No write occurs. |
| EX-027 | src/Hyyan/WPI/Product/Meta.php:225 | Recommended | Validated product and language selectors choose field locking. The source metadata backfill was moved into the native product save callback. |
| EX-028 | src/Hyyan/WPI/Product/Meta.php:230 | Recommended | Validated product and language selectors choose field locking. The source metadata backfill was moved into the native product save callback. |
| EX-029 | src/Hyyan/WPI/Product/Meta.php:910 | DirectQuery | Prepared SKU query uses %s for the stored SKU and %d for the excluded product ID. Native posts cache versions invalidate these lookups after metadata, status and deletion writes. Cache entries expire after sixty seconds. Prepared predicates remain unchanged. Large catalog performance remains unmeasured. |
| EX-030 | src/Hyyan/WPI/Product/Variation.php:170 | DirectQuery | Prepared exact relationship query uses validated integer IDs represented as strings for text metadata. Native posts cache versions invalidate these lookups after metadata, status and deletion writes. Cache entries expire after sixty seconds. Prepared predicates remain unchanged. Large catalog performance remains unmeasured. |
| EX-031 | __init__.php:0 | trademarked_term | Existing WordPress.org directory slug begins with woo. Changing it changes installation and update identity. Plugins Team acceptance or a coordinated directory migration is required. |
| EX-032 | src/Hyyan/WPI/Admin/AbstractSettings.php:43 | DynamicHooknameFound | Existing public settings hook is generated as woo-poly.settings.%s_sections. The prefix is fixed and the suffix is the native settings ID. |
| EX-033 | src/Hyyan/WPI/Admin/AbstractSettings.php:61 | DynamicHooknameFound | Existing public settings hook is generated as woo-poly.settings.%s_fields. The prefix is fixed and the suffix is the native settings ID. |
