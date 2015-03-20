<?php

/*
 * Plugin Name: Hyyan WooCommerce Polylang Integration
 * Plugin URI: https://github.com/hyyan/woo-poly-integration/
 * Description: Integrates Polylang with Woocommerce
 * Author: Hyyan Abo Fakher
 * Version: 0.3
 * Author URI: https://github.com/hyyan
 * GitHub Plugin URI: hyyan/woo-poly-integration
 * License: MIT License
 */

/**
 * This file is part of the hyyan/woo-poly-integration plubin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!defined('ABSPATH')) {
    exit('restricted access');
}

define('WPI_BASE_FILE', __FILE__);

require_once __DIR__ . '/src/WPI/Plugin.php';

new WPI\Plugin();
