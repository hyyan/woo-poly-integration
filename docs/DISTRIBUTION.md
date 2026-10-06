# Local release archive

Run `tests/bin/package.sh` from the checkout. The default output is `work/release/woo-poly-integration.zip` with an adjacent manifest containing each file hash and the ZIP hash. An optional first argument selects an output path. Keep generated output under excluded work or outside the checkout.

The builder reuses `.exclude-list` with rsync and creates a sorted ZIP with fixed timestamps using Python standard libraries. It includes working source changes. It does not stage, commit, upload or publish. Development fixtures, tests, internal evidence, repository metadata and deployment scripts are excluded. Git export exclusions also omit development content. Runtime scripts, compiled catalogs and bundled runtime settings code remain.

The inspected candidate contains 73 files. Two builds produce SHA256 `150a9239f3ff03f116a9ea90cce218f5820a1088f54dc1556c64618f6051cc9e`. Every file matches source and the fresh installed plugin. The existing candidate version remains 1.5.1. Final version and tested headers await full acceptance and release preparation.

Plugin Check was run on the unpacked actual archive with `--mode=update --format=json` and no finding exclusions. The complete raw report and matching dispositions are saved in evidence. It contains 231 rows, 66 errors and 165 warnings. Reviewed raw source findings remain visible. The outdated tested header and established name and slug warnings still require release review. Native parser contributor warnings do not prove directory ownership.

Fresh native installation and activation, two settings forms with nonce fields, six local controls and all four JavaScript files and two compiled catalogs pass Chrome inspection. Served assets match the archive hashes. Direct bootstrap access is restricted. Browser requests added no runtime log entries. This proves package installation and scoped admin acceptance, not the complete store or older stack matrix.

The legacy `deploy.sh` publishes to SVN and is not a packaging command. It was not executed. WordPress.org submission and all publication require separate authorization.
