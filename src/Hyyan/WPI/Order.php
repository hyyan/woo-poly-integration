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
 * Order
 *
 * Handle order language
 *
 * @author Hyyan
 */
class Order
{

    /**
     * Constrcut object
     */
    public function __construct()
    {
        // to save the order language with every checkout
        add_action(
                'woocommerce_checkout_update_order_meta'
                , array($this, 'saveOrderLanguage')
        );

        // show order language for admin
        add_action(
                'woocommerce_admin_order_data_after_order_details'
                , array($this, 'showOrderLanguageInOrderDetails')
        );

        // translate products in order details
        add_filter(
                'woocommerce_order_item_product'
                , array($this, 'translateProductsInOrdersDetails')
                , 10, 3
        );
        add_filter(
                'woocommerce_order_item_name'
                , array($this, 'translateProductNameInOrdersDetails')
                , 10, 2
        );
    }

    /**
     * Save the order language with every checkout
     *
     * @param \WC_Order $order the order object
     */
    public function saveOrderLanguage($order)
    {
        $current = pll_current_language();
        if ($current) {
            add_post_meta($order, 'lang', pll_current_language(), true);
        }
    }

    /**
     * Show the order langauge in order details meta box for admins
     *
     * This option will allow the admin to add note according to the order
     * language
     */
    public function showOrderLanguageInOrderDetails()
    {

        $langEntity = static::getOrderLangauge(get_the_ID());

        if ($langEntity) {
            printf(
                    '<div class="update-nag" style="position:relative !important">'
                    . '     <p class="form-field form-field-wide">'
                    . '     <strong><label for="order_lang">%s</label></strong>'
                    . '     <input type="text" disabled placeholder="%s" '
                    . '            id="order_lang" name="order_lang"/>'
                    . '     </p>'
                    . '</div>'
                    , __('Order/Checkout Language : ', 'woo-poly-integration')
                    , $langEntity->name
            );
        }
    }

    /**
     * Translate products in order details pages
     *
     * @param \WC_Product $product
     * @param array       $items   order items
     *
     * @return \WC_Product
     */
    public function translateProductsInOrdersDetails($product, $items)
    {
        return Utilities::getProductTranslationByObject($product);
    }

    /**
     * Translate the prodcut name in order details page
     *
     * @param string $name prodcut name
     * @param array  $item order item
     *
     * @return string prodcut name
     *
     * @todo should I remove this filter and let handle the translation in the
     *       theme file?
     */
    public function translateProductNameInOrdersDetails($name, $item)
    {
        $id = $item['item_meta']['_product_id'][0];
        $product = Utilities::getProductTranslationByID($id);
        if (!$product->is_visible()) {
            return $product->post->post_title;
        } else {
            return sprintf('<a href="%s">%s</a>', get_permalink($product->id), $product->post->post_title);
        }
    }

    /**
     * Get the order language
     *
     * @param integer $id order id
     *
     * @return \PLL_Language|false lang entity in success , false otherwise
     */
    public static function getOrderLangauge($id)
    {
        $orderLangArray = get_post_meta($id, 'lang');
        if ($orderLangArray) {
            return Utilities::getLanguageEntity($orderLangArray[0]);
        }

        return false;
    }

}
