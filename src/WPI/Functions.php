<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WPI;

/**
 * Get polylang langauge entity
 *
 * @global \Polylang $polylang
 *
 * @param string $slug the lang slug
 *
 * @return \PLL_Language|false lang entity in success , false otherwise
 */
function getLanguageEntity($slug)
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
 * Get order language
 *
 * @param integer $id the order id
 *
 * @return string the roder language
 */
function getOrderLanguage($id)
{
    return Order::getOrderLangauge($id);
}

/**
 * Get product translation by object
 *
 * @param \WC_Product $product the product to use to retirve translation
 * @param string $slug the language slug
 *
 * @return \WC_Product product translation or same prodcut if translaion not found
 */
function getProductTranslationByObject(\WC_Product $product, $slug = '')
{
    $productTranslationID = pll_get_post($product->id, $slug = '');

    if ($productTranslationID) {
        $translated = wc_get_product($productTranslationID);
        $product = $translated ? $translated : $product;
    }

    return $product;
}

/**
 * Get porduct translation by ID
 *
 * @param integer $id the porduct id
 * @param string $slug the language slug
 *
 * @return \WC_Product|false product translation if found or the default product
 *                           false otherwise
 */
function getProductTranslationByID($id, $slug = '')
{
    $product = wc_get_product($id);
    if (!$product)
        return false;

    return getProductTranslationByObject($product);
}
