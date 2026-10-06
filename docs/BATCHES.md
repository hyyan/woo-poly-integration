# Maintenance batches

Findings are grouped by shared cause. A checker row is not a confirmed defect. Forum title routing is not content review or reproduction. Raw source evidence and previous dispositions remain available.

| Batch | Current checker rows | State |
| --- | ---: | --- |
| Distribution and development files | 261 | Pending source review |
| Request validation and authorization | 111 | 108 current rows have source dispositions, three context selectors remain pending |
| Translation contracts | 63 | Pending source review |
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

Evidence: `evidence/batch-inventory.json`. Current counts use `query-diagnostics-plugin-check.json`. The previous snapshot is retained. There are 247 current production rows, including 90 errors and 157 warnings. Source dispositions cover 108 request rows, all 22 output rows, ten database rows and eight diagnostic rows. There are 99 production rows awaiting review or correction. Counts are scan rows, not unique defects.

Related settings and notices, queries and diagnostics were completed as shared batches. Remaining source groups contain 63 translation rows, 25 public contract rows, eight runtime rows and three request context selectors. Release archive review has 261 development or packaging rows. Forum content review still has 383 reports pending. Native feature acceptance, third party integrations, large catalog performance and older stack verification remain separate completion gates.
