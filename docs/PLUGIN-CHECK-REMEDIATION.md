# Active Plugin Check remediation

The baseline installed scan contained 230 findings: 66 errors and 164 warnings. The current installed scan contains 209 findings: 45 errors and 164 warnings. The prior dispositions explain inspected code but do not resolve the new goal.

| Related batch | Findings | Verification |
| --- | --- | --- |
| Request boundaries | 83 recommended nonce checks, 13 missing nonce checks, 13 sanitization checks and five unslash checks | Inspect actual mutation and read contracts, preserve upstream verification and native frontend behavior, run negative and authorized cases |
| Output boundaries | 16 output errors and six exception errors | Verify the complete rendered output boundary, preserve forms, scripts and native controls, test hostile values |
| Translation contracts | 30 domain errors, 11 dynamic string errors, two mixed placeholder errors and one unordered placeholder error | Preserve existing catalogs and stored string identities, verify translated rendering and argument order |
| Public hooks and identifiers | 22 dynamic hook warnings, three variable warnings, two function warnings and one hook warning | Preserve existing public contracts and demonstrate any required compatibility mapping |
| Database and diagnostics | Six query shape warnings, four direct query or cache warnings and eight logging warnings | Preserve lookup and mutation semantics, inspect caching lifetimes and diagnostic data |
| Distribution and catalog loading | Three trademark warnings and one textdomain loading warning | Preserve plugin identity, bundled catalogs and older runtime behavior |

Work proceeds in related batches. Every finding stays tracked until corrected and rechecked or explicitly accepted by the maintainer as an unavoidable exception. Static classifications do not prove vulnerabilities, and written dispositions do not count as fixes. No blanket suppression is added. The final gate is the actual installed package scan and preserved native behavior.

CHECK-001 resolved 21 output findings with actual source changes and fresh installed verification. The single remaining output finding is the trusted inline script wrapper and requires separate inspection. All other batches remain open. Regression, syntax, artifact and native browser evidence uses the plugin-check-output-batch prefix.
