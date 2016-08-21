<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Gateways;

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
        if (!$sent_to_admin && 'bacs' === $order->payment_method && $order->has_status('on-hold')) {
            if ($this->instructions) {
                echo wpautop(wptexturize(function_exists('pll__') ? pll__($this->instructions) : __($this->instructions, 'woocommerce'))).PHP_EOL;
            }
            $this->bank_details($order->id);
        }
    }

    /**
     * Get bank details and place into a list format.
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
        $order = wc_get_order($order_id);

        // Get the order country and country $locale
        $country = $order->billing_country;
        $locale = $this->get_country_locale();

        // Get sortcode label in the $locale array and use appropriate one
        $sortcode = isset($locale[ $country ]['sortcode']['label']) ? $locale[ $country ]['sortcode']['label'] : __('Sort Code', 'woocommerce');

        $bacs_accounts = apply_filters('woocommerce_bacs_accounts', $this->account_details);

        if (!empty($bacs_accounts)) {
            echo '<h2>'.__('Our Bank Details', 'woocommerce').'</h2>'.PHP_EOL;

            foreach ($bacs_accounts as $bacs_account) {
                $bacs_account = (object) $bacs_account;

                if ($bacs_account->account_name || $bacs_account->bank_name) {
                    echo '<h3>'.wp_unslash(implode(' - ', array_filter(array($bacs_account->account_name, $bacs_account->bank_name)))).'</h3>'.PHP_EOL;
                }

                echo '<ul class="order_details bacs_details">'.PHP_EOL;

                        // BACS account fields shown on the thanks page and in emails
                        $account_fields = apply_filters('woocommerce_bacs_account_fields', array(
                                'account_number' => array(
                                        'label' => __('Account Number', 'woocommerce'),
                                        'value' => $bacs_account->account_number,
                                ),
                                'sort_code' => array(
                                        'label' => $sortcode,
                                        'value' => $bacs_account->sort_code,
                                ),
                                'iban' => array(
                                        'label' => __('IBAN', 'woocommerce'),
                                        'value' => $bacs_account->iban,
                                ),
                                'bic' => array(
                                        'label' => __('BIC', 'woocommerce'),
                                        'value' => $bacs_account->bic,
                                ),
                        ), $order_id);

                foreach ($account_fields as $field_key => $field) {
                    if (!empty($field['value'])) {
                        echo '<li class="'.esc_attr($field_key).'">'.esc_attr($field['label']).': <strong>'.wptexturize($field['value']).'</strong></li>'.PHP_EOL;
                    }
                }

                echo '</ul>';
            }
        }
    }
}
