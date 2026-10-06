# Maintenance batches

Findings are grouped by shared cause. A checker row is not a confirmed defect. Forum title routing is not content review or reproduction. Raw source evidence and previous dispositions remain available.

| Batch | Current checker rows | State |
| --- | ---: | --- |
| Distribution and development files | 167 | Pending source review |
| Request validation and authorization | 164 | Pending source review |
| Translation contracts | 64 | Pending source review |
| Public hooks and symbol ownership | 25 | Pending source review |
| Output escaping | 22 | 35 corrected rows and 10 owned or native HTML dispositions recorded, remaining sinks pending |
| Database query safety and cost | 10 | Pending source review |
| Runtime and network compatibility | 9 | Pending source review |
| Diagnostic logging | 8 | Pending source review |

The first output escaping batch is corrected and verified. Templates and the three payment gateways share regression cases for unsafe translations and preserved rich text. Request validation and authorization is next, with common negative inputs and native WordPress permission checks. Public hook findings are reviewed together without renaming existing extension contracts. Development file findings are handled through the release archive rather than production source changes.

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

Evidence: `evidence/batch-inventory.json`. Current counts use `output-batch-plugin-check.json`. The previous snapshot is retained in the inventory. There are 302 current production rows, including 92 errors and 210 warnings. Ten output rows have explicit source dispositions. Counts are scan rows, not unique defects.
