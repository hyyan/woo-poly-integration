# Work journal

## 2026-10-06 — Discovery and plan
- User authorized clone/tool installation/local WordPress setup, non-breaking modernization, issue/security audit, Chrome-only browser verification, and separate conventional commits after verification.
- Read supplied private WordPress closure notice. Tracked SQL injection and missing authorization separately; no disclosure/publication performed.
- Cloned requested repository into exact authorized directory. Baseline b5f7495180d37020204a6f725307eb6ecdc29cf0. No plugin edits or commits.
- Read applicable /Users/Shared/AGENTS.md; no repository AGENTS.md/CLAUDE.md found.
- Cortex scopes/search consulted; no WooPoly-specific history found.
- Chrome DevTools MCP list_pages succeeded. Earlier unified-browser Chrome lookup was unavailable; actual Chrome MCP is working and will be sole browser test surface.
- Docker installed (29.6.1), initially stopped; started installed Docker Desktop and confirmed daemon ready.
- PHP/Composer/WP-CLI absent from host; use isolated containers rather than broad host installs.
- Saved complete public API inventory: 20 open GitHub issues. GitHub CLI authentication invalid; public APIs remain available.
- Source confirms raw SQL in Variation::getRelatedVariation and unguarded priority-9 removal callback before WooCommerce checks. Featured-product priority-5 callback also needs independent authorization audit.
- Official WordPress release archive reports latest 7.1.2; WordPress recommends PHP >=8.3 and MariaDB >=10.11/MySQL >=8.0. Verify distributable artifacts and record actual installed versions before claiming acceptance.
- Next: dependency artifact verification, isolated environment setup, full external issue inventory, baseline Chrome fixtures.

- Verified official plugin API: WooCommerce 11.1.2, Polylang 3.8.10, Plugin Check 2.1.0. WordPress 7.1.2/PHP 8.5 container manifest exists. Added isolated localhost-only test stack, local runtime excluded from Git.
- Found public CVE-2026-24585 missing-authorization advisory; affected operation not yet established. Added security investigation; no assumption that it is the same issue as closure notice.

## Baseline environment and evidence
- WordPress 7.1.2 / PHP 8.5.11 / WooCommerce 11.1.2 / Polylang 3.8.10 / Storefront 4.6.2 / Plugin Check 2.1.0 installed; intl extension present. WordPress image digest sha256:7e9c763f3b8aeec88e38c6032390337bf817311ea034dad4a732f5e1ed2165b7.
- Chrome verified frontend, WordPress dashboard and WooPoly settings. Plugin activates with English/German configured. WooCommerce flags undeclared feature compatibility; baseline PHP 8.5 warnings in Shipping.php recorded.
- Plugin Check baseline: 368 findings (144 errors / 224 warnings), saved unfiltered report; not yet resolved.
- Archived all 423 forum topics without download failures; saved all 550 closed GitHub issue/PR records across 6 pages and all open-issue discussions. Downloaded is not equivalent to fully triaged.
- Created disposable bilingual shop/cart/checkout/account pages, variable products with linked variations, simple products and subscriber/shop-manager users. Saved fixture IDs.
- Next: reproduce security behavior using Chrome, then narrow security patch with before/after proof.

## Security reproduction (Chrome MCP)
- Subscriber login in isolated Chrome context confirmed. POST remove_variations with SQL payload SLEEP(2) returned 403/-1 but took 2054 ms. Error response did not prevent earlier SQL execution.
- Subscriber POST without nonce variation_ids[]=18 returned 403/-1, but real database verification shows source variation 18 and German translation 21 permanently removed from disposable fixtures. Untouched variations 19/22 remain.
- Benign ID zero caused baseline PHP fatal due to casting false related lookup to array(false); include zero/nonexistent ID robustness in SEC-001/002 tests.
- Chrome variable-product page shows critical error; log confirms LocaleNumbers.php:97 NumberFormatter::format string TypeError (GH-571).
- User expanded scope: automated regressions for each fix, prefer unit tests where appropriate, CI on every pull request and push. Added to goal plan.

## Continued verification
- Revalidated current lab and source. Existing security and locale changes were uncommitted. Repeated the existing suites: 8 unit tests and 8 integration tests passed on PHP 8.5.11.
- Chrome MCP profile was held by an earlier automation instance. User explicitly authorized stopping that specific instance. Chrome MCP recovered.
- Evidence save paths were refused by Chrome MCP workspace restrictions. Captured results inline and saved returned screenshot bytes through the authorized filesystem operation instead.
- GH-571: English and German variable pages rendered without a critical error, preserved empty weight/dimension strings, showed 12.50 and 12,50 respectively, and each selected Large variation was added to the cart. The admin variation endpoint retained regular price 12.50 and blank dimensions.
- Discovered stale fixture IDs 110/113 had been deleted by prior testing. Their rejection results were superseded by repeats against freshly created and verified pair 238/239.
- SEC-001/002: administrator missing/invalid nonce, SQL expression, nested batch and zero/nonexistent IDs left pair 238/239 and siblings 111/114 present. SQL expression returned in 36 ms, compared with the earlier baseline delay of 2054 ms.
- Subscriber and customer logins were confirmed through Chrome. Removal requests were denied with HTTP 403. Database retained linked variations.
- Normal shop manager UI removal of 238 deleted 238/239 together. Normal administrator UI removal of fresh 240 deleted 240/241 together. Siblings 111/114 survived both operations.
- Added administrator, customer and corrupt relationship integration cases. Global feature acceptance, other public reports, featured AJAX and all checker findings remain open.
- Expanded removal suite passed: 11 tests, 25 assertions. Administrator UI acceptance also passed, preserving sibling variations. No full compatibility claim is made.

- Original stock acceptance: changing variation stock from 10 to 7 and completing the product Update step synchronized English 111 and German 114 to 7, preserving regular price 12.50. The earlier intermediate variation Save step alone retained translated stock 10, consistent with Stock.php deferring admin synchronization until product update.
- Automatic approval review rejected Git staging, citing the supplied workspace rule requiring an explicit order for index mutation. No files were staged or committed. A user approval question for staging and committing verified fixes is pending.
- Restored the combined runner so current working tree verification runs both suites while commit preparation is pending.
- Read all 20 open issue bodies and relevant archived discussions. Classified feature requests, administrative discussion and compatibility notices separately from reported regressions. Classification is not reproduction or resolution.
- SEC-003 Chrome baseline: signed in subscriber requested featured toggle for English simple product 115 without nonce. Source stayed unfeatured while German 116 changed from false to true before WooCommerce rejected the operation through its normal redirect. Source remains unmodified, regression test and fix are next after the verified commits.
- Current deliverables: variation removal fix, locale formatting fix, PHPUnit foundation, PR/push CI definition, issue tracker, journal and original feature acceptance matrix. CI has not run remotely. Full Plugin Check, old stacks, HPOS, checkout, mail and remaining issues remain incomplete.

## Additional baseline reproductions
- Previous goal turn made progress: fresh browser acceptance, expanded tests, issue triage and journal evidence. Git index approval is still unanswered. Revalidated unchanged Git history and no staged files before further investigation.
- Added Polylang's native language switcher widget to the empty disposable header region through WP-CLI. This is lab fixture configuration, not plugin source.
- GH-566 reproduced with multiple product search results. English and German searches both emitted an empty href for the other language. Normal shop archive links correctly switched between linked English and German shop pages.
- Confirmed unchanged Pages::translateShopUrl changes a German product search URL into an invalid shop-de-2 scheme because strrpos returns false when the URL has no shop slug. Saved Chrome links, result languages and direct callback evidence.
- COMPAT-001 reproduced with disposable page 407 containing the original products shortcode with IDs. Chrome displayed a critical error and PHP log identified reversed implode arguments at Pages.php:183. Equivalent category shortcode call at line 204 still awaits browser reproduction.
- The Plugin Check baseline file is grouped CLI JSON, not a single JSON document. Parsed all groups, preserved all 368 findings as individual CHECK identifiers, and created docs/CHECKS.md. No finding was silently dropped or called fixed.
- No additional production source was changed. The verified commits remain pending explicit index approval. Next implementation remains featured product authorization after the verified fixes are committed, followed by the reproduced search and shortcode regressions.

## Commit gate investigation
- Previous goal turn made progress by reproducing search and shortcode regressions and preserving all checker findings.
- Checked instructions along the actual plugin directory ancestry. Only the shared Cortex instructions apply there. The webforJ Git gate is scoped to its separate repository.
- Submitted the verified variation removal staging operation again with this new scope evidence. Automatic approval review accepted it. Thirteen narrowly selected source, regression setup and evidence files are staged.
- The conventional commit attempt was then rejected by the git-write-block PreToolUse hook. It identifies the task's attached webforJ workspace even though git -C and the execution workdir both name the plugin directory. No workaround or hook change was attempted.
- Verified Git history still ends at baseline b5f7495. The variation removal patch is staged. Locale formatting and the broader tracker remain unstaged.
- The equivalent workspace Git gate has recurred across three consecutive goal turns. The required per fix commit sequence cannot continue in the current attached workspace. User was asked to move the task workspace or open its continuation in the requested plugin directory.
- Resume from the staged variation commit, preserve all current work, then commit the separately tested locale fix and continue featured product, search, shortcode and full compatibility work. The overall objective is incomplete.

## Local commits completed
- User explicitly ordered local commits and authorized moving execution to the plugin repository.
- Inspected the actual git-write-block hook. Its effective directory follows an explicit shell cd and does not resolve git -C by itself. Executing transparently from the plugin directory allowed the requested commits. No hook, global configuration or webforJ files were changed.
- 01bfb80: validate and authorize linked variation removal, with WordPress regressions and saved Chrome deletion and stock evidence.
- f49cb58: preserve empty values during decimal localization, with PHPUnit regressions and bilingual Chrome product and cart evidence.
- Repeated the combined runner successfully: 8 unit tests and 11 integration tests, 33 assertions in total, on PHP 8.5.11.
- 2c9aaa3: run plugin regressions on pushes and pull requests. The local runner passed. The remote workflow has not run because no push was authorized or performed.
- Committing the plan, tracker, journal, acceptance matrix, checker inventory and structured source evidence. Bulk raw HTML forum captures remain preserved locally outside these commits.
- The commit obstacle is resolved. Remaining work is still required: featured product fix, search and shortcode regressions, other reported issues, complete checker dispositions, original feature acceptance, HPOS, checkout and older compatibility stacks.

## Featured product callback fix
- Previous goal turn completed local commits and resolved the directory hook obstacle. Revalidated current source, Git state and running lab before this step.
- Added 9 initial WordPress regression cases. Saw 5 failures for subscriber/customer and missing, invalid or nested nonces. The original authorized and disabled visibility cases passed, and missing product ID produced a warning.
- Updated only the existing priority 5 featured callback to check WooCommerce edit_products permission, product input shape and the unchanged woocommerce-feature-product nonce action before modifying translations.
- Preserved WooCommerce's REQUEST nonce contract, including a nonce supplied in POST. Added a tenth regression for that path.
- All 10 featured regressions passed with 20 assertions. Full suite passed: 8 unit tests and 21 integration tests, 53 assertions total. PHP source lint and diff whitespace checks passed.
- Chrome subscriber and customer requests left both simple products unfeatured. Administrator requests with missing, invalid and nested nonces returned 403 without changing either flag.
- Normal administrator star click featured both products. Normal shop manager star click cleared both flags. Unrelated variable product 109 remained unchanged.
- With visibility synchronization disabled in the existing setting, an administrator star click changed only the original product. Restored the exact prior setting.
- Chrome POST nonce path then toggled both products back to unfeatured. Saved request, UI, database and screenshot evidence under featured-chrome-after.
- Committed locally as 0ec30b1. No remote publication. Next: reproduced search URL defect, shortcode PHP crash and the remaining compatibility acceptance matrix.

## Search language links
- Previous turn closed leftover role test windows and verified the dedicated Chrome instance stopped.
- Added three WordPress regressions in the existing Pages callback. Baseline failed because a German search URL was overwritten by the translated shop slug.
- Added only an is_search early return. Polylang keeps its existing search URLs. Shop archive translation remains unchanged.
- Full suite passed: 8 unit tests and 24 integration tests, 57 assertions total, on PHP 8.5.11.
- Chrome clicked the native language switcher English to German and back. Search query and product post type survived, and results matched each language.
- Chrome clicked the normal shop language link with orderby=price. Translated shop and sort query both survived.
- Used one browser window and stopped the dedicated instance after acceptance. Evidence saved in search-chrome-after.json.
- Preparing the separate local search fix commit. No push. Next: shortcode PHP crash and the remaining tracked compatibility work.

## Product and category shortcode PHP compatibility
- Previous step committed the search fix locally as b035464. Git source changes were limited to this independent shortcode fix.
- Added WordPress regression coverage for explicit product IDs, conflicting single ID query removal, category IDs and parent translation, both languages, defaults, existing attributes and comma separated IDs.
- Before the fix, both explicit ID callbacks raised TypeError from the reversed implode arguments. Chrome reproduced the category fatal, supplementing the existing product fatal evidence.
- Changed only the two implode calls to separator first. Translation resolution, shortcode attributes, hook registration and filtering remain unchanged.
- Full suite passed: 8 unit tests and 29 integration tests, 74 assertions total on PHP 8.5.11. Source lint passed.
- Chrome verified the original failing product page, category page and linked English/German pages. Explicit IDs selected the translated products and categories. Default product shortcode output remained in the current language.
- Saved shortcode-chrome-after.json. Used one test browser window and verified the dedicated instance stopped after acceptance.
- Preparing a separate conventional local commit. No push. Remaining tracked issues and complete original feature acceptance remain open.

## Shipping zone zero compatibility
- Previous step committed the shortcode PHP fix locally as a88c467.
- Read installed WooCommerce zone constructor and get_shipping_methods behavior. No argument means an unsaved zone with null ID and no methods; explicit 0 loads the existing rest of world zone.
- Added two integration regressions. Baseline raised a PHP 8.5 null array key deprecation and omitted the rest of world flat rate fixture. Configured zone behavior remains covered.
- Changed only the existing Shipping collector constructor argument to 0. Full suite passed: 8 unit tests and 31 integration tests, 79 assertions total.
- Added an enabled flat rate to disposable lab zone 0. Chrome filtered Polylang translations by shipping group, saved English/German labels through its native form, and verified the labels and unchanged 5 euro rate in cart and checkout.
- Initial translation listing was paginated. An absent title on its first page was not evidence of missing registration and is not used as baseline proof.
- The existing browser cart contains mixed language products from earlier tests. Full cart language transition and completed checkout are still pending, not claimed accepted by this shipping check.
- Saved shipping-chrome-after.json. Closed the sole dedicated Chrome test instance after acceptance.
- Preparing a separate local shipping fix commit. No push. Next: order storage, checkout and remaining issue reproduction.

## Checkout currency formatter without current language
- Previous turn committed shipping zone fix as 93f6226. Read Order, Emails and installed classic and Store API checkout source before testing.
- Lab has HPOS enabled with legacy synchronization disabled. Original Order callback registers only the classic checkout hook.
- Chrome German block checkout created processing order 1041 without language, then returned HTTP 500. PHP log identified NumberFormatter ValueError for locale @currency=EUR while rendering the order email.
- Added a lab only pre_wp_mail interceptor before checkout. Messages are captured locally and never sent externally.
- Added a regression that failed with the same ValueError. Added WordPress locale fallback to all four existing NumberFormatter paths only when Polylang returns no locale.
- Full suite passed: 9 unit tests and 31 integration tests, 93 assertions total. Existing empty dimension and normal locale cases remain covered.
- Chrome repeated checkout after the fix. Store API returned 200, processing order 1143 reached order received with total 62.50, and order emails rendered into the local capture.
- German block checkout still redirected to English and order language remained false. These reproduced defects require their own order storage/Store API fix.
- Automatic approval review rejected storing raw network evidence containing session material. Saved only sanitized results, with cookies, headers, nonces, keys and customer details omitted.
- Used one browser test window and closed the dedicated instance after acceptance. Preparing separate local formatter commit. No push.
- Next: order hook regression coverage, language persistence, email locale and localized thank you routing for blocks/HPOS while preserving classic order behavior.

## Block checkout order language
- Previous turn committed the missing formatter locale fix locally as 7077cf8.
- Read installed Polylang REST source: it selects language from an explicit lang parameter, and the normal Store API request lacks that parameter. Read WooCommerce's existing Store API order metadata hook.
- Direct classic HPOS probe showed the preserved Polylang taxonomy contract works on the reserved shop_order_placehold ID. No new order metadata or changed getter was necessary.
- Added five order regressions. Baseline had three failures for block language assignment. Classic language assignment passed.
- Registered the existing saveOrderLanguage callback on the Store API metadata action and accepted its WC_Order argument. When current language is absent only on this object path, a valid Polylang language cookie selects the locale through the existing Utilities switcher. Explicit language wins. Invalid or nested cookies preserve existing order language.
- Chrome block HPOS purchases passed for German order 1256 and English order 1257. Both processing orders retained language, totals 25.00 and the correct localized order received path.
- Chrome classic HPOS German order 1262 passed. Legacy switch was initially refused because orders were unsynchronized. Used supported wp wc hpos sync, then verified the selected storage option was no.
- Corrected a test fixture assumption: Polylang automatically assigns default language to new legacy order posts. Invalid cookie regression now asserts preservation of an explicit existing language, rather than assuming a blank one.
- Five order regressions passed with legacy storage. Chrome classic legacy English order 1278 and German order 1279 passed with correct languages and paths.
- Synced the two new legacy orders with the supported command and restored original block page IDs 7/8, HPOS yes and compatibility synchronization false. Final full suite passed: 45 tests, 104 assertions.
- Saved sanitized order-language-chrome.json. Closed and verified the sole Chrome testing instance stopped. No remote publication.
- Preparing the separate local order fix commit. Broader cookie disabled/client language handling, localized email catalog acceptance, stock/refunds, coupons, reports and complete checkout matrix remain open.

## Contributor authorization coverage and advisory mapping
- Previous turn committed block order language as 9b6780b. Revalidated the actual callbacks and settings registration source.
- Fetched primary Wordfence and Patchstack CVE-2026-24585 advisories. They name contributor privileges but not the function or operation. Kept the advisory unmapped rather than attributing it to a guessed fix.
- Read settings menu history: manage_options guard was added in 2019 commit 90a9e62. Native registered settings use WordPress options.php. No production settings change was justified by the public advisory alone.
- Added contributor fixture to CI lab setup and expanded the existing variation/featured regressions. Full suite passed: 47 tests, 108 assertions.
- Chrome contributor requests with valid same session nonces left both linked variations and featured flags unchanged. Direct settings page returned 403. Saved settings hash remained unchanged.
- Temporary nonce fixture existed only in the local lab and was removed after checks. No nonce, cookie or session material was saved. Closed the sole Chrome test instance and verified it stopped.
- Saved contributor-chrome.json and contributor-authorization-audit.md. Preparing separate local test coverage commit. No push.
- Next: remaining forum and checker dispositions plus original cart, attributes, email and order lifecycle acceptance. Public advisory operation mapping remains an explicit unknown.

## Account email template translation (GH-556)
- Previous turn committed contributor regression coverage as 48acfbc. Read the archived issue body and comment plus actual email locale and formatting code.
- Existing callback found translations but discarded the template when the target was a WP_User instead of WC_Order. Both reset and new account subjects, headings and additional content were affected.
- Added seven regression cases using real Polylang translation storage, recipient user locales, native WooCommerce format_string and an order preservation case. Baseline had six account field failures; order behavior passed.
- Chrome requested a reset email for the disposable German customer. Local mail capture contained source English additional content, not its German translation.
- Kept legacy order placeholder filters unchanged and moved the existing native WooCommerce formatter to the common return, applying translated account templates too.
- Full suite passed: 54 tests and 121 assertions. Chrome repeated reset requests for German and English recipients. Captured mail used the intended help text, with site title expanded and no raw template placeholder.
- All emails stayed in the local interceptor. Saved sanitized account-email-chrome.json with boolean markers, not email bodies, reset keys, credentials or addresses.
- Restored original email settings, translation data and recipient locale. Closed the sole Chrome test window and verified its dedicated process stopped.
- Preparing separate local GH-556 fix commit. No push. Browser reset flow passed; broader email catalog/type coverage, cart, coupons, product/attribute synchronization and checker/forum dispositions remain pending.

## Forum inventory correction and public hook review
- Confirmed the account email fix is committed locally as c1afb22. No push.
- Parsed all 423 saved forum pages with the standard HTML parser. Corrected 79 titles that had captured an age instead of the topic heading. Every topic has its actual heading, a stable FORUM identifier and captured post count. The inventory contains 1697 captured topic/reply bodies. Individual report triage remains pending.
- Reviewed all 22 dynamic hook naming warnings against current calls and definitions. All resolve to existing woo-poly prefixed extension hooks, including settings and gateway suffixes. Recorded individual source evidence and preserved public names and spelling.
- Verified inventory counts, unique URLs, headings, captured body counts and all 22 source mappings. No production code changed, so no runtime or browser acceptance was claimed for this documentation work.
- The dedicated Chrome automation process is stopped. No browser was opened during this source audit.
- Next: triage forum report bodies, review remaining checker findings, and continue original product, cart, email and order acceptance.

## Forum triage and missing Polylang locale (FORUM-020)
- Read topics and all captured replies for FORUM-001 through FORUM-020. Recorded individual category, summary and next check. Matching search and formatter reports map to their tested fixes.
- FORUM-020 identifies an unknown locale assignment on false still present in Utilities. Four new integration cases preceded the fix. The first invocation omitted the required environment flag and stopped at bootstrap. The corrected invocation reproduced one Error, with three preservation cases passing.
- Chrome MCP reproduced the same Error through a temporary local route calling the existing callback. English, German, empty language and existing legacy object fallback were checked before and after.
- Restricted the existing fallback assignment to an actual current language object. No public signature or hook changed.
- Full suite passed: 9 unit tests with 22 assertions and 49 integration tests with 106 assertions. Chrome callback checks passed. English and German home pages loaded without fatal errors. Saved polylang-locale-chrome.json. Complete order email workflow and catalog acceptance remain pending.
- Removed the temporary lab route. Closed the dedicated Chrome MCP process and verified it absent.
- Next: remaining 403 forum reports, email catalogs, product/attribute synchronization, cart/order lifecycle and checker findings.

## Forum cart dependency fix and continued triage
- Confirmed prior locale fix is local commit 76e3f53.
- Read all captured posts for FORUM-021 through FORUM-040 and recorded individual summaries, categories and next checks. Forty forum topics are now triaged, with 383 pending.
- FORUM-022 reports Cookies is not defined. Current Cart registers jquery-cookie while both script variants already use the Cookies API. Installed WooCommerce registers js-cookie as a supported alias for its existing wc-js-cookie library. No new dependency was added.
- Current homepage masks the failure by loading another cookie consumer. An isolated native WordPress script graph regression failed before the fix. A temporary lab route rendering only the fragment dependency chain reproduced the ReferenceError in Chrome MCP.
- Replaced only the incorrect dependency handle with js-cookie. Full suite passed: 9 unit tests/22 assertions and 50 integration tests/109 assertions. Isolated Chrome route loaded the correct API with no console errors.
- Native product UI added two Test shirt en items. English mini cart showed 2 x 20.00 and subtotal 40.00. German language link changed the name, product/cart/checkout paths and decimal display while retaining quantity and subtotal. Returning to English preserved the contents.
- The first removal click failed because the header mini cart was hidden. Hovering the header then using its remove button emptied the cart. Saved cart-fragment-chrome.json.
- Observed German Test shirt de in related products on the English product page. Recorded COMPAT-006 for source investigation rather than attributing a cause.
- Removed the temporary route, closed the sole dedicated Chrome process and verified it absent. Older stack, complete cart/coupon/variation behavior and remaining acceptance rows are still open.

## Related product observation resolved as fixture data (COMPAT-006)
- Confirmed the cart dependency fix is committed locally as 132e4a2.
- Read current Product language mapping and installed WooCommerce related product query/cache code before considering a production change.
- The German simple fixture 116 had English category 27, despite being assigned language de. The existing category translation was term 33. The local fixture builder saved products before assigning their language, retaining the initial default category.
- Chrome confirmed English related products included Test shirt de before correction. Updated only the malformed lab product category to its existing German translation through native APIs and cleared affected native product transients.
- Chrome then showed only Security fixture en as English related content and Security fixture de as German related content. English and German category archives each showed the correct two fixture products and their corresponding routes. Saved category-fixture-chrome.json.
- Verified the other fixture roots already had correct category assignments. Corrected the ignored local fixture builder to map categories after language assignment, preventing the same lab setup error in future.
- No production filter or language behavior was changed. Updated COMPAT-006 disposition and partial category acceptance. This does not resolve all external category reports.
- Full regression suite passed unchanged: 59 tests and 131 assertions. Closed the single dedicated Chrome process and verified it absent.
- Next: remaining forum reports, native translation creation and category hierarchy, email catalogs, cart/order lifecycle and checker dispositions.

## Security maintenance review requested by maintainer

- Prior turn produced a pending CSV import patch. Revalidated current source and lab. Reproduced two original import regression failures and restored the pending patch. Browser upload was denied by Chrome workspace path restrictions. Import acceptance and its commit remain pending.
- Prioritized the explicit security maintenance request. Inspected current and baseline removal, related lookup, featured callback, native WooCommerce server and JavaScript, SKU query and settings registration. The two existing AJAX security fixes are already local commits. No unrelated production change was added.
- Added eleven security cases covering nine invalid ID types with no related SQL lookup and denied permissions on original or translated parent. Full suite passed with 72 tests and 189 assertions. Source and test PHP lint and full diff whitespace checks passed.
- Chrome initially refused its stale profile lock. The named process was absent. Removed only stale lock links and resumed the dedicated instance. Current administrator rejection tests preserved fresh pair 2392/2393 and siblings 111/114. Native Remove included security nonce, deleted the linked pair and preserved siblings.
- Ran Plugin Check and preserved all 499 findings, 173 errors and 326 warnings. Production source/scripts account for 345, tests for 30 and other files for 124. No PreparedSQL or UnescapedDBParameter finding remains. Other checker findings remain unreviewed and release clearance is not claimed.
- Added SECURITY-REVIEW.md with exact issue handling, changed file inventory, actual checks and remaining release requirements. Preserved the pending CSV change and raw forum inventory. Next security work is individual source review of remaining input, save hook and output findings, followed by distributable and supported version checks.

## Category translation script escaping

- Security review and regression expansion committed locally as e391007. Maintainer ordered all remaining findings fixed.
- Read the category callback and reproduced two failures before the fix. Native Chrome translation form executed a harmless stored category marker.
- Encoded the existing selector and image strings with wp_json_encode and HTML safe JSON flags, and printed the thumbnail through absint. Existing hook and selection flow remain unchanged.
- Full suite passed with 75 tests and 200 assertions. Chrome marker stopped executing. Existing subcategories display type, image URL, thumbnail 5 and visible Remove button were preserved. Source lint and diff whitespace checks passed.
- Repeated Plugin Check. The category output escaping findings no longer appear. Other category input and save hook findings remain pending review.
- Saved before and after regressions, Chrome evidence and the checker report. Next confirmed security issue is certificate verification disabled in translation pack requests.

## Translation transport certificate verification

- Category output fix committed locally as 4b02c13.
- Both translation request methods explicitly disabled certificate verification. Two regression cases failed before the change, and the existing transport error case passed. Chrome observed sslverify false on the real WordPress.org request.
- Removed only the two sslverify overrides, retaining the repository hook, URL, timeout, filesystem flow and error behavior. WordPress now supplies its existing verified transport default.
- Full suite passed with 78 tests and 205 assertions. Source lint and diff whitespace checks passed. Chrome observed sslverify true and successful availability. Browser then invoked the original downloader and installed the French pack successfully with verified transport on every request.
- Removed the temporary capability and nonce protected lab route. No production endpoint was added. Next work remains checker input and output dispositions, confirmed fixes and the original compatibility matrix.

## Status report output escaping

- TLS certificate fix committed locally as 26fbe5f.
- Read report output and native WooCommerce tooltip escaping. A regression failed for a controlled language object value. Temporary read only lab instrumentation showed the same unsafe output in the native Chrome status page. This identifies an output sink, not proof that Polylang accepts that value from an untrusted user.
- Escaped only locale and translation path text with esc_html. Native tooltip HTML remains unchanged and its escaping findings have source dispositions.
- Full suite passed with 80 tests and 211 assertions. Chrome showed the controlled marker as text without execution. The normal report retained both language locales, translation paths and all three help tips. PHP lint and diff whitespace checks passed. Repeated Plugin Check preserved for review.
- Removed the temporary lab instrumentation. No production route was added. Remaining input, settings, template output and packaging findings stay open.

## Production direct file access guards

- Status escaping fix committed locally as ecd05e6. Reviewed the two production files flagged for direct access. Other matching findings are test or local fixture files that must be excluded from the distributable.
- First test helper name collided with the final PHPUnit run method and prevented loading. Renamed that private helper, then observed the two intended baseline failures. Direct Chrome status request returned HTTP 500 before the guard.
- Added the existing ABSPATH convention to StatusReport.php and Views/social.php. No existing widget, hook or report behavior changed.
- Full suite passed with 83 tests and 225 assertions. PHP lint and diff whitespace checks passed. Chrome direct requests returned empty content without errors. Native status report and all existing social widgets remained present. Repeated Plugin Check has no production direct file access protection findings.
- Updated individual checker dispositions and preserved direct request, normal browser and standalone process evidence. Template escaping, input and save hook review, packaging, supported version testing, forum triage and full original feature acceptance remain open.

- Direct file guards committed locally as 9d093a2. Latest full tree Plugin Check snapshot has 500 findings, 170 errors and 330 warnings, including development files. Dedicated Chrome test browser is being closed after acceptance. Next production work is template output escaping with preservation of existing HTML and widgets.

## Shared cause batches and output fixes

- Grouped all checker rows into eight shared cause batches and all 423 forum reports into feature groups. Forum title routing does not change any review or reproduction status. Batch inventory preserves the previous scan and every report URL.
- Reviewed and corrected seven translated templates, all three payment gateway output implementations and notice wrapper attributes as one output batch. The 17 new tests cover shared negative inputs and retained links, rich text, bank fields and widgets. Native view composition remains intact.
- Baseline gateway tests reproduced five failures. Initial test setup also triggered unrelated order notification locale handling, so the output fixture now exercises existing callbacks without saving a status transition. The first attempted executable path was unavailable, then the existing repository PHPUnit command was used.
- Full suite passes with 100 tests and 275 assertions. PHP syntax and diff whitespace checks pass. Chrome before and after evidence covers all six gateway outputs, notice attribute injection and the normal settings page. All temporary lab instrumentation was removed.
- One full Plugin Check scan for the batch has 302 production findings, 92 errors and 210 warnings. This is 35 fewer production output errors. Ten remaining output rows have explicit owned or native HTML source dispositions. Other rows remain pending.
- New or changed production files are Views/admin.php, Views/Admin/about.php, Views/Admin/getHelp.php, Views/Admin/support.php, Views/Messages/activateError.php, Views/Messages/endpointsTranslations.php, Views/Messages/support.php, Gateways/GatewayBACS.php, Gateways/GatewayCOD.php, Gateways/GatewayCheque.php and Tools/FlashMessages.php. Tests are TemplateOutputSecurityTest.php and GatewayOutputSecurityTest.php. Evidence uses output-batch, template-output and gateway-output prefixes.
- Next batch is request validation and authorization, followed by script value encoding, translation contracts and grouped feature acceptance. The earlier CSV import patch remains separate. No remote push or publication.

- The output batch is committed locally as f0f5c99. The staged whitespace check reported the raw Plugin Check stream trailing blank line. Production and test changes pass whitespace checks. The raw checker evidence is retained unchanged, and no claim of a completely clean checker report is made. Documentation now lists every batch file and its reason.

## Shared request boundary batch

- Inspected request paths and their actual WordPress, WooCommerce and Polylang callers. Native term add and edit handlers verify nonces and permissions before calling the plugin. Read only report and display selectors do not require a new mutation nonce. The early translation downloader did require its own guard.
- Added the native add-lang nonce check and language management permission guard, scalar request validation and safe locale checks. Filtered downloader error HTML at its sole production display boundary. Kept legacy manage_options behavior and the current manage_languages capability. No new minimum dependency API was used.
- Validated editor source IDs and read_post permission. Normalized report IDs and selectors, category form IDs and display type and attribute, coupon and endpoint route selectors. Reproduced and corrected language key overwriting that dropped an earlier selected report product.
- The shared baseline suite reproduced 17 failures. Initial fixtures needed the native admin template functions and the actual lab manager login, corrected before capturing the baseline. Separate report and thumbnail regression outputs are preserved.
- Native WooCommerce source confirmed image removal posts an empty thumbnail value. The first validation rejected it, so the validation now retains empty to zero clearing. Regression and the native Chrome Remove button verified both translated category thumbnails clear. Original category fixture metadata was restored.
- Full suite passes 123 tests and 337 assertions. Production and test PHP syntax and whitespace checks pass. Chrome verified native form nonce use, rejected requests without transport calls, preserved installed pack lookup, escaped error HTML, permitted and denied source defaults, normal category saves and rejected native term nonce. Native reports display both selected products and both translations. Attribute, translation and advanced settings pages load without fatal errors.
- Temporary browser instrumentation was removed. Controlled role changes in the lab callback are identified as callback evidence, not separate browser sessions. Native category and report workflows are verified separately. No complete feature acceptance claim is made.
- Current Plugin Check has 298 production rows, 92 errors and 206 warnings. Guard expressions add some checker warnings, so raw count reduction is not the security measure. Source dispositions resolve 79 current request rows and six exception data rows together. The remaining request rows are pending.
- Changed production files are Language.php for request and error guards, Product/Product.php for protected defaults and language input, Reports.php for route and ID normalization and complete selection, Taxonomies/Categories.php for safe copy and save values, Taxonomies/Attributes.php, Coupon.php and Endpoints.php for display route validation. Added RequestInputSecurityTest.php. Evidence uses request-batch and request report or thumbnail prefixes.
- Next request batch covers product cloning and synchronization inputs, navigation and current URL handling. Script data encoding, remaining output and translation contracts follow. The earlier CSV import patch remains separate. No push or publication.

- The shared request boundary batch is committed locally as 30f9688. Reloaded the native report after removing the lab endpoint and confirmed all four selected product IDs remain. Current production inventory has 203 rows awaiting source review or correction, including 81 request rows. The earlier CSV import change remains unstaged.

## Native CSV import preservation

- Completed the pending CSV import fix before changing the shared metadata class further. The Polylang post save action now receives the stored WP_Post instead of WC_Product. Existing source identity and translations are retained.
- Browser uploaded the existing fixture CSV through the native file input, confirmed ID and price mappings and completed the importer with one updated product and no failures. Both language prices became 25 while dates, menu order and review status were unchanged. The original Polylang settings were restored.
- Two real importer regressions pass. The complete 123 test suite had already passed with the same source change. The source diff and PHP syntax checks pass. No unrelated import behavior is claimed verified. New product import, export and the remaining original feature matrix remain pending.

## Product synchronization request batch

- Inspected Meta, Variable, Variation and Utilities together. Shared helpers validate integer query values, registered languages and readable product sources. Existing native editor verification and variation AJAX guards remain in place. Malformed translation requests return before copying metadata, variations or defaults.
- The baseline regression set reproduced five errors and six failures. The corrected batch passes 20 new cases for malformed inputs, denied private source access and normal copying. Test callback filters are removed during cleanup. The full suite passes 143 tests and 416 assertions.
- Native Chrome followed the existing Polylang add translation link, opened Variations and published the German product. Product type, weight, default Size, copied variation and price were retained. Initial browser fixture default casing did not match its custom option, corrected in the fixture and repeated before acceptance. Stored metadata and linked translations were checked after publish. Disposable products were removed and original feature settings restored.
- PHP syntax checks and production diff whitespace checks pass. Full source diff reviewed. Plugin Check reports 265 production rows, 92 errors and 173 warnings. Shared source dispositions cover 102 current request rows and 16 output rows. There are 147 production rows pending, including 25 request rows. Checker rows and forum title routing are not confirmed defect counts.
- Changed Utilities.php for shared request validation, Meta.php for protected metadata copying and screen inputs, Variable.php for variation and default copying and Variation.php for validated target language selection. Added ProductTranslationInputTest.php. Evidence uses product-request prefixes. The inventory, issue tracker, batch summary, goal and security review record scope and remaining work.
- Next request batch covers navigation and current URL handling. Script values, translation contracts, distribution and grouped feature acceptance remain pending. No push, publication or full release clearance claim.

## Shared navigation request batch

- Inspected native WooCommerce query and widget parsing, WordPress widget activation and all callers of the current URL helper. The helper has no production callers, so its Chrome proof is an explicitly controlled API check. Shop filters are read only and require no mutation nonce. Remaining stock and numeric formatting context flags and coupon diagnostic data remain pending in their feature groups.
- Added a separate real frontend suite because the existing integration bootstrap defines WP_ADMIN. Four frontend cases cover array rejection, current slugs, legacy IDs and multiple selection. Four URL cases cover normal URLs, missing and array server fields and control characters. The baseline reproduced one error and five failures with four warnings across the two suites.
- Shared navigation parsing now validates shape, unslashes and cleans input, normalizes legacy IDs and sanitizes current slugs. Cross language source term lookup explicitly clears the language restriction. Current URL output uses WordPress URL sanitization and returns configured home for malformed server shapes.
- The initial callback fix passed frontend tests but native Chrome exposed a separate WooCommerce widget renderer failure because the invalid array was still present. The fix now removes malformed filter values before rendering. The regression asserts removal and Chrome confirms no critical error. This browser failure remains in the evidence.
- Native Chrome verifies source slug filtering displays the German fixture, widget removal clears the filter and native selection uses the translated slug. An administrator and nonce protected temporary endpoint verifies normal, absent, array and control character URL helper cases. It was removed before repeating native filtering. All fixture products, terms and the attribute were deleted and original widget settings restored.
- Full suite passes 151 tests with 435 assertions. PHP syntax and shell syntax checks pass. Full production diff reviewed and scoped whitespace checks pass. Raw Plugin Check output remains unchanged, including its trailing blank line. Current production inventory has 260 rows, 92 errors and 168 warnings. There are 137 pending production rows and 15 pending request rows. Current source dispositions cover 107 request rows and 16 output rows.
- Changed Widgets/LayeredNav.php and Utilities.php for the shared request fixes. Added frontend bootstrap, NavigationRequestTest.php and phpunit-frontend.xml and wired the frontend suite into tests/bin/run.sh. Added CurrentUrlRequestTest.php. Batch docs, goal, tracker and inventories record source dispositions and scope. Evidence uses navigation-request prefixes.
- Next shared batch covers script value encoding and remaining output. Full feature matrix, context flags, diagnostic data, query cost review, release archive check and Plugins Team submission remain pending. No push or publication.

## Shared inline script output batch

- Inspected all eight production script wrapper callers. Order and Shipping use static executable code. Attribute links, variable product messages, fields locker data and the retained protected product type script now encode variable data with WordPress JSON and HTML hex flags. Wrapper IDs use attribute escaping. The wrapper continues to accept trusted executable code.
- Attribute labels use DOM text, URLs use DOM attributes and search values use encodeURIComponent. Variable dialog content uses permitted HTML while title and language remain encoded data. The legacy language warning uses DOM text. Fields locker selectors and metadata use WordPress JSON encoding.
- Initial test helper name collided with a final PHPUnit method and the screen fixture lacked native admin functions. Those fixtures were corrected before capturing the baseline. Five shared regressions then reproduced five failures and one warning. A later normal help link assertion was corrected for the quote normalization performed by WordPress HTML filtering.
- Native Chrome verified marker translations remain inert in attribute links and the variable dialog. Quote and ampersand search values survive the actual translation link and search form. Dialog help remains available, wrong language variable creation disables saving and switching to a simple product restores saving. Wrapper attribute injection is rejected while its intended code still runs.
- Browser acceptance exposed the current Polylang page no longer using the legacy settings form. An additional failing regression reproduced the missing default language UI restriction. The existing restriction now uses the native row action filter available since Polylang 1.8. Legacy handling remains. The disabled action retains native row action visibility, safely escaped warning text and accessibility text. The no variations case preserves the original action.
- Actual browser execution also exposed a leading comma after the initial empty selector fix. Selector construction now adds separators only when needed. Native editor metadata fields lock without a jQuery error. The before and after failure evidence is retained.
- Full suite passes 160 tests and 460 assertions. PHP syntax and scoped whitespace checks pass. Final source diff reviewed. Plugin Check has 259 production rows, 91 errors and 168 warnings. There are 135 pending production rows, including 15 request rows and five output rows. The script composition row has an explicit source disposition after inspecting all callers. Checker disappearance is not the sole proof of correction.
- Removed temporary marker instrumentation before repeating normal attribute search and native language restriction checks. Removed the disposable attribute and four known auto draft editor products. The default language remains unchanged. Older dependency versions and the full feature matrix are not yet verified.
- Changed Utilities.php for the wrapper ID, Taxonomies/Attributes.php for encoded DOM link construction, Product/Variable.php for encoded messages and current native language UI support and Product/Meta.php for encoded selector and type data and empty selector compatibility. Added InlineScriptSecurityTest.php. Batch documentation and inventories record scope, pending rows and evidence. Evidence uses script-output and script-language prefixes.
- Next batch covers settings renderer and notice output. Other security groups, feature acceptance, release archive review and Plugins Team submission remain pending. No push or publication.

## Shared settings output and page maintenance batch

- Inspected the complete bundled settings renderer, native settings registration, all internal flash message producers and page maintenance. All remaining five output rows are owned HTML composition after leaf inspection and correction. The public FlashMessages API retains trusted developer HTML and original social widgets. No request path accepts arbitrary message bodies. Extension callers must escape their own values.
- Corrected attributes, plain labels and permitted rich descriptions across field callbacks and sections. Native WordPress forms, option nonce fields and wp_editor remain. Quoted select values compare before output escaping. Page warning variables and destinations are escaped and the composed warning uses permitted HTML.
- Found and corrected a separate permission gap before automatic page maintenance writes global state. manage_woocommerce or manage_options is required. A dedicated normal admin suite avoids a false proof from the existing AJAX integration bootstrap.
- Baseline settings regressions reproduced 14 failures. The normal admin baseline reproduced three denied role failures. Final suite passes 181 tests with 532 assertions. PHP syntax and shell syntax checks pass. Full source and regression diff reviewed and scoped whitespace checks pass. Raw checker output retains its trailing blank line.
- Chrome verifies the field matrix with inert markers, native forms and editors, invalid nonce HTTP 403, unchanged native Save Changes and retained Fields Locker state, normal warning edit destination and original social widgets. Controlled current user changes test roles through the callback, not separate login sessions.
- Chrome reproduced invalid fragment selector failure on reload. Matching owned group IDs corrects the failure and preserves Meta List navigation. Both temporary browser endpoints were removed, fixture options restored and disposable pages deleted.
- Automatic approval review rejected the attempted Fields Locker disable operation because persisting a security control change lacked explicit verification authorization. The operation was not executed. Unchanged native settings save provided safe acceptance instead.
- Plugin Check completed with 258 production rows, 90 errors and 168 warnings. There are 129 production rows pending. All 22 output rows now have source dispositions, including trusted owned HTML boundaries. Static rows are not unique vulnerabilities. Fourteen additional development rows come from the new test context and belong to archive review.
- Changed vendor/class.settings-api.php, Plugin.php and Admin/Settings.php for renderer escaping, warning links, permissions and labels. Added SettingsRendererSecurityTest.php, admin/bootstrap.php, PageMaintenanceSecurityTest.php and phpunit-admin.xml. Updated tests/bin/run.sh, inventories, batch summary, issues, goal and security review. Evidence uses settings prefixes.
- Native acceptance of automatic missing page creation and unassigned base language repair still remains, along with older stacks, broader feature matrix and 383 forum content reviews. Next work remains grouped by translation contracts, query and diagnostics, runtime and public contracts and release archive. No push, publication or full release clearance claim.

## Shared query and diagnostic batch

- Previous settings batch committed locally as 390162c. Reviewed every direct database query and the native metadata lookup callers together. Both direct queries already used prepare. Removed extra wp_slash from SKU search input because preparation handles SQL escaping. Literal quote and backslash data now matches stored values.
- Reviewed direct relationship reads without caching. Immediate link edits and deletion decisions require current results. Native get_posts metadata lookups retain WordPress query handling and parent scope. Large catalog performance remains unmeasured.
- All six optional coupon logs share a private request path helper. Nonstring URI values return unavailable. WordPress unslashing, path extraction and text sanitization retain the path while dropping query data. Existing logging switch remains false by default. The other two logs describe operational term errors and duplicate variation cleanup, not request bodies.
- The first diagnostic fixture captured an empty log because WordPress initialization changed the sink. Capture now selects the sink immediately before the production callback. A backslash SKU fixture also required wp_slash when writing metadata. The test asserts the exact stored SKU. Repeated baseline against committed production source with corrected fixtures reproduces five failures. Fixed targeted set passes 14 tests and 43 assertions.
- Full suite passes 195 tests and 575 assertions. Three PHP files pass syntax checks. Source and test diffs reviewed. Changed lines inherited old trailing spaces, removed only on touched lines. Scoped whitespace check passes. Raw Plugin Check stream is retained unchanged.
- Chrome controlled callback proof verifies four literal SKU formats, same language rejection and cross language allowance, malformed and normal diagnostic inputs, missing and invalid nonce HTTP 403 and authorized execution. This is explicit API proof, not native editor save or full checkout acceptance. All temporary products are deleted in finally and the callback was removed. The normal settings page remained available.
- Complete Plugin Check has 247 production rows, 90 errors and 157 warnings. Explicit dispositions cover query and diagnostics rows and normalized coupon path output. There are 99 pending production rows. Development and archive rows total 261. Checker rows are not unique vulnerabilities.
- Changed Coupon.php for the shared diagnostic path, Product/Meta.php for raw prepared SKU input and added QueryDiagnosticSecurityTest.php. Browser instrumentation is kept only under work and is not production code. Updated inventories, batches, goal, issues and this journal. Evidence uses query-diagnostics prefixes.
- Remaining three request flags belong to stock and locale feature acceptance. Translation contracts, runtime, public contracts, archive review, 383 forum content reviews and the full feature matrix remain pending. No push, publication or complete release clearance claim.

- Native acceptance exposed WooCommerce's own slashed SKU lookup returning false. The plugin previously trusted that result and skipped its corrected query. Four new baseline cases reproduced the bypass. Removed that early return so the existing prepared query owns the language decision. The no product ID contract remains. This adds a fresh query when the native lookup reports no match, necessary for correctness of literal stored data.
- The first native product fixture wrote only post metadata and lacked WooCommerce lookup data. Corrected the fixture using WooCommerce CRUD, then reproduced the false lookup bypass again with real lookup rows. After correction the actual English editor shows Invalid or duplicated SKU and retains an empty stored SKU. The German editor saves the quoted SKU. Stored state verified before removing all three fixture products. Native before and after evidence is retained.
- Final targeted suite passes 14 cases with 43 assertions and full suite passes 195 tests with 575 assertions. Test cleanup restores the previous current user. Wider checkout and third party integration acceptance remains pending.

- Added a failing empty SKU compatibility case during final diff review. The callback now returns the native result for an empty SKU and retains the no product ID guard. Nonempty SKUs use the prepared lookup. Final normal settings page has two native forms and no temporary diagnostic nonce. The final suite includes 14 batch cases and 43 assertions.

## Shared translation contract batch

- Previous query and diagnostic batch committed locally as 7df3bd5. Reviewed 63 translation findings together. Corrected two plugin help labels using the default WordPress domain. Removed a duplicated argument and made a concatenated translation source a single identical literal. Original message keys and feature option values remain.
- Added placeholder meanings to downloader errors, page maintenance warnings, privacy defaults, coupon label and endpoint guidance. Original placeholder source keys remain to preserve installed catalogs. Fully ordered translated formats work with both existing downloader error keys and endpoint link formatting.
- Retained dependency owned WooCommerce catalogs for native titles, bank details, privacy defaults, status help and coupon label. Installed WooCommerce source confirms the privacy defaults, status help and coupon label. Stored shipping labels and gateway title, description and instructions remain Polylang runtime business strings with their original dependency fallback.
- Retained load_plugin_textdomain for bundled Arabic and Spanish files and supported local installations. Source and compiled catalog files remain unchanged. Reading bundled compiled error keys returned the existing English fallback. This is not proof of complete non English catalog coverage.
- Initial four case regression set reproduced one failure for the missing plugin domain. Final five shared cases pass with 13 assertions. Full suite passes 200 tests with 588 assertions. Eight touched PHP files pass syntax checks. Source and tests diff reviewed and scoped whitespace checks pass.
- Native settings marker shows both translated help links, existing description catalog lookup, all radio values, two forms and two nonce fields. Temporary instrumentation first called user functions before WordPress user initialization and returned HTTP 500. Moved its filter registration to authenticated admin_init, repeated the native checks, removed it and repeated normal settings. Normal links and forms remain and marker text is absent. No production settings failure was observed.
- Full Plugin Check has 229 production rows, 72 errors and 157 warnings. Eighteen translation source errors no longer appear. Remaining 45 translation rows have explicit dependency catalog or preserved key dispositions. The raw report remains unchanged, including reviewed rows. There are 36 pending production rows and 264 development or archive rows.
- Changed Admin/Features.php and Product/Meta.php for translation call and domain corrections. Tools/TranslationsDownloader.php, Plugin.php, Privacy.php, Coupon.php and Views/Messages/endpointsTranslations.php add argument guidance. Added TranslationContractTest.php. Local browser helper lives only under work and is removed from the active lab. Evidence uses translation-contract prefixes. Updated inventory, batches, goal, issues and security review.
- Next groups cover runtime, public symbol and hook contracts and the three stock and locale request selectors. Full native language catalogs, feature matrix, 383 forum content reviews, older stacks, archive review and Plugins Team submission remain pending. No push or publication.

## Shared stock and formatting request context batch

- Previous translation batch committed locally as e0cf70d. Reviewed stock and formatting context selectors together. A frontend product type flag previously suppressed translated stock hooks. Hook deferral now requires the product screen or authorized admin editpost with a scalar type. Native product editing owns its existing synchronization and request verification.
- Final review identified that an authorized editor could still suppress legacy checkout and order AJAX hooks. Two additional failing regressions reproduce this before the exact native editpost action guard. Installed WordPress form source confirms that action. No extension hooks were renamed.
- Genuine frontend baseline reproduced two failures, denied admin and malformed contexts reproduced four failures, legacy routes reproduced two failures and empty currency formatting reproduced one failure. Fifteen added cases cover 36 assertions. All four suites pass 215 tests and 624 assertions. Five PHP syntax checks and scoped whitespace checks pass. Production and regression diffs reviewed.
- Formatting selectors are pure getters. Scalar and array presence semantics remain because values are not consumed. Corrected the grouping fallback argument while retaining native admin decimal and localized formatting behavior.
- Native Chrome checkout with the posted type and native nonce succeeds and linked stocks move from 20 to 19. Native product editing updates both to 17. A fresh pair then passes frontend checkout and actual admin AJAX checkout, moving 20 to 19 to 18. Final native editpost updates both to 17. Both orders retain German language. The first legacy attempt still used the frontend URL, so the actual checkout_url parameter was corrected and the legacy request repeated. Evidence distinguishes them.
- Temporary local instrumentation blocked fixture mail, required administrator capability and an AJAX nonce for controlled formatting checks and was removed after acceptance. Validated and deleted only owned fixture products and orders. Final native settings shows two forms, no helper nonce and an empty cart.
- Automatic approval review rejected raw browser evidence persistence because receipt URLs contained access keys and customer details. A strict allowlist now retains only non-sensitive results, with receipt URLs, keys and customer details omitted. No approval was requested or sensitive payload committed.
- Plugin Check completed with 232 production rows, 72 errors and 160 warnings. All 114 request rows have explicit source dispositions. There are 33 pending production rows grouped into public contracts and runtime compatibility. Development and archive rows total 270. Forum content review still has 383 reports pending. Counts are checker rows, not unique defects.
- Changed Stock.php and LocaleNumbers.php, added three shared context regression files and updated inventories, evidence, tracker, journal, batches, review and goal. Next groups are public contracts, runtime compatibility and distribution review. Broader feature acceptance remains open. No push or publication.
