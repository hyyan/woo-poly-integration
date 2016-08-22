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
 * Utilities.
 *
 * Some helper methods
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
final class Utilities
{
    /**
     * Get the translations IDS of the given product ID.
     *
     * @global \Polylang $polylang
     *
     * @param int  $ID             the product ID
     * @param bool $excludeDefault true to exclude default language
     *
     * @return array associative array with language code as key and ID of translations
     *               as value
     */
    public static function getProductTranslationsArrayByID($ID, $excludeDefault = false)
    {
        global $polylang;
        $IDS = PLL()->model->post->get_translations($ID);
        if (true === $excludeDefault) {
            unset($IDS[pll_default_language()]);
        }

        return $IDS;
    }

    /**
     * Get the translations IDS of the given product object.
     *
     * @see \Hyyan\WPI\getProductTranslationsByID()
     *
     * @param \WC_Product $product        the product object
     * @param bool        $excludeDefault true to exclude default language
     *
     * @return array associative array with language code as key and ID of translations
     *               as value
     */
    public static function getProductTranslationsArrayByObject(\WC_Product $product, $excludeDefault = false)
    {
        return static::getProductTranslationsArrayByID($product->id, $excludeDefault);
    }

    /**
     * Get product translation by ID.
     *
     * @param int    $ID   the product ID
     * @param string $slug the language slug
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
     * Get product translation by object.
     *
     * @param \WC_Product $product the product to use to retrive translation
     * @param string      $slug    the language slug
     *
     * @return \WC_Product product translation or same product if translaion not found
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
     * Get polylang language entity.
     *
     * @global \Polylang $polylang
     *
     * @param string $slug the language slug
     *
     * @return \PLL_Language|false language entity on success , false otherwise
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
     * Get the translations IDS of the given term ID.
     *
     * @global \Polylang $polylang
     *
     * @param int  $ID             term id
     * @param bool $excludeDefault true to exclude default language
     *
     * @return array associative array with language code as key and ID of translations
     *               as value
     */
    public static function getTermTranslationsArrayByID($ID, $excludeDefault = false)
    {
        global $polylang;
        $IDS = PLL()->model->term->get_translations($ID);
        if (true === $excludeDefault) {
            unset($IDS[pll_default_language()]);
        }

        return $IDS;
    }

    /**
     * Get current url.
     *
     * Get the full url for current location
     *
     * @return string
     */
    public static function getCurrentUrl()
    {
        return (is_ssl() ? 'https://' : 'http://')
                .$_SERVER['HTTP_HOST']
                .$_SERVER['REQUEST_URI'];
    }

    /**
     * Js Script wrapper.
     *
     * Warp the given js code
     *
     * @param string $ID     the script ID
     * @param string $code   js code
     * @param bool   $jquery true to include jQuery ready wrap , false otherwise
     *                       (true by default)
     * @param bool   $return true to return wrapped code , false othwerwise
     *                       false by default
     *
     * @return string wrapped js code if return is true
     */
    public static function jsScriptWrapper($ID, $code, $jquery = true, $return = false)
    {
        $result = '';
        $prefix = 'hyyan-wpi-';
        $header = sprintf('<script type="text/javascript" id="%s">', $prefix.$ID);
        $footer = '</script>';

        if (true === $jquery) {
            $result = sprintf(
                    "%s\n jQuery(document).ready(function ($) {\n %s \n});\n %s \n", $header, $code, $footer
            );
        } else {
            $result = sprintf(
                    "%s\n %s \n%s", $header, $code, $footer
            );
        }

        if (false === $return) {
            echo $result;
        } else {
            return $result;
        }
    }

    /**
     * Check WooCommerce version.
     *
     * Check if you are running a specified WooCommerce version (or higher)
     *
     * @param string $version Version to check agains. (Default: 2.6)
     *
     * @return bool true if running version is equal or higher, false otherwise
     */
    public static function woocommerceVersionCheck($version = '2.6')
    {
        global $woocommerce;

        if (version_compare($woocommerce->version, $version, '>=')) {
            return true;
        }

        return false;
    }
}
