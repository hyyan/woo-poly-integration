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
 * Coupon
 *
 * Handle coupon with products translations
 *
 * @author Hyyan
 */
class Coupon
{

    /**
     * Constrcut object
     */
    public function __construct()
    {
        add_action('woocommerce_coupon_loaded', array($this, 'couponLoaded'));
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
     * Get array of product post translations IDS
     *
     * @param integer $id the product id
     *
     * @return array array contains all translation IDS for the given product post
     */
    protected function getProductPostTranslationIDS($id)
    {

        $result = array($id);
        $langs = pll_languages_list();

        foreach ($langs as $name) {

            $translationID = pll_get_post($id, $name);

            if ($translationID) {
                $result[] = $translationID;
            }
        }

        return $result;
    }

    /**
     * Get array of product term translations IDS
     *
     * @param integer $id the term id
     *
     * @return array array contains all translation IDS for the given product term
     */
    protected function getProductTermTranslationIDS($id)
    {

        $result = array($id);
        $langs = pll_languages_list();

        foreach ($langs as $name) {

            $translationID = pll_get_term($id, $name);

            if ($translationID) {
                $result[] = $translationID;
            }
        }

        return $result;
    }

}
