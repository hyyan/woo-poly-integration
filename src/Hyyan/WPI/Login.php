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
 * Login.
 *
 * Handle login
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Login
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter(
                'woocommerce_login_redirect', array($this, 'getLoginRedirectPermalink'), 10, 2
        );
    }

    /**
     * Find the correct login redirect permalink.
     *
     * @param string $to redirect url
     *
     * @return string redirect url
     */
    public function getLoginRedirectPermalink($to)
    {
        $ID = url_to_postid($to);
        $translatedID = pll_get_post($ID);

        if ($translatedID) {
            return get_permalink($translatedID);
        }

        return $to;
    }
}
