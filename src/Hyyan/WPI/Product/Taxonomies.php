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
 * Taxonomies
 *
 * Handle taxonomies translation
 *
 * @author Hyyan
 */
class Taxonomies
{

    /**
     * Construct object
     */
    public function __construct()
    {
        // manage taxonomies translation
        add_filter(
                'pll_get_taxonomies'
                , array($this, 'manageTaxonomiesTranslation')
        );

        // manage attributes label translation
        add_action(
                'init'
                , array($this, 'manageAttrLablesTranslation')
                , 11, 2
        );
        add_filter(
                'woocommerce_attribute_label'
                , array($this, 'translateAttrLable')
        );

        add_action('admin_print_scripts', array($this, 'addAttsTranslateButton'), 100);

        // extend meta list to include attributes
        add_filter(
                HooksInterface::PRODUCT_META_SYNC_FILTER
                , array($this, 'extendProductMetaList')
        );

        // handle product category custom fiedlds
        add_action(
                'product_cat_add_form_fields'
                , array($this, 'copyProductCatCustomFields')
                , 11
        );
        add_action(
                'created_term'
                , array($this, 'syncProductCatCustomFields')
                , 11
                , 3
        );
        add_action(
                'edit_term'
                , array($this, 'syncProductCatCustomFields')
                , 11
                , 3
        );
    }

    /**
     * Notifty polylang about product taxonomies
     *
     * @param array $taxonomies array of cutoms taxonomies managed by polylang
     *
     * @return array
     */
    public function manageTaxonomiesTranslation($taxonomies)
    {
        $new = $this->getTaxonomies();
        $options = get_option('polylang');
        $taxs = $options['taxonomies'];
        $update = false;
        foreach ($new as $tax) {
            if (!in_array($tax, $taxs)) {
                $options['taxonomies'][] = $tax;
                $update = true;
            }
        }
        if ($update) {
            update_option('polylang', $options);
        }

        return array_merge($taxonomies, $new);
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
     * Extend the product meta list that must by synced
     *
     * @param array $metas current meta list
     *
     * @return array
     */
    public function extendProductMetaList(array $metas)
    {
        return array_merge($metas, array(
            '_product_attributes',
            '_default_attributes',
        ));
    }

    /**
     * Add a button before the attributes table to let the user know how to
     * translate the attributes labels
     *
     * @global type $pagenow
     *
     * @return boolean false if not attributes page
     */
    public function addAttsTranslateButton()
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
                , admin_url('options-general.php?page=mlang&tab=strings&s&group=Woocommerce+Attributes&paged=1')
                , __('Translate Attributes Lables', 'woo-poly-integration')
        );

        Utilities::jsScriptWrapper($jsID, $code);
    }

    /**
     * Sync Product Category Custom Fields
     *
     * Keep product categories translation synced
     *
     * @param integer $termID   the term id
     * @param integer $ttID     ?
     * @param string  $taxonomy the taxonomy name
     */
    public function syncProductCatCustomFields($termID, $ttID = '', $taxonomy = '')
    {
        if (isset($_POST['display_type']) && 'product_cat' === $taxonomy) {
            $this->doSyncProductCatCustomFields(
                    $termID
                    , 'display_type'
                    , esc_attr($_POST['display_type'])
            );
        }
        if (isset($_POST['product_cat_thumbnail_id']) && 'product_cat' === $taxonomy) {
            $this->doSyncProductCatCustomFields(
                    $termID
                    , 'thumbnail_id'
                    , absint($_POST['product_cat_thumbnail_id'])
            );
        }

        if ('product_cat' === $taxonomy) {
            /* Allow other plugins to check for category custom fields */
            do_action(
                    HooksInterface::PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS
                    , $this, $termID, $taxonomy
            );
        }
    }

    /**
     * Copy product Category Custom fields
     *
     * Copy the category custom fields from orginal category to its translations
     * when we start adding new category translation
     *
     * @return boolean false if this action must not be executed
     */
    public function copyProductCatCustomFields()
    {

        /* We sync custom fields only for translation */
        if (!(isset($_GET['from_tag']) && isset($_GET['new_lang']))) {
            return false;
        }

        $ID = esc_attr($_GET['from_tag']);
        $type = get_woocommerce_term_meta($ID, 'display_type', true);
        $thumbID = absint(get_woocommerce_term_meta($ID, 'thumbnail_id', true));
        $image = $thumbID ?
                wp_get_attachment_thumb_url($thumbID) :
                wc_placeholder_img_src();
        ?>
        <script type="text/javascript">
            jQuery('document').ready(function ($) {
                $('#display_type option[value="<?php echo $type ?>"]')
                        .attr("selected", true);
                $('#product_cat_thumbnail img').attr('src', '<?php echo $image; ?>');
                $('#product_cat_thumbnail_id').val('<?php echo $thumbID; ?>');
        <?php if ($thumbID): ?>
                    $('.remove_image_button').show();
        <?php endif; ?>
            });
        </script>
        <?php
        /* Allow other plugins to check for category custom fields */
        do_action(
                HooksInterface::PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS
                , $ID
        );
    }

    /**
     * Do sync category custom fields
     *
     * @param integer $ID    the term ID
     * @param string  $key   the key
     * @param mixed   $value the value
     */
    public function doSyncProductCatCustomFields($ID, $key, $value = '')
    {
        $translations = Utilities::getTermTranslationsArrayByID($ID);

        foreach ($translations as $translation) {
            update_woocommerce_term_meta($translation, $key, $value);
        }
    }

    /**
     * Get taxonomies array
     *
     * @return array
     */
    protected function getTaxonomies()
    {
        return array_merge(wc_get_attribute_taxonomy_names(), array(
            'product_cat',
            'product_tag',
            'product_shipping_class'
        ));
    }

}
