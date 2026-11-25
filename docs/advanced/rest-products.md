# Filter REST products by language

Use the `lang` query parameter on the authenticated WooCommerce products collection when a catalog feed or integration needs one language. The parameter accepts the **slug** shown under **Languages → Languages** in Polylang. For a French language whose slug is `fr`, use `fr` even if its WordPress locale is `fr_FR`.

For example, request `GET /wp-json/wc/v3/products?lang=fr` with your normal WooCommerce API credentials. If an English shirt and its French translation are linked, the French product’s own ID appears in the response. Products without a French version are omitted. The same parameter works on the v1 and v2 products collections.

## Filtering and pagination

- Combine `lang` with existing collection parameters. For example, `GET /wp-json/wc/v3/products?lang=fr&per_page=1` returns at most one French product on the first page.
- Filter products by category or tag using the term ID for the requested language. For example, if your German category ID is 31 and your German tag ID is 35, use `GET /wp-json/wc/v3/products?lang=de&category=31&tag=35`. WooPoly uses the IDs you supply.
- Read `X-WP-Total` and `X-WP-TotalPages` from the response headers. WooCommerce calculates them after applying the language and other product filters. Keep the same language when requesting the next page.
- An unknown slug or an array supplied as `lang` returns an empty collection. If an expected product is missing, check its assigned language and any other filters on the request.
- Omitting `lang` or passing an empty value keeps the endpoint’s existing query behavior.

This filter applies to WooCommerce product collections. Category, tag and attribute-term collections use separate WooCommerce endpoints. Fetch a specific product translation by using its own product ID.
