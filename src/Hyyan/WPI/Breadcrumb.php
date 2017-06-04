<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Breadcrumb.
 *
 * Handle Breadcrumb translation
 *
 * @author Antonio de Carvalho <decarvalhoaa@gmail.com>
 */
class Breadcrumb
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter('woocommerce_breadcrumb_home_url', array($this, 'translateBreadrumbHomeUrl'), 10, 1);
    }

    /**
     * Translate WooCommerce Breadcrumbs home url.
     *
     * @return string translated home url
     */
    public function translateBreadrumbHomeUrl($home)
    {
        if (function_exists('pll_home_url')) {
            return pll_home_url();
        }
        
        return $home;
    }
}
