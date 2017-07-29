<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Widgets;

/**
 * SearchWidget.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class SearchWidget
{
    /**
     * Constuct object.
     */
    public function __construct()
    {
        add_filter('get_product_search_form', array(
            $this, 'fixSearchForm',
        ));
    }

    /**
     * Fix search form to avoid duplicated products results.
     *
     * @global \Polylang $polylang
     *
     * @param string $form
     *
     * @return string modified form
     */
    public function fixSearchForm($form)
    {
        global $polylang;

        if ($form) {
            if ((isset($polylang->links_model)) && ($polylang->links_model->using_permalinks)) {

                /* Take care to modify only the url in the <form> tag */
                preg_match('#<form.+>#', $form, $matches);
                $old = reset($matches);
                $new = preg_replace(
                        '#'.$polylang->links_model->home.'\/?#', $polylang->curlang->search_url, $old
                );

                $form = str_replace($old, $new, $form);
            } else {
                if (isset($polylang->curlang, $polylang->curlang->slug)) {
                    $form = str_replace(
                        '</form>', '<input type="hidden" name="lang" value="'
                        .esc_attr($polylang->curlang->slug)
                        .'" /></form>', $form
                );
                }
            }
        }

        return $form;
    }
}
