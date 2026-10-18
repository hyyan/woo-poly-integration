# Manual testing

Run commands from the plugin repository. Docker Desktop, Make, Bash, Node.js 24.15 or later, zip and rsync are required. The first launch downloads the pinned WordPress tools and plugins. The store uses its own Docker project and volumes.

## Start, reset and stop

```sh
make up
make reset
make stop
```

`make up` starts the containers and creates fixtures on the first launch. Repeating it retains the existing store and refreshes the plugin from the current repository source. `make reset` deletes this manual store and creates the same starting products, stock, settings and accounts. It removes test orders and edits. After reset, clear browser cookies or use a fresh private browser window. `make stop` preserves the data. `make status` shows the containers.

Open the store and administration addresses printed after setup.

| Account | Role | Password |
| --- | --- | --- |
| manual-admin | Administrator | manual-password |
| manual-manager | Shop manager | manual-password |
| manual-customer | Customer | manual-password |
| manual-subscriber | Subscriber | manual-password |

These credentials belong to the disposable manual store. The web server listens on the local interface only.

## Starting data

English and German store pages and linked products are created. Every translated product pair shares its SKU, while descriptions differ by language.

| Product | Starting behavior |
| --- | --- |
| Manual simple | Price 20, stock 10 in each language |
| Manual variable | Red or Rot costs 20, Blue or Blau costs 25, each linked variation has stock 10 |
| Manual parent-stock | Variations share parent stock 10 |
| Manual wildcard | One Any colour variation accepts either colour, stock 10 |
| Manual backorder | Starting variation stock 0, backorders allowed with notification |
| Manual download | Virtual downloadable product, price 20, stock 10, language specific local text file |

The store includes linked Clothing and Kleidung categories, Featured and Empfohlen tags, and translated colour terms. Coupons are `manual5` for a fixed 5 discount with minimum spend 10, `manual10` for 10 percent, and `manual-category` for 3 off matching translated category items.

Shipping to the UK offers delivery for 5 or free pickup. A 20 percent VAT rate applies. Offline payment creates real local orders without taking payment. Use address 10 Test Street, London, SW1A 1AA, United Kingdom. All outgoing mail is captured locally. Administrator Tools > Manual store mail shows the last twenty messages, including password reset links. No email is delivered externally.

## Manual feature checklist

Perform each applicable check in English and German. Reset between destructive checks.

1. Open the shop, product, category and tag pages. Switch language. Check titles, descriptions, colour labels, prices, URLs and the matching translated page.
2. Edit a product as the administrator and shop manager. Change price and stock and save. Check the linked values and independent translated description. Create a translation from the native product editor.
3. Select both colours on Manual variable. Check the selected price and availability. Repeat with wildcard, parent stock and backorder products.
4. Add a product to the cart. Change quantity and switch language. Check product identity, selected attributes, prices and quantities. Apply each coupon and check minimum spend and category restrictions.
5. Complete guest and signed customer purchases. Check delivery versus pickup, VAT, receipt language and order total. Verify order language and items in administration.
6. Buy one variation. Confirm both linked stocks fall once, while the other variation is unchanged. Refund with restocking and confirm both linked stocks return once. Repeat parent stock and backorder purchases.
7. Sign in as manual-customer. Check order history, order details, addresses and account language links. Register another customer and request a password reset. Inspect the captured emails and follow their links.
8. Change order status and resend an order email. Check the captured subject, heading, footer and order language.
9. Buy Manual download, then mark the offline order completed. Check the account Downloads page and the language specific file.
10. Delete a variation using the native product editor. Confirm its linked variation is removed and unrelated products remain. A subscriber or customer must have no product editing controls. Automated regressions cover malformed IDs and invalid nonces.
11. Open WooCommerce reports in each language and combined mode. Check quantities against the purchases just made. Review integration settings and change a setting, then reload to confirm it saved.
12. Check attributes and language filtering from native shop controls. Check product language restrictions and save prevention in the product editor.

## Classic and block checkout

```sh
make classic
make block
```

These commands switch the configured cart and checkout pages. Repeat purchases in each format. They preserve the existing products and orders.

## Order storage

The starting store uses legacy order storage. WooCommerce Settings > Advanced > Features provides native HPOS controls. Enable compatibility synchronization, let existing orders synchronize, and then enable HPOS. Check existing and new orders. Disable compatibility synchronization and repeat account history, purchase and refund checks. Follow the native WooCommerce migration prompts. Reset restores the starting legacy mode.

## Upgrade comparison

Use the same store for a real update comparison. Install the official published 1.5.0 ZIP through Plugins > Add Plugin > Upload Plugin, replacing the installed candidate. Perform the checks and retain a few orders. Then upload the freshly generated `work/manual-lab/candidate.zip` through the same native screen. Confirm products, stock, settings, accounts, orders and translated links remain intact. Recheck the workflows after update.

This manual store runs the pinned current WordPress and WooCommerce stack. It does not replace minimum version verification. External payment gateways, third party themes and real email delivery require separate checks.

The fixtures use dedicated shop pages. Using the shop as the static homepage produced a German redirect loop during fixture preparation and still requires a separate published versus candidate comparison. No production fix or regression attribution is claimed for that configuration.
