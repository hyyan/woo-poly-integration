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
        
        // Custom 'add to cart' handler for variable products
        add_filter('woocommerce_add_to_cart_handler', array($this, 'setAddToCartHandler'), 10, 2);
        add_action('woocommerce_add_to_cart_handler_' . self::ADD_TO_CART_HANDLER_VARIABLE, array($this, 'addToCartHandlerVariable'), 10, 1);
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
        $cart_product_id   = isset($cart_item['product_id']) ? $cart_item['product_id'] : 0;
        $cart_variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;

        // By default, returns the same input
        $cart_item_data_translation = $cart_item_data;

        switch ($cart_item_data->get_type()) {
            case 'variation':
                $variation_translation   = $this->getVariationTranslation($cart_variation_id);
                $cart_item_data_translation = $variation_translation ? $variation_translation : $cart_item_data_translation;
                break;

            case 'simple':
            default:
                $product_translation        = Utilities::getProductTranslationByID($cart_product_id);
                $cart_item_data_translation = $product_translation ? $product_translation : $cart_item_data_translation;
                break;
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
        } elseif ($cart_variation_id != 0  && false == $this->getVariationTranslation($cart_variation_id)) {
            // Variation product without translation in current language
            return $item_data;
        }

        $item_data_translation = array();

        foreach ($item_data as $data) {
            $term_id = null;

            foreach ($cart_item['variation'] as $tax => $term_slug) {
                $tax  = str_replace('attribute_', '', $tax);
                $term = get_term_by('slug', $term_slug, $tax);

                if ($term && $term->name === $data['value']) {
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

                    $item_data_translation[] = array('key' => $data['key'], 'value' => !$error ? $term_translation->name : $data['value'] ); // On error return same
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
     * Set custom add to cart handler.
     *
     * @param string    $product_type   Product type of the product being added to cart
     * @param (mixed)   $product        Product object of the product being added to cart
     *
     * @return string   Costum add to cart handler
     */
    public function setAddToCartHandler($product_type, $product)
    {
        return 'variable' === $product_type ? self::ADD_TO_CART_HANDLER_VARIABLE : $product_type;
    }

    /**
     * Custom add to cart handler for variable products
     *
     * Based on function add_to_cart_handler_variable( $product_id ) from
     * <install_dir>/wp-content/plugins/woocommerce/includes/class-wc-form-handler.php
     * but using $url as argument.Therefore we use the initial bits from
     * add_to_cart_action( $url ).
     *
     * @param string    $url   Add to cart url (e.g. https://www.yourdomain.com/?add-to-cart=123&quantity=1&variation_id=117&attribute_size=Small&attribute_color=Black )
     */
    public function addToCartHandlerVariable($url)
    {
        // From add_to_cart_action( $url )
        if (empty($_REQUEST['add-to-cart']) || !is_numeric($_REQUEST['add-to-cart'])) {
            return;
        }

        $product_id          = apply_filters('woocommerce_add_to_cart_product_id', absint($_REQUEST['add-to-cart']));
        $was_added_to_cart   = false;
        $adding_to_cart      = wc_get_product($product_id);

        if (!$adding_to_cart) {
            return;
        }
        // End: From add_to_cart_action( $url )

        // From add_to_cart_handler_variable( $product_id )
        $variation_id       = empty($_REQUEST['variation_id']) ? '' : absint($_REQUEST['variation_id']);
        $quantity           = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount($_REQUEST['quantity']);
        $missing_attributes = array();
        $variations         = array();
        $attributes         = $adding_to_cart->get_attributes();

        // If no variation ID is set, attempt to get a variation ID from posted attributes.
        if (empty($variation_id)) {
            $data_store   = \WC_Data_Store::load('product');
            $variation_id = $data_store->find_matching_product_variation($adding_to_cart, wp_unslash($_POST));
        }

        /**
         * Custom code to check if a translation of the product is already in the
         * cart,* and in that case, replace the variation being added to the cart
         * by the respective translation in the language of the product already
         * in the cart.
         * NOTE: The product_id is filtered by $this->add_to_cart() and holds the
         * id of the product translation, if one exists in the cart.
         */
        if ($product_id != absint($_REQUEST['add-to-cart'])) {
            // There is a translation of the product already in the cart:
            // Get the language of the product in the cart
            $lang = pll_get_post_language($product_id);

            // Get the respective variation in the language of the product in the cart
            $variation    = $this->getVariationTranslation($variation_id, $lang);
            $variation_id = $variation->get_id();
        } else {
            $variation = wc_get_product($variation_id);
        }
        /**
         * End of custom code.
         */

        // Validate the attributes.
        try {
            if (empty($variation_id)) {
                return;
                throw new \Exception(__('Please choose product options&hellip;', 'woocommerce'));
            }

            $variation_data = wc_get_product_variation_attributes($variation_id);

            foreach ($attributes as $attribute) {
                if (!$attribute['is_variation']) {
                    continue;
                }

                $taxonomy = 'attribute_' . sanitize_title($attribute['name']);

                if (isset($_REQUEST[$taxonomy])) {
                    // Get value from post data
                    if ($attribute['is_taxonomy']) {
                        // Don't use wc_clean as it destroys sanitized characters
                        $value = sanitize_title(stripslashes($_REQUEST[$taxonomy]));
                        
                        /**
                        * Custom code to check if a translation of the product is already in the cart,
                        * and in that case, replace the variation attribute being added to the cart by
                        * the respective translation in the language of the product already in the cart
                        * NOTE: The product_id is filtered by $this->add_to_cart() and holds the id of
                        * the product translation, if one exists in the cart.
                        */
                        if ($product_id != absint($_REQUEST['add-to-cart'])) {
                            // Get the translation of the term
                            $term  = get_term_by('slug', $value, $attribute['name']);
                            $_term = get_term_by('id', pll_get_term(absint($term->term_id), $lang), $attribute['name']);
    
                            if ($_term) {
                                $value = $_term->slug;
                            }
                        }
                        /**
                        * End of custom code.
                        */
                    } else {
                        $value = wc_clean(stripslashes($_REQUEST[$taxonomy]));
                    }

                    // Get valid value from variation
                    //change proposed by @theleemon
                    $variation_data = wc_get_product_variation_attributes($variation->get_id());
                    $valid_value = isset($variation_data[$taxonomy]) ? $variation_data[$taxonomy] : '';
                    //$valid_value = isset($variation_data[$taxonomy]) ? $variation_data[$taxonomy] : '';

                    // Allow if valid or show error.
                    if ('' === $valid_value || $valid_value === $value) {
                        $variations[$taxonomy] = $value;
                    } else {
                        throw new \Exception(sprintf(__('Invalid value posted for %s', 'woocommerce'), wc_attribute_label($attribute['name'])));
                    }
                } else {
                    $missing_attributes[] = wc_attribute_label($attribute['name']);
                }
            }
            if (!empty($missing_attributes)) {
                throw new \Exception(sprintf(_n('%s is a required field', '%s are required fields', sizeof($missing_attributes), 'woocommerce'), wc_format_list_of_items($missing_attributes)));
            }
        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            //return false;
            /**
             * Custom code: We are doing an action, therefore no return needed. Instead we need to
             * set $passed_validation to false, to ensure the product is not added to the card.
             */
            add_filter('woocommerce_add_to_cart_validation', '__return_false');
        }

        // Add to cart validation
        $passed_validation    = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations);

        if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variations) !== false) {
            wc_add_to_cart_message(array($product_id => $quantity), true);
            //return true;
            /**
             * Custom code: We are doing an action, therefore no return needed. Intead we need to
             * set $was_added_to_cart to true to trigger the optional redirect
             */
            $was_added_to_cart = true;
        }

        //return false; We are doing an action, therefore no return needed. but we need to set $was_added_to_cart to trigger the redirect
        // End: From add_to_cart_handler_variable( $product_id )

        /**
         * Because this is a custom handler we need to take care of the rediret
         * to the cart. Again we use the code from add_to_cart_action( $url )
         */
        // From add_to_cart_action( $url )
        // If we added the product to the cart we can now optionally do a redirect.
        if ($was_added_to_cart && wc_notice_count('error') === 0) {
            // If has custom URL redirect there
            if ($url = apply_filters('woocommerce_add_to_cart_redirect', $url)) {
                wp_safe_redirect($url);
                exit;
            } elseif (get_option('woocommerce_cart_redirect_after_add') === 'yes') {
                wp_safe_redirect(wc_get_cart_url());
                exit;
            }
        }
        // End: From add_to_cart_action( $url )
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
        $variation   = wc_get_product($variation_id);
        $parentid = Utilities::get_variation_parentid($variation);
        $_product_id = pll_get_post($parentid, $lang);

        // Get variation translation using the duplication metadata value
        $meta = get_post_meta($variation_id, Variation::DUPLICATE_KEY, true);

        if ($_product_id && $meta) {
            // Get posts (variations) with duplication metadata value
            $variation_post = get_posts(array(
                'meta_key'    => Variation::DUPLICATE_KEY,
                'meta_value'  => $meta,
                'post_type'   => 'product_variation',
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
