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
 * Coupon
 *
 * Handle coupon with products translations
 *
 * @author Hyyan
 */
class Coupon
{

    /**
     * Construct object
     */
    public function __construct()
    {
        if ('on' === Settings::getOption('coupons', Features::getID(), 'on')) {
            add_action('woocommerce_coupon_loaded', array($this, 'couponLoaded'));
        }
    }

    /**
     * Extend the coupon to include porducts translations
     *
     * @param \WC_Coupon $coupon
     *
     * @return \WC_Coupon
     */
    public function couponLoaded(\WC_Coupon $coupon)
    {

        $productIDS = array();
        $excludeProductIDS = array();
        $productCategoriesIDS = array();
        $excludeProductCategoriesIDS = array();

        foreach ($coupon->product_ids as $id) {
            $productIDS = array_merge(
                    $productIDS
                    , $this->getProductPostTranslationIDS($id)
            );
        }
        foreach ($coupon->exclude_product_ids as $id) {
            $excludeProductIDS = array_merge(
                    $excludeProductIDS
                    , $this->getProductPostTranslationIDS($id)
            );
        }

        foreach ($coupon->product_categories as $id) {
            $productCategoriesIDS = array_merge(
                    $productCategoriesIDS
                    , $this->getProductTermTranslationIDS($id)
            );
        }

        foreach ($coupon->exclude_product_categories as $id) {
            $excludeProductCategoriesIDS = array_merge(
                    $excludeProductCategoriesIDS
                    , $this->getProductTermTranslationIDS($id)
            );
        }

        $coupon->product_ids = $productIDS;
        $coupon->exclude_product_ids = $excludeProductIDS;
        $coupon->product_categories = $productCategoriesIDS;
        $coupon->exclude_product_categories = $excludeProductCategoriesIDS;

        return $coupon;
    }

    /**
     * Get array of product translations IDS
     *
     * @param integer $ID the product ID
     *
     * @return array array contains all translation IDS for the given product
     */
    protected function getProductPostTranslationIDS($ID)
    {
        $result = array($ID);
        $product = wc_get_product($ID);

        if ($product && $product->product_type === 'variation') {
            $IDS = Product\Variation::getRelatedVariation($ID, true);
            if (is_array($IDS)) {
                $result = array_merge($result, $IDS);
            }
        } else {
            $IDS = Utilities::getProductTranslationsArrayByID($ID);
            if (is_array($IDS)) {
                $result = array_merge($result, $IDS);
            }
        }

        return $IDS ? $IDS : array($ID);
    }

    /**
     * Get array of term translations IDS
     *
     * @param integer $ID the term ID
     *
     * @return array array contains all translation IDS for the given term
     */
    protected function getProductTermTranslationIDS($ID)
    {

        $IDS = Utilities::getTermTranslationsArrayByID($ID);

        return $IDS ? $IDS : array($ID);
    }

}
