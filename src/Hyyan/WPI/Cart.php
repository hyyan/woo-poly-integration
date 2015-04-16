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
 * Cart
 *
 * Handle cart translation
 *
 * @author Hyyan
 */
class Cart
{

    /**
     * Construct object
     */
    public function __construct()
    {
        // handle add to cart
        add_filter(
                'woocommerce_add_to_cart_product_id'
                , array($this, 'addToCart'), 10, 1
        );

        // handle the translation of displayed porducts in cart
        add_filter(
                'woocommerce_cart_item_product'
                , array($this, 'translateCartProducts')
                , 10
                , 2
        );

        // handle the update of cart widget when language is switched
        add_action('wp_enqueue_scripts'
                , array($this, 'replaceCartFragmentsScript')
                , 100
        );
    }

    /**
     * Add to cart
     *
     * The function will make sure that products won't be duplicated for each
     * language
     *
     * @param integer $ID the current product ID
     *
     * @return integer the final product ID
     */
    public function addToCart($ID)
    {

        $result = $ID;

        // get the product translations
        $IDS = Utilities::getProductTranslationsArrayByID($ID);

        // check if any of product's translation is already in cart
        foreach (WC()->cart->get_cart() as $values) {

            $product = $values['data'];

            if (in_array($product->id, $IDS)) {
                $result = $product->id;
                break;
            }
        }

        return $result;
    }

    /**
     * Translate displayed products in cart
     *
     * @param \WC_Product $cartItemData
     * @param array       $cartItem
     *
     * @return \WC_Product
     */
    public function translateCartProducts($cartItemData, $cartItem)
    {

        $translation = Utilities::getProductTranslationByID(
                        $cartItem['product_id']
        );

        return $translation ? $translation : $cartItemData;
    }

    /**
     * Replace woo fragments script
     *
     * To update cart widget when language is switched
     */
    public function replaceCartFragmentsScript()
    {
        /* remove the orginal wc-cart-fragments.js and register ours */
        wp_deregister_script('wc-cart-fragments');
        wp_enqueue_script(
                'wc-cart-fragments'
                , plugins_url('public/js/Cart.js', Hyyan_WPI_DIR)
                , array('jquery', 'jquery-cookie')
                , Plugin::getVersion()
                , true
        );
    }

}
