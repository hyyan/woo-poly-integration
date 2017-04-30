<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Shortcodes;

/**
 * Shortcode.
 *
 * @author Jonathan Moore <jonathan.moore@bcs.org>
 */
class Shortcodes
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter('woocommerce_shortcode_products_query', array(
            $this, 'fixProductsQuery',
        ), 10, 3);
    }

    /**
     * Fix shortcodes to include language filter.
     *
     * @global \Polylang $polylang
     *
     * @param array $query_args
     * @param array $atts
     * @param string $loop_name
     *
     * @return string modified form
     */
    public function fixProductsQuery($query_args, $atts, $loop_name)
    {
        global $polylang;
				if (function_exists('pll_current_language')) {
					$query_args['lang'] = isset($query_args['lang']) ? $query_args['lang'] : pll_current_language();
					return $query_args;
				}
    }
}
