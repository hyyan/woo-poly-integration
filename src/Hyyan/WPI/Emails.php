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
 * Emails
 *
 * Handle woocommerce emails
 *
 * @author Hyyan
 */
class Emails
{

    /**
     * Construct object
     */
    public function __construct()
    {
        add_filter('plugin_locale', array($this, 'correctLocal'), 100);
    }

    /**
     * Correct the locale for orders emails , Othe emails must be handled
     * correctly out of the box
     *
     * @global type $polylang
     * @global type $woocommerce
     *
     * @param string $locale current locale
     *
     * @return string locale
     */
    public function correctLocal($locale)
    {

        global $polylang, $woocommerce;
        if (!$polylang || !$woocommerce) {
            return $locale;
        }

        if (!is_admin() || defined('DOING_AJAX')) {
            return $locale;
        }

        if ('GET' === filter_input(INPUT_SERVER, 'REQUEST_METHOD')) {
            return $locale;
        }

        $ID = false;
        $search = array('post', 'post_ID', 'pll_post_id', 'order_id');

        foreach ($search as $value) {
            if (isset($_REQUEST[$value])) {
                $ID = esc_attr($_REQUEST[$value]);
                break;
            }
        }

        if ((get_post_type($ID) !== 'shop_order')) {
            return $locale;
        }

        $orderLanguage = Order::getOrderLangauge($ID);

        if ($orderLanguage) {

            $entity = Utilities::getLanguageEntity($orderLanguage);

            if ($entity) {
                $polylang->curlang = $polylang->model->get_language(
                        $entity->locale
                );
                $GLOBALS['text_direction'] = $entity->is_rtl ? 'rtl' : 'ltr';
                $GLOBALS['wp_locale'] = new \WP_Locale();

                return $entity->locale;
            }
        }

        return $locale;
    }

}
