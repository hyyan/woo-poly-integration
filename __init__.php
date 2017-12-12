<?php

/*
 * Plugin Name: Hyyan WooCommerce Polylang Integration
 * Plugin URI: https://github.com/hyyan/woo-poly-integration/
 * Description: Integrates Woocommerce with Polylang
 * Author: Hyyan Abo Fakher
 * Author URI: https://github.com/hyyan
 * Text Domain: woo-poly-integration
 * Domain Path: /languages
 * GitHub Plugin URI: hyyan/woo-poly-integration
 * License: MIT License
 * Version: 1.0.4
 */

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!defined('ABSPATH')) {
    exit('restricted access');
}

define('Hyyan_WPI_DIR', __FILE__);
define('Hyyan_WPI_URL', plugin_dir_url(__FILE__));

require_once ABSPATH.'wp-admin/includes/plugin.php';
require_once __DIR__.'/vendor/class.settings-api.php';
require_once __DIR__.'/src/Hyyan/WPI/Autoloader.php';

/* register the autoloader */
new Hyyan\WPI\Autoloader(__DIR__.'/src/');

/* bootstrap the plugin */
new Hyyan\WPI\Plugin();
