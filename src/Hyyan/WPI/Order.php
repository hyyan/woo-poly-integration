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
 * Order.
 *
 * Handle order language
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Order
{
    /**
     * Construct object.
     */
    public function __construct()
    {

        /* Manage order translation */
        add_filter(
                'pll_get_post_types', array($this, 'manageOrderTranslation')
        );
        /* Save the order language with every checkout */
        add_action(
                'woocommerce_checkout_update_order_meta', array($this, 'saveOrderLanguage')
        );

        if (is_admin()) {
            $this->limitPolylangFeaturesForOrders();
        }

        /* For the query used to get orders in my accout page */
        add_filter('woocommerce_my_account_my_orders_query', array($this, 'correctMyAccountOrderQuery')
        );

        /* Translate products in order details */
        add_filter(
                'woocommerce_order_item_product', array($this, 'translateProductsInOrdersDetails'), 10, 3
        );
        add_filter(
                'woocommerce_order_item_name', array($this, 'translateProductNameInOrdersDetails'), 10, 3
        );
    }

    /**
     * Notifty polylang about order custom post.
     *
     * @param array $types array of custom post names managed by polylang
     *
     * @return array
     */
    public function manageOrderTranslation(array $types)
    {
        $options = get_option('polylang');
        $postTypes = $options['post_types'];
        if (!in_array('shop_order', $postTypes)) {
            $options['post_types'][] = 'shop_order';
            update_option('polylang', $options);
        }

        $types [] = 'shop_order';

        return $types;
    }

    /**
     * Save the order language with every checkout.
     *
     * @param int $order the order object
     */
    public function saveOrderLanguage($order)
    {
        $current = pll_current_language();
        if ($current) {
            pll_set_post_language($order, $current);
        }
    }

    /**
     * Translate products in order details pages.
     *
     * @param \WC_Product $product
     *
     * @return \WC_Product
     */
    public function translateProductsInOrdersDetails($product)
    {
        if ($product) {
            return Utilities::getProductTranslationByObject($product);
        } else {
            return false;
        }
    }

    /**
     * Translate the product name in order details page.
     *
     * @param string $name product name
     * @param array  $item order item
     * @param boolean $is_visible whether product is visible. Defaults to false.
     *
     * @return string product name
     *
     * @todo should I remove this filter and let handle the translation in the
     *       theme file?
     */
    public function translateProductNameInOrdersDetails($name, $item, $is_visible = false)
    {
        $id = $item['item_meta']['_product_id'][0];
        $product = Utilities::getProductTranslationByID($id);
        if ($product) {
            if (!$is_visible) {
                return $product->post->post_title;
            } else {
                return sprintf('<a href="%s">%s</a>', get_permalink($product->id), $product->post->post_title);
            }
        } else {
            return $name;
        }
    }

    /**
     * Correct My account order query.
     *
     * Will correct the query to display orders from all languages
     *
     * @param array $query
     *
     * @return array
     */
    public function correctMyAccountOrderQuery(array $query)
    {
        $query['lang'] = implode(',', pll_languages_list());

        return $query;
    }

    /**
     * Disallow the user to create translations for this post type.
     */
    public function limitPolylangFeaturesForOrders()
    {
        add_action('current_screen', function () {
            $screen = get_current_screen();

            if ($screen->post_type === 'shop_order') {
                add_action('admin_print_scripts', function () {
                    $jsID = 'order-translations-buttons';
                    $code = '$(".pll_icon_add,#post-translations").fadeOut()';

                    Utilities::jsScriptWrapper($jsID, $code);
                }, 100);
            }
        });
    }

    /**
     * Get the order language.
     *
     * @param int $ID order ID
     *
     * @return string|false language in success , false otherwise
     */
    public static function getOrderLangauge($ID)
    {
        return pll_get_post_language($ID);
    }
}
