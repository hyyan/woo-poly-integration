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
 * Gateways.
 *
 * Handle Payment Gateways
 *
 * @author Nicolas Joannès <nic@cobea.be>
 */
class Gateways
{
    /** @var array Array of enabled gateways */
    public $enabledGateways;

    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter('woocommerce_paypal_args', array($this, 'setPaypalLocalCode'));

        //key construction actions moved to wp_loaded as many payment gateways not ready before then..
        add_action('wp_loaded', array($this, 'loadOnWpLoaded')); // called only after Wordpress is loaded

        // Payment gateway title and respective description
        add_filter('woocommerce_gateway_title', array($this, 'translatePaymentGatewayTitle'), 10, 2);
        add_filter('woocommerce_gateway_description', array($this, 'translatePaymentGatewayDescription'), 10, 2);

        // Payment method in Thank You and Order View pages
        //add_filter( 'woocommerce_get_order_item_totals', array( $this, 'translateWoocommerceOrderPaymentMethod' ), 10, 2 ); // @todo: Needs further testing before enabling
    }

    /**
     * Move initialisation code to run on wp_loaded instead of constructor
     */
    public function loadOnWpLoaded()
    {
        // Set enabled payment gateways
        $this->enabledGateways = $this->getEnabledPaymentGateways();
        // Register Woocommerce Payment Gateway custom  titles and descriptions in Polylang's Strings translations table
        $this->registerGatewayStringsForTranslation();
        
        // Load payment gateways extensions (gateway intructions translation)
        $this->loadPaymentGatewaysExtentions();
    }
    
    /**
     * Set the PayPal checkout locale code.
     *
     * @param array $args the current paypal request args array
     */
    public function setPaypalLocalCode($args)
    {
        $lang = pll_current_language('locale');
        $args['locale.x'] = $lang;

        return $args;
    }

    /**
     * Get enabled payment gateways.
     *
     * @return array Array of enabled gateways
     */
    public function getEnabledPaymentGateways()
    {
        $_enabledGateways = array();

        $gateways = \WC_Payment_Gateways::instance();

        if (sizeof($gateways->payment_gateways) > 0) {
            foreach ($gateways->payment_gateways() as $gateway) {
                if ($this->isEnabled($gateway)) {
                    $_enabledGateways[ $gateway->id ] = $gateway;
                }
            }
        }

        return $_enabledGateways;
    }

    /**
     * Is payment gateway enabled?
     *
     * @param WC_Payment_Gateway $gateway
     *
     * @return bool True if gateway enabled, false otherwise
     */
    public function isEnabled($gateway)
    {
        return  'yes' === $gateway->enabled;
    }

    /**
     * Load payment gateways extentions.
     *
     * Manage the gateways intructions translation in the Thank You page and
     * Order emails. This is required because the strings are defined in the Construct
     * object and no filters are available.
     */
    public function loadPaymentGatewaysExtentions()
    {

        // Remove the gateway construct actions to avoid duplications
        $this->removeGatewayActions();

        // Load our custom extensions with Polylang support
        foreach ($this->enabledGateways as $gateway) {
            switch ($gateway->id) {
                case 'bacs':
                    new Gateways\GatewayBACS();
                    break;
                case 'cheque':
                    new Gateways\GatewayCheque();
                    break;
                case 'cod':
                    new Gateways\GatewayCOD();
                    break;
                default:
                    break;
            }

            // Allows other plugins to load payment gateways class extentions or change the gateway object
            do_action(HooksInterface::GATEWAY_LOAD_EXTENTION.$gateway->id, $gateway, $this->enabledGateways);
        }
    }

    /**
     * Remove the gateway construct actions to avoid duplications when we instanciate
     * the class extentions to add polylang support that doesn't have a __construct
     * function and will use the parent's function and set all these actions again.
     */
    public function removeGatewayActions()
    {
        $default_gateways = array('bacs', 'cheque', 'cod');
        
        foreach ($this->enabledGateways as $gateway) {
            if (in_array($gateway->id, $default_gateways)) {
                remove_action('woocommerce_email_before_order_table', array($gateway, 'email_instructions'));
                remove_action('woocommerce_thankyou_'.$gateway->id, array($gateway, 'thankyou_page'));
                //remove_action( 'woocommerce_update_options_payment_gateways_' . $gateway->id, array( $gateway, 'process_admin_options' ) );
            }
            if ('bacs' == $gateway->id) {
                remove_action('woocommerce_update_options_payment_gateways_'.$gateway->id, array($gateway, 'save_account_details'));
            }
        }
    }

    /**
     * Register Woocommerce Payment Gateway custom titles, descriptions and
     * instructions in Polylang's Strings translations table.
     */
    public function registerGatewayStringsForTranslation()
    {
        if (function_exists('pll_register_string') && !empty($this->enabledGateways)) {
            foreach ($this->enabledGateways as $gateway) {
                $settings = get_option($gateway->plugin_id.$gateway->id.'_settings');

                if (!empty($settings)) {
                    if (isset($settings['title'])) {
                        pll_register_string($gateway->plugin_id.$gateway->id.'_gateway_title', $settings['title'], __('Woocommerce Payment Gateways', 'woo-poly-integration'));
                    }
                    if (isset($settings['description'])) {
                        pll_register_string($gateway->plugin_id.$gateway->id.'_gateway_description', $settings['description'], __('Woocommerce Payment Gateways', 'woo-poly-integration'));
                    }
                    if (isset($settings['instructions'])) {
                        pll_register_string($gateway->plugin_id.$gateway->id.'_gateway_instructions', $settings['instructions'], __('Woocommerce Payment Gateways', 'woo-poly-integration'));
                    }
                }
            }
        }
    }

    /**
     * Translate Payment gateway title.
     *
     * @param string     Gateway title
     * @param int        Gateway id
     *
     * @return string Translated title
     */
    public function translatePaymentGatewayTitle($title, $id)
    {
        return function_exists('pll__') ? pll__($title) : __($title, 'woocommerce');
    }

    /**
     * Translate Payment gateway description.
     *
     * @param string     Gateway description
     * @param int        Gateway id
     *
     * @return string Translated description
     */
    public function translatePaymentGatewayDescription($description, $id)
    {
        return function_exists('pll__') ? pll__($description) : __($description, 'woocommerce');
    }

    /**
     * Translate the payment method in Thank You and Order View pages.
     *
     * @param array    $total_rows Array of the order item totals
     * @param WC_Order $order      Order object
     *
     * @return array Order item totals with translated payment method
     */
    public function translateWoocommerceOrderPaymentMethod($total_rows, $order)
    {
        if (isset($total_rows['payment_method']['value'])) {
            $total_rows['payment_method']['value'] = function_exists('pll__') ? pll__($total_rows['payment_method']['value']) : __($total_rows['payment_method']['value'], 'woocommerce');
        }

        return $total_rows;
    }
}
