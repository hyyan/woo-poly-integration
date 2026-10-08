# Security review handoff

Current production source is c2487d4. Refund verification is committed in a73ff74 and its evidence in 5c0979d. The four existing suites pass 699 tests with 1935 assertions. The actual installed release scan has thirteen findings, one error and twelve warnings. The goal is incomplete. No exception is accepted. No WordPress.org approval, submission, publication or push is claimed.

## Reported security issues

The variation AJAX callback runs at priority nine before WooCommerce's callback. It verifies delete-variations through check_ajax_referer using the security field, then requires edit_products. Every request member must be an integer or ASCII digit string within the native integer range before any batch deletion. Leading zeros remain supported. The callback validates actual variation types and requires edit_post on the selected parent and every related parent. Corrupt related product types cause rejection before deletion.

Variation::getRelatedVariation independently validates direct input and canonical source metadata before database access. Its cached query uses wpdb->prepare with string placeholders for the metadata key and exact normalized text comparison. Binary comparison rejects malformed suffixes and whitespace while retaining padded integer references. Duplicate metadata writes reject overflowing IDs. The shared request integer reader also rejects overflow. Stock and coupon consumers independently restrict variation relationships to actual variation posts.

Both actual installed WooCommerce clients, versions 3.0.0 and 11.1.2, already send security from delete_variations_nonce for woocommerce_remove_variations. Their source was inspected again. The plugin has no independent removal AJAX sender. No duplicate client or dependency asset modification was added. The first current asset lookup used a missing development path and was corrected to the installed assets path.

Regression and native browser evidence includes malformed and mixed batch rejection, overflow rejection, missing and invalid nonce, denied roles and parent permissions, authorized administrator and shop manager removal, real linked deletion and unrelated product preservation. These checks cover the inspected paths and do not prove every possible deployment or extension.

## Artifact and upgrade evidence

Archive SHA256 is 660c13f5dbada861f4cd4e9c27872fdb702d7704e9af9cb42d82936634dcf074. Both installed stores match all 73 manifest file hashes. Tests, tools and review documents are excluded from the archive. Version remains 1.5.1. The directory slug, text domain, catalogs, settings IDs, public hooks and published minimum requirements remain.

The current store uses WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10 and PHP 8.5.11. The minimum upgrade store uses WordPress 5.4.2, WooCommerce 3.0.0, Polylang 2.7.4 and PHP 7.4.9. The latest native helper passes PHP 7.0 syntax. The preceding production integer range batch retains its full source syntax evidence.

Published archive 1.5.0 update checks cover stored product, term, media, shipping and coupon data, populated translated taxonomy pages, native reports, classic guest carts, simple and custom variation checkout, linked stock and partial refunds. The refund batch creates native orders programmatically and executes eight refunds through Chrome, with twelve browser comparisons. Published orders and refunds retain their exact business snapshots across the update. Native edit locks are excluded from those snapshots because viewing an order changes them. Fixture data, settings and temporary users are cleaned up.

The complete published upgrade feature matrix is unfinished. Remaining cases include broader global and wildcard variation selection, parent managed stock, backorders, account purchases, shipping, current block checkout and HPOS upgrade scenarios. Prior candidate behavior checks are retained but are not substituted for complete published upgrade proof. A native old WordPress and WooCommerce admin thumbnail defect is reproduced under the published baseline and recorded separately. External plugin, theme, transport and service defects remain outside the owned plugin correction scope.

## Thirteen remaining findings

| Group | Count | Current evidence |
| --- | --- | --- |
| Request verification | 6 | Two currency switcher display flag reads and four public layered navigation reads retain existing unsigned client and link behavior |
| Public hook naming | 2 | Protected filter name methods retain their existing default names and subclass overrides |
| Direct database queries | 2 | Relationship and duplicate SKU queries are prepared and cached. Scanner warnings remain |
| Other contracts | 3 | Trusted inline script output, legacy chosen attribute global and the existing directory slug |

The exact current locations, source fingerprints and reasons are in evidence/plugin-check-remaining-compatibility-review.json. These dispositions do not resolve findings. All thirteen remain open.

The minimum WordPress inline script renderer does not emit the existing ID attribute. The newer renderer that accepts attributes is absent from the minimum version. No version increase, script identity change, whole JavaScript HTML escaping, unchecked verifier or suppression is introduced.

The installed trademark rule checks the existing directory name directly and its restricted list includes woo-. Changing the display name has already removed the display name findings but cannot remove this directory finding. A coordinated directory identity decision remains necessary. No directory migration is approved or performed.

## Files, commits and completion

COMMITS.md lists actual Conventional Commit subjects and counts excluding tests and configuration through its stated snapshot. evidence/current-maintenance-file-history.json maps all changed files to their commits. The final documentation commit is reported separately when newer than that inventory snapshot.

Completion requires zero installed findings, the complete requested upgrade acceptance matrix, full diff review and successful relevant verification. Finite tests do not establish an absolute guarantee against every possible future defect. Independent Plugins Team review remains necessary. Large catalog performance, remote CI, real email delivery and hosted payment callbacks are not claimed verified.
