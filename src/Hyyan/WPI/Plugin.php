<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Plugin
 *
 * @author Hyyan
 */
class Plugin
{

    /**
     * Construct the plugin
     */
    public function __construct()
    {
        add_action('init', array($this, 'activate'));

        /* Registered anyway */
        new Emails();
    }

    /**
     * Activate plugin
     *
     * The plugin will register its core if the requirements are full filled , otherwise
     * it will show an admin error message
     *
     * @return boolean false if plugin can not be activated
     */
    public function activate()
    {
        if (!static::canActivate()) {
            add_action('admin_notices', function () {
                printf('<div id="message" class="error"><p>%s</p></div>', __('Hyyan WooCommerce Polylang Integration Plugin can not function correctly , the plugin requires WooCommerce and Polylang plugins', 'woo-poly-integration'));
            });

            return false;
        }

        $this->registerCore();
    }

    /**
     * Check if the plugin can be activated
     *
     * @return boolean true if can be activated , false otherwise
     */
    public static function canActivate()
    {
        $requiredPlugins = array(
            'polylang/polylang.php',
            'woocommerce/woocommerce.php'
        );

        $plugins = apply_filters('active_plugins', get_option('active_plugins'));

        foreach ($requiredPlugins as $name) {
            if (!in_array($name, $plugins)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get current plugin version
     *
     * @return integer
     */
    public static function getVersion()
    {
        $data = get_plugin_data(Hyyan_WPI_DIR);

        return $data['Version'];
    }

    /**
     * Add plugin core classes
     */
    protected function registerCore()
    {
        new Cart();
        new Coupon();
        new Login();
        new Order();
        new Pages();
        new Product();
        new Media();
        new Reports();
    }

}
