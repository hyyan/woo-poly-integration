<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;

/**
 * Shipping.
 *
 * Handle Shipping Methods
 *
 * @author Antonio de Carvalho <decarvalhoaa@gmail.com>
 */
class Shipping
{

    /**
     * Construct object.
     */
    public function __construct()
    {

        // Register woocommerce shipping method custom names in polylang strings translations table
        // called only after Wordpress is loaded
        add_action('wp_loaded', array($this, 'registerShippingStringsForTranslation'));

        // Shipping method in the Cart and Checkout pages
        add_filter('woocommerce_shipping_rate_label', array($this, 'translateShippingLabel'), 10, 1);

        // Shipping method in My Account page, Order Emails and Paypal requests
        add_filter('woocommerce_order_shipping_method', array($this, 'translateOrderShippingMethod'), 10, 2);
    }

    /**
     * Disable Settings.
     *
     * @return false if the current post type is not "product"
     */
    public function disableSettings()
    {
        $currentScreen = get_current_screen();
        if ($currentScreen->id !== 'settings_page_hyyan-wpi') {
            return false;
        }

        add_action('admin_print_scripts', array($this, 'disableShippingClassFeature'), 100);
    }

    /**
     * Add the disable Shipping Class translation feature script.
     *
     * The script will disable enabling the Shipping Class translation feature
     */
    public function disableShippingClassFeature()
    {
        $jsID = 'shipping-class-translation-disabled';
        $code = '$( "#wpuf-wpi-features\\\[shipping-class\\\]" ).prop( "disabled", true );';

        // To use any of the meta-characters ( such as !"#$%&'()*+,./:;<=>?@[]^`{|}~ )
        // as a literal part of a name, it must be escaped with with two backslashes: \\.
        // Because jsScriptWrapper() uses sprintf() it will treat one backslash as escape
        // character, so we need to add a 3rd (crazy!) backslashes.

        Utilities::jsScriptWrapper($jsID, $code);
    }

    /**
     * Helper function - Gets the shipping methods enabled in the shop.
     *
     * @return array $active_methods The id and respective plugin id of all active methods
     */
    private function getActiveShippingMethods()
    {
        $active_methods = array();

        // Format:  $shipping_methods[method_id] => shipping_method_object
        // where methods_id is e.g. flat_rate, free_shiping, local_pickup, etc
        $shipping_methods = $this->getZonesShippingMethods();

        foreach ($shipping_methods as $id => $shipping_method) {
            if (isset($shipping_method->enabled) && 'yes' === $shipping_method->enabled) {
                $active_methods[$id] = $shipping_method->plugin_id;
            }
        }

        return $active_methods;
    }

    /**
     * Get the shipping methods for all shipping zones.
     *
     * Note: WooCommerce 2.6 intoduces the concept of Shipping Zones
     *
     * @return array (Array of) all shipping methods instances
     */
    public function getZonesShippingMethods()
    {
        $zones = array();

        // Rest of the World zone
        $zone                                              = new \WC_Shipping_Zone();
        $zones[$zone->get_id()]                            = $zone->get_data();
        $zones[$zone->get_id()]['formatted_zone_location'] = $zone->get_formatted_location();
        $zones[$zone->get_id()]['shipping_methods']        = $zone->get_shipping_methods();

        // Add user configured zones
        $zones = array_merge($zones, \WC_Shipping_Zones::get_zones());

        $shipping_methods = array();

        // Format:  $shipping_methods[zone_name_method_id] => shipping_method_object
        // where zone_name is e.g. domestic, europe, rest_of_the_world, and
        // methods_id is e.g. flat_rate, free_shiping, local_pickup, etc
        foreach ($zones as $zone) {
            foreach ($zone['shipping_methods'] as $instance_id => $shipping_method) {
                // Zone names are converted to all lower-case and spaces replaced with
                $shipping_methods[$shipping_method->id . '_' . $instance_id] = $shipping_method;
            }
        }

        return $shipping_methods;
    }

    /**
     * Register shipping method custom titles in Polylang's Strings translations table.
     */
    public function registerShippingStringsForTranslation()
    {
        if (function_exists('pll_register_string')) {
            $shipping_methods = $this->getActiveShippingMethods();

            foreach ($shipping_methods as $method_id => $plugin_id) {
                $setting = get_option($plugin_id . $method_id . '_settings');

                if ($setting && isset($setting['title'])) {
                    pll_register_string($plugin_id . $method_id . '_shipping_method', $setting['title'], __('Woocommerce Shipping Methods', 'woo-poly-integration'));
                }
            }
        }
    }

    /**
     * Translate shipping label in the Cart and Checkout pages.
     *
     * @param string $label Shipping method label
     *
     * @return string Translated label
     */
    public function translateShippingLabel($label)
    {
        return function_exists('pll__') ? pll__($label) : __($label, 'woocommerce');
    }

    /**
     * Translate shipping method title in My Account page, Order Emails and Paypal requests.
     *
     * @param string   $implode  Comma separated string of shipping methods used in order
     * @param WC_Order $instance Order instance
     *
     * @return string Comma separated string of translated shipping methods' titles
     */
    public function translateOrderShippingMethod($implode, $instance)
    {

        // Convert the imploded array again to an array that is easy to manipulate
        $shipping_methods = explode(', ', $implode);

        // Array with translated shipping methods
        $translated = array();

        foreach ($shipping_methods as $shipping) {
            if (function_exists('pll__')) {
                $translated[] = pll__($shipping);
            } else {
                $translated[] = __($shipping, 'woocommerce');
            }
        }

        // Implode array to string again
        $translated_implode = implode(', ', $translated);

        return $translated_implode;
    }
}
