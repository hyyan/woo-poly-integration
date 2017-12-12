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
            
            add_action('wp_loaded', array($this, 'adminRegisterCouponStrings'));
            
            //apply label filter with higher priority than woocommerce-auto-added-coupons
            add_filter('woocommerce_cart_totals_coupon_label',
                array($this, 'translateLabel'), 20, 2);
            add_filter('woocommerce_coupon_get_description',
                array($this, 'translateDescription'), 10, 2);
            
            /* additional fields for WooCommerce Extended Coupon Features */
            add_filter('woocommerce_coupon_get__wjecf_enqueue_message',
                array($this, 'translateMessage'), 10, 2);
            add_filter('woocommerce_coupon_get__wjecf_select_free_product_message',
                array($this, 'translateMessage'), 10, 2);
            add_filter('woocommerce_coupon_get__wjecf_free_product_ids',
                array($this, 'getFreeProductsInLanguage'), 10, 2);
        }
    }

    /**
     * filter product ids
     *
     * @param string     $product_ids list
     * @param WC_Coupon  $coupon current coupon
     *
     * @return array filtered result
     */
    public function getFreeProductsInLanguage($productIds, $coupon)
    {
        if (is_admin()) {
            return $productIds;
        }
        $productLang = pll_current_language();
        $productIds = explode(',', $productIds);
        $mappedIds = array();
        foreach ($productIds as $productId) {
            $mappedIds[] = Utilities::get_translated_variation($productId, $productLang);
        }
        return $mappedIds;
    }
    
    /**
     * translate coupon code.
     *
     * @param string      $value
     * @param \WC_Coupon $coupon
     *
     * @return string
     */
    public function translateLabel($value, $coupon)
    {
        $this->registerCouponStringsForTranslation();
        return sprintf(esc_html__('Coupon: %s', 'woocommerce'),
            pll__(\get_post($coupon->get_id())->post_title));
    }
    /**
     * translate coupon description.
     *
     * @param string      $value
     * @param \WC_Coupon $coupon
     *
     * @return string
     */
    public function translateDescription($value, $coupon)
    {
        $this->registerCouponStringsForTranslation();
        return pll__($value);
    }
    /**
     * translate coupon message.
     *
     * @param string      $value
     * @param \WC_Coupon $coupon
     *
     * @return string
     */
    public function translateMessage($value, $coupon)
    {
        $this->registerCouponStringsForTranslation();
        return pll__($value);
    }
    
    public function adminRegisterCouponStrings()
    {
        if (is_admin() && (!is_ajax())) {
            $this->registerCouponStringsForTranslation();
        }
    }
    /**
     * Register coupon titles adn descriptions in Polylang's Strings translations table.
     */
    public function registerCouponStringsForTranslation()
    {
        static $coupons_loaded;
        if (! $coupons_loaded) {
            if (function_exists('pll_register_string')) {
                $coupons = $this->getCoupons();
            
                foreach ($coupons as $coupon) {
                    //$code = wc_format_coupon_code($coupon->post_title);
                    pll_register_string($coupon->post_name, $coupon->post_title,
                        __('Woocommerce Coupon Names', 'woo-poly-integration'));
                    pll_register_string($coupon->post_name . '_description', $coupon->post_excerpt,
                        __('Woocommerce Coupon Names', 'woo-poly-integration'), true);
                
                    $coupon_message = get_post_meta($coupon->ID, '_wjecf_enqueue_message', true);
                    if ($coupon_message) {
                        pll_register_string($coupon->post_name . '_message', $coupon_message,
                        __('Woocommerce Coupon Names', 'woo-poly-integration'), true);
                    }
                    $freeproduct_message = get_post_meta($coupon->ID, '_wjecf_select_free_product_message', true);
                    if ($freeproduct_message) {
                        pll_register_string($coupon->post_name . '_freeproductmessage', $coupon_message,
                        __('Woocommerce Coupon Names', 'woo-poly-integration'), true);
                    }
                }
            }
            $coupons_loaded = true;
        }
    }
    
    /**
    * Helper function - Gets the coupons enabled in the shop.
    *
    * @return array $coupons Coupons settings including post_type, post_excerpt and post_title
    */
    private function getCoupons()
    {
        global $woocommerce;
        
        $args = array(
            'posts_per_page'   => -1,
            'orderby'          => 'title',
            'order'            => 'asc',
            'post_type'        => 'shop_coupon',
            'post_status'      => 'publish',
        );
    
        $coupons = get_posts($args);
        return $coupons;
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
