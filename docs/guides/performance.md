# Performance

Polylang and woo-poly-integration have relatively low performance impact since they do not fundamentally change the way information is stored and retrieved.

So generally the same performance recommendations apply as with any installation.

## Server

* use a PHP accelerator such as OPCache to cache the pre-parsed PHP code
* use a Caching tool such as WP SuperCache to cache pre-generated html for your products:  in the fastest modes this drastically reduces server load and increases throughput by allowing the webserver to serve the shop page from cache without executing PHP code.
   Note: check that your cart, checkout and account pages are excluded from caching, if necessary, specifically add these terms (and any translations you are using for them) to the list of urls which should not be cached.


## Client

Additional recommendations for editing products on a slow computer:

* Use Screen Options to turn off boxes that you don't need on the product edit screen
* Reduce Autosave frequency, eg add in wp-config.php
    `/* switch autosave to 10 minutes */`
    `define('AUTOSAVE_INTERVAL', 600);`
    or as appropriate for your situation.
* Use a plugin to reduce WordPress Heartbeat frequency
