# WordPress WooCommerce Polylang Integration

[![project status](http://www.repostatus.org/badges/latest/active.svg)](http://www.gitchecker.com/hyyan/woo-poly-integration)
[![Latest Stable Version](https://poser.pugx.org/hyyan/woo-poly-integration/v/stable.svg)](https://packagist.org/packages/hyyan/woo-poly-integration)
[![Wordpress plugin](http://img.shields.io/wordpress/plugin/v/woo-poly-integration.svg)](https://wordpress.org/plugins/woo-poly-integration/)
[![Wordpress](http://img.shields.io/wordpress/plugin/dt/woo-poly-integration.svg)](https://wordpress.org/plugins/woo-poly-integration/)
[![Wordpress rating](http://img.shields.io/wordpress/plugin/r/woo-poly-integration.svg)](https://wordpress.org/plugins/woo-poly-integration/)
[![License](https://poser.pugx.org/hyyan/woo-poly-integration/license.svg)](https://packagist.org/packages/hyyan/woo-poly-integration)

[This plugin](https://github.com/hyyan/woo-poly-integration/) makes it possible to run multilingual e-commerce sites using
WooCommerce and Polylang.It makes products and store pages translatable, lets 
visitors switch languages and order products in their language. and all that from
the same interface you love.

[Read the full docs](https://github.com/hyyan/woo-poly-integration/wiki)

## Features

- [x] Auto Download Woocommerce Translation Files
- [x] Page Translation
- [x] Endpoints Translation
- [x] Product Translation
  - [x] Categories
  - [x] Tags
  - [x] Attributes
  - [x] Shipping Classes
  - [x] Meta Synchronization
  - [x] Variation Product
  - [x] Product Gallery
- [x] Order Translation
- [x] Stock Synchronization
- [x] Cart Synchronization
- [x] Coupon Synchronization
- [x] Emails
- [x] Reports
  - [x] Filter by language
  - [x] Combine reports for all languages


## What you need to know about this plugin

1. The plugin needs `PHP5.3 and above`
2. This plugin is developed in sync with [Polylang](https://wordpress.org/plugins/polylang) 
   and [WooCommerce](https://wordpress.org/plugins/woocommerce/) latest version
3. The plugin support variable products , but using them will `disallow you to 
   change the default language` , because of the way the plugin implements this
   support. So you have to make sure to choose the default language before you start
   adding new variable products.
4. Polylang URL modifications method `The language is set from content` is not 
   supported yet

## How to install

### Classical way

1. Download the plugin as zip archive and then upload it to your wordpress plugins folder and
extract it there.
2. Activate the plugin from your admin panel

### Composer way

1. run composer command : ``` composer require hyyan/woo-poly-integration```

## Setup your environment

1. You need to translate woocommerce pages by yourself
2. The plugin will handle the rest for you

## Translations

* Arabic by [Hyyan Abo Fakher](https://github.com/hyyan)

## Contributing

Everyone is welcome to help contribute and improve this plugin. There are several
ways you can contribute:

* Reporting issues (please read [issue guidelines](https://github.com/necolas/issue-guidelines))
* Suggesting new features
* Writing or refactoring code
* Fixing [issues](https://github.com/hyyan/woo-poly-integration/issues)
