<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Breadcrumb.
 *
 * Handle Breadcrumb translation
 *
 * @author Antonio de Carvalho <decarvalhoaa@gmail.com>
 */
class Breadcrumb
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter('woocommerce_breadcrumb_home_url', array($this, 'translateBreadrumbHomeUrl'), 10, 1);
        add_filter('woocommerce_get_breadcrumb', array($this, 'wpi_shopbreadcrumb'), 10, 2);
    }

    /*
     * Issue #542 Shop page omitted from breadcrumb in secondary languages
	 * @param array         $crumbs     already populated breadcrumb array
     * @param WC_Breadcrumb $breadcrumb WC_Breadcrumb object
	 * @return array                    return modified array of breadcrumbs
     */
    public function wpi_shopbreadcrumb($crumbs, $breadcrumb){
		$permalinks   = wc_get_permalink_structure();
        $curLang = pll_current_language();
        $baseLang = pll_default_language();
        if ($curLang != $baseLang){
            //get shop page in current language
            $shop_page_translated_id = wc_get_page_id( 'shop' );
            $shop_page_translated  = get_post( $shop_page_translated_id );
            //also get shop page in base language for the woocommerce test
            $shop_page_id = pll_get_post($shop_page_translated_id, $baseLang);
            $shop_page = get_post( $shop_page_id );

            //perform same check whether shop page should be added as prepend_shop_page, but on the base language page
            if ( $shop_page_id && $shop_page && isset( $permalinks['product_base'] ) && strstr( $permalinks['product_base'], '/' . $shop_page->post_name ) && intval( get_option( 'page_on_front' ) ) !== $shop_page_id ) {
                //add breadcrumb for translated shop page as second item in breadcrumb array
                $homecrumb = array_shift($crumbs);
                array_unshift($crumbs,
                            array(
                                wp_strip_all_tags( get_the_title( $shop_page_translated ) ),
                                get_permalink( $shop_page_translated )
                                )                        
                            );
                array_unshift($crumbs, $homecrumb);
            }            
        }
        return $crumbs;
    }

    
    /**
     * Translate WooCommerce Breadcrumbs home url.
     *
     * @return string translated home url
     */
    public function translateBreadrumbHomeUrl($home)
    {
        if (function_exists('pll_home_url')) {
            return pll_home_url();
        }
        
        return $home;
    }
}
