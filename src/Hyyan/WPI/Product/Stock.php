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
				if (Utilities::woocommerceVersionCheck('3.0')) 
				{
        foreach ($items as $item) {
            $this->change($item, self::STOCK_REDUCE_ACTION);
        }
    }
				else
				{
					foreach ($items as $item) {
							$this->change_old($item, self::STOCK_REDUCE_ACTION);
					}
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
				if (Utilities::woocommerceVersionCheck('3.0')) 
				{
        foreach ($items as $item) {
            $item->change = $change;
            $this->change($item, self::STOCK_INCREASE_ACTION);
        }
				}
				else
				{
					foreach ($items as $item) {
							$item->change = $change;
							$this->change_old($item, self::STOCK_INCREASE_ACTION);
					}
				}		
        return $change;
    }

    /**
     * Change the product stock in the given order item.
     *
     * @param array  $item   the order data
     * @param string $action STOCK_REDUCE_ACTION | STOCK_INCREASE_ACTION
     */
protected function change( \WC_Order_Item_Product $item, $action = self::STOCK_REDUCE_ACTION )
    {
				$productID = Utilities::get_order_item_productid($item);
        $productObject = wc_get_product($productID);
        $productLang = pll_get_post_language($productID);

        $variationID = Utilities::get_order_item_variationid($item);

        /* Handle Products */
        if ($productObject && $productLang) {

            /* Get the translations IDS */
            $translations = Utilities::getProductTranslationsArrayByObject(
                            $productObject
            );

            /* Remove the current product from translation array */
            unset($translations[$productLang]);

            $isManageStock = $productObject->managing_stock();
            $isVariation = $variationID && $variationID > 0;
            $method = ($action === self::STOCK_REDUCE_ACTION) ?
                    'decrease' :
                    'increase';
            $change = ($action === self::STOCK_REDUCE_ACTION) ?
              Utilities::get_order_item_quantity($item) :
          		Utilities::get_order_item_change($item);

            /* Sync stock for all translation */
            foreach ($translations as $ID) {

                /* Only if product is managing stock */
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
                $posts = Variation::getRelatedVariation($variationID);
                foreach ($posts as $post) {
                    if ($post->ID == $variationID) {
                        continue;
                    }
                    $variation = wc_get_product($post);
                    if ($variation && $variation->managing_stock()) {
												\wc_update_product_stock($variation, $change, $method);
                    }
                }
            }
        }
    }
		
		/** OLD WOO < 2-6 version
     * Change the product stock in the given order item.
     *
     * @param array  $item   the order data
     * @param string $action STOCK_REDUCE_ACTION | STOCK_INCREASE_ACTION
     */
    protected function change_old(array $item, $action = self::STOCK_REDUCE_ACTION)
    {
        $productID = $item['product_id'];
        $productObject = wc_get_product($productID);
        $productLang = pll_get_post_language($productID);
        $variationID = $item['variation_id'];
        /* Handle Products */
        if ($productObject && $productLang) {
            /* Get the translations IDS */
            $translations = Utilities::getProductTranslationsArrayByObject(
                            $productObject
            );
            /* Remove the current product from translation array */
            unset($translations[$productLang]);
            $isManageStock = $productObject->managing_stock();
            $isVariation = $variationID && $variationID > 0;
            $method = ($action === self::STOCK_REDUCE_ACTION) ?
                    'reduce_stock' :
                    'increase_stock';
            $change = ($action === self::STOCK_REDUCE_ACTION) ?
                    $item['qty'] :
                    $item['change'];
            /* Sync stock for all translation */
            foreach ($translations as $ID) {
                /* Only if product is managing stock */
                if ($isManageStock) {
                    if (($translation = wc_get_product($ID))) {
                        $translation->$method($change);
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
            /* Handle variation stock UNLESS stock is managed on the parent */
            if (($isVariation) && !($isManageStock)) {
                $posts = Variation::getRelatedVariation($variationID);
                foreach ($posts as $post) {
                    if ($post->ID == $variationID) {
                        continue;
                    }
                    $variation = wc_get_product($post);
                    if ($variation && $variation->managing_stock()) {
                        $variation->$method($change);
                    }
                }
            }
        }
    }

}
