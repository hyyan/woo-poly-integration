<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;

/**
 * Variable
 *
 * Handle Variable Products
 *
 * @author Hyyan
 */
class Variable
{

    /**
     * Construct object
     */
    public function __construct()
    {

        add_action('save_post', array($this, 'duplicateVariations'), 10, 3);
        add_action(
                'wp_ajax_woocommerce_remove_variations'
                , array($this, 'removeVariations')
                , 9
        );

        // extend meta list to include variation meta
        add_filter(
                HooksInterface::PRODUCT_META_SYNC_FILTER
                , array($this, 'extendProductMetaList')
        );
        /* Extend selectors list to include variation meta */
        add_filter(
                HooksInterface::FIELDS_LOCKER_SELECTORS_FILTER
                , array($this, 'extendFieldsLockerSelectors')
        );

        if (is_admin()) {
            $this->handleVariableLimitation();
            $this->shouldDisableLangSwitcher();
        }
    }

    /**
     * Translate Variation for given variable product
     *
     * @param integer  $ID     product variable ID
     * @param \WP_Post $post   Product Post
     * @param boolean  $update true if update , false otherwise
     *
     * @return boolean
     */
    public function duplicateVariations($ID, \WP_Post $post, $update)
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        global $pagenow;
        if (!in_array($pagenow, array('post.php', 'post-new.php'))) {
            return false;
        }

        $product = wc_get_product($ID);

        if (!$product) {
            return false;
        }

        $from = null;

        if (pll_get_post_language($ID) == pll_default_language()) {
            $from = $product;
        } else {

            if (isset($_GET['from_post'])) {
                /*
                 * This check will make sure that variation , will be
                 * created for brand new products which are not saved yet by user
                 */
                $from = Utilities::getProductTranslationByID(
                                esc_attr($_GET['from_post'])
                                , pll_default_language()
                );
            } else {
                $from = Utilities::getProductTranslationByObject(
                                $product
                                , pll_default_language()
                );
            }
        }

        if (!($from instanceof \WC_Product_Variable)) {
            return false;
        }

        $langs = pll_languages_list();

        foreach ($langs as $lang) {
            $variation = new Variation(
                    $from
                    , Utilities::getProductTranslationByObject($product, $lang)
            );

            remove_action('save_post', array($this, __FUNCTION__), 10);

            $variation->duplicate();

            add_action('save_post', array($this, __FUNCTION__), 10, 3);
        }
    }

    /**
     * Remove variatoins related to current removed variation
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
     * Extend the product meta list that must by synced
     *
     * @param array $metas current meta list
     *
     * @return array
     */
    public function extendProductMetaList(array $metas)
    {

        $metas['Variables'] = array(
            'name' => __('Variables Metas', 'woo-poly-integration'),
            'desc' => __('Variables Metas', 'woo-poly-integration'),
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
            )
        );

        return $metas;
    }

    /**
     * Extend the fields locker selectors
     *
     * Extend the fields locker selectors to lock variation fields for translation
     *
     * @param array $selectors
     *
     * @return array
     */
    public function extendFieldsLockerSelectors(array $selectors)
    {

        $selectors[] = '#variable_product_options :input';

        return $selectors;
    }

    /**
     * Handle variation limitation about defualt language
     *
     * @global string $pagenow current page name
     *
     * @return boolean false if this is not new variable product
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
                    . '     title       : "%s" ,'
                    . '     content     : "%s" ,'
                    . '     defaultLang : "%s"'
                    . '};'
                    , __('Wrong Language For Variable Product', 'woo-poly-integration')
                    , __("Variable product must be created in the default language first or things will get messy. <br> <a href='https://github.com/hyyan/woo-poly-integration/tree/master#what-you-need-to-know-about-this-plugin' target='_blank'>Read more , to know why</a>", "woo-poly-integration")
                    , pll_default_language()
            );

            Utilities::jsScriptWrapper($jsID, $code, false);
        });

        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script("jquery-effects-core");
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script(
                    'woo-poly-variables'
                    , plugins_url('public/js/Variables.js', Hyyan_WPI_DIR)
                    , array('jquery', 'jquery-ui-core', 'jquery-ui-dialog')
                    , \Hyyan\WPI\Plugin::getVersion()
                    , true
            );
        }, 100);
    }

    /**
     * Check if we have to disable the language switcher in the polylang setting
     * page
     */
    public function shouldDisableLangSwitcher()
    {
        add_action('current_screen', function () {

            $screen = get_current_screen();
            if ($screen->id !== 'settings_page_mlang') {
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
                        . '.css({'
                        . '     "opacity": .5,'
                        . '     "pointer-events": "none"'
                        . '});'
                        . ' $("#options-lang").prepend('
                        . '     "<p class=\'update-nag\'>%s</p>"'
                        . ');'
                        , __('You can not change the default language ,Becuase you are using variable products', 'woo-poly-integration')
                );
                Utilities::jsScriptWrapper($jsID, $code);
            }, 100);
        });
    }

}
