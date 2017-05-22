<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

/**
 * Duplicator.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Duplicator
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        add_action('woocommerce_product_duplicate', array(
            $this, 'unlinkOrginalProductTranslations',
        ), 10, 3);

        add_action('woocommerce_product_duplicate_before_save', array(
            $this, 'unlinkCopiedVariations',
        ), 10, 3);
    }

    /**
     * Unlink new variation copies from previous variation
     *
     * @param WC_Product $child_duplicate  the new variation
     * @param WC_Product $child  the original variation
     */
    public function unlinkCopiedVariations($child_duplicate, $child)
    {
        //clear the reference to previous variation
        if ($child_duplicate instanceof \WC_Product_Variation) {
            //at this point is not saved, no id, so remove the key reference
            //(there is no alternative after-save filter)
            $child_duplicate->delete_meta_data(Variation::DUPLICATE_KEY);
            //later the existing code will get false checking for DUPLICATE_KEY and reset it to the new variation id
        }
    }
    
    /**
     * Unlink original product translations from the new copy.
     *
     * @param WC_Product $duplicate  the new product
     * @param WC_Product $product  the original product
     *
     */
    public function unlinkOrginalProductTranslations($duplicate, $product)
    {
        global $polylang;
        //deprecated in Polylang 1.8 [currently 2.1.4], use PLL()->model->post->delete_translation() instead
        //$polylang->model->delete_translation('post', $ID);
        $polylang->model->post->delete_translation($duplicate->get_id());
    }
}
