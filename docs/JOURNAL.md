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
