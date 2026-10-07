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
    private static $storeApiContexts = array();

    /**
     * Construct object.
     */
    public function __construct()
    {
        // Handle add to cart
        add_filter('woocommerce_add_to_cart_product_id', array($this, 'addToCart'), 10, 1);
        add_filter('woocommerce_cart_id', array($this, 'retainTranslatedVariationCartKey'), 10, 5);
        if (defined('WC_VERSION') && version_compare(WC_VERSION, '3.3.0', '<')) {
            $this->registerLegacyCartStockValidation();
        } else {
            add_filter('woocommerce_add_cart_item_data', array($this, 'validateLinkedCartStock'), 10, 4);
        }

        // Handle the translation of displayed porducts in cart
        add_filter('woocommerce_cart_item_product', array($this, 'translateCartItemProduct'), 10, 2);
        add_filter('woocommerce_cart_item_product_id', array($this, 'translateCartItemProductId'), 10, 1);
        add_filter('woocommerce_cart_item_permalink', array($this, 'translateCartItemPermalink'), 10, 2);
        add_filter('woocommerce_get_item_data', array($this, 'translateCartItemData'), 10, 2);

        add_filter('rest_request_before_callbacks', array($this, 'switchStoreApiLocale'), 10, 3);
        add_filter('rest_request_after_callbacks', array($this, 'translateStoreApiCart'), 10, 3);
        add_filter('rest_request_after_callbacks', array($this, 'restoreStoreApiLocale'), 20, 3);
        add_filter('woocommerce_hydration_request_after_callbacks', array($this, 'translateStoreApiCart'), 10, 3);

        // Handle the update of cart widget when language is switched
        add_action('wp_enqueue_scripts', array($this, 'replaceCartFragmentsScript'), 100);

    }

    private function registerLegacyCartStockValidation()
    {
        add_filter('woocommerce_add_cart_item', function ($item) {
            return $this->validateLinkedCartStock($item, $item['product_id'], $item['variation_id'], $item['quantity']);
        });
        add_action('woocommerce_after_cart_item_quantity_update', function ($key, $quantity, $previous) {
            if ($quantity <= $previous) {
                return;
            }
            $item = WC()->cart->cart_contents[$key];
            try {
                $this->validateLinkedCartStock($item, $item['product_id'], $item['variation_id'], 0);
            } catch (\Exception $error) {
                WC()->cart->cart_contents[$key]['quantity'] = $previous;
                throw $error;
            }
        }, 0, 3);
        add_filter('woocommerce_update_cart_validation', function ($passed, $key, $item, $quantity) {
            if (!$passed || $quantity <= $item['quantity']) {
                return $passed;
            }
            try {
                $this->validateLinkedCartStock($item, $item['product_id'], $item['variation_id'], $quantity - $item['quantity']);
            } catch (\Exception $error) {
                wc_add_notice($error->getMessage(), 'error');
                return false;
            }
            return $passed;
        }, 10, 4);
    }

    public function switchStoreApiLocale($response, $handler, $request)
    {
        if (!preg_match('#^/wc/store/v1/(?:cart|checkout)(?:/|$)#', $request->get_route())) {
            return $response;
        }
        $locale = $this->getStoreApiLocale();
        if (!$locale) { return $response; }
        self::$storeApiContexts[] = array(
            'request' => spl_object_hash($request),
            'locale' => get_locale(),
            'language' => PLL()->curlang,
            'strings' => $GLOBALS['l10n']['pll_string'] ?? null,
            'direction' => $GLOBALS['text_direction'] ?? null,
        );
        try {
            Utilities::switchLocale($locale);
        } catch (\Throwable $error) {
            $this->restoreStoreApiLocale($response, $handler, $request);
            throw $error;
        }
        return $response;
    }

    public function restoreStoreApiLocale($response, $handler, $request)
    {
        $context = end(self::$storeApiContexts);
        if (!$context || $context['request'] !== spl_object_hash($request)) { return $response; }
        array_pop(self::$storeApiContexts);
        if (get_locale() !== $context['locale'] && is_locale_switched()) { restore_previous_locale(); }
        PLL()->curlang = $context['language'];
        if ($context['strings'] === null) { unset($GLOBALS['l10n']['pll_string']); }
        else { $GLOBALS['l10n']['pll_string'] = $context['strings']; }
        if ($context['direction'] === null) { unset($GLOBALS['text_direction']); }
        else { $GLOBALS['text_direction'] = $context['direction']; }
        return $response;
    }

    private function getStoreApiLocale()
    {
        $locale = pll_current_language('locale');
        if (!$locale && defined('PLL_COOKIE') && isset($_COOKIE[PLL_COOKIE]) && is_string($_COOKIE[PLL_COOKIE])) {
            $language = PLL()->model->get_language(sanitize_key(wp_unslash($_COOKIE[PLL_COOKIE])));
            $locale = $language ? $language->locale : false;
        }
        return $locale;
    }

    /**
     * Translate Store API presentation without replacing stored cart items.
     */
    public function translateStoreApiCart($response, $handler, $request)
    {
        if (!$response instanceof \WP_REST_Response || $response->get_status() !== 200 ||
                !preg_match('#^/wc/store/v1/cart(?:/|$)#', $request->get_route()) ||
                !class_exists('Automattic\\WooCommerce\\StoreApi\\StoreApi') ||
                !WC()->cart) {
            return $response;
        }
        $data = $response->get_data();
        if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
            return $response;
        }
        $locale = $this->getStoreApiLocale();
        if (!$locale) { return $response; }
        $originalLanguage = PLL()->curlang;
        $originalStrings = isset($GLOBALS['l10n']['pll_string']) ? $GLOBALS['l10n']['pll_string'] : null;
        $originalDirection = isset($GLOBALS['text_direction']) ? $GLOBALS['text_direction'] : null;
        try {
            Utilities::switch_pll_locale($locale);
            $schemas = \Automattic\WooCommerce\StoreApi\StoreApi::container()->get(\Automattic\WooCommerce\StoreApi\SchemaController::class);
            $schema = $schemas->get(\Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema::IDENTIFIER);
            $cart = WC()->cart->get_cart();
            $fields = array_flip(array('name', 'short_description', 'description', 'sku', 'permalink', 'images', 'variation', 'item_data'));
            $currencyFields = array_flip(array('currency_decimal_separator', 'currency_thousand_separator', 'currency_prefix', 'currency_suffix'));
            foreach ($data['items'] as &$item) {
                if (!isset($item['key'], $cart[$item['key']])) { continue; }
                $view = $cart[$item['key']];
                $view['data'] = $this->translateCartItemProduct($view['data'], $view);
                if ($view['data']->is_type('variation')) {
                    $view['variation'] = $this->translateSelectedCartAttributes($view['variation'], pll_get_post_language($view['product_id']), pll_get_post_language($view['data']->get_parent_id()));
                }
                $translated = $schema->get_item_response($view);
                $item = array_replace($item, array_intersect_key($translated, $fields));
                $currency = (array) $translated['prices'];
                foreach (array('prices', 'totals') as $field) {
                    $original = (array) $item[$field];
                    if ($original['currency_code'] === $currency['currency_code']) {
                        $formatted = array_replace($original, array_intersect_key($currency, $currencyFields));
                        $item[$field] = is_object($item[$field]) ? (object) $formatted : $formatted;
                    }
                }
                if (isset($data['totals'])) {
                    $original = (array) $data['totals'];
                    if ($original['currency_code'] === $currency['currency_code']) {
                        $formatted = array_replace($original, array_intersect_key($currency, $currencyFields));
                        $data['totals'] = is_object($data['totals']) ? (object) $formatted : $formatted;
                    }
                }
            }
            unset($item);
            if (isset($data['shipping_rates']) && WC()->cart->has_calculated_shipping()) {
                $controller = new \Automattic\WooCommerce\StoreApi\Utilities\CartController();
                $shippingSchema = $schemas->get(\Automattic\WooCommerce\StoreApi\Schemas\V1\CartShippingRateSchema::IDENTIFIER);
                foreach ($controller->get_shipping_packages() as $index => $package) {
                    foreach ($package['contents'] as &$contents) {
                        $contents['data'] = $this->translateCartItemProduct($contents['data'], $contents);
                    }
                    unset($contents);
                    if (isset($data['shipping_rates'][$index])) {
                        $translated = $shippingSchema->get_item_response($package);
                        foreach ($translated['shipping_rates'] as $rateIndex => $rate) {
                            if (!isset($data['shipping_rates'][$index]['shipping_rates'][$rateIndex])) { continue; }
                            $original = $data['shipping_rates'][$index]['shipping_rates'][$rateIndex];
                            if ($original['rate_id'] !== $rate['rate_id']) { continue; }
                            $original['name'] = $rate['name'];
                            if ($original['currency_code'] === $rate['currency_code']) {
                                $original = array_replace($original, array_intersect_key($rate, $currencyFields));
                            }
                            $data['shipping_rates'][$index]['shipping_rates'][$rateIndex] = $original;
                        }
                    }
                }
            }
            $response->set_data($data);
            return $response;
        } finally {
            PLL()->curlang = $originalLanguage;
            if ($originalStrings) { $GLOBALS['l10n']['pll_string'] = $originalStrings; } else { unset($GLOBALS['l10n']['pll_string']); }
            if ($originalDirection !== null) { $GLOBALS['text_direction'] = $originalDirection; } else { unset($GLOBALS['text_direction']); }
        }
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
     * Include translated cart quantities in the native stock limit.
     */
    public function validateLinkedCartStock($item_data, $product_id, $variation_id, $quantity)
    {
        $product = wc_get_product($variation_id ? $variation_id : $product_id);
        if (!$product || !$product->managing_stock() || !WC()->cart) { return $item_data; }
        $managed = $product->get_stock_managed_by_id();
        $parents = Utilities::getProductTranslationsArrayByID($product_id);
        if ($variation_id && $managed == $variation_id) {
            $source = get_post_meta($variation_id, Variation::DUPLICATE_KEY, true);
            $related = $source ? Variation::getRelatedVariation($source, true) : false;
            $ids = $related ? array_filter($related, function ($id) use ($parents) {
                return in_array(wp_get_post_parent_id($id), $parents);
            }) : array();
        } else {
            $ids = Utilities::getProductTranslationsArrayByID($managed);
        }
        $ids[] = $managed;
        $quantities = WC()->cart->get_cart_item_quantities();
        $in_cart = array_sum(array_intersect_key($quantities, array_flip($ids)));
        if (!$product->has_enough_stock($in_cart + $quantity)) {
            throw new \Exception(esc_html(sprintf(
                /* translators: 1: product name, 2: quantity in stock. */
                __('You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', 'woocommerce'),
                $product->get_name(), wc_format_stock_quantity_for_display($product->get_stock_quantity(), $product)
            )));
        }
        return $item_data;
    }

    /**
     * Retain an existing cart key for the same linked variation and item data.
     */
    public function retainTranslatedVariationCartKey($cart_id, $product_id, $variation_id, $variation, $cart_item_data)
    {
        if (!$variation_id || !WC()->cart) { return $cart_id; }
        $source = get_post_meta($variation_id, Variation::DUPLICATE_KEY, true);
        $related = $source ? Variation::getRelatedVariation($source, true) : false;
        if (!$related) { return $cart_id; }
        $parents = Utilities::getProductTranslationsArrayByID($product_id);
        foreach (WC()->cart->get_cart() as $key => $item) {
            if (!$item['variation_id'] || $item['variation_id'] == $variation_id ||
                    !in_array($item['variation_id'], $related) || !in_array($item['product_id'], $parents)) {
                continue;
            }
            $attributes = $this->translateSelectedCartAttributes($variation, pll_get_post_language($product_id), pll_get_post_language($item['product_id']));
            remove_filter('woocommerce_cart_id', array($this, 'retainTranslatedVariationCartKey'), 10);
            try {
                $candidate = WC()->cart->generate_cart_id($item['product_id'], $item['variation_id'], $attributes, $cart_item_data);
            } finally {
                add_filter('woocommerce_cart_id', array($this, 'retainTranslatedVariationCartKey'), 10, 5);
            }
            if ($candidate === $key) { return $key; }
        }
        return $cart_id;
    }

    private function translateSelectedCartAttributes($attributes, $source_language, $target_language)
    {
        if (!$source_language || !$target_language || $source_language === $target_language) { return $attributes; }
        foreach ($attributes as $attribute => &$value) {
            $taxonomy = str_replace('attribute_', '', $attribute);
            if (taxonomy_exists($taxonomy) && pll_is_translated_taxonomy($taxonomy)) {
                $terms = get_terms(array('taxonomy' => $taxonomy, 'slug' => $value, 'hide_empty' => false, 'number' => 1, 'lang' => $source_language));
                $term = !is_wp_error($terms) && $terms ? reset($terms) : false;
                $translated = $term ? pll_get_term($term->term_id, $target_language) : false;
                $term = $translated ? get_term($translated, $taxonomy) : false;
                if ($term && !is_wp_error($term)) { $value = $term->slug; }
            }
        }
        unset($value);
        return $attributes;
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

        return $this->canReadCartProduct($cart_item_data_translation) ? $cart_item_data_translation : $cart_item_data;
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
        $translation = $translation_id ? wc_get_product($translation_id) : false;
        return $translation && $this->canReadCartProduct($translation) ? $translation_id : $cart_product_id;
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
            'wc-cart-fragments', plugins_url('public/js/Cart' . $suffix . '.js', Hyyan_WPI_DIR), array('jquery', 'js-cookie'), Plugin::getVersion(), true
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
            $relatedIDS = Variation::getRelatedVariation($variation_id, true);
            $variation_post = $relatedIDS ? get_posts(array(
                'post__in' => $relatedIDS,
                'post_type' => 'product_variation',
                'post_parent' => $_product_id
            )) : array();

            // Get variation translation
            if ($variation_post && count($variation_post) == 1) {
                $_variation = wc_get_product($variation_post[0]->ID);
            }
        }

        return $_variation && $this->canReadCartProduct($_variation) ? $_variation : false;
    }

    private function canReadCartProduct($product)
    {
        $status = get_post_status_object(get_post_status($product->get_id()));
        if (!$status || (!$status->public && !current_user_can('read_post', $product->get_id()))) {
            return false;
        }
        if ($product->is_type('variation')) {
            $parent = wc_get_product($product->get_parent_id());
            return $parent && $this->canReadCartProduct($parent);
        }
        return true;
    }
}
