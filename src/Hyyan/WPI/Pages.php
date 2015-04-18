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
        $pages = array(
            'shop',
            'cart',
            'checkout',
            'terms',
            'myaccount',
        );

        foreach ($pages as $page) {
            add_filter(sprintf('woocommerce_get_%s_page_id', $page), $method);
            add_filter(sprintf('option_woocommerce_%s_page_id', $page), $method);
        }
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
