# Plugin Check inventory

Baseline report contains 368 findings: 144 errors and 224 warnings. These are checker classifications, not confirmed runtime defects. Every finding has an individual identifier and pending disposition in evidence/plugin-check-baseline-normalized.json.

The original CLI output is grouped by FILE followed by a JSON array for that file. It is not one JSON document. The normalized artifact preserves all findings and locations.

| Check code | Count |
| --- | --- |
| WordPress.Security.NonceVerification.Recommended | 76 |
| WordPress.Security.ValidatedSanitizedInput.MissingUnslash | 39 |
| WordPress.Security.ValidatedSanitizedInput.InputNotSanitized | 39 |
| WordPress.Security.EscapeOutput.OutputNotEscaped | 38 |
| WordPress.WP.I18n.TextDomainMismatch | 31 |
| WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound | 22 |
| WordPress.Security.EscapeOutput.UnsafePrintingFunction | 19 |
| WordPress.WP.I18n.MissingTranslatorsComment | 14 |
| WordPress.WP.I18n.NonSingularStringLiteralText | 12 |
| WordPress.PHP.DevelopmentFunctions.error_log_error_log | 8 |
| WordPress.Security.ValidatedSanitizedInput.InputNotValidated | 7 |
| WordPress.Security.NonceVerification.Missing | 7 |
| WordPress.Security.EscapeOutput.ExceptionNotEscaped | 6 |
| hidden_files | 4 |
| WordPress.DB.SlowDBQuery.slow_db_query_meta_key | 3 |
| WordPress.DB.SlowDBQuery.slow_db_query_meta_value | 3 |
| WordPress.WP.AlternativeFunctions.parse_url_parse_url | 3 |
| WordPress.DB.PreparedSQL.NotPrepared | 3 |
| trademarked_term | 3 |
| PluginCheck.CodeAnalysis.Offloading.OffloadedContent | 3 |
| WordPress.WP.I18n.MixedOrderedPlaceholdersText | 2 |
| missing_direct_file_access_protection | 2 |
| WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound | 2 |
| WordPress.WP.I18n.MissingArgDomain | 2 |
| WordPress.DB.DirectDatabaseQuery.DirectQuery | 2 |
| WordPress.DB.DirectDatabaseQuery.NoCaching | 2 |
| Squiz.PHP.DiscouragedFunctions.Discouraged | 2 |
| PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound | 1 |
| WordPress.WP.I18n.TooManyFunctionArgs | 1 |
| WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound | 1 |
| WordPress.WP.AlternativeFunctions.rand_rand | 1 |
| WordPress.WP.I18n.UnorderedPlaceholdersText | 1 |
| application_detected | 1 |
| PluginCheck.Security.DirectDB.UnescapedDBParameter | 1 |
| license_mismatch | 1 |
| outdated_tested_upto_header | 1 |
| upgrade_notice_limit | 1 |
| readme_parser_warnings_too_many_tags | 1 |
| readme_parser_warnings_trimmed_section_description | 1 |
| github_directory | 1 |
| WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | 1 |

Review the owning source before deciding the disposition. Preserve public hooks and native WooCommerce translations. Confirmed defects need regression and Chrome proof. Packaging findings need the actual distributable artifact. Document supported false positives precisely rather than changing working behavior to silence the checker.

## Dynamic public hook review

All 22 DynamicHooknameFound baseline findings were reviewed against the current call and hook definition. Every resolved name starts with woo-poly. The settings methods keep that prefix before their settings ID, and the gateway extension keeps it before the gateway ID. These are preserved public extension contracts, not names to replace. Each finding records its resolved name, current call location and definition evidence in the normalized inventory. This disposition addresses naming only. It does not prove the callback behavior or resolve other checker findings.

## Current security maintenance review

The new full tree scan completed with 499 findings, 173 errors and 326 warnings. Production source and scripts account for 345, tests for 30, and other files for 124. The new scan is not equivalent in scope to the original source snapshot. Preserve both reports. Current individual entries use REVIEW-CHECK identifiers in evidence/plugin-check-security-review-normalized.json. No PreparedSQL or UnescapedDBParameter findings remain in this scan. Remaining security input, save hook and output findings still require source review. The actual distributable archive has not been checked.

## Current public contract batch

All 25 current public naming rows have per-call source dispositions in evidence/public-contract-source-review.json. Twenty two dynamic hook expressions resolve to the existing woo-poly prefix, including generated settings and gateway names. The remaining rows are the dependency owned pll_save_post action, the plugin callback wpi_status_report and the retained legacy navigation global. Current WooCommerce uses WC_Query::$chosen_attributes rather than that global. The plugin also normalizes the filter query consumed by current WooCommerce, and existing frontend tests cover both representations. No naming change is required to correct a demonstrated defect.

The installed Polylang action passes ID, WP_Post and translation map, matching the corrected plugin import call. Native status Chrome renders all locale rows and help tips. Relevant existing tests pass 17 cases and 62 assertions. This review addresses public naming and dependency ownership. It does not claim that every email, gateway, category or cart extension has completed native feature acceptance. The production source has not changed since the runtime context Plugin Check, so that raw scan remains authoritative. Eight runtime and network rows remain pending.
