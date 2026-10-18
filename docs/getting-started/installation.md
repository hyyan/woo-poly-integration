# Installation

The plugin requires WordPress 5.4+, PHP 7.0+, WooCommerce 3.0+, and Polylang 2.0+. Your installed WooCommerce and Polylang versions may require newer WordPress or PHP versions. The current release has been tested with WordPress 7.1.3, WooCommerce 11.1.2, Polylang 3.8.10, and PHP 8.3, 8.4, and 8.5. Older supported versions have not all been retested.

Back up your store before installing an update and test it on a staging site first. Choose your default language before creating variable products; changing it afterward is unsupported.

Screenshots show earlier versions of the administration screens. Labels and layout may differ in your installed versions.

## Dependencies

1. **WooCommerce**: woo-poly-integration version 1 requires WooCommerce 3 or higher, it is recommended to use the [latest release version from WordPress.org](https://wordpress.org/plugins/woocommerce/).  If you are doing development or very brave you might also like to use the [WooCommerce Beta Tester plugin](https://github.com/woothemes/woocommerce-beta-tester/) or the [WooCommerce GitHub repository](https://github.com/woocommerce/woocommerce). If you must use WooCommerce2.6 please continue to use woo-poly-integration version 0.26.
    Please activate wooCommerce and run through the setup wizard or manually set the basic settings before activating the translation tool.

2. **Polylang**  please use [latest version from Wordpress.org](https://wordpress.org/plugins/polylang/) or [Polylang.Pro](https://polylang.pro/).
After activating Polylang, please complete Polylang basic configuration, woo-poly-integration can't do anything unless Polylang is configured, that is:
    1. go to Languages, Languages /wp-admin/admin.php?page=mlang and add your chosen languages.
    2. go to Languages, Settings /wp-admin/admin.php?page=mlang_settings and check the options for URL modifications. Note that the Polylang URL setting "The language is set from content" is not compatible with the Polylang-WooCommerce-Plugin combination.

    Make sure that Polylang is configured and working before installing this plugin. 


## How to install

### Standard install
In your site Admin, go to Plugins, Add New and search for Hyyan Integration for WooCommerce and Polylang and install it.

### Manual install

1. Download the plugin ZIP from the [GitHub releases](https://github.com/hyyan/woo-poly-integration/releases).
2. In WordPress, open Plugins, Add New, Upload Plugin, select the ZIP and install it.
3. Activate the plugin from your admin panel.

### Composer way

1. run composer command : ``` composer require hyyan/woo-poly-integration```


## Historical upgrade from WooCommerce 2.x

This section applies only to stores still running WooCommerce 2.x. Skip it when updating a current WooCommerce installation.

WooCommerce Polylang Integration 1.0.0 is the first release for WooCommerce 3.x.
DO NOT install until you are doing the upgrade to WooCommerce 3.x.

If you are currently on WooCommerce 2.x, we recommend:
1. Take a backup, especially of the database and the current versions of WooCommerce and woocommmerce-polylang integration. And do EVERYTHING in a test environment first.  Test. 
WooCommerce 3 has a large number of breaking changes which may and do break other plugins.
1. temporarily deactivate this plugin, 
2. upgrade WooCommerce and this plugin
3. run WooCommerce database upgrade following the wooCommerce prompt
4. reactivate this plugin
