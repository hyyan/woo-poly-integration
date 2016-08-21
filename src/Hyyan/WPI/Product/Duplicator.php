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
        add_action('woocommerce_duplicate_product', array(
            $this, 'unlinkOrginalProductTranslations',
        ));

        add_action('woocommerce_duplicate_product_capability', array(
            $this, 'disableDuplicateForVariables',
        ));
    }

    /**
     * Unlink orginal product translations from the new copy.
     *
     * @global \Polylang $polylang
     *
     * @param int $ID the new product ID
     */
    public function unlinkOrginalProductTranslations($ID)
    {
        global $polylang;
        $polylang->model->delete_translation('post', $ID);
    }

    /**
     * Disable duplicate capability for variables.
     *
     * @param string $capability
     *
     * @return bool|srting false if should be disables , passed capability
     *                     otherwise
     */
    public function disableDuplicateForVariables($capability)
    {
        $screen = get_current_screen();

        if ($screen && $screen->post_type !== 'product') {
            return $capability;
        }

        $ID = get_the_ID();
        if (wc_get_product($ID) instanceof \WC_Product_Variable) {
            return false;
        }

        return $capability;
    }
}
