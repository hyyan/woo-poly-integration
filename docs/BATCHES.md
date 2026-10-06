# Maintenance batches

Findings are grouped by shared cause. A checker row is not a confirmed defect. Forum title routing is not content review or reproduction. Raw source evidence and previous dispositions remain available.

| Batch | Current checker rows | State |
| --- | ---: | --- |
| Distribution and development files | 189 | Pending source review |
| Request validation and authorization | 160 | 79 current rows have source dispositions, 81 remain pending |
| Translation contracts | 64 | Pending source review |
| Public hooks and symbol ownership | 25 | Pending source review |
| Output escaping | 22 | 35 earlier rows corrected, 16 current native HTML or exception data rows reviewed, 6 remain pending |
| Database query safety and cost | 10 | Pending source review |
| Runtime and network compatibility | 9 | Pending source review |
| Diagnostic logging | 8 | Pending source review |

The first output escaping batch is corrected and verified. Templates and the three payment gateways share regression cases for unsafe translations and preserved rich text. The first request batch is corrected and reviewed together. Remaining product cloning and synchronization inputs, navigation and current URL handling form the next request batch. Public hook findings are reviewed together without renaming existing extension contracts. Development file findings are handled through the release archive rather than production source changes.

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

Evidence: `evidence/batch-inventory.json`. Current counts use `request-batch-plugin-check.json`. The previous snapshot is retained in the inventory. There are 298 current production rows, including 92 errors and 206 warnings. Current source dispositions cover 79 request rows and 16 output rows. Counts are scan rows, not unique defects.
