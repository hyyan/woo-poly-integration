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
