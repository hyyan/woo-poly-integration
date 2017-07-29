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
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Admin\MetasList;
use Hyyan\WPI\Taxonomies\Attributes;

/**
 * Product Meta.
 *
 * Handle product meta sync
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Meta
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        // sync product meta
        add_action(
            'current_screen', array($this, 'syncProductsMeta')
        );
        add_action(
            'woocommerce_product_quick_edit_save', array($this, 'saveQuickEdit')
        );

        // suppress "Invalid or duplicated SKU." error message when SKU syncronization is enabled
        add_filter(
            'wc_product_has_unique_sku',
            array($this, 'suppressInvalidDuplicatedSKUErrorMsg'), 100, 3
        );

        if ('on' === Settings::getOption('importsync', Features::getID(), 'on')) {
            add_action('woocommerce_product_import_inserted_product_object', array($this, 'onImport'), 10, 2);
        }

        //if translate attributes feature is 'on',
        if ('on' === Settings::getOption('attributes', Features::getID(), 'on')) {
            add_action('woocommerce_attribute_added', array($this, 'newProductAttribute'), 10, 2);
        }
    }


    /**
     * On insert of a new product attribute, attempt to set it to translateable by default
     *
     * @param integer  $insert_id  id of attribute
     * @param Array    $attribute  array of attribute data, see get_posted_attribute()
     */
    public function newProductAttribute($insert_id, $attribute)
    {
        $options = get_option('polylang');
        $sync = $options['taxonomies'];
        $attrname = 'pa_' . $attribute['attribute_name'];
        if (!in_array($attribute, $sync)) {
            $options['taxonomies'][] = $attrname;
            update_option('polylang', $options);
        }
    }

    /**
     * On Import, attempt synchronization of any existing translations
     *
     * @param [product]      $object array of product ids
     * @param Array          $data   data in import
     */
    public function onImport($object, $data)
    {
        // sync product meta with polylang
        add_filter('pll_copy_post_metas', array(__CLASS__, 'getProductMetaToCopy'));
        
        //sync taxonomies
        $ProductID = $object->get_id();
        if ($ProductID) {
            do_action('pll_save_post', $ProductID, $object,
                PLL()->model->post->get_translations($ProductID));

            $this->syncTaxonomiesAndProductAttributes($ProductID, $object, true);
        }
    }
    /**
     * catch save from QuickEdit
     *
       * @param WC_Product $product
     */
    public function saveQuickEdit(\WC_Product $product)
    {
        // sync product meta with polylang
        add_filter('pll_copy_post_metas', array(__CLASS__, 'getProductMetaToCopy'));
        
        //some taxonomies can actually be changed in the QuickEdit
        $this->syncTaxonomiesAndProductAttributes($product->get_id(), $product, true);
    }
    
    /**
     * Sync product meta.
     *
     * @return bool			false if the current post type is not "product"
     */
    public function syncProductsMeta()
    {
        //change proposed Teemu Suoranta 3/Nov
        $currentScreen = get_current_screen();
        if ($currentScreen->post_type !== 'product') {
            return false;
        }

        // sync product meta with polylang
        add_filter('pll_copy_post_metas', array(__CLASS__, 'getProductMetaToCopy'));

        //  new code to synchronise Taxonomies and Product attributes applied to WooCommerce 3.0
        //  which now moves product_visibility from meta to taxonomy
        //  (includes Catalog Visibility, Featured Product previously in meta)
        add_action('wp_insert_post', array($this, 'syncTaxonomiesAndProductAttributes'), 10, 3);

        $ID = false;
        $disable = false;

        /*
         * Disable editing product meta for translation
         *
         * if the "post" is defined in $_GET then we should check if the current
         * product has a translation and it is the same as the default translation
         * lang defined in polylang then product meta editing must by enabled
         *
         * if the "new_lang" is defined or if the current page is the "edit"
         * page then product meta editing must by disabled
         */

        if (isset($_GET['post'])) {
            $ID = absint($_GET['post']);
            $disable = $ID && (pll_get_post_language($ID) != pll_default_language());
        } elseif (isset($_GET['new_lang']) || $currentScreen->base == 'edit') {
            $disable = isset($_GET['new_lang']) && (esc_attr($_GET['new_lang']) != pll_default_language()) ?
                true : false;
            $ID = isset($_GET['from_post']) ? absint($_GET['from_post']) : false;

            // Add the '_translation_porduct_type' meta, for the case where
            // the product was created before plugin acivation.
            if ($ID) {
                $this->addProductTypeMeta($ID);
            }
        }

        // disable fields edit for translation
        if ($disable) {
            add_action(
                'admin_print_scripts', array($this, 'addFieldsLocker'), 100
            );
        }

        return true;
    }

    /**
     * Sync Product Taxonomies and Product Attributes:
     * after WooCommerce 3.0 a new product_visibility taxonomy handles data such as
     * Catalog Visibility, Featured Product which were previously in meta
     *
     * @param int       $post_id    Id of product being edited: if new product copy from source,
     * 																if existing product synchronize translations
     * @param \WP_Post  $post       Post object
     * @param boolean   $update     Whether this is an existing post being updated or not
     */
    public function syncTaxonomiesAndProductAttributes($post_id, $post, $update)
    {
        //get the taxonomies for the post
        $taxonomies = get_object_taxonomies(get_post_type($post_id));

        //is this a new translation being created?
        $copy = isset($_GET['new_lang']) && isset($_GET['from_post']);

        if ($copy) {
            // New translation - copy attributes from product source
            $source_id = isset($_GET['from_post']) ? absint($_GET['from_post']) : false;

            if ($source_id) {
                $this->copyTerms($source_id, $post_id, $_GET['new_lang'], $taxonomies);
                $this->syncCustomProductAttributes($source_id, $_GET['new_lang']);
            }
        } else {
            // Product edit - update terms of all product translations
            //for each language
            $langs = pll_languages_list();
            foreach ($langs as $lang) {
                //if a translation exists, and it is not this same post
                $translation_id = pll_get_post($post_id, $lang);
                if (($translation_id) && ($translation_id != $post_id)) {
                    //set ALL the terms
                    $this->copyTerms($post_id, $translation_id, $lang, $taxonomies);

                    //and synchronise custom product attributes which are not terms
                    $this->syncCustomProductAttributes($post_id, $copy);

                    //handle special case meta which should be translated instead of synchronized
                    //
                    //$this->syncUpSellsCrossSells($post_id, $copy);
                }
            }
        }
    }

    /**
     * convert any upsells and cross sells to target language
     * (unused - instead allow any language and map on render via filters in product.php
     * this allows additional translations to be added later and references update correctly)
     *
     * @param int       $source_id     Id of the source product to sync from
     * @param string	$lang      if set we are creating new lang translation so always sync
     *
     * @return bool		did mapping
     */
    /*
    public function syncUpSellsCrossSells($source_id, $lang)
    {
    //validate source and target product
    $target_product = utilities::getProductTranslationByID($source_id, $lang);
    if (!($target_product)){return false;}
    $source_product = wc_get_product($source_id);
    if (!($source_product)){return false;}

    //get product references to translate
    $upsell_ids = array();
    $cross_sell_ids = array();
    if (in_array('_upsell_ids', static::getProductMetaToCopy())) {
        $upsell_ids=$source_product->get_upsell_ids();
    }
    if (in_array('_crosssell_ids', static::getProductMetaToCopy())) {
        $cross_sell_ids=$source_product->get_cross_sell_ids();
    }

    //stop if no references to copy
    if ( (count($cross_sell_ids) == 0) && (count($upsell_ids) == 0) ) {return false;}


    //            add_post_meta( $to, $key, ( '_thumbnail_id' == $key && $tr_value = $this->model->post->get_translation( $value, $lang ) ) ? $tr_value : $value );
    return true;
    }
    */
    
    /**
     * convert array of product ids to target language
     *
     * @param array     $sourceids ids of the products
     * @param string	$lang      if set we are creating new lang translation so always sync
     *
     * @return array	mapped ides
     */
    /*
    public function getTranslatedSourceIds($sourceids, $lang)
    {
        $translatedids = array();
        foreach ($sourceids as $source_id) {
            $translated_id = utilities::getProductTranslationByID($source_id, $lang);
            if ($translated_id) {
                $translatedids[] = $translated_id;
            } else {
                $translatedids[] = $source_id;
            }
        return $translatedids;
    }
    */
    
    /**
     * sync Custom Product attributes from source product post id to all translations
     *
     * @param int       $source     Id of the source product to sync from
     * @param bool		$copy       if set we are creating new item, so always sync
     *
     * @return bool		translations were updated
     */
    public function syncCustomProductAttributes($source, $copy)
    {
        //if saving existing item, then add check that sync is currently on
        if (!($copy)) {
            $metas = static::getProductMetaToCopy();
            if (!(in_array('_custom_product_attributes', $metas))) {
                return false;
            }
        }

        //add on product custom attributes, which are not terms
        $product = wc_get_product($source);
        $productattrs = $product->get_attributes();
        //$customattrs = array_diff($productattr, $taxonomies);
        $copyattrs = array();

        //first get attributes to copy if any
        foreach ($productattrs as $productattr) {
            if (isset($productattr['is_taxonomy'])) {
                if ($productattr['is_taxonomy'] == 0) {
                    $copyattrs[] = $productattr;
                }
            }
        }

        //if there are custom attributes, sync them to any product translations
        if (count($copyattrs) > 0) {
            if ($copy) {
                $product_obj = Utilities::getProductTranslationByID($product, $copy);
                $product_obj->set_attributes($copyattrs);
            } else {
                $product_translations = Utilities::getProductTranslationsArrayByObject($product);
                foreach ($product_translations as $product_translation) {
                    if ($product_translation != $source) {
                        $product_obj = Utilities::getProductTranslationByID($product_translation);
                        $product_obj->set_attributes($copyattrs);
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * copy terms from old product post id to new product post it
     *
     *
     * @param int       $old        Id of the source product to sync from
     * @param int       $new        Id of the target product to update
     * @param string    $lang		target language
     * @param array     $taxonomies taxonomies to synchronise
     *
     */
    public function copyTerms($old, $new, $lang, $taxonomies)
    {
        //get the polylang options for later use
        global $polylang;
        $polylang_options = get_option('polylang');
        $polylang_taxs = $polylang_options['taxonomies'];

        //loop through taxonomies and take appropriate action
        foreach ($taxonomies as $tax) {
            $old_terms = wp_get_object_terms($old, $tax);
            $new_terms = array();
            foreach ($old_terms as $t) {
                $slug = $t->slug;
                //depending on the term, translate if applicable
                switch ($tax) {
                    //core language fields must not be synchronized
                    case "language":
                    case "term_language":
                    case "term_translations":
                    case "post_translations":
                        break;
                    //attributes to synchronize, not translated
                    case "product_shipping_class":
                        if (! (in_array('product_shipping_class', static::getProductMetaToCopy()))) {
                            break;
                        }
                    //woo3 visibility and featured product
                    case "product_visibility":
                        if (! (in_array('_visibility', static::getProductMetaToCopy()))) {
                            break;
                        }
                    case "product_type":
                        $new_terms[] = $slug;
                        break;
                    //categories and tags may be translated: checked against Polylang setting
                    //(if disabled in woopoly options, will be disabled in Polylang)
                    case "product_tag":
                    case "product_cat":
                    //additional terms may be Product Attributes
                    default:
                        //if is configured as translateable attribute in Polylang
                        //(no need to recheck WooPoly as when turned off in WooPoly is removed from Polylang)
                        if (pll_is_translated_taxonomy($tax)) {
                            $translated_term = pll_get_term($t->term_id, $lang);
                            if ($translated_term) {
                                $new_terms[] = get_term_by('id', $translated_term, $tax)->slug;
                            } else {
                                //if no translation exists then create one
                                $result = static::createDefaultTermTranslation($tax, $t, $slug, $lang, false);
                                if ($result) {
                                    $new_terms[] = $result;
                                }
                            }
                        } else {
                            //otherwise not translatable, do synchronisation
                            $new_terms[] = $slug;
                        }
                } //switch taxonomy slug
            } // foreach old term
            if (count($new_terms) > 0) {
                wp_set_object_terms($new, $new_terms, $tax);
            }
        } //for each taxonomy
    }

    /**
     * create a default term translation
     * (based on Polylang model->create_default_category)
     *
     *
     * @param string    $tax        taxonomy
     * @param WP_Term   $term       term object to translate
     * @param string    $slug       term slug to translate
     * @param string    $lang				target language
     * @param array     $taxonomies taxonomies to synchronise
     * @param bool      $return_id  return id of new term, otherwise return slug
     */
    public static function createDefaultTermTranslation($tax, $term, $slug, $lang, $return_id)
    {
        global $polylang;


        $newterm_name = $term->name;
        $newterm_slug = sanitize_title($slug . '-' . $lang);
        $args = array('slug' => $newterm_slug
//					,'lang' => $lang  //setting lang here has no effect, Polylang uses GET/POST vars
        );

        //if the orignal term has a parent,
        if ($term->parent) {
            //if the parent has a translation, save this to copy to new term
            $translated_parent = pll_get_term($term->parent, $lang);
            if ($translated_parent) {
                $args['parent'] = $translated_parent; //get_term_by('id', $translated_parent, $tax)->slug;
            } else {
                //no translation exists so get the actual parent
                $parent_term = \WP_Term::get_instance($term->parent);
                //and use this function to create default translation of the parent
                $result = static::createDefaultTermTranslation($tax, $parent_term, $parent_term->slug, $lang, true);
                if ($result) {
                    $args['parent'] = $result;
                }
            }
        }

        //attempt to insert the new term
        $newterm = wp_insert_term($newterm_name, $tax, $args);
        if (is_wp_error($newterm)) {
            error_log($newterm->get_error_message());
            return false;
        } else {
            $newterm_id = (int) $newterm['term_id'];
        }
        //unfortunately Polylang hooks the wp function and forces new term save into current language
        //so then we reset into current language and re-save the translations
        $translations = $polylang->model->term->get_translations($term->term_id);
        $translations[$lang] = $newterm_id;
        $polylang->model->term->set_language($newterm_id, $lang);
        $polylang->model->term->save_translations($term->term_id, $translations);

        //when auto-creating missing parent category, the id is returned
        if ($return_id) {
            return $newterm_id;
        } else {
            return $newterm_slug;
        }
    }

    /**
     * Sync Product Shipping Class.
     *
     * Shipping Class translation is not supported after WooCommerce 2.6
     * but it is still implemented by WooCommerce as a taxonomy. Therefore,
     * Polylang will not copy the Shipping Class meta.
     *
     * @param int       $post_id    Id of the product being created or edited
     * @param \WP_Post  $post       Post object
     * @param boolean   $update     Whether this is an existing post being updated or not
     */
    /*
    public function syncShippingClass($post_id, $post, $update)
    {
        if (in_array('product_shipping_class', $this->getProductMetaToCopy())) {
            // If adding new product translation copy shipping class, otherwise
            // sync all product translations with shipping class of this.
            $copy = isset($_GET['new_lang']) && isset($_GET['from_post']);

            if ($copy) {
                // New translation - copy shipping class from product source
                $ID = isset($_GET['from_post']) ? absint($_GET['from_post']) : false;
                $product = wc_get_product($ID);
            } else {
                // Product edit - update shipping class of all product translations
                $product = wc_get_product($post_id);
            }

            if ($product) {
                $shipping_class = $product->get_shipping_class();
                if ($shipping_class) {
                    $shipping_terms = get_term_by('slug', $shipping_class, 'product_shipping_class');
                    if ($shipping_terms) {
                        if ($copy) {
                            // New translation - copy shipping class from product source
                            wp_set_post_terms($post_id, array($shipping_terms->term_id), 'product_shipping_class');
                        } else {
                            // Product edit - update shipping class of all product translations
                            $langs = pll_languages_list();

                            foreach ($langs as $lang) {
                                $translation_id = pll_get_post($post_id, $lang);
                                if ($translation_id != $post_id) {
                                    // Don't sync if is the same product
                                    wp_set_post_terms($translation_id, array($shipping_terms->term_id), 'product_shipping_class');
                                }
                            }
                        }
                    }
                }
            }
        }
    }
*/
    /**
     * Add product type meta to products created before plugin activation.
     *
     * @param int $ID Id of the product in the default language
     */
    public function addProductTypeMeta($ID)
    {
        if ($ID) {
            $meta = get_post_meta($ID, '_translation_porduct_type');

            if (empty($meta)) {
                $product = wc_get_product($ID);
                if ($product) {
                    update_post_meta($ID, '_translation_porduct_type', $product->get_type());
                }
            }
        }
    }

    /**
     * Define the meta keys that must copyied from orginal product to its
     * translation.
     *
     * @param array $metas array of meta keys
     * @param bool  $flat  false to return meta list with sections (default true)
     *
     * @return array extended meta keys array
     */
    public static function getProductMetaToCopy(array $metas = array(), $flat = true)
    {
        $default = apply_filters(HooksInterface::PRODUCT_META_SYNC_FILTER, array(
            // general
            'general' => array(
                'name' => __('General Metas', 'woo-poly-integration'),
                'desc' => __('General Metas', 'woo-poly-integration'),
                'metas' => array(
                    'product-type',
                    '_virtual',
                    '_sku',
                    '_upsell_ids',
                    '_crosssell_ids',
                    '_children' ,
//                    '_featured',          //has no effect, in woo3 now product_visibility taxonomy
                    '_product_image_gallery',
                    'total_sales',
                    '_translation_porduct_type',
                    '_visibility',         //this setting now used to control sync of woo3 now product_visibility taxonomy
                ),
            ),
            // price
            'polylang' => array(
                'name' => __('Polylang Metas', 'woo-poly-integration'),
                'desc' => __('To control these values please check ', 'woo-poly-integration') .
                    ' <a href="' . get_admin_url() . 'admin.php?page=mlang_settings">' .
                    __('Polylang admin menu "Languages, Settings"') . '</a> ' .
                    __('Synchronisation section values for Page order, Featured image, Comment Status', 'woo-poly-integration'),
                'metas' => array(
                    'menu_order',           //controlled by Polylang Languages, Settings, Page order
                    '_thumbnail_id',        //controlled by Polylang Languages, Settings, Featured image
                    'comment_status',
                ),
            ),
             // stock
            'stock' => array(
                'name' => __('Stock Metas', 'woo-poly-integration'),
                'desc' => __('Stock Metas: see also Features, Stock Sync', 'woo-poly-integration'),
                'metas' => array(
                    '_manage_stock',
                    '_stock',
                    '_backorders',
                    '_stock_status',
                    '_sold_individually',
                ),
            ),
            // shipping
            'shipping' => array(
                'name' => __('ShippingClass Metas', 'woo-poly-integration'),
                'desc' => __('Shipping size and weight metas and Shipping class taxonomy', 'woo-poly-integration'),
                'metas' => array(
                    '_weight',
                    '_length',
                    '_width',
                    '_height',
                    'product_shipping_class',  //this setting now used to control sync of woo3 shipping class taxonomy
                ),
            ),
            // attributes
            'Attributes' => array(
                'name' => __('Attributes Metas', 'woo-poly-integration'),
                'desc' => __('To select individual Product Attributes for translation or synchronization, turn on here and check', 'woo-poly-integration') .
                    ' <a href="' . get_admin_url() . 'admin.php?page=mlang_settings">' .
                    __('Polylang admin menu "Languages, Settings"') . '</a> ' .
                    __(' "Custom post types and Taxonomies", "Custom Taxonomies"', 'woo-poly-integration'),
                'metas' => array(
                    '_product_attributes',
                    '_custom_product_attributes',
                    '_default_attributes',
                ),
            ),
            // Downloads
            'Downloadable' => array(
                'name' => __('Downloadable Metas', 'woo-poly-integration'),
                'desc' => __('Downloadable product Meta', 'woo-poly-integration'),
                'metas' => array(
                    '_downloadable',
                    '_downloadable_files',
                    '_download_limit',
                    '_download_expiry',
                    '_download_type',
                ),
            ),
            // Taxes
            'Taxes' => array(
                'name' => __('Taxes Metas', 'woo-poly-integration'),
                'desc' => __('Taxes Metas', 'woo-poly-integration'),
                'metas' => array(
                    '_tax_status',
                    '_tax_class',
                ),
            ),
            // price metas moved to the end next to taxes, variable class adds variable price next
            'price' => array(
                'name' => __('Price Metas', 'woo-poly-integration'),
                'desc' => __('Note the last price field is the final price taking into account the effect of sale price ', 'woo-poly-integration'),
                'metas' => array(
                    '_regular_price',
                    '_sale_price',
                    '_sale_price_dates_from',
                    '_sale_price_dates_to',
                    '_price',
                ),
            ),
        ));

        if (false === $flat) {
            return $default;
        }

        foreach ($default as $ID => $value) {
            $metas = array_merge($metas, Settings::getOption(
                    $ID, MetasList::getID(), $value['metas']
            ));
        }

        return array_values($metas);
    }

    /**
     * Get the meta keys disabled in the Metas List settings section, to be synced
     * between products and their translations.
     *
     * @param array $metas array of meta keys
     *
     * @return array extended meta keys array
     */
    public static function getDisabledProductMetaToCopy(array $metas = array())
    {
        foreach (static::getProductMetaToCopy(array(), false) as $group) {
            $metas = array_merge($metas, $group['metas']);
        }
        return apply_filters(HooksInterface::PRODUCT_DISABLED_META_SYNC_FILTER, array_values(array_diff($metas, static::getProductMetaToCopy())));
    }

    /**
     * Add the Fields Locker script.
     *
     * The script will disable editing of some product metas for product
     * translation
     *
     * @return bool false if the fields locker feature is disabled
     */
    public function addFieldsLocker()
    {
        if ('off' === Settings::getOption('fields-locker', \Hyyan\WPI\Admin\Features::getID(), 'on')) {
            return false;
        }

        $metas = static::getProductMetaToCopy();
        //change selector code to allow Product Attributes and Custom Product Attributes
        //to be separately locked or unlocked.
        $selectors[] = '.insert';
        if (in_array('_product_attributes', $metas)) {
            if (in_array('_custom_product_attributes', $metas)) {
                $selectors[] = '#product_attributes :input';
                $selectors[] = '#product_attributes .select2-selection';
            } else {
                //disable where is a taxonomy (custom taxonomy doesn't have this class)
                $selectors[] = '#product_attributes div.taxonomy :input';
                $selectors[] = '#product_attributes .select2-selection';
            }
        }
        //if only global product attributes are NOT synchronised, exclude them from selection
        elseif (in_array('_custom_product_attributes', $metas)) {
            $selectors[] = '#product_attributes div.woocommerce_attribute:not(.taxonomy) :input';
        }
        
        //filters hooked by Variable class to add locking for variations section
        $selectors = apply_filters(HooksInterface::FIELDS_LOCKER_SELECTORS_FILTER, $selectors);

        $jsID = 'product-fields-locker';
        $code = sprintf(
            'function hyyan_wpi_lockFields(){ '
            . '  var disabled = %s;'
            . 'var disabledSelectors = %s;'
            . 'var metaSelectors = "";'
            . 'for (var i = 0; i < disabled.length; i++) {'
            . '     metaSelectors += (","
                     + "." + disabled[i] + ","'
            . '       + "#" +disabled[i] + ","'
            . '      + "*[name^=\'"+disabled[i]+"\']"'
            . '      )'
            . '  }'
            . '  $(disabledSelectors + metaSelectors)'
            . '      .off("click")'
            . '      .on("click", function (e) {e.preventDefault()})'
            . '      .css({'
            . '          opacity: .5,'
            . '          \'pointer-events\': \'none\','
            . '          cursor: \'not-allowed\''
            . '      });'
            . '};'
            . 'hyyan_wpi_lockFields();'
            . '$(document).ajaxComplete(function(){'
            . '    hyyan_wpi_lockFields(); '
            . '});', json_encode($metas), !empty($selectors) ?
            json_encode(implode(',', $selectors)) :
            array(rand())
        );

        Utilities::jsScriptWrapper($jsID, $code);
    }

    /**
     * Sync the product select list.
     *
     * @param int $ID product type
     */
    protected function syncSelectedproductType($ID = null)
    {
        /*
         * First we add save_post action to save the product type
         * as post meta
         *
         * This is step is important so we can get the right product type
         */
        add_action('save_post', function ($_ID) {
            $product = wc_get_product($_ID); // get_product soft deprecated for wc_get_product
            if ($product && !isset($_GET['from_post'])) {
                $type = $product->get_type();
                update_post_meta($_ID, '_translation_porduct_type', $type);
            }
        });

        /*
         * If the _translation_porduct_type meta is
         * found then we add the js script to sync the product type select
         * list
         */
        if ($ID && ($type = get_post_meta($ID, '_translation_porduct_type'))) {
            add_action('admin_print_scripts', function () use ($type) {
                $jsID = 'product-type-sync';
                $code = sprintf(
                    '// <![CDATA[ %1$s'
                    . ' addLoadEvent(function () { %1$s'
                    . '  jQuery("#product-type option")'
                    . '     .removeAttr("selected");%1$s'
                    . '  jQuery("#product-type option[value=\"%2$s\"]")'
                    . '         .attr("selected", "selected");%1$s'
                    . '})'
                    . '// ]]>', PHP_EOL, $type[0]
                );

                Utilities::jsScriptWrapper($jsID, $code, false);
            }, 11);
        }
    }

    /**
     * Suppress "Invalid or duplicated SKU." error message when SKU syncronization is enabled.
     * TODO: related #73 if SKU synchronization is turned off on an existing shop,
     * there will be a lot of duplicated SKU error messages
     * Ideally when turning off SKU synchronisation:
     *  - duplicate SKU should be allowed on translations even though synchronization is off
     *    it's just that the SKU should not be forced to be a duplicate and not kept in sync
     *    if user has chosen to disable this synchronisation
     *  - a default non-duplicate SKU should be provided for new products and variations,
     *    for example by appending language code to existing SKU (user can change this later).
     *
     * @param bool   $sky_found    whether a product sku is unique
     * @param int    $product_id   id of affected product
     * @param string $sku          sku being tested
     * @return boolean  false if SKU sync is enabled, same as input otherwise
     */
    public function suppressInvalidDuplicatedSKUErrorMsg($sku_found, $product_id, $sku)
    {
        $metas = static::getProductMetaToCopy();

        if (in_array('_sku', $metas)) {
            return false;
        } else {
            return $sku_found;
        }
    }
}
