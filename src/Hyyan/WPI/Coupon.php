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
    static $enable_logging = false;
    static $enable_wjecf = false;

    /**
     * Construct object.
     */
    public function __construct()
    {
        //avoid excessive loading
        if ( defined( 'DOING_CRON') ) {return;} 
        if ('on' === Settings::getOption('coupons', Features::getID(), 'on')) {

            add_action('woocommerce_coupon_loaded', array($this, 'couponLoaded'));
            
            add_action('wp_loaded', array($this, 'adminRegisterCouponStrings'));
            
            //apply label filter with higher priority than woocommerce-auto-added-coupons
            add_filter('woocommerce_cart_totals_coupon_label',
                array($this, 'translateLabel'), 20, 2);
            add_filter('woocommerce_coupon_get_description',
                array($this, 'translateDescription'), 10, 2);
            
            /* additional fields for WooCommerce Extended Coupon Features */
            $enable_wjecf = function_exists('WJECF');
            if ($enable_wjecf){
                if (self::$enable_logging) {
                    error_log('woopoly enabled wjecf translation: ' . 
                        ' in request: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 
                        ' no $_SERVER[REQUEST_URI] available'));            
                }
                add_filter('woocommerce_coupon_get__wjecf_enqueue_message',
                    array($this, 'translateMessage'), 10, 2);
                add_filter('woocommerce_coupon_get__wjecf_select_free_product_message',
                    array($this, 'translateMessage'), 10, 2);
                add_filter('woocommerce_coupon_get__wjecf_free_product_ids',
                    array($this, 'getFreeProductsInLanguage'), 10, 2);
            }
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
    public function getFreeProductsInLanguage($productIds, \WC_Coupon $coupon)
    {
        if (is_admin()) {
            return $productIds;
        }
        $productLang = pll_current_language();
        if (self::$enable_logging) {
            error_log('woopoly getting translated ids for: ' . $productIds . ' for coupon ' . $coupon->get_code() . 
                ' in request: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 
                ' no $_SERVER[REQUEST_URI] available'));            
        }
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
    public function translateLabel($value, \WC_Coupon $coupon)
    {
        $this->registerCouponStringsForTranslation();
        $code = $coupon->get_code();
        if ($code){
            return sprintf(esc_html__('Coupon: %s', 'woocommerce'), pll__($code));
        } else {
            return $value;
        }
    }
    /**
     * translate coupon description.
     *
     * @param string      $value
     * @param \WC_Coupon $coupon
     *
     * @return string
     */
    public function translateDescription($value, \WC_Coupon $coupon)
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
        global $pagenow;
            if ( ($pagenow) && ( $pagenow == 'admin.php' ) && ($_GET[ 'page' ] == 'mlang_strings') ) {
                  $this->registerCouponStringsForTranslation();
            }
        }
	  }

    /**
     * Register coupon titles adn descriptions in Polylang's Strings translations table.
     */
    public function registerCouponStringsForTranslation()
    {
        static $coupons_loaded;
        static $doingload;      
        if ($coupons_loaded || $doingload){return;}
        if (! $coupons_loaded && ! $doingload) {
            $doingload = true;
            if (function_exists('pll_register_string')) {
                if (self::$enable_logging) {
                    error_log('woopoly registering coupons for translation: ' . 
                        ' in request: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 
                        ' no $_SERVER[REQUEST_URI] available'));            
                }
                $coupons = $this->getCoupons();
            
                foreach ($coupons as $coupon_postid) {
                    $coupon = new \WC_Coupon( $coupon_postid );

                    $coupon_code = $coupon->get_code();
                    $coupon_slug = sanitize_title_with_dashes($coupon_code);
                    pll_register_string($coupon_slug, $coupon_code,
                        __('WooCommerce Coupon Names', 'woo-poly-integration'));
                    pll_register_string($coupon_slug . '_description', $coupon->get_description(),
                        __('WooCommerce Coupon Names', 'woo-poly-integration'), true);

                    if (self::$enable_wjecf) {
                        
                        $coupon_message = $coupon->get_meta('_wjecf_enqueue_message', true);
                        if ($coupon_message) {
                            pll_register_string($coupon_slug . '_message', $coupon_message,
                            __('WooCommerce Coupon Names', 'woo-poly-integration'), true);
                        }
                        $freeproduct_message = $coupon->get_meta('_wjecf_select_free_product_message', true);
                        if ($freeproduct_message) {
                            pll_register_string($coupon_slug . '_freeproductmessage', $coupon_message,
                            __('WooCommerce Coupon Names', 'woo-poly-integration'), true);
                        }
                    }
                }
            }
            $coupons_loaded = true;
            $doingload = false;
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
        
        $tKey	 = 'coupons-ids';
        
        $coupon_ids = get_transient($tKey);
        if ($coupon_ids) {
            if (self::$enable_logging) {
                error_log('woopoly found coupons in transient: ' . 
                    ' in request: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 
                    ' no $_SERVER[REQUEST_URI] available'));            
            }
        } else {        
            if (self::$enable_logging) {
                error_log('woopoly loading coupons to transient: ' . 
                    ' in request: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 
                    ' no $_SERVER[REQUEST_URI] available'));            
            }
            $args = array(
                'posts_per_page'   => -1,
                'orderby'          => 'title',
                'order'            => 'asc',
                'post_type'        => 'shop_coupon',
                'post_status'      => 'publish',
                'fields'           => 'ids',                
            );
            $coupon_ids = get_posts($args);
            set_transient($tKey, $coupon_ids, 3600);
        }
        return $coupon_ids;
    }
    
    
    /**
     * Extend the coupon to include product translations.
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

        if (self::$enable_logging) {
            error_log('woopoly setting related for coupon : ' . $coupon->get_code() .
                ' include-products:' . implode(',', $productIDS) .
                ' exclude-products:' . implode(',', $excludeProductIDS) .
                ' include-categories:' . implode(',', $productCategoriesIDS) .
                ' exclude-categories:' . implode(',', $excludeProductCategoriesIDS) .
                ' in request: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 
                ' no $_SERVER[REQUEST_URI] available'));            
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
