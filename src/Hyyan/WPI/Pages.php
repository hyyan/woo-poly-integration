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

        if (!is_admin()) {
            add_filter('parse_request', array($this, 'correctShopPage'));
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

    /**
     * Correct the shop page to display products from currrent language only
     *
     * @param  \WP     $wp wordpress instance
     * @return boolean false if the current language is the same as default
     *                    language or if the "pagename" var is empty
     */
    public function correctShopPage(\WP $wp)
    {
        if (pll_default_language() === pll_current_language()) {
            return false;
        }

        if (empty($wp->query_vars['pagename'])) {
            return false;
        }

        $shopPage = get_post(wc_get_page_id('shop'));

        /* Explode by / for children page */
        $page = explode('/', $wp->query_vars['pagename']);

        if (
                isset($shopPage->post_name) &&
                $shopPage->post_name == $page[count($page) - 1]
        ) {
            unset($wp->query_vars['page']);
            unset($wp->query_vars['pagename']);
            $wp->query_vars['post_type'] = 'product';
        }
    }
}
