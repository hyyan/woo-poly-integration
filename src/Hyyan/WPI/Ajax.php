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
 * Ajax.
 *
 * Handle Ajax
 *
 * @author Marian Kadanka <marian.kadanka@gmail.com>
 */
class Ajax
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter('woocommerce_ajax_get_endpoint', array($this, 'filter_woocommerce_ajax_get_endpoint'), 10, 2);
    }

    /**
     * Filter woocommerce_ajax_get_endpoint URL - replace the path part 
     * with the correct relative home URL according to the current language 
     * and append the query string
     *
     * @param string $url WC AJAX endpoint URL to filter
     * @param string $request
     *
     * @return string filtered WC AJAX endpoint URL
     */
    public function filter_woocommerce_ajax_get_endpoint($url, $request)
    {
        global $polylang;
        $lang = ( $polylang->curlang ) ? $polylang->curlang : $polylang->pref_lang;
        return parse_url($polylang->filters_links->links->get_home_url($lang), PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);
    }
}
