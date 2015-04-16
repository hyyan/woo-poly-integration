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
 * Pages
 *
 * Handle page translations
 *
 * @author Hyyan
 */
class Pages
{

    /**
     * Construct object
     */
    public function __construct()
    {

        $method = array($this, 'getPostTranslationID');

        // shop page
        add_filter('woocommerce_get_shop_page_id', $method);

        //cart page
        add_filter('woocommerce_get_cart_page_id', $method);

        // checkout page
        add_filter('woocommerce_get_checkout_page_id', $method);

        // terms page (Categories,Tags)
        add_filter('woocommerce_get_terms_page_id', $method);

        // myaccount page
        add_filter('woocommerce_get_myaccount_page_id', $method);
    }

    /**
     * Get the id of translated post
     *
     * @param integer $id the post to get translation id for
     *
     * @return integer
     */
    public function getPostTranslationID($id)
    {
        $translatedID = pll_get_post($id);

        if ($translatedID) {
            return $translatedID;
        }

        return $id;
    }

}
