# Maintenance batches

Findings are grouped by shared cause. A checker row is not a confirmed defect. Forum title routing is not content review or reproduction. Raw source evidence and previous dispositions remain available.

| Batch | Current checker rows | State |
| --- | ---: | --- |
| Distribution and development files | 270 | Pending source review |
| Request validation and authorization | 114 | All current rows have source dispositions, stock and formatting contexts verified together |
| Translation contracts | 45 | Source reviewed, owned calls corrected, dependency catalogs and legacy keys preserved |
| Public hooks and symbol ownership | 25 | Pending source review |
| Output escaping | 22 | Settings and notice batch corrected, all 22 current HTML, script composition and exception data rows reviewed |
| Database query safety and cost | 10 | Prepared and native query boundaries reviewed, fresh reads retained |
| Runtime and network compatibility | 8 | Pending source review |
| Diagnostic logging | 8 | Intentional diagnostics reviewed, request paths corrected |

The template output and inline script batches are corrected and verified. Templates and the three payment gateways share regression cases for unsafe translations and preserved rich text. The first request batch is corrected and reviewed together. The product cloning and synchronization request batch is corrected and verified. Navigation and current URL handling are corrected and verified together. Remaining context flags and diagnostic request data are reviewed with their owning feature batches. Public hook findings are reviewed together without renaming existing extension contracts. Development file findings are handled through the release archive rather than production source changes.

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

Evidence: `evidence/batch-inventory.json`. Current counts use `runtime-context-plugin-check.json`. The previous snapshot is retained. There are 232 current production rows, including 72 errors and 160 warnings. There are 33 production rows awaiting review or correction: 25 public contract rows and eight runtime rows. Counts are scan rows, not unique defects. Reviewed rows remain in the complete raw report, with their source dispositions available for re-review.

Translation contracts were handled together with unchanged source catalog keys, corrected plugin domains and placeholder guidance. Release archive review contains 270 development or packaging rows. Forum content review still has 383 reports pending. Native feature acceptance, catalog language acceptance, third party integrations, large catalog performance and older stack verification remain separate completion gates.

Stock and numeric formatting request contexts were verified in one batch. Native frontend checkout, legacy admin AJAX checkout and product editing preserve linked stock. Pure formatting flags retain their legacy presence contract. The full suite passes 215 tests with 624 assertions.
