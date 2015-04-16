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
 * Utilities
 *
 * Some helper methods
 *
 * @author Hyyan
 */
final class Utilities
{

    /**
     * Get the translations IDS of the given product ID
     *
     * @global \Polylang $polylang
     *
     * @param integer $ID             the product ID
     * @param boolean $excludeDefault ture to exclude defualt language
     *
     * @return array associative array with language code as key and ID of translations
     *               as value.
     */
    public static function getProductTranslationsArrayByID($ID, $excludeDefault = false)
    {
        global $polylang;
        $IDS = $polylang->model->get_translations('post', $ID);
        if (true === $excludeDefault) {
            unset($IDS[pll_default_language()]);
        }

        return $IDS;
    }

    /**
     * Get the translations IDS of the given product object
     *
     * @see \Hyyan\WPI\getProductTranslationsByID()
     *
     * @param \WC_Product $product        the product object
     * @param boolean     $excludeDefault ture to exclude defualt language
     *
     * @return array associative array with language code as key and ID of translations
     *               as value.
     */
    public static function getProductTranslationsArrayByObject(\WC_Product $product, $excludeDefault = false)
    {
        return static::getProductTranslationsByID($product->id, $excludeDefault);
    }

    /**
     * Get porduct translation by ID
     *
     * @param integer $ID   the porduct ID
     * @param string  $slug the language slug
     *
     * @return \WC_Product|false product translation if found , false if the
     *                           given ID is not for product
     */
    public static function getProductTranslationByID($ID, $slug = '')
    {
        $product = wc_get_product($ID);
        if (!$product) {
            return false;
        }

        return static::getProductTranslationByObject($product, $slug);
    }

    /**
     * Get product translation by object
     *
     * @param \WC_Product $product the product to use to retirve translation
     * @param string      $slug    the language slug
     *
     * @return \WC_Product product translation or same prodcut if translaion not found
     */
    public static function getProductTranslationByObject(\WC_Product $product, $slug = '')
    {
        $productTranslationID = pll_get_post($product->id, $slug);

        if ($productTranslationID) {
            $translated = wc_get_product($productTranslationID);
            $product = $translated ? $translated : $product;
        }

        return $product;
    }

    /**
     * Get polylang langauge entity
     *
     * @global \Polylang $polylang
     *
     * @param string $slug the language slug
     *
     * @return \PLL_Language|false language entity in success , false otherwise
     */
    public static function getLanguageEntity($slug)
    {
        global $polylang;

        $langs = $polylang->model->get_languages_list();

        foreach ($langs as $lang) {
            if ($lang->slug == $slug) {
                return $lang;
            }
        }

        return false;
    }

    /**
     * Get the translations IDS of the given term ID
     *
     * @global \Polylang $polylang
     *
     * @param integer $ID             term id
     * @param boolean $excludeDefault ture to exclude defualt language
     *
     * @return array associative array with language code as key and ID of translations
     *               as value.
     */
    public static function getTermTranslationsArrayByID($ID, $excludeDefault = false)
    {
        global $polylang;
        $IDS = $polylang->model->get_translations('term', $ID);
        if (true === $excludeDefault) {
            unset($IDS[pll_default_language()]);
        }

        return $IDS;
    }

}
