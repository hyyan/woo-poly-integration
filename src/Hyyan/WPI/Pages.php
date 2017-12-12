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
 * Pages.
 *
 * Handle page translations
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Pages
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        $method = array($this, 'getPostTranslationID');
        $pages = apply_filters(HooksInterface::PAGES_LIST, array(
            'shop',
            'cart',
            'checkout',
            'terms',
            'myaccount',
        ));

        foreach ($pages as $page) {
            add_filter(sprintf('woocommerce_get_%s_page_id', $page), $method);
            add_filter(sprintf('option_woocommerce_%s_page_id', $page), $method);
        }

        /* To generate the correct url for shop page */
        add_filter(
                'pll_get_archive_url', array($this, 'translateShopUrl'), 10, 2
        );

        if (!is_admin()) {

            /* To get product from current language in the shop page */
            add_filter('parse_request', array($this, 'correctShopPage'));
        }

        add_filter(
                'woocommerce_shortcode_products_query', array($this, 'addShortcodeLanguageFilter'), 10, 2
        );
    }

    /**
     * Get the id of translated post.
     *
     * @param int $id the post to get translation id for
     *
     * @return int
     */
    public function getPostTranslationID($id)
    {
        if (!function_exists('pll_get_post')) {
            return $id;
        }

        $translatedID = pll_get_post($id);

        if ($translatedID) {
            return $translatedID;
        }

        return $id;
    }

    /**
     * Correct the shop page to display products from currrent language only.
     *
     * @param \WP $wp wordpress instance
     *
     * @return bool false if the current language is the same as default
     *              language or if the "pagename" var is empty
     */
    public function correctShopPage(\WP $wp)
    {
        global $polylang;

        $shopID = wc_get_page_id('shop');
        $shopOnFront = ('page' === get_option('show_on_front')) && in_array(
                        get_option('page_on_front'), PLL()->model->post->get_translations(
                                $shopID
        ));

        $vars = array('pagename', 'page', 'name');
        foreach ($vars as $var) {
            if (isset($wp->query_vars[$var])) {
                $shopOnFront = false;
                break;
            }
        }
        if (!$shopOnFront) {
            if (!empty($wp->query_vars['pagename'])) {
                $shopPage = get_post($shopID);

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
    }

    /**
     * Translate the shop page name in the given shop url.
     *
     * @param string $url      complete url
     * @param string $language the current language
     *
     * @return string translated url
     */
    public function translateShopUrl($url, $language)
    {
        $result = $url;

        if (!is_post_type_archive('product')) {
            return $result;
        }

        $shopPageID = get_option('woocommerce_shop_page_id');
        $shopPage = get_post($shopPageID);

        if ($shopPage) {
            $shopPageTranslatedID = pll_get_post($shopPageID, $language);
            $shopPageTranslation = get_post($shopPageTranslatedID);

            if ($shopPageTranslation) {
                $result = str_replace(
                        $shopPage->post_name, $shopPageTranslation->post_name, $url
                );
            }
        }

        return $result;
    }

    /**
     * Add Shortcode Language Filter
     *
     * Fix shortcodes to include language filter.
     *
     * @param array $query_args
     * @param array $atts
     * @param string $loop_name  --  not provided by some shortcodes
     *
     * @return string modified form
     */
    public function addShortcodeLanguageFilter($query_args, $atts)
    {
        if (strlen($atts['ids'])) {
            $ids = explode(',', $atts['ids']);
            $transIds = array();
            foreach ($ids as $id) {
                array_push($transIds, pll_get_post($id));
            }

            $atts['ids'] = implode($transIds, ',');
            $query_args['post__in'] = $transIds;
        } else {
            $query_args['lang'] = isset($query_args['lang']) ?
                    $query_args['lang'] : pll_current_language();
        }
        
        return $query_args;
    }
}
