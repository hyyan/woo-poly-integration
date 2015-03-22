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
