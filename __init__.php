<?php

/*
 * Plugin Name: Hyyan Integration for WooCommerce and Polylang
 * Plugin URI: https://github.com/hyyan/woo-poly-integration/
 * Description: Integrates Woocommerce with Polylang
 * Author: Hyyan Abo Fakher
 * Author URI: https://github.com/hyyan
 * Text Domain: woo-poly-integration
 * Domain Path: /languages
 * GitHub Plugin URI: hyyan/woo-poly-integration
 * License: MIT
 * Version: 1.5.1
 * Requires At Least: 5.4
 * Tested Up To: 7.1
 * WC requires at least: 3.0.0
 * WC tested up to: 11.1.2
 * Requires PHP: 7.0
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

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once __DIR__ . '/vendor/class.settings-api.php';
require_once __DIR__ . '/src/Hyyan/WPI/Autoloader.php';

/* register the autoloader */
new Hyyan\WPI\Autoloader(__DIR__ . '/src/');

add_action('before_woocommerce_init', static function () {
    if (class_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Get a product translation request after nonce and permission verification.
 *
 * @return array|false source and language, or false when verification fails
 */
function woo_poly_integration_get_product_translation_request()
{
    return Hyyan\WPI\Utilities::getProductTranslationRequest();
}

/**
 * Verify the native custom date request before report callbacks use it.
 * Preset and ordinary report links remain available without a nonce.
 *
 * @return bool
 */
function woo_poly_integration_verify_report_request()
{
    if (!isset($_GET['page']) || !is_string($_GET['page']) ||
            sanitize_text_field(wp_unslash($_GET['page'])) !== 'wc-reports') {
        return true;
    }
    if (isset($_GET['tab']) && is_string($_GET['tab']) &&
            sanitize_text_field(wp_unslash($_GET['tab'])) === 'stock') {
        return true;
    }
    if (isset($_GET['tab'], $_GET['report']) && is_string($_GET['tab']) && is_string($_GET['report']) &&
            sanitize_text_field(wp_unslash($_GET['tab'])) === 'customers' &&
            sanitize_text_field(wp_unslash($_GET['report'])) === 'customer_list') {
        return true;
    }
    if (!isset($_GET['range']) || !is_string($_GET['range']) ||
            sanitize_text_field(wp_unslash($_GET['range'])) !== 'custom') {
        return true;
    }
    return current_user_can('view_woocommerce_reports') &&
            isset($_GET['wc_reports_nonce']) && is_string($_GET['wc_reports_nonce']) &&
            wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wc_reports_nonce'])), 'custom_range') !== false;
}

/* bootstrap the plugin */
new Hyyan\WPI\Plugin();


/*
 * called when plugin is activated in settings, plugins
 */
function woo_poly_integration_on_activate() {
	update_option( 'wpi_wcpagecheck_passed', false );
	update_option( 'hyyan-wpi-flash-messages', '' );
}

register_activation_hook( __FILE__, 'woo_poly_integration_on_activate' );

