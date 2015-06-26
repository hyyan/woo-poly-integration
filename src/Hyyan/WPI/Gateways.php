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
 * Gateways
 *
 * Handle Payment Gateways
 *
 * @author Nicolas Joannès <nic@cobea.be>
 */
class Gateways
{

    /**
     * Construct object
     */
    public function __construct()
    {
        add_filter('woocommerce_paypal_args', array($this, 'setPaypalLocalCode'));

    }

    /**
     * Set the PayPal checkout locale code
     *
     * @param array $args the current paypal request args array
     *
     * @return void
     */
    public function setPaypalLocalCode($args)
    {
        $lang = pll_current_language('locale');
        $args['locale.x'] = $lang;

        return $args;

    }

}
