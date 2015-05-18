# [Wordpress  WooCommerce Polylang Integration ](https://github.com/hyyan/woo-poly-integration/)

[![project status](http://stillmaintained.com/hyyan/woo-poly-integration.png)](http://stillmaintained.com/hyyan/woo-poly-integration)
[![Latest Stable Version](https://poser.pugx.org/hyyan/woo-poly-integration/v/stable.svg)](https://packagist.org/packages/hyyan/woo-poly-integration)
[![License](https://poser.pugx.org/hyyan/woo-poly-integration/license.svg)](https://packagist.org/packages/hyyan/woo-poly-integration)


This plugin makes it possible to run multilingual e-commerce sites using
WooCommerce and Polylang.It makes products and store pages translatable, lets 
visitors switch languages and order products in their language. and all that frome 
the same interface you love.

## Features

- [x] Page Translation
- [x] Product Translation
  - [x] Categories
  - [x] Tags
  - [x] Attributes
  - [x] Shipping Classes
  - [x] Meta Synchronization
  - [x] Variation Product
  - [x] Product Gallery
- [x] Order Translation
- [x] Cart Synchronization `Without Variation Support`
- [x] Coupon Synchronization
- [x] Emails
- [x] Reports
  - [x] Filter by language
  - [x] Combine reports for all languages


## What you need to know about this plugin

1. The plugin support variable products , but using them will `disallow you to 
  change the default language` , because of the way the plugin implements this
  support. So you have to make sure to choose the default language before start
  adding new variable products.

## How to install

### Classical way

1. Download the plugin as zip archive and then upload it to your wordpress plugins folder and
extract it there.
2. Activate the plugin from your admin panel

### Composer way

1. run composer command : ``` composer require hyyan/woo-poly-integration```

## Setup your environment

1. Make sure to setup your woocommerce permalinks correctly
2. You need to translate woocommerce pages by yourself
3. The plugin will handle the rest for you

## Contributing

Everyone is welcome to help contribute and improve this plugin. There are several
ways you can contribute:

* Reporting issues (please read [issue guidelines](https://github.com/necolas/issue-guidelines))
* Suggesting new features
* Writing or refactoring code
* Fixing [issues](https://github.com/hyyan/woo-poly-integration/issues)
