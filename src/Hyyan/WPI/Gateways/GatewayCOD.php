<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Gateways;

use Hyyan\WPI\Utilities;

/**
 * Gateways Cash on Delivery.
 *
 * Handle Payment Gateways Cash on Delivery
 *
 * @author Antonio de Carvalho <decarvalhoaa@gmail.com>
 */
class GatewayCOD extends \WC_Gateway_COD
{
    /**
     * Output for the order received page.
     *
     * Note 1: The difference to WC_Gateway_COD is that we use pll__() or __()
     * before passing the string through wptexturize() and wpautop().
     *
     * Note 2: We wrap the intructions strings with wp_kses_post() to sanitize
     * content for allowed HTML tags like BACS does it.
     */
    public function thankyou_page()
    {
        if ($this->instructions) {
            echo wpautop(wptexturize(wp_kses_post(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))));
        }
    }

    /**
     * Add content to the WC emails.
     *
     * Note: The difference from WC_Gateway_COD is that we use __() before
     * passing the string through wptexturize() and wpautop().
     *
     * @param WC_Order $order
     * @param bool     $sent_to_admin
     * @param bool     $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && !$sent_to_admin && 'cod' === Utilities::get_payment_method($order)) {
            echo wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))).PHP_EOL;
        }
    }
}
