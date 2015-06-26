<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings,
    Hyyan\WPI\Admin\Features;

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
        if ('on' === Settings::getOption('emails', Features::getID(), 'on')) {
            add_filter('plugin_locale', array($this, 'correctLocal'), 100);
        }
    }

    /**
     * Correct the locale for orders emails , Othe emails must be handled
     * correctly out of the box
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
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

        $refer = isset($_GET['action']) &&
                esc_attr($_GET['action'] === 'woocommerce_mark_order_status');

        if ((!is_admin() && !isset($_REQUEST['ipn_track_id'])) || (defined('DOING_AJAX') && !$refer)) {
            return $locale;
        }

        if ('GET' === filter_input(INPUT_SERVER, 'REQUEST_METHOD') && !$refer) {
            return $locale;
        }

        $ID = false;

        if (!isset($_REQUEST['ipn_track_id'])) {
            $search = array('post', 'post_ID', 'pll_post_id', 'order_id');

            foreach ($search as $value) {
                if (isset($_REQUEST[$value])) {
                    $ID = esc_attr($_REQUEST[$value]);
                    break;
                }
            }
        } else {
            $ID = $this->getOrderIDFromIPNRequest();
        }

        if ((get_post_type($ID) !== 'shop_order') && !$refer) {
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
                if (class_exists('WP_Locale')) {
                    $GLOBALS['wp_locale'] = new \WP_Locale();
                }

                return $entity->locale;
            }
        }

        return $locale;
    }

    /**
     * Return the order id associated with the current IPN request
     *
     * @return int the order id if one was found or false
     */
     public function getOrderIDFromIPNRequest()
     {
         if (!empty($_REQUEST)) {

             $posted = wp_unslash($_REQUEST);

             if (empty($posted['custom'])) {
                return false;
            }

             $custom = maybe_unserialize($posted['custom']);

             if (!is_array($custom)) {
                return false;
             }

            list($order_id, $order_key) = $custom;

            return $order_id;
        }

        return false;
    }
}
