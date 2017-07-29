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
 * Gateways BACS.
 *
 * Handle Payment Gateways BACS
 *
 * @author Antonio de Carvalho <decarvalhoaa@gmail.com>
 */
class GatewayBACS extends \WC_Gateway_BACS
{
    /**
     * Output for the order received page.
     *
     * Note: The difference to WC_Gateway_BACS is that we use pll__() or __()
     * before passing the string through wptexturize() and wpautop().
     *
     * @param int $order_id
     */
    public function thankyou_page($order_id)
    {
        if ($this->instructions) {
            echo wpautop(wptexturize(wp_kses_post(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))));
        }
        $this->bank_details($order_id);
    }

    /**
     * Add content to the WC emails.
     *
     * Note: The difference from WC_Gateway_BACS is that we use __() before
     * passing the string through wptexturize() and wpautop().
     *
     * @param WC_Order $order
     * @param bool     $sent_to_admin
     * @param bool     $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if (!$sent_to_admin && 'bacs' === Utilities::get_payment_method($order) && $order->has_status('on-hold')) {
            if ($this->instructions) {
                echo wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))).PHP_EOL;
            }
            $this->bank_details(Utilities::get_orderid($order));
        }
    }

    /**
     * Get bank details and place into a list format.
     * Updated from wooCommerce 3.1
     *
     * Note: Since this is declared as a private function in WC_Gateway_BACS, it needs
     * to be copied here 1:1
     *
     * @param int $order_id
     */
    private function bank_details($order_id = '')
    {
        if (empty($this->account_details)) {
            return;
        }

        // Get order and store in $order
        $order        = wc_get_order($order_id);

        // Get the order country and country $locale
        $country    = $order->get_billing_country();
        $locale        = $this->get_country_locale();

        // Get sortcode label in the $locale array and use appropriate one
        $sortcode = isset($locale[ $country ]['sortcode']['label']) ? $locale[ $country ]['sortcode']['label'] : __('Sort code', 'woocommerce');

        $bacs_accounts = apply_filters('woocommerce_bacs_accounts', $this->account_details);

        if (! empty($bacs_accounts)) {
            $account_html = '';
            $has_details  = false;

            foreach ($bacs_accounts as $bacs_account) {
                $bacs_account = (object) $bacs_account;

                if ($bacs_account->account_name) {
                    $account_html .= '<h3 class="wc-bacs-bank-details-account-name">' . wp_kses_post(wp_unslash($bacs_account->account_name)) . ':</h3>' . PHP_EOL;
                }

                $account_html .= '<ul class="wc-bacs-bank-details order_details bacs_details">' . PHP_EOL;

                // BACS account fields shown on the thanks page and in emails
                $account_fields = apply_filters('woocommerce_bacs_account_fields', array(
                    'bank_name' => array(
                        'label' => __('Bank', 'woocommerce'),
                        'value' => $bacs_account->bank_name,
                    ),
                    'account_number' => array(
                        'label' => __('Account number', 'woocommerce'),
                        'value' => $bacs_account->account_number,
                    ),
                    'sort_code'     => array(
                        'label' => $sortcode,
                        'value' => $bacs_account->sort_code,
                    ),
                    'iban'          => array(
                        'label' => __('IBAN', 'woocommerce'),
                        'value' => $bacs_account->iban,
                    ),
                    'bic'           => array(
                        'label' => __('BIC', 'woocommerce'),
                        'value' => $bacs_account->bic,
                    ),
                ), $order_id);

                foreach ($account_fields as $field_key => $field) {
                    if (! empty($field['value'])) {
                        $account_html .= '<li class="' . esc_attr($field_key) . '">' . wp_kses_post($field['label']) . ': <strong>' . wp_kses_post(wptexturize($field['value'])) . '</strong></li>' . PHP_EOL;
                        $has_details   = true;
                    }
                }

                $account_html .= '</ul>';
            }

            if ($has_details) {
                echo '<section class="woocommerce-bacs-bank-details"><h2 class="wc-bacs-bank-details-heading">' . __('Our bank details', 'woocommerce') . '</h2>' . PHP_EOL . $account_html . '</section>';
            }
        }
    }
}
