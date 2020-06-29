<?php
/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hyyan\WPI;

use Hyyan\WPI\Product\Variation;
use Hyyan\WPI\Product\Meta;
use Hyyan\WPI\Utilities;

/**
 * Cart.
 *
 * Handle cart translation
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Cart
{

    const ADD_TO_CART_HANDLER_VARIABLE = 'wpi_variable';

    /**
     * Construct object.
     */
    public function __construct()
    {
        // Handle add to cart
        add_filter('woocommerce_add_to_cart_product_id', array($this, 'addToCart'), 10, 1);

        // Handle the translation of displayed porducts in cart
        add_filter('woocommerce_cart_item_product', array($this, 'translateCartItemProduct'), 10, 2);
        add_filter('woocommerce_cart_item_product_id', array($this, 'translateCartItemProductId'), 10, 1);
        add_filter('woocommerce_cart_item_permalink', array($this, 'translateCartItemPermalink'), 10, 2);
        add_filter('woocommerce_get_item_data', array($this, 'translateCartItemData'), 10, 2);

        // Handle the update of cart widget when language is switched
        add_action('wp_enqueue_scripts', array($this, 'replaceCartFragmentsScript'), 100);

    }

    /**
     * Add to cart.
     *
     * The function will make sure that products won't be duplicated for each
     * language
     *
     * @param int $ID the current product ID
     *
     * @return int the final product ID
     */
    public function addToCart($ID)
    {
        $result = $ID;

        // get the product translations
        $IDS = Utilities::getProductTranslationsArrayByID($ID);

        // check if any of product's translation is already in cart
        foreach (WC()->cart->get_cart() as $values) {
            $product = $values['data'];

            if (in_array($product->get_id(), $IDS)) {
                $result = $product->get_id();
                break;
            }
        }

        return $result;
    }

    /**
     * Translate displayed product in cart.
     *
     * @param \WC_Product|\WC_Product_Variation $cart_item_data
     * @param array                             $cart_item
     *
     * @return \WC_Product|\WC_Product_Variation
     */
    public function translateCartItemProduct($cart_item_data, $cart_item)
    {
        $cart_product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : 0;
        $cart_variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;

        // By default, returns the same input
        $cart_item_data_translation = $cart_item_data;

        switch ($cart_item_data->get_type()) {
            case 'variation':
                $variation_translation = $this->getVariationTranslation($cart_variation_id);
                if ($variation_translation && $variation_translation->get_id() != $cart_variation_id) {
                    $cart_item_data_translation = $variation_translation;
                }
                break;

            case 'simple':
            default:
                $product_translation = Utilities::getProductTranslationByID($cart_product_id);
                if ($product_translation && $product_translation->get_id() != $cart_product_id) {
                    $cart_item_data_translation = $product_translation;
                }
                break;
        }


        // If we are changing the product to the right language
        if ($cart_item_data_translation->get_id() != $cart_item_data->get_id()) {
            $cart_item_data_translation = apply_filters(HooksInterface::CART_SWITCHED_ITEM, $cart_item_data_translation, $cart_item_data, $cart_item);
        }

        return $cart_item_data_translation;
    }

    /**
     * Replace products id in cart with id of product translation.
     *
     * @param int   $cart_product_id    Product Id
     *
     * @return int Id of the product translation
     */
    public function translateCartItemProductId($cart_product_id)
    {
        $translation_id = pll_get_post($cart_product_id);
        return $translation_id ? $translation_id : $cart_product_id;
    }

    /**
     * Translate product attributes in the product permalink querystring.
     *
     * @param string    $item_permalink    Product permalink
     * @param array     $cart_item         Cart item
     *
     * @return string   Translated permalink
     */
    public function translateCartItemPermalink($item_permalink, $cart_item)
    {
        $cart_variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;

        if ($cart_variation_id !== 0) {
            // Variation
            $variation_translation = $this->getVariationTranslation($cart_variation_id);
            return $variation_translation ? $variation_translation->get_permalink() : $item_permalink;
        }

        return $item_permalink;
    }

    /**
     * Translate product variation attributes.
     *
     * @param array     $item_data      Variation attributes
     * @param array     $cart_item      Cart item
     *
     * @return array   Translated attributes
     */
    public function translateCartItemData($item_data, $cart_item)
    {
        // We don't translate the variation attributes if the product in the cart
        // is not a product variation, and in case of a product variation, it
        // doesn't have a translation in the current language.
        $cart_variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;

        if ($cart_variation_id == 0) {
            // Not a variation product
            return $item_data;
        } elseif ($cart_variation_id != 0 && false == $this->getVariationTranslation($cart_variation_id)) {
            // Variation product without translation in current language
            return $item_data;
        }

        $item_data_translation = array();

        foreach ($item_data as $data) {
            $term_id = null;

            foreach ($cart_item['variation'] as $tax => $term_slug) {
                $tax = str_replace('attribute_', '', $tax);
                $term = get_term_by('slug', $term_slug, $tax);

                if ($term && isset($data['value']) && $term->name === $data['value']) {
                    $term_id = $term->term_id;
                    break;
                }
            }

            if ($term_id !== 0 && $term_id !== null) {
                // Product attribute is a taxonomy term - check if Polylang has a translation
                $term_id_translation = pll_get_term($term_id);

                if ($term_id_translation == $term_id) {
                    // Already showing the attribute (term) in the correct language
                    $item_data_translation[] = $data;
                } else {
                    // Get term translation from id
                    $term_translation = get_term($term_id_translation);

                    $error = get_class($term_translation) == 'WP_Error';

                    $item_data_translation[] = array('key' => $data['key'], 'value' => !$error ? $term_translation->name : $data['value']); // On error return same
                }
            } else {
                // Product attribute is post metadata and not translatable - return same
                $item_data_translation[] = $data;
            }
        }

        return !empty($item_data_translation) ? $item_data_translation : $item_data;
    }

    /**
     * Replace woo fragments script.
     *
     * To update cart widget when language is switched
     */
    public function replaceCartFragmentsScript()
    {
        /* remove the orginal wc-cart-fragments.js and register ours */
        wp_deregister_script('wc-cart-fragments');
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script(
            'wc-cart-fragments', plugins_url('public/js/Cart' . $suffix . '.js', Hyyan_WPI_DIR), array('jquery', 'jquery-cookie'), Plugin::getVersion(), true
        );
    }

    /**
     * Get product variation translation.
     *
     * Returns the product variation object for a given language.
     *
     * @param int       $variation_id   (required) Id of the variation to translate
     * @param string    $lang           (optional) 2-letters code of the language
     *                                  like Polylang
     *                                  language slugs, defaults to current language
     *
     * @return \WP_Product_Variation    Product variation object for the given
     *                                  language, false on error or if doesn't exists.
     */
    public function getVariationTranslation($variation_id, $lang = '')
    {
        $_variation = false;

        // Get parent product translation id for the given language
        $variation = wc_get_product($variation_id);
        $parentid = Utilities::get_variation_parentid($variation);
        $_product_id = pll_get_post($parentid, $lang);

        // Get variation translation using the duplication metadata value
        $meta = get_post_meta($variation_id, Variation::DUPLICATE_KEY, true);

        if ($_product_id && $meta) {
            // Get posts (variations) with duplication metadata value
            $variation_post = get_posts(array(
                'meta_key' => Variation::DUPLICATE_KEY,
                'meta_value' => $meta,
                'post_type' => 'product_variation',
                'post_parent' => $_product_id
            ));

            // Get variation translation
            if ($variation_post && count($variation_post) == 1) {
                $_variation = wc_get_product($variation_post[0]->ID);
            }
        }

        return $_variation;
    }
}
