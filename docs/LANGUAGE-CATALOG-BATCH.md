# Language and catalog acceptance


Language and catalog batch: native Polylang Add language creates French with a valid native nonce. Both WooCommerce catalog requests return HTTP 200 with verification enabled. The first browser attempts do not install the catalog because CLI created WordPress core and target directories as root. CLI runs force direct filesystem access and therefore do not reproduce the native ownership check. Native observation shows the filesystem object was never initialized. WordPress get_filesystem_method compares the owner of its core file with newly written files. Correcting target directory ownership alone is insufficient. The owned package installation now has normal web process ownership. The final native browser addition selects WP_Filesystem_Direct, has no settings errors and installs 236 WooCommerce French files plus the WordPress core language pack.

The actual existing German and French pack paths return successfully with zero additional HTTP requests. The shipped Spanish plugin catalog loads through Plugin::loadTextDomain and native WordPress locale selection, translating Features to Características. Native French account labels use Se connecter. The integration generates French cart, checkout, account and shop pages. Empty checkout redirects to the French cart as expected. Copied cart block content retains source authored English headings while dynamic native account text is French. That page content case remains explicit in the navigation group and is not claimed translated. No production plugin change is justified by the filesystem failure.

Existing input, transport and catalog contract checks freshly pass 31 tests and 80 assertions. All 73 source and installed distributable hashes match the existing artifact. Helper PHP syntax, shell syntax and both diff checks pass. The complete 294 test suite with 1000 assertions and 231 finding installed scan remain current. Full suite, rebuild and checker are not repeated for unchanged production source.

Cleanup uses native Polylang Delete for the owned French language, deletes four generated pages and removes 317 newly created French catalog files and the temporary archive. Three baseline options are restored exactly. The owned observer, observation option and fixture option are removed. Only the owned French page creation notice is removed. Languages return to English and German. The owned guest tab is closed and the admin tab returns to its prior home page. Normal lab filesystem ownership remains as an intentional environment correction. The locale catalog group is closed, leaving five shared acceptance groups. No index, commit or publication action occurs. Evidence uses the language-catalog-batch prefix.

## Files changed

| Files | Purpose |
| --- | --- |
| work/language-catalog-batch-setup.php | Guard the fresh French fixture and snapshot baseline options and language files |
| work/language-catalog-batch-observer.php | Observe native nonce validity, verified HTTP status and filesystem initialization without recording secrets |
| work/language-catalog-batch-cleanup.php | Remove owned generated pages and files and restore baseline options after native language deletion |
| work/prepare-package-filesystem.sh | Correct ownership only in the guarded disposable package installation |
| docs/evidence/language-catalog-batch files | Preserve native request, filesystem diagnostics, catalog loading, browser content, existing pack, security check, hash continuity and cleanup proof |
| docs/ACCEPTANCE.md, docs/ISSUES.md, docs/JOURNAL.md, docs/BATCHES.md, docs/RELEASE-AUDIT.md, goal.txt and handoff.md | Record the completed catalog acceptance and five remaining groups |

No production source changed. Native browser catalog addition is proven after the environment correction. Authored English headings copied into French cart block content remain visible and require the separate page content check. Native hosted environments using FTP credentials are not covered by the direct filesystem fixture.
