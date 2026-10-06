# Original feature acceptance

The README feature list defines the preservation contract. A pending row blocks a complete compatibility claim. Each row needs Chrome steps, expected and actual results, saved evidence and relevant automated regression coverage.

| Feature | Required proof | Current status |
| --- | --- | --- |
| WooCommerce translation downloads | Language installation downloads and loads the correct catalog | Pending |
| Store pages | Shop, cart, checkout and account route to linked language pages | Native automatic creation, both language shop and account routes and empty cart and checkout redirects pass. Filled checkout and custom translated slugs pending |
| Endpoints | Account and checkout endpoints work in both languages | Four authenticated account endpoints pass in each language. Custom translated slugs and checkout endpoints pending |
| Products | Create translation from original admin flow and preserve independent content | Pending |
| Categories and tags | Linked terms, URLs and archive filtering in both languages | Linked category archive filtering and related product fixtures pass in Chrome. Native term creation, hierarchy and tags pending |
| Attributes | Global and custom attributes translate and select correctly | Custom variable selection checked, global attributes pending |
| Shipping classes | Class translation and rates persist through checkout | Pending |
| Product meta | Enabled synchronization and disabled fields behave as configured | Pending |
| Variable products | Creation, updates, prices, default attributes and translated removal | Removal verified for admin and shop manager, creation/update pending |
| Product gallery | Linked gallery and featured image render and stay synchronized | Pending |
| Orders | Language retained at checkout and throughout order administration | Pending for legacy and HPOS storage |
| Stock | Orders, refunds and direct edits synchronize stock without double deduction | Chrome full product Update synchronized variation stock, orders and refunds pending |
| Cart | Language changes retain quantities, variations and valid coupons | Product language transition observed, full matrix pending |
| Coupons | Validity, limits and translated cart application agree | Pending |
| Emails | Order and account emails use intended customer language | Pending |
| Reports | Filter language or combine translations without duplicate totals | Pending |
| Settings and upgrade | Existing settings, data and public hooks retain semantics | Pending |
| Locale formatting | Empty dimensions render, numbers localize and admin values stay numeric | Targeted PHP and bilingual Chrome checks passed |
| Variation deletion | Invalid requests preserve records and valid UI deletion removes linked pair | Targeted WordPress and Chrome checks passed |
| Classic cart and checkout | Original supported purchase flow completes in both languages | Pending |
| Block cart and checkout | Latest purchase flow completes in both languages | Pending |
| Older supported stack | Same feature contract passes without increasing plugin minima | Pending |

Existing documented limitations remain: default language cannot be changed after variable products are created, and the content based language URL mode is unsupported. These limitations are not new fixes or compatibility claims.

Automatic page creation retains native block cart and checkout content with identical source and target hashes. Four format regression cases preserve block and classic behavior. Evidence: store-pages prefixes. The fresh site initially lacked Apache rewrite configuration and reached the original lab. Saved native rules for the owned subdirectory before claiming routing acceptance. No purchase claim follows from empty checkout redirects.
