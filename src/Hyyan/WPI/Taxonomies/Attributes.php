<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\Utilities;

/**
 * Attributes.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Attributes implements TaxonomiesInterface
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        /* Manage attributes label translation */
        add_action(
                'init', array($this, 'manageAttrLablesTranslation'), 11, 2
        );
        add_filter(
                'woocommerce_attribute_label', array($this, 'translateAttrLable')
        );
        add_action(
                'admin_print_scripts', array($this, 'addAttrsTranslateLinks'), 100
        );
    }

    /**
     * Make all attributes lables managed by polylang string translation.
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
     *
     * @return bool false if polylang or woocommerce can not be found
     */
    public function manageAttrLablesTranslation()
    {
        global $polylang, $woocommerce;

        if (!$polylang || !$woocommerce) {
            return false;
        }

        $attrs = wc_get_attribute_taxonomies();
        $section = __('Woocommerce Attributes', 'woo-poly-integration');
        foreach ($attrs as $attr) {
            pll_register_string(
                    $attr->attribute_label, $attr->attribute_label, $section
            );
        }
    }

    /**
     * Translate the attribute label.
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
     * translate the attributes labels.
     *
     * @global type $pagenow
     *
     * @return bool false if not attributes page
     */
    public function addAttrsTranslateLinks()
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

        $stringTranslationURL = add_query_arg(array(
            'page' => 'mlang_strings',
            //'tab' => 'strings',
            'group' => __('Woocommerce Attributes', 'woo-poly-integration'),
        ), admin_url('admin.php'));

        /* Add attribute translate button */
        $buttonID = 'attrs-label-translation-button';
        $buttonCode = sprintf(
                '$("<a href=\'%s\' class=\'button button-primary button-large\'>%s</a><br><br>")'
                .' .insertBefore(".attributes-table");', $stringTranslationURL, __('Translate Attributes Lables', 'woo-poly-integration')
        );

        /* Add attribute translate search link */
        $searchLinkID = 'attr-label-translate-search-link';
        $searchLinkCode = sprintf(
                "$('.attributes-table .row-actions').each(function () {\n"
                .' var $this = $(this);'
                .' var attrName = $this.parent().find("strong a").text();'
                .' var attrTranslateUrl = "%s&s="+attrName ;'
                .' var attrTranslateHref = '
                .'     "<span class=\'translate\'>"'
                .'     + "| "'
                .'     + "<a href=\'"+attrTranslateUrl+"\'>%s</a>"'
                .'     + "</span>";'
                .' $this.append(attrTranslateHref);'
                ."\n});\n", $stringTranslationURL, __('Translate', 'woo-poly-integration')
        );

        /* Output code */
        Utilities::jsScriptWrapper($buttonID, $buttonCode);
        Utilities::jsScriptWrapper($searchLinkID, $searchLinkCode);
    }

    /**
     * {@inheritdoc}
     */
    public static function getNames()
    {
        return wc_get_attribute_taxonomy_names();
    }
}
