<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Utilities;

/**
 * Categories.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Categories implements TaxonomiesInterface
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        /* Handle product category custom fiedlds */
        add_action(
                'product_cat_add_form_fields', array($this, 'copyProductCatCustomFields'), 11
        );
        add_action(
                'created_term', array($this, 'syncProductCatCustomFields'), 11, 3
        );
        add_action(
                'edit_term', array($this, 'syncProductCatCustomFields'), 11, 3
        );
    }

    /**
     * Sync Product Category Custom Fields.
     *
     * Keep product categories translation synced
     *
     * @param int    $termID   the term id
     * @param int    $ttID     ?
     * @param string $taxonomy the taxonomy name
     */
    public function syncProductCatCustomFields($termID, $ttID = '', $taxonomy = '')
    {
        $verified = 'product_cat' === $taxonomy && current_user_can('edit_term', absint($termID)) && (
            (isset($_POST['_wpnonce_add-tag']) && is_string($_POST['_wpnonce_add-tag']) &&
                wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_add-tag'])), 'add-tag')) ||
            (isset($_POST['_wpnonce']) && is_string($_POST['_wpnonce']) &&
                wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'update-tag_'.absint($termID)))
        );
        if ($verified && isset($_POST['display_type']) && is_string($_POST['display_type'])) {
            $this->doSyncProductCatCustomFields(
                    $termID, 'display_type', sanitize_key(wp_unslash($_POST['display_type']))
            );
        }
        if ($verified && isset($_POST['product_cat_thumbnail_id']) &&
                (is_int($_POST['product_cat_thumbnail_id']) || is_string($_POST['product_cat_thumbnail_id']))) {
            $thumbnail = filter_var(wp_unslash($_POST['product_cat_thumbnail_id']), FILTER_VALIDATE_REGEXP,
                    array('options' => array('regexp' => '/^[0-9]*$/D')));
            if ($thumbnail !== false) {
                $this->doSyncProductCatCustomFields($termID, 'thumbnail_id', absint($thumbnail));
            }
        }

        if ('product_cat' === $taxonomy) {
            /* Allow other plugins to check for category custom fields */
            do_action(
                    HooksInterface::PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS, $this, $termID, $taxonomy
            );
        }
    }

    /**
     * Copy product Category Custom fields.
     *
     * Copy the category custom fields from orginal category to its translations
     * when we start adding new category translation
     *
     * @return bool false if this action must not be executed
     */
    public function copyProductCatCustomFields()
    {

        /* We sync custom fields only for translation */
        if (Utilities::getRequestLanguage() === '') {
            return false;
        }

        $ID = Utilities::getRequestInteger('from_tag');
        if (!$ID) {
            return false;
        }
        $type = get_term_meta($ID, 'display_type', true);
        $thumbID = absint(get_term_meta($ID, 'thumbnail_id', true));
        $image = $thumbID ?
                wp_get_attachment_thumb_url($thumbID) :
                wc_placeholder_img_src(); ?>
        <script type="text/javascript">
            jQuery(function ($) {
                $(<?php echo wp_json_encode('#display_type option[value="' . $type . '"]', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)
                        .prop("selected", true);
                $('#product_cat_thumbnail img').attr('src', <?php echo wp_json_encode($image, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
                $('#product_cat_thumbnail_id').val('<?php echo absint($thumbID); ?>');
        <?php if ($thumbID): ?>
                    $('.remove_image_button').show();
        <?php endif; ?>
            });
        </script>
        <?php

        /* Allow other plugins to check for category custom fields */
        do_action(
                HooksInterface::PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS, $ID
        );
    }
    /**
     * Do sync category custom fields.
     *
     * @param int    $ID    the term ID
     * @param string $key   the key
     * @param mixed  $value the value
     */
    public function doSyncProductCatCustomFields($ID, $key, $value = '')
    {
        $translations = Utilities::getTermTranslationsArrayByID($ID);

        foreach ($translations as $translation) {
			      update_term_meta( $translation, $key, $value );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getNames()
    {
        return array('product_cat');
    }
}
