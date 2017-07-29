<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Utilities;
use Hyyan\WPI\Product\Variation;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\MetasList;

/**
 * Product Stock.
 *
 * Handle stock sync
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Stock
{
    const STOCK_REDUCE_ACTION = 'reduce';
    const STOCK_INCREASE_ACTION = 'increase';

    /**
     * Construct object.
     */
    public function __construct()
    {
        // sync stock
        add_action(
                'woocommerce_reduce_order_stock', array($this, 'reduceStock')
        );
        add_filter(
                'woocommerce_restore_order_stock_quantity', array($this, 'increaseStock')
        );
    }

    /**
     * Reduce stock for product and its translation.
     *
     * @param \WC_Order $order
     */
    public function reduceStock($order)
    {

        /* Get array of ordered products */
        $items = $order->get_items();

        /* Reduce stock */
        foreach ($items as $item) {
            $this->change($item, self::STOCK_REDUCE_ACTION);
        }
    }

    /**
     * Increase stock for product and its translation.
     *
     * @param int $change the stock change
     *
     * @return int stock change
     */
    public function increaseStock($change)
    {
        $orderId = absint($_POST['order_id']);
        $order = new \WC_Order($orderId);

        /* Get array of ordered products */
        $items = $order->get_items();

        /* Increase stock */
        foreach ($items as $item) {
            $item->change = $change;
            $this->change($item, self::STOCK_INCREASE_ACTION);
        }
    }

    /**
     * Change the product stock in the given order item.
     *
     * @param array  $item   the order data
     * @param string $action STOCK_REDUCE_ACTION | STOCK_INCREASE_ACTION
     */
    protected function change(\WC_Order_Item_Product $item, $action = self::STOCK_REDUCE_ACTION)
    {
        $productID = Utilities::get_order_item_productid($item);
        $productObject = wc_get_product($productID);
        //$productLang = pll_get_post_language($productID); //#184
        $orderLang = pll_get_post_language($item->get_order_id());
        $variationID = Utilities::get_order_item_variationid($item);

        /* Handle Products */
        if ($productObject && $orderLang) {

            /* Get the translations IDS */
            $translations = Utilities::getProductTranslationsArrayByObject(
                            $productObject
            );

            $method = ($action === self::STOCK_REDUCE_ACTION) ?
                'decrease' :
                'increase';
            $change = ($action === self::STOCK_REDUCE_ACTION) ?
                Utilities::get_order_item_quantity($item) :
                Utilities::get_order_item_change($item);


            $isManageStock = $productObject->managing_stock();
            $isVariation = $variationID && $variationID > 0;

            //in 3.0.8 at least, current lang item must not be removed from array if is variable
            if ($isManageStock && (!$isVariation)) {
                /* Remove the current product from translation array */
                unset($translations[$orderLang]);
            }

            /* Sync stock for all translation */
            foreach ($translations as $ID) {

                /* Only if product is managing stock
                 * including variation with stock managed at product level*/
                if ($isManageStock) {
                    if (($translation = wc_get_product($ID))) {
                        \wc_update_product_stock($translation, $change, $method);
                    }
                }

                $general = Settings::getOption(
                                'general', MetasList::getID(), array('total_sales')
                );
                if (in_array('total_sales', $general)) {
                    update_post_meta(
                            $ID, 'total_sales', get_post_meta($productID, 'total_sales', true)
                    );
                }
            }

            /* Handle variation stock UNLESS stock is managed on the parent
             * there is a function for this $variation->get_stock_managed_by_id() however in woo-poly-context
             * this returns the master language id of either the variation of the parent.
             */
            if (($isVariation) && !($isManageStock)) {
                //unfortunately pll functions can't be used as the
                //variations are not currently linked using pll_save_post_translations
                //still it might be more sensible to get master in base language, and synchronise from that
                //$variationMaster = (pll_get_post_language($variationID) == pll_default_language()) ?
                //    wc_get_product($variationID) : Utilities::getProductTranslationByID($variationID, pll_default_language());

                $variationMasterID = get_post_meta($variationID, Variation::DUPLICATE_KEY, true);
                $variationMaster = wc_get_product($variationMasterID);
                if ($variationMaster) {
                    $variationMasterManagerStock = $variationMaster->managing_stock();
                    if ($variationMasterManagerStock) {
                        //$posts = Utilities::getProductTranslationsArrayByObject($variationMaster);
                        $posts = Variation::getRelatedVariation($variationMasterID);
                        foreach ($posts as $post) {
                            $variation = wc_get_product($post);
                            if ($variation) {
                                //tested with orderlang, actually here it is the product language as currently variation item
                                //is added and handled in original language even if order switched language
                                if (pll_get_post_language($variation->get_parent_id()) != pll_get_post_language($productID)) {
                                    \wc_update_product_stock($variation, $change, $method);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
