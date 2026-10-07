# Verified maintenance commits

The previously pending source and regression changes are now committed in related batches. The source at 4df5b1a matches every distributable file in the tested archive manifest. Commits do not change the installed artifact or invalidate its native browser evidence.

- 67271c0 fix: preserve translated checkout receipt endpoints
- c66e470 fix: preserve translated cart state and validation
- ae7bbea fix: restore caller language after translated emails
- 6b0d805 fix: retain product defaults and reload locale catalogs
- 8d6f920 fix: replace only complete translated shop path segments
- 3a83aa6 fix: resolve validated translated variation deletion sources
- d4aa1ee fix: restore stock synchronization callbacks after errors
- 6dd38b2 fix: preserve independent product attributes during copies
- 28bbc81 fix: preserve variation copy and import synchronization
- d28e504 fix: preserve native translated store notice controls
- 5c0a0ed fix: translate configured native tax rate labels
- 0f31724 fix: translate configured block and receipt payment titles
- df3ece1 fix: preserve language filtered reports under HPOS
- 4df5b1a docs: update tested versions and maintenance changelogs

All earlier 37 maintenance commits remain intact. Current validation passes 312 tests with 1061 assertions, including 26 security cases with 78 assertions. All 61 installed PHP files pass syntax checks and all 73 installed hashes match. Plugin Check retains 230 individually reviewed findings. These findings and directory approval are not represented as a clean scan. The evidence and journal are committed separately from these source batches.
