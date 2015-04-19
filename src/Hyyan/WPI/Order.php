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
 * Order
 *
 * Handle order language
 *
 * @author Hyyan
 */
class Order
{

    /**
     * Construct object
     */
    public function __construct()
    {

        /* Manage order translation */
        add_filter(
                'pll_get_post_types'
                , array($this, 'manageOrderTranslation')
        );

        // to save the order language with every checkout
        add_action(
                'woocommerce_checkout_update_order_meta'
                , array($this, 'saveOrderLanguage')
        );

        /* Translate products in order details */
        add_filter(
                'woocommerce_order_item_product'
                , array($this, 'translateProductsInOrdersDetails')
                , 10, 3
        );
        add_filter(
                'woocommerce_order_item_name'
                , array($this, 'translateProductNameInOrdersDetails')
                , 10, 2
        );
    }

    /**
     * Notifty polylang about order custom post
     *
     * @param array $types array of custom post names managed by polylang
     *
     * @return array
     */
    public function manageOrderTranslation(array $types)
    {
        $options = get_option('polylang');
        $postTypes = $options['post_types'];
        if (!in_array('shop_order', $postTypes)) {
            $options['post_types'][] = 'shop_order';
            update_option('polylang', $options);
        }

        $types [] = 'shop_order';

        return $types;
    }

    /**
     * Save the order language with every checkout
     *
     * @param integer $order the order object
     */
    public function saveOrderLanguage($order)
    {
        $current = pll_current_language();
        if ($current) {
            pll_set_post_language($order, $current);
        }
    }

    /**
     * Translate products in order details pages
     *
     * @param \WC_Product $product
     *
     * @return \WC_Product
     */
    public function translateProductsInOrdersDetails($product)
    {
        return Utilities::getProductTranslationByObject($product);
    }

    /**
     * Translate the prodcut name in order details page
     *
     * @param string $name prodcut name
     * @param array  $item order item
     *
     * @return string prodcut name
     *
     * @todo should I remove this filter and let handle the translation in the
     *       theme file?
     */
    public function translateProductNameInOrdersDetails($name, $item)
    {
        $id = $item['item_meta']['_product_id'][0];
        $product = Utilities::getProductTranslationByID($id);
        if (!$product->is_visible()) {
            return $product->post->post_title;
        } else {
            return sprintf('<a href="%s">%s</a>', get_permalink($product->id), $product->post->post_title);
        }
    }

    /**
     * Get the order language
     *
     * @param integer $ID order ID
     *
     * @return string|false language in success , false otherwise
     */
    public static function getOrderLangauge($ID)
    {
        pll_get_post_language($ID);
    }

}
