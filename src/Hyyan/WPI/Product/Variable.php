<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;

/**
 * Variable.
 *
 * Handle Variable Products
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Variable
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        // Handle variations duplication
        add_action( 'save_post_product', array( $this, 'duplicateVariations' ), 10, 3 );
        add_action( 'save_post_product', array( $this, 'syncDefaultAttributes' ), 10, 3 );

        // Remove variations
        add_action('wp_ajax_woocommerce_remove_variations', array($this, 'removeVariations'), 9);

        // Extend meta list to include variation meta and fields to lock
        add_filter(HooksInterface::PRODUCT_META_SYNC_FILTER, array($this, 'extendProductMetaList'));
        add_filter(HooksInterface::FIELDS_LOCKER_SELECTORS_FILTER, array($this, 'extendFieldsLockerSelectors'));

        add_filter( 'woocommerce_variable_children_args', array( $this, 'allow_variable_children' ), 10, 3 ); 

        // Variable Products limitations warnings and safe-guards
        if (is_admin()) {
            $this->handleVariableLimitation();
            $this->shouldDisableLangSwitcher();
        }
    }

    /**
     * Stop Polylang preventing WooCommerce from finding child variations
     * by hooking  woocommerce_variable_children_args and 
     * adding any langugage parameter to variable children
     * needed since Polylang 2.8
     *
     * @param array    $args         array of WP_Query args
     * @param \WC_Product $product      Product 
     * @param bool     $visible      whether querying for visible children or not
     * 
     * @return $args
    */
    public function allow_variable_children($args, $product, $visible){
        $args['lang'] = '';  
        return $args;
    }
        
    /**
     * Translate Variation for given variable product.
     *
     * @param int      $ID     product variable ID
     * @param \WP_Post $post   Product Post
     * @param bool     $update true if update , false otherwise
     *
     * @return bool
     */
    public function duplicateVariations($ID, \WP_Post $post, $update)
    {
        static $last_id;

        //JM2021: fast exit from repeated saves
        if ($ID==$last_id){
            //was proved this is called
            //error_log('woopoly Variable:duplicateVariations quit from repeated save event on ' . $ID);
            return false;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        global $pagenow;
        if (!in_array($pagenow, array('post.php', 'post-new.php'))  || $post->post_type !== 'product') {
            //note, arrives here for example when duplicating variable product from products screen
            return false;
        }

        $product = wc_get_product($ID);

        if (!$product) {
            return false;
        }

        //JM2021: limit over-calling of this function
        if ($product->get_type()!='variable' && !isset($_GET['from_post'])){
            //error_log('aborted save_post_product hook duplicateVariations called on product type ' . $product->get_type());
            return false;            
        }

        $last_id = $ID;
            
        if ($product->get_parent_id()) {
            $product = wc_get_product($product->get_parent_id());
        }

        $from = null;

        $post_lang=pll_get_post_language($product->get_id());
        $def_lang=pll_default_language();
        //error_log('in save_post_product hook duplicateVariations ID ' . $ID . ' parent id ' . $product->get_id() . ' post lang ' . $post_lang . ' def lang ' . $def_lang);
        if (pll_get_post_language($product->get_id()) == pll_default_language()) {
            $from = $product;
        } else {
            if (isset($_GET['from_post'])) {
                /*
                 * This check will make sure that variation , will be
                 * created for brand new products which are not saved yet by user
                 */
                $from = Utilities::getProductTranslationByID(
                                esc_attr($_GET['from_post']),
                    pll_default_language()
                );
            } else {
                $from = Utilities::getProductTranslationByObject(
                                $product,
                    pll_default_language()
                );
            }
        }

        if (!($from instanceof \WC_Product_Variable)) {
            return false;
        }

        //if creating a new translation, process the target language only, otherwise check all languages 
        $langs = isset($_GET['new_lang']) ? array($_GET['new_lang']) : pll_languages_list();
        //JM2021: remove default lang since this should always be source not destination for copy (for variable products)
        if (($key = array_search($def_lang, $langs)) !== false) {
            unset($langs[$key]);
        }
        remove_action('save_post', array($this, __FUNCTION__), 10);
        add_filter( 'woocommerce_hide_invisible_variations', function() {
          return false;
        } );
        foreach ($langs as $lang) {
            $variation = new Variation(
                    $from,
                Utilities::getProductTranslationByObject($product, $lang)
            );
            $variation->duplicate();
        }
        add_action('save_post', array($this, __FUNCTION__), 10, 3);

        /*
                remove_action('save_post', array($this, __FUNCTION__), 10);
                $translations = Utilities::getProductTranslationsArrayByObject($from, true);
                foreach ($translations as $translation){
                    $variation = new Variation($from, wc_get_product($translation));
                    $variation->duplicate();
                }
                add_action('save_post', array($this, __FUNCTION__), 10, 3);
         *
         */
    }

    /**
     * Prevents plugins (like Polylang) from overwriting default attribute meta sync.
         * TODO: split and correct: this function is now covering multiple concepts, not just skipping default attributes
     *
     * Why is this required: Polylang to simplify the synchronization process of multiple meta values,
     * deletes all metas first. In this process Variable Product default attributes that are not taxomomies
     * managed by Polylang, are lost.
     *
     * @param boolean   $check      Whether to manipulate metadata. (true to continue, false to stop execution)
     * @param int       $object_id  ID of the object metadata is for
     * @param string    $meta_key   Metadata key
     * @param string    $meta_value Metadata value
     */
    public function skipDefaultAttributesMeta($check, $object_id, $meta_key, $meta_value)
    {
        // Ignore if not 'default attribute' meta
        if ('_default_attributes' === $meta_key) {
            $product = wc_get_product($object_id);
            $current_filter	 = current_filter();

            // Don't let anyone delete the meta. NO ONE!
            if ( $product && $current_filter === 'delete_post_metadata' ) {
                return false;
            }

            // _default_attributes meta should be unique
            if ($product && $current_filter === 'add_post_metadata') {
                $old_value = get_post_meta($product->get_id(), '_default_attributes');
                return empty($old_value) ? $check : false;
            }


            /* #432: this check was partially incorrect and
             * is no longer needed after removing _default_attributes
             * from the list of meta synchronised by Polylang.
             * (another way of doing it would be to hook the polylang filter
             * in sync-metas maybe_translate_value
             * and translate the default attributes there, but that is bigger change to this plugin
             */
        }

        return $check;
    }

    /**
     * Sync default attributes between product translations.
     *
     * @param int       $post_id    Post ID
     * @param \WP_Post  $post       Post Object
     * @param boolean   $update     true if updating the post, false otherwise
     */
    public function syncDefaultAttributes($post_id, $post, $update)
    {
        // Don't sync if not in the admin backend nor on autosave or not product page
        if (!is_admin() &&  defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || get_post_type($post_id) !== 'product') {
            return;
        }

        // Don't sync if Default Attribute syncronization is disabled
        $metas = Meta::getProductMetaToCopy();

        if (!in_array('_default_attributes', $metas)) {
            return;
        }

        //  To avoid Polylang overwriting default attribute meta
        add_filter('delete_post_metadata', array($this, 'skipDefaultAttributesMeta'), 10, 4);
        add_filter('add_post_metadata', array($this, 'skipDefaultAttributesMeta'), 10, 4);
        add_filter('update_post_metadata', array($this, 'skipDefaultAttributesMeta'), 10, 4);

        // Don't sync if not a Variable Product
        $product = wc_get_product($post_id);

        //JM2021: new translations of Variable Products are first created as simple
        //but now at this point are already Variable but not linked translations
        if ($product && isset($_GET['from_post']) && Utilities::maybeVariableProduct($product)) {

            // Only need to sync for the new translation from source product
            // The other product translation stay untouched
            $attributes_translation = Utilities::getDefaultAttributesTranslation($_GET['from_post'], $_GET['new_lang']);

            if (!empty($attributes_translation) && isset($attributes_translation[$_GET['new_lang']])) {
                update_post_meta($product->get_id(), '_default_attributes', $attributes_translation[$_GET['new_lang']]);
				$product->set_default_attributes( $attributes_translation[ $_GET[ 'new_lang' ] ] );
            }
        } elseif ($product && 'variable' === $product->get_type()) {
            // Variable Product

            // For each product translation, get the translated (default) terms/attributes
            $attributes_translation = Utilities::getDefaultAttributesTranslation($post_id);
            $langs                  = pll_languages_list();

            foreach ($langs as $lang) {
                $translation_id = pll_get_post($post_id, $lang);

                if ( $translation_id && $translation_id != $post_id) {
                    update_post_meta($translation_id, '_default_attributes', $attributes_translation[$lang]);
                }
            }
        }
    }

    /**
     * Remove variatoins related to current removed variation.
     */
    public function removeVariations()
    {
        if (isset($_POST['variation_ids'])) {
            $IDS = (array) $_POST['variation_ids'];

            foreach ($IDS as $ID) {
                Variation::deleteRelatedVariation($ID);
            }
        }
    }

    /**
     * Extend the product meta list that must by synced.
     *
     * @param array $metas current meta list
     *
     * @return array
     */
    public function extendProductMetaList(array $metas)
    {
        $metas['Variables'] = array(
            'name' => __('Variables Metas', 'woo-poly-integration'),
            'desc' => __('Variable Product pricing Metas', 'woo-poly-integration'),
            'metas' => array(
                '_min_variation_price',
                '_max_variation_price',
                '_min_price_variation_id',
                '_max_price_variation_id',
                '_min_variation_regular_price',
                '_max_variation_regular_price',
                '_min_regular_price_variation_id',
                '_max_regular_price_variation_id',
                '_min_variation_sale_price',
                '_max_variation_sale_price',
                '_min_sale_price_variation_id',
                '_max_sale_price_variation_id',
            ),
        );

        return $metas;
    }

    /**
     * Extend the fields locker selectors.
     *
     * Extend the fields locker selectors to lock variation fields for translation
     *
     * @param array $selectors
     *
     * @return array
     */
    public function extendFieldsLockerSelectors(array $selectors)
    {
        //FIX: #128 allow variable product description to be translated
        $variable_exclude	 = array( '[name^="variable_description"]' );
        /* metas disabled for sync */
        $metas_nosync		 = Meta::getDisabledProductMetaToCopy();
        foreach ( $metas_nosync as $meta_nosync ) {
          switch ( $meta_nosync ) {
            case '_sku':
            case '_manage_stock':
            case '_stock':
            case '_backorders':
            case '_stock_status':
            case '_sold_individually':
            case '_weight':
            case '_length':
            case '_width':
            case '_height':
            case '_tax_status':
            case '_tax_class':
            case '_regular_price':
            case '_sale_price':
            case '_sale_price_dates_from':
            case '_sale_price_dates_to':
            case '_download_limit':
            case '_download_expiry':
            case '_download_type':
                $variable_exclude[]	 = '[name^="variable' . $meta_nosync . '"]';
				    case 'product_shipping_class':
                $variable_exclude[]	 = '[name^="variable_shipping_class"]';
				    case '_virtual':
				    case '_downloadable':
                $variable_exclude[]	 = '[name^="variable_is' . $meta_nosync . '"]';
          }
        }
        $variable_exclude = apply_filters( HooksInterface::FIELDS_LOCKER_VARIABLE_EXCLUDE_SELECTORS_FILTER, $variable_exclude );

        $selectors[] = '#variable_product_options :input:not(' . implode(',', $variable_exclude) . ')';
        return $selectors;
    }

    /**
     * Handle variation limitation about defualt language.
     *
     * @global string $pagenow current page name
     *
     * @return bool false if this is not new variable product
     */
    public function handleVariableLimitation()
    {
        global $pagenow;
        if ($pagenow !== 'post-new.php') {
            return false;
        }

        if (isset($_GET['from_post'])) {
            return false;
        }

        if (pll_current_language() === pll_default_language()) {
            return false;
        }

        add_action('admin_print_scripts', function () {
            $jsID = 'variables-data';
            $code = sprintf(
                    'var HYYAN_WPI_VARIABLES = {'
                    .'     title       : "%s" ,'
                    .'     content     : "%s" ,'
                    .'     defaultLang : "%s"'
                    .'};',
                __('Wrong Language For Variable Product', 'woo-poly-integration'),
                __("Variable product must be created in the default language first or things will get messy. <br> <a href='https://github.com/hyyan/woo-poly-integration/tree/master#what-you-need-to-know-about-this-plugin' target='_blank'>Read more, to know why</a>", 'woo-poly-integration'),
                pll_default_language()
            );

            Utilities::jsScriptWrapper($jsID, $code, false);
        });

        add_action('admin_enqueue_scripts', function () {
            $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-effects-core');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script(
                    'woo-poly-variables',
                plugins_url('public/js/Variables' . $suffix . '.js', Hyyan_WPI_DIR),
                array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'),
                \Hyyan\WPI\Plugin::getVersion(),
                true
            );
        }, 100);
    }

    /**
     * Check if we have to disable the language switcher in the polylang setting
     * page.
     */
    public function shouldDisableLangSwitcher()
    {
        add_action('current_screen', function () {
            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
            if ($screen && $screen->id !== 'settings_page_mlang') {
                return false;
            }

            $count = wp_count_posts('product_variation');
            if (!($count && $count->publish > 0)) {
                return false;
            }

            add_action('admin_print_scripts', function () {
                $jsID = 'disable-lang-switcher';
                $code = sprintf(
                        '$("#options-lang #default_lang")'
                        .'.css({'
                        .'     "opacity": .5,'
                        .'     "pointer-events": "none"'
                        .'});'
                        .' $("#options-lang").prepend('
                        .'     "<p class=\'update-nag\'>%s</p>"'
                        .');',
                    __('You can not change the default language because you are using variable products', 'woo-poly-integration')
                );
                Utilities::jsScriptWrapper($jsID, $code);
            }, 100);
        });
    }
}
