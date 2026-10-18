# Stock synchronization

WooCommmerce has a fairly simple Inventory management model, which allows for a single pool of stock (no concept of different warehouses in different locations) without further plugins.

If you use this option in WooCommerce-Polylang environment then you do have the option as to whether to manage stock separately for each language version of the products, or unified as one pool of stock available in different languages.

However **these settings apply to all products**: you can't currently choose for each product or product category whether you want stock to be synchronised or not.

Generally we recommend this option is left enabled.

For example:
* if only a small percentage of the products have language specific packaging etc and need to be tracked separately for inventory management, then leave stock synchronisation enabled and create these language specific product versions as separate products (which may or may not be translatable).   
* if there are different products in different languages then they should be created as separate products and synchronisation should be left on:  For example "Love in the Time of Cholera" and "El amor en los tiempos del cólera" are the same book, in English translation and Spanish original but clearly they are different products from stock management point of view and customer point of view.  In fact an English speaker who is studying Spanish might like to buy the original language "El amor en los tiempos del cólera" on the English site (or even both versions), so it is quite correct to treat them both as separate products.

If you have a good use case for turning off Stock synchronization, please add the use case here.

Note that to completely turn off stock synchronization you need to turn off both the [Metas](/settings/metadata.md) and [Features](/settings/features.md) settings for Stock.
