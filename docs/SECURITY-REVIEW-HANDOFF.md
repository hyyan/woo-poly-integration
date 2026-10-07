# Security re-review handoff

The four reported variation removal issues are corrected in the inspected code. This document identifies the corrected boundaries and current artifact evidence. The broader maintenance goal remains incomplete.

| Report | Correction | Evidence |
| --- | --- | --- |
| Request variation IDs reach database operations without sufficient validation | Variable.php checks every batch member before any deletion, accepts integers or decimal digit strings, normalizes IDs to integers and checks actual variation post types. Variation.php independently validates direct callers | VariationSecurityTest malformed batch, type and lookup cases |
| Related variation lookup interpolates an ID into SQL | Variation.php prepares the relationship query with %s for the metadata key and %d for the normalized ID. The other direct SKU lookup already uses %s and %d | Source inspection and native database regressions |
| Authenticated users can invoke removal without sufficient authorization | The early callback requires edit_products and edit_post permission for the source parent and every affected translated parent before mutation | Denied subscriber, customer and contributor cases, denied parent cases and authorized administrator and shop manager cases |
| State changing AJAX lacks request verification | The callback invokes check_ajax_referer with delete-variations and the security request field before deletion | Missing and invalid nonce regressions and native removal evidence |

## Client request and equivalent paths

The installed native WooCommerce variation client supplies security from delete_variations_nonce. Its existing nonce action matches the handler. The plugin JavaScript does not send a separate removal request, so introducing a duplicate client or replacing WooCommerce JavaScript is unnecessary. The final installed client was inspected again after the release rebuild.

The equivalent early featured product callback in Product.php requires edit_products and the native featured product nonce before changing translations. The only two direct plugin queries are prepared. Settings use the native Settings API. Broader input and output findings retain their individual source dispositions in the checker inventory.

## Changed security files

Product/Variable.php adds request verification, complete batch validation and permission checks before deletion. Product/Variation.php validates direct lookup input, prepares the relationship query and limits deletion to variation products. Product/Product.php corrects the equivalent featured product boundary. VariationSecurityTest.php and FeaturedProductSecurityTest.php cover real WordPress permissions, requests and database effects. Existing test configuration, lab helpers and the security evidence files are listed in SECURITY-REVIEW.md. Broader compatibility file changes and reasons are recorded in RELEASE-AUDIT.md and the individual batch documents.

__init__.php and readme.txt synchronize tested headers with the verified runtime. CHANGELOG.md and readme.txt describe the maintenance fixes. No minimum dependency requirement, public method signature or native AJAX nonce name is changed by these security corrections.

## Current verification

The current archive is woo-poly-integration.zip, with 73 files matching source, archive manifest and installed package. SHA256 is 9aba61f4fc629c154d781ff6960609677db560abbbe4c5b89c64a8c3b77880ec. Development fixtures, tests and review documents are excluded from the distribution.

The complete suite passes 312 tests with 1061 assertions. Fresh security execution passes 26 tests with 78 assertions. All 61 installed PHP files pass syntax checks. Both working tree and index diff checks pass. The native removal browser evidence verifies rejected requests retain linked variations and siblings, and authorized native removal deletes the linked variations while preserving siblings. Current Chrome confirms the final installed plugin is active without visible error notices.

The tested current stack is WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10 and PHP 8.5.11. Older native checks are documented in OLDER-COMPATIBILITY.md and the later batch records. This does not assert every historical dependency combination was verified.

Actual installed Plugin Check reports 230 findings, including 66 errors and 164 warnings, with an empty diagnostic stream. All findings retain source dispositions. PLUGIN-CHECK-SUBMISSION.md groups them for review. No clean checker result or Plugins Team approval is claimed.

## Manual and external verification

The maintainer excludes defects and missing features in other extensions from this release. EXTERNAL-BATCH.md is retained as historical context and is not an outstanding release gate. Actual email delivery and hosted payment callbacks were not exercised. Transport stayed suppressed in the local lab. The Plugins Team must assess the retained checker dispositions. The verified fixes are now committed in the batches listed in COMMITS.md. No remote CI success, directory submission or publication is claimed.

The final archive evidence is in final-source-review-audit.json and the release-metadata and release-security evidence files. Existing native browser evidence is preserved rather than replaced by a broad assertion of complete compatibility.
