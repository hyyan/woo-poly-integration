# Original feature acceptance

The README feature list defines the preservation contract. A pending row blocks a complete compatibility claim. Each row needs Chrome steps, expected and actual results, saved evidence and relevant automated regression coverage.

| Feature | Required proof | Current status |
| --- | --- | --- |
| WooCommerce translation downloads | Language installation downloads and loads the correct catalog | Pending |
| Store pages | Shop, cart, checkout and account route to linked language pages | Native automatic creation, both language shop and account routes and empty cart and checkout redirects pass. Filled simple checkout and configured translated receipt slugs pass for the matrix below |
| Endpoints | Account and checkout endpoints work in both languages | Four authenticated account endpoints pass in each language. Custom slug, order pagination, address type and lost password language switching pass with pretty and plain URLs. Checkout receipt purchases pass for the matrix below |
| Products | Create translation from original admin flow and preserve independent content | Pending |
| Categories and tags | Linked terms, URLs and archive filtering in both languages | Linked category archive filtering and related product fixtures pass in Chrome. Native term creation, hierarchy and tags pending |
| Attributes | Global and custom attributes translate and select correctly | Custom variable selection checked, global attributes pending |
| Shipping classes | Class translation and rates persist through checkout | Pending |
| Product meta | Enabled synchronization and disabled fields behave as configured | Pending |
| Variable products | Creation, updates, prices, default attributes and translated removal | Removal verified for admin and shop manager, creation/update pending |
| Product gallery | Linked gallery and featured image render and stay synchronized | Pending |
| Orders | Language retained at checkout and throughout order administration | Simple checkout order language passes in legacy and HPOS with synchronization enabled or disabled. Wider order administration remains pending |
| Stock | Orders, refunds and direct edits synchronize stock without double deduction | Chrome full product Update synchronized variation stock, Simple purchase deduction and one native HPOS manual refund pass. Variation orders and refunds pending |
| Cart | Language changes retain quantities, variations and valid coupons | Product language transition observed, full matrix pending |
| Coupons | Validity, limits and translated cart application agree | Pending |
| Emails | Order and account emails use intended customer language | Pending |
| Reports | Filter language or combine translations without duplicate totals | Pending |
| Settings and upgrade | Existing settings, data and public hooks retain semantics | Pending |
| Locale formatting | Empty dimensions render, numbers localize and admin values stay numeric | Targeted PHP and bilingual Chrome checks passed |
| Variation deletion | Invalid requests preserve records and valid UI deletion removes linked pair | Targeted WordPress and Chrome checks passed |
| Classic cart and checkout | Original supported purchase flow completes in both languages | English and German simple offline purchases pass with legacy and both HPOS synchronization modes. Shipping and coupons pending |
| Block cart and checkout | Latest purchase flow completes in both languages | English and German simple offline purchases pass with legacy and both HPOS synchronization modes. Shipping and coupons pending |
| Older supported stack | Same feature contract passes without increasing plugin minima | Pending |

Existing documented limitations remain: default language cannot be changed after variable products are created, and the content based language URL mode is unsupported. These limitations are not new fixes or compatibility claims.

Automatic page creation retains native block cart and checkout content with identical source and target hashes. Four format regression cases preserve block and classic behavior. Evidence: store-pages prefixes. The fresh site initially lacked Apache rewrite configuration and reached the original lab. Saved native rules for the owned subdirectory before claiming routing acceptance. No purchase claim follows from empty checkout redirects.

Translated endpoint proof uses native Polylang string catalogs and a configured WooCommerce order-history slug. Chrome follows native language links with order page 2, preserving the target slug and value. Address type and lost password links stay on their respective endpoint. Plain permalink navigation also passes. Native account menu links do not accumulate endpoints. The owned read only language helper was removed and pretty permalinks restored.

Receipt matrix: 12 actual signed account purchases of linked virtual simple products using the native cheque gateway. Both language paths, block and classic checkout and legacy or HPOS storage pass. HPOS was tested with synchronization enabled and disabled, including native placeholder posts. Stored order language and total match each case. Starting stock 30 becomes 15 in both products after 15 units including baseline purchases, then 16 after one native manual refund. All fixture orders, refund, products and classic pages were deleted with ownership guards and original settings restored. Native German block empty cart renders afterward. Evidence: checkout-receipt prefixes. This does not establish guest cookie behavior, visible default language URLs, variation stock, shipping, coupons, email catalogs, external gateways or older stack support.
