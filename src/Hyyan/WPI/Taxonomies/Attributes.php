<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\Utilities;

/**
 * Attributes
 *
 * @author Hyyan
 */
class Attributes implements TaxonomiesInterface
{

    /**
     * Construct object
     */
    public function __construct()
    {
        /* Manage attributes label translation */
        add_action(
                'init'
                , array($this, 'manageAttrLablesTranslation')
                , 11
                , 2
        );
        add_filter(
                'woocommerce_attribute_label'
                , array($this, 'translateAttrLable')
        );
        add_action(
                'admin_print_scripts'
                , array($this, 'addAttrsTranslateButton')
                , 100
        );
    }

    /**
     * Make all attributes lables managed by polylang string translation
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
     *
     * @return boolean false if polylang or woocommerce can not be found
     */
    public function manageAttrLablesTranslation()
    {
        global $polylang, $woocommerce;

        if (!$polylang || !$woocommerce) {
            return false;
        }

        $attrs = wc_get_attribute_taxonomies();
        $section = 'Woocommerce Attributes';
        foreach ($attrs as $attr) {
            pll_register_string(
                    $attr->attribute_label
                    , $attr->attribute_label
                    , $section
            );
        }
    }

    /**
     * Translate the attribute label
     *
     * @param string $label original attribute label
     *
     * @return string translated attribute label
     */
    public function translateAttrLable($label)
    {
        return pll__($label);
    }

    /**
     * Add a button before the attributes table to let the user know how to
     * translate the attributes labels
     *
     * @global type $pagenow
     *
     * @return boolean false if not attributes page
     */
    public function addAttrsTranslateButton()
    {
        global $pagenow;
        if ($pagenow !== 'edit.php') {
            return false;
        }

        $isAttrPage = isset($_GET['page']) &&
                esc_attr($_GET['page']) === 'product_attributes';

        if (!$isAttrPage) {
            return false;
        }

        $jsID = 'attrs-label-translation-button';
        $code = sprintf(
                '$("<a href=\'%s\' class=\'button button-primary button-large\'>%s</a><br><br>")'
                . ' .insertBefore(".attributes-table");'
                , admin_url('options-general.php?page=mlang&tab=strings&group=Woocommerce+Attributes')
                , __('Translate Attributes Lables', 'woo-poly-integration')
        );

        Utilities::jsScriptWrapper($jsID, $code);
    }

    /**
     * @{inheritdoc}
     */
    public function getNames()
    {
        return wc_get_attribute_taxonomy_names();
    }

}
