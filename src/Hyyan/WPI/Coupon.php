<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;

/**
 * Coupon.
 *
 * Handle coupon with products translations
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Coupon
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        if ('on' === Settings::getOption('coupons', Features::getID(), 'on')) {
            add_action('woocommerce_coupon_loaded', array($this, 'couponLoaded'));
        }
    }

    /**
     * Extend the coupon to include porducts translations.
     *
     * @param \WC_Coupon $coupon
     *
     * @return \WC_Coupon
     */
    public function couponLoaded(\WC_Coupon $coupon)
    {
        $productIDS                  = array();
        $excludeProductIDS           = array();
        $productCategoriesIDS        = array();
        $excludeProductCategoriesIDS = array();

        foreach ($coupon->get_product_ids() as $id) {
            foreach ($this->getProductPostTranslationIDS($id) as $_id) {
                $productIDS[] = $_id;
            }
        }
        foreach ($coupon->get_excluded_product_ids() as $id) {
            foreach ($this->getProductPostTranslationIDS($id) as $_id) {
                $excludeProductIDS[] = $_id;
            }
        }

        foreach ($coupon->get_product_categories() as $id) {
            foreach ($this->getProductTermTranslationIDS($id) as $_id) {
                $productCategoriesIDS[] = $_id;
            }
        }

        foreach ($coupon->get_excluded_product_categories() as $id) {
            foreach ($this->getProductTermTranslationIDS($id) as $_id) {
                $excludeProductCategoriesIDS[] = $_id;
            }
        }

        $coupon->set_product_ids($productIDS);
        $coupon->set_excluded_product_ids($excludeProductIDS);
        $coupon->set_product_categories($productCategoriesIDS);
        $coupon->set_excluded_product_categories($excludeProductCategoriesIDS);

        return $coupon;
    }

    /**
     * Extend the coupon to include porducts translations.
     *
     * @param \WC_Coupon $coupon
     *
     * @return \WC_Coupon
     */
    public function couponLoadedOld(\WC_Coupon $coupon)
    {
        $productIDS                  = array();
        $excludeProductIDS           = array();
        $productCategoriesIDS        = array();
        $excludeProductCategoriesIDS = array();
        foreach ($coupon->product_ids as $id) {
            foreach ($this->getProductPostTranslationIDS($id) as $_id) {
                $productIDS[] = $_id;
            }
        }
        foreach ($coupon->exclude_product_ids as $id) {
            foreach ($this->getProductPostTranslationIDS($id) as $_id) {
                $excludeProductIDS[] = $_id;
            }
        }
        foreach ($coupon->product_categories as $id) {
            foreach ($this->getProductTermTranslationIDS($id) as $_id) {
                $productCategoriesIDS[] = $_id;
            }
        }
        foreach ($coupon->exclude_product_categories as $id) {
            foreach ($this->getProductTermTranslationIDS($id) as $_id) {
                $excludeProductCategoriesIDS[] = $_id;
            }
        }
        $coupon->product_ids                = $productIDS;
        $coupon->exclude_product_ids        = $excludeProductIDS;
        $coupon->product_categories         = $productCategoriesIDS;
        $coupon->exclude_product_categories = $excludeProductCategoriesIDS;
        return $coupon;
    }

    /**
     * Get array of product translations IDS.
     *
     * @param int $ID the product ID
     *
     * @return array array contains all translation IDS for the given product
     */
    protected function getProductPostTranslationIDS($ID)
    {
        $result  = array($ID);
        $product = wc_get_product($ID);

        if ($product && $product->get_type() === 'variation') {
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
     * Get array of term translations IDS.
     *
     * @param int $ID the term ID
     *
     * @return array array contains all translation IDS for the given term
     */
    protected function getProductTermTranslationIDS($ID)
    {
        $IDS = Utilities::getTermTranslationsArrayByID($ID);

        return $IDS ? $IDS : array($ID);
    }

}
