<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

/**
 * Product Stock
 *
 * Handle stock sync
 *
 * @author Hyyan
 */
class Stock
{

    /**
     * Construct object
     */
    public function __construct()
    {
        // sync stock
        add_action(
                'woocommerce_reduce_order_stock'
                , array($this, 'syncStock')
        );
        add_filter(
                'woocommerce_restore_order_stock_quantity'
                , array($this, 'restoreStockQuantity')
                , 10
                , 2
        );
    }

    /**
     * Sync stock for product and its translation
     *
     * @param \WC_Order $order
     *
     * @return boolean false if sync failed , true otherwise
     */
    public function syncStock($order)
    {
        // get array of defined langs
        $langs = pll_languages_list();

        // get array of ordered products
        $items = $order->get_items();

        foreach ($items as $item) {

            $productId = $item['product_id'];
            $productObject = wc_get_product($productId);

            // product not found
            if (!$productObject) {
                return false;
            }

            // product does not manage the stock
            if (!$productObject->managing_stock()) {
                return false;
            }

            $productLang = pll_get_post_language($productId);

            // product default lang can not be found
            if (!$productLang) {
                return false;
            }

            foreach ($langs as $name) {

                // skip the current product lang
                if ($productLang == $name) {
                    continue;
                }

                $translationID = pll_get_post($productId, $name);

                if ($translationID && ($transltedProduct = wc_get_product($translationID))) {
                    $transltedProduct->reduce_stock($item['qty']);
                }
            }

        }

        return true;
    }

    /**
     * Restore order stock quantity
     *
     * @param integer $change the stock change
     * @param integer $id     item id
     *
     * @return integer stock change
     */
    public function restoreStockQuantity($change, $id)
    {

        $orderId = absint($_POST['order_id']);
        $order = new \WC_Order($orderId);
        $items = $order->get_items();
        $product = $order->get_product_from_item($items[$id]);

        // get array of defined langs
        $langs = pll_languages_list();

        foreach ($items as $item) {

            $productId = $item['product_id'];
            $productObject = wc_get_product($productId);

            // product not found
            if (!$productObject) {
                return $change;
            }

            $productLang = pll_get_post_language($productId);

            // product default lang can not be found
            if (!$productLang) {
                return $change;
            }

            foreach ($langs as $name) {

                // skip the current product lang
                if ($productLang == $name) {
                    continue;
                }

                $translationID = pll_get_post($productId, $name);

                if ($translationID && ($transltedProduct = wc_get_product($translationID))) {
                    $transltedProduct->increase_stock($change);
                }
            }
        }

        return $change;
    }

}
