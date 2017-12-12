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
 * Ajax.
 *
 * Handle Ajax
 *
 * @author Marian Kadanka <marian.kadanka@gmail.com>
 */
class Ajax
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter('pll_home_url_white_list', array( $this, 'pll_home_url_white_list' ));
    }

    /**
     * Add WooCommerce class-wc-ajax.php to the Polylang home_url white list
     *
     * @param array $white_list Polylang home_url white list
     *
     * @return array filtered white list
     */
    public function pll_home_url_white_list($white_list)
    {
        $white_list[] = array( 'file' => 'class-wc-ajax.php' );
        return $white_list;
    }
}
