# Maintenance batches

Findings are grouped by shared cause. A checker row is not a confirmed defect. Forum title routing is not content review or reproduction. Raw source evidence and previous dispositions remain available.

| Batch | Current checker rows | State |
| --- | ---: | --- |
| Distribution and development files | 277 | 268 excluded from actual archive, four metadata rows corrected, five retained rows reviewed or awaiting release gates |
| Request validation and authorization | 114 | All current rows have source dispositions, stock and formatting contexts verified together |
| Translation contracts | 45 | Source reviewed, owned calls corrected, dependency catalogs and legacy keys preserved |
| Public hooks and symbol ownership | 27 | Existing contracts and scoped template locals source reviewed |
| Output escaping | 22 | Settings and notice batch corrected, all 22 current HTML, script composition and exception data rows reviewed |
| Database query safety and cost | 10 | Prepared and native query boundaries reviewed, fresh reads retained |
| Runtime and network compatibility | 0 | Server runtime and admin network findings corrected and verified |
| Diagnostic logging | 8 | Intentional diagnostics reviewed, request paths corrected |

The template output and inline script batches are corrected and verified. Templates and the three payment gateways share regression cases for unsafe translations and preserved rich text. The first request batch is corrected and reviewed together. The product cloning and synchronization request batch is corrected and verified. Navigation and current URL handling are corrected and verified together. Context flags and diagnostic request data are reviewed with their owning feature batches. Public hook findings are reviewed together without renaming existing extension contracts. Development file findings are handled through the release archive rather than production source changes.

| Forum batch | Reports |
| --- | ---: |
| Product synchronization | 135 |
| Language and page translation | 63 |
| Checkout and cart | 62 |
| Needs content classification | 58 |
| Emails and payment gateways | 34 |
| Runtime failures and installation | 31 |
| Taxonomies and filtering | 27 |
| Third party integrations | 7 |
| Administration and project support | 6 |

Each feature batch uses one fixture set and acceptance matrix across related GitHub and forum reports. The batch inventory retains every report URL and existing summary. Reports without a summary remain unreviewed. Nothing is marked fixed from a title or from disappearing checker output alone.

Evidence: `evidence/batch-inventory.json`. Current counts use `network-views-plugin-check.json`. The previous snapshot is retained. There are 226 current production rows, including 66 errors and 160 warnings. There are no current production rows awaiting an initial source disposition. Counts are scan rows, not unique defects. Reviewed rows remain in the complete raw report, with their source dispositions available for re-review.

Translation contracts were handled together with unchanged source catalog keys, corrected plugin domains and placeholder guidance. Release archive review covers 277 development or packaging rows. Of these, 268 are excluded, four are corrected and five remain in the artifact scan. Forum content review has 367 reports pending after the related endpoint topic batch. Native feature acceptance, catalog language acceptance, third party integrations, large catalog performance and older stack verification remain separate completion gates.

Stock and numeric formatting request contexts were verified in one batch. Native frontend checkout, legacy admin AJAX checkout and product editing preserve linked stock. Pure formatting flags retain their legacy presence contract. At completion of that batch, the suite passed 215 tests with 624 assertions.

Public naming review preserves all 25 existing contracts. Current call and definition evidence is in public-contract-source-review.json. Relevant existing regressions pass 17 tests with 62 assertions. Native WooCommerce status renders the retained callback. This naming disposition does not imply complete feature acceptance.

Server runtime findings were corrected in one batch. WordPress URL parsing preserves native localized fragments and account queries. WooCommerce guarded timeout handling restores the captured limit on success and exceptions and supports disabled native functions. At completion of that batch, 225 tests passed with 639 assertions. Three remote badge rows and related social embeds carried into the now completed network batch.

The admin network batch replaces automatic remote images, scripts and frames with local controls and direct release, license, project, sharing and profile actions. Native Chrome and all 231 tests with 659 assertions pass. Two additional naming rows are scoped template locals with explicit source dispositions. Raw reviewed findings remain visible, and the full acceptance matrix is not yet cleared.

The distribution batch produces a reproducible 73 file ZIP and verifies every installed byte on a separate fresh WordPress site. Native activation, settings forms, nonce fields and all six script and catalog resources pass. The actual archive update scan contains 231 rows, 66 errors and 165 warnings. All 226 source rows retain their inspected dispositions. Remaining artifact metadata warnings cover the tested version header and three established name or slug notices. The original activation callback is source reviewed and preserved. Forum contents and full native compatibility remain open.

The shared store page and endpoint batch corrects native block content loss during automatic translation. Four format cases pass and all suites pass 235 tests with 671 assertions. Eight store page and eight account endpoint requests pass in both language paths. Native German empty block cart renders. Empty checkout redirects stay in the same language. Purchases and custom endpoint slug translation remain separate unproved cases. The corrected installed archive still reports 231 rows with retained source dispositions.

The translated endpoint and related report batch preserves active endpoint, configured source slug and value when switching language. Nine shared cases pass, with eight baseline failures reproduced. Full suites pass 244 tests with 680 assertions. Native Chrome verifies both language directions for pagination, address type and lost password, including actual plain permalink navigation. Eight related forum topics have content dispositions, leaving 367 pending. Current actual archive scan remains 231 rows with retained source dispositions.

The filled checkout and receipt batch verifies 12 actual native purchases across English and German, block and classic checkout, legacy storage and HPOS with synchronization enabled or disabled. The late Store API locale caused a source receipt slug to be used in a translated page URL. The native endpoint filter now translates only the matching endpoint suffix or query key, preserving values and existing filter query data. Five baseline failures and 14 endpoint cases pass. Full suites pass 249 tests with 685 assertions on WordPress 7.1.3. Native simple stock deduction and one manual refund pass. Eight more captured topics receive individual dispositions, with 367 content reviews pending. Visible default language URLs, variable stock, shipping, coupons, guest checkout, payment gateways and emails remain separate acceptance cases.
